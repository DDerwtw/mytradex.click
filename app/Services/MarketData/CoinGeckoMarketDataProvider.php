<?php

namespace App\Services\MarketData;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CoinGeckoMarketDataProvider
{
    /**
     * @return array<int, array{symbol: string, name: string, current_price: float, price_change_percentage_24h: float|null, last_updated: string, sparkline: array<int, float>}>|null
     */
    public function markets(): ?array
    {
        $apiKey = config('services.coingecko.demo_api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $cached = Cache::remember('market-data.coingecko.markets.v2', now()->addMinute(), function () use ($apiKey): array {
            try {
                $baseUrl = rtrim((string) config('services.coingecko.base_url'), '/');
                $response = Http::connectTimeout(3)
                    ->timeout(8)
                    ->withHeaders(['x-cg-demo-api-key' => $apiKey])
                    ->get($baseUrl.'/coins/markets', [
                        'vs_currency' => 'usd',
                        'ids' => 'bitcoin,ethereum,solana',
                        'price_change_percentage' => '24h',
                        'sparkline' => 'true',
                        'precision' => 'full',
                    ])
                    ->throw();

                $payload = $response->json();

                if (! is_array($payload)) {
                    return ['markets' => null];
                }

                $markets = array_values(array_filter($payload, function (mixed $market): bool {
                    return is_array($market)
                        && is_string($market['id'] ?? null)
                        && is_string($market['symbol'] ?? null)
                        && is_string($market['name'] ?? null)
                        && is_numeric($market['current_price'] ?? null)
                        && is_string($market['last_updated'] ?? null);
                }));

                $markets = array_map(static fn (array $market): array => [
                    'symbol' => $market['symbol'],
                    'name' => $market['name'],
                    'current_price' => (float) $market['current_price'],
                    'price_change_percentage_24h' => is_numeric($market['price_change_percentage_24h'] ?? null)
                        ? (float) $market['price_change_percentage_24h']
                        : null,
                    'last_updated' => $market['last_updated'],
                    'sparkline' => self::sparkline($market['sparkline_in_7d']['price'] ?? []),
                ], $markets);

                return ['markets' => $markets === [] ? null : $markets];
            } catch (ConnectionException|RequestException) {
                return ['markets' => null];
            }
        });

        return $cached['markets'];
    }

    /**
     * @return array<int, float>
     */
    private static function sparkline(mixed $prices): array
    {
        if (! is_array($prices)) {
            return [];
        }

        $prices = array_values(array_filter($prices, static fn (mixed $price): bool => is_numeric($price) && (float) $price > 0));

        if (count($prices) < 2) {
            return [];
        }

        $sampleCount = min(48, count($prices));
        $lastIndex = count($prices) - 1;

        return array_map(static function (int $index) use ($prices, $lastIndex, $sampleCount): float {
            $sourceIndex = (int) round($index * $lastIndex / ($sampleCount - 1));

            return (float) $prices[$sourceIndex];
        }, range(0, $sampleCount - 1));
    }
}
