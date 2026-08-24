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
            'quocminh'   => ['qm', 'quocminh'],
            'minhduc'    => ['md', 'minhduc'],
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
                if ($legacyKey === 'quocminh') $found = $members->first(fn($u) => str_contains(mb_strtolower($u->name), 'quốc minh'));
                if ($legacyKey === 'minhduc')  $found = $members->first(fn($u) => str_contains(mb_strtolower($u->name), 'minh đức'));
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
            'viet'       => 2546666,
            'quyduc'     => 2390000,
            'quangminh'  => 2636666,
            'thanhan'    => 1550000,
            'phuchung'   => 570000,
            'trungkien'  => -183334,
            'hoanganh'   => -310000,
            'phucdang'   => -510000,
            'dangsinh'   => -710000,
            'duong'      => -510000,
            'quocminh'   => 0,
            'minhduc'    => 0,
        ];

        // Verified Base Net Balances from Sheet (after Lẩu Phan Đào Duy Anh split)
        $legacyNetBaseline = [
            'hotrungson' => 5249033,
            'quangminh'  => 2171267,
            'thanhan'    => 1301267,
            'viet'       => 432534,
            'quyduc'     => 372534,
            'phuchung'   => 321267,
            'trungkien'  => -648733,
            'phucdang'   => -510000,
            'duong'      => -510000,
            'dangsinh'   => -958733,
            'hoanganh'   => -808733,
            'quocminh'   => -248733,
            'minhduc'    => -248733,
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

        $treasuryCash = 995000;

        // Process all NEW approved transactions created after baseline (created after 06/08/2026)
        $newTxs = Transaction::where('status', 'approved')
            ->where('id', '>', 153)
            ->where('created_at', '>', '2026-08-06 23:59:59')
            ->with(['user', 'project.members', 'journalEntries.toAccount'])
            ->orderBy('id')
            ->get();

        foreach ($newTxs as $tx) {
            $uid = $tx->user_id;
            $amount = (float) $tx->amount;

            if ($tx->is_fund_only) {
                // Direct fund impact: No impact on personal Net or Gross
                if ($tx->type === 'contribution' || $tx->type === 'repayment' || $tx->type === 'profit' || $tx->type === 'adjustment') {
                    $treasuryCash += $amount;
                } elseif ($tx->type === 'expense' || $tx->type === 'loan' || $tx->type === 'withdrawal' || $tx->type === 'distribution') {
                    $treasuryCash -= $amount;
                }
                continue;
            }

            // Check if tx has split journal entries pointing to projects or users
            $hasMultipleSplits = $tx->journalEntries->count() > 1;
            $projectEntries = $tx->journalEntries->filter(fn($je) => $je->toAccount && $je->toAccount->type === 'project');
            $userEntries = $tx->journalEntries->filter(fn($je) => $je->toAccount && $je->toAccount->type === 'user');

            if ($tx->type === 'contribution' || $tx->type === 'repayment' || $tx->type === 'profit') {
                if ($hasMultipleSplits && ($projectEntries->isNotEmpty() || $userEntries->isNotEmpty())) {
                    // Process user splits (direct allocation to individual user accounts)
                    if ($userEntries->isNotEmpty()) {
                        $treasuryCash += $userEntries->sum('amount');
                        foreach ($userEntries as $je) {
                            $targetUid = $je->toAccount->owner_id;
                            $jeAmount = (float)$je->amount;
                            if ($targetUid) {
                                $netBalances[$targetUid] = ($netBalances[$targetUid] ?? 0) + $jeAmount;
                                $grossBalances[$targetUid] = ($grossBalances[$targetUid] ?? 0) + $jeAmount;
                            }
                        }
                    }

                    // Process project splits (distribution according to project shares)
                    if ($projectEntries->isNotEmpty()) {
                        foreach ($projectEntries as $je) {
                            $projId = $je->toAccount->owner_id;
                            $project = Project::with('members')->find($projId);
                            $jeAmount = (float) $je->amount;

                            if ($project) {
                                $pMembers = $project->members->where('role', '!=', 'admin')->unique('id');
                                $fundPct = (float) $project->weamis_fund_percentage / 100;
                                $treasuryCash += ($jeAmount * $fundPct);

                                // Net calculation: direct share_percentage on gross amount
                                foreach ($pMembers as $pm) {
                                    $mUid = $pm->id;
                                    $netPct = (float) $pm->pivot->share_percentage / 100;
                                    $netBalances[$mUid] = ($netBalances[$mUid] ?? 0) + ($jeAmount * $netPct);
                                }

                                // Gross calculation (Sweat Equity)
                                if ($projId == 15 || $projId == 14 || $projId == 16) { // Wifi marketing: equal split of fund cut across project members
                                    $memberCount = $pMembers->count();
                                    $fundCut = $jeAmount * $fundPct;
                                    foreach ($pMembers as $pm) {
                                        $mUid = $pm->id;
                                        $netPct = (float) $pm->pivot->share_percentage / 100;
                                        $grossBalances[$mUid] = ($grossBalances[$mUid] ?? 0) + ($jeAmount * $netPct) + ($fundCut / $memberCount);
                                    }
                                } elseif ($projId == 17) { // Landing BMG: 50% Kiên, 50% Minh
                                    foreach ($pMembers as $pm) {
                                        $mUid = $pm->id;
                                        $grossBalances[$mUid] = ($grossBalances[$mUid] ?? 0) + ($jeAmount * 0.50);
                                    }
                                } else {
                                    $totalMemberShare = $pMembers->sum(fn($pm) => (float)$pm->pivot->share_percentage);
                                    foreach ($pMembers as $pm) {
                                        $mUid = $pm->id;
                                        $grossRatio = $totalMemberShare > 0 ? ((float)$pm->pivot->share_percentage / $totalMemberShare) : 0;
                                        $grossBalances[$mUid] = ($grossBalances[$mUid] ?? 0) + ($jeAmount * $grossRatio);
                                    }
                                }
                            }
                        }
                    }
                } elseif ($tx->project_id && $tx->project) {
                    $pMembers = $tx->project->members->where('role', '!=', 'admin')->unique('id');
                    $fundPct = (float) $tx->project->weamis_fund_percentage / 100;
                    $treasuryCash += ($amount * $fundPct);
                    $totalMemberShare = $pMembers->sum(fn($pm) => (float)$pm->pivot->share_percentage);

                    foreach ($pMembers as $pm) {
                        $mUid = $pm->id;
                        $netPct = (float) $pm->pivot->share_percentage / 100;
                        $grossRatio = $totalMemberShare > 0 ? ((float)$pm->pivot->share_percentage / $totalMemberShare) : $netPct;

                        $grossBalances[$mUid] = ($grossBalances[$mUid] ?? 0) + ($amount * $grossRatio);
                        $netBalances[$mUid]   = ($netBalances[$mUid] ?? 0)   + ($amount * $netPct);
                    }
                } else {
                    $isRepaymentDesc = str_contains(mb_strtolower($tx->description), 'trả lẩu') || str_contains(mb_strtolower($tx->description), 'trả nợ');
                    $targetUid = $tx->responsible_user_id ?: $uid;
                    if ($targetUid) {
                        if (!$isRepaymentDesc) {
                            $grossBalances[$targetUid] = ($grossBalances[$targetUid] ?? 0) + $amount;
                        }
                        $netBalances[$targetUid] = ($netBalances[$targetUid] ?? 0) + $amount;
                    }
                    if ($isRepaymentDesc) {
                        $treasuryCash += $amount;
                    }
                }
            } elseif ($tx->type === 'loan' || $tx->type === 'withdrawal' || $tx->type === 'expense') {
                $targetUid = $tx->responsible_user_id ?: $uid;
                if ($targetUid) {
                    $netBalances[$targetUid] = ($netBalances[$targetUid] ?? 0) - $amount;
                    $grossBalances[$targetUid] = ($grossBalances[$targetUid] ?? 0) - $amount;
                }
            } elseif ($tx->type === 'repayment') {
                $targetUid = $tx->responsible_user_id ?: $uid;
                if ($targetUid) {
                    $netBalances[$targetUid] = ($netBalances[$targetUid] ?? 0) + $amount;
                }
                $treasuryCash += $amount;
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
                'name'     => $u->name,
                'username' => $u->username,
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
