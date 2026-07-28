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
        $user = User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Báo Cáo Thu Chi');
        $response->assertSee('7.028.106');
    }

    public function test_contribution_increases_fund_balance()
    {
        $fund = Fund::first();
        $initialBalance = $fund->balance;
        $user = User::first();

        $response = $this->actingAs($user)->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'contribution',
            'amount' => 500000,
            'description' => 'Test contribution',
        ]);

        $response->assertRedirect();
        $this->assertEquals($initialBalance + 500000, $fund->fresh()->balance);
    }

    public function test_update_transaction_recalculates_balance()
    {
        $fund = Fund::first();
        $user = User::first();

        // 1. Create a contribution of 1,000,000
        $this->actingAs($user)->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'contribution',
            'amount' => 1000000,
            'description' => 'Góp ban đầu',
        ]);

        $tx = Transaction::where('description', 'Góp ban đầu')->first();
        $balanceAfterAdd = $fund->fresh()->balance;

        // 2. Edit transaction amount to 1,500,000 (+500k diff)
        $this->actingAs($user)->put("/transactions/{$tx->id}", [
            'user_id' => $user->id,
            'type' => 'contribution',
            'amount' => 1500000,
            'description' => 'Góp sau khi sửa',
        ]);

        $this->assertEquals('Góp sau khi sửa', $tx->fresh()->description);
        $this->assertEquals($balanceAfterAdd + 500000, $fund->fresh()->balance);
    }

    public function test_delete_transaction_reverts_balance()
    {
        $fund = Fund::first();
        $user = User::first();

        $initialBalance = $fund->balance;

        $this->actingAs($user)->post('/transactions', [
            'user_id' => $user->id,
            'type' => 'contribution',
            'amount' => 800000,
            'description' => 'Góp để xóa',
        ]);

        $tx = Transaction::where('description', 'Góp để xóa')->first();
        $this->assertEquals($initialBalance + 800000, $fund->fresh()->balance);

        // Delete transaction
        $this->actingAs($user)->delete("/transactions/{$tx->id}");

        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
        $this->assertEquals($initialBalance, $fund->fresh()->balance);
    }

    public function test_percentage_distribution_calculator()
    {
        $fund = Fund::first();
        $user = User::first();
        $fund->update(['balance' => 10000000]); // 10m

        $response = $this->actingAs($user)->post('/distributions', [
            'total_amount' => 10000000,
            'note' => 'Chia lợi nhuận dự án',
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $fund->fresh()->balance);

        $dist = Distribution::first();
        $this->assertNotNull($dist);
        $this->assertEquals(10000000, $dist->total_amount);

        // Check payout: Administrator has 100% share = 10m
        $payouts = collect($dist->payout_details);
        $adminPayout = $payouts->firstWhere('name', 'Administrator');

        $this->assertEquals(10000000, $adminPayout['amount']);
    }
}
