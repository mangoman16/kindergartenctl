/**
 * Kindergarten Game Organizer - Main JavaScript
 */

(function() {
    'use strict';

    // CSRF Token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Translation helper - falls back to key if not found
    function t(key) {
        return (window.AppTranslations && window.AppTranslations[key]) || key;
    }

    /**
     * Add CSRF token to fetch requests
     */
    function fetchWithCsrf(url, options = {}) {
        options.headers = options.headers || {};
        if (csrfToken) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return fetch(url, options);
    }

    /**
     * Show a flash error message (consistent AJAX error feedback)
     */
    function showFlashError(message) {
        const container = document.querySelector('.main-content') || document.body;
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.style.margin = 'var(--spacing-4) var(--spacing-6)';
        alert.innerHTML =
            '<span class="alert-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></span>' +
            '<span class="alert-message">' + message + '</span>' +
            '<button class="alert-close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
        alert.querySelector('.alert-close').addEventListener('click', function() {
            alert.remove();
        });
        container.prepend(alert);
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }

    /**
     * Initialize alert close buttons
     */
    function initAlerts() {
        document.querySelectorAll('.alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert').remove();
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }

    /**
     * Initialize sidebar + mobile overlay
     */
    function initSidebar() {
        const overlay = document.getElementById('sidebarOverlay');
        const contextSidebar = document.querySelector('.context-sidebar');

        if (!overlay || !contextSidebar) return;

        function isMobile() { return window.innerWidth <= 768; }

        // Observe sidebar open state to toggle overlay on mobile
        const observer = new MutationObserver(() => {
            if (isMobile()) {
                overlay.classList.toggle('active', contextSidebar.classList.contains('open'));
            }
        });
        observer.observe(contextSidebar, { attributes: true, attributeFilter: ['class'] });

        // Close sidebar when overlay is clicked
        overlay.addEventListener('click', () => {
            contextSidebar.classList.remove('open');
            overlay.classList.remove('active');
            // Persist collapsed state
            try { localStorage.setItem('sidebarCollapsed', 'true'); } catch(e) {}
        });

        // Hide overlay on resize to desktop
        window.addEventListener('resize', () => {
            if (!isMobile()) overlay.classList.remove('active');
        });
    }

    /**
     * Initialize confirm dialogs for delete actions
     */
    function initConfirmDialogs() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            // Forms confirm on submit (covers button click and Enter); other
            // elements (links/buttons) confirm on click.
            const eventName = el.tagName === 'FORM' ? 'submit' : 'click';
            el.addEventListener(eventName, function(e) {
                const message = this.dataset.confirm || t('confirm_default');
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Auto-submit a form when a tagged control changes. CSP-safe replacement
     * for inline onchange="this.form.submit()".
     */
    function initAutoSubmit() {
        document.querySelectorAll('.js-auto-submit').forEach(el => {
            el.addEventListener('change', function() {
                if (this.form) this.form.submit();
            });
        });
    }

    /**
     * Proxy buttons that open a hidden file picker. CSP-safe replacement for
     * inline onclick="this.previousElementSibling.click()". Targets the element
     * named by data-target, falling back to the previous sibling.
     */
    function initFileTriggers() {
        document.querySelectorAll('.js-file-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.dataset.target
                    ? document.getElementById(this.dataset.target)
                    : this.previousElementSibling;
                if (target) target.click();
            });
        });
    }

    /**
     * Initialize favorite toggle buttons
     */
    function initFavoriteToggles() {
        document.querySelectorAll('.favorite-toggle').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const type = this.dataset.type;
                const id = this.dataset.id;
                const url = `/api/${type}/${id}/toggle-favorite`;

                try {
                    const response = await fetchWithCsrf(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id }),
                    });

                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const data = await response.json();

                    if (data.success) {
                        this.classList.toggle('active', data.is_favorite);
                    }
                } catch (error) {
                    showFlashError(t('error_generic'));
                }
            });
        });
    }

    /**
     * Initialize Choices.js for select elements
     */
    function initChoices() {
        if (typeof Choices === 'undefined') return;

        document.querySelectorAll('select[data-choices]').forEach(el => {
            new Choices(el, {
                removeItemButton: true,
                searchEnabled: true,
                placeholder: true,
                placeholderValue: el.dataset.placeholder || t('select_placeholder'),
                noResultsText: t('no_results'),
                noChoicesText: t('no_options'),
                itemSelectText: t('click_to_select'),
            });
        });
    }

    /**
     * Initialize image upload with Cropper.js
     */
    function initImageUpload() {
        const uploadContainers = document.querySelectorAll('.image-upload-container');

        uploadContainers.forEach(container => {
            const input = container.querySelector('input[type="file"]');
            const preview = container.querySelector('.image-preview');
            const hiddenInput = container.querySelector('input[type="hidden"]');

            if (!input) return;

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file type
                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
                    alert(t('invalid_image_format'));
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert(t('image_too_large'));
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    // Show cropper modal
                    showCropperModal(event.target.result, (croppedBlob) => {
                        // Upload cropped image
                        uploadImage(croppedBlob, container.dataset.type)
                            .then(imagePath => {
                                if (hiddenInput) {
                                    hiddenInput.value = imagePath;
                                }
                                if (preview) {
                                    preview.textContent = '';
                                    const img = document.createElement('img');
                                    img.src = '/uploads/' + imagePath;
                                    img.alt = 'Preview';
                                    preview.appendChild(img);
                                }
                            })
                            .catch(error => {
                                console.error('Upload failed:', error);
                                alert(t('upload_error'));
                            });
                    });
                };
                reader.readAsDataURL(file);
            });
        });
    }

    /**
     * Show cropper modal
     */
    function showCropperModal(imageSrc, onCrop) {
        // Create modal with unique class names to avoid conflicts with calendar modal
        const modal = document.createElement('div');
        modal.className = 'cropper-modal-overlay';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex !important;align-items:center;justify-content:center;z-index:10000;opacity:1 !important;visibility:visible !important;';
        // No inline style attributes here: the CSP strips them. All dialog
        // styling lives in style.css under .cropper-dialog-*.
        modal.innerHTML = `
            <div class="cropper-dialog">
                <div class="cropper-dialog-header">
                    <h3 class="cropper-dialog-title">${t('crop_title')}</h3>
                    <button type="button" class="cropper-modal-close" aria-label="${t('action_close')}"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                </div>
                <div class="cropper-dialog-body">
                    <div class="cropper-dialog-stage">
                        <img id="cropperImage" src="${imageSrc}">
                    </div>
                </div>
                <div class="cropper-dialog-footer">
                    <button type="button" class="btn btn-secondary" id="cancelCrop">${t('crop_cancel')}</button>
                    <button type="button" class="btn btn-primary" id="applyCrop">${t('crop_apply')}</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Initialize Cropper
        const image = modal.querySelector('#cropperImage');
        const cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });

        // Close modal
        function closeModal() {
            cropper.destroy();
            modal.remove();
            document.removeEventListener('keydown', handleEscape);
        }

        modal.querySelector('.cropper-modal-close').addEventListener('click', closeModal);
        modal.querySelector('#cancelCrop').addEventListener('click', closeModal);

        // Close on overlay click (clicking outside the dialog)
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close on Escape key
        function handleEscape(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        }
        document.addEventListener('keydown', handleEscape);

        // Apply crop
        modal.querySelector('#applyCrop').addEventListener('click', () => {
            const applyBtn = modal.querySelector('#applyCrop');
            applyBtn.disabled = true;
            applyBtn.textContent = t('crop_processing');

            const canvas = cropper.getCroppedCanvas({
                width: 600,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Try WebP first, fall back to JPEG for browser compatibility
            function tryBlob(mimeType, quality, fallbackMime) {
                const timeout = setTimeout(() => {
                    applyBtn.disabled = false;
                    applyBtn.textContent = t('crop_apply');
                    alert(t('crop_error'));
                }, 10000);

                canvas.toBlob((blob) => {
                    clearTimeout(timeout);
                    if (blob) {
                        onCrop(blob);
                        closeModal();
                    } else if (fallbackMime) {
                        tryBlob(fallbackMime, 0.9, null);
                    } else {
                        applyBtn.disabled = false;
                        applyBtn.textContent = t('crop_apply');
                        alert(t('crop_error'));
                    }
                }, mimeType, quality);
            }

            tryBlob('image/webp', 0.85, 'image/jpeg');
        });
    }

    /**
     * Upload image to server
     */
    async function uploadImage(blob, type) {
        const formData = new FormData();
        const ext = blob.type === 'image/webp' ? 'webp' : (blob.type === 'image/png' ? 'png' : 'jpg');
        formData.append('image', blob, 'image.' + ext);
        formData.append('type', type);

        const response = await fetchWithCsrf('/api/upload-image', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Upload failed');
        }

        return data.path;
    }

    /**
     * Initialize duplicate checking
     */
    function initDuplicateCheck() {
        document.querySelectorAll('[data-check-duplicate]').forEach(input => {
            let timeout;

            input.addEventListener('input', function() {
                clearTimeout(timeout);

                const value = this.value.trim();
                const type = this.dataset.checkDuplicate;
                const excludeId = this.dataset.excludeId;
                const warningEl = this.closest('.form-group').querySelector('.duplicate-warning');

                if (value.length < 2) {
                    if (warningEl) warningEl.remove();
                    return;
                }

                timeout = setTimeout(async () => {
                    try {
                        const params = new URLSearchParams({ type, value: value, exclude_id: excludeId || '' });
                        const response = await fetch(`/api/check-duplicate?${params.toString()}`);

                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        const data = await response.json();

                        // Remove existing warning
                        if (warningEl) warningEl.remove();

                        if (data.exists) {
                            const warning = document.createElement('div');
                            warning.className = 'duplicate-warning form-hint text-warning';
                            warning.textContent = t('duplicate_exists');
                            this.closest('.form-group').appendChild(warning);
                        }
                    } catch (error) {
                        // Duplicate check is non-critical, silent fail is acceptable
                        console.error('Error checking duplicate:', error);
                    }
                }, 500);
            });
        });
    }

    /**
     * Apply data-bg / data-fg attributes as element styles. The CSP blocks
     * inline style="..." attributes (no 'unsafe-inline' in style-src), but
     * styling via the CSSOM is allowed — so dynamic per-element colors (tag
     * badges, color dots, swatches) are declared as data attributes and
     * applied here. Runs on DOM ready; call App.applyDataStyles(container)
     * again after injecting new markup (e.g. search results).
     */
    function applyDataStyles(root) {
        (root || document).querySelectorAll('[data-bg]').forEach(el => {
            el.style.backgroundColor = el.dataset.bg;
        });
        (root || document).querySelectorAll('[data-fg]').forEach(el => {
            el.style.color = el.dataset.fg;
        });
    }

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initAlerts();
        initSidebar();
        initConfirmDialogs();
        initAutoSubmit();
        initFileTriggers();
        applyDataStyles();
        initFavoriteToggles();
        initChoices();
        initImageUpload();
        initDuplicateCheck();

        // Prevent browser autocomplete from cross-contaminating form fields
        document.querySelectorAll('form textarea, form input[type="text"]').forEach(input => {
            if (!input.hasAttribute('autocomplete')) {
                input.setAttribute('autocomplete', 'off');
            }
        });

        // Specifically fix description fields - use unique names for autocomplete
        document.querySelectorAll('textarea[name="description"]').forEach(textarea => {
            textarea.setAttribute('autocomplete', 'off');
            textarea.setAttribute('name', 'description');
        });

        // User dropdown
        document.addEventListener('click', function(e) {
            const wrapper = document.querySelector('.user-menu-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                wrapper.classList.remove('open');
            }
        });
    });

    // Expose utility functions
    window.App = {
        fetchWithCsrf,
        uploadImage,
        applyDataStyles,
        t,
    };
})();
