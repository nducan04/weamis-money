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
        ]);

        $fund = Fund::firstOrFail();
        $user = User::findOrFail($validated['user_id']);

        DB::transaction(function () use ($fund, $user, $validated) {
            $status = in_array($validated['type'], ['contribution', 'repayment']) ? 'approved' : 'pending';
            $adminUser = User::where('role', 'admin')->first() ?? $user;

            $transaction = Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $user->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'status' => $status,
                'approved_by' => $status === 'approved' ? $adminUser->id : null,
            ]);

            // If immediate approval for contribution/repayment, update balance
            if ($status === 'approved') {
                if ($validated['type'] === 'contribution') {
                    $fund->increment('balance', $validated['amount']);
                } elseif ($validated['type'] === 'repayment') {
                    $fund->increment('balance', $validated['amount']);
                    $user->decrement('current_debt', min($validated['amount'], $user->current_debt));
                }
            }
        });

        return redirect()->back()->with('success', 'Giao dịch đã được ghi nhận thành công!');
    }

    public function approve(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý từ trước.');
        }

        DB::transaction(function () use ($transaction) {
            $fund = $transaction->fund;
            $user = $transaction->user;
            $adminUser = User::where('role', 'admin')->first() ?? $user;

            if ($transaction->type === 'expense') {
                $fund->decrement('balance', $transaction->amount);
            } elseif ($transaction->type === 'loan') {
                $fund->decrement('balance', $transaction->amount);
                $user->increment('current_debt', $transaction->amount);
            }

            $transaction->update([
                'status' => 'approved',
                'approved_by' => $adminUser->id,
            ]);
        });

        return redirect()->back()->with('success', 'Đã phê duyệt giao dịch thành công!');
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch này đã được xử lý từ trước.');
        }

        $transaction->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
    }
}
