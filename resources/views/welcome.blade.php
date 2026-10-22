@extends('layouts.app')

@section('title', 'MyTradeX · Explore digital assets')

@section('content')
@php
    $featuredMarket = $markets[0] ?? null;
    $chartValues = $featuredMarket['sparkline'] ?? [];
    $chartPoints = [];
    $chartArea = '';

    if (count($chartValues) > 1) {
        $chartMin = min($chartValues);
        $chartRange = max($chartValues) - $chartMin;
        $chartRange = $chartRange > 0 ? $chartRange : 1;

        foreach ($chartValues as $index => $value) {
            $x = round($index * 420 / (count($chartValues) - 1), 1);
            $y = round(106 - (($value - $chartMin) / $chartRange * 86), 1);
            $chartPoints[] = $x.','.$y;
        }

        $chartArea = 'M 0,124 L '.implode(' L ', $chartPoints).' L 420,124 Z';
    }
@endphp

<div class="mockup-home">
    <section class="mockup-top" id="about">
        <div class="mockup-hero-copy">
            <div class="mockup-eyebrow"><span></span>A clearer way to explore digital assets</div>
            <h1>Explore digital assets.<br><em>Move with clarity.</em></h1>
            <p>MyTradeX brings market context and useful account tools together, with clear information about what is available and what is still being built.</p>
            <div class="mockup-trust-row"><span><b>⬡</b> Clear and transparent</span><span><b>₮</b> USDT transfer preview</span><span><b>◉</b> Account controls</span></div>
            <div class="mockup-hero-links"><a class="mockup-blue-button" href="{{ route('markets') }}">Explore markets <span>→</span></a><a href="{{ route('register') }}">Create your account</a></div>
        </div>

        <div class="mockup-visual" aria-hidden="true">
            <div class="mockup-visual-glow"></div>
            <div class="mockup-visual-grid"></div>
            <div class="mockup-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <div class="mockup-coin coin-btc">₿</div><div class="mockup-coin coin-eth">◇</div>
            <div class="mockup-orbit"></div><div class="mockup-platform"></div>
            <div class="mockup-usdt-coin"><span>₮</span></div>
            <div class="mockup-arrow">↗</div>
        </div>

        <aside class="mockup-side-stack">
            <section class="mockup-side-card why-card">
                <h2>Why choose MyTradeX?</h2>
                <div class="why-item"><span class="why-icon green">▥</span><div><strong>Useful market context</strong><p>Prices from CoinGecko. Informational only.</p></div></div>
                <div class="why-item"><span class="why-icon blue">◇</span><div><strong>Account tools</strong><p>Email verification and private account access.</p></div></div>
                <div class="why-item"><span class="why-icon purple">⬡</span><div><strong>Identity review</strong><p>Private document storage with administrator review.</p></div></div>
                <div class="why-item"><span class="why-icon orange">◎</span><div><strong>Referral tracking</strong><p>Configured commissions accrue only after a referred deposit is approved.</p></div></div>
            </section>
            <section class="mockup-side-card market-mini-card">
                <div class="mockup-card-heading"><h2>Market overview</h2><span class="mockup-live-badge {{ $markets ? 'is-live' : '' }}">{{ $markets ? '● LIVE DATA' : 'UNAVAILABLE' }}</span></div>
                @if ($markets)
                    <div class="mockup-market-head"><span>Asset</span><span>Price (USD)</span><span>24h</span></div>
                    @foreach ($markets as $market)
                        <div class="mockup-market-row"><span><i class="asset-dot asset-{{ strtolower($market['symbol']) }}">{{ strtoupper(substr($market['symbol'], 0, 1)) }}</i>{{ $market['name'] }}</span><strong>${{ number_format($market['current_price'], 2) }}</strong><b class="{{ ($market['price_change_percentage_24h'] ?? 0) < 0 ? 'negative' : 'positive' }}">{{ is_numeric($market['price_change_percentage_24h'] ?? null) ? number_format($market['price_change_percentage_24h'], 2).'%' : '—' }}</b></div>
                    @endforeach
                    <a class="mockup-table-link" href="{{ route('markets') }}">Open full market view →</a>
                @else
                    <div class="mockup-market-empty">No market prices are available right now.</div>
                @endif
            </section>
        </aside>
    </section>

    <section class="mockup-platform-section" id="packages">
        <div class="mockup-platform-heading"><div><h2>Explore the platform</h2><p>Available account and market features, with their current status.</p></div><span class="mockup-outline-label">FEATURE OVERVIEW</span></div>
        <div class="mockup-feature-grid">
            <a class="mockup-feature-card tone-teal" href="{{ route('markets') }}"><span class="feature-gem">◈</span><h3>Markets</h3><strong>Live prices</strong><small>CoinGecko data</small><span class="feature-link">View markets →</span></a>
            <a class="mockup-feature-card tone-coral" href="{{ route('register') }}"><span class="feature-gem">◉</span><h3>Starter</h3><strong>Account access</strong><small>Email verification</small><span class="feature-link">Create account →</span></a>
            <a class="mockup-feature-card tone-blue" href="{{ route('login') }}"><span class="feature-gem">✧</span><h3>Profile</h3><strong>Private account</strong><small>Personal dashboard</small><span class="feature-link">Sign in →</span></a>
            <a class="mockup-feature-card tone-gold" href="{{ route('login') }}"><span class="feature-gem">⬡</span><h3>Verification</h3><strong>Identity review</strong><small>Secure document flow</small><span class="feature-link">Account required</span></a>
            <a class="mockup-feature-card tone-violet" href="{{ route('home') }}#referrals"><span class="feature-gem">◇</span><h3>Referrals</h3><strong>Invite links</strong><small>Deposit-based commissions</small><span class="feature-link">Learn more →</span></a>
            <a class="mockup-feature-card tone-sky" href="{{ route('legal.risk') }}"><span class="feature-gem">◉</span><h3>Risk guide</h3><strong>Read before use</strong><small>Digital assets are volatile</small><span class="feature-link">Read disclosure →</span></a>
            <a class="mockup-feature-card tone-aqua" href="{{ route('home') }}#how-it-works"><span class="feature-gem">▤</span><h3>Transparency</h3><strong>Clear feature status</strong><small>No invented account figures</small><span class="feature-link">How it works →</span></a>
            <a class="mockup-feature-card tone-purple" href="{{ route('legal.terms') }}"><span class="feature-gem">▧</span><h3>Terms</h3><strong>Platform information</strong><small>Read the current terms</small><span class="feature-link">View terms →</span></a>
            <a class="mockup-feature-card tone-amber" href="{{ route('home') }}#faq"><span class="feature-gem">?</span><h3>Help</h3><strong>Common questions</strong><small>Feature availability</small><span class="feature-link">See answers →</span></a>
        </div>
        <div class="mockup-info-strip"><div><i>◈</i><span><strong>Market information</strong><small>Prices may be delayed and are not a trading feature.</small></span></div><div><i>⬡</i><span><strong>Account security</strong><small>Verified access and private document storage.</small></span></div><div><i>▣</i><span><strong>USDT · TRC20</strong><small>Transfer preview only. No funds are handled.</small></span></div><div><i>!</i><span><strong>Important</strong><small>No returns, balances, or rewards are promised.</small></span></div></div>
    </section>

    <section class="mockup-bottom-layout" id="how-it-works">
        <div class="mockup-dashboard-preview">
            <div class="mockup-bottom-title"><h2>MyTradeX account preview</h2><a href="{{ route('login') }}">Open account →</a></div>
            <div class="mockup-dashboard-inner">
                <aside class="mockup-dash-nav"><strong><span>▥</span> mytradex.click</strong><a class="active" href="{{ route('login') }}">▦ &nbsp; Dashboard</a><a href="{{ route('markets') }}">◈ &nbsp; Market data</a><a href="{{ route('login') }}">◉ &nbsp; Verification</a><a href="{{ route('login') }}">↗ &nbsp; Referral links</a><a href="{{ route('login') }}">◇ &nbsp; USDT transfer preview</a><a href="{{ route('legal.risk') }}">⚙ &nbsp; Profile &amp; security</a></aside>
                <div class="mockup-dash-content">
                    <div class="mockup-dash-top"><strong>Account overview</strong><span>Private account · Sign in required</span></div>
                    <div class="mockup-metrics"><article><i class="tone-blue">◈</i><small>Market feed</small><strong>{{ $markets ? 'Available' : 'Unavailable' }}</strong></article><article><i class="tone-teal">◎</i><small>Account access</small><strong>Sign in</strong></article><article><i class="tone-purple">◇</i><small>Referral commissions</small><strong>Deposit based</strong></article><article><i class="tone-gold">▣</i><small>Wallet balances</small><strong>Unavailable</strong></article></div>
                    <div class="mockup-dash-panels"><section><div class="mockup-dash-panel-title"><strong>Market trend</strong><a href="{{ route('markets') }}">View markets →</a></div>
                        @if (count($chartValues) > 1)
                            <svg class="mockup-dashboard-chart" viewBox="0 0 420 132" role="img" aria-label="{{ $featuredMarket['name'] }} seven-day price trend"><defs><linearGradient id="dashboard-chart-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#0d9cff" stop-opacity=".3"/><stop offset="100%" stop-color="#0d9cff" stop-opacity="0"/></linearGradient></defs><path d="{{ $chartArea }}" fill="url(#dashboard-chart-fill)"/><polyline points="{{ implode(' ', $chartPoints) }}" fill="none" stroke="#12c9f4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @else
                            <div class="mockup-chart-empty">Market chart will appear when data is available.</div>
                        @endif
                    </section><section><div class="mockup-dash-panel-title"><strong>Account setup</strong><span class="mockup-outline-label">IN PROGRESS</span></div><div class="mockup-progress"><i>01</i><span><strong>Create account</strong><small>Email verification required</small></span></div><div class="mockup-progress"><i>02</i><span><strong>Verify identity</strong><small>Private review process</small></span></div><div class="mockup-progress"><i>03</i><span><strong>Explore the platform</strong><small>Financial services disabled</small></span></div></section></div>
                </div>
            </div>
        </div>

        <aside class="mockup-bottom-aside" id="referrals">
            <section class="mockup-side-card referral-structure"><h2>Referral programme</h2><div class="mockup-level"><i>1</i><strong>Direct referrals</strong><span>10%</span></div><div class="mockup-level"><i>2</i><strong>Direct invitees’ referrals</strong><span>5%</span></div><div class="mockup-level"><i>3</i><strong>Third level</strong><span>3%</span></div><div class="mockup-level"><i>4</i><strong>Fourth level</strong><span>2%</span></div><div class="mockup-level"><i>5</i><strong>Fifth level</strong><span>1%</span></div><div class="mockup-safe-note">Commission is calculated from approved deposits. Referral payouts are reviewed manually; no automatic transfer is made.</div><a class="mockup-table-link" href="{{ route('login') }}">Sign in to view referrals →</a></section>
            <section class="mockup-side-card wallet-overview"><h2>Wallet overview</h2><div class="mockup-wallet-grid"><div><i class="tone-blue">▣</i><span>Account balance<strong>Not available</strong></span></div><div><i class="tone-teal">↗</i><span>Market data<strong>{{ $markets ? 'Available' : 'Unavailable' }}</strong></span></div><div><i class="tone-purple">◎</i><span>Referral income<strong>Disabled</strong></span></div><div><i class="tone-gold">⇄</i><span>USDT transfer<strong>Preview only</strong></span></div></div></section>
            <section class="mockup-cta-card"><div class="mockup-cta-icon">⬡</div><h2>Explore with clarity.</h2><p>Understand the features and risks before creating an account.</p><a href="{{ route('register') }}">Create account <span>→</span></a></section>
        </aside>
    </section>

    <section class="mockup-faq-row" id="faq"><div><strong>Does MyTradeX place trades?</strong><span>No. Market information is for reference only.</span></div><div><strong>Can I deposit or withdraw USDT?</strong><span>Deposits are verified manually. Withdrawals require administrator review; this app does not send transfers automatically.</span></div><div><strong>Are referral commissions available?</strong><span>Configured commissions accrue after an administrator approves a referred member’s deposit. Withdrawals are reviewed manually.</span></div></section>
    <footer class="mockup-footer"><a href="{{ route('home') }}"><strong>mytradex.click</strong></a><span>© {{ date('Y') }} MyTradeX. Digital assets involve risk.</span><nav><a href="{{ route('legal.terms') }}">Terms</a><a href="{{ route('legal.risk') }}">Risk disclosure</a></nav></footer>
</div>
@endsection
