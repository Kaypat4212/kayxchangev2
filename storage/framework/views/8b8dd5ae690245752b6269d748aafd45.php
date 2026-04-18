<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* ── Editor Layout ─────────────────────────────────────── */
.editor-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px 22px;
    transition: box-shadow .15s;
}
.editor-card:focus-within {
    box-shadow: 0 0 0 3px rgba(13,110,253,.08);
    border-color: #86b7fe;
}

/* ── Title input ───────────────────────────────────────── */
.title-input {
    width: 100%;
    border: none;
    outline: none;
    font-size: 1.65rem;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    color: #1a1a2e;
    background: transparent;
    padding: 4px 8px 2px;
    line-height: 1.3;
}
.title-input::placeholder { color: #adb5bd; }
.title-input.is-invalid   { border-bottom: 2px solid #dc3545; }
.slug-preview { border-top: 1px dashed #e9ecef; }

/* ── Quill integration ─────────────────────────────────── */
#quill-toolbar {
    border: none !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 10px 14px !important;
    background: #f8f9fa;
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
    color: #2d2d2d;
}
.ql-editor h1 { font-size: 2rem; font-weight: 700; margin-bottom: .5rem; }
.ql-editor h2 { font-size: 1.5rem; font-weight: 600; margin-bottom: .4rem; }
.ql-editor h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: .4rem; }
.ql-editor p  { margin-bottom: .85rem; }
.ql-editor blockquote {
    border-left: 4px solid #0d6efd;
    margin: 1rem 0;
    padding: .5rem 1rem;
    color: #495057;
    background: #f0f4ff;
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

/* ── Editor label ──────────────────────────────────────── */
.editor-label {
    font-size: .82rem;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: #6c757d;
    display: block;
}

/* ── Category chips ────────────────────────────────────── */
.category-chip {
    font-size: .72rem;
    font-weight: 500;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    color: #495057;
    cursor: pointer;
    transition: all .15s;
    line-height: 1.6;
    font-family: 'Poppins', sans-serif;
}
.category-chip:hover,
.category-chip.active {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

/* ── SEO preview ───────────────────────────────────────── */
.seo-preview {
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
}
.seo-site-name { font-size: .72rem; color: #188038; margin-bottom: 2px; }
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
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/admin/blog/_editor-styles.blade.php ENDPATH**/ ?>