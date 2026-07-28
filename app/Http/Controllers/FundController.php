<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index()
    {
        $fund = Fund::firstOrCreate(
            ['id' => 1],
            ['name' => 'Trả nợ thuê Ltd', 'balance' => 7028106.00, 'total_profit' => 126160.00]
        );

        $members = User::orderBy('id')->get();
        $transactions = Transaction::with(['user', 'approver'])
            ->latest()
            ->take(20)
            ->get();

        $pendingTransactions = Transaction::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalShare = $members->sum('share_percentage');

        return view('dashboard', compact('fund', 'members', 'transactions', 'pendingTransactions', 'totalShare'));
    }
}
