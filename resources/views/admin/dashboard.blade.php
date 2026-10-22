@extends('layouts.app')

@section('title', 'Administrator overview')

@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="eyebrow">Restricted area</div><h1 class="page-title">Administrator overview</h1><p class="page-lede">Privileged actions require an active, verified administrator account.</p>
    <div class="draft-banner"><strong>Deposit review:</strong> Claims can be checked against confirmed USDT TRC20 transfers on TRON. Only an administrator's verified approval posts the matching deposit to the ledger and activates that saved plan. Withdrawals are not sent.</div>
    <div class="dashboard-grid admin-stats">
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">♙</span></div><h2>Registered users</h2><p class="dashboard-metric">{{ number_format($usersCount) }}</p><div class="dashboard-detail"><span>Source</span><strong>Database</strong></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">✓</span></div><h2>Verified emails</h2><p class="dashboard-metric">{{ number_format($verifiedUsersCount) }}</p><div class="dashboard-detail"><span>Source</span><strong>Database</strong></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">▤</span><span class="status-pill">Review needed</span></div><h2>Pending KYC</h2><p class="dashboard-metric">{{ number_format($pendingKycCount) }}</p><div class="dashboard-detail"><a class="text-link" href="{{ route('admin.kyc.index') }}">Open review queue →</a></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">◇</span><span class="status-pill">Disabled</span></div><h2>Financial activity</h2><p class="dashboard-muted">Ledger foundation only; payments and user balances are not enabled.</p><div class="dashboard-detail"><span>Balances</span><strong>Not available</strong></div></article>
    </div>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">Referral programme</div><h2>Referral relationships</h2></div><a class="text-link" href="{{ route('admin.referrals.index') }}">Review relationships →</a></div><p class="dashboard-muted">{{ number_format($referralRelationshipsCount) }} relationships recorded. Commission rewards are disabled.</p></section>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">Investment and withdrawal reviews</div><h2>Saved plans</h2></div><a class="text-link" href="{{ route('admin.investments.index') }}">Open saved plans and {{ $pendingWithdrawalCount }} pending withdrawal reviews →</a></div><p class="dashboard-muted">Withdrawal review decisions are recorded for administration only; no funds are sent.</p></section>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">Accounting foundation</div><h2>USDT ledger report</h2></div><a class="text-link" href="{{ route('admin.ledger.index') }}">View journal entries →</a></div><p class="dashboard-muted">The ledger records deposits only after an administrator matches and approves a confirmed TRON transfer.</p></section>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">TRON · USDT TRC20</div><h2>Deposit review queue</h2></div><a class="text-link" href="{{ route('admin.deposits.index') }}">Review {{ $pendingDepositCount }} pending claims and deposit records →</a></div><p class="dashboard-muted">Deposit claims require a matching transaction hash, sender, exact amount, USDT contract, TRC20 network, and destination wallet.</p></section>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">User communications</div><h2>Dashboard messages</h2></div><a class="text-link" href="{{ route('admin.messages.index') }}">Write a user message or action note →</a></div><p class="dashboard-muted">Messages and action updates appear in the selected user's dashboard.</p></section>
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">Verification queue</div><h2>Recent applications</h2></div><a class="text-link" href="{{ route('admin.kyc.index') }}">View all →</a></div>
        @forelse ($latestSubmissions as $item)
            <a class="admin-list-row" href="{{ route('admin.kyc.show', $item) }}"><span><strong>{{ $item->user->name }}</strong><small>{{ $item->user->email }}</small></span><span class="status-pill">{{ ucfirst($item->status) }}</span></a>
        @empty
            <div class="empty-state"><span>✓</span><strong>No verification applications yet</strong><p>Submitted applications will appear here for authorized review.</p></div>
        @endforelse
    </section>
</div></section>
@endsection
