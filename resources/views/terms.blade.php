@extends('layout')

@section('title', 'Terms of Service — KayXchange')

@push('styles')
<style>
:root {
    --kx-green:#00cc00; --kx-dark:#0d1117; --kx-card:#161b27;
    --kx-card2:#1e2535; --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0; --kx-muted:#7a8599;
}
body { background:var(--kx-dark); color:var(--kx-text); font-family:'Poppins',sans-serif; }

/* Hero */
.terms-hero {
    background: linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom: 1px solid var(--kx-border);
    padding: 2.5rem 1rem 2rem; text-align:center; margin-bottom:2rem; position:relative; overflow:hidden;
}
.terms-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(0,204,0,.15),transparent 70%);
    pointer-events:none;
}
.terms-hero-icon {
    width:64px; height:64px; border-radius:50%;
    background:rgba(0,204,0,.1); border:1px solid rgba(0,204,0,.25);
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; color:var(--kx-green); margin:0 auto 1rem;
}
.terms-hero h1 { font-size:1.7rem; font-weight:700; color:#fff; margin:0 0 .4rem; }
.terms-hero p  { color:var(--kx-muted); font-size:.88rem; margin:0; }

/* Content */
.terms-content { max-width:800px; margin:0 auto; padding:0 1rem 3rem; }
.terms-section { margin-bottom:2rem; }
.terms-section h2 { font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:.75rem; border-bottom:1px solid var(--kx-border); padding-bottom:.5rem; }
.terms-section p { line-height:1.6; color:var(--kx-text); margin-bottom:1rem; }
.terms-section ul { margin-left:1.5rem; margin-bottom:1rem; }
.terms-section li { line-height:1.5; color:var(--kx-text); margin-bottom:.5rem; }
</style>
@endpush

@section('content')
<div class="terms-hero">
    <div class="terms-hero-icon">
        <i class="bi bi-file-earmark-text"></i>
    </div>
    <h1>Terms of Service</h1>
    <p>Last updated: {{ date('F j, Y') }}</p>
</div>

<div class="terms-content">
    <div class="terms-section">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using KayXchange, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
    </div>

    <div class="terms-section">
        <h2>2. Description of Service</h2>
        <p>KayXchange is a cryptocurrency trading platform that facilitates the exchange of cryptocurrencies for Nigerian Naira and other fiat currencies. Our services include buying and selling cryptocurrencies, wallet management, and related financial services.</p>
    </div>

    <div class="terms-section">
        <h2>3. User Eligibility</h2>
        <p>To use our services, you must:</p>
        <ul>
            <li>Be at least 18 years old</li>
            <li>Be a resident of Nigeria or eligible jurisdictions</li>
            <li>Provide accurate and complete information during registration</li>
            <li>Complete identity verification as required</li>
            <li>Not be prohibited from using financial services in your jurisdiction</li>
        </ul>
    </div>

    <div class="terms-section">
        <h2>4. Account Registration and Security</h2>
        <p>You are responsible for:</p>
        <ul>
            <li>Maintaining the confidentiality of your account credentials</li>
            <li>All activities that occur under your account</li>
            <li>Notifying us immediately of any unauthorized use</li>
            <li>Ensuring the security of your devices and internet connection</li>
        </ul>
    </div>

    <div class="terms-section">
        <h2>5. Trading and Transactions</h2>
        <p>When using our trading services:</p>
        <ul>
            <li>All transactions are final once confirmed</li>
            <li>You must provide accurate payment information</li>
            <li>Transaction fees will be clearly displayed before confirmation</li>
            <li>We reserve the right to cancel suspicious transactions</li>
            <li>Cryptocurrency values are volatile and may fluctuate significantly</li>
        </ul>
    </div>

    <div class="terms-section">
        <h2>6. Prohibited Activities</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use the service for any illegal purposes</li>
            <li>Attempt to manipulate exchange rates or engage in market manipulation</li>
            <li>Provide false information or documentation</li>
            <li>Attempt to hack, disrupt, or interfere with our systems</li>
            <li>Use automated tools or bots without permission</li>
            <li>Engage in money laundering or terrorist financing</li>
        </ul>
    </div>

    <div class="terms-section">
        <h2>7. Fees and Charges</h2>
        <p>We charge fees for our services as displayed on our platform. Fees may change with notice. You are responsible for all applicable taxes on your transactions.</p>
    </div>

    <div class="terms-section">
        <h2>8. Risk Disclosure</h2>
        <p>Cryptocurrency trading involves significant risk. You acknowledge that:</p>
        <ul>
            <li>Cryptocurrency values can be highly volatile</li>
            <li>You may lose all invested funds</li>
            <li>Past performance does not guarantee future results</li>
            <li>You trade at your own risk</li>
        </ul>
    </div>

    <div class="terms-section">
        <h2>9. Limitation of Liability</h2>
        <p>KayXchange shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of our services, including but not limited to loss of profits, data, or cryptocurrencies.</p>
    </div>

    <div class="terms-section">
        <h2>10. Termination</h2>
        <p>We reserve the right to suspend or terminate your account at any time for violations of these terms or for other reasons we deem necessary for security or compliance purposes.</p>
    </div>

    <div class="terms-section">
        <h2>11. Governing Law</h2>
        <p>These terms are governed by the laws of Nigeria. Any disputes shall be resolved through Nigerian courts.</p>
    </div>

    <div class="terms-section">
        <h2>12. Changes to Terms</h2>
        <p>We may update these terms at any time. Continued use of our services after changes constitutes acceptance of the new terms.</p>
    </div>

    <div class="terms-section">
        <h2>13. Contact Information</h2>
        <p>For questions about these Terms of Service, please contact us through our support channels.</p>
    </div>
</div>
@endsection