@extends('layouts.app')

@section('title', 'Investment and withdrawal reviews')

@section('content')
<section class="page-section">
    <div class="container dashboard-container model-preview">
        <div class="dashboard-heading mockup-page-heading">
            <div><div class="eyebrow">Restricted administrator area</div><h1 class="page-title">Investment and withdrawal reviews</h1><p class="page-lede">Saved investment previews and USDT · TRC20 withdrawal review requests.</p></div>
            <a class="button button-outline" href="{{ route('admin.dashboard') }}">Back to admin</a>
        </div>
        @if (session('status'))<div class="draft-banner" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="notice-card" role="alert"><span class="notice-icon">!</span><div><strong>Review was not saved.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
        <div class="notice-card"><span class="notice-icon">i</span><div><strong>Deposit verification is handled separately.</strong><p>Open the deposit review page to match a user's transaction hash and sender address to a confirmed USDT TRC20 transfer. Withdrawal review decisions do not trigger a payment.</p><a class="text-link" href="{{ route('admin.deposits.index') }}">Open deposit review queue →</a></div></div>

        <section class="dashboard-panel model-table-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Saved selections</div><h2>Investment previews</h2></div><span class="status-pill">{{ $investments->total() }} records</span></div>
            <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Account</th><th>Package</th><th>Amount</th><th>Rate · term</th><th>Estimated reward</th><th>Maturity</th></tr></thead><tbody>
                @forelse ($investments as $investment)
                    <tr><td>{{ $investment->user->name }}<br><small>{{ $investment->user->email }}</small></td><td><strong>{{ $investment->package_name }}</strong></td><td>{{ number_format((float) $investment->principal_usdt, 2) }} USDT</td><td>{{ number_format((float) $investment->daily_rate, 2) }}% · {{ $investment->maturity_days }} days</td><td>{{ number_format((float) $investment->estimated_reward_usdt, 2) }} USDT</td><td>{{ $investment->matures_at->format('M j, Y') }}</td></tr>
                @empty<tr><td colspan="6">No investment previews saved.</td></tr>@endforelse
            </tbody></table></div>
            <div class="model-pagination">{{ $investments->links() }}</div>
        </section>

        <section class="dashboard-panel model-table-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Withdrawal queue</div><h2>Review requests</h2></div><span class="status-pill">{{ $pendingWithdrawals }} pending</span></div>
            <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Account</th><th>Package</th><th>Gross · fee · net</th><th>Network</th><th>Submitted</th><th>Status / action</th></tr></thead><tbody>
                @forelse ($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $withdrawal->user->name }}<br><small>{{ $withdrawal->user->email }}</small></td>
                        <td>{{ $withdrawal->investmentAccount->package_name }}<br><small>{{ number_format((float) $withdrawal->investmentAccount->principal_usdt, 2) }} USDT preview</small></td>
                        <td>{{ number_format((float) $withdrawal->amount_usdt, 2) }} USDT<br><small>Fee {{ number_format((float) ($withdrawal->fee_amount_usdt ?? 0), 2) }} · Net {{ number_format((float) ($withdrawal->net_amount_usdt ?? $withdrawal->amount_usdt), 2) }} USDT</small></td><td>{{ $withdrawal->network }}</td><td>{{ $withdrawal->created_at->format('M j, Y H:i') }}</td>
                        <td>
                            @if ($withdrawal->status === 'pending')
                                <form method="POST" action="{{ route('admin.withdrawals.review', $withdrawal) }}" class="model-review-form">@csrf<label class="sr-only" for="review-note-{{ $withdrawal->id }}">Review note (required)</label><input id="review-note-{{ $withdrawal->id }}" name="review_note" type="text" maxlength="1000" placeholder="Review note (required)" required><button class="button button-primary" name="decision" value="review_approved">Approve review only</button><button class="button button-outline" name="decision" value="rejected">Reject</button></form>
                            @else
                                <strong>{{ $withdrawal->status === 'review_approved' ? 'Review approved' : 'Rejected' }}</strong><br><small>{{ $withdrawal->reviewer?->name }} · {{ $withdrawal->reviewed_at?->format('M j, Y') }}</small>@if ($withdrawal->review_note)<br><small>{{ $withdrawal->review_note }}</small>@endif
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="6">No withdrawal review requests.</td></tr>@endforelse
            </tbody></table></div>
            <div class="model-pagination">{{ $withdrawals->links() }}</div>
            <p class="dashboard-muted">“Mark reviewed” records an internal decision only. No cryptocurrency is transferred.</p>
        </section>
    </div>
</section>
@endsection
