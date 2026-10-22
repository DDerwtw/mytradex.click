@extends('layouts.app')

@section('title', 'KYC review queue')

@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="eyebrow">Administrator · Private records</div><h1 class="page-title">KYC Verification Review</h1><p class="page-lede">Review complete identity submissions and user requests to change previously approved KYC information.</p>
    @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="form-alert" role="alert"><strong>Action was not completed.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="dashboard-panel admin-recent"><div class="dashboard-panel-heading"><div><div class="eyebrow">Change tickets</div><h2>Approved KYC detail change requests</h2></div><span class="status-pill">{{ $changeRequests->total() }} pending</span></div>
        @forelse ($changeRequests as $changeRequest)
            <div class="admin-list-row kyc-change-row"><span><strong>{{ $changeRequest->user->name }} · {{ $changeRequest->user->email }}</strong><small>Current approved KYC: {{ ucfirst(str_replace('_', ' ', $changeRequest->submission->document_type)) }} · Withdrawal wallet: {{ $changeRequest->submission->withdrawal_wallet_address ?: 'not previously recorded' }}</small><small>Requested change: {{ $changeRequest->reason }}</small></span><form method="POST" action="{{ route('admin.kyc.change-requests.review', $changeRequest) }}" class="model-review-form">@csrf<label class="sr-only" for="kyc-change-note-{{ $changeRequest->id }}">Administrator note, required when declining</label><input id="kyc-change-note-{{ $changeRequest->id }}" name="admin_note" type="text" maxlength="1000" placeholder="Note (required when declining)"><button class="button button-primary" name="decision" value="approved">Approve</button><button class="button button-outline" name="decision" value="rejected">Decline</button></form></div>
        @empty<div class="empty-state"><strong>No pending change requests</strong><p>There are no KYC change tickets waiting for review.</p></div>@endforelse
        {{ $changeRequests->links() }}
    </section>
    <section class="dashboard-panel admin-recent">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">New and updated submissions</div><h2>Pending KYC review</h2></div></div>
        @forelse ($submissions as $submission)
            <a class="admin-list-row" href="{{ route('admin.kyc.show', $submission) }}"><span><strong>{{ $submission->user->name }}</strong><small>{{ $submission->user->email }} · {{ ucfirst(str_replace('_', ' ', $submission->document_type)) }}</small></span><span class="admin-date">{{ $submission->submitted_at?->format('M j, Y') }}</span></a>
        @empty
            <div class="empty-state"><span>✓</span><strong>No pending applications</strong><p>There are no KYC submissions waiting for review.</p></div>
        @endforelse
        {{ $submissions->links() }}
    </section>
    <p class="auth-switch"><a href="{{ route('admin.dashboard') }}">Return to administrator overview</a></p>
</div></section>
@endsection
