<?php

namespace App\Services\Ledger;

use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerService
{
    /**
     * Post a balanced, single-currency journal. Amounts are decimal strings with up to 8 places.
     * Each entry is ['account_id' => int, 'direction' => 'debit'|'credit', 'amount' => string].
     */
    public function post(string $idempotencyKey, string $currency, string $description, array $entries): LedgerTransaction
    {
        $currency = strtoupper(trim($currency));
        $description = trim($description);

        if (! preg_match('/^[A-Z0-9]{2,12}$/', $currency)) {
            throw new LedgerException('Currency code is invalid.');
        }

        if ($idempotencyKey === '' || strlen($idempotencyKey) > 128) {
            throw new LedgerException('An idempotency key of 1 to 128 characters is required.');
        }

        if ($description === '' || strlen($description) > 255) {
            throw new LedgerException('A description of 1 to 255 characters is required.');
        }

        $normalized = $this->normalizeEntries($entries);
        $debits = '0';
        $credits = '0';

        foreach ($normalized as $entry) {
            if ($entry['direction'] === 'debit') {
                $debits = $this->addUnits($debits, $entry['units']);
            } else {
                $credits = $this->addUnits($credits, $entry['units']);
            }
        }

        if (count($normalized) < 2 || $debits === '0' || $debits !== $credits) {
            throw new LedgerException('A journal must contain at least two entries and balance exactly.');
        }

        if (strlen($debits) > 28) {
            throw new LedgerException('The journal total exceeds the supported decimal precision.');
        }

        $payloadHash = hash('sha256', json_encode([
            'currency' => $currency,
            'description' => $description,
            'entries' => array_map(fn (array $entry): array => [
                'account_id' => $entry['account_id'],
                'direction' => $entry['direction'],
                'amount' => $entry['amount'],
            ], $normalized),
        ], JSON_THROW_ON_ERROR));

        try {
            return DB::transaction(function () use ($idempotencyKey, $currency, $description, $normalized, $payloadHash): LedgerTransaction {
                $existing = LedgerTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $this->existingOrFail($existing, $payloadHash);
                }

                $accountIds = collect($normalized)->pluck('account_id')->unique()->sort()->values();
                $accounts = LedgerAccount::query()->whereIn('id', $accountIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                if ($accounts->count() !== $accountIds->count()) {
                    throw new LedgerException('One or more ledger accounts do not exist.');
                }

                foreach ($accounts as $account) {
                    if (strtoupper($account->currency) !== $currency) {
                        throw new LedgerException('All accounts in a journal must use the same currency.');
                    }
                }

                $transaction = LedgerTransaction::create([
                    'reference' => (string) Str::ulid(),
                    'idempotency_key' => $idempotencyKey,
                    'payload_hash' => $payloadHash,
                    'currency' => $currency,
                    'description' => $description,
                    'posted_at' => now(),
                ]);

                foreach ($normalized as $entry) {
                    LedgerEntry::create([
                        'ledger_transaction_id' => $transaction->id,
                        'ledger_account_id' => $entry['account_id'],
                        'direction' => $entry['direction'],
                        'amount' => $entry['amount'],
                        'created_at' => now(),
                    ]);
                }

                return $transaction->load('entries');
            });
        } catch (QueryException $exception) {
            // A concurrent retry may win the unique idempotency key race.
            $existing = LedgerTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $this->existingOrFail($existing, $payloadHash);
            }

            throw $exception;
        }
    }

    private function existingOrFail(LedgerTransaction $existing, string $payloadHash): LedgerTransaction
    {
        if (! hash_equals($existing->payload_hash, $payloadHash)) {
            throw new LedgerException('The idempotency key was already used for a different journal.');
        }

        return $existing->load('entries');
    }

    private function normalizeEntries(array $entries): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            if (! is_array($entry) || ! isset($entry['account_id'], $entry['direction'], $entry['amount'])) {
                throw new LedgerException('Each entry requires an account, direction, and decimal amount.');
            }

            if (! is_int($entry['account_id']) || $entry['account_id'] < 1 || ! in_array($entry['direction'], ['debit', 'credit'], true)) {
                throw new LedgerException('A ledger entry contains an invalid account or direction.');
            }

            if (! is_string($entry['amount']) || ! preg_match('/^\d{1,20}(?:\.\d{1,8})?$/', $entry['amount'])) {
                throw new LedgerException('Amounts must be positive decimal strings with up to 8 decimal places.');
            }

            [$whole, $fraction] = array_pad(explode('.', $entry['amount'], 2), 2, '');
            $whole = ltrim($whole, '0') ?: '0';
            $fraction = str_pad($fraction, 8, '0');
            $units = ltrim($whole.$fraction, '0') ?: '0';

            if ($units === '0') {
                throw new LedgerException('Ledger amounts must be greater than zero.');
            }

            $normalized[] = [
                'account_id' => $entry['account_id'],
                'direction' => $entry['direction'],
                'amount' => $whole.'.'.$fraction,
                'units' => $units,
            ];
        }

        usort($normalized, fn (array $a, array $b): int => [$a['account_id'], $a['direction'], $a['amount']] <=> [$b['account_id'], $b['direction'], $b['amount']]);

        return $normalized;
    }

    private function addUnits(string $left, string $right): string
    {
        $left = strrev($left);
        $right = strrev($right);
        $length = max(strlen($left), strlen($right));
        $carry = 0;
        $sum = '';

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) ($left[$i] ?? '0') + (int) ($right[$i] ?? '0') + $carry;
            $sum .= (string) ($digit % 10);
            $carry = intdiv($digit, 10);
        }

        if ($carry > 0) {
            $sum .= (string) $carry;
        }

        return strrev($sum);
    }
}
