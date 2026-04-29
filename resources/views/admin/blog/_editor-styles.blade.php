<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* ── Dark Mode Variables ────────────────────────────────── */
:root {
    --editor-bg: #fff;
    --editor-border: #e9ecef;
    --editor-text: #1a1a2e;
    --editor-muted: #6c757d;
    --editor-toolbar-bg: #f8f9fa;
    --editor-toolbar-border: #e9ecef;
    --editor-focus-border: #86b7fe;
    --editor-focus-shadow: rgba(13,110,253,.08);
    --editor-placeholder: #adb5bd;
    --editor-error: #dc3545;
    --editor-success: #198754;
    --editor-warning: #ffc107;
    --editor-info: #0dcaf0;
}

/* Dark mode styles */
body.dark-mode {
    --editor-bg: #1a1a1a;
    --editor-border: #374151;
    --editor-text: #f9fafb;
    --editor-muted: #9ca3af;
    --editor-toolbar-bg: #111827;
    --editor-toolbar-border: #374151;
    --editor-focus-border: #60a5fa;
    --editor-focus-shadow: rgba(96,165,250,.15);
    --editor-placeholder: #6b7280;
    --editor-error: #ef4444;
    --editor-success: #10b981;
    --editor-warning: #f59e0b;
    --editor-info: #06b6d4;
}

/* ── Editor Layout ─────────────────────────────────────── */
.editor-card {
    background: var(--editor-bg);
    border: 1px solid var(--editor-border);
    border-radius: 12px;
    padding: 20px 22px;
    transition: box-shadow .15s;
}
.editor-card:focus-within {
    box-shadow: 0 0 0 3px var(--editor-focus-shadow);
    border-color: var(--editor-focus-border);
}

/* ── Title input ───────────────────────────────────────── */
.title-input {
    width: 100%;
    border: none;
    outline: none;
    font-size: 1.65rem;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    color: var(--editor-text);
    background: transparent;
    padding: 4px 8px 2px;
    line-height: 1.3;
}
.title-input::placeholder { color: var(--editor-placeholder); }
.title-input.is-invalid   { border-bottom: 2px solid var(--editor-error); }
.slug-preview { border-top: 1px dashed var(--editor-border); }

/* ── Quill integration ─────────────────────────────────── */
#quill-toolbar {
    border: none !important;
    border-bottom: 1px solid var(--editor-toolbar-border) !important;
    padding: 10px 14px !important;
    background: var(--editor-toolbar-bg);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 2px;
}
#quill-toolbar .ql-formats { margin-right: 6px !important; }
.ql-container.ql-snow {
    border: none !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 1rem;
}
.ql-editor {
    min-height: 440px;
    padding: 20px 24px !important;
    line-height: 1.85;
    color: var(--editor-text);
}
.ql-editor h1 { font-size: 2rem; font-weight: 700; margin-bottom: .5rem; }
.ql-editor h2 { font-size: 1.5rem; font-weight: 600; margin-bottom: .4rem; }
.ql-editor h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: .4rem; }
.ql-editor p  { margin-bottom: .85rem; }
.ql-editor blockquote {
    border-left: 4px solid var(--editor-info);
    margin: 1rem 0;
    padding: .5rem 1rem;
    color: var(--editor-text);
    background: rgba(13,202,240,.1);
    border-radius: 0 8px 8px 0;
    font-style: italic;
}
.ql-editor pre.ql-syntax {
    background: #1e1e2e;
    color: #cdd6f4;
    border-radius: 8px;
    padding: 16px;
    font-size: .875rem;
}
.ql-editor img { max-width: 100%; border-radius: 8px; margin: .5rem 0; }

/* Dark mode Quill overrides */
body.dark-mode .ql-editor pre.ql-syntax {
    background: #0f0f23;
    color: #e2e8f0;
}
body.dark-mode .ql-toolbar .ql-stroke {
    stroke: var(--editor-muted);
}
body.dark-mode .ql-toolbar .ql-fill {
    fill: var(--editor-muted);
}
body.dark-mode .ql-toolbar button:hover .ql-stroke {
    stroke: var(--editor-text);
}
body.dark-mode .ql-toolbar button:hover .ql-fill {
    fill: var(--editor-text);
}
body.dark-mode .ql-toolbar button.ql-active .ql-stroke {
    stroke: var(--editor-info);
}
body.dark-mode .ql-toolbar button.ql-active .ql-fill {
    fill: var(--editor-info);
}

/* ── Editor label ──────────────────────────────────────── */
.editor-label {
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: var(--editor-muted);
    display: block;
}

/* ── Category chips ────────────────────────────────────── */
.category-chip {
    font-size: .72rem;
    font-weight: 500;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid var(--editor-border);
    background: var(--editor-toolbar-bg);
    color: var(--editor-text);
    cursor: pointer;
    transition: all .15s;
    line-height: 1.6;
    font-family: 'Poppins', sans-serif;
}
.category-chip:hover,
.category-chip.active {
    background: var(--editor-info);
    border-color: var(--editor-info);
    color: #fff;
}

/* ── SEO preview ───────────────────────────────────────── */
.seo-preview {
    background: var(--editor-toolbar-bg);
    border: 1px dashed var(--editor-border);
}
.seo-site-name { font-size: .72rem; color: var(--editor-success); margin-bottom: 2px; }
.seo-title {
    font-size: .95rem;
    color: #1a0dab;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.seo-desc {
    font-size: .78rem;
    color: #4d5156;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* AI assistant */
.ai-assistant-card {
    border-color: #dbe7ff;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
}

.ai-title-suggestions {
    margin-top: .65rem;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}

.ai-title-chip {
    font-size: .72rem;
    border: 1px solid #cfe0ff;
    background: #eef4ff;
    color: #204283;
    border-radius: 999px;
    padding: .25rem .55rem;
    cursor: pointer;
}

.ai-title-chip:hover {
    background: #dce9ff;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 991px) {
    .editor-card { padding: 14px 16px; }
    .title-input  { font-size: 1.3rem; }
}
</style>
