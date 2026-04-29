<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: { toolbar: '#quill-toolbar' },
        placeholder: 'Start writing your article...',
    });
    var ci = document.getElementById('content-input');
    quill.on('text-change', function () {
        var h = quill.root.innerHTML;
        ci.value = (h === '<p><br></p>') ? '' : h;
        updateWC();
    });
    if (ci.value.trim()) { quill.clipboard.dangerouslyPasteHTML(ci.value); }
    function updateWC() {
        var t = quill.getText().trim();
        var w = t ? t.split(/\s+/).filter(Boolean).length : 0;
        var m = Math.max(1, Math.ceil(w / 200));
        var wc = document.getElementById('word-count');
        var rt = document.getElementById('reading-time-badge');
        if (wc) wc.textContent = w.toLocaleString() + ' words - ' + m + ' min read';
        if (rt) rt.textContent = m + ' min read';
    }
    updateWC();
    var ti = document.getElementById('post-title');
    var sp = document.getElementById('slug-preview-text');
    var st = document.getElementById('seo-title');
    function slugify(s) {
        return s.toLowerCase().replace(/[^\w\s-]/g,'').replace(/[\s_-]+/g,'-').replace(/^-+|-+$/g,'');
    }
    if (ti) ti.addEventListener('input', function () {
        var slug = slugify(this.value);
        if (sp) sp.textContent = slug || 'auto-generated';
        if (st) st.textContent = this.value || 'Post title will appear here';
    });
    var ei = document.getElementById('excerpt-input');
    var ec = document.getElementById('excerpt-chars');
    var sd = document.getElementById('seo-desc');
    if (ei) ei.addEventListener('input', function () {
        if (ec) ec.textContent = this.value.length;
        if (sd) sd.textContent = this.value || 'Excerpt will appear here as the meta description.';
    });
    var pt = document.getElementById('is_published');
    var pl = document.getElementById('publish-status-label');
    if (pt) pt.addEventListener('change', function () {
        if (pl) pl.textContent = this.checked ? 'Published - visible to all' : 'Draft - not visible';
    });
    document.querySelectorAll('[name="_action"]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (this.value === 'publish' && pt) pt.checked = true;
        });
    });
    var catInput = document.getElementById('category-input');
    document.querySelectorAll('.category-chip').forEach(function (chip) {
        if (catInput && chip.dataset.value === catInput.value) chip.classList.add('active');
        chip.addEventListener('click', function () {
            if (catInput) catInput.value = this.dataset.value;
            document.querySelectorAll('.category-chip').forEach(function(c){ c.classList.remove('active'); });
            this.classList.add('active');
        });
    });
    if (catInput) catInput.addEventListener('input', function () {
        document.querySelectorAll('.category-chip').forEach(function (c) {
            c.classList.toggle('active', c.dataset.value === catInput.value);
        });
    });
    var cvIn = document.getElementById('cover-image-input');
    var cvWr = document.getElementById('cover-preview-wrapper');
    var cvIm = document.getElementById('cover-preview-img');
    var cvRm = document.getElementById('remove-cover-btn');
    if (cvIn) cvIn.addEventListener('input', function () {
        var url = this.value.trim();
        if (url) { if (cvIm) cvIm.src = url; if (cvWr) cvWr.classList.remove('d-none'); }
        else { if (cvWr) cvWr.classList.add('d-none'); }
    });
    if (cvRm) cvRm.addEventListener('click', function () {
        if (cvIn) cvIn.value = '';
        if (cvWr) cvWr.classList.add('d-none');
        if (cvIm) cvIm.src = '';
    });
    var form = document.getElementById('blog-form');
    if (form) form.addEventListener('submit', function () {
        var h = quill.root.innerHTML;
        ci.value = (h === '<p><br></p>') ? '' : h;
    });

    // AI assistant actions
    var aiStatus = document.getElementById('ai-status');
    var aiTopic = document.getElementById('ai-topic');
    var aiTone = document.getElementById('ai-tone');
    var aiGenerateBtn = document.getElementById('ai-generate');
    var aiOutlineBtn = document.getElementById('ai-outline');
    var aiTitlesBtn = document.getElementById('ai-titles');
    var aiImproveBtn = document.getElementById('ai-improve');
    var aiImproveAction = document.getElementById('ai-improve-action');
    var aiExcerptBtn = document.getElementById('ai-excerpt');

    function setAiStatus(text, state) {
        if (!aiStatus) return;
        aiStatus.textContent = text;
        aiStatus.className = 'badge border';
        if (state === 'busy') aiStatus.classList.add('text-bg-warning');
        else if (state === 'ok') aiStatus.classList.add('text-bg-success');
        else if (state === 'error') aiStatus.classList.add('text-bg-danger');
        else aiStatus.classList.add('text-bg-light');
    }

    function getCsrf() {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : '';
    }

    async function aiPost(url, payload) {
        var response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        var data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || data.message || 'AI request failed.');
        }

        if (data.error) {
            throw new Error(data.error);
        }

        return data;
    }

    function setEditorHtml(html) {
        quill.clipboard.dangerouslyPasteHTML(html || '');
        var h = quill.root.innerHTML;
        ci.value = (h === '<p><br></p>') ? '' : h;
        updateWC();
    }

    function appendEditorHtml(html) {
        var current = quill.root.innerHTML;
        var merged = current + '<p><br></p>' + (html || '');
        quill.clipboard.dangerouslyPasteHTML(merged);
        var h = quill.root.innerHTML;
        ci.value = (h === '<p><br></p>') ? '' : h;
        updateWC();
    }

    function ensureTopic(defaultValue) {
        if (!aiTopic) return '';
        var value = (aiTopic.value || '').trim();
        if (!value && defaultValue) {
            aiTopic.value = defaultValue;
            value = defaultValue;
        }
        return value;
    }

    function renderTitleChips(titles) {
        var old = document.getElementById('ai-title-suggestions');
        if (old) old.remove();
        if (!titles || !titles.length || !aiTopic) return;

        var box = document.createElement('div');
        box.id = 'ai-title-suggestions';
        box.className = 'ai-title-suggestions';

        titles.forEach(function (title) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ai-title-chip';
            chip.textContent = title;
            chip.addEventListener('click', function () {
                if (ti) {
                    ti.value = title;
                    ti.dispatchEvent(new Event('input'));
                }
            });
            box.appendChild(chip);
        });

        aiTopic.parentNode.appendChild(box);
    }

    if (aiGenerateBtn) {
        aiGenerateBtn.addEventListener('click', async function () {
            try {
                var topic = ensureTopic(ti ? ti.value.trim() : '');
                if (!topic) {
                    alert('Enter a topic or title first.');
                    return;
                }

                setAiStatus('Generating...', 'busy');
                aiGenerateBtn.disabled = true;

                var payload = { topic: topic, tone: aiTone ? aiTone.value : 'informative' };
                var data = await aiPost('{{ route('admin.blog.ai.generate') }}', payload);
                setEditorHtml(data.content || '');
                setAiStatus('Draft ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate draft.');
            } finally {
                aiGenerateBtn.disabled = false;
            }
        });
    }

    if (aiOutlineBtn) {
        aiOutlineBtn.addEventListener('click', async function () {
            try {
                var topic = ensureTopic(ti ? ti.value.trim() : '');
                if (!topic) {
                    alert('Enter a topic or title first.');
                    return;
                }

                setAiStatus('Building outline...', 'busy');
                aiOutlineBtn.disabled = true;
                var data = await aiPost('{{ route('admin.blog.ai.outline') }}', { topic: topic });
                appendEditorHtml(data.content || '');
                setAiStatus('Outline added', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate outline.');
            } finally {
                aiOutlineBtn.disabled = false;
            }
        });
    }

    if (aiTitlesBtn) {
        aiTitlesBtn.addEventListener('click', async function () {
            try {
                var seed = ensureTopic(ti ? ti.value.trim() : quill.getText().trim().slice(0, 200));
                if (!seed) {
                    alert('Enter a topic or add some content first.');
                    return;
                }

                setAiStatus('Generating titles...', 'busy');
                aiTitlesBtn.disabled = true;
                var data = await aiPost('{{ route('admin.blog.ai.titles') }}', { topic: seed });
                renderTitleChips(data.titles || []);
                setAiStatus('Titles ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate titles.');
            } finally {
                aiTitlesBtn.disabled = false;
            }
        });
    }

    if (aiImproveBtn) {
        aiImproveBtn.addEventListener('click', async function () {
            try {
                var selection = quill.getSelection();
                if (!selection || selection.length === 0) {
                    alert('Select text in the editor first.');
                    return;
                }

                var selectedText = quill.getText(selection.index, selection.length).trim();
                if (!selectedText) {
                    alert('Select a non-empty text block.');
                    return;
                }

                setAiStatus('Improving text...', 'busy');
                aiImproveBtn.disabled = true;

                var data = await aiPost('{{ route('admin.blog.ai.improve') }}', {
                    text: selectedText,
                    action: aiImproveAction ? aiImproveAction.value : 'improve'
                });

                quill.deleteText(selection.index, selection.length);
                quill.clipboard.dangerouslyPasteHTML(selection.index, data.content || '');

                var h = quill.root.innerHTML;
                ci.value = (h === '<p><br></p>') ? '' : h;
                updateWC();

                setAiStatus('Selection updated', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to improve selected text.');
            } finally {
                aiImproveBtn.disabled = false;
            }
        });
    }

    if (aiExcerptBtn) {
        aiExcerptBtn.addEventListener('click', async function () {
            try {
                var html = quill.root.innerHTML;
                if (!html || html === '<p><br></p>') {
                    alert('Write or generate content before creating an excerpt.');
                    return;
                }

                setAiStatus('Generating excerpt...', 'busy');
                aiExcerptBtn.disabled = true;

                var data = await aiPost('{{ route('admin.blog.ai.excerpt') }}', { content: html });
                if (ei) {
                    ei.value = data.excerpt || '';
                    ei.dispatchEvent(new Event('input'));
                }
                setAiStatus('Excerpt ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate excerpt.');
            } finally {
                aiExcerptBtn.disabled = false;
            }
        });
    }

    // ── SEO Tags ──────────────────────────────────────────────────────────────
    var aiSeoBtn = document.getElementById('ai-seo-tags');
    if (aiSeoBtn) {
        aiSeoBtn.addEventListener('click', async function () {
            var html = quill.root.innerHTML;
            if (!html || html === '<p><br></p>') { alert('Write or generate content first.'); return; }
            try {
                setAiStatus('Generating SEO…', 'busy');
                aiSeoBtn.disabled = true;
                var data = await aiPost('{{ route('admin.blog.ai.seo-tags') }}', { content: html });

                var metaEl = document.getElementById('ai-seo-meta-desc');
                var kwBox  = document.getElementById('ai-seo-keywords-box');
                var out    = document.getElementById('ai-seo-output');

                if (metaEl) metaEl.textContent = data.meta_description || '';
                if (kwBox) {
                    var kws = data.keywords || [];
                    kwBox.innerHTML = kws.map(function(k){
                        return '<span style="background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.2);border-radius:20px;padding:.1rem .55rem;font-size:.7rem;color:#38bdf8;cursor:pointer" onclick="navigator.clipboard.writeText(\''+k+'\')">'+ k +'</span>';
                    }).join('');
                }
                if (out) out.style.display = 'block';
                // Also populate the excerpt if empty
                if (data.meta_description && ei && !ei.value.trim()) {
                    ei.value = data.meta_description;
                    ei.dispatchEvent(new Event('input'));
                }
                setAiStatus('SEO tags ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate SEO tags.');
            } finally {
                aiSeoBtn.disabled = false;
            }
        });
    }

    // ── Social Caption ────────────────────────────────────────────────────────
    var aiSocialBtn      = document.getElementById('ai-social-caption');
    var aiSocialPlatform = document.getElementById('ai-social-platform');
    var aiSocialCopyBtn  = document.getElementById('ai-social-copy');
    if (aiSocialBtn) {
        aiSocialBtn.addEventListener('click', async function () {
            var title = ti ? ti.value.trim() : '';
            if (!title) { alert('Enter a post title first.'); return; }
            var platform = aiSocialPlatform ? aiSocialPlatform.value : 'twitter';
            var excerptEl = ei ? ei.value.trim() : '';
            try {
                setAiStatus('Writing caption…', 'busy');
                aiSocialBtn.disabled = true;
                var data = await aiPost('{{ route('admin.blog.ai.social-caption') }}', {
                    title: title, excerpt: excerptEl, platform: platform
                });
                var textEl = document.getElementById('ai-social-text');
                var out    = document.getElementById('ai-social-output');
                if (textEl) textEl.textContent = data.caption || '';
                if (out) out.style.display = 'block';
                setAiStatus('Caption ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate caption.');
            } finally {
                aiSocialBtn.disabled = false;
            }
        });
    }
    if (aiSocialCopyBtn) {
        aiSocialCopyBtn.addEventListener('click', function () {
            var t = document.getElementById('ai-social-text');
            if (t) {
                navigator.clipboard.writeText(t.textContent || '').then(function(){
                    aiSocialCopyBtn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
                    setTimeout(function(){ aiSocialCopyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy'; }, 2000);
                });
            }
        });
    }

    // ── Content Planner ───────────────────────────────────────────────────────
    var aiPlannerBtn = document.getElementById('ai-content-planner');
    if (aiPlannerBtn) {
        aiPlannerBtn.addEventListener('click', async function () {
            try {
                setAiStatus('Planning content…', 'busy');
                aiPlannerBtn.disabled = true;
                var ctx = aiTopic ? aiTopic.value.trim() : '';
                var data = await aiPost('{{ route('admin.blog.ai.content-planner') }}', { context: ctx || undefined });

                var list = document.getElementById('ai-planner-list');
                var out  = document.getElementById('ai-planner-output');
                var topics = data.topics || [];
                if (list) {
                    if (topics.length) {
                        list.innerHTML = topics.map(function(t){
                            return '<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:6px;padding:.5rem .7rem;cursor:pointer" '
                                + 'onclick="document.getElementById(\'ai-topic\').value=\''+( (t.title||'').replace(/'/g,'\\\'') )+'\'">'
                                + '<span style="font-size:.8rem;color:#e4e8f0;font-weight:600">'+( t.title||'' )+'</span>'
                                + '<span style="font-size:.7rem;color:#38bdf8;margin-left:.5rem;background:rgba(56,189,248,.08);padding:.05rem .4rem;border-radius:10px">'+( t.category||'' )+'</span>'
                                + '<div style="font-size:.72rem;color:#7a8599;margin-top:.2rem">'+( t.angle||'' )+'</div>'
                                + '</div>';
                        }).join('');
                    } else if (data.raw) {
                        list.innerHTML = '<pre style="font-size:.78rem;color:#e4e8f0;white-space:pre-wrap">'+data.raw+'</pre>';
                    }
                }
                if (out) out.style.display = 'block';
                setAiStatus('Ideas ready', 'ok');
            } catch (err) {
                setAiStatus('Error', 'error');
                alert(err.message || 'Failed to generate content plan.');
            } finally {
                aiPlannerBtn.disabled = false;
            }
        });
    }

    // ── Dark Mode Toggle ──────────────────────────────────────────────────────
    function initDarkMode() {
        var toggleBtn = document.getElementById('adminModeToggleBtn');
        var modeIcon = document.getElementById('adminModeIcon');

        if (!toggleBtn || !modeIcon) return;

        // Check for saved theme preference or default to light mode
        var currentTheme = localStorage.getItem('admin-theme') || 'light';
        applyTheme(currentTheme === 'dark');

        toggleBtn.addEventListener('click', function() {
            var isDark = document.body.classList.contains('dark-mode');
            var newTheme = isDark ? 'light' : 'dark';
            applyTheme(!isDark);
            localStorage.setItem('admin-theme', newTheme);
        });

        function applyTheme(dark) {
            if (dark) {
                document.body.classList.add('dark-mode');
                modeIcon.className = 'bi bi-sun-fill';
                toggleBtn.title = 'Switch to light mode';
            } else {
                document.body.classList.remove('dark-mode');
                modeIcon.className = 'bi bi-moon-stars-fill';
                toggleBtn.title = 'Switch to dark mode';
            }
        }
    }

    // Initialize dark mode when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarkMode);
    } else {
        initDarkMode();
    }
})();
</script>