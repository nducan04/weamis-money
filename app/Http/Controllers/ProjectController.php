<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMember;
use App\Models\Transaction;
use Illuminate\Http\Request;

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
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'weamis_fund_percentage' => 'required|numeric|min:0|max:100',
            'lead_user_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.share_percentage' => 'required|numeric|min:0|max:100',
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

        $project = Project::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'release_date' => $validated['release_date'] ?? null,
            'weamis_fund_percentage' => $validated['weamis_fund_percentage'],
            'lead_user_id' => $validated['lead_user_id'] ?? null,
            'created_by_user_id' => auth()->id(),
            'status' => 'active',
        ]);

        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $m) {
                if ($m['share_percentage'] > 0) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $m['user_id'],
                        'share_percentage' => $m['share_percentage'],
                    ]);
                }
            }
        }

        return redirect()->route('projects.index')->with('success', 'Đã tạo dự án ' . $project->name . ' thành công!');
    }

    public function show(Project $project)
    {
        $project->load(['lead', 'creator', 'members', 'projectMembers.user', 'transactions.user', 'transactions.responsibleUser', 'transactions.claimantUser']);

        $totalIncome = $project->transactions()->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
        $totalExpense = $project->transactions()->where('status', 'approved')->where('type', 'expense')->sum('amount');

        // Audit & Payout Breakdown
        $fundCut = ($totalIncome * $project->weamis_fund_percentage) / 100;
        $distributable = max(0, $totalIncome - $fundCut);

        $memberPayouts = [];
        foreach ($project->projectMembers as $pm) {
            $amount = ($distributable * $pm->share_percentage) / 100;
            $memberPayouts[] = [
                'user' => $pm->user,
                'share_percentage' => $pm->share_percentage,
                'estimated_payout' => $amount,
            ];
        }

        $allMembers = User::where('role', '!=', 'admin')->orderBy('id')->get();

        return view('projects.show', compact('project', 'totalIncome', 'totalExpense', 'fundCut', 'distributable', 'memberPayouts', 'allMembers'));
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
            'members.*.share_percentage' => 'required|numeric|min:0|max:100',
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
                    ['name' => 'Trả nợ thuê Ltd', 'balance' => 7028106.00, 'total_profit' => 126160.00]
                );
                $fund->increment('balance', $fundCut);
                $fund->increment('total_profit', $fundCut);

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
                $fund->decrement('total_profit', $project->fund_credited_amount);
            }
            $project->update(['fund_credited_amount' => 0]);
        }

        // Sync Project Members
        ProjectMember::where('project_id', $project->id)->delete();
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $m) {
                if ($m['share_percentage'] > 0) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $m['user_id'],
                        'share_percentage' => $m['share_percentage'],
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
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Đã xóa dự án ' . $name . '!');
    }
}
