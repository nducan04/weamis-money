<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Fund;
use App\Models\JournalEntry;
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
            ['name' => 'Trả nợ thuê Ltd', 'balance' => 7135340.00]
        );

        $allTransactions = Transaction::with(['user', 'project', 'responsibleUser', 'claimantUser', 'approver'])->latest()->get()->map(function($tx) {
            // Get journal entry flow info and split breakdown
            $jes = JournalEntry::where('transaction_id', $tx->id)->with(['fromAccount', 'toAccount'])->get();
            $firstJe = $jes->first();
            $isSplit = $jes->count() > 1;

            $splits = $isSplit ? $jes->map(function($e) {
                return [
                    'amount' => (float)$e->amount,
                    'to_account_name' => $e->toAccount ? $e->toAccount->name : 'N/A',
                    'memo' => $e->memo,
                ];
            })->toArray() : [];

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
                'is_fund_only' => (bool)$tx->is_fund_only,
                'created_at' => $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '',
                'created_at_formatted' => $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : '',
                'from_account_name' => $firstJe && $firstJe->fromAccount ? $firstJe->fromAccount->name : null,
                'to_account_name' => $firstJe && $firstJe->toAccount ? $firstJe->toAccount->name : null,
                'is_split' => $isSplit,
                'splits' => $splits,
            ];
        });

        $accounts = Account::orderBy('type')->orderBy('name')->get();

        return view('history', compact('members', 'projects', 'fund', 'allTransactions', 'accounts'));
    }

    public function report(Request $request)
    {
        $members = User::where('role', '!=', 'admin')->orderBy('id')->get();
        $projects = Project::with(['members' => function($q) {
            $q->where('role', '!=', 'admin');
        }])->get();

        $fund = Fund::firstOrCreate(
            ['id' => 1],
            ['name' => 'Trả nợ thuê Ltd', 'balance' => 7135340.00]
        );

        $allTransactions = Transaction::with(['user', 'project', 'responsibleUser', 'claimantUser', 'approver', 'journalEntries.toAccount'])->latest()->get()->map(function($tx) {
            $splits = $tx->journalEntries->map(function($je) {
                return [
                    'to_account_id' => $je->to_account_id,
                    'owner_type' => $je->toAccount?->owner_type,
                    'owner_id' => $je->toAccount?->owner_id,
                    'amount' => (float)$je->amount,
                ];
            })->values()->all();

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
                'is_fund_only' => (bool)$tx->is_fund_only,
                'created_at' => $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '',
                'created_at_formatted' => $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : '',
                'is_split' => count($splits) > 1,
                'splits' => $splits,
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
        if (!$request->has('type') && $request->filled('project_id')) {
            $request->merge(['type' => 'contribution']);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'claimant_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment,distribution,withdrawal,adjustment',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'billing_cycle' => 'nullable|string|max:100',
            'evidence_type' => 'nullable|in:file,link,text,none',
            'evidence_link' => 'nullable|string',
            'evidence_text' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'revenue_type' => 'nullable|in:development,subscription',
            'is_fund_only' => 'nullable|boolean',
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

        $isFundOnly = $request->boolean('is_fund_only', false);

        DB::transaction(function () use ($fund, $user, $validated, $evidenceType, $evidenceValue, $isFundOnly) {
            $status = 'approved';
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
                'revenue_type' => $validated['type'] === 'expense' ? null : ($validated['revenue_type'] ?? (!empty($validated['project_id']) ? 'development' : null)),
                'evidence_type' => $evidenceType,
                'evidence_value' => $evidenceValue,
                'status' => $status,
                'is_fund_only' => $isFundOnly,
                'approved_by' => $status === 'approved' ? $adminUser->id : null,
            ];

            if (!empty($validated['created_at'])) {
                $txData['created_at'] = $validated['created_at'];
            }

            $tx = Transaction::create($txData);

            if ($status === 'approved') {
                $this->applyUserDebtImpact($user, $validated['type'], $validated['amount'], $isFundOnly);
                $this->createJournalEntry($tx);
            }

            // ALWAYS sync fund balance as the very last step
            $this->syncFundBalance();
        });

        return redirect()->back()->with('success', 'Giao dịch đã được thêm thành công!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $authUser = auth()->user();
        if (!$authUser?->isAdmin() && $transaction->user_id !== $authUser?->id) {
            return redirect()->back()->with('error', 'Bạn chỉ có quyền chỉnh sửa giao dịch của chính mình!');
        }

        if (!$request->has('type') && ($request->filled('project_id') || $transaction->project_id)) {
            $request->merge(['type' => 'contribution']);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'claimant_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment,distribution,withdrawal,adjustment',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'billing_cycle' => 'nullable|string|max:100',
            'evidence_type' => 'nullable|in:file,link,text,none',
            'evidence_link' => 'nullable|string',
            'evidence_text' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'revenue_type' => 'nullable|in:development,subscription',
            'is_fund_only' => 'nullable|boolean',
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

        $isFundOnly = $request->boolean('is_fund_only', false);

        DB::transaction(function () use ($transaction, $fund, $validated, $evidenceType, $evidenceValue, $isFundOnly) {
            $oldUser = User::findOrFail($transaction->user_id);
            $newUser = User::findOrFail($validated['user_id']);

            if ($transaction->status === 'approved') {
                $this->revertUserDebtImpact($oldUser, $transaction->type, $transaction->amount, (bool)$transaction->is_fund_only);
                $this->deleteJournalEntries($transaction);
                $this->applyUserDebtImpact($newUser, $validated['type'], $validated['amount'], $isFundOnly);
            }
            $transaction->user_id = $newUser->id;
            $transaction->project_id = $validated['project_id'] ?? null;
            $transaction->responsible_user_id = $validated['responsible_user_id'] ?? null;
            $transaction->claimant_user_id = $validated['claimant_user_id'] ?? null;
            $transaction->type = $validated['type'];
            $transaction->amount = $validated['amount'];
            $transaction->description = $validated['description'];
            $transaction->billing_cycle = $validated['billing_cycle'] ?? null;
            $transaction->is_fund_only = $isFundOnly;
            if ($validated['type'] === 'expense') {
                $transaction->revenue_type = null;
            } elseif (isset($validated['revenue_type'])) {
                $transaction->revenue_type = $validated['revenue_type'];
            } elseif (!empty($validated['project_id']) && $validated['type'] === 'contribution' && !$transaction->revenue_type) {
                $transaction->revenue_type = 'development';
            }
            $transaction->evidence_type = $evidenceType;
            $transaction->evidence_value = $evidenceValue;
            if (!empty($validated['created_at'])) {
                $transaction->created_at = $validated['created_at'];
            }
            $transaction->save();

            if ($transaction->status === 'approved') {
                $this->createJournalEntry($transaction);
            }

            // ALWAYS sync fund balance as the very last step
            $this->syncFundBalance();
        });

        return redirect()->back()->with('success', 'Đã cập nhật giao dịch thành công!');
    }

    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()?->isAdmin() && $transaction->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa giao dịch của thành viên khác. Chỉ Admin hoặc người tạo mới có quyền.');
        }

        $user = User::findOrFail($transaction->user_id);

        DB::transaction(function () use ($transaction, $user) {
            if ($transaction->status === 'approved') {
                $this->revertUserDebtImpact($user, $transaction->type, $transaction->amount, (bool)$transaction->is_fund_only);
                $this->deleteJournalEntries($transaction);
            }

            // Soft-delete the transaction FIRST
            $transaction->delete();

            // THEN sync fund balance (now the deleted tx is excluded from the sum)
            $this->syncFundBalance();
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
            $user = $transaction->user;
            $adminUser = auth()->user();

            // Update status FIRST so syncFundBalance sees it as approved
            $transaction->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
            ]);

            $this->applyUserDebtImpact($user, $transaction->type, $transaction->amount, (bool)$transaction->is_fund_only);
            $this->createJournalEntry($transaction);

            // ALWAYS sync fund balance as the very last step
            $this->syncFundBalance();
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

    public function split(Request $request, Transaction $transaction)
    {
        $authUser = auth()->user();
        if (!$authUser?->isAdmin() && $transaction->user_id !== $authUser?->id) {
            return redirect()->back()->with('error', 'Bạn chỉ có quyền tách giao dịch của chính mình!');
        }

        $validated = $request->validate([
            'splits' => 'required|array|min:2',
            'splits.*.to_account_id' => 'required|exists:accounts,id',
            'splits.*.amount' => 'required|numeric|min:1',
            'splits.*.memo' => 'nullable|string|max:255',
        ]);

        $totalSplit = array_sum(array_column($validated['splits'], 'amount'));
        if (abs($totalSplit - $transaction->amount) > 0.01) {
            return redirect()->back()->with('error', 'Tổng số tiền tách (' . number_format($totalSplit) . 'đ) phải đúng bằng số tiền giao dịch gốc (' . number_format($transaction->amount) . 'đ)!');
        }

        DB::transaction(function () use ($transaction, $validated) {
            $this->deleteJournalEntries($transaction);

            $userAcc = Account::where('type', 'user')->where('owner_type', User::class)->where('owner_id', $transaction->user_id)->first();
            $fundAcc = Account::where('type', 'fund')->first();

            $fromAccId = $userAcc ? $userAcc->id : $fundAcc->id;

            $affectedIds = [$fromAccId];
            foreach ($validated['splits'] as $item) {
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'from_account_id' => $fromAccId,
                    'to_account_id' => $item['to_account_id'],
                    'amount' => $item['amount'],
                    'memo' => $item['memo'] ?? ($transaction->type . ': ' . $transaction->description),
                ]);
                $affectedIds[] = $item['to_account_id'];
            }

            foreach (array_unique($affectedIds) as $accId) {
                $this->recalcAccountBalance($accId);
            }

            // ALWAYS sync fund balance as the very last step
            $this->syncFundBalance();
        });

        return redirect()->back()->with('success', 'Đã tách giao dịch #' . $transaction->id . ' thành công!');
    }

    private function syncFundBalance(): void
    {
        Fund::syncBalance();
    }

    /**
     * Only handles user debt tracking (loan/repayment).
     * Fund balance is NOT touched here - syncFundBalance handles that.
     */
    private function applyUserDebtImpact(User $user, string $type, float $amount, bool $isFundOnly = false): void
    {
        if ($isFundOnly) return;

        if ($type === 'repayment') {
            $user->decrement('current_debt', min($amount, $user->current_debt));
        } elseif ($type === 'loan') {
            $user->increment('current_debt', $amount);
        }
    }

    /**
     * Only reverts user debt tracking (loan/repayment).
     * Fund balance is NOT touched here - syncFundBalance handles that.
     */
    private function revertUserDebtImpact(User $user, string $type, float $amount, bool $isFundOnly = false): void
    {
        if ($isFundOnly) return;

        if ($type === 'repayment') {
            $user->increment('current_debt', $amount);
        } elseif ($type === 'loan') {
            $user->decrement('current_debt', min($amount, $user->current_debt));
        }
    }

    /**
     * Create JournalEntry for a transaction (Double-Entry Bookkeeping).
     */
    private function createJournalEntry(Transaction $tx): void
    {
        $user = $tx->user ?? User::find($tx->user_id);
        $userAcc = Account::firstOrCreate(
            ['type' => 'user', 'owner_type' => User::class, 'owner_id' => $tx->user_id],
            ['name' => 'Ví ' . ($user ? $user->name : 'Thành viên #' . $tx->user_id), 'balance' => 0]
        );
        $fundAcc = Account::where('type', 'fund')->first();
        $externalAcc = Account::where('type', 'external')->first();

        if (!$userAcc || !$fundAcc || !$externalAcc) return;

        $projectAcc = $tx->project_id ? Account::firstOrCreate(
            ['type' => 'project', 'owner_type' => Project::class, 'owner_id' => $tx->project_id],
            ['name' => 'Dự án #' . $tx->project_id, 'balance' => 0]
        ) : null;

        $targetAcc = $projectAcc ?? $fundAcc;

        $fromAccId = null;
        $toAccId = null;

        if ($tx->is_fund_only) {
            // Direct flow between external and fund - does NOT touch member wallet
            if ($tx->type === 'expense') {
                $fromAccId = $targetAcc->id;
                $toAccId = $externalAcc->id;
            } else {
                $fromAccId = $externalAcc->id;
                $toAccId = $targetAcc->id;
            }
        } else {
            switch ($tx->type) {
                case 'contribution':
                case 'repayment':
                case 'profit':
                    $fromAccId = $userAcc->id;
                    $toAccId = $targetAcc->id;
                    break;
                case 'adjustment':
                    $fromAccId = $externalAcc->id;
                    $toAccId = $fundAcc->id;
                    break;
                case 'loan':
                case 'withdrawal':
                case 'distribution':
                    $fromAccId = $fundAcc->id;
                    $toAccId = $userAcc->id;
                    break;
                case 'expense':
                    $fromAccId = $targetAcc->id;
                    $toAccId = $userAcc->id;
                    break;
                default:
                    $fromAccId = $externalAcc->id;
                    $toAccId = $targetAcc->id;
            }
        }

        JournalEntry::create([
            'transaction_id' => $tx->id,
            'from_account_id' => $fromAccId,
            'to_account_id' => $toAccId,
            'amount' => $tx->amount,
            'memo' => $tx->type . ': ' . $tx->description,
        ]);

        // Update cached balances
        $this->recalcAccountBalance($fromAccId);
        $this->recalcAccountBalance($toAccId);
    }

    /**
     * Delete JournalEntries for a transaction and recalculate balances.
     */
    private function deleteJournalEntries(Transaction $tx): void
    {
        $entries = JournalEntry::where('transaction_id', $tx->id)->get();
        $affectedIds = [];
        foreach ($entries as $je) {
            $affectedIds[] = $je->from_account_id;
            $affectedIds[] = $je->to_account_id;
            $je->forceDelete();
        }
        foreach (array_unique($affectedIds) as $accId) {
            $this->recalcAccountBalance($accId);
        }
    }

    /**
     * Recalculate cached balance for a non-fund Account.
     * Fund balance is handled exclusively by syncFundBalance().
     */
    private function recalcAccountBalance(int $accountId): void
    {
        $acc = Account::find($accountId);
        if (!$acc) return;

        // Fund balance is managed solely by syncFundBalance() - skip here
        if ($acc->type === 'fund') return;

        $totalIn = JournalEntry::where('to_account_id', $acc->id)->sum('amount');
        $totalOut = JournalEntry::where('from_account_id', $acc->id)->sum('amount');

        if ($acc->type === 'user') {
            $acc->balance = $totalOut - $totalIn;
        } else {
            $acc->balance = $totalIn - $totalOut;
        }
        $acc->save();
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
