

<?php if($errors->any()): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex gap-2 align-items-start mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-1 ps-3">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    
    <div class="col-lg-8">

        
        <div class="editor-card mb-3">
            <input type="text" id="post-title" name="title"
                   class="title-input <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="Post title…"
                   value="<?php echo e(old('title', $post->title ?? '')); ?>"
                   autocomplete="off" required>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback ps-2"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="slug-preview px-3 pb-2 pt-1">
                <span class="text-muted" style="font-size:.78rem;">
                    <i class="bi bi-link-45deg"></i> Slug:&nbsp;
                </span>
                <span id="slug-preview-text" class="text-muted font-monospace" style="font-size:.78rem;">
                    <?php echo e(old('title', isset($post) ? $post->slug : '') ?: 'auto-generated'); ?>

                </span>
            </div>
        </div>

        
        <div class="editor-card mb-3 p-0 overflow-hidden">
            <div id="quill-toolbar">
                <span class="ql-formats">
                    <select class="ql-header">
                        <option value="1">Heading 1</option>
                        <option value="2">Heading 2</option>
                        <option value="3">Heading 3</option>
                        <option selected>Normal</option>
                    </select>
                </span>
                <span class="ql-formats">
                    <button class="ql-bold" title="Bold"></button>
                    <button class="ql-italic" title="Italic"></button>
                    <button class="ql-underline" title="Underline"></button>
                    <button class="ql-strike" title="Strikethrough"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-blockquote" title="Blockquote"></button>
                    <button class="ql-code-block" title="Code block"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-list" value="ordered" title="Ordered list"></button>
                    <button class="ql-list" value="bullet" title="Bullet list"></button>
                    <button class="ql-indent" value="-1" title="Outdent"></button>
                    <button class="ql-indent" value="+1" title="Indent"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-link" title="Link"></button>
                    <button class="ql-image" title="Image"></button>
                    <button class="ql-video" title="Video"></button>
                </span>
                <span class="ql-formats">
                    <select class="ql-align">
                        <option selected></option>
                        <option value="center"></option>
                        <option value="right"></option>
                        <option value="justify"></option>
                    </select>
                    <select class="ql-color"></select>
                    <select class="ql-background"></select>
                </span>
                <span class="ql-formats">
                    <button class="ql-clean" title="Remove formatting"></button>
                </span>
                <span class="ql-formats ms-auto d-flex align-items-center">
                    <span id="word-count" class="text-muted" style="font-size:.75rem;line-height:2.4;">
                        0 words &middot; 1 min read
                    </span>
                </span>
            </div>
            <div id="quill-editor" style="min-height:440px;font-size:1rem;">
                <?php echo old('content', isset($post) ? $post->content : ''); ?>

            </div>
            <textarea name="content" id="content-input" class="d-none <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('content', isset($post) ? $post->content : '')); ?></textarea>
            <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback px-3 pb-2 d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="editor-card mb-3">
            <label class="editor-label">
                <i class="bi bi-card-text me-1 text-muted"></i>Excerpt
                <small class="text-muted fw-normal">— shown on the blog listing page</small>
            </label>
            <textarea name="excerpt" id="excerpt-input" rows="3"
                      class="form-control border-0 shadow-none px-0 <?php $__errorArgs = ['excerpt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      placeholder="Write a short, compelling summary of this post (max 500 chars)…"
                      maxlength="500"><?php echo e(old('excerpt', isset($post) ? $post->excerpt : '')); ?></textarea>
            <div class="d-flex justify-content-end mt-1">
                <span id="excerpt-counter" class="text-muted" style="font-size:.75rem;">
                    <span id="excerpt-chars"><?php echo e(strlen(old('excerpt', isset($post) ? $post->excerpt : ''))); ?></span>/500
                </span>
            </div>
            <?php $__errorArgs = ['excerpt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

    </div>

    
    <div class="col-lg-4">

        
        <div class="editor-card mb-3">
            <div class="editor-label mb-3"><i class="bi bi-send me-1 text-muted"></i>Publish settings</div>

            <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-3 mb-3">
                <div>
                    <div class="fw-semibold" style="font-size:.88rem;">Status</div>
                    <small id="publish-status-label" class="text-muted" style="font-size:.78rem;">
                        <?php echo e(old('is_published', isset($post) ? $post->is_published : false) ? 'Published — visible to all' : 'Draft — not visible'); ?>

                    </small>
                </div>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_published" id="is_published" value="1"
                           style="width:2.8rem;height:1.4rem;cursor:pointer;"
                           <?php if(old('is_published', isset($post) ? $post->is_published : false)): echo 'checked'; endif; ?>>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark border" style="font-size:.73rem;">
                    <i class="bi bi-clock me-1"></i><span id="reading-time-badge">1 min read</span>
                </span>
                <?php if(isset($post) && $post->published_at): ?>
                <span class="badge bg-light text-dark border" style="font-size:.73rem;">
                    <i class="bi bi-calendar-check me-1"></i><?php echo e($post->published_at->format('M d, Y')); ?>

                </span>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="editor-card mb-3">
            <label class="editor-label mb-2"><i class="bi bi-image me-1 text-muted"></i>Cover Image</label>
            <div id="cover-preview-wrapper" class="<?php echo e(old('cover_image', isset($post) ? $post->cover_image : '') ? '' : 'd-none'); ?> mb-2">
                <img id="cover-preview-img"
                     src="<?php echo e(old('cover_image', isset($post) ? $post->cover_image : '')); ?>"
                     alt="Cover preview"
                     class="img-fluid rounded-3 w-100"
                     style="max-height:160px;object-fit:cover;">
                <button type="button" id="remove-cover-btn"
                        class="btn btn-sm btn-outline-danger mt-2 w-100">
                    <i class="bi bi-trash me-1"></i>Remove image
                </button>
            </div>
            <input type="url" name="cover_image" id="cover-image-input"
                   class="form-control <?php $__errorArgs = ['cover_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="https://example.com/image.jpg"
                   value="<?php echo e(old('cover_image', isset($post) ? $post->cover_image : '')); ?>">
            <small class="text-muted mt-1 d-block" style="font-size:.74rem;">Paste an image URL (jpg, png, webp)</small>
            <?php $__errorArgs = ['cover_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="editor-card mb-3">
            <label class="editor-label mb-2" for="category-input">
                <i class="bi bi-tag me-1 text-muted"></i>Category <span class="text-danger">*</span>
            </label>
            <input type="text" name="category" id="category-input"
                   class="form-control <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   list="category-suggestions"
                   value="<?php echo e(old('category', isset($post) ? $post->category : '')); ?>"
                   placeholder="e.g. Beginner's Guide" required>
            <datalist id="category-suggestions">
                <option value="Beginner's Guide">
                <option value="Market Insights">
                <option value="Trading Tips">
                <option value="Crypto News">
                <option value="Security">
                <option value="How It Works">
                <option value="General">
            </datalist>
            <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="d-flex flex-wrap gap-1 mt-2">
                <?php $__currentLoopData = ["Beginner's Guide","Market Insights","Trading Tips","Crypto News","Security","How It Works","General"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="category-chip" data-value="<?php echo e($cat); ?>"><?php echo e($cat); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="editor-card mb-3 ai-assistant-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="editor-label mb-0"><i class="bi bi-stars me-1 text-primary"></i>AI Assistant (Groq)</div>
                <span id="ai-status" class="badge text-bg-light border">Ready</span>
            </div>

            <label class="form-label small mb-1" for="ai-topic">Topic or prompt</label>
            <textarea id="ai-topic" rows="2" class="form-control form-control-sm mb-2"
                      placeholder="Example: Safe ways to avoid crypto scams in Nigeria"></textarea>

            <label class="form-label small mb-1" for="ai-tone">Tone</label>
            <select id="ai-tone" class="form-select form-select-sm mb-2">
                <option value="informative" selected>Informative</option>
                <option value="professional">Professional</option>
                <option value="friendly">Friendly</option>
                <option value="persuasive">Persuasive</option>
            </select>

            <div class="d-grid gap-2 mb-2">
                <button type="button" id="ai-generate" class="btn btn-sm btn-primary">
                    <i class="bi bi-magic me-1"></i>Generate Full Draft
                </button>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" id="ai-outline" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-list-nested me-1"></i>Outline
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" id="ai-titles" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-type-h1 me-1"></i>Titles
                        </button>
                    </div>
                </div>
            </div>

            <label class="form-label small mb-1" for="ai-improve-action">Improve selected text</label>
            <div class="input-group input-group-sm mb-2">
                <select id="ai-improve-action" class="form-select">
                    <option value="improve" selected>Improve</option>
                    <option value="simplify">Simplify</option>
                    <option value="expand">Expand</option>
                    <option value="formal">Formal</option>
                    <option value="casual">Casual</option>
                    <option value="proofread">Proofread</option>
                </select>
                <button type="button" id="ai-improve" class="btn btn-outline-secondary">Apply</button>
            </div>

            <button type="button" id="ai-excerpt" class="btn btn-sm btn-outline-success w-100">
                <i class="bi bi-card-text me-1"></i>Generate Excerpt from Content
            </button>

            
            <hr style="border-color:rgba(255,255,255,.1);margin:.875rem 0">
            <div class="editor-label mb-2" style="font-size:.72rem;color:#94a3b8"><i class="bi bi-tools me-1"></i>Advanced AI Tools</div>
            <div class="d-grid gap-2">
                <button type="button" id="ai-seo-tags" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-tag me-1"></i>Generate SEO Tags + Meta
                </button>
                <div class="row g-2">
                    <div class="col-7">
                        <select id="ai-social-platform" class="form-select form-select-sm">
                            <option value="twitter">Twitter/X</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                            <option value="linkedin">LinkedIn</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <button type="button" id="ai-social-caption" class="btn btn-sm btn-outline-info w-100">
                            <i class="bi bi-share me-1"></i>Caption
                        </button>
                    </div>
                </div>
                <button type="button" id="ai-content-planner" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-calendar3 me-1"></i>Content Planner (10 ideas)
                </button>
            </div>

            
            <div id="ai-seo-output" class="mt-2" style="display:none">
                <div style="background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.2);border-radius:8px;padding:.75rem">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size:.72rem;color:#38bdf8;font-weight:600">SEO Metadata</span>
                        <button type="button" onclick="document.getElementById('ai-seo-output').style.display='none'"
                            style="background:none;border:none;color:#94a3b8;font-size:.9rem;cursor:pointer">&times;</button>
                    </div>
                    <div id="ai-seo-meta-desc" style="font-size:.78rem;color:#94a3b8;margin-bottom:.5rem"></div>
                    <div id="ai-seo-keywords-box" style="display:flex;flex-wrap:wrap;gap:.3rem"></div>
                </div>
            </div>

            
            <div id="ai-social-output" class="mt-2" style="display:none">
                <div style="background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.2);border-radius:8px;padding:.75rem">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size:.72rem;color:#38bdf8;font-weight:600">Social Caption</span>
                        <button type="button" onclick="document.getElementById('ai-social-output').style.display='none'"
                            style="background:none;border:none;color:#94a3b8;font-size:.9rem;cursor:pointer">&times;</button>
                    </div>
                    <pre id="ai-social-text" style="font-size:.78rem;color:#e4e8f0;white-space:pre-wrap;margin:0;font-family:inherit"></pre>
                    <button type="button" id="ai-social-copy" class="btn btn-sm btn-outline-secondary mt-2" style="font-size:.72rem;padding:.2rem .55rem">
                        <i class="bi bi-clipboard me-1"></i>Copy
                    </button>
                </div>
            </div>

            
            <div id="ai-planner-output" class="mt-2" style="display:none">
                <div style="background:rgba(245,158,11,.04);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:.75rem">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size:.72rem;color:#f59e0b;font-weight:600">Content Ideas</span>
                        <button type="button" onclick="document.getElementById('ai-planner-output').style.display='none'"
                            style="background:none;border:none;color:#94a3b8;font-size:.9rem;cursor:pointer">&times;</button>
                    </div>
                    <div id="ai-planner-list" style="display:flex;flex-direction:column;gap:.4rem"></div>
                </div>
            </div>

            <small class="text-muted d-block mt-2" style="font-size:.72rem;line-height:1.45;">
                Tip: Select text in the editor before clicking Improve.
            </small>
        </div>

        
        <div class="editor-card">
            <div class="editor-label mb-2"><i class="bi bi-search me-1 text-muted"></i>SEO Preview</div>
            <div class="seo-preview rounded-3 p-3">
                <div class="seo-site-name">kayxchange.com &rsaquo; blog</div>
                <div class="seo-title" id="seo-title"><?php echo e(isset($post) && $post->title ? $post->title : 'Post title will appear here'); ?></div>
                <div class="seo-desc" id="seo-desc"><?php echo e(isset($post) && $post->excerpt ? $post->excerpt : 'Excerpt will appear here as the meta description.'); ?></div>
            </div>
        </div>

    </div>
</div>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/admin/blog/_form.blade.php ENDPATH**/ ?>