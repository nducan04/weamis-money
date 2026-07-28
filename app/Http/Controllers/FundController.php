<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $fund = Fund::firstOrCreate(
            ['id' => 1],
            ['name' => 'Quỹ Nhóm Weamis', 'balance' => 7028106.00, 'total_profit' => 126160.00]
        );

        $members = User::orderBy('id')->get();

        // 1. Stat Totals
        $approvedTxs = Transaction::where('status', 'approved')->get();
        $totalIncome = $approvedTxs->whereIn('type', ['contribution', 'repayment'])->sum('amount');
        $totalExpense = $approvedTxs->where('type', 'expense')->sum('amount');
        $totalLoans = $members->sum('current_debt');

        // 2. Member Statistics Breakdown
        $memberStats = $members->map(function ($m) use ($approvedTxs, $fund) {
            $userTxs = $approvedTxs->where('user_id', $m->id);
            $contributed = $userTxs->where('type', 'contribution')->sum('amount');
            $withdrawn = $userTxs->whereIn('type', ['expense', 'loan'])->sum('amount');
            $estimatedPayout = round(($fund->balance * $m->share_percentage) / 100, 0);

            return [
                'user' => $m,
                'contributed' => $contributed,
                'withdrawn' => $withdrawn,
                'debt' => $m->current_debt,
                'share' => $m->share_percentage,
                'estimated_payout' => $estimatedPayout,
            ];
        });

        // 3. Filtered Transaction Query
        $query = Transaction::with(['user', 'approver'])->latest();

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('member_id')) {
            $query->where('user_id', $request->member_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(15)->withQueryString();

        $pendingTransactions = Transaction::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalShare = $members->sum('share_percentage');

        return view('dashboard', compact(
            'fund',
            'members',
            'memberStats',
            'transactions',
            'pendingTransactions',
            'totalIncome',
            'totalExpense',
            'totalLoans',
            'totalShare'
        ));
    }
}
