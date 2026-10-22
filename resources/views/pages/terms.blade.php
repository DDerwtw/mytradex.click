@extends('layouts.app')

@section('title', 'Terms of use')

@section('content')
<section class="page-section"><div class="container narrow-container legal-copy">
    <div class="eyebrow">Legal information</div><h1 class="page-title">Terms of use</h1>
    <div class="draft-banner">Draft information. The operator must review and approve these terms for the jurisdictions and services offered before launch.</div>
    <h2>Using this website</h2><p>By creating an account, you agree to use the site lawfully, provide accurate information, protect your sign-in details, and follow any service-specific rules presented before you use a service.</p>
    <h2>Services and availability</h2><p>Features may be unavailable, changed, or withdrawn. A feature is not available merely because it appears in future plans or product descriptions. We will show its actual status in the application.</p>
    <h2>No financial advice or guaranteed outcomes</h2><p>Information on this website is general product information, not personal financial, investment, tax, or legal advice. No return, profit, asset value, or account balance is promised.</p>
    <h2>Risk and eligibility</h2><p>Digital assets are volatile and you may lose some or all of the amount you commit. Services may be restricted by location, verification status, or other applicable requirements. Review the <a href="{{ route('legal.risk') }}">Risk Disclosure</a> before use.</p>
    <h2>Contact and changes</h2><p>These draft terms must be replaced with approved operator details, governing-law information, complaint channels, and effective dates before this application is launched.</p>
</div></section>
@endsection
