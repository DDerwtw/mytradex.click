<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07111d">
    <meta name="description" content="MyTradeX is a digital asset platform. Explore the product, its risks, and account access.">
    <title>@yield('title', 'MyTradeX') · MyTradeX</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @if (request()->routeIs('home'))<link rel="stylesheet" href="{{ asset('css/mockup-home.css') }}">@elseif (request()->routeIs('dashboard', 'account.*', 'admin.*', 'markets', 'login', 'register', 'password.*', 'verification.notice'))<link rel="stylesheet" href="{{ asset('css/mockup-pages.css') }}">@endif
</head>
<body>
    @if (request()->routeIs('home'))
    <header class="site-header mockup-site-header">
        <div class="mockup-nav-wrap">
            <a class="mockup-brand" href="{{ route('home') }}" aria-label="MyTradeX home"><span class="mockup-brand-mark" aria-hidden="true"><i></i><i></i><i></i><i></i></span><span><strong>mytradex<span>.click</span></strong><small>Explore · Learn · Grow carefully</small></span></a>
            <nav class="mockup-nav" aria-label="Main navigation"><a href="{{ route('home') }}">Home</a><a href="{{ route('home') }}#packages">Platform</a><a href="{{ route('home') }}#how-it-works">How It Works</a><a href="{{ route('home') }}#referrals">Referral</a><a href="{{ route('home') }}#about">About</a><a href="{{ route('home') }}#faq">FAQ</a></nav>
            <div class="mockup-nav-actions">
                @auth
                    @if (auth()->user()->role === 'admin')<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
                    @if (auth()->user()->hasVerifiedEmail())<a href="{{ route('dashboard') }}">Dashboard</a>@endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="mockup-login" type="submit">Sign out</button></form>
                @else
                    <a class="mockup-login" href="{{ route('login') }}">Login</a><a class="mockup-signup" href="{{ route('register') }}">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>
    @else
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}" aria-label="MyTradeX home">
                <span class="brand-mark" aria-hidden="true">▥</span>
                <span>mytradex<span class="brand-accent">.click</span></span>
            </a>
            <nav class="desktop-nav" aria-label="Main navigation">
                <a href="{{ route('markets') }}">Markets</a>
                <a href="{{ route('home') }}#how-it-works">How it works</a>
                <a href="{{ route('home') }}#security">Security</a>
                <a href="{{ route('legal.risk') }}">Risk disclosure</a>
            </nav>
            <div class="nav-actions">
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a class="button button-quiet" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                    @if (auth()->user()->hasVerifiedEmail())
                        <a class="button button-quiet" href="{{ route('dashboard') }}">Dashboard</a>
                        <a class="button button-quiet" href="{{ route('account.referrals') }}">Referrals</a>
                    @else
                        <a class="button button-quiet" href="{{ route('verification.notice') }}">Verify email</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-outline" type="submit">Sign out</button></form>
                @else
                    <a class="button button-quiet" href="{{ route('login') }}">Log in</a>
                    <a class="button button-primary button-small" href="{{ route('register') }}">Create account</a>
                @endauth
            </div>
            <details class="mobile-nav">
                <summary aria-label="Open navigation"><span></span><span></span><span></span></summary>
                <nav aria-label="Mobile navigation">
                    <a href="{{ route('markets') }}">Markets</a>
                    <a href="{{ route('home') }}#how-it-works">How it works</a>
                    <a href="{{ route('home') }}#security">Security</a>
                    <a href="{{ route('legal.risk') }}">Risk disclosure</a>
                    @auth
                        @if (auth()->user()->role === 'admin')<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
                        @if (auth()->user()->hasVerifiedEmail())<a href="{{ route('dashboard') }}">Dashboard</a>@else<a href="{{ route('verification.notice') }}">Verify email</a>@endif
                    @else
                        <a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Create account</a>
                    @endauth
                </nav>
            </details>
        </div>
    </header>
    @endif

    @if (session('status'))
        <div class="container flash flash-success" role="status">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="container flash flash-error" role="alert">{{ session('error') }}</div>
    @endif

    <main>@yield('content')</main>

    @if (! request()->routeIs('home'))
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">▥</span><span>mytradex<span class="brand-accent">.click</span></span></a>
                <p class="footer-note">Digital assets involve risk. This site does not promise returns or provide investment advice.</p>
            </div>
            <div class="footer-links">
                <a href="{{ route('legal.terms') }}">Terms</a>
                <a href="{{ route('legal.risk') }}">Risk disclosure</a>
            </div>
            <p class="copyright">© {{ date('Y') }} MyTradeX. Information on this site is subject to change.</p>
        </div>
    </footer>
    @endif
    @stack('scripts')
</body>
</html>
