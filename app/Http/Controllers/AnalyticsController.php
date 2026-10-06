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
        // DUAL-AXIS FINANCIAL ENGINE:
        // 1. Net Balance: PURE DOUBLE-ENTRY (SỔ CÁI KÉP: Tổng Có - Tổng Nợ)
        //    -> Phản ánh tài sản, lương còn để dư trong tổ chức (loại trừ 10% quỹ)
        // 2. Gross Sweat-Equity: DYNAMIC SLICING PIE (Tỷ lệ 1:1:1)
        //    -> Slices = Base (Page 8) + Doanh thu tạo ra (R) - Tiền đã rút bỏ túi (W)
        // ══════════════════════════════════════════════════════════════════
        
        // Base Gross Balances from Master Sheet (Tài sản ròng - Gross.csv Dòng 81)
        $legacyGrossBaseline = [
            'hts'  => 5082766, // Hồ Trùng Sơn (31,27% sheet gốc)
            'nhv'  => 5088220, // Nguyễn Hoàng Việt (31,30% sheet gốc)
            'nqd'  => 2025667, // Nguyễn Quý Đức (12,46% sheet gốc)
            'lvta' => 2050000, // Lê Văn Thành An (12,61% sheet gốc)
            'ntk'  => 774999,  // Nguyễn Trung Kiên (4,77% sheet gốc)
            'tqm'  => 573732,  // Trịnh Quang Minh (3,53% sheet gốc)
            'ndph' => 570000,  // Nguyễn Đăng Phúc Hưng (3,51% sheet gốc)
            'ndd'  => 90000,   // Dương (0,55% sheet gốc)
            'vdha' => -310000, // Vũ Đức Hoàng Anh
            'pd'   => -510000, // Phúc Đăng
            'tds'  => -315000, // Đăng Sinh (-315.000₫ sheet gốc)
            'qm'   => 0,       // Quốc Minh
            'md'   => 0,       // Minh Đức
            'nda'  => 0,       // Nguyễn Đức An
        ];

        // Additional Sweat-Equity from post-baseline project deliverables (nếu có dự án mới được phân bổ cổ phần)
        $deltaGross = [];

        // Recovered Cash / Salary Withdrawn (W x 1.0)
        $recoveredCash = [];

        $grossBalances = [];
        $netBalances   = [];
        $memberLedgers = [];
        $inMap         = [];
        $outMap        = [];

        foreach ($members as $m) {
            $userAcc = Account::where('type', 'user')->where('owner_id', $m->id)->first();
            $in = 0.0;
            $out = 0.0;
            $memberLedger = [];

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

                // Lấy toàn bộ lịch sử bút toán cá nhân để drilldown tra cứu chi tiết
                $memberLedger = JournalEntry::where(function($q) use ($userAcc) {
                    $q->where('to_account_id', $userAcc->id)->orWhere('from_account_id', $userAcc->id);
                })->whereHas('transaction', fn($q) => $q->where('status', 'approved'))
                  ->with('transaction')
                  ->latest('id')
                  ->get()
                  ->map(function($e) use ($userAcc) {
                      $isCredit = ($e->to_account_id === $userAcc->id);
                      return [
                          'id'        => $e->id,
                          'tx_id'     => $e->transaction_id,
                          'date'      => $e->transaction?->created_at ? $e->transaction->created_at->format('d/m/Y H:i') : '',
                          'desc'      => $e->transaction?->description ?? '',
                          'type'      => $e->transaction?->type ?? '',
                          'is_credit' => $isCredit,
                          'amount'    => (float)$e->amount,
                          'memo'      => $e->memo ?? '',
                      ];
                  })->values()->all();
            } else {
                $netVal = 0.0;
            }

            $inMap[$m->id]         = $in;
            $outMap[$m->id]        = $out;
            $netBalances[$m->id]   = $netVal;
            $memberLedgers[$m->id] = $memberLedger;

            // Gross Sweat-Equity (Dynamic Slicing Pie 1:1:1):
            // Slices = Base Sheet Page 8 + Delta Project Contribution (R) - Recovered Cash (W)
            $base = $legacyGrossBaseline[$m->username] ?? 0.0;
            $delta = $deltaGross[$m->username] ?? 0.0;
            $withdrawn = $recoveredCash[$m->username] ?? 0.0;
            $grossBalances[$m->id] = (float) ($base + $delta - $withdrawn);
        }

        // Calculate total positive Gross for Equity % calculation
        $totalPosGross = 0.0;
        foreach ($grossBalances as $uid => $val) {
            if ($val > 0) {
                $totalPosGross += $val;
            }
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
                'id'       => $u->id,
                'name'     => $u->name,
                'username' => $u->username,
                'avatar'   => $u->avatar,
                'email'    => $u->email,
                'value'    => (float) round($val, 0),
                'equity'   => $equityStr,
            ];
        }

        // Build Net Data array (kèm IN / OUT để phục vụ SSOT Spreadsheet Table)
        $netData = [];
        foreach ($netBalances as $uid => $val) {
            $u = $userMap[$uid] ?? null;
            if (!$u) continue;

            $netData[] = [
                'id'        => $u->id,
                'name'      => $u->name,
                'username'  => $u->username,
                'avatar'    => $u->avatar,
                'email'     => $u->email,
                'in_value'  => (float) round($inMap[$uid] ?? 0, 0),
                'out_value' => (float) round($outMap[$uid] ?? 0, 0),
                'value'     => (float) round($val, 0),
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

        // 3. System Health Audit
        $auditService = app(\App\Services\Audit\ReconciliationService::class);
        $auditResult = $auditService->reconcile(false); // Dry-run check

        return view('analytics.networth', compact('grossData', 'netData', 'memberLedgers', 'treasuryCash', 'nodes', 'edges', 'edgeMap', 'topPairs', 'members', 'projects', 'auditResult'));
    }

    public function network()
    {
        return redirect()->route('analytics.networth');
    }
}
