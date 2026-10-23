@extends('layouts.app')
@section('title', 'My Wallets')
@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading">
        <div>
            <div class="eyebrow">Financial Dashboard</div>
            <h1 class="page-title">My Wallets</h1>
            <p class="page-lede">Manage your funds. Withdraw, or reinvest to grow your portfolio.</p>
        </div>
        <a class="button button-outline" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 30px;">
        
        <!-- Referral Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #38bdf8; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #38bdf8; font-size: 0.9rem; letter-spacing: 1px;">REFERRAL INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($referralBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Earned from direct and indirect referrals. Can be withdrawn or reinvested.</p>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="{{ route('account.referrals') }}" class="button button-primary" style="flex: 1; text-align: center; padding: 10px;">Withdraw</a>
                <button class="button button-outline" style="flex: 1; padding: 10px; opacity: 0.8;" onclick="alert('Reinvest feature coming soon!')">Reinvest</button>
            </div>
        </div>

        <!-- Maturity Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #4ade80; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #4ade80; font-size: 0.9rem; letter-spacing: 1px;">MATURITY INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($maturityBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Principal and profits from matured investments. Can be withdrawn or reinvested.</p>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="button button-primary" style="flex: 1; padding: 10px;" onclick="alert('Withdrawal request feature coming soon!')">Withdraw</button>
                <button class="button button-outline" style="flex: 1; padding: 10px; opacity: 0.8;" onclick="alert('Reinvest feature coming soon!')">Reinvest</button>
            </div>
        </div>

        <!-- Bonus Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #eab308; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #eab308; font-size: 0.9rem; letter-spacing: 1px;">BONUS INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($bonusBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Special bonuses. Cannot be withdrawn directly. Must be invested.</p>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <!-- Bonus Wallet එකට Withdraw එකක් නෑ, Invest විතරයි -->
                <button class="button button-primary" style="width: 100%; padding: 10px; background: #eab308; color: #000; font-weight: bold;" onclick="alert('Invest Bonus feature coming soon!')">Invest Bonus</button>
            </div>
        </div>

    </div>
</div></section>
@endsection