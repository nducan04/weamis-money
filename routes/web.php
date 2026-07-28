<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\MemberController;

Route::get('/', [FundController::class, 'index'])->name('dashboard');

Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

Route::post('/distributions', [DistributionController::class, 'store'])->name('distributions.store');

Route::post('/members', [MemberController::class, 'store'])->name('members.store');
Route::put('/members/{user}/share', [MemberController::class, 'updateShare'])->name('members.updateShare');
