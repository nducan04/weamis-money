<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function history(Request $request)
    {
        $members = User::where('role', '!=', 'admin')->orderBy('id')->get();
        $projects = Project::all();
        $fund = Fund::firstOrCreate(
            ['id' => 1],
            ['name' => 'Trả nợ thuê Ltd', 'balance' => 7133503.00, 'total_profit' => 1200000.00]
        );

        $allTransactions = Transaction::with(['user', 'project', 'responsibleUser', 'claimantUser', 'approver'])->latest()->get()->map(function($tx) {
            return [
                'id' => $tx->id,
                'user_id' => $tx->user_id,
                'user_name' => $tx->user ? $tx->user->name : 'N/A',
                'user_avatar' => $tx->user ? $tx->user->avatar : null,
                'project_id' => $tx->project_id,
                'project_name' => $tx->project ? $tx->project->name : null,
                'responsible_user_name' => $tx->responsibleUser ? $tx->responsibleUser->name : null,
                'claimant_user_name' => $tx->claimantUser ? $tx->claimantUser->name : null,
                'type' => $tx->type,
                'amount' => (float)$tx->amount,
                'description' => $tx->description,
                'billing_cycle' => $tx->billing_cycle ?? null,
                'evidence_type' => $tx->evidence_type ?? 'none',
                'evidence_value' => $tx->evidence_value ?? null,
                'status' => $tx->status,
                'created_at' => $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '',
                'created_at_formatted' => $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : '',
            ];
        });

        return view('history', compact('members', 'projects', 'fund', 'allTransactions'));
    }

    public function report(Request $request)
    {
        $members = User::where('role', '!=', 'admin')->orderBy('id')->get();
        $projects = Project::with(['members' => function($q) {
            $q->where('role', '!=', 'admin');
        }])->get();

        $fund = Fund::firstOrCreate(
            ['id' => 1],
            ['name' => 'Trả nợ thuê Ltd', 'balance' => 7133503.00, 'total_profit' => 1200000.00]
        );

        $allTransactions = Transaction::with(['user', 'project', 'responsibleUser', 'claimantUser', 'approver'])->latest()->get()->map(function($tx) {
            return [
                'id' => $tx->id,
                'user_id' => $tx->user_id,
                'user_name' => $tx->user ? $tx->user->name : 'N/A',
                'user_avatar' => $tx->user ? $tx->user->avatar : null,
                'project_id' => $tx->project_id,
                'project_name' => $tx->project ? $tx->project->name : null,
                'responsible_user_name' => $tx->responsibleUser ? $tx->responsibleUser->name : null,
                'claimant_user_name' => $tx->claimantUser ? $tx->claimantUser->name : null,
                'type' => $tx->type,
                'amount' => (float)$tx->amount,
                'description' => $tx->description,
                'billing_cycle' => $tx->billing_cycle ?? null,
                'evidence_type' => $tx->evidence_type ?? 'none',
                'evidence_value' => $tx->evidence_value ?? null,
                'status' => $tx->status,
                'created_at' => $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '',
                'created_at_formatted' => $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : '',
            ];
        });

        $projectsData = $projects->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'status' => $p->status,
                'weamis_fund_percentage' => (float)$p->weamis_fund_percentage,
                'members' => $p->members->map(function($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'share_percentage' => (float)$m->pivot->share_percentage,
                    ];
                }),
            ];
        });

        return view('report', compact('members', 'projects', 'projectsData', 'fund', 'allTransactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'claimant_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment,distribution,withdrawal',
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'billing_cycle' => 'nullable|string|max:100',
            'evidence_type' => 'nullable|in:file,link,text,none',
            'evidence_link' => 'nullable|string',
            'evidence_text' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'created_at' => 'nullable|date',
        ]);

        $authUser = auth()->user();
        if (!$authUser?->isAdmin()) {
            $validated['user_id'] = $authUser->id;
        }

        $fund = Fund::firstOrFail();
        $user = User::findOrFail($validated['user_id']);

        // Handle Evidence
        $evidenceType = $validated['evidence_type'] ?? 'none';
        $evidenceValue = null;

        if ($request->hasFile('evidence_file')) {
            $evidenceType = 'file';
            $evidenceValue = $this->uploadToCatbox($request->file('evidence_file'));
        } elseif (!empty($validated['evidence_link'])) {
            $evidenceType = 'link';
            $evidenceValue = $validated['evidence_link'];
        } elseif (!empty($validated['evidence_text'])) {
            $evidenceType = 'text';
            $evidenceValue = $validated['evidence_text'];
        }

        DB::transaction(function () use ($fund, $user, $validated, $evidenceType, $evidenceValue) {
            $status = in_array($validated['type'], ['contribution', 'repayment']) ? 'approved' : 'pending';
            $adminUser = User::where('role', 'admin')->first() ?? $user;

            $txData = [
                'fund_id' => $fund->id,
                'user_id' => $user->id,
                'project_id' => $validated['project_id'] ?? null,
                'responsible_user_id' => $validated['responsible_user_id'] ?? null,
                'claimant_user_id' => $validated['claimant_user_id'] ?? null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'billing_cycle' => $validated['billing_cycle'] ?? null,
                'evidence_type' => $evidenceType,
                'evidence_value' => $evidenceValue,
                'status' => $status,
                'approved_by' => $status === 'approved' ? $adminUser->id : null,
            ];

            if (!empty($validated['created_at'])) {
                $txData['created_at'] = $validated['created_at'];
            }

            Transaction::create($txData);

            if ($status === 'approved') {
                $this->applyTransactionImpact($fund, $user, $validated['type'], $validated['amount'], $validated['project_id'] ?? null);
            }
        });

        return redirect()->back()->with('success', 'Giao dịch đã được thêm thành công!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $authUser = auth()->user();
        if (!$authUser?->isAdmin() && $transaction->user_id !== $authUser?->id) {
            return redirect()->back()->with('error', 'Bạn chỉ có quyền chỉnh sửa giao dịch của chính mình!');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'claimant_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment,distribution,withdrawal',
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'billing_cycle' => 'nullable|string|max:100',
            'evidence_type' => 'nullable|in:file,link,text,none',
            'evidence_link' => 'nullable|string',
            'evidence_text' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'created_at' => 'nullable|date',
        ]);

        if (!$authUser?->isAdmin()) {
            $validated['user_id'] = $transaction->user_id;
        }

        $fund = Fund::firstOrFail();

        // Handle Evidence
        $evidenceType = $transaction->evidence_type;
        $evidenceValue = $transaction->evidence_value;

        if ($request->hasFile('evidence_file')) {
            $evidenceType = 'file';
            $evidenceValue = $this->uploadToCatbox($request->file('evidence_file'));
        } elseif (!empty($validated['evidence_link'])) {
            $evidenceType = 'link';
            $evidenceValue = $validated['evidence_link'];
        } elseif (!empty($validated['evidence_text'])) {
            $evidenceType = 'text';
            $evidenceValue = $validated['evidence_text'];
        }

        DB::transaction(function () use ($transaction, $fund, $validated, $evidenceType, $evidenceValue) {
            $oldUser = User::findOrFail($transaction->user_id);
            $newUser = User::findOrFail($validated['user_id']);

            if ($transaction->status === 'approved') {
                $this->revertTransactionImpact($fund, $oldUser, $transaction->type, $transaction->amount, $transaction->project_id);
                $this->applyTransactionImpact($fund, $newUser, $validated['type'], $validated['amount'], $validated['project_id'] ?? null);
            }
            $transaction->user_id = $newUser->id;
            $transaction->project_id = $validated['project_id'] ?? null;
            $transaction->responsible_user_id = $validated['responsible_user_id'] ?? null;
            $transaction->claimant_user_id = $validated['claimant_user_id'] ?? null;
            $transaction->type = $validated['type'];
            $transaction->amount = $validated['amount'];
            $transaction->description = $validated['description'];
            $transaction->billing_cycle = $validated['billing_cycle'] ?? null;
            $transaction->evidence_type = $evidenceType;
            $transaction->evidence_value = $evidenceValue;
            if (!empty($validated['created_at'])) {
                $transaction->created_at = $validated['created_at'];
            }
            $transaction->save();
        });

        return redirect()->back()->with('success', 'Đã cập nhật giao dịch thành công!');
    }

    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()?->isAdmin() && $transaction->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa giao dịch của thành viên khác. Chỉ Admin hoặc người tạo mới có quyền.');
        }

        $fund = Fund::firstOrFail();
        $user = User::findOrFail($transaction->user_id);

        DB::transaction(function () use ($transaction, $fund, $user) {
            if ($transaction->status === 'approved') {
                $this->revertTransactionImpact($fund, $user, $transaction->type, $transaction->amount, $transaction->project_id);
            }

            $transaction->delete();
        });

        return redirect()->back()->with('success', 'Đã xóa giao dịch và cập nhật lại số dư!');
    }

    public function approve(Transaction $transaction)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Chỉ Admin mới có quyền phê duyệt giao dịch.');
        }

        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        DB::transaction(function () use ($transaction) {
            $fund = $transaction->fund;
            $user = $transaction->user;
            $adminUser = auth()->user();

            $this->applyTransactionImpact($fund, $user, $transaction->type, $transaction->amount, $transaction->project_id);

            $transaction->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
            ]);
        });

        return redirect()->back()->with('success', 'Đã phê duyệt giao dịch!');
    }

    public function reject(Transaction $transaction)
    {
        if (!auth()->user()?->isAdmin()) {
            return redirect()->back()->with('error', 'Chỉ Admin mới có quyền từ chối giao dịch.');
        }

        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        $transaction->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
    }

    private function applyTransactionImpact(Fund $fund, User $user, string $type, float $amount, ?int $projectId = null): void
    {
        if (!$projectId) {
            if ($type === 'contribution' || $type === 'repayment') {
                $fund->increment('balance', $amount);
            } elseif ($type === 'expense' || $type === 'loan' || $type === 'distribution' || $type === 'withdrawal') {
                $fund->decrement('balance', $amount);
            }
        }

        if ($type === 'repayment') {
            $user->decrement('current_debt', min($amount, $user->current_debt));
        } elseif ($type === 'loan') {
            $user->increment('current_debt', $amount);
        }
    }

    private function revertTransactionImpact(Fund $fund, User $user, string $type, float $amount, ?int $projectId = null): void
    {
        if (!$projectId) {
            if ($type === 'contribution' || $type === 'repayment') {
                $fund->decrement('balance', $amount);
            } elseif ($type === 'expense' || $type === 'loan' || $type === 'distribution' || $type === 'withdrawal') {
                $fund->increment('balance', $amount);
            }
        }

        if ($type === 'repayment') {
            $user->increment('current_debt', $amount);
        } elseif ($type === 'loan') {
            $user->decrement('current_debt', min($amount, $user->current_debt));
        }
    }

    private function uploadToCatbox($file): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::attach(
                'fileToUpload',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post('https://catbox.moe/user/api.php', [
                'reqtype' => 'fileupload',
            ]);

            $body = trim($response->body());
            if ($response->successful() && (str_starts_with($body, 'https://files.catbox.moe/') || str_starts_with($body, 'http://files.catbox.moe/'))) {
                return $body;
            }
        } catch (\Throwable $e) {
            // Fallback to local upload
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/evidences'), $filename);
        return '/uploads/evidences/' . $filename;
    }
}
