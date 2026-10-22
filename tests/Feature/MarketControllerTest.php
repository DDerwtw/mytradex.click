<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketControllerTest extends TestCase
{
    public function test_public_market_page_displays_provider_prices(): void
    {
        config(['services.coingecko.demo_api_key' => 'test-demo-key']);
        Http::preventStrayRequests();
        Http::fake([
            'api.coingecko.com/api/v3/coins/markets*' => Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'current_price' => 12345.67,
                    'price_change_percentage_24h' => 1.25,
                    'last_updated' => '2026-10-22T02:00:00.000Z',
                ],
            ]),
        ]);

        $this->get(route('markets'))
            ->assertOk()
            ->assertSee('Bitcoin')
            ->assertSee('$12,345.67')
            ->assertSee('1.25%')
            ->assertSee('CoinGecko')
            ->assertDontSee('test-demo-key');

        Http::assertSent(function (Request $request): bool {
            $query = [];
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->hasHeader('x-cg-demo-api-key', 'test-demo-key')
                && ($query['vs_currency'] ?? null) === 'usd'
                && ($query['ids'] ?? null) === 'bitcoin,ethereum,solana';
        });
    }

    public function test_public_market_page_shows_unavailable_without_api_key(): void
    {
        config(['services.coingecko.demo_api_key' => null]);
        Http::preventStrayRequests();

        $this->get(route('markets'))
            ->assertOk()
            ->assertSee('Market data is unavailable right now.')
            ->assertDontSee('Bitcoin');

        Http::assertNothingSent();
    }

    public function test_public_market_page_shows_unavailable_when_provider_rejects_request(): void
    {
        config(['services.coingecko.demo_api_key' => 'test-demo-key']);
        Http::preventStrayRequests();
        Http::fake([
            'api.coingecko.com/api/v3/coins/markets*' => Http::response(['error' => 'Rate limit exceeded'], 429),
        ]);

        $this->get(route('markets'))
            ->assertOk()
            ->assertSee('Market data is unavailable right now.')
            ->assertDontSee('Bitcoin');

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/coins/markets'));
    }
}
