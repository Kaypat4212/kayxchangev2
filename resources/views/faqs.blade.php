@extends('layout')

@section('title', 'FAQs — KayXchange')

@push('styles')
<style>
:root {
    --kx-green:#00cc00; --kx-dark:#0d1117; --kx-card:#161b27;
    --kx-card2:#1e2535; --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0; --kx-muted:#7a8599;
}
body { background:var(--kx-dark); color:var(--kx-text); }

/* Hero */
.faq-hero {
    background: linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom: 1px solid var(--kx-border);
    padding: 2.5rem 1rem 2rem; text-align:center; margin-bottom:2rem; position:relative; overflow:hidden;
}
.faq-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(0,204,0,.15),transparent 70%);
    pointer-events:none;
}
.faq-hero-icon {
    width:64px; height:64px; border-radius:50%;
    background:rgba(0,204,0,.1); border:1px solid rgba(0,204,0,.25);
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; color:var(--kx-green); margin:0 auto 1rem;
}
.faq-hero h1 { font-size:1.7rem; font-weight:700; color:#fff; margin:0 0 .4rem; }
.faq-hero p  { color:var(--kx-muted); font-size:.88rem; margin:0; }

/* Layout */
.faq-wrap { max-width:760px; margin:0 auto; padding:0 1rem 3rem; }

/* Category pill tabs */
.faq-cats { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.75rem; }
.faq-cat-btn {
    padding:.38rem .9rem; border-radius:20px; font-size:.78rem; font-weight:600;
    border:1px solid var(--kx-border); background:var(--kx-card2); color:var(--kx-muted);
    cursor:pointer; transition:all .2s;
}
.faq-cat-btn:hover { border-color:rgba(0,204,0,.4); color:var(--kx-green); }
.faq-cat-btn.active { background:rgba(0,204,0,.12); border-color:var(--kx-green); color:var(--kx-green); }

/* Search box */
.faq-search-wrap { position:relative; margin-bottom:1.5rem; }
.faq-search-wrap i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--kx-muted); }
.faq-search {
    width:100%; background:var(--kx-card2); border:1px solid var(--kx-border);
    color:var(--kx-text); border-radius:12px; padding:.7rem 1rem .7rem 2.5rem;
    font-size:.88rem; font-family:inherit; transition:border-color .2s;
}
.faq-search::placeholder { color:var(--kx-muted); }
.faq-search:focus { outline:none; border-color:rgba(0,204,0,.5); box-shadow:0 0 0 3px rgba(0,204,0,.08); }

/* Accordion */
.faq-group { margin-bottom:2rem; }
.faq-group-title {
    font-size:.72rem; font-weight:600; color:var(--kx-muted);
    text-transform:uppercase; letter-spacing:.08em;
    margin-bottom:.75rem; display:flex; align-items:center; gap:.45rem;
}
.faq-group-title i { color:var(--kx-green); }

.faq-item {
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:12px; margin-bottom:.5rem; overflow:hidden; transition:border-color .2s;
}
.faq-item:hover { border-color:rgba(0,204,0,.2); }
.faq-item.open { border-color:rgba(0,204,0,.3); }

.faq-q {
    width:100%; text-align:left; background:none; border:none;
    padding:.9rem 1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;
    color:var(--kx-text); font-size:.88rem; font-weight:600; cursor:pointer; font-family:inherit;
}
.faq-q .faq-chevron {
    flex-shrink:0; color:var(--kx-muted); font-size:.85rem; transition:transform .25s;
}
.faq-item.open .faq-chevron { transform:rotate(180deg); color:var(--kx-green); }

.faq-a {
    max-height:0; overflow:hidden; transition:max-height .3s ease, padding .2s;
    padding:0 1rem;
}
.faq-a-inner {
    padding-bottom:1rem; font-size:.84rem; color:var(--kx-muted); line-height:1.7;
    border-top:1px solid var(--kx-border);
    padding-top:.75rem;
}
.faq-a-inner a { color:var(--kx-green); text-decoration:none; }
.faq-a-inner a:hover { text-decoration:underline; }
.faq-item.open .faq-a { max-height:600px; }

/* No results */
.faq-noresult {
    text-align:center; padding:2.5rem 1rem; display:none;
    color:var(--kx-muted); font-size:.88rem;
}
.faq-noresult i { font-size:2rem; display:block; margin-bottom:.5rem; color:var(--kx-muted); }

/* Contact CTA */
.faq-cta {
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; padding:1.75rem; text-align:center; margin-top:2rem;
}
.faq-cta h5 { font-size:1rem; font-weight:700; color:#fff; margin-bottom:.4rem; }
.faq-cta p  { font-size:.83rem; color:var(--kx-muted); margin-bottom:1rem; }
.btn-kx-green {
    background:var(--kx-green); color:#000; font-weight:700; border:none;
    border-radius:10px; padding:.65rem 1.4rem; font-size:.88rem; cursor:pointer;
    text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; transition:all .2s;
}
.btn-kx-green:hover { background:#00e600; transform:translateY(-1px); box-shadow:0 4px 16px rgba(0,204,0,.3); }

/* Light mode */
body.light-mode { background:#f4faf4; color:#1a2a1a; }
body.light-mode .faq-hero { background:linear-gradient(135deg,#e8f5e9,#f0fff0); }
body.light-mode .faq-item,
body.light-mode .faq-cta { background:#fff; border-color:rgba(0,0,0,.08); }
body.light-mode .faq-q  { color:#1a2a1a; }
body.light-mode .faq-a-inner { color:#4a6a4a; border-color:rgba(0,0,0,.08); }
body.light-mode .faq-search  { background:#fff; border-color:rgba(0,0,0,.1); color:#1a2a1a; }
body.light-mode .faq-cat-btn { background:#e8f5e9; border-color:rgba(0,0,0,.1); color:#4a6a4a; }
body.light-mode .faq-cat-btn.active { background:rgba(0,204,0,.15); }
</style>
@endpush

@section('content')

<div class="faq-hero">
    <div class="faq-hero-icon"><i class="bi bi-patch-question-fill"></i></div>
    <h1>Frequently Asked Questions</h1>
    <p>Everything you need to know about KayXchange</p>
</div>

<div class="faq-wrap">

    {{-- Search --}}
    <div class="faq-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" class="faq-search" id="faq-search" placeholder="Search questions…">
    </div>

    {{-- Category pills --}}
    <div class="faq-cats">
        <button class="faq-cat-btn active" data-cat="all">All</button>
        <button class="faq-cat-btn" data-cat="account">Account</button>
        <button class="faq-cat-btn" data-cat="deposit">Deposits</button>
        <button class="faq-cat-btn" data-cat="buy">Buying Crypto</button>
        <button class="faq-cat-btn" data-cat="sell">Selling Crypto</button>
        <button class="faq-cat-btn" data-cat="kyc">Verification</button>
        <button class="faq-cat-btn" data-cat="security">Security</button>
    </div>

    <div id="faq-noresult" class="faq-noresult">
        <i class="bi bi-search"></i>
        No questions matched your search. Try different keywords.
    </div>

    {{-- ── ACCOUNT ─────────────────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="account">
        <div class="faq-group-title"><i class="bi bi-person-circle"></i> Account &amp; Registration</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How do I create a KayXchange account?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Visit our <a href="{{ url('/register') }}">registration page</a>, enter your name, email address, and a strong password. After signing up, complete your email verification and KYC to unlock all features.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Can I have more than one account?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                No. KayXchange allows only one account per individual. Operating multiple accounts is a violation of our terms and may result in all accounts being permanently suspended.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                I forgot my password. How do I reset it?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Click <a href="{{ url('/forgot-password') }}">Forgot Password</a> on the login page, enter your registered email address, and follow the reset link sent to your inbox. Check your spam folder if the email doesn't arrive within a few minutes.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How do I update my profile information?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Go to <strong>Dashboard → Profile Settings</strong>. You can update your phone number, bank account details, and notification preferences at any time. Some fields (name, email) may require identity re-verification.
            </div></div>
        </div>
    </div>

    {{-- ── DEPOSITS ─────────────────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="deposit">
        <div class="faq-group-title"><i class="bi bi-wallet2"></i> Deposits &amp; Funding</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What payment methods can I use to fund my wallet?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                You can fund your wallet via:
                <ul style="margin:.5rem 0 0 1rem;padding:0;">
                    <li><strong>Bank Transfer</strong> — Transfer to our company account and upload a proof of payment. Approved within business hours.</li>
                    <li><strong>Paystack</strong> — Pay instantly with debit/credit card, USSD, or bank transfer.</li>
                    <li><strong>Korapay</strong> — Card and bank transfer payments.</li>
                    <li><strong>Flutterwave</strong> — Card, bank transfer, and mobile money payments.</li>
                </ul>
                Paystack, Korapay, and Flutterwave deposits are credited <strong>automatically and instantly</strong> once payment is confirmed.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What is the minimum deposit amount?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                The minimum deposit is <strong>₦1,000</strong>. There is no maximum limit, though very large deposits may require additional verification.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How long does a bank transfer deposit take?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Bank transfer deposits are reviewed manually by our team. They are typically approved within <strong>15–60 minutes</strong> during business hours (Monday–Friday, 8 AM–8 PM WAT). You'll receive a notification once your deposit is approved.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                I made a deposit but my wallet wasn't credited. What do I do?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                For <strong>automatic gateway payments</strong> (Paystack, Korapay, Flutterwave): if you were charged but your wallet wasn't credited, please contact support with your transaction reference number — we'll resolve it promptly.<br><br>
                For <strong>bank transfers</strong>: ensure you uploaded a clear proof of payment. If more than 2 hours have passed, contact support with your transaction reference.
            </div></div>
        </div>
    </div>

    {{-- ── BUY CRYPTO ───────────────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="buy">
        <div class="faq-group-title"><i class="bi bi-arrow-down-circle"></i> Buying Crypto</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Which cryptocurrencies can I buy?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                KayXchange supports buying <strong>Bitcoin (BTC)</strong>, <strong>Ethereum (ETH)</strong>, and <strong>USDT (TRC20 &amp; ERC20)</strong>. New coins are added periodically — check the Buy page for the latest list.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How does the buy process work?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                <ol style="margin:.5rem 0 0 1rem;padding:0;line-height:2;">
                    <li>Enter the amount in USD or NGN you want to buy.</li>
                    <li>Provide your wallet address and choose the network.</li>
                    <li>Review the trade summary.</li>
                    <li>Transfer the naira equivalent to our company bank account and upload proof of payment.</li>
                    <li>Our team verifies payment and sends crypto to your wallet — typically within 30 minutes.</li>
                </ol>
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How long does it take to receive my crypto?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                After payment verification, crypto is sent to your wallet within <strong>15–30 minutes</strong> during business hours. Network congestion may occasionally cause delays beyond our control.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Can I cancel a buy trade after submitting?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Cancellations are only possible <strong>before payment is uploaded</strong>. Once you have submitted proof of payment and the trade is under review, it cannot be cancelled. Please contact support if you have an urgent issue.
            </div></div>
        </div>
    </div>

    {{-- ── SELL CRYPTO ──────────────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="sell">
        <div class="faq-group-title"><i class="bi bi-arrow-up-circle"></i> Selling Crypto</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How do I sell my crypto on KayXchange?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Go to <strong>Sell Crypto</strong>, select the coin and enter the amount you want to sell. You'll receive a wallet address to send your crypto to. Once we confirm receipt on-chain, the naira equivalent is paid to your linked bank account or wallet balance.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How fast are sell trade payouts?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Payouts are processed within <strong>30–60 minutes</strong> after we confirm the blockchain transaction. For high-value trades, it may take up to 2 hours.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What happens if I send the wrong coin or wrong amount?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Sending the wrong coin or amount may result in permanent loss of funds. KayXchange is <strong>not liable</strong> for user errors of this nature. Always double-check the wallet address and coin type before sending. Contact support immediately if you believe you've made an error.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What is the minimum amount I can sell?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                The minimum sell amount varies by coin. Generally, the minimum naira equivalent is <strong>₦2,000</strong>. The exact minimum for each coin is displayed on the Sell page.
            </div></div>
        </div>
    </div>

    {{-- ── KYC / VERIFICATION ───────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="kyc">
        <div class="faq-group-title"><i class="bi bi-shield-check"></i> Identity Verification (KYC)</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Why do I need to verify my identity?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                KYC (Know Your Customer) verification is required by financial regulations to prevent fraud, money laundering, and other illegal activities. It also ensures a safe trading environment for all users.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                What documents are accepted for KYC?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                We accept:
                <ul style="margin:.5rem 0 0 1rem;padding:0;">
                    <li>National Identity Card (NIN slip or physical card)</li>
                    <li>International Passport (data page)</li>
                    <li>Driver's License (front and back)</li>
                    <li>Voter's Card (PVC)</li>
                </ul>
                Documents must be valid, clearly legible, and must match your registered name.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How long does KYC approval take?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                KYC is typically reviewed within <strong>1–24 hours</strong>. You'll receive an email notification once your verification is approved or if additional information is required.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                My KYC was rejected. What should I do?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Re-submit with a clearer image of your document. Ensure:
                <ul style="margin:.5rem 0 0 1rem;padding:0;">
                    <li>The document is not expired</li>
                    <li>All four corners are visible</li>
                    <li>There is no glare or blurriness</li>
                    <li>The name matches exactly what you registered with</li>
                </ul>
                If the issue persists, contact our support team.
            </div></div>
        </div>
    </div>

    {{-- ── SECURITY ─────────────────────────────────────────────────────── --}}
    <div class="faq-group" data-cat="security">
        <div class="faq-group-title"><i class="bi bi-lock-fill"></i> Security</div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                Is KayXchange safe to use?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Yes. KayXchange uses industry-standard security measures including encrypted connections (HTTPS/TLS), hashed password storage (bcrypt), CSRF protection, and session-based authentication. We never store full card details.
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                How do I keep my account secure?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                <ul style="margin:.5rem 0 0 1rem;padding:0;line-height:2;">
                    <li>Use a strong, unique password</li>
                    <li>Never share your login credentials with anyone</li>
                    <li>Always log out on shared devices</li>
                    <li>Be wary of phishing emails — we will <strong>never</strong> ask for your password via email or phone</li>
                    <li>Contact support immediately if you notice suspicious activity</li>
                </ul>
            </div></div>
        </div>

        <div class="faq-item">
            <button class="faq-q" onclick="toggleFaq(this)">
                I think my account has been compromised. What should I do?
                <i class="bi bi-chevron-down faq-chevron"></i>
            </button>
            <div class="faq-a"><div class="faq-a-inner">
                Immediately <a href="{{ url('/forgot-password') }}">reset your password</a> and contact our support team. Provide as much detail as possible so we can investigate and secure your account.
            </div></div>
        </div>
    </div>

    {{-- Contact CTA --}}
    <div class="faq-cta">
        <h5>Still have questions?</h5>
        <p>Our support team is available to help you — reach out any time.</p>
        <a href="{{ url('/contact') }}" class="btn-kx-green">
            <i class="bi bi-chat-dots-fill"></i> Contact Support
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Accordion ─────────────────────────────────────────────────
    window.toggleFaq = function (btn) {
        const item = btn.closest('.faq-item');
        const isOpen = item.classList.contains('open');
        // Close all
        document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
    };

    // ── Category filter ───────────────────────────────────────────
    document.querySelectorAll('.faq-cat-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.faq-cat-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cat = this.dataset.cat;
            document.querySelectorAll('.faq-group').forEach(g => {
                g.style.display = (cat === 'all' || g.dataset.cat === cat) ? '' : 'none';
            });
            document.getElementById('faq-search').value = '';
            showNoResults(false);
        });
    });

    // ── Search ────────────────────────────────────────────────────
    document.getElementById('faq-search').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        // Reset category filter
        document.querySelectorAll('.faq-cat-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.faq-cat-btn[data-cat="all"]').classList.add('active');
        document.querySelectorAll('.faq-group').forEach(g => g.style.display = '');

        document.querySelectorAll('.faq-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            const match = !q || text.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // Show/hide group titles when all items hidden
        document.querySelectorAll('.faq-group').forEach(g => {
            const anyVisible = [...g.querySelectorAll('.faq-item')].some(i => i.style.display !== 'none');
            g.style.display = anyVisible ? '' : 'none';
        });

        showNoResults(visible === 0 && q !== '');
    });

    function showNoResults(show) {
        document.getElementById('faq-noresult').style.display = show ? 'block' : 'none';
    }
})();
</script>
@endpush
