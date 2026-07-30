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
        $members = User::all();

        return view('projects.index', compact('projects', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'weamis_fund_percentage' => 'required|numeric|min:0|max:100',
            'lead_user_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'weamis_fund_percentage' => $validated['weamis_fund_percentage'],
            'lead_user_id' => $validated['lead_user_id'] ?? null,
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
        $project->load(['lead', 'members', 'projectMembers.user', 'transactions.user', 'transactions.responsibleUser', 'transactions.claimantUser']);

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

        $allMembers = User::all();

        return view('projects.show', compact('project', 'totalIncome', 'totalExpense', 'fundCut', 'distributable', 'memberPayouts', 'allMembers'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
            'weamis_fund_percentage' => 'required|numeric|min:0|max:100',
            'lead_user_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'weamis_fund_percentage' => $validated['weamis_fund_percentage'],
            'lead_user_id' => $validated['lead_user_id'] ?? null,
        ]);

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

        return redirect()->route('projects.show', $project)->with('success', 'Đã cập nhật dự án thành công!');
    }

    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Đã xóa dự án ' . $name . '!');
    }
}
