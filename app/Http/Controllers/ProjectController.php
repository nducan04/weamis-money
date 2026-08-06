<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMember;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['lead', 'members', 'transactions'])->latest()->get();
        $members = User::where('role', '!=', 'admin')->orderBy('id')->get();

        return view('projects.index', compact('projects', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')->withoutTrashed()],
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'weamis_fund_percentage' => 'required|numeric|min:0|max:100',
            'lead_user_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.share_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $sumShares = 0;
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $m) {
                $sumShares += (float)($m['share_percentage'] ?? 0);
            }
        }
        $totalPct = (float)$validated['weamis_fund_percentage'] + $sumShares;

        if (round($totalPct, 2) > 100.00) {
            return redirect()->back()->withInput()->with('error', 'Lỗi: Tổng % Trích Về Quỹ (' . $validated['weamis_fund_percentage'] . '%) và cổ phần các thành viên (' . $sumShares . '%) vượt quá 100% (Tổng hiện tại: ' . $totalPct . '%). Vui lòng kiểm tra lại!');
        }

        $code = strtoupper($validated['code']);
        Project::onlyTrashed()->where('code', $code)->forceDelete();

        $project = Project::create([
            'name' => $validated['name'],
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'release_date' => $validated['release_date'] ?? null,
            'weamis_fund_percentage' => $validated['weamis_fund_percentage'],
            'lead_user_id' => $validated['lead_user_id'] ?? null,
            'created_by_user_id' => auth()->id(),
            'status' => 'active',
        ]);

        if (!empty($validated['members'])) {
            $effectiveFrom = '2020-01-01'; // Default to a very early date so it always becomes the first period
            foreach ($validated['members'] as $m) {
                if ($m['share_percentage'] > 0) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $m['user_id'],
                        'share_percentage' => $m['share_percentage'],
                        'effective_from' => $effectiveFrom,
                    ]);
                }
            }
        }

        return redirect()->route('projects.index')->with('success', 'Đã tạo dự án ' . $project->name . ' thành công!');
    }

    public function show($projectKey)
    {
        $project = Project::where('id', $projectKey)->orWhere('code', $projectKey)->first();

        if (!$project) {
            return redirect()->route('projects.index')->with('error', 'Dự án ID/Mã #' . $projectKey . ' không tồn tại hoặc danh sách đã được đồng bộ lại. Đã chuyển về Danh sách dự án.');
        }

        $project->load(['lead', 'creator', 'members', 'projectMembers.user', 'transactions.user', 'transactions.responsibleUser', 'transactions.claimantUser']);

        // Revenue by type
        $approvedTxs = $project->transactions()->where('status', 'approved');
        $totalIncome = (clone $approvedTxs)->whereIn('type', ['contribution', 'repayment'])->sum('amount');
        $totalExpense = (clone $approvedTxs)->where('type', 'expense')->sum('amount');

        $devRevenue = (clone $approvedTxs)->whereIn('type', ['contribution', 'repayment'])->where('revenue_type', 'development')->sum('amount');
        $subRevenue = (clone $approvedTxs)->whereIn('type', ['contribution', 'repayment'])->where('revenue_type', 'subscription')->sum('amount');
        $otherRevenue = $totalIncome - $devRevenue - $subRevenue;

        // Temporal Shares: get all periods and calculate income & payouts per period
        $sharePeriods = ProjectMember::getPeriods($project->id);
        $shareTimeline = [];

        foreach ($sharePeriods as $idx => $period) {
            $startDate = $idx === 0 ? null : \Carbon\Carbon::parse($period)->startOfDay();
            $nextPeriod = isset($sharePeriods[$idx + 1]) ? $sharePeriods[$idx + 1] : null;
            $endDate = $nextPeriod ? \Carbon\Carbon::parse($nextPeriod)->startOfDay() : null;

            $txQuery = $project->transactions()->where('status', 'approved');
            if ($startDate) {
                $txQuery->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $txQuery->where('created_at', '<', $endDate);
            }

            $periodTxs = $txQuery->get();
            $periodIncome = (float)$periodTxs->whereIn('type', ['contribution', 'repayment'])->sum('amount');
            $periodExpense = (float)$periodTxs->where('type', 'expense')->sum('amount');

            $periodFundCut = ($periodIncome * $project->weamis_fund_percentage) / 100;
            $periodDistributable = max(0, $periodIncome - $periodFundCut);

            $periodShares = ProjectMember::where('project_id', $project->id)
                ->whereDate('effective_from', $period)
                ->with('user')
                ->get();

            $sumShares = $periodShares->sum('share_percentage');
            $periodPayouts = [];

            foreach ($periodShares as $pm) {
                $amount = ($sumShares > 0)
                    ? ($periodDistributable * $pm->share_percentage) / $sumShares
                    : 0;

                $periodPayouts[] = [
                    'user' => $pm->user,
                    'share_percentage' => (float)$pm->share_percentage,
                    'estimated_payout' => $amount,
                ];
            }

            $shareTimeline[] = [
                'effective_from' => $period,
                'period_income' => $periodIncome,
                'period_expense' => $periodExpense,
                'fund_cut' => $periodFundCut,
                'distributable' => $periodDistributable,
                'members' => $periodPayouts,
            ];
        }

        // Active / Current Period summary for top card
        $currentTimeline = !empty($shareTimeline) ? end($shareTimeline) : null;
        $fundCut = $currentTimeline ? $currentTimeline['fund_cut'] : 0;
        $distributable = $currentTimeline ? $currentTimeline['distributable'] : 0;
        $currentPeriodIncome = $currentTimeline ? $currentTimeline['period_income'] : 0;
        $memberPayouts = $currentTimeline ? $currentTimeline['members'] : [];

        $allMembers = User::where('role', '!=', 'admin')->orderBy('id')->get();

        $unassignedTransactions = Transaction::with(['user'])
            ->whereNull('project_id')
            ->latest()
            ->get();

        return view('projects.show', compact(
            'project', 'totalIncome', 'totalExpense',
            'devRevenue', 'subRevenue', 'otherRevenue',
            'fundCut', 'distributable', 'memberPayouts', 'currentPeriodIncome',
            'shareTimeline', 'sharePeriods',
            'allMembers', 'unassignedTransactions'
        ));
    }

    public function attachTransactions(Request $request, Project $project)
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);

        Transaction::whereIn('id', $validated['transaction_ids'])
            ->update(['project_id' => $project->id]);

        $count = count($validated['transaction_ids']);

        return redirect()->back()->with('success', "Đã gắn thành công {$count} giao dịch vào dự án {$project->name}!");
    }

    public function update(Request $request, Project $project)
    {
        if (!$project->canManage(auth()->user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa cấu hình dự án này. Chỉ Admin, Người tạo hoặc Lead của dự án mới có quyền.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
            'release_date' => 'nullable|date',
            'weamis_fund_percentage' => 'required|numeric|min:0|max:100',
            'lead_user_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.share_percentage' => 'nullable|numeric|min:0|max:100',
            'share_effective_from' => 'nullable|date',
        ]);

        $sumShares = 0;
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $m) {
                $sumShares += (float)($m['share_percentage'] ?? 0);
            }
        }
        $totalPct = (float)$validated['weamis_fund_percentage'] + $sumShares;

        if (round($totalPct, 2) > 100.00) {
            return redirect()->back()->withInput()->with('error', 'Lỗi: Tổng % Trích Về Quỹ (' . $validated['weamis_fund_percentage'] . '%) và cổ phần các thành viên (' . $sumShares . '%) vượt quá 100% (Tổng hiện tại: ' . $totalPct . '%). Vui lòng kiểm tra lại!');
        }

        $oldStatus = $project->status;
        $newStatus = $validated['status'];

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $newStatus,
            'release_date' => $validated['release_date'] ?? null,
            'weamis_fund_percentage' => $validated['weamis_fund_percentage'],
            'lead_user_id' => $validated['lead_user_id'] ?? null,
        ]);

        // Handle Fund Crediting on Project Completion
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $totalIncome = $project->transactions()->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
            $fundCut = ($totalIncome * $project->weamis_fund_percentage) / 100;

            if ($fundCut > 0) {
                $fund = \App\Models\Fund::firstOrCreate(
                    ['id' => 1],
                    ['name' => 'Trả nợ thuê Ltd', 'balance' => 7133503.00]
                );
                $fund->increment('balance', $fundCut);

                Transaction::create([
                    'fund_id' => 1,
                    'user_id' => auth()->id() ?? $project->lead_user_id ?? 1,
                    'project_id' => $project->id,
                    'type' => 'contribution',
                    'amount' => $fundCut,
                    'description' => 'Trích Về Quỹ Chung (' . number_format($project->weamis_fund_percentage, 0) . '%) khi Hoàn Thành Dự Án: ' . $project->name,
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                ]);

                $project->update(['fund_credited_amount' => $fundCut]);
            }
        } elseif ($newStatus !== 'completed' && $oldStatus === 'completed' && $project->fund_credited_amount > 0) {
            // Revert fund credit if status changed back to active/cancelled
            $fund = \App\Models\Fund::first();
            if ($fund) {
                $fund->decrement('balance', $project->fund_credited_amount);
            }
            $project->update(['fund_credited_amount' => 0]);
        }

        // Sync Project Members with Temporal Share Periods
        $effectiveFrom = $validated['share_effective_from'] ?? now()->format('Y-m-d');

        // Delete existing shares for THIS specific effective_from date only
        ProjectMember::where('project_id', $project->id)
            ->where('effective_from', $effectiveFrom)
            ->delete();

        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $m) {
                if ($m['share_percentage'] > 0) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $m['user_id'],
                        'share_percentage' => $m['share_percentage'],
                        'effective_from' => $effectiveFrom,
                    ]);
                }
            }
        }

        $msg = $newStatus === 'completed' && $oldStatus !== 'completed'
            ? 'Chúc mừng! Dự án đã hoàn thành và số tiền Trích Về Quỹ Chung đã được cộng dồn vào Quỹ!'
            : 'Đã cập nhật dự án thành công!';

        return redirect()->route('projects.show', $project)->with('success', $msg);
    }

    public function destroy(Project $project)
    {
        if (!$project->canManage(auth()->user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa dự án này. Chỉ Admin, Người tạo hoặc Lead của dự án mới có quyền xóa.');
        }

        $name = $project->name;
        Transaction::where('project_id', $project->id)->update(['project_id' => null]);
        ProjectMember::where('project_id', $project->id)->delete();
        $project->forceDelete();

        return redirect()->route('projects.index')->with('success', 'Đã xóa hoàn toàn dự án ' . $name . '!');
    }

    public function destroySharePeriod(Request $request, Project $project)
    {
        if (!$project->canManage(auth()->user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa đợt cổ phần này.');
        }

        $effectiveFrom = $request->input('effective_from');
        if ($effectiveFrom) {
            ProjectMember::where('project_id', $project->id)
                ->whereDate('effective_from', $effectiveFrom)
                ->delete();
        }

        return redirect()->route('projects.show', $project)->with('success', 'Đã xóa đợt cổ phần ngày ' . \Carbon\Carbon::parse($effectiveFrom)->format('d/m/Y') . '!');
    }

    public function updateSharePeriodDate(Request $request, Project $project)
    {
        if (!$project->canManage(auth()->user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa đợt cổ phần này.');
        }

        $validated = $request->validate([
            'old_effective_from' => 'required|date',
            'new_effective_from' => 'required|date',
        ]);

        ProjectMember::where('project_id', $project->id)
            ->whereDate('effective_from', $validated['old_effective_from'])
            ->update(['effective_from' => $validated['new_effective_from']]);

        return redirect()->route('projects.show', $project)->with('success', 'Đã cập nhật ngày mốc cổ phần thành ' . \Carbon\Carbon::parse($validated['new_effective_from'])->format('d/m/Y') . '!');
    }
}
