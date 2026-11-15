<div id="kxAndroidAppPrompt" role="region" aria-label="KayXchange Android app" style="display:none;position:fixed;z-index:1080;left:12px;right:12px;bottom:76px;max-width:560px;margin:auto;padding:12px 14px;border:1px solid rgba(0,204,0,.32);border-radius:16px;background:#101b14;color:#eef8ef;box-shadow:0 12px 36px #0008;align-items:center;gap:12px">
    <img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="KayXchange" width="42" height="42" style="border-radius:12px;object-fit:cover">
    <div style="flex:1;min-width:0"><strong style="display:block">KayXchange for Android</strong><small style="color:#a8b8ac">Quick access to your account and wallets</small></div>
    <a href="{{ route('install.app.public') }}" class="btn btn-sm btn-success fw-bold">Get app</a>
    <button type="button" id="kxAndroidPromptClose" aria-label="Dismiss" style="border:0;background:transparent;color:#aab8ad;font-size:1.2rem">×</button>
</div>
<script>
(function(){var android=/android/i.test(navigator.userAgent||'');var standalone=window.matchMedia('(display-mode: standalone)').matches||window.navigator.standalone===true;var key='kxAndroidPromptDismissed';var bar=document.getElementById('kxAndroidAppPrompt');if(android&&!standalone&&!sessionStorage.getItem(key)&&bar){bar.style.display='flex';document.getElementById('kxAndroidPromptClose').addEventListener('click',function(){bar.remove();sessionStorage.setItem(key,'1')})}})();
</script>
