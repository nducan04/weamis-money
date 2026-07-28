<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Fund;
use App\Models\Transaction;
use App\Models\Distribution;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FundManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dashboard_renders_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Trả nợ thuê Ltd');
        $response->assertSee('7.028.106');
    }

    public function test_contribution_increases_fund_balance()
    {
        $fund = Fund::first();
        $initialBalance = $fund->balance;
        $user = User::first();

        $response = $this->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'contribution',
            'amount' => 500000,
            'description' => 'Test contribution',
        ]);

        $response->assertRedirect();
        $this->assertEquals($initialBalance + 500000, $fund->fresh()->balance);
    }

    public function test_expense_and_approval_flow()
    {
        $fund = Fund::first();
        $initialBalance = $fund->balance;
        $user = User::where('role', 'member')->first();

        // 1. Submit expense request -> pending
        $this->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'expense',
            'amount' => 200000,
            'description' => 'Coffee for team',
        ]);

        $tx = Transaction::where('description', 'Coffee for team')->first();
        $this->assertEquals('pending', $tx->status);
        $this->assertEquals($initialBalance, $fund->fresh()->balance); // Not deducted yet

        // 2. Admin approves expense -> deducted
        $this->post("/transactions/{$tx->id}/approve");

        $this->assertEquals('approved', $tx->fresh()->status);
        $this->assertEquals($initialBalance - 200000, $fund->fresh()->balance);
    }

    public function test_loan_and_repayment_flow()
    {
        $fund = Fund::first();
        $initialBalance = $fund->balance;
        $user = User::where('name', 'Nguyễn Trung Kiên')->first();
        $initialDebt = $user->current_debt;

        // 1. Submit loan request -> pending
        $this->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'loan',
            'amount' => 500000,
            'description' => 'Vay tạm nộp tiền nhà',
        ]);

        $tx = Transaction::where('description', 'Vay tạm nộp tiền nhà')->first();
        $this->post("/transactions/{$tx->id}/approve");

        // Fund decreased by 500k, User debt increased by 500k
        $this->assertEquals($initialBalance - 500000, $fund->fresh()->balance);
        $this->assertEquals($initialDebt + 500000, $user->fresh()->current_debt);

        // 2. Repayment -> immediate approved
        $this->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'repayment',
            'amount' => 500000,
            'description' => 'Trả tiền nhà đã vay',
        ]);

        // Fund restored, User debt reduced back to initial
        $this->assertEquals($initialBalance, $fund->fresh()->balance);
        $this->assertEquals($initialDebt, $user->fresh()->current_debt);
    }

    public function test_percentage_distribution_calculator()
    {
        $fund = Fund::first();
        $fund->update(['balance' => 10000000]); // 10m

        $response = $this->post('/distributions', [
            'total_amount' => 10000000,
            'note' => 'Chia lợi nhuận dự án',
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $fund->fresh()->balance);

        $dist = Distribution::first();
        $this->assertNotNull($dist);
        $this->assertEquals(10000000, $dist->total_amount);

        // Check payouts: Việt (40% = 4m), Kiên (30% = 3m), Đức (30% = 3m)
        $payouts = collect($dist->payout_details);
        $vietPayout = $payouts->firstWhere('name', 'Nguyễn Hoàng Việt');
        $kienPayout = $payouts->firstWhere('name', 'Nguyễn Trung Kiên');
        $ducPayout = $payouts->firstWhere('name', 'Nguyễn Quý Đức');

        $this->assertEquals(4000000, $vietPayout['amount']);
        $this->assertEquals(3000000, $kienPayout['amount']);
        $this->assertEquals(3000000, $ducPayout['amount']);
    }
}
