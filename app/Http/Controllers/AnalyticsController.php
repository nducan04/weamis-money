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
        $projects = Project::with('members')->get();

        // 1. Net Worth calculation per member
        $netWorthData = [];
        foreach ($members as $m) {
            $contributions = Transaction::where('user_id', $m->id)->where('status', 'approved')->where('type', 'contribution')->whereNull('project_id')->sum('amount');
            $loans = (float) $m->current_debt;

            // Estimated payouts from active & completed projects
            $projectEarnings = 0;
            foreach ($projects as $p) {
                $pIncome = $p->transactions()->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
                $pCut = ($pIncome * $p->weamis_fund_percentage) / 100;
                $pDistributable = max(0, $pIncome - $pCut);
                $pMember = $p->members->where('id', $m->id)->first();
                if ($pMember) {
                    $projectEarnings += ($pDistributable * $pMember->pivot->share_percentage) / 100;
                }
            }

            $netWorth = ($contributions + $projectEarnings) - $loans;

            $netWorthData[] = [
                'id' => $m->id,
                'name' => $m->name,
                'username' => $m->username,
                'avatar' => $m->avatar,
                'contributions' => $contributions,
                'project_earnings' => $projectEarnings,
                'loans' => $loans,
                'net_worth' => $netWorth,
            ];
        }

        // Sort descending by Net Worth
        usort($netWorthData, function ($a, $b) {
            return $b['net_worth'] <=> $a['net_worth'];
        });

        // 2. Collaboration Network Graph data (Nodes and Edges)
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

        return view('analytics.networth', compact('netWorthData', 'nodes', 'edges', 'edgeMap', 'members', 'projects'));
    }
}
