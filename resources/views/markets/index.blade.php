@extends('layouts.app')

@section('title', 'Market data')

@section('content')
<section class="page-section">
    <div class="container market-page">
        <div class="dashboard-heading">
            <div>
                <div class="eyebrow">PUBLIC MARKET DATA</div>
                <h1 class="page-title">Market overview</h1>
                <p class="page-lede">Indicative cryptocurrency prices in USD, supplied by CoinGecko.</p>
            </div>
            <span class="status-pill {{ $markets ? 'status-ready' : '' }}">{{ $markets ? 'LIVE DATA' : 'UNAVAILABLE' }}</span>
        </div>

        @if ($markets)
            <section class="dashboard-panel market-data-panel" aria-label="Cryptocurrency market prices">
                <div class="referral-table-wrap">
                    <table class="referral-table market-table">
                        <thead>
                            <tr><th>Asset</th><th>Price (USD)</th><th>24-hour change</th><th>7-day trend</th><th>Provider update</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($markets as $market)
                                @php
                                    $sparkline = $market['sparkline'] ?? [];
                                    $sparklinePoints = [];

                                    if (count($sparkline) > 1) {
                                        $sparklineMin = min($sparkline);
                                        $sparklineRange = max($sparkline) - $sparklineMin;
                                        $sparklineRange = $sparklineRange > 0 ? $sparklineRange : 1;

                                        foreach ($sparkline as $index => $price) {
                                            $x = round($index * 100 / (count($sparkline) - 1), 1);
                                            $y = round(27 - (($price - $sparklineMin) / $sparklineRange * 21), 1);
                                            $sparklinePoints[] = $x.','.$y;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $market['name'] }}</strong><span class="market-symbol">{{ strtoupper($market['symbol']) }}</span></td>
                                    <td>${{ number_format((float) $market['current_price'], 2) }}</td>
                                    <td>
                                        @if (is_numeric($market['price_change_percentage_24h'] ?? null))
                                            <span class="{{ $market['price_change_percentage_24h'] < 0 ? 'market-negative' : 'market-positive' }}">{{ number_format((float) $market['price_change_percentage_24h'], 2) }}%</span>
                                        @else
                                            <span class="market-muted">Unavailable</span>
                                        @endif
                                    </td>
                                    <td class="market-trend-cell">
                                        @if (count($sparklinePoints) > 1)
                                            <svg class="market-table-sparkline" viewBox="0 0 100 32" role="img" aria-label="{{ $market['name'] }} seven-day price trend"><polyline points="{{ implode(' ', $sparklinePoints) }}" fill="none" stroke="#23d9d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        @else
                                            <span class="market-muted">Unavailable</span>
                                        @endif
                                    </td>
                                    <td>{{ $market['last_updated'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="market-attribution">Market data powered by <a href="https://www.coingecko.com/" rel="noopener noreferrer" target="_blank">CoinGecko</a>. Prices may be delayed and are informational only.</p>
            </section>
        @else
            <section class="notice-card market-unavailable" role="status">
                <strong>Market data is unavailable right now.</strong>
                <p>No prices are shown until the provider is configured and returns valid data. Please try again later.</p>
            </section>
        @endif

        <section class="notice-card market-disclosure">
            <strong>Market data is not a trading feature.</strong>
            <p>This page does not place orders or provide investment advice. Digital asset prices are volatile; review the <a href="{{ route('legal.risk') }}">risk disclosure</a>.</p>
        </section>
    </div>
</section>
@endsection
