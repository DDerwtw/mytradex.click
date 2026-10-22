@extends('layouts.app')

@section('title', 'TRC20 deposit review')

@section('content')
<section class="page-section"><div class="container dashboard-container model-preview">
    <div class="dashboard-heading mockup-page-heading"><div><div class="eyebrow">Restricted administrator area</div><h1 class="page-title">USDT TRC20 deposits</h1><p class="page-lede">Confirmed transfers to the configured TRON wallet.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">Back to admin</a></div>
    <div class="notice-card"><span class="notice-icon">i</span><div><strong>One shared wallet address.</strong><p>All users see the configured wallet. Users submit their transaction hash and sender address. The system checks the confirmed TRON transfer, destination, USDT contract, and exact plan amount; administrator approval then records the ledger entry and activates the plan. No withdrawal endpoint is connected.</p></div></div>
    @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice-card" role="alert"><span class="notice-icon">!</span><div><strong>Action was not completed.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    <section class="dashboard-panel model-table-panel">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">Connection status</div><h2>{{ $configured ? 'TRON history lookup configured' : 'Not configured' }}</h2></div><span class="status-pill">{{ $configured ? 'USDT · TRC20' : 'Disabled' }}</span></div>
        @if ($configured)
            <div class="deposit-admin-actions"><form method="POST" action="{{ route('admin.deposits.sync') }}">@csrf<button class="button button-primary" type="submit">Check confirmed transfers</button></form></div>
        @else
            <p class="dashboard-muted">Set the TronGrid API key and wallet address in the server environment, then enable history lookup. Keep user deposits disabled until the wallet address and API setup have been independently checked.</p>
        @endif
        @if ($depositAddress)<div class="deposit-admin-address"><strong>Configured USDT · TRC20 address</strong><code class="deposit-address">{{ $depositAddress }}</code></div>@endif
        <p class="dashboard-muted">User submissions {{ $investmentDepositsEnabled ? 'enabled' : 'disabled' }}. Enable only after verifying the wallet address and successful history sync.</p>
    </section>
    <section class="dashboard-panel model-table-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">Deposit review queue</div><h2>User claims</h2></div><span class="status-pill">{{ $pendingDeposits }} pending</span></div>
        <p class="dashboard-muted">Approve only after the confirmed transfer, exact amount, TRC20 network, sender, and destination all match. Approval writes one balanced USDT ledger transaction and starts the selected term.</p>
        <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>User · plan</th><th>Amount</th><th>Transaction hash</th><th>TRON match</th><th>Submitted</th><th>Decision</th></tr></thead><tbody>
            @forelse ($depositClaims as $claim)
                <tr><td>{{ $claim->user->name }}<br><small>{{ $claim->user->email }}</small><br><strong>{{ $claim->investmentAccount->package_name }}</strong> · {{ $claim->investmentAccount->maturity_days }} days</td><td>{{ number_format((float) $claim->amount_usdt, 2) }} USDT</td><td class="hash-cell">{{ $claim->transaction_id }}<br><small>Sender: {{ $claim->source_address }}</small>@if ($claim->receipt_path)<br><a class="text-link" href="{{ route('admin.deposits.receipt', $claim) }}" target="_blank" rel="noopener">View transaction slip</a>@else<br><small>No slip uploaded</small>@endif</td><td>@if ($claim->observation){{ number_format((float) $claim->observation->amount, 8) }} USDT · {{ $claim->observation->provider_status }}<br><small>From: {{ $claim->observation->source_address }}<br>To: {{ $claim->observation->destination_address }}</small>@else{{ $claim->status === 'pending' ? 'Check TRON history' : '—' }}@endif</td><td>{{ $claim->created_at->format('M j, Y H:i') }}</td><td>
                    @if ($claim->status === 'pending')
                        <form method="POST" action="{{ route('admin.deposits.review', $claim) }}" class="model-review-form">@csrf<label class="sr-only" for="deposit-note-{{ $claim->id }}">Review note (required)</label><input id="deposit-note-{{ $claim->id }}" name="review_note" type="text" maxlength="1000" placeholder="Review note (required)" required><button class="button button-primary" name="decision" value="approved">Verify and activate plan</button><button class="button button-outline" name="decision" value="rejected">Reject</button></form>
                    @else
                        <strong>{{ ucfirst($claim->status) }}</strong><br><small>{{ $claim->reviewer?->name }} · {{ $claim->reviewed_at?->format('M j, Y') }}</small>@if ($claim->review_note)<br><small>{{ $claim->review_note }}</small>@endif
                    @endif
                </td></tr>
            @empty<tr><td colspan="6">No user deposit claims.</td></tr>@endforelse
        </tbody></table></div><div class="model-pagination">{{ $depositClaims->links() }}</div>
    </section>
    <section class="dashboard-panel model-table-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">TRON history</div><h2>Confirmed USDT transfers</h2></div><span class="status-pill">{{ $deposits->total() }} records</span></div>
        <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Transaction ID</th><th>Coin · network</th><th>Amount</th><th>Status</th><th>Sender → destination</th><th>Block time</th></tr></thead><tbody>
            @forelse ($deposits as $deposit)
                <tr><td class="hash-cell">{{ $deposit->transaction_id }}@if ($deposit->investment_deposit_claims_count)<br><small>Attributed</small>@endif</td><td>{{ $deposit->coin }} · {{ $deposit->network }}</td><td>{{ $deposit->amount }} USDT</td><td>{{ $deposit->provider_status }}</td><td class="hash-cell">{{ $deposit->source_address }}<br>→ {{ $deposit->destination_address }}</td><td>{{ $deposit->occurred_at?->format('M j, Y H:i') ?? '—' }}</td></tr>
            @empty<tr><td colspan="6">No transfer history has been imported.</td></tr>@endforelse
        </tbody></table></div><div class="model-pagination">{{ $deposits->links() }}</div>
    </section>
</div></section>
@endsection
