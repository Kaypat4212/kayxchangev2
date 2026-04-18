@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;}

.kx-sc-wrap { padding: 28px 0 60px; }
.kx-sc-card { background: var(--kx-card); border: 1px solid var(--kx-border); border-radius: 18px; padding: 28px 32px; margin-bottom: 24px; position:relative; overflow:hidden; }
.kx-sc-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--kx-green); }
.kx-sc-group { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #00cc00; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.kx-sc-group::after { content:''; flex:1; height:1px; background: rgba(0,204,0,0.15); }
.kx-sc-field { margin-bottom: 20px; }
.kx-sc-label { font-size: 0.78rem; font-weight: 600; color: var(--kx-muted); margin-bottom: 6px; }
.kx-sc-input { width: 100%; background: var(--kx-card2); border: 1px solid var(--kx-border); border-radius: 10px; color: var(--kx-text); font-size: 0.88rem; padding: 10px 14px; outline: none; font-family: 'Poppins', sans-serif; transition: border-color 0.2s, box-shadow 0.2s; }
.kx-sc-input:focus { border-color: rgba(0,204,0,0.5); box-shadow: 0 0 0 3px rgba(0,204,0,0.08); }
textarea.kx-sc-input { resize: vertical; min-height: 80px; }
.kx-sc-save { background: linear-gradient(135deg,#00cc00,#007a0c); color:#fff; border:none; border-radius:12px; padding:13px 32px; font-weight:700; font-size:0.92rem; cursor:pointer; transition:all 0.22s; box-shadow:0 4px 18px rgba(0,204,0,0.3); }
.kx-sc-save:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,204,0,0.42); }
.kx-sc-alert { background: rgba(0,204,0,0.12); border: 1px solid rgba(0,204,0,0.3); color: #00cc00; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-size: 0.88rem; }
.kx-sc-page-title { font-size: clamp(1.4rem,3vw,1.9rem); font-weight: 800; color: #fff; margin-bottom: 6px; }
.kx-sc-page-sub { color: var(--kx-muted); font-size: 0.88rem; margin-bottom: 28px; }

/* group colors */
.kx-grp-about    { color: #60a5fa; }
.kx-grp-about::after { background: rgba(96,165,250,0.15); }
.kx-grp-about-page { color: #38bdf8; }
.kx-grp-about-page::after { background: rgba(56,189,248,0.15); }
.kx-grp-why      { color: #a78bfa; }
.kx-grp-why::after { background: rgba(167,139,250,0.15); }
.kx-grp-stats    { color: #fbbf24; }
.kx-grp-stats::after { background: rgba(251,191,36,0.15); }
.kx-grp-newsletter { color: #f472b6; }
.kx-grp-newsletter::after { background: rgba(244,114,182,0.15); }
.kx-grp-footer   { color: #34d399; }
.kx-grp-footer::after { background: rgba(52,211,153,0.15); }
</style>
@endpush

@section('content')
<div class="kx-sc-wrap">
    <div class="container-xl">
        <h1 class="kx-sc-page-title"><i class="bi bi-pencil-square me-2" style="color:#00cc00"></i>Homepage Content Editor</h1>
        <p class="kx-sc-page-sub">Edit text displayed on the public homepage. Changes are saved instantly.</p>

        @if(session('success'))
        <div class="kx-sc-alert"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.site-content.update') }}">
            @csrf

            @php
            $groupLabels = [
                'about'      => ['label' => 'About Section (Homepage)',  'icon' => 'bi-info-circle',       'cls' => 'kx-grp-about'],
                'about_page' => ['label' => 'About Page Content',        'icon' => 'bi-building-check',    'cls' => 'kx-grp-about-page'],
                'why'        => ['label' => 'Why Choose Crypto',         'icon' => 'bi-stars',             'cls' => 'kx-grp-why'],
                'stats'      => ['label' => 'Statistics',                'icon' => 'bi-bar-chart',         'cls' => 'kx-grp-stats'],
                'newsletter' => ['label' => 'Newsletter Section',        'icon' => 'bi-envelope',          'cls' => 'kx-grp-newsletter'],
                'footer'     => ['label' => 'Footer & Contact',          'icon' => 'bi-layout-text-window','cls' => 'kx-grp-footer'],
            ];
            @endphp

            @foreach($sections as $group => $items)
            @php $g = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-folder', 'cls' => '']; @endphp
            <div class="kx-sc-card">
                <div class="kx-sc-group {{ $g['cls'] }}"><i class="bi {{ $g['icon'] }}"></i>{{ $g['label'] }}</div>
                <div class="row g-3">
                    @foreach($items as $item)
                    <div class="{{ Str::contains($item->key, 'desc') || Str::contains($item->key, 'description') || Str::contains($item->key, 'subtitle') || Str::contains($item->key, 'tagline') ? 'col-12' : 'col-md-6' }}">
                        <div class="kx-sc-field">
                            <div class="kx-sc-label">{{ $item->label }}</div>
                            @if(Str::contains($item->key, ['desc', 'description', 'subtitle', 'tagline']))
                            <textarea name="content[{{ $item->key }}]" class="kx-sc-input">{{ old('content.'.$item->key, $item->value) }}</textarea>
                            @else
                            <input type="text" name="content[{{ $item->key }}]" value="{{ old('content.'.$item->key, $item->value) }}" class="kx-sc-input">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
                <button type="submit" class="kx-sc-save"><i class="bi bi-floppy-fill me-2"></i>Save All Changes</button>
                <a href="{{ url('/home') }}" target="_blank" class="btn btn-sm" style="border-radius:10px;padding:11px 20px;background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text)">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Preview Homepage
                </a>
                <a href="{{ url('/about') }}" target="_blank" class="btn btn-sm" style="border-radius:10px;padding:11px 20px;background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text)">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Preview About Page
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
