@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
<section class="auth-section"><div class="auth-card">
    <div class="eyebrow">Account recovery</div><h1>Reset your password</h1><p class="auth-intro">Enter your email address. If mail delivery is configured, we’ll send a reset link.</p>
    @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}" class="form-stack">@csrf
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <button class="button button-primary button-wide" type="submit">Send reset link</button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Return to log in</a></p>
</div></section>
@endsection
