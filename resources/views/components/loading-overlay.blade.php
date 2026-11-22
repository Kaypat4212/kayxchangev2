<div id="loader" class="kx-loader kx-loader--{{ $siteLoaderTheme ?? 'classic' }}" aria-hidden="true">
    <div class="kx-loader__panel kx-loader__classic" role="status" aria-live="polite" aria-label="Loading">
        <div class="kx-loader__brand"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="" width="34" height="34"><span>Kay<span style="color:#35e878">Xchange</span></span></div>
        <p class="kx-loader__title">Trade with Kay</p>
        <p class="kx-loader__hint">Fast · Secure · Best Rates</p>
        <div class="kx-loader__coins" aria-hidden="true"><span>₿</span><span>Ξ</span><span>₮</span></div>
        <div class="kx-classic-progress" aria-hidden="true"><i></i></div>
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
    <div class="kx-loader__panel kx-loader__christmas" role="status" aria-live="polite" aria-label="KayXchange Christmas loader">
        <span class="kx-snow">✦</span><span class="kx-snow">✧</span><span class="kx-snow">✦</span><span class="kx-snow">✧</span>
        <div class="kx-loader__brand"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="" width="34" height="34"><span>Kay<span style="color:#35e878">Xchange</span></span></div>
        <div class="kx-christmas-scene" aria-hidden="true">
            <span class="kx-christmas-star">✦</span><div class="kx-christmas-tree"><i></i><i></i><i></i></div>
            <span class="kx-christmas-bauble"></span><span class="kx-christmas-bauble"></span><span class="kx-christmas-bauble"></span>
            <span class="kx-christmas-gift kx-christmas-gift--red"></span><span class="kx-christmas-gift kx-christmas-gift--gold"></span>
        </div>
        <p class="kx-loader__title">Merry Christmas</p>
        <p class="kx-loader__hint">Warm wishes from KayXchange. Loading your secure experience…</p>
        <div class="kx-festive-progress" aria-hidden="true"><i></i></div>
    </div>
    <div class="kx-loader__panel kx-loader__variant" role="status" aria-live="polite" aria-label="KayXchange animated loader">
        <div class="kx-loader__brand"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="" width="34" height="34"><span>Kay<span style="color:#35e878">Xchange</span></span></div>
        <div class="kx-variant-stage" aria-hidden="true">
            <i class="kx-variant-orbit kx-variant-orbit--one"></i><i class="kx-variant-orbit kx-variant-orbit--two"></i>
            <i class="kx-variant-orbit kx-variant-orbit--three"></i><span class="kx-variant-core">K</span>
            <span class="kx-variant-spark kx-variant-spark--one">✦</span><span class="kx-variant-spark kx-variant-spark--two">✧</span>
        </div>
        <p class="kx-loader__title">Loading securely</p>
        <p class="kx-loader__hint">Preparing your KayXchange experience…</p>
        <div class="kx-festive-progress" aria-hidden="true"><i></i></div>
    </div>
</div>
<script>
window.kxLoading = window.kxLoading || {
    show(message, hint) {
        const overlay = document.getElementById('loader');
        if (!overlay) return;
        const christmas = overlay.classList.contains('kx-loader--christmas');
        const festive = overlay.classList.contains('kx-loader--festive');
        const classic = overlay.classList.contains('kx-loader--classic');
        const panel = overlay.querySelector(christmas ? '.kx-loader__christmas' : (festive ? '.kx-loader__festive' : (classic ? '.kx-loader__classic' : '.kx-loader__variant')));
        const title = panel?.querySelector('.kx-loader__title');
        const subtitle = panel?.querySelector('.kx-loader__hint');
        if (title) title.textContent = message || (christmas ? 'Merry Christmas' : (festive ? 'Welcome to 2027' : (classic ? 'Trade with Kay' : 'Loading securely')));
        if (subtitle) subtitle.textContent = hint || (christmas ? 'Warm wishes from KayXchange. Loading your secure experience…' : (festive ? 'A bright new year. A little festive magic.' : (classic ? 'Fast · Secure · Best Rates' : 'Preparing your KayXchange experience…')));
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
    if (!overlay) return;
    const christmas = overlay.classList.contains('kx-loader--christmas');
    const festive = overlay.classList.contains('kx-loader--festive');
    if (!christmas && !festive) return;
    const key = christmas ? 'kx-christmas-intro-2026' : 'kx-festive-intro-2027';
    try {
        if (sessionStorage.getItem(key)) return;
        sessionStorage.setItem(key, '1');
    } catch (_) { /* session storage can be unavailable in private browsing */ }
    window.kxLoading.show();
    window.setTimeout(() => window.kxLoading.hide(), matchMedia('(prefers-reduced-motion: reduce)').matches ? 900 : 2400);
});
</script>
