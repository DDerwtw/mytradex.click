<?php

namespace App\Services\Binance;

use App\Models\BinanceDepositObservation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BinanceDepositHistory
{
    /** Fetch the Binance account's shared USDT/TRX deposit address (not a user-specific address). */
    public function depositAddress(): array
    {
        $response = $this->signedGet('/sapi/v1/capital/deposit/address', [
            'coin' => 'USDT',
            'network' => 'TRX',
        ]);

        if (! is_array($response) || empty($response['address'])) {
            throw new RuntimeException('Binance did not return a USDT TRC20 deposit address.');
        }

        return [
            'address' => (string) $response['address'],
            'tag' => isset($response['addressTag']) && (string) $response['addressTag'] !== '' ? (string) $response['addressTag'] : null,
        ];
    }

    /** Query Binance USDT/TRX deposit history and store observations only; never credits a balance. */
    public function syncRecent(): int
    {
        $response = $this->signedGet('/sapi/v1/capital/deposit/hisrec', [
            'coin' => 'USDT',
            'network' => 'TRX',
            'includeSource' => 'true',
            'startTime' => now()->subDays(30)->getTimestampMs(),
            'endTime' => now()->getTimestampMs(),
            'limit' => 1000,
        ]);

        if (! is_array($response) || ! array_is_list($response)) {
            throw new RuntimeException('Binance did not return a valid deposit-history response.');
        }

        $saved = 0;
        foreach ($response as $deposit) {
            if (! is_array($deposit) || empty($deposit['id']) || ($deposit['coin'] ?? null) !== 'USDT' || ($deposit['network'] ?? null) !== 'TRX') {
                continue;
            }

            $amount = (string) ($deposit['amount'] ?? '');
            if (! preg_match('/^\d{1,20}(?:\.\d{1,8})?$/', $amount) || (float) $amount <= 0) {
                continue;
            }

            BinanceDepositObservation::query()->updateOrCreate(
                ['binance_id' => (string) $deposit['id']],
                [
                    'coin' => 'USDT',
                    'network' => 'TRC20',
                    'amount' => $amount,
                    'provider_status' => isset($deposit['status']) ? (string) $deposit['status'] : null,
                    'transaction_id' => isset($deposit['txId']) ? (string) $deposit['txId'] : null,
                    'source_address' => isset($deposit['sourceAddress']) ? (string) $deposit['sourceAddress'] : null,
                    'occurred_at' => isset($deposit['insertTime']) ? now()->setTimestamp((int) floor(((int) $deposit['insertTime']) / 1000)) : null,
                    'last_seen_at' => now(),
                ],
            );
            $saved++;
        }

        return $saved;
    }

    private function signedGet(string $path, array $parameters): array
    {
        $apiKey = (string) config('services.binance.api_key');
        $secret = (string) config('services.binance.api_secret');
        if (! config('services.binance.deposit_sync_enabled') || $apiKey === '' || $secret === '') {
            throw new RuntimeException('Binance read-only wallet access is not enabled or credentials are missing.');
        }

        $parameters['recvWindow'] = 5000;
        $parameters['timestamp'] = now()->getTimestampMs();
        $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        $signature = hash_hmac('sha256', $query, $secret);
        $baseUrl = rtrim((string) config('services.binance.base_url'), '/');
        $response = Http::acceptJson()
            ->withHeaders(['X-MBX-APIKEY' => $apiKey])
            ->timeout(12)
            ->get($baseUrl.$path.'?'.$query.'&signature='.$signature);

        if (! $response->successful() || ! is_array($response->json())) {
            throw new RuntimeException('Binance did not return a valid read-only wallet response.');
        }

        return $response->json();
    }
}
