@extends('layouts.app')
@section('title', 'My Wallets')
@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading">
        <div>
            <div class="eyebrow">Financial Dashboard</div>
            <h1 class="page-title">My Wallets</h1>
            <p class="page-lede">View a detailed breakdown of your earnings and balances.</p>
        </div>
        <a class="button button-outline" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
        
        <!-- Referral Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #38bdf8;">
            <div class="eyebrow" style="color: #38bdf8;">Referral Income</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($referralBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
            <p class="dashboard-muted">Includes your direct and indirect referral commissions, plus any bonus adjustments.</p>
        </div>

        <!-- Maturity Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #4ade80;">
            <div class="eyebrow" style="color: #4ade80;">Maturity Income</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($maturityBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
            <p class="dashboard-muted">The principal and profits generated from your matured investment packages.</p>
        </div>

        <!-- Bonus Wallet (Can be merged with Referral) -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #eab308;">
            <div class="eyebrow" style="color: #eab308;">Bonus Income</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">0.00 <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
            <p class="dashboard-muted">Special bonuses added by the administration. (Currently visible in Referral Balance).</p>
        </div>

    </div>
</div></section>
@endsection