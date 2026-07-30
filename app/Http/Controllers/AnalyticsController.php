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
        $members = User::all();
        $projects = Project::with('members')->get();

        // 1. Net Worth calculation per member
        $netWorthData = [];
        foreach ($members as $m) {
            $contributions = Transaction::where('user_id', $m->id)->where('status', 'approved')->where('type', 'contribution')->sum('amount');
            $repayments = Transaction::where('user_id', $m->id)->where('status', 'approved')->where('type', 'repayment')->sum('amount');
            $loans = Transaction::where('user_id', $m->id)->where('status', 'approved')->where('type', 'loan')->sum('amount');
            $expenses = Transaction::where('user_id', $m->id)->where('status', 'approved')->where('type', 'expense')->sum('amount');

            // Estimated payouts from projects
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

            $netWorth = ($contributions + $repayments + $projectEarnings) - ($loans + $expenses);

            $netWorthData[] = [
                'id' => $m->id,
                'name' => $m->name,
                'avatar' => $m->avatar,
                'contributions' => $contributions,
                'project_earnings' => $projectEarnings,
                'loans' => $loans,
                'net_worth' => $netWorth,
            ];
        }

        // 2. Collaboration Network Graph data (Nodes and Edges)
        $nodes = [];
        $edges = [];

        // Member nodes
        foreach ($members as $m) {
            $nodes[] = [
                'id' => 'u_' . $m->id,
                'label' => $m->name,
                'group' => 'member',
                'shape' => 'circularImage',
                'image' => $m->avatar && str_starts_with($m->avatar, 'http') ? $m->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($m->name),
            ];
        }

        // Project nodes and edges
        $edgeMap = [];
        foreach ($projects as $p) {
            $nodes[] = [
                'id' => 'p_' . $p->id,
                'label' => $p->name . ' (' . $p->code . ')',
                'group' => 'project',
                'shape' => 'box',
                'color' => '#10b981',
            ];

            $pMemberIds = $p->members->pluck('id')->toArray();
            foreach ($pMemberIds as $uid) {
                $edges[] = [
                    'from' => 'u_' . $uid,
                    'to' => 'p_' . $p->id,
                    'label' => $p->members->where('id', $uid)->first()->pivot->share_percentage . '%',
                    'color' => ['color' => '#6366f1'],
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
