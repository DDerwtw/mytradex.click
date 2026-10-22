@extends('layouts.app')

@section('title', 'Referral relationships')

@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading"><div><div class="eyebrow">Restricted area</div><h1 class="page-title">Referral relationships</h1><p class="page-lede">Review referral links, deposit-based commissions, and Friday withdrawal requests.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">Admin overview</a></div>
    <div class="draft-banner"><strong>Commission basis:</strong> The configured level rate is recorded against each referred member’s administrator-approved USDT deposit. Payouts remain manual and require an on-chain transaction hash.</div>
    <div class="dashboard-grid referral-stats">
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">↗</span></div><h2>Relationships</h2><p class="dashboard-metric">{{ number_format($referralRelationshipsCount) }}</p><div class="dashboard-detail"><span>Source</span><strong>Database</strong></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">✓</span></div><h2>Active and verified</h2><p class="dashboard-metric">{{ number_format($qualifiedReferralCount) }}</p><div class="dashboard-detail"><span>Approved deposit commissions</span><strong>Enabled</strong></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">◎</span></div><h2>Referring accounts</h2><p class="dashboard-metric">{{ number_format($referrersCount) }}</p><div class="dashboard-detail"><span>At least one invite</span><strong>Database</strong></div></article>
        <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">◇</span><span class="status-pill">Enabled</span></div><h2>Commissions accrued</h2><p class="dashboard-metric">{{ number_format($commissionTotal, 2) }} USDT</p><div class="dashboard-detail"><span>Rates by level</span><strong>{{ collect($commissionRates)->map(fn ($rate, $level) => 'L'.$level.' '.number_format((float) $rate, 2).'%')->implode(' · ') }}</strong></div></article>
    </div>
    <section class="dashboard-panel admin-recent">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">Friday payout cycle</div><h2>Referral income withdrawal queue</h2></div><span class="status-pill">{{ $pendingReferralWithdrawals }} pending</span></div>
        <p class="dashboard-muted">Requests are scheduled for Friday. The 2.5% charge is already shown. Review approval does not transfer funds; first complete the external TRC20 transfer, then record its transaction hash here.</p>
        <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Account</th><th>Gross</th><th>Charge</th><th>Net to send</th><th>Scheduled</th><th>Wallet</th><th>Status / action</th></tr></thead><tbody>
            @forelse ($referralWithdrawals as $withdrawal)
                <tr><td>{{ $withdrawal->user->name }}<br><small>{{ $withdrawal->user->email }}</small></td><td>{{ number_format((float) $withdrawal->gross_amount_usdt, 2) }} USDT</td><td>{{ number_format((float) $withdrawal->fee_amount_usdt, 2) }} USDT<br><small>{{ number_format((float) $withdrawal->fee_rate, 2) }}%</small></td><td>{{ number_format((float) $withdrawal->net_amount_usdt, 2) }} USDT</td><td>{{ $withdrawal->scheduled_for->format('M j, Y') }}</td><td><code>{{ $withdrawal->wallet_address }}</code><br><small>{{ $withdrawal->network }}</small></td><td>
                    @if ($withdrawal->status === 'pending')
                        <form method="POST" action="{{ route('admin.referrals.withdrawals.review', $withdrawal) }}" class="model-review-form">@csrf<label class="sr-only" for="referral-review-{{ $withdrawal->id }}">Review note</label><input id="referral-review-{{ $withdrawal->id }}" name="review_note" type="text" maxlength="1000" placeholder="Optional note"><button class="button button-primary" name="decision" value="review_approved">Approve review</button><button class="button button-outline" name="decision" value="rejected">Reject</button></form>
                    @elseif ($withdrawal->status === 'review_approved')
                        <strong>Review approved</strong><br><small>{{ $withdrawal->reviewer?->name }} · {{ $withdrawal->reviewed_at?->format('M j, Y') }}</small><form method="POST" action="{{ route('admin.referrals.withdrawals.paid', $withdrawal) }}" class="model-review-form">@csrf<label class="sr-only" for="referral-hash-{{ $withdrawal->id }}">External TRON transaction hash</label><input id="referral-hash-{{ $withdrawal->id }}" name="payout_transaction_id" type="text" maxlength="64" pattern="[a-fA-F0-9]{64}" placeholder="TRON transaction hash after sending" required><button class="button button-primary" type="submit">Record external payment</button></form>
                    @else
                        <strong>{{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }}</strong>@if ($withdrawal->payout_transaction_id)<br><small>Tx: {{ $withdrawal->payout_transaction_id }}</small>@endif @if ($withdrawal->review_note)<br><small>{{ $withdrawal->review_note }}</small>@endif
                    @endif
                </td></tr>
            @empty<tr><td colspan="7">No referral withdrawal requests.</td>@endforelse
        </tbody></table></div>{{ $referralWithdrawals->links() }}
    </section>
    <section class="dashboard-panel admin-recent">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">Account relationships</div><h2>Recorded referrals</h2></div><span class="status-pill">{{ number_format($referrals->total()) }} results</span></div>
        <form method="GET" action="{{ route('admin.referrals.index') }}" class="referral-search-form"><label for="search">Search account email or referral code</label><div class="referral-copy-row"><input id="search" name="search" type="search" maxlength="100" value="{{ $search }}" placeholder="Enter email or code"><button class="button button-primary" type="submit">Search</button>@if ($search !== '')<a class="button button-outline" href="{{ route('admin.referrals.index') }}">Clear</a>@endif</div></form>
        @if ($referrals->isEmpty())
            <div class="empty-state"><span>◎</span><strong>No referral relationships found</strong><p>New accounts that use an invite code will appear here.</p></div>
        @else
            <div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>Joined account</th><th>Referrer</th><th>Joined</th><th>Email</th><th>Status</th></tr></thead><tbody>
                @foreach ($referrals as $member)
                    <tr><td><span>{{ $member->email }}</span><x-kyc-badge :verified="(bool) $member->kyc_verified" /></td><td><span>{{ $member->referrer?->email ?? 'Unavailable' }}</span>@if ($member->referrer)<x-kyc-badge :verified="(bool) $member->referrer->kyc_verified" />@endif</td><td>{{ $member->created_at->format('M j, Y') }}</td><td>{{ $member->email_verified_at ? 'Verified' : 'Pending' }}</td><td>{{ ucfirst($member->status) }}</td></tr>
                @endforeach
            </tbody></table></div>
            {{ $referrals->links() }}
        @endif
    </section>

    <!-- Manual Balance Adjustment Section -->
    <section class="dashboard-panel admin-recent" style="border-top: 2px solid #38bdf8; margin-bottom: 30px;">
        <div class="dashboard-panel-heading">
            <div>
                <div class="eyebrow" style="color: #38bdf8;">Financial Controls</div>
                <h2>Manual Balance Adjustment</h2>
            </div>
            <span class="status-pill" style="background: rgba(234, 179, 8, 0.1); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.2);">Admin Only</span>
        </div>
        <p class="dashboard-muted">Adjust a user's Referral or Maturity wallet balance manually. A ledger record will be created for auditing purposes.</p>
        
        <form method="POST" action="" id="manual-adjust-form" style="margin-top: 20px;">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">Select User</label>
                    <select name="user_id" id="adjust_user_id" required class="referral-search-form input" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff;">
                        <option value="">-- Search or select user --</option>
                        @foreach(App\Models\User::limit(100)->get() as $u)
                            <option value="{{ $u->id }}" data-url="{{ route('admin.users.adjust-balance', $u->id) }}">
                                {{ $u->name }} ({{ $u->email }}) - ID: {{ $u->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">Wallet Type</label>
                    <select name="wallet_type" required style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff;">
                            <option value="referral">Referral Income Wallet</option>
                            <option value="maturity">Maturity (Investment) Wallet</option>
                            <option value="bonus">Bonus Income Wallet</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">Action</label>
                    <select name="action" required style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff;">
                        <option value="add">Add Funds (+)</option>
                        <option value="subtract">Deduct Funds (-)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">Amount (USDT)</label>
                    <input type="number" name="amount" step="0.00000001" min="0.00000001" required placeholder="0.00" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">Reason / Internal Note</label>
                <input type="text" name="reason" required placeholder="e.g., Bonus for early investor, Deposit correction" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 15px; align-items: center;">
                <span style="font-size: 0.8rem; color: #64748b;">⚠️ This action will be permanently recorded in the ledger.</span>
                <button type="submit" class="button button-primary" style="padding: 12px 24px; font-weight: bold;">Apply Adjustment</button>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('adjust_user_id');
            const form = document.getElementById('manual-adjust-form');
            
            userSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    form.action = selectedOption.getAttribute('data-url');
                } else {
                    form.action = '';
                }
            });
        });
    </script>

</div></section>
@endsection