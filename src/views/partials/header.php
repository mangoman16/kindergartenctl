<?php
$searchPlaceholder = __('search.global_placeholder');
?>
<header class="top-header">
    <button class="search-trigger" id="searchTrigger">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <span class="search-trigger-text"><?= __('search.placeholder') ?></span>
        <kbd class="search-trigger-kbd">Ctrl+K</kbd>
    </button>

    <div class="header-actions">
        <button class="header-icon-btn help-toggle-btn" id="helpToggleBtn" title="<?= __('help.title') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><circle cx="12" cy="17" r="0.5" fill="currentColor"></circle></svg>
        </button>

        <?php $user = currentUser(); ?>
        <?php if ($user): ?>
        <div class="user-menu-wrapper">
            <button class="user-menu-btn" id="userMenuBtn">
                <span class="user-avatar"><?= strtoupper(mb_substr($user['username'] ?? 'U', 0, 1)) ?></span>
                <span class="user-menu-name"><?= e($user['username'] ?? 'User') ?></span>
                <svg class="user-menu-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <span class="user-avatar user-avatar-lg"><?= strtoupper(mb_substr($user['username'] ?? 'U', 0, 1)) ?></span>
                    <div>
                        <div class="user-dropdown-name"><?= e($user['username'] ?? 'User') ?></div>
                        <div class="user-dropdown-email"><?= e($user['email'] ?? '') ?></div>
                    </div>
                </div>
                <div class="user-dropdown-items">
                    <a href="<?= url('/user/settings') ?>" class="user-dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?= __('user.settings') ?>
                    </a>
                    <a href="<?= url('/settings') ?>" class="user-dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <?= __('nav.settings') ?>
                    </a>
                </div>
                <div class="user-dropdown-footer">
                    <form action="<?= url('/logout') ?>" method="POST" style="margin: 0;">
                        <?= csrfField() ?>
                        <button type="submit" class="user-dropdown-item user-dropdown-logout">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <?= __('auth.logout') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>

<!-- Search Command Palette -->
<div class="search-palette-overlay" id="searchPaletteOverlay">
    <div class="search-palette" id="searchPalette">
        <div class="search-palette-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="searchPaletteInput" placeholder="<?= e($searchPlaceholder) ?>" autocomplete="off">
            <kbd class="search-palette-esc">Esc</kbd>
        </div>
        <div class="search-palette-filters" id="searchFilters">
            <button type="button" class="search-filter-chip active" data-type="all"><?= __('search.all') ?></button>
            <button type="button" class="search-filter-chip" data-type="game"><?= __('nav.games') ?></button>
            <button type="button" class="search-filter-chip" data-type="material"><?= __('nav.materials') ?></button>
            <button type="button" class="search-filter-chip" data-type="box"><?= __('nav.boxes') ?></button>
            <button type="button" class="search-filter-chip" data-type="tag"><?= __('nav.tags') ?></button>
            <button type="button" class="search-filter-chip" data-type="group"><?= __('nav.groups') ?></button>
        </div>
        <div class="search-palette-body" id="searchPaletteBody">
            <div class="search-palette-hint">
                <p><?= __('search.hint') ?></p>
            </div>
        </div>
        <div class="search-palette-footer">
            <span class="search-palette-footer-item"><kbd>&uarr;</kbd><kbd>&darr;</kbd> <?= __('search.navigate') ?></span>
            <span class="search-palette-footer-item"><kbd>&crarr;</kbd> <?= __('search.open') ?></span>
            <span class="search-palette-footer-item"><kbd>Esc</kbd> <?= __('search.close') ?></span>
        </div>
    </div>
</div>

<script<?= cspNonce() ?>>
(function() {
    var btn = document.getElementById('userMenuBtn');
    var dropdown = document.getElementById('userDropdown');
    if (!btn || !dropdown) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('open');
        btn.classList.toggle('active');
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-menu-wrapper')) {
            dropdown.classList.remove('open');
            btn.classList.remove('active');
        }
    });
})();

(function() {
    var helpBtn = document.getElementById('helpToggleBtn');
    var helpPanel = document.getElementById('helpPanel');
    var helpClose = document.getElementById('helpPanelClose');
    if (!helpBtn || !helpPanel) return;
    helpBtn.addEventListener('click', function() {
        helpPanel.classList.toggle('open');
        var currentPage = helpPanel.dataset.currentPage;
        var activeSection = helpPanel.querySelector('.help-section[data-page="' + currentPage + '"]');
        if (activeSection && helpPanel.classList.contains('open')) {
            setTimeout(function() { activeSection.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
        }
    });
    if (helpClose) {
        helpClose.addEventListener('click', function() { helpPanel.classList.remove('open'); });
    }
    helpPanel.querySelectorAll('.help-toc-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.getElementById(this.getAttribute('href').substring(1));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            helpPanel.querySelectorAll('.help-toc-item').forEach(function(i) { i.classList.remove('active'); });
            this.classList.add('active');
        });
    });
})();

