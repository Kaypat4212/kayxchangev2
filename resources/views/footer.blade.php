<!-- Footer Section -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-12 col-md-4 text-center text-md-start mb-3 mb-md-0">
                <h5 class="fw-bold text-green mb-3">Quick Links</h5>
                <ul class="list-unstyled d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                    <li><a href="{{ route('dashboard') }}" class="text-success text-decoration-none">Dashboard</a></li>
                    <li><a href="{{ route('buy') }}" class="text-success text-decoration-none">Buy Crypto</a></li>
                    <li><a href="{{ route('sell.form') }}" class="text-success text-decoration-none">Sell Crypto</a></li>
                    <li><a href="{{ route('transactions.history') }}" class="text-success text-decoration-none">Transactions</a></li>
                    <li><a href="{{ route('feature.request.form') }}" class="text-success text-decoration-none">Feature Request</a></li>
                </ul>
            </div>
            <div class="col-12 col-md-4 text-center mb-3 mb-md-0">
                <h5 class="fw-bold text-green mb-3">Contact Us</h5>
                <p class="mb-1"><i class="bi bi-envelope-fill me-2"></i>support@kayxchange.net</p>
                <p class="mb-0"><i class="bi bi-telephone-fill me-2"></i>+2349016740523</p>
            </div>
            <div class="col-12 col-md-4 text-center text-md-end">
                <p class="mb-0">© {{ date('Y') }} Kay Xchange. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>