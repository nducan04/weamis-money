<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment',
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'created_at' => 'nullable|date',
        ]);

        $fund = Fund::firstOrFail();
        $user = User::findOrFail($validated['user_id']);

        DB::transaction(function () use ($fund, $user, $validated) {
            $status = in_array($validated['type'], ['contribution', 'repayment']) ? 'approved' : 'pending';
            $adminUser = User::where('role', 'admin')->first() ?? $user;

            $txData = [
                'fund_id' => $fund->id,
                'user_id' => $user->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'status' => $status,
                'approved_by' => $status === 'approved' ? $adminUser->id : null,
            ];

            if (!empty($validated['created_at'])) {
                $txData['created_at'] = $validated['created_at'];
            }

            Transaction::create($txData);

            if ($status === 'approved') {
                if ($validated['type'] === 'contribution') {
                    $fund->increment('balance', $validated['amount']);
                } elseif ($validated['type'] === 'repayment') {
                    $fund->increment('balance', $validated['amount']);
                    $user->decrement('current_debt', min($validated['amount'], $user->current_debt));
                }
            }
        });

        return redirect()->back()->with('success', 'Giao dịch đã được thêm thành công!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:contribution,expense,loan,repayment,distribution',
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'created_at' => 'nullable|date',
        ]);

        $fund = Fund::firstOrFail();

        DB::transaction(function () use ($transaction, $fund, $validated) {
            $oldUser = User::findOrFail($transaction->user_id);
            $newUser = User::findOrFail($validated['user_id']);

            // If transaction was approved, revert old impact first
            if ($transaction->status === 'approved') {
                $this->revertTransactionImpact($fund, $oldUser, $transaction->type, $transaction->amount);
            }

            // Update record
            $transaction->user_id = $newUser->id;
            $transaction->type = $validated['type'];
            $transaction->amount = $validated['amount'];
            $transaction->description = $validated['description'];
            if (!empty($validated['created_at'])) {
                $transaction->created_at = $validated['created_at'];
            }
            $transaction->save();

            // Apply new impact if approved
            if ($transaction->status === 'approved') {
                $this->applyTransactionImpact($fund, $newUser, $validated['type'], $validated['amount']);
            }
        });

        return redirect()->back()->with('success', 'Đã cập nhật giao dịch thành công!');
    }

    public function destroy(Transaction $transaction)
    {
        $fund = Fund::firstOrFail();
        $user = User::findOrFail($transaction->user_id);

        DB::transaction(function () use ($transaction, $fund, $user) {
            if ($transaction->status === 'approved') {
                $this->revertTransactionImpact($fund, $user, $transaction->type, $transaction->amount);
            }

            $transaction->delete();
        });

        return redirect()->back()->with('success', 'Đã xóa giao dịch và cập nhật lại số dư!');
    }

    public function approve(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        DB::transaction(function () use ($transaction) {
            $fund = $transaction->fund;
            $user = $transaction->user;
            $adminUser = User::where('role', 'admin')->first() ?? $user;

            $this->applyTransactionImpact($fund, $user, $transaction->type, $transaction->amount);

            $transaction->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
            ]);
        });

        return redirect()->back()->with('success', 'Đã phê duyệt giao dịch!');
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        $transaction->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
    }

    private function applyTransactionImpact(Fund $fund, User $user, string $type, float $amount): void
    {
        if ($type === 'contribution' || $type === 'repayment') {
            $fund->increment('balance', $amount);
            if ($type === 'repayment') {
                $user->decrement('current_debt', min($amount, $user->current_debt));
            }
        } elseif ($type === 'expense' || $type === 'loan' || $type === 'distribution') {
            $fund->decrement('balance', $amount);
            if ($type === 'loan') {
                $user->increment('current_debt', $amount);
            }
        }
    }

    private function revertTransactionImpact(Fund $fund, User $user, string $type, float $amount): void
    {
        if ($type === 'contribution' || $type === 'repayment') {
            $fund->decrement('balance', $amount);
            if ($type === 'repayment') {
                $user->increment('current_debt', $amount);
            }
        } elseif ($type === 'expense' || $type === 'loan' || $type === 'distribution') {
            $fund->increment('balance', $amount);
            if ($type === 'loan') {
                $user->decrement('current_debt', min($amount, $user->current_debt));
            }
        }
    }
}