(function() {
    var btn = document.getElementById('quickCreateBtn');
    var popup = document.getElementById('quickCreatePopup');
    if (!btn || !popup) return;
    btn.addEventListener('click', function(e) { e.stopPropagation(); popup.classList.toggle('open'); });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.quick-create-popup') && !e.target.closest('#quickCreateBtn')) popup.classList.remove('open');
    });
})();

/* Sidebar toggle + context sidebar persistence */
(function() {
    var contextSidebar = document.getElementById('contextSidebar');
    var toggleBtn = document.getElementById('sidebarToggleBtn');
    var sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply collapsed state on load
    if (sidebarCollapsed && contextSidebar) {
        contextSidebar.classList.remove('open');
        contextSidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    // Restore sidebar section on pages without a context sidebar (e.g. home)
    if (contextSidebar && !sidebarCollapsed) {
        if (!contextSidebar.classList.contains('open')) {
            var savedSection = localStorage.getItem('ctxSidebarSection');
            if (savedSection) {
                var savedTarget = contextSidebar.querySelector('.ctx-section[data-for="' + savedSection + '"]');
                if (savedTarget) {
                    savedTarget.classList.add('visible');
                    contextSidebar.classList.add('open');
                    contextSidebar.dataset.active = savedSection;
                    document.querySelectorAll('.rail-btn[data-section]').forEach(function(b) { b.classList.remove('active'); });
                    var matchBtn = document.querySelector('.rail-btn[data-section="' + savedSection + '"]');
                    if (matchBtn) matchBtn.classList.add('active');
                }
            }
        } else if (contextSidebar.dataset.active) {
            localStorage.setItem('ctxSidebarSection', contextSidebar.dataset.active);
        }
    }

    // Toggle button handler
    if (toggleBtn && contextSidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebarCollapsed = !sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
            if (sidebarCollapsed) {
                contextSidebar.classList.remove('open');
                contextSidebar.classList.add('collapsed');
                document.body.classList.add('sidebar-collapsed');
            } else {
                contextSidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');
                // Restore last section
                var savedSection = localStorage.getItem('ctxSidebarSection');
                if (savedSection) {
                    var savedTarget = contextSidebar.querySelector('.ctx-section[data-for="' + savedSection + '"]');
                    if (savedTarget) {
                        savedTarget.classList.add('visible');
                        contextSidebar.classList.add('open');
                        contextSidebar.dataset.active = savedSection;
                    }
                }
            }
        });
    }

    document.querySelectorAll('.rail-btn[data-section]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var section = this.dataset.section;
            var href = this.dataset.href;
            if (section === 'home') { window.location.href = href; return; }
            if (!contextSidebar) { if (href) window.location.href = href; return; }
            // If sidebar is collapsed, expand it first
            if (sidebarCollapsed) {
                sidebarCollapsed = false;
                localStorage.setItem('sidebarCollapsed', false);
                contextSidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');
            }
            if (contextSidebar.classList.contains('open') && contextSidebar.dataset.active === section && href) { window.location.href = href; return; }
            contextSidebar.querySelectorAll('.ctx-section').forEach(function(s) { s.classList.remove('visible'); });
            var targetSection = contextSidebar.querySelector('.ctx-section[data-for="' + section + '"]');
            if (targetSection) {
                targetSection.classList.add('visible');
                contextSidebar.classList.add('open');
                contextSidebar.dataset.active = section;
                localStorage.setItem('ctxSidebarSection', section);
            }
            if (href) { window.location.href = href; return; }
            document.querySelectorAll('.rail-btn[data-section]').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });
})();
</script>

