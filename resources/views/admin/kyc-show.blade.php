@extends('layouts.app')

@section('title', 'Review verification')

@section('content')
<section class="page-section"><div class="container narrow-container">
    <div class="eyebrow">Administrator · Private record</div><h1 class="page-title">Review application</h1>
    <div class="dashboard-panel kyc-status-panel">
        <div class="account-meta"><span>Applicant</span><strong>{{ $submission->user->name }}</strong></div>
        <div class="account-meta"><span>Email</span><strong>{{ $submission->user->email }}</strong></div>
        <div class="account-meta"><span>Identity document type</span><strong>{{ ucfirst(str_replace('_', ' ', $submission->document_type)) }}</strong></div>
        <div class="account-meta"><span>Submitted</span><strong>{{ $submission->submitted_at?->format('M j, Y g:i A') }}</strong></div>
        <div class="account-meta"><span>Status</span><strong>{{ ucfirst($submission->status) }} @if ($submission->status === 'approved')<x-kyc-badge :verified="true" />@endif</strong></div>
        <div class="account-meta"><span>USDT TRC20 maturity withdrawal wallet</span><strong class="hash-cell">{{ $submission->withdrawal_wallet_address ?: 'Not supplied' }}</strong></div>
        <div class="kyc-document-links"><a class="button button-outline" href="{{ route('admin.kyc.document', $submission) }}">Open identity document</a>@if ($submission->address_proof_path)<a class="button button-outline" href="{{ route('admin.kyc.attachment', [$submission, 'address-proof']) }}">Open address proof</a>@endif @if ($submission->face_photo_path)<a class="button button-outline" href="{{ route('admin.kyc.attachment', [$submission, 'face-photo']) }}">Open face photo</a>@endif</div>
    </div>
    @if ($submission->status === 'pending')
        <form method="POST" action="{{ route('admin.kyc.review', $submission) }}" class="auth-card kyc-form">@csrf
            <h2>Record a decision</h2><label for="decision">Decision</label><select id="decision" name="decision" required><option value="approved">Approve</option><option value="rejected">Reject</option></select>
            <label for="reason">Reason (required when rejecting)</label><textarea id="reason" name="reason" maxlength="1000" rows="4"></textarea>@error('reason')<small class="field-error">{{ $message }}</small>@enderror
            <button class="button button-primary button-wide" type="submit">Save decision</button>
        </form>
    @endif
    @if ($submission->reviews->isNotEmpty())
        <section class="dashboard-panel admin-recent"><div class="eyebrow">Audit history</div><h2>Recorded reviews</h2>
            @foreach ($submission->reviews as $review)<div class="account-meta"><span>{{ ucfirst($review->decision) }} by {{ $review->reviewer->name }}<small class="review-time">{{ $review->created_at?->format('M j, Y g:i A') }}</small></span><strong>{{ $review->reason ?: 'No reason recorded' }}</strong></div>@endforeach
        </section>
    @endif
    <p class="auth-switch"><a href="{{ route('admin.kyc.index') }}">Return to review queue</a></p>
</div></section>
@endsection
