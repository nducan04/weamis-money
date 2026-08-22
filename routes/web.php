<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AnalyticsController;


/*
|--------------------------------------------------------------------------
| Public Routes (Guest & View-Only Mode)
|--------------------------------------------------------------------------
*/
Route::get('/', [FundController::class, 'index'])->name('dashboard');
Route::get('/history', [TransactionController::class, 'history'])->name('history');
Route::get('/report', fn() => redirect()->route('dashboard'))->name('report');

// Projects Overview (Overview Cards for Guests)
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

// Analytics: Net Worth & Collaboration Network Graph
Route::get('/analytics/networth', [AnalyticsController::class, 'networth'])->name('analytics.networth');
Route::get('/analytics/network', fn() => redirect()->route('analytics.networth'))->name('analytics.network');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes (Requires Login - Member & Admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    // Projects Management & Audit (Details require login)
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::delete('/projects/{project}/share-period', [ProjectController::class, 'destroySharePeriod'])->name('projects.destroy-share-period');
    Route::put('/projects/{project}/share-period', [ProjectController::class, 'updateSharePeriodDate'])->name('projects.update-share-period');
    Route::post('/projects/{project}/attach-transactions', [ProjectController::class, 'attachTransactions'])->name('projects.attach-transactions');

    // Transaction CRUD
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/{transaction}/split', [TransactionController::class, 'split'])->name('transactions.split');

    // Transaction Approval
    Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

    // Distributions & Profit Sharing
    Route::post('/distributions', [DistributionController::class, 'store'])->name('distributions.store');

    // Admin Member Management
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::put('/members/{user}', [MemberController::class, 'update'])->name('members.update');
    Route::post('/members/{user}/reset-password', [MemberController::class, 'resetPassword'])->name('members.resetPassword');
    Route::delete('/members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::put('/members/{user}/share', [MemberController::class, 'updateShare'])->name('members.updateShare');
});