<script<?= cspNonce() ?>>
/* Global Search Command Palette */
(function() {
    var trigger = document.getElementById('searchTrigger');
    var overlay = document.getElementById('searchPaletteOverlay');
    var palette = document.getElementById('searchPalette');
    var input = document.getElementById('searchPaletteInput');
    var body = document.getElementById('searchPaletteBody');
    var filtersEl = document.getElementById('searchFilters');
    if (!trigger || !overlay || !input || !body) return;

    var debounceTimer = null;
    var currentQuery = '';
    var activeFilter = 'all';
    var HISTORY_KEY = 'searchHistory';
    var RECENT_KEY = 'searchRecent';
    var MAX_HISTORY = 8;
    var MAX_RECENT = 6;

    var typeIcons = {
        game: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>',
        material: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
        box: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        tag: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        group: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>'
    };
    var typeLabels = { game: '<?= __('game.title') ?>', material: '<?= __('material.title') ?>', box: '<?= __('box.title') ?>', tag: '<?= __('tag.title') ?>', group: '<?= __('group.title') ?>' };

    function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function highlightMatch(text, query) {
        if (!query) return escHtml(text);
        var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return escHtml(text).replace(new RegExp('(' + escaped + ')', 'gi'), '<mark>$1</mark>');
    }

    /* --- History & Recent management --- */
    function getHistory() {
        try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveHistory(query) {
        var h = getHistory().filter(function(q) { return q !== query; });
        h.unshift(query);
        if (h.length > MAX_HISTORY) h = h.slice(0, MAX_HISTORY);
        localStorage.setItem(HISTORY_KEY, JSON.stringify(h));
    }
    function getRecent() {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveRecent(item) {
        var r = getRecent().filter(function(i) { return i.url !== item.url; });
        r.unshift({ name: item.name, type: item.type, url: item.url });
        if (r.length > MAX_RECENT) r = r.slice(0, MAX_RECENT);
        localStorage.setItem(RECENT_KEY, JSON.stringify(r));
    }
    function clearHistory() {
        localStorage.removeItem(HISTORY_KEY);
        localStorage.removeItem(RECENT_KEY);
        showStartScreen();
    }

    /* --- Render start screen (history + recent) --- */
    function showStartScreen() {
        var history = getHistory();
        var recent = getRecent();
        if (history.length === 0 && recent.length === 0) {
            body.innerHTML = '<div class="search-palette-hint"><p><?= __('search.hint') ?></p></div>';
            return;
        }
        var html = '';
        if (history.length > 0) {
            html += '<div class="search-palette-section"><div class="search-palette-section-header"><span><?= __('search.recent_searches') ?></span><button type="button" class="search-palette-clear-btn" id="clearSearchHistory"><?= __('action.clear') ?></button></div>';
            history.forEach(function(q) {
                html += '<a href="#" class="search-palette-item search-history-item" data-query="' + escHtml(q) + '"><span class="search-palette-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg></span><span class="search-palette-item-content"><span class="search-palette-item-name">' + escHtml(q) + '</span></span></a>';
            });
            html += '</div>';
        }
        if (recent.length > 0) {
            html += '<div class="search-palette-section"><div class="search-palette-section-header"><span><?= __('search.recently_found') ?></span></div>';
            recent.forEach(function(item) {
                var icon = typeIcons[item.type] || '';
                var label = typeLabels[item.type] || item.type;
                html += '<a href="' + escHtml(item.url) + '" class="search-palette-item"><span class="search-palette-item-icon">' + icon + '</span><span class="search-palette-item-content"><span class="search-palette-item-name">' + escHtml(item.name) + '</span><span class="search-palette-item-type">' + label + '</span></span></a>';
            });
            html += '</div>';
        }
        body.innerHTML = html;

        // Bind clear history button
        var clearBtn = document.getElementById('clearSearchHistory');
        if (clearBtn) clearBtn.addEventListener('click', function(e) { e.stopPropagation(); clearHistory(); });

        // Bind history items to re-run search
        body.querySelectorAll('.search-history-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var q = this.dataset.query;
                input.value = q;
                currentQuery = q;
                performSearch(q);
            });
        });
    }

    function openPalette() {
        overlay.classList.add('active');
        showStartScreen();
        setTimeout(function() { input.focus(); }, 50);
    }

    function closePalette() {
        overlay.classList.remove('active');
        input.value = '';
        currentQuery = '';
        activeFilter = 'all';
        filtersEl.querySelectorAll('.search-filter-chip').forEach(function(c) { c.classList.remove('active'); });
        filtersEl.querySelector('[data-type="all"]').classList.add('active');
    }

    function showResults(results, query, moreUrl) {
        // Filter by active type filter
        var filtered = activeFilter === 'all' ? results : results.filter(function(r) { return r.type === activeFilter; });

        if (filtered.length === 0) {
            body.innerHTML = '<div class="search-palette-empty"><?= __('search.no_results') ?></div>';
            return;
        }

        // Group by type
        var grouped = {};
        filtered.forEach(function(item) {
            if (!grouped[item.type]) grouped[item.type] = [];
            grouped[item.type].push(item);
        });

        var html = '';
        var firstItem = true;
        Object.keys(grouped).forEach(function(type) {
            var label = typeLabels[type] || type;
            html += '<div class="search-palette-section"><div class="search-palette-section-header"><span>' + label + '</span></div>';
            grouped[type].forEach(function(item) {
                var icon = typeIcons[item.type] || '';
                var colorDot = item.color ? '<span class="color-dot" style="background:' + escHtml(item.color) + '"></span>' : '';
                html += '<a href="' + escHtml(item.url) + '" class="search-palette-item' + (firstItem ? ' active' : '') + '" data-result-name="' + escHtml(item.name) + '" data-result-type="' + escHtml(item.type) + '" data-result-url="' + escHtml(item.url) + '"><span class="search-palette-item-icon">' + icon + '</span><span class="search-palette-item-content"><span class="search-palette-item-name">' + colorDot + highlightMatch(item.name, query) + '</span><span class="search-palette-item-type">' + label + '</span></span></a>';
                firstItem = false;
            });
            html += '</div>';
        });

        // Update filter chip counts
        var allTypes = ['game', 'material', 'box', 'tag', 'group'];
        allTypes.forEach(function(t) {
            var chip = filtersEl.querySelector('[data-type="' + t + '"]');
            var count = results.filter(function(r) { return r.type === t; }).length;
            if (chip) {
                var countSpan = chip.querySelector('.filter-count');
                if (count > 0) {
                    if (!countSpan) { countSpan = document.createElement('span'); countSpan.className = 'filter-count'; chip.appendChild(countSpan); }
                    countSpan.textContent = count;
                } else if (countSpan) {
                    countSpan.remove();
                }
            }
        });

        body.innerHTML = html;

        // Track clicked items as recent
        body.querySelectorAll('.search-palette-item').forEach(function(el) {
            el.addEventListener('click', function() {
                saveRecent({ name: this.dataset.resultName, type: this.dataset.resultType, url: this.dataset.resultUrl });
                saveHistory(query);
            });
        });
    }

    var lastResults = [];
    var lastQuery = '';
    var lastMoreUrl = '';

    function performSearch(query) {
        if (query.length < 2) {
            showStartScreen();
            return;
        }
        body.innerHTML = '<div class="search-palette-hint"><p><?= __('misc.loading') ?></p></div>';
        var url = '/api/search?q=' + encodeURIComponent(query);
        fetch(url).then(function(r) { return r.json(); }).then(function(data) {
            if (query === currentQuery) {
                lastResults = data.results;
                lastQuery = data.query;
                lastMoreUrl = data.more_url;
                showResults(data.results, data.query, data.more_url);
                saveHistory(query);
            }
        }).catch(function() {});
    }

    /* --- Filter chips --- */
    filtersEl.querySelectorAll('.search-filter-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            filtersEl.querySelectorAll('.search-filter-chip').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            activeFilter = this.dataset.type;
            // Re-render results with current filter
            if (lastResults.length > 0 && currentQuery.length >= 2) {
                showResults(lastResults, lastQuery, lastMoreUrl);
            }
        });
    });

    // Trigger button opens palette
    trigger.addEventListener('click', openPalette);

    // Ctrl+K / Cmd+K shortcut
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (overlay.classList.contains('active')) { closePalette(); } else { openPalette(); }
        }
    });

    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePalette();
    });

    // Input handling
    input.addEventListener('input', function() {
        var q = this.value.trim();
        currentQuery = q;
        clearTimeout(debounceTimer);
        if (q.length < 2) {
            lastResults = [];
            showStartScreen();
            return;
        }
        debounceTimer = setTimeout(function() { performSearch(q); }, 200);
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closePalette(); return; }
        var items = body.querySelectorAll('.search-palette-item');
        var activeItem = body.querySelector('.search-palette-item.active');
        var ci = Array.from(items).indexOf(activeItem);
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            ci = (ci + 1) % items.length;
            if (items[ci]) { items[ci].classList.add('active'); items[ci].scrollIntoView({ block: 'nearest' }); }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            ci = ci <= 0 ? items.length - 1 : ci - 1;
            if (items[ci]) { items[ci].classList.add('active'); items[ci].scrollIntoView({ block: 'nearest' }); }
        } else if (e.key === 'Enter' && activeItem) {
            e.preventDefault();
            if (activeItem.classList.contains('search-history-item')) {
                var q = activeItem.dataset.query;
                input.value = q;
                currentQuery = q;
                performSearch(q);
            } else {
                saveRecent({ name: activeItem.dataset.resultName, type: activeItem.dataset.resultType, url: activeItem.dataset.resultUrl });
                window.location.href = activeItem.href;
            }
        }
    });
})();
</script>
