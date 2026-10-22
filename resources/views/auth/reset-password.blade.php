@extends('layouts.app')

@section('title', 'Choose a new password')

@section('content')
<section class="auth-section"><div class="auth-card">
    <div class="eyebrow">Account recovery</div><h1>Choose a new password</h1><p class="auth-intro">Create a new password of at least 12 characters.</p>
    @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="form-stack">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
        <label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required>
        <label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required>
        <button class="button button-primary button-wide" type="submit">Save new password</button>
    </form>
</div></section>
@endsection
