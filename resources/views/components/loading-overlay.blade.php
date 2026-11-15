<div id="loader" class="kx-loader kx-loader--{{ $siteLoaderTheme ?? 'classic' }}" aria-hidden="true">
    <div class="kx-loader__panel kx-loader__classic" role="status" aria-live="polite" aria-label="Loading">
        <div class="kx-loader__brand"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="" width="34" height="34"><span>Kay<span style="color:#35e878">Xchange</span></span></div>
        <div class="kx-loader__ring" aria-hidden="true"></div>
        <p class="kx-loader__title">Loading…</p>
        <p class="kx-loader__hint">Please wait</p>
    </div>
    <div class="kx-loader__panel kx-loader__festive" role="status" aria-live="polite" aria-label="KayXchange festive introduction">
        <span class="kx-snow">✦</span><span class="kx-snow">✧</span><span class="kx-snow">✦</span><span class="kx-snow">✧</span>
        <div class="kx-loader__brand"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="" width="34" height="34"><span>Kay<span style="color:#35e878">Xchange</span></span></div>
        <div class="kx-festive-scene" aria-hidden="true">
            <span class="kx-festive-star">✦</span><div class="kx-festive-tree"><i></i><i></i><i></i></div>
            <span class="kx-bauble"></span><span class="kx-bauble"></span><span class="kx-bauble"></span>
        </div>
        <p class="kx-loader__title">Welcome to 2027</p>
        <p class="kx-loader__hint">A bright new year. A little festive magic.</p>
        <div class="kx-festive-progress" aria-hidden="true"><i></i></div>
    </div>
</div>
<script>
window.kxLoading = window.kxLoading || {
    show(message, hint) {
        const overlay = document.getElementById('loader');
        if (!overlay) return;
        const title = overlay.querySelector('.kx-loader__title');
        const subtitle = overlay.querySelector('.kx-loader__hint');
        if (title) title.textContent = message || (overlay.classList.contains('kx-loader--festive') ? 'Welcome to 2027' : 'Loading…');
        if (subtitle) subtitle.textContent = hint || (overlay.classList.contains('kx-loader--festive') ? 'A bright new year. A little festive magic.' : 'Please wait');
        overlay.style.display = 'block';
        overlay.setAttribute('aria-hidden', 'false');
    },
    hide() {
        const overlay = document.getElementById('loader');
        if (!overlay) return;
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
    }
};
// A short seasonal splash appears once per browser tab session. It never repeats
// on every navigation and respects the user's reduced-motion preference.
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('loader');
    if (!overlay?.classList.contains('kx-loader--festive')) return;
    try {
        if (sessionStorage.getItem('kx-festive-intro-2027')) return;
        sessionStorage.setItem('kx-festive-intro-2027', '1');
    } catch (_) { /* session storage can be unavailable in private browsing */ }
    window.kxLoading.show();
    window.setTimeout(() => window.kxLoading.hide(), matchMedia('(prefers-reduced-motion: reduce)').matches ? 900 : 2400);
});
</script>
