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

        // Net Worth calculation per member matching Google Sheet formula
        $rawNetWorth = [];
        foreach ($members as $m) {
            $userTxs = Transaction::where('user_id', $m->id)->where('status', 'approved')->get();
            $contributions = $userTxs->whereIn('type', ['contribution', 'repayment', 'profit'])->sum('amount');
            $withdrawals = $userTxs->whereIn('type', ['expense', 'loan', 'withdrawal'])->sum('amount');
            $netWorth = $contributions - $withdrawals;

            $rawNetWorth[] = [
                'id' => $m->id,
                'name' => $m->name,
                'username' => $m->username,
                'avatar' => $m->avatar,
                'contributions' => $contributions,
                'withdrawals' => $withdrawals,
                'net_worth' => $netWorth,
                'is_investment_fund' => strtolower($m->username) === 'tuithantai' || str_contains(strtolower($m->name), 'túi thần tài'),
            ];
        }

        // Sort descending by Net Worth
        usort($rawNetWorth, function ($a, $b) {
            return $b['net_worth'] <=> $a['net_worth'];
        });

        // Find max positive (human) and min negative Net Worth
        $humanMembers = array_filter($rawNetWorth, fn($item) => !$item['is_investment_fund']);
        $maxPositiveId = null;
        $minNegativeId = null;

        $maxVal = -PHP_FLOAT_MAX;
        $minVal = PHP_FLOAT_MAX;

        foreach ($humanMembers as $hm) {
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
            if ($item['is_investment_fund']) {
                $statusLabel = 'Quỹ đầu tư tích lũy sinh lời';
            } elseif ($item['id'] === $maxPositiveId) {
                $statusLabel = 'Chủ nợ lớn nhất của quỹ';
            } elseif ($item['net_worth'] > 0) {
                $statusLabel = 'Chủ nợ của quỹ';
            } elseif ($item['id'] === $minNegativeId) {
                $statusLabel = 'Đang âm rồng nhiều nhất (Lương + Vay)';
            } elseif ($item['net_worth'] < 0) {
                $statusLabel = 'Đang mượn rồng của quỹ';
            } else {
                $statusLabel = 'Thành viên';
            }

            $item['status_label'] = $statusLabel;
            return $item;
        }, $rawNetWorth);

        return view('analytics.networth', compact('netWorthData'));
    }

    public function network()
    {
        $members = User::where('role', '!=', 'admin')->get();
        $projects = Project::with('members')->get();

        // Collaboration Network Graph data (Nodes and Edges)
        $nodes = [];
        $edges = [];

        // Member nodes (Exclude Admin)
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

        // Project nodes and edges
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

            // Member-to-Member collaboration weight
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

        // Top collaborating member pairs
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

        return view('analytics.network', compact('nodes', 'edges', 'edgeMap', 'topPairs', 'members', 'projects'));
    }
}
