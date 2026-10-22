@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
<section class="auth-section"><div class="auth-card">
    <div class="eyebrow">One more step</div><h1>Verify your email</h1><p class="auth-intro">Your account is created, but dashboard access stays locked until your email address is verified.</p>
    @if ($errors->any())<div class="form-alert" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('verification.send') }}" class="form-stack">@csrf<button class="button button-primary button-wide" type="submit">Send a new verification link</button></form>
    <p class="form-hint">Email delivery depends on the mail service configured for this installation.</p>
    @if (app()->environment('local') && config('mail.default') === 'log')
        <div class="notice-card local-mail-note"><span class="notice-icon" aria-hidden="true">i</span><div><strong>Local testing email</strong><p>This setup records verification messages in <code>storage/logs/laravel.log</code> instead of emailing them. Open that file and use the latest verification link, or configure an email provider.</p></div></div>
    @endif
</div></section>
@endsection
