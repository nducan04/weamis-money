<?php

namespace Tests\Unit\Audit;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Audit\ReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_is_clean_when_balances_match()
    {
        $acc1 = Account::factory()->create(['balance' => 1000]);
        $acc2 = Account::factory()->create(['balance' => -1000]);

        JournalEntry::create([
            'from_account_id' => $acc2->id,
            'to_account_id' => $acc1->id,
            'amount' => 1000,
            'memo' => 'Test Tx',
        ]);

        $service = new ReconciliationService();
        $result = $service->reconcile();

        $this->assertTrue($result['is_clean']);
        $this->assertEmpty($result['drifts']);
    }

    public function test_reconciliation_detects_drifts()
    {
        Log::shouldReceive('warning')->once(); // It should log the drift

        $acc1 = Account::factory()->create(['balance' => 500, 'name' => 'Acc 1']);
        $acc2 = Account::factory()->create(['balance' => 0, 'name' => 'Acc 2']);

        // Only 200 transferred, but acc1 has 500 balance -> drift of 300
        JournalEntry::create([
            'from_account_id' => $acc2->id,
            'to_account_id' => $acc1->id,
            'amount' => 200,
            'memo' => 'Test Tx',
        ]);

        $service = new ReconciliationService();
        $result = $service->reconcile();

        $this->assertFalse($result['is_clean']);
        $this->assertCount(2, $result['drifts']);

        $drift1 = collect($result['drifts'])->firstWhere('account_id', $acc1->id);
        $this->assertEquals(200, $drift1['computed']);
        $this->assertEquals(500, $drift1['stored']);
        $this->assertEquals(-300, $drift1['diff']);
    }

    public function test_reconciliation_fixes_drifts_when_flag_is_true()
    {
        $acc1 = Account::factory()->create(['balance' => 500]);
        $acc2 = Account::factory()->create(['balance' => 0]);

        JournalEntry::create([
            'from_account_id' => $acc2->id,
            'to_account_id' => $acc1->id,
            'amount' => 200,
            'memo' => 'Test Tx',
        ]);

        $service = new ReconciliationService();
        $result = $service->reconcile(true);

        $this->assertFalse($result['is_clean']); // It was dirty before fix
        
        $acc1->refresh();
        $acc2->refresh();

        $this->assertEquals(200, $acc1->balance);
        $this->assertEquals(-200, $acc2->balance);
    }
}
