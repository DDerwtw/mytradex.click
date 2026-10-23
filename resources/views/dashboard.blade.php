@extends('layouts.app')

@section('title', 'Account overview')

@section('content')
<section class="page-section">
    <div class="container dashboard-container mockup-account">
        <div class="dashboard-heading mockup-page-heading">
            <div><div class="eyebrow">MyTradeX account</div><h1 class="page-title">Welcome, {{ auth()->user()->name }}. @if ($kycVerified)<x-kyc-badge :verified="$kycVerified" />@endif</h1><p class="page-lede">Your account overview and current feature status.</p></div>
            <span class="account-status"><i></i> Account active</span>
        </div>

        @if ($errors->any())
            <div class="notice-card" role="alert"><span class="notice-icon">!</span><div><strong>Please review the form details.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        @endif
        @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif

        @if ($adminMessages->isNotEmpty())
            <section class="dashboard-panel user-notifications-panel" aria-label="Administrator messages and action updates" aria-live="polite">
                <div class="dashboard-panel-heading"><div><div class="eyebrow">Notifications</div><h2>Messages and action notes</h2></div><span class="status-pill {{ $unreadAdminMessages ? 'notification-unread-pill' : '' }}">{{ $unreadAdminMessages }} unread</span></div>
                @if ($unreadAdminMessages)<p class="user-notification-intro">You have a new update from the MyTradeX administrator.</p>@endif
                <div class="user-notification-list">
                    @foreach ($adminMessages as $message)
                        <article class="user-notification-item {{ $message->read_at ? 'is-read' : 'is-unread' }}">
                            <span class="user-notification-indicator" aria-hidden="true">{{ $message->read_at ? '✓' : '!' }}</span>
                            <div class="user-notification-content"><div class="user-notification-meta"><strong>{{ $message->title }}</strong><span>{{ $message->category === 'action' ? 'Action update' : 'Administrator message' }} · {{ $message->created_at->format('M j, Y H:i') }} · {{ $message->admin?->name ?? 'MyTradeX' }}</span></div><p>{{ $message->body }}</p></div>
                            @if (! $message->read_at)<form method="POST" action="{{ route('account.messages.read', $message) }}">@csrf<button class="button button-outline" type="submit">Mark as read</button></form>@else<span class="status-pill">Read</span>@endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="dashboard-grid mockup-account-stats">
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">◉</span><span class="status-pill status-ready">Verified</span></div><h2>Email account</h2><p class="mockup-stat-value">Active</p><div class="dashboard-detail"><span>Email</span><strong>{{ auth()->user()->email }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">◎</span><span class="status-pill status-ready">Available</span></div><h2>Referral network</h2><p class="mockup-stat-value">{{ number_format($referralCount) }}</p><div class="dashboard-detail"><span>Direct referrals</span><a class="text-link" href="{{ route('account.referrals') }}">Open →</a></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">⬡</span><span class="status-pill {{ $kycSubmission?->status === 'approved' ? 'status-ready' : '' }}">{{ $kycSubmission ? ucfirst(str_replace('_', ' ', $kycSubmission->status)) : 'Not submitted' }}</span></div><h2>KYC Verification Review</h2><p class="mockup-stat-value">{{ $kycSubmission ? ucfirst(str_replace('_', ' ', $kycSubmission->status)) : 'Not started' }}</p><div class="dashboard-detail"><span>Private document review</span><a class="text-link" href="{{ route('account.kyc') }}">View →</a></div></article>
            
            <!-- Wallet Balance Card (Updated with Link) -->
            <a href="{{ route('account.wallets') }}" style="text-decoration: none; color: inherit; display: block;">
                <article class="dashboard-card" style="transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 30px rgba(56, 189, 248, 0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                    <div class="dashboard-card-top">
                        <span class="dashboard-icon">◇</span>
                        <span class="status-pill" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">Active</span>
                    </div>
                    <h2>Wallet balance</h2>
                    <p class="mockup-stat-value">Available</p>
                    <div class="dashboard-detail">
                        <span>USDT · TRC20</span>
                        <strong style="color: #38bdf8; font-weight: bold;">View Details →</strong>
                    </div>
                </article>
            </a>

        </div>

        <div class="mockup-account-main">
            <section class="dashboard-panel mockup-account-profile">
                <div class="dashboard-panel-heading"><div><div class="eyebrow">Account details</div><h2>Your profile</h2></div><span class="status-pill status-ready">ACTIVE</span></div>
                <div class="mockup-profile-grid"><div><span>Full name</span><strong>{{ auth()->user()->name }} @if ($kycVerified)<x-kyc-badge :verified="$kycVerified" />@endif</strong></div><div><span>Email address</span><strong>{{ auth()->user()->email }}</strong></div><div><span>Email verified</span><strong>{{ auth()->user()->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</strong></div><div><span>Member since</span><strong>{{ auth()->user()->created_at->format('M j, Y') }}</strong></div></div>
                <div class="mockup-setup-title"><strong>Account setup</strong><span>Secure access steps</span></div>
                <div class="mockup-setup-steps"><div><i>01</i><span><strong>Create account</strong><small>Complete</small></span></div><div><i>02</i><span><strong>Verify email</strong><small>{{ auth()->user()->hasVerifiedEmail() ? 'Complete' : 'Required' }}</small></span></div><div><i>03</i><span><strong>KYC Verification Review</strong><small>{{ $kycSubmission ? ucfirst(str_replace('_', ' ', $kycSubmission->status)) : 'Not submitted' }}</small></span></div></div>
            </section>
            <aside class="mockup-account-quick">
                <section class="dashboard-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">Quick access</div><h2>Platform areas</h2></div></div><a class="mockup-quick-link" href="{{ route('markets') }}"><i>◈</i><span><strong>Market overview</strong><small>View CoinGecko prices</small></span><b>→</b></a><a class="mockup-quick-link" href="{{ route('account.referrals') }}"><i>◎</i><span><strong>Referral links</strong><small>Share your personal link</small></span><b>→</b></a><a class="mockup-quick-link" href="{{ route('account.kyc') }}"><i>⬡</i><span><strong>KYC Verification Review</strong><small>Review account verification</small></span><b>→</b></a></section>
                <section class="dashboard-panel mockup-account-risk"><div class="eyebrow">Account security</div><strong>Keep your sign-in details private.</strong><p>MyTradeX will never ask you to share your password or verification link.</p><a href="{{ route('legal.risk') }}">Read platform information →</a></section>
            </aside>
        </div>

        <div class="draft-banner model-user-banner"><strong>Investment plan:</strong> Saved plans are estimates until the exact USDT TRC20 deposit is verified by an administrator.</div>
        @include('components.investment-calculator', ['packages' => $packages, 'payoutCapMultiple' => $payoutCapMultiple])
        <section class="dashboard-panel model-table-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Your saved selections</div><h2>Investment previews</h2></div><span class="status-pill">{{ $investments->count() }} saved</span></div>
            <p class="dashboard-muted">A saved plan becomes active only after its exact USDT TRC20 deposit is found on the TRON network and approved by an administrator.</p>
            @if ($investments->isEmpty())
                <div class="empty-state"><strong>No saved selections yet</strong><p>Use the calculator above to save a package and maturity period.</p></div>
            @else
                <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Package</th><th>Period</th><th>Estimated total</th><th>Matures on</th><th>Status</th></tr></thead><tbody>
                    @foreach ($investments as $investment)
                        <tr><td><strong>{{ $investment->package_name }}</strong><br>{{ number_format((float) $investment->principal_usdt, 2) }} USDT</td><td>{{ $investment->maturity_days }} days</td><td>{{ number_format($investment->estimatedTotal(), 2) }} USDT</td><td>{{ $investment->matures_at->format('M j, Y') }}</td><td>{{ $investment->status === 'active' ? ($investment->matures_at->isPast() ? 'Matured' : 'Active') : ($investment->status === 'awaiting_deposit_review' ? 'Deposit review pending' : 'Saved preview') }}</td></tr>
                        @if ($investment->status === 'preview' && $depositFlowReady)
                            <tr><td colspan="5">
                                <div class="deposit-instructions"><strong>Start this plan with {{ number_format((float) $investment->principal_usdt, 2) }} USDT on TRC20</strong><p>Send only the exact amount over the TRON (TRC20) network to this shared wallet address. Your transaction hash and sending address identify your deposit. Do not send other coins or use another network.</p><div class="deposit-wallet-panel"><div><strong>USDT TRC20 deposit wallet</strong><code class="deposit-address">{{ $depositAddress }}</code><small>Scan the QR code to copy this TRON wallet address. Check that your wallet is set to the TRON (TRC20) network.</small></div><div class="deposit-wallet-qr"><div class="deposit-qr-code" data-wallet-address="{{ $depositAddress }}" aria-label="QR code for the USDT TRC20 wallet address"></div><span>Wallet address QR</span></div></div>
                                    <form class="model-save-form" method="POST" enctype="multipart/form-data" action="{{ route('account.investments.deposits.store', $investment) }}">@csrf<label>After sending, enter the TRON transaction hash <input name="transaction_id" type="text" minlength="64" maxlength="64" pattern="[A-Fa-f0-9]{64}" autocomplete="off" required></label><label>Sending wallet address <input name="source_address" type="text" minlength="34" maxlength="34" pattern="T[1-9A-HJ-NP-Za-km-z]{33}" autocomplete="off" required></label><label>Transaction slip image (JPG, PNG, or WebP; maximum 520 KB)<input name="receipt" type="file" accept="image/jpeg,image/png,image/webp" required /></label><span class="dashboard-muted">All three fields are required. The plan starts only after the confirmed TRON transfer matches the amount, transaction hash, sender address, and destination, then an administrator approves it. The slip supports review but does not replace on-chain verification.</span><button class="button button-primary" type="submit">Submit deposit for verification</button></form>
                                </div>
                            </td></tr>
                        @endif
                        @foreach ($investment->depositClaims as $claim)
                            <tr><td colspan="5">Deposit {{ number_format((float) $claim->amount_usdt, 2) }} USDT · {{ strtoupper($claim->network) }} · {{ ucfirst($claim->status) }} · transaction {{ $claim->transaction_id }}</td></tr>
                        @endforeach
                        @if ($investment->status === 'active' && $investment->matures_at->isPast() && $investment->withdrawalRequests->whereIn('status', ['pending', 'review_approved'])->isEmpty())
                            <tr><td colspan="5">
                                @if ((float) $investment->minimum_withdrawal_usdt <= $investment->estimatedTotal())
                                    <form class="model-save-form" method="POST" action="{{ route('account.investments.withdrawals.store', $investment) }}">@csrf<label>Withdrawal review amount (USDT) <input name="amount_usdt" type="number" min="{{ number_format((float) $investment->minimum_withdrawal_usdt, 2, '.', '') }}" max="{{ number_format($investment->estimatedTotal(), 8, '.', '') }}" step="0.00000001" required></label><span class="dashboard-muted">Minimum {{ number_format((float) $investment->minimum_withdrawal_usdt, 2) }} USDT. Administrator review only; no funds are sent.</span>@if ($kycVerified)<button class="button button-outline" type="submit">Request review</button>@else<span class="dashboard-muted">Approved identity verification is required.</span>@endif</form>
                                @else
                                    <span class="dashboard-muted">This estimate has not reached the package's minimum withdrawal amount.</span>
                                @endif
                            </td></tr>
                        @endif
                        @foreach ($investment->withdrawalRequests as $withdrawal)
                            <tr><td colspan="5">Withdrawal review: {{ number_format((float) $withdrawal->amount_usdt, 2) }} USDT · {{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }} · no payment made</td></tr>
                        @endforeach
                    @endforeach
                </tbody></table></div>
            @endif
        </section>
        <section class="dashboard-panel model-table-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Package comparison</div><h2>Daily-rate assumptions</h2></div><span class="status-pill">{{ count($packages) }} entries</span></div>
            <div class="referral-table-wrap"><table class="referral-table model-table">
                <thead><tr><th>Package</th><th>Amount (USDT)</th><th>Daily rate assumption</th><th>Minimum withdrawal amount</th></tr></thead>
                <tbody>@foreach ($packages as $package)<tr><td><strong>{{ $package['name'] }}</strong></td><td>{{ number_format($package['amount']) }} USDT</td><td>{{ number_format($package['daily_rate'], 1) }}%</td><td>{{ number_format($package['minimum_withdrawal']) }} USDT</td></tr>@endforeach</tbody>
            </table></div>
        </section>
        <div class="notice-card model-preview-notice"><span class="notice-icon">i</span><div><strong>Preview only.</strong><p>This estimate is not a promise of earnings. Deposit review must be configured before a plan can be activated.</p></div></div>

        <!-- New Account Summary Section (Replacing Crypto Transfers & Recent Transactions) -->
        <section class="dashboard-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading">
                <div>
                    <div class="eyebrow">Account Summary</div>
                    <h2>Quick Links & Status</h2>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <a href="{{ route('account.wallets') }}" style="text-decoration: none; color: inherit;">
                    <div class="dashboard-card" style="padding: 20px; background: rgba(56, 189, 248, 0.05); border: 1px solid rgba(56, 189, 248, 0.2);">
                        <h3 style="color: #38bdf8;">Wallet Balance</h3>
                        <p class="dashboard-muted">Check your total referral income, maturity funds, and bonus.</p>
                        <strong style="color: #38bdf8;">Go to Wallets →</strong>
                    </div>
                </a>
                <a href="{{ route('account.referrals') }}" style="text-decoration: none; color: inherit;">
                    <div class="dashboard-card" style="padding: 20px; background: rgba(74, 222, 128, 0.05); border: 1px solid rgba(74, 222, 128, 0.2);">
                        <h3 style="color: #4ade80;">Referral Network</h3>
                        <p class="dashboard-muted">Invite friends, view your team, and check commissions.</p>
                        <strong style="color: #4ade80;">View Referrals →</strong>
                    </div>
                </a>
                <a href="{{ route('account.kyc') }}" style="text-decoration: none; color: inherit;">
                    <div class="dashboard-card" style="padding: 20px; background: rgba(234, 179, 8, 0.05); border: 1px solid rgba(234, 179, 8, 0.2);">
                        <h3 style="color: #eab308;">KYC Verification</h3>
                        <p class="dashboard-muted">Complete your identity verification to unlock withdrawals.</p>
                        <strong style="color: #eab308;">Check KYC →</strong>
                    </div>
                </a>
            </div>
        </section>

        <div class="notice-card"><span class="notice-icon">i</span><div><strong>Deposits require manual verification.</strong><p>The USDT TRC20 wallet address is shared across users. An investment is activated only after its transaction hash, exact amount, sender, and destination are matched to a confirmed TRON transfer and approved by an administrator. This site does not send withdrawals or promise earnings.</p></div></div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
<script>
document.querySelectorAll('.deposit-qr-code[data-wallet-address]').forEach((element) => {
    const address = element.dataset.walletAddress;
    if (window.QRCode && address) {
        new QRCode(element, { text: address, width: 144, height: 144, colorDark: '#06162a', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    } else {
        element.textContent = 'QR unavailable';
    }
});
</script>
@endpush