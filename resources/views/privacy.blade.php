@extends('layout')

@section('title', 'Privacy Policy — KayXchange')

@push('styles')
<style>
:root {
    --kx-green:#00cc00; --kx-dark:#0d1117; --kx-card:#161b27;
    --kx-card2:#1e2535; --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0; --kx-muted:#7a8599;
}
body { background:var(--kx-dark); color:var(--kx-text); font-family:'Poppins',sans-serif; }

/* Hero */
.privacy-hero {
    background: linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom: 1px solid var(--kx-border);
    padding: 2.5rem 1rem 2rem; text-align:center; margin-bottom:2rem; position:relative; overflow:hidden;
}
.privacy-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(0,204,0,.15),transparent 70%);
    pointer-events:none;
}
.privacy-hero-icon {
    width:64px; height:64px; border-radius:50%;
    background:rgba(0,204,0,.1); border:1px solid rgba(0,204,0,.25);
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; color:var(--kx-green); margin:0 auto 1rem;
}
.privacy-hero h1 { font-size:1.7rem; font-weight:700; color:#fff; margin:0 0 .4rem; }
.privacy-hero p  { color:var(--kx-muted); font-size:.88rem; margin:0; }

/* Content */
.privacy-content { max-width:800px; margin:0 auto; padding:0 1rem 3rem; }
.privacy-section { margin-bottom:2rem; }
.privacy-section h2 { font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:.75rem; border-bottom:1px solid var(--kx-border); padding-bottom:.5rem; }
.privacy-section p { line-height:1.6; color:var(--kx-text); margin-bottom:1rem; }
.privacy-section ul { margin-left:1.5rem; margin-bottom:1rem; }
.privacy-section li { line-height:1.5; color:var(--kx-text); margin-bottom:.5rem; }
</style>
@endpush

@section('content')
<div class="privacy-hero">
    <div class="privacy-hero-icon">
        <i class="bi bi-shield-check"></i>
    </div>
    <h1>Privacy Policy</h1>
    <p>Last updated: {{ date('F j, Y') }}</p>
</div>

<div class="privacy-content">
    <div class="privacy-section">
        <h2>1. Introduction</h2>
        <p>Welcome to KayXchange. We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our cryptocurrency trading platform.</p>
    </div>

    <div class="privacy-section">
        <h2>2. Information We Collect</h2>
        <p>We collect information you provide directly to us, such as:</p>
        <ul>
            <li>Personal identification information (name, email, phone number)</li>
            <li>Financial information (bank details, wallet addresses)</li>
            <li>Transaction data and trading history</li>
            <li>Verification documents for KYC purposes</li>
            <li>Communication records with our support team</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>3. How We Use Your Information</h2>
        <p>Your information is used to:</p>
        <ul>
            <li>Provide and maintain our trading services</li>
            <li>Verify your identity and prevent fraud</li>
            <li>Process transactions securely</li>
            <li>Communicate with you about your account and transactions</li>
            <li>Comply with legal and regulatory requirements</li>
            <li>Improve our services and develop new features</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>4. Data Security</h2>
        <p>We implement industry-standard security measures to protect your data:</p>
        <ul>
            <li>Encryption of sensitive data in transit and at rest</li>
            <li>Secure server infrastructure</li>
            <li>Regular security audits and updates</li>
            <li>Limited access to personal data on a need-to-know basis</li>
            <li>Multi-factor authentication for account access</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>5. Data Sharing</h2>
        <p>We do not sell or rent your personal information to third parties. We may share your data only in the following circumstances:</p>
        <ul>
            <li>With your explicit consent</li>
            <li>To comply with legal obligations</li>
            <li>To prevent fraud or illegal activities</li>
            <li>With trusted service providers who assist our operations (under strict confidentiality agreements)</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>6. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
            <li>Access your personal data</li>
            <li>Correct inaccurate information</li>
            <li>Request deletion of your data (subject to legal requirements)</li>
            <li>Object to certain data processing</li>
            <li>Data portability</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>7. Cookies and Tracking</h2>
        <p>We use cookies and similar technologies to enhance your experience on our platform. You can control cookie settings through your browser preferences.</p>
    </div>

    <div class="privacy-section">
        <h2>8. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy or our data practices, please contact us through our support channels or email us at privacy@kayxchange.com.</p>
    </div>
</div>
@endsection