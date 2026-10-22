@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<section class="auth-section"><div class="auth-card">
    <div class="eyebrow">Welcome back</div><h1>Log in to MyTradeX</h1><p class="auth-intro">Use the email address and password linked to your account.</p>
    @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>@error('email')<small class="field-error">{{ $message }}</small>@enderror
        <div class="label-row"><label for="password">Password</label><a href="{{ route('password.request') }}">Forgot password?</a></div><input id="password" name="password" type="password" autocomplete="current-password" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror
        <label class="checkbox-row"><input type="checkbox" name="remember" value="1"> <span>Remember me on this device</span></label>
        <button class="button button-primary button-wide" type="submit">Log in</button>
    </form>
    <p class="auth-switch">New to MyTradeX? <a href="{{ route('register') }}">Create an account</a></p>
</div></section>
@endsection
