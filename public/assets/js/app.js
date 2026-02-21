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
            el.addEventListener('click', function(e) {
                const message = this.dataset.confirm || t('confirm_default');
                if (!confirm(message)) {
                    e.preventDefault();
                }
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
                    console.error('Error toggling favorite:', error);
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
                                    preview.innerHTML = `<img src="/uploads/${imagePath}" alt="Preview">`;
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
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex !important;align-items:center;justify-content:center;z-index:2000;opacity:1 !important;visibility:visible !important;';
        modal.innerHTML = `
            <div class="cropper-dialog" style="background:var(--color-white, #fff);border-radius:0.75rem;width:90%;max-width:600px;max-height:90vh;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);transform:scale(1) !important;">
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--color-gray-200, #E5E7EB);display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="font-size:1.125rem;font-weight:600;margin:0;">${t('crop_title')}</h3>
                    <button type="button" class="cropper-modal-close" style="background:none;border:none;cursor:pointer;color:var(--color-gray-400,#9CA3AF);padding:0.25rem;display:flex;align-items:center;border-radius:0.375rem;" aria-label="${t('action_close')}"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                </div>
                <div style="padding:1.25rem;overflow-y:auto;max-height:calc(90vh - 140px);">
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="cropperImage" src="${imageSrc}" style="max-width: 100%;">
                    </div>
                </div>
                <div style="padding:1rem 1.25rem;border-top:1px solid var(--color-gray-200, #E5E7EB);display:flex;justify-content:flex-end;gap:0.75rem;">
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
                        console.error('Error checking duplicate:', error);
                    }
                }, 500);
            });
        });
    }

    /**
     * Initialize search functionality
     */
    function initSearch() {
        const searchInput = document.querySelector('.search-form input');
        const searchDropdown = document.querySelector('.search-dropdown');

        if (!searchInput) return;

        let timeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);

            const query = this.value.trim();

            if (query.length < 3) {
                if (searchDropdown) searchDropdown.classList.remove('active');
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);

                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const data = await response.json();

                    if (searchDropdown) {
                        // Populate dropdown with results
                        // Implementation depends on UI design
                    }
                } catch (error) {
                    console.error('Search error:', error);
                }
            }, 300);
        });
    }

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initAlerts();
        initSidebar();
        initConfirmDialogs();
        initFavoriteToggles();
        initChoices();
        initImageUpload();
        initDuplicateCheck();
        initSearch();

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
        t,
    };
})();
