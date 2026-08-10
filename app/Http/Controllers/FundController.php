<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $fund = Fund::first();

        $members = User::where('role', '!=', 'admin')->orderBy('id')->get();
        $projects = Project::all();

        // 1. Stat Totals (Refactored for Double Entry)
        $fundAcc = \App\Models\Account::where('type', 'fund')->first();
        $totalIncome = 0;
        $totalExpense = 0;
        if ($fundAcc) {
            $totalIncome = \App\Models\JournalEntry::where('to_account_id', $fundAcc->id)->whereHas('transaction', function($q) { $q->where('status', 'approved'); })->sum('amount');
            $totalExpense = \App\Models\JournalEntry::where('from_account_id', $fundAcc->id)->whereHas('transaction', function($q) { $q->where('status', 'approved'); })->sum('amount');
        }
        $totalLoans = Transaction::where('status', 'approved')->where('type', 'loan')->sum('amount') - Transaction::where('status', 'approved')->where('type', 'repayment')->sum('amount');

        // 2. Member Statistics Breakdown
        $memberStats = $members->map(function ($m) use ($fund) {
            $userAcc = \App\Models\Account::where('type', 'user')->where('owner_id', $m->id)->first();
            $contributed = 0;
            $withdrawn = 0;
            if ($userAcc) {
                // contributed = Out from User
                $contributed = \App\Models\JournalEntry::where('from_account_id', $userAcc->id)->whereHas('transaction', function($q) { $q->where('status', 'approved'); })->sum('amount');
                // withdrawn = In to User
                $withdrawn = \App\Models\JournalEntry::where('to_account_id', $userAcc->id)->whereHas('transaction', function($q) { $q->where('status', 'approved'); })->sum('amount');
            }
            
            $userTxs = Transaction::where('status', 'approved')->where('user_id', $m->id);
            $loans = (clone $userTxs)->where('type', 'loan')->sum('amount');
            $repaid = (clone $userTxs)->where('type', 'repayment')->sum('amount');
            $remainingDebt = max(0, $loans - $repaid);
            $estimatedPayout = round(($fund->balance * $m->share_percentage) / 100, 0);

            return [
                'user' => $m,
                'id' => $m->id,
                'name' => $m->name,
                'avatar' => $m->avatar,
                'share_percentage' => $m->share_percentage,
                'contributions' => $contributed,
                'contributed' => $contributed,
                'loans' => $loans,
                'repaid' => $repaid,
                'withdrawn' => $withdrawn,
                'debt' => $remainingDebt,
                'share' => $m->share_percentage,
                'estimated_payout' => $estimatedPayout,
                'estimated_share_amount' => $estimatedPayout,
            ];
        });


        $allTransactions = Transaction::with(['user', 'approver'])->latest()->get()->map(function($tx) {
            return [
                'id' => $tx->id,
                'user_id' => $tx->user_id,
                'user_name' => $tx->user ? $tx->user->name : 'N/A',
                'user_avatar' => $tx->user ? $tx->user->avatar : null,
                'type' => $tx->type,
                'amount' => (float)$tx->amount,
                'description' => $tx->description,
                'status' => $tx->status,
                'created_at' => $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '',
                'created_at_formatted' => $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : '',
            ];
        });

        $pendingTransactions = Transaction::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalShare = $members->sum('share_percentage');

        return view('dashboard', compact(
            'fund',
            'members',
            'projects',
            'memberStats',
            'allTransactions',
            'pendingTransactions',
            'totalIncome',
            'totalExpense',
            'totalLoans',
            'totalShare'
        ));
    }
}
