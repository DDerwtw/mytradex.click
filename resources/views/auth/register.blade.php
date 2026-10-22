@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<section class="auth-section"><div class="auth-card">
    <div class="eyebrow">Get started</div><h1>Create your account</h1><p class="auth-intro">We’ll send a verification link to confirm your email address.</p>
    @if ($invalidReferralLink)<div class="form-alert" role="status">That referral link is not valid. You can continue without a referral.</div>@endif
    @if ($referralCode)<div class="form-note">Referral code applied: <strong>{{ $referralCode }}</strong></div>@endif
    @if ($errors->any())<div class="form-alert" role="alert">Please review the highlighted fields and try again.</div>@endif
    <form method="POST" action="{{ route('register.store') }}" class="form-stack">@csrf
        <input type="hidden" name="referral_code" value="{{ old('referral_code', $referralCode) }}">
        @error('referral_code')<small class="field-error">{{ $message }}</small>@enderror
        <label for="name">Full name</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required><small class="field-hint">Use at least 12 characters.</small>@error('password')<small class="field-error">{{ $message }}</small>@enderror
        <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required>
        <label class="checkbox-row"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))> <span>I have read and accept the <a href="{{ route('legal.terms') }}">Terms</a> and <a href="{{ route('legal.risk') }}">Risk Disclosure</a>.</span></label>@error('terms')<small class="field-error">{{ $message }}</small>@enderror
        <button class="button button-primary button-wide" type="submit">Create account</button>
    </form>
    <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Log in</a></p>
</div></section>
@endsection
