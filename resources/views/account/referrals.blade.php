@extends('layouts.app')

@section('title', 'Referrals')

@section('content')
<section class="page-section">
    <div class="container dashboard-container">
        <div class="dashboard-heading">
            <div><div class="eyebrow">MyTradeX community</div><h1 class="page-title">Referral overview</h1><p class="page-lede">Share your link and view referral relationships connected to your account.</p></div>
            <a class="button button-outline" href="{{ route('dashboard') }}">Back to dashboard</a>
        </div>

        <section class="dashboard-panel referral-balance-panel"><div><div class="eyebrow">Total referral income balance</div><strong>{{ number_format($availableReferralIncome, 2) }} <small>USDT</small></strong><span>Available to request</span></div><div class="referral-balance-meta"><span>Total earned <strong>{{ number_format($earnedReferralIncome, 2) }} USDT</strong></span><span>Reserved for requests <strong>{{ number_format($reservedReferralIncome, 2) }} USDT</strong></span></div></section>

        <div class="referral-share-panel dashboard-panel">
            <div class="eyebrow">Your referral link</div>
            <h2>Invite someone to MyTradeX</h2>
            <p class="dashboard-muted">Your personal code is <strong>{{ $user->referral_code }}</strong>. A referral is recorded only when a new account is created through this link.</p>
            <div class="referral-copy-row">
                <input type="text" readonly aria-label="Your referral link" value="{{ $referralUrl }}" id="referral-link">
                <button class="button button-primary" type="button" data-copy-referral="{{ $referralUrl }}">Copy link</button>
            </div>
            <p class="referral-copy-status" role="status" aria-live="polite" data-copy-status></p>
        </div>

        <div class="dashboard-grid referral-stats">
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">1</span></div><h2>Level 1 · Direct referrals</h2><p class="dashboard-metric">{{ number_format($levelCounts[1]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[1] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[1] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Verified and active</span><strong>{{ number_format($qualifiedDirectCount) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">2</span></div><h2>Level 2</h2><p class="dashboard-metric">{{ number_format($levelCounts[2]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[2] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[2] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[2]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">3</span></div><h2>Level 3</h2><p class="dashboard-metric">{{ number_format($levelCounts[3]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[3] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[3] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[3]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">4</span></div><h2>Level 4</h2><p class="dashboard-metric">{{ number_format($levelCounts[4]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[4] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[4] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[4]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">5</span></div><h2>Level 5</h2><p class="dashboard-metric">{{ number_format($levelCounts[5]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[5] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[5] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[5]) }}</strong></div></article>
        </div>

        <section class="dashboard-panel referral-withdraw-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">Earnings withdrawal</div><h2>Choose withdrawal type</h2></div><span class="status-pill">TRC20</span></div>
            @if (! $kycVerified || ! $withdrawalWalletAddress)<div class="draft-banner"><strong>Approved KYC and a saved TRC20 wallet are required.</strong> <a class="text-link" href="{{ route('account.kyc') }}">Open KYC Verification Review →</a></div>@else<p class="dashboard-muted">Saved payout wallet: <code class="deposit-address">{{ $withdrawalWalletAddress }}</code></p>@endif
            @if ($errors->any())<div class="form-alert" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form class="model-save-form referral-withdraw-form" method="POST" action="{{ route('account.referral-withdrawals.store') }}">@csrf
                <label for="withdrawal_type">Withdrawal type<select name="withdrawal_type" id="withdrawal_type" required><option value="referral">Referral income withdrawal</option><option value="maturity" @if ($maturedInvestments->isEmpty()) disabled @endif>Maturity withdrawal</option></select></label>
                <label id="maturity-investment-field" hidden>Investment<select name="investment_account_id" id="investment_account_id" disabled>@foreach ($maturedInvestments as $investment)<option value="{{ $investment->id }}" data-min="{{ number_format((float) $investment->minimum_withdrawal_usdt, 8, '.', '') }}" data-max="{{ number_format($investment->estimatedTotal(), 8, '.', '') }}">{{ $investment->package_name }} · min {{ number_format((float) $investment->minimum_withdrawal_usdt, 2) }} USDT</option>@endforeach</select>@if ($maturedInvestments->isEmpty())<small class="field-hint">No eligible investment has matured yet.</small>@endif</label>
                <label for="withdrawal-amount">Amount (USDT)<input name="amount_usdt" id="withdrawal-amount" type="number" min="0.00000001" max="{{ number_format($availableReferralIncome, 8, '.', '') }}" step="0.00000001" required value="{{ old('amount_usdt') }}"></label>
                <div class="referral-fee-preview" id="referral-fee-preview"><span>Tax and service charge ({{ number_format($withdrawalFeeRate, 2) }}%) <strong id="withdrawal-fee">0.00 USDT</strong></span><span>Estimated net amount <strong id="withdrawal-net">0.00 USDT</strong></span><small id="referral-withdraw-note">Referral income requests are scheduled for the next Friday. Requests submitted Thursday or Friday go to the following Friday. An administrator reviews them; this app does not send funds.</small></div>
                <span class="dashboard-muted" id="maturity-withdraw-note" hidden>Maturity requests are available only after the selected period ends and remain subject to the package minimum. The same 2.5% tax and service charge applies. Administrator review only; no funds are sent.</span>
                <button class="button button-primary" type="submit" @if (! $kycVerified || ! $withdrawalWalletAddress) disabled @endif>Submit withdrawal request</button>
            </form>
        </section>

        @if ($referralWithdrawals->isNotEmpty())
            <section class="dashboard-panel referral-withdraw-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">Referral income</div><h2>Withdrawal requests</h2></div></div><div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>Requested</th><th>Gross</th><th>Fee · net</th><th>Scheduled Friday</th><th>Status</th></tr></thead><tbody>@foreach ($referralWithdrawals as $withdrawal)<tr><td>{{ $withdrawal->created_at->format('M j, Y') }}</td><td>{{ number_format((float) $withdrawal->gross_amount_usdt, 2) }} USDT</td><td>{{ number_format((float) $withdrawal->fee_amount_usdt, 2) }} · {{ number_format((float) $withdrawal->net_amount_usdt, 2) }} USDT</td><td>{{ $withdrawal->scheduled_for->format('M j, Y') }}</td><td>{{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }}</td></tr>@endforeach</tbody></table></div></section>
        @endif

        <section class="dashboard-panel referral-team-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Direct relationships</div><h2>Referral team</h2></div><span class="status-pill">{{ number_format($directReferrals->total()) }} total</span></div>
            @if ($directReferrals->isEmpty())
                <div class="empty-state"><span>◎</span><strong>No referrals yet</strong><p>When someone creates an account with your link, the relationship will appear here.</p></div>
            @else
                <div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>Member</th><th>KYC</th><th>Joined</th><th>Email status</th><th>Account status</th></tr></thead><tbody>
                    @foreach ($directReferrals as $member)
                        <tr><td>Referral member</td><td><x-kyc-badge :verified="(bool) $member->kyc_verified" /></td><td>{{ $member->created_at->format('M j, Y') }}</td><td>{{ $member->email_verified_at ? 'Verified' : 'Pending' }}</td><td>{{ ucfirst($member->status) }}</td></tr>
                    @endforeach
                </tbody></table></div>
                {{ $directReferrals->links() }}
            @endif
        </section>

        <div class="notice-card"><span class="notice-icon">i</span><div><strong>How referral commission is calculated</strong><p>Commission is accrued only after an administrator approves a referred member’s USDT deposit, using the level rate shown above and the approved deposit amount. Withdrawal estimates deduct the displayed 2.5% charge. Requests are reviewed by an administrator; no transfer is sent automatically.</p></div></div>
    </div>
</section>
<script>
    document.querySelector('[data-copy-referral]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const status = document.querySelector('[data-copy-status]');
        try {
            await navigator.clipboard.writeText(button.dataset.copyReferral);
            status.textContent = 'Referral link copied.';
        } catch {
            const field = document.querySelector('#referral-link');
            field.select();
            status.textContent = 'Select and copy the link from the field above.';
        }
    });
</script>
<script>
(() => {
    const type = document.querySelector('#withdrawal_type');
    const investmentField = document.querySelector('#maturity-investment-field');
    const investmentSelect = document.querySelector('#investment_account_id');
    const amount = document.querySelector('#withdrawal-amount');
    const feePanel = document.querySelector('#referral-fee-preview');
    const maturityNote = document.querySelector('#maturity-withdraw-note');
    const referralNote = document.querySelector('#referral-withdraw-note');
    const feeOutput = document.querySelector('#withdrawal-fee');
    const netOutput = document.querySelector('#withdrawal-net');
    if (!type || !amount) return;
    const update = () => {
        const maturity = type.value === 'maturity';
        investmentField.hidden = !maturity;
        investmentSelect.disabled = !maturity;
        maturityNote.hidden = !maturity;
        feePanel.hidden = false;
        referralNote.hidden = maturity;
        if (maturity && investmentSelect.selectedOptions.length) {
            const selected = investmentSelect.selectedOptions[0];
            amount.min = selected.dataset.min;
            amount.max = selected.dataset.max;
        } else {
            amount.min = '0.00000001';
            amount.max = '{{ number_format($availableReferralIncome, 8, '.', '') }}';
        }
        const gross = Number(amount.value || 0);
        const fee = Math.round(gross * {{ $withdrawalFeeRate }} / 100 * 1e8) / 1e8;
        feeOutput.textContent = `${fee.toFixed(2)} USDT`;
        netOutput.textContent = `${Math.max(0, gross - fee).toFixed(2)} USDT`;
    };
    type.addEventListener('change', update);
    investmentSelect?.addEventListener('change', update);
    amount.addEventListener('input', update);
    update();
})();
</script>
@endsection
