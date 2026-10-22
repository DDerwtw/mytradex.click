@extends('layouts.app')

@section('title', 'Ledger report')

@section('content')
<section class="page-section"><div class="container dashboard-container model-preview">
    <div class="dashboard-heading mockup-page-heading"><div><div class="eyebrow">Restricted administrator area</div><h1 class="page-title">Ledger report</h1><p class="page-lede">Read-only journal entries with debit and credit totals grouped by currency.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">Back to admin</a></div>
    <div class="notice-card"><span class="notice-icon">i</span><div><strong>Verified deposits only.</strong><p>USDT deposits appear here only after an administrator matches the transaction against a confirmed TRON TRC20 transfer. Estimates and withdrawal review decisions are not posted as money movements.</p></div></div>
    <section class="dashboard-panel model-table-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">Journal</div><h2>Posted ledger transactions</h2></div><span class="status-pill">{{ $transactions->total() }} records</span></div>
        <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>Reference</th><th>Description and entries</th><th>Currency</th><th>Journal</th><th>Posted</th></tr></thead><tbody>
            @forelse ($transactions as $transaction)
                <tr><td><strong>{{ $transaction->reference }}</strong></td><td>{{ $transaction->description }}<br><small>@foreach ($transaction->entries as $entry){{ $entry->account->account_key }}: {{ ucfirst($entry->direction) }} {{ $entry->amount }}@if (! $loop->last) · @endif @endforeach</small></td><td>{{ $transaction->currency }}</td><td>{{ $transaction->entries->count() }} balanced entries</td><td>{{ $transaction->posted_at->format('M j, Y H:i') }}</td></tr>
            @empty<tr><td colspan="6">No ledger entries have been posted.</td></tr>@endforelse
        </tbody></table></div><div class="model-pagination">{{ $transactions->links() }}</div>
    </section>
</div></section>
@endsection
