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
        // 1. Verified Historical Baseline (Transactions up to ID 153)
        // 2. Dynamic Processing for ANY NEW approved transactions (ID > 153)
        // ══════════════════════════════════════════════════════════════════

        // Map all users and username aliases to canonical user_id
        $userByUsername = [];
        foreach ($members as $m) {
            $userByUsername[$m->id] = $m->id;
            if ($m->username) {
                $userByUsername[$m->username] = $m->id;
            }
        }
        
        // Map legacy baseline keys to active DB users (supports usernames pd, tds, ndd, phucdang, etc. + name fallback)
        $aliasMap = [
            'hotrungson' => ['hts', 'son', 'hotrungson'],
            'viet'       => ['nhv', 'viet'],
            'quyduc'     => ['nqd', 'duc', 'quyduc'],
            'quangminh'  => ['tqm', 'minh', 'quangminh'],
            'thanhan'    => ['lvta', 'an', 'thanhan'],
            'phuchung'   => ['ndph', 'hung', 'phuchung'],
            'trungkien'  => ['ntk', 'kien', 'trungkien'],
            'hoanganh'   => ['vdha', 'hoanganh'],
            'phucdang'   => ['pd', 'phucdang'],
            'dangsinh'   => ['tds', 'dangsinh'],
            'duong'      => ['ndd', 'duong'],
        ];

        foreach ($aliasMap as $legacyKey => $possibleUsernames) {
            $found = null;
            foreach ((array)$possibleUsernames as $dbUname) {
                $found = $members->firstWhere('username', $dbUname);
                if ($found) break;
            }
            if (!$found) {
                // Fallback by name matching if username differs
                if ($legacyKey === 'phucdang') $found = $members->first(fn($u) => str_contains(mb_strtolower($u->name), 'phúc đăng'));
                if ($legacyKey === 'dangsinh') $found = $members->first(fn($u) => str_contains(mb_strtolower($u->name), 'sinh'));
                if ($legacyKey === 'duong')    $found = $members->first(fn($u) => str_contains(mb_strtolower($u->name), 'dương'));
            }
            if ($found) {
                $userByUsername[$legacyKey] = $found->id;
            }
        }

        // Initialize balances by user_id
        $grossBalances = [];
        $netBalances   = [];
        foreach ($members as $m) {
            $grossBalances[$m->id] = 0.0;
            $netBalances[$m->id]   = 0.0;
        }

        // Verified Base Gross Balances from Sheet
        $legacyGrossBaseline = [
            'hotrungson' => 5747766,
            'viet'       => 2896666,
            'quyduc'     => 2740000,
            'quangminh'  => 1986666,
            'thanhan'    => 1500000,
            'phuchung'   => 570000,
            'trungkien'  => -183334,
            'hoanganh'   => -310000,
            'phucdang'   => -510000,
            'dangsinh'   => -710000,
            'duong'      => -510000,
        ];

        // Verified Base Net Balances from Sheet
        $legacyNetBaseline = [
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
            'hoanganh'   => -560000,
        ];

        foreach ($legacyGrossBaseline as $key => $val) {
            $uid = $userByUsername[$key] ?? null;
            if ($uid) {
                $grossBalances[$uid] = (float) $val;
            }
        }

        foreach ($legacyNetBaseline as $key => $val) {
            $uid = $userByUsername[$key] ?? null;
            if ($uid) {
                $netBalances[$uid] = (float) $val;
            }
        }

        $treasuryCash = -1538520;

        // Process all NEW approved transactions created after historical sheet baseline (after 06/08/2026)
        $newTxs = Transaction::where('status', 'approved')
            ->where('created_at', '>', '2026-08-06 23:59:59')
            ->with(['user', 'project.members'])
            ->orderBy('created_at')
            ->get();

        foreach ($newTxs as $tx) {
            $uid = $tx->user_id;
            $amount = (float) $tx->amount;

            if ($tx->type === 'contribution') {
                if ($tx->project_id && $tx->project) {
                    $pMembers = $tx->project->members->where('role', '!=', 'admin');
                    foreach ($pMembers as $pm) {
                        $mUid = $pm->id;
                        $pct = (float) $pm->pivot->share_percentage / 100;
                        
                        $grossBalances[$mUid] = ($grossBalances[$mUid] ?? 0) + ($amount * $pct);
                        $netBalances[$mUid]   = ($netBalances[$mUid] ?? 0)   + (($amount * 0.90) * $pct);
                    }
                    $treasuryCash += ($amount * 0.10);
                } else {
                    if ($uid) {
                        $grossBalances[$uid] = ($grossBalances[$uid] ?? 0) + $amount;
                        $netBalances[$uid]   = ($netBalances[$uid] ?? 0)   + $amount;
                    }
                }
            } elseif ($tx->type === 'loan' || $tx->type === 'withdrawal') {
                if ($uid) {
                    $netBalances[$uid] = ($netBalances[$uid] ?? 0) - $amount;
                }
                $treasuryCash -= $amount;
            } elseif ($tx->type === 'repayment') {
                if ($uid) {
                    $netBalances[$uid] = ($netBalances[$uid] ?? 0) + $amount;
                }
            } elseif ($tx->type === 'expense') {
                $treasuryCash -= $amount;
            }
        }

        // Calculate total positive Gross for Equity % calculation
        $totalPosGross = 0;
        foreach ($grossBalances as $uid => $val) {
            if ($val > 0) $totalPosGross += $val;
        }

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
                'name'     => $u->name,
                'username' => $u->username,
                'value'    => (float) $val,
                'equity'   => $equityStr,
            ];
        }

        // Build Net Data array
        $netData = [];
        foreach ($netBalances as $uid => $val) {
            $u = $userMap[$uid] ?? null;
            if (!$u) continue;

            $netData[] = [
                'name'     => $u->name,
                'username' => $u->username,
                'value'    => (float) $val,
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
