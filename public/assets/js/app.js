/**
 * Kindergarten Game Organizer - Main JavaScript
 */

(function() {
    'use strict';

    // CSRF Token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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
     * Initialize mobile sidebar toggle
     */
    function initSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('open') &&
                    !sidebar.contains(e.target) &&
                    !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });
        }
    }

    /**
     * Initialize confirm dialogs for delete actions
     */
    function initConfirmDialogs() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function(e) {
                const message = this.dataset.confirm || 'Sind Sie sicher?';
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
                const url = `/api/${type}/toggle-favorite`;

                try {
                    const response = await fetchWithCsrf(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id }),
                    });

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
                placeholderValue: el.dataset.placeholder || 'Auswählen...',
                noResultsText: 'Keine Ergebnisse gefunden',
                noChoicesText: 'Keine Optionen verfügbar',
                itemSelectText: 'Klicken zum Auswählen',
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
            let cropper = null;

            if (!input) return;

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validate file type
                if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
                    alert('Bitte wählen Sie ein gültiges Bildformat (JPG, PNG, GIF, WebP).');
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('Das Bild ist zu groß. Maximale Größe: 10MB.');
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
                                alert('Fehler beim Hochladen des Bildes.');
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
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal-overlay active';
        modal.innerHTML = `
            <div class="modal" style="max-width: 600px;">
                <div class="modal-header">
                    <h3 class="modal-title">Bild zuschneiden</h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="cropperImage" src="${imageSrc}" style="max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelCrop">Abbrechen</button>
                    <button type="button" class="btn btn-primary" id="applyCrop">Zuschneiden</button>
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
        }

        modal.querySelector('.modal-close').addEventListener('click', closeModal);
        modal.querySelector('#cancelCrop').addEventListener('click', closeModal);

        // Apply crop
        modal.querySelector('#applyCrop').addEventListener('click', () => {
            const canvas = cropper.getCroppedCanvas({
                width: 600,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob((blob) => {
                onCrop(blob);
                closeModal();
            }, 'image/webp', 0.85);
        });
    }

    /**
     * Upload image to server
     */
    async function uploadImage(blob, type) {
        const formData = new FormData();
        formData.append('image', blob, 'image.webp');
        formData.append('type', type);

        const response = await fetchWithCsrf('/api/upload-image', {
            method: 'POST',
            body: formData,
        });

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
                        const response = await fetchWithCsrf(`/api/${type}/check-duplicate`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: value, exclude_id: excludeId }),
                        });

                        const data = await response.json();

                        // Remove existing warning
                        if (warningEl) warningEl.remove();

                        if (data.exists) {
                            const warning = document.createElement('div');
                            warning.className = 'duplicate-warning form-hint text-warning';
                            warning.textContent = 'Ein Eintrag mit diesem Namen existiert bereits.';
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
    });

    // Expose utility functions
    window.App = {
        fetchWithCsrf,
        uploadImage,
    };
})();
