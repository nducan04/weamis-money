<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\MemberController;

/*
|--------------------------------------------------------------------------
| Public Application Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::get('/', [FundController::class, 'index'])->name('dashboard');

// Transaction CRUD
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

// Transaction Approval
Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

// Distributions & Profit Sharing
Route::post('/distributions', [DistributionController::class, 'store'])->name('distributions.store');

// Members Management
Route::post('/members', [MemberController::class, 'store'])->name('members.store');
Route::put('/members/{user}/share', [MemberController::class, 'updateShare'])->name('members.updateShare');
