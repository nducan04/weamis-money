<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Transaction;
use App\Models\Fund;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function networth()
    {
        $members = User::where('role', '!=', 'admin')->get();

        Fund::syncBalance();
        $fund = Fund::first();
        $treasuryCash = $fund ? (float)$fund->balance : 0.0;

        // ══════════════════════════════════════════════════════════════════
        // PURE DOUBLE-ENTRY GENERAL LEDGER (SỔ CÁI KÉP TOÀN DIỆN)
        // 1. Net Balance: Tổng Có (In to user) - Tổng Nợ (Out from user)
        // 2. Gross Sweat-Equity: Vốn Cống Hiến Giữ Lại (Retained Equity = max(0, Net))
        // ══════════════════════════════════════════════════════════════════
        $grossBalances = [];
        $netBalances   = [];

        foreach ($members as $m) {
            $userAcc = Account::where('type', 'user')->where('owner_id', $m->id)->first();
            $in = 0.0;
            $out = 0.0;

            if ($userAcc) {
                $in = (float) JournalEntry::where('to_account_id', $userAcc->id)
                    ->whereHas('transaction', fn($q) => $q->where('status', 'approved'))
                    ->sum('amount');
                $out = (float) JournalEntry::where('from_account_id', $userAcc->id)
                    ->whereHas('transaction', fn($q) => $q->where('status', 'approved'))
                    ->sum('amount');
                
                // Đồng bộ cột balance của Account để mọi trang (History, Networth) nhất quán 100%
                $netVal = $in - $out;
                $userAcc->update(['balance' => $netVal]);
            } else {
                $netVal = 0.0;
            }

            $netBalances[$m->id] = $netVal;
            // Vốn cống hiến ngầm thực tế (Gross Retained Equity): 
            // Nếu rút sạch lương âm cả ví thì cống hiến ròng còn lại = 0₫
            $grossBalances[$m->id] = max(0.0, $netVal);
        }

        // Calculate total positive Gross for Equity % calculation
        $totalPosGross = array_sum($grossBalances);

        $userMap = $members->keyBy('id');

        // Build Gross Data array
        $grossData = [];
        foreach ($grossBalances as $uid => $val) {
            $u = $userMap[$uid] ?? null;
            if (!$u) continue;

            $equityStr = ($val > 0 && $totalPosGross > 0) 
                ? number_format(($val / $totalPosGross) * 100, 2, ',', '.') . '%' 
                : '--';

            $grossData[] = [
                'id'       => $u->id,
                'name'     => $u->name,
                'username' => $u->username,
                'avatar'   => $u->avatar,
                'email'    => $u->email,
                'value'    => (float) round($val, 0),
                'equity'   => $equityStr,
            ];
        }

        // Build Net Data array
        $netData = [];
        foreach ($netBalances as $uid => $val) {
            $u = $userMap[$uid] ?? null;
            if (!$u) continue;

            $netData[] = [
                'id'       => $u->id,
                'name'     => $u->name,
                'username' => $u->username,
                'avatar'   => $u->avatar,
                'email'    => $u->email,
                'value'    => (float) round($val, 0),
            ];
        }

        // Sort descending: Highest balance first
        usort($grossData, fn($a, $b) => $b['value'] <=> $a['value']);
        usort($netData, fn($a, $b) => $b['value'] <=> $a['value']);

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
                'label' => '📁 ' . $p->name,
                'group' => 'project',
                'shape' => 'box',
                'borderRadius' => 10,
                'margin' => 12,
                'color' => [
                    'background' => '#f59e0b',
                    'border' => '#d97706',
                    'highlight' => ['background' => '#fbbf24', 'border' => '#b45309']
                ],
                'font' => ['color' => '#0f172a', 'face' => 'Plus Jakarta Sans', 'size' => 14, 'bold' => 'true', 'vadjust' => 0]
            ];

            $activeShares = ProjectMember::getActiveShares($p->id)->filter(fn($pm) => $pm->user && $pm->user->role !== 'admin');
            $pMemberIds = $activeShares->pluck('user_id')->toArray();
            foreach ($activeShares as $pm) {
                $uid = $pm->user_id;
                $edges[] = [
                    'from' => 'u_' . $uid,
                    'to' => 'p_' . $p->id,
                    'label' => $pm->share_percentage . '%',
                    'color' => ['color' => '#10b981', 'highlight' => '#34d399'],
                    'width' => 3,
                    'font' => ['color' => '#10b981', 'size' => 14, 'face' => 'Plus Jakarta Sans', 'bold' => 'true', 'strokeWidth' => 4, 'strokeColor' => '#0f172a']
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

        return view('analytics.networth', compact('grossData', 'netData', 'treasuryCash', 'nodes', 'edges', 'edgeMap', 'topPairs', 'members', 'projects'));
    }

    public function network()
    {
        return redirect()->route('analytics.networth');
    }
}
