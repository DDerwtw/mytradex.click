<?php

namespace App\Services\Tron;

use App\Models\Trc20DepositObservation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TronDepositHistory
{
    public function syncRecent(): int
    {
        $address = (string) config('services.tron.deposit_address');
        $apiKey = (string) config('services.tron.api_key');
        $contract = (string) config('services.tron.usdt_contract');
        if (! config('services.tron.deposit_sync_enabled') || $address === '' || $apiKey === '' || $contract === '') {
            throw new RuntimeException('TRON deposit lookup is disabled or missing configuration.');
        }

        $baseUrl = rtrim((string) config('services.tron.base_url'), '/');
        $url = $baseUrl.'/v1/accounts/'.rawurlencode($address).'/transactions/trc20';
        $parameters = [
            'only_confirmed' => 'true',
            'only_to' => 'true',
            'limit' => 200,
            'contract_address' => $contract,
            'order_by' => 'block_timestamp,desc',
        ];
        $transfers = [];
        for ($page = 0; $page < 10; $page++) {
            $response = Http::acceptJson()
                ->withHeaders(['TRON-PRO-API-KEY' => $apiKey])
                ->timeout(20)
                ->get($url, $parameters);

            if (! $response->successful() || ! is_array($response->json('data'))) {
                throw new RuntimeException('TronGrid returned an invalid transaction-history response.');
            }

            $transfers = array_merge($transfers, $response->json('data'));
            $fingerprint = $response->json('meta.fingerprint');
            if (! is_string($fingerprint) || $fingerprint === '') {
                break;
            }
            $parameters['fingerprint'] = $fingerprint;
        }

        $saved = 0;
        foreach ($transfers as $transfer) {
            if (! is_array($transfer)
                || ! isset($transfer['transaction_id'], $transfer['token_info']['address'], $transfer['from'], $transfer['to'], $transfer['value'])
                || ! hash_equals($contract, (string) $transfer['token_info']['address'])
                || ! hash_equals($address, (string) $transfer['to'])
                || (int) ($transfer['token_info']['decimals'] ?? 0) !== 6
                || ! preg_match('/\A[0-9a-fA-F]{64}\z/', (string) $transfer['transaction_id'])
                || ! ctype_digit((string) $transfer['value'])) {
                continue;
            }

            $amount = $this->fromBaseUnits((string) $transfer['value'], 6);
            if ($amount === '0.00000000') {
                continue;
            }

            Trc20DepositObservation::query()->updateOrCreate(
                ['transaction_id' => strtolower((string) $transfer['transaction_id'])],
                [
                    'coin' => 'USDT',
                    'network' => 'TRC20',
                    'contract_address' => $contract,
                    'source_address' => (string) $transfer['from'],
                    'destination_address' => (string) $transfer['to'],
                    'amount' => $amount,
                    'provider_status' => 'confirmed',
                    'occurred_at' => isset($transfer['block_timestamp']) ? now()->setTimestamp((int) floor(((int) $transfer['block_timestamp']) / 1000)) : null,
                    'last_seen_at' => now(),
                ],
            );
            $saved++;
        }

        return $saved;
    }

    private function fromBaseUnits(string $value, int $decimals): string
    {
        $value = ltrim($value, '0') ?: '0';
        $value = str_pad($value, $decimals + 1, '0', STR_PAD_LEFT);
        $whole = substr($value, 0, -$decimals);
        $fraction = substr($value, -$decimals);

        return $whole.'.'.str_pad($fraction, 8, '0');
    }
}
