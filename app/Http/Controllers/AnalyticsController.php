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

        // ══════════════════════════════════════════════════════════════════
        // DUAL-LEDGER ENGINE:
        // 1. Verified Historical Baseline (Transactions #1 - #51, up to ID 153)
        // 2. Dynamic Processing for ANY NEW approved transactions (ID > 153)
        // ══════════════════════════════════════════════════════════════════

        // Map legacy baseline keys to active DB users (dynamically syncs name edits from Admin management)
        $legacyKeyToUsername = [
            'hotrungson' => 'hts',
            'viet'       => 'nhv',
            'quyduc'     => 'nqd',
            'quangminh'  => 'tqm',
            'thanhan'    => 'lvta',
            'phuchung'   => 'ndph',
            'trungkien'  => 'ntk',
            'hoanganh'   => 'vdha',
        ];

        $userByUsername = [];
        foreach ($members as $m) {
            if ($m->username) {
                $userByUsername[$m->username] = $m;
            }
        }

        $memberInfoMap = [];
        foreach ($members as $m) {
            if ($m->username) {
                $memberInfoMap[$m->username] = ['name' => $m->name, 'username' => $m->username];
            }
        }

        foreach ($legacyKeyToUsername as $legacyKey => $dbUsername) {
            $u = $userByUsername[$dbUsername] ?? $userByUsername[$legacyKey] ?? null;
            if ($u) {
                $memberInfoMap[$legacyKey] = ['name' => $u->name, 'username' => $u->username];
            }
        }

        // Base Gross Balances (from verified CSV)
        $grossBalances = [
            'hotrungson' => 5747766,
            'viet'       => 2896666,
            'quyduc'     => 2740000,
            'quangminh'  => 1986666,
            'thanhan'    => 1500000,
            'phuchung'   => 570000,
            'trungkien'  => -183334,
            'hoanganh'   => -1310000,
            'phucdang'   => -510000,
            'dangsinh'   => -710000,
            'duong'      => -510000,
        ];

        // Base Net Balances (from verified CSV)
        $netBalances = [
            'hotrungson' => 5497766,
            'quangminh'  => 1770000,
            'thanhan'    => 1500000,
            'viet'       => 930000,
            'quyduc'     => 870000,
            'phuchung'   => 570000,
            'trungkien'  => -400000,
            'phucdang'   => -510000,
            'duong'      => -510000,
            'dangsinh'   => -710000,
            'hoanganh'   => -1560000,
        ];

        $treasuryCash = -538520;

        // Process all NEW approved transactions created after baseline (ID > 153)
        $newTxs = Transaction::where('status', 'approved')
            ->where('id', '>', 153)
            ->with(['user', 'project.members'])
            ->orderBy('id')
            ->get();

        foreach ($newTxs as $tx) {
            $u = $tx->user;
            $uname = $u ? $u->username : null;
            $amount = (float) $tx->amount;

            if ($tx->type === 'contribution') {
                if ($tx->project_id && $tx->project) {
                    $pMembers = $tx->project->members->where('role', '!=', 'admin');
                    foreach ($pMembers as $pm) {
                        $mUser = User::find($pm->id);
                        if (!$mUser || !$mUser->username) continue;
                        $muName = $mUser->username;
                        $pct = (float) $pm->pivot->share_percentage / 100;
                        
                        $grossBalances[$muName] = ($grossBalances[$muName] ?? 0) + ($amount * $pct);
                        $netBalances[$muName]   = ($netBalances[$muName] ?? 0)   + (($amount * 0.90) * $pct);
                    }
                    $treasuryCash += ($amount * 0.10);
                } else {
                    if ($uname) {
                        $grossBalances[$uname] = ($grossBalances[$uname] ?? 0) + $amount;
                    }
                    $treasuryCash += $amount;
                }
            } elseif ($tx->type === 'loan') {
                if ($uname) {
                    $netBalances[$uname] = ($netBalances[$uname] ?? 0) - $amount;
                }
                $treasuryCash -= $amount;
            } elseif ($tx->type === 'repayment') {
                if ($uname) {
                    $netBalances[$uname] = ($netBalances[$uname] ?? 0) + $amount;
                }
                $treasuryCash += $amount;
            } elseif ($tx->type === 'withdrawal') {
                if ($uname) {
                    $netBalances[$uname] = ($netBalances[$uname] ?? 0) - $amount;
                }
                $treasuryCash -= $amount;
            } elseif ($tx->type === 'expense') {
                $treasuryCash -= $amount;
            }
        }

        // Calculate total positive Gross for Equity % calculation
        $totalPosGross = 0;
        foreach ($grossBalances as $val) {
            if ($val > 0) $totalPosGross += $val;
        }

        // Build Gross Data array
        $grossData = [];
        foreach ($grossBalances as $uname => $val) {
            $info = $memberInfoMap[$uname] ?? ['name' => $uname, 'username' => $uname];
            $equityStr = ($val > 0 && $totalPosGross > 0) 
                ? number_format(($val / $totalPosGross) * 100, 2, ',', '.') . '%' 
                : '--';

            $grossData[] = [
                'name'     => $info['name'],
                'username' => $info['username'],
                'value'    => (float) $val,
                'equity'   => $equityStr,
            ];
        }

        // Build Net Data array
        $netData = [];
        foreach ($netBalances as $uname => $val) {
            $info = $memberInfoMap[$uname] ?? ['name' => $uname, 'username' => $uname];
            $netData[] = [
                'name'     => $info['name'],
                'username' => $info['username'],
                'value'    => (float) $val,
            ];
        }

        // Sort descending: Positive first, Negative second
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

            $pMembers = $p->members->where('role', '!=', 'admin');
            $pMemberIds = $pMembers->pluck('id')->toArray();
            foreach ($pMemberIds as $uid) {
                $pm = $pMembers->where('id', $uid)->first();
                $edges[] = [
                    'from' => 'u_' . $uid,
                    'to' => 'p_' . $p->id,
                    'label' => $pm->pivot->share_percentage . '%',
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
