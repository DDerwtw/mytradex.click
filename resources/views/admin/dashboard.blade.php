@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading">
        <div>
            <div class="eyebrow">Admin Control Panel</div>
            <h1 class="page-title">Welcome, Administrator</h1>
            <p class="page-lede">Overview of the platform's activity and pending tasks.</p>
        </div>
        <a class="button button-outline" href="{{ route('dashboard') }}">View Site</a>
    </div>

    <!-- Stats Overview Row -->
    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 30px; margin-bottom: 30px;">
        <!-- Total Users -->
        <div class="dashboard-card" style="padding: 20px; border-left: 4px solid #38bdf8;">
            <div class="eyebrow" style="color: #38bdf8;">Total Users</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($usersCount) }}</h2>
            <div class="dashboard-detail"><span>Verified</span><strong>{{ number_format($verifiedUsersCount) }}</strong></div>
        </div>
        
        <!-- Pending KYC -->
        <div class="dashboard-card" style="padding: 20px; border-left: 4px solid #eab308;">
            <div class="eyebrow" style="color: #eab308;">Pending KYC</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($pendingKycCount) }}</h2>
            <div class="dashboard-detail"><span>Action Required</span><a href="{{ route('admin.kyc.index') }}" style="color: #eab308; text-decoration: underline;">Review Now →</a></div>
        </div>

        <!-- Pending Deposits -->
        <div class="dashboard-card" style="padding: 20px; border-left: 4px solid #a855f7;">
            <div class="eyebrow" style="color: #a855f7;">Pending Deposits</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($pendingDepositCount) }}</h2>
            <div class="dashboard-detail"><span>Action Required</span><a href="{{ route('admin.deposits.index') }}" style="color: #a855f7; text-decoration: underline;">Review Now →</a></div>
        </div>

        <!-- Pending Withdrawals -->
        <div class="dashboard-card" style="padding: 20px; border-left: 4px solid #4ade80;">
            <div class="eyebrow" style="color: #4ade80;">Pending Withdrawals</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">{{ number_format($pendingWithdrawalCount + $pendingMaturityCount) }}</h2>
            <div class="dashboard-detail"><span>Total (Referral + Maturity)</span></div>
        </div>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
        
        <!-- Left Column: Quick Actions -->
        <div>
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading"><div><div class="eyebrow">Management</div><h2>Quick Actions</h2></div></div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('admin.kyc.index') }}" class="mockup-quick-link" style="text-decoration: none;">
                        <i style="color: #eab308;">⬡</i>
                        <span><strong>KYC Verification</strong><small>Review user identities</small></span>
                        <b>→</b>
                    </a>
                    <a href="{{ route('admin.referrals.index') }}" class="mockup-quick-link" style="text-decoration: none;">
                        <i style="color: #38bdf8;">◎</i>
                        <span><strong>Referral Management</strong><small>Adjust balances & review withdrawals</small></span>
                        <b>→</b>
                    </a>
                    <a href="{{ route('admin.maturity-withdrawals.index') }}" class="mockup-quick-link" style="text-decoration: none;">
                        <i style="color: #4ade80;">◇</i>
                        <span><strong>Maturity Withdrawals</strong><small>Review & approve requests</small></span>
                        <b>→</b>
                    </a>
                    <a href="{{ route('admin.deposits.index') }}" class="mockup-quick-link" style="text-decoration: none;">
                        <i style="color: #a855f7;">◈</i>
                        <span><strong>Deposit Claims</strong><small>Verify TRC20 deposits</small></span>
                        <b>→</b>
                    </a>
                    <a href="{{ route('admin.ledger.index') }}" class="mockup-quick-link" style="text-decoration: none;">
                        <i style="color: #94a3b8;">▤</i>
                        <span><strong>Financial Ledger</strong><small>View all transactions</small></span>
                        <b>→</b>
                    </a>
                </div>
            </section>
        </div>

        <!-- Right Column: Recent Activity -->
        <div>
            <section class="dashboard-panel">
                <div class="dashboard-panel-heading"><div><div class="eyebrow">Recent Activity</div><h2>Latest KYC Submissions</h2></div></div>
                @if ($latestSubmissions->isEmpty())
                    <div class="empty-state"><strong>No recent submissions</strong><p>New KYC submissions will appear here.</p></div>
                @else
                    <div class="referral-table-wrap">
                        <table class="referral-table">
                            <thead><tr><th>User</th><th>Submitted</th><th>Action</th></tr></thead>
                            <tbody>
                                @foreach ($latestSubmissions as $submission)
                                    <tr>
                                        <td>{{ $submission->user->name }}<br><small>{{ $submission->user->email }}</small></td>
                                        <td>{{ $submission->submitted_at->diffForHumans() }}</td>
                                        <td><a href="{{ route('admin.kyc.show', $submission) }}" class="text-link">Review →</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

    </div>
</div></section>
@endsection