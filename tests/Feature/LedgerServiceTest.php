<?php

namespace Tests\Feature;

use App\Models\LedgerAccount;
use App\Services\Ledger\LedgerException;
use App\Services\Ledger\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balanced_journal_posts_exact_decimal_entries(): void
    {
        [$cash, $clearing] = $this->accounts();

        $transaction = app(LedgerService::class)->post('deposit-event-1', 'USD', 'Test journal', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '10.00000001'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '10.00000001'],
        ]);

        $this->assertSame('USD', $transaction->currency);
        $this->assertSame(2, $transaction->entries->count());
        $this->assertDatabaseHas('ledger_entries', ['ledger_transaction_id' => $transaction->id, 'amount' => '10.00000001']);
    }

    public function test_unbalanced_journal_is_rejected_without_writing_records(): void
    {
        [$cash, $clearing] = $this->accounts();

        try {
            app(LedgerService::class)->post('unbalanced-event', 'USD', 'Unbalanced journal', [
                ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '10.00'],
                ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '9.99'],
            ]);
            $this->fail('An unbalanced journal should not post.');
        } catch (LedgerException $exception) {
            $this->assertSame('A journal must contain at least two entries and balance exactly.', $exception->getMessage());
        }

        $this->assertDatabaseCount('ledger_transactions', 0);
        $this->assertDatabaseCount('ledger_entries', 0);
    }

    public function test_duplicate_idempotency_key_returns_the_original_transaction(): void
    {
        [$cash, $clearing] = $this->accounts();
        $entries = [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '2.50'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '2.50000000'],
        ];

        $service = app(LedgerService::class);
        $first = $service->post('same-event', 'USD', 'Idempotent journal', $entries);
        $second = $service->post('same-event', 'USD', 'Idempotent journal', $entries);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('ledger_transactions', 1);
        $this->assertDatabaseCount('ledger_entries', 2);
    }

    public function test_idempotency_key_cannot_be_reused_for_a_different_journal(): void
    {
        [$cash, $clearing] = $this->accounts();
        $service = app(LedgerService::class);
        $service->post('reused-event', 'USD', 'Original journal', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '2.00'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '2.00'],
        ]);

        $this->expectException(LedgerException::class);
        $service->post('reused-event', 'USD', 'Different journal', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '3.00'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '3.00'],
        ]);
    }

    public function test_journal_cannot_mix_currency_accounts(): void
    {
        [$cash, $clearing] = $this->accounts();
        $clearing->update(['currency' => 'BTC']);

        $this->expectException(LedgerException::class);
        app(LedgerService::class)->post('mixed-currency-event', 'USD', 'Mixed journal', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '1.00'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '1.00'],
        ]);
    }

    public function test_posted_entries_cannot_be_changed_or_deleted(): void
    {
        [$cash, $clearing] = $this->accounts();
        $transaction = app(LedgerService::class)->post('immutable-event', 'USD', 'Immutable journal', [
            ['account_id' => $cash->id, 'direction' => 'debit', 'amount' => '1.00'],
            ['account_id' => $clearing->id, 'direction' => 'credit', 'amount' => '1.00'],
        ]);

        $this->expectException(\LogicException::class);
        $transaction->entries->first()->delete();
    }

    /** @return array{LedgerAccount, LedgerAccount} */
    private function accounts(): array
    {
        return [
            LedgerAccount::create(['account_key' => 'test:cash:USD', 'currency' => 'USD', 'kind' => 'test']),
            LedgerAccount::create(['account_key' => 'test:clearing:USD', 'currency' => 'USD', 'kind' => 'test']),
        ];
    }
}
