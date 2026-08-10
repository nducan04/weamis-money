<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function networth()
    {
        $members = User::where('role', '!=', 'admin')->get();

        // Net Worth is now calculated using the Double-Entry system's Account balances
        $rawNetWorth = [];
        foreach ($members as $m) {
            $userAcc = \App\Models\Account::where('type', 'user')->where('owner_type', \App\Models\User::class)->where('owner_id', $m->id)->first();
            
            $netWorth = $userAcc ? (float) $userAcc->balance : 0;
            
            // To maintain compatibility with the UI, we can pull total_in and total_out
            $contributions = 0;
            $withdrawals = 0;
            if ($userAcc) {
                // contributions = Out from User (User -> Fund/Others)
                $contributions = \App\Models\JournalEntry::where('from_account_id', $userAcc->id)->whereHas('transaction', function($q) {
                    $q->where('status', 'approved');
                })->sum('amount');
                
                // withdrawals = In to User (Fund/Others -> User)
                $withdrawals = \App\Models\JournalEntry::where('to_account_id', $userAcc->id)->whereHas('transaction', function($q) {
                    $q->where('status', 'approved');
                })->sum('amount');
            }

            $rawNetWorth[] = [
                'id' => $m->id,
                'name' => $m->name,
                'username' => $m->username,
                'avatar' => $m->avatar,
                'contributions' => $contributions,
                'withdrawals' => $withdrawals,
                'net_worth' => $netWorth,
            ];
        }

        // Sort descending by Net Worth
        usort($rawNetWorth, function ($a, $b) {
            return $b['net_worth'] <=> $a['net_worth'];
        });

        // Find max positive and min negative Net Worth
        $maxPositiveId = null;
        $minNegativeId = null;

        $maxVal = -PHP_FLOAT_MAX;
        $minVal = PHP_FLOAT_MAX;

        foreach ($rawNetWorth as $hm) {
            if ($hm['net_worth'] > 0 && $hm['net_worth'] > $maxVal) {
                $maxVal = $hm['net_worth'];
                $maxPositiveId = $hm['id'];
            }
            if ($hm['net_worth'] < 0 && $hm['net_worth'] < $minVal) {
                $minVal = $hm['net_worth'];
                $minNegativeId = $hm['id'];
            }
        }

        $netWorthData = array_map(function ($item) use ($maxPositiveId, $minNegativeId) {
            if ($item['id'] === $maxPositiveId) {
                $statusLabel = 'Chủ nợ lớn nhất của quỹ';
            } elseif ($item['net_worth'] > 0) {
                $statusLabel = 'Chủ nợ của quỹ';
            } elseif ($item['id'] === $minNegativeId) {
                $statusLabel = 'Đang âm ròng nhiều nhất (Lương + Vay)';
            } elseif ($item['net_worth'] < 0) {
                $statusLabel = 'Đang mượn ròng của quỹ';
            } else {
                $statusLabel = 'Thành viên';
            }

            $item['status_label'] = $statusLabel;
            return $item;
        }, $rawNetWorth);

        $fund = \App\Models\Fund::first();

        // 2. Collaboration Network Graph data (Nodes and Edges)
        $projects = Project::with('members')->get();
        $nodes = [];
        $edges = [];

        foreach ($members as $m) {
            $avatarUrl = ($m->avatar && (str_starts_with($m->avatar, 'http://') || str_starts_with($m->avatar, 'https://') || str_starts_with($m->avatar, '/uploads/')))
                ? $m->avatar
                : 'https://ui-avatars.com/api/?name=' . urlencode($m->name) . '&background=10b981&color=ffffff&size=128&font-size=0.45&bold=true';

            $nodes[] = [
                'id' => 'u_' . $m->id,
                'label' => $m->name,
                'group' => 'member',
                'shape' => 'circularImage',
                'image' => $avatarUrl,
                'borderWidth' => 3,
                'color' => [
                    'border' => '#10b981',
                    'background' => '#0f172a',
                    'highlight' => ['border' => '#34d399', 'background' => '#1e293b']
                ]
            ];
        }

        $edgeMap = [];
        foreach ($projects as $p) {
            $nodes[] = [
                'id' => 'p_' . $p->id,
                'label' => '📁 ' . $p->name . ' (' . $p->code . ')',
                'group' => 'project',
                'shape' => 'box',
                'borderRadius' => 8,
                'margin' => 10,
                'color' => [
                    'background' => '#f59e0b',
                    'border' => '#d97706',
                    'highlight' => ['background' => '#fbbf24', 'border' => '#b45309']
                ],
                'font' => ['color' => '#0f172a', 'face' => 'Plus Jakarta Sans', 'size' => 12, 'vadjust' => 0]
            ];

            $pMembers = $p->members->where('role', '!=', 'admin');
            $pMemberIds = $pMembers->pluck('id')->toArray();
            foreach ($pMemberIds as $uid) {
                $pm = $pMembers->where('id', $uid)->first();
                $edges[] = [
                    'from' => 'u_' . $uid,
                    'to' => 'p_' . $p->id,
                    'label' => $pm->pivot->share_percentage . '%',
                    'color' => ['color' => '#10b981', 'highlight' => '#34d399'],
                    'width' => 2,
                    'font' => ['color' => '#10b981', 'size' => 11, 'face' => 'Plus Jakarta Sans', 'strokeWidth' => 3, 'strokeColor' => '#0f172a']
                ];
            }

            $count = count($pMemberIds);
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $u1 = $pMemberIds[$i];
                    $u2 = $pMemberIds[$j];
                    $key = $u1 < $u2 ? "{$u1}_{$u2}" : "{$u2}_{$u1}";
                    $edgeMap[$key] = ($edgeMap[$key] ?? 0) + 1;
                }
            }
        }

        $topPairs = [];
        $memberMap = $members->keyBy('id');
        foreach ($edgeMap as $key => $sharedCount) {
            list($u1, $u2) = explode('_', $key);
            if (isset($memberMap[$u1]) && isset($memberMap[$u2])) {
                $topPairs[] = [
                    'm1' => $memberMap[$u1],
                    'm2' => $memberMap[$u2],
                    'count' => $sharedCount
                ];
            }
        }
        usort($topPairs, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $topPairs = array_slice($topPairs, 0, 5);

        return view('analytics.networth', compact('netWorthData', 'fund', 'nodes', 'edges', 'edgeMap', 'topPairs', 'members', 'projects'));
    }

    public function network()
    {
        return redirect()->route('analytics.networth');
    }
}
