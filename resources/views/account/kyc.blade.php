@extends('layouts.app')

@section('title', 'KYC Verification Review')

@section('content')
<section class="page-section"><div class="container narrow-container">
    <div class="eyebrow">Account verification</div><h1 class="page-title">KYC Verification Review</h1>
    <p class="page-lede">Submit the required identity and address documents, a clear face photo, and your USDT TRC20 withdrawal wallet. An administrator reviews every submission.</p>
    @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
    @if (session('error'))<div class="flash flash-error" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="form-alert" role="alert"><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if ($submission)
        <div class="dashboard-panel kyc-status-panel">
            <div class="dashboard-panel-heading"><div><div class="eyebrow">Current application</div><h2>{{ ucfirst(str_replace('_', ' ', $submission->status)) }} @if ($submission->status === 'approved')<x-kyc-badge :verified="true" />@endif</h2></div><span class="status-pill {{ $submission->status === 'approved' ? 'status-ready' : '' }}">{{ $submission->status === 'pending' ? 'In review' : ucfirst($submission->status) }}</span></div>
            <div class="account-meta"><span>Identity document</span><strong>{{ ucfirst(str_replace('_', ' ', $submission->document_type)) }}</strong></div>
            <div class="account-meta"><span>Address proof</span><strong>{{ $submission->address_proof_path ? 'Submitted' : 'Not included in this earlier application' }}</strong></div>
            <div class="account-meta"><span>Face photo</span><strong>{{ $submission->face_photo_path ? 'Submitted' : 'Not included in this earlier application' }}</strong></div>
            <div class="account-meta"><span>USDT TRC20 withdrawal wallet</span><strong>{{ $submission->withdrawal_wallet_address ?: 'Not included in this earlier application' }}</strong></div>
            <div class="account-meta"><span>Submitted</span><strong>{{ $submission->submitted_at?->format('M j, Y g:i A') }}</strong></div>
            @if ($submission->status === 'rejected' && $submission->rejection_reason)<div class="form-alert" role="alert"><strong>Resubmission requested:</strong> {{ $submission->rejection_reason }}</div>@endif
        </div>
    @endif

    @if ($changeRequest && in_array($changeRequest->status, ['pending', 'approved'], true) && ! $changeRequest->fulfilled_at)
        <div class="dashboard-panel kyc-status-panel"><div class="dashboard-panel-heading"><div><div class="eyebrow">KYC detail change ticket</div><h2>{{ $changeRequest->status === 'pending' ? 'Waiting for administrator approval' : 'Approved — updated details required' }}</h2></div><span class="status-pill">{{ ucfirst($changeRequest->status) }}</span></div><p>{{ $changeRequest->reason }}</p>@if ($changeRequest->status === 'approved')<p class="dashboard-muted">Submit the complete updated KYC documents below. Your current approved details remain unchanged until the new submission is reviewed.</p>@endif</div>
    @elseif ($changeRequest && $changeRequest->status === 'rejected')
        <div class="form-alert"><strong>Change request declined:</strong> {{ $changeRequest->admin_note ?: 'You may submit a new request with more details.' }}</div>
    @endif

    @if ($canSubmit)
        <div class="draft-banner"><strong>Privacy notice:</strong> Identity documents, face photos, and wallet addresses are sensitive information. This development version stores files in private local storage, but production retention, encryption, and operational access controls are not configured. Use test documents only until production security is reviewed.</div>
        <form method="POST" action="{{ route('account.kyc.store') }}" enctype="multipart/form-data" class="auth-card kyc-form">@csrf
            <h2>Required verification details</h2>
            <label for="document_type">Identity document type</label>
            <select id="document_type" name="document_type" required><option value="">Choose a document type</option><option value="passport">Passport</option><option value="national_id">National identity card</option><option value="drivers_license">Driver's licence</option></select>
            @error('document_type')<small class="field-error">{{ $message }}</small>@enderror
            <label for="document">Identity document file</label><input id="document" name="document" type="file" accept=".jpg,.jpeg,.png,.pdf" required><small class="field-hint">JPG, PNG, or PDF; maximum 5 MB.</small>
            @error('document')<small class="field-error">{{ $message }}</small>@enderror
            <label for="address_proof">Address proof document</label><input id="address_proof" name="address_proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required><small class="field-hint">A document showing your name and current address. JPG, PNG, or PDF; maximum 5 MB.</small>
            @error('address_proof')<small class="field-error">{{ $message }}</small>@enderror
            <label for="face_photo">Clear face photo</label><input id="face_photo" name="face_photo" type="file" accept="image/jpeg,image/png,image/webp" required><small class="field-hint">JPG, PNG, or WebP; maximum 5 MB. Face must be clearly visible, without accessories covering it.</small>
            @error('face_photo')<small class="field-error">{{ $message }}</small>@enderror
            <label for="withdrawal_wallet_address">Maturity withdrawal wallet address (USDT TRC20)</label><input id="withdrawal_wallet_address" name="withdrawal_wallet_address" type="text" minlength="34" maxlength="34" pattern="T[1-9A-HJ-NP-Za-km-z]{33}" autocomplete="off" required value="{{ old('withdrawal_wallet_address') }}"><small class="field-hint">Enter the TRON (TRC20) address carefully. It will be locked after approval. This only records your address; withdrawals are not currently enabled.</small>
            @error('withdrawal_wallet_address')<small class="field-error">{{ $message }}</small>@enderror
            <button class="button button-primary button-wide" type="submit">Submit complete KYC for review</button>
        </form>
    @elseif ($canRequestChange)
        <div class="dashboard-panel kyc-status-panel"><div class="eyebrow">Need to update approved information?</div><h2>Ask an administrator first</h2><p>Approved KYC documents and the withdrawal wallet address are locked. Explain what you need changed below. You can submit updated information only after the administrator approves this ticket.</p>
            <form method="POST" action="{{ route('account.kyc.change-requests.store') }}" class="auth-card kyc-form">@csrf<label for="reason">Why do you need to change your KYC information?</label><textarea id="reason" name="reason" rows="4" minlength="10" maxlength="1000" required>{{ old('reason') }}</textarea>@error('reason')<small class="field-error">{{ $message }}</small>@enderror<button class="button button-primary button-wide" type="submit">Send change request to administrator</button></form>
        </div>
    @endif
    <p class="auth-switch"><a href="{{ route('dashboard') }}">Return to dashboard</a></p>
</div></section>
@endsection
