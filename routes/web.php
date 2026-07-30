<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Public Application Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::get('/', [FundController::class, 'index'])->name('dashboard');
Route::get('/history', [TransactionController::class, 'history'])->name('history');
Route::get('/report', [TransactionController::class, 'report'])->name('report');

// Projects Management & Audit
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

// Analytics: Net Worth & Collaboration Network Graph
Route::get('/analytics/networth', [AnalyticsController::class, 'networth'])->name('analytics.networth');

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
