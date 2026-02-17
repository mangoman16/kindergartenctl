<?php
$searchPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$searchContext = 'all';
$searchPlaceholder = __('search.placeholder');
if (strpos($searchPath, '/games') === 0 || strpos($searchPath, '/categories') === 0 || strpos($searchPath, '/tags') === 0 || strpos($searchPath, '/groups') === 0) {
    $searchContext = 'game';
    $searchPlaceholder = __('search.placeholder') . ' (' . __('nav.games') . ')';
} elseif (strpos($searchPath, '/materials') === 0) {
    $searchContext = 'material';
    $searchPlaceholder = __('search.placeholder') . ' (' . __('nav.materials') . ')';
} elseif (strpos($searchPath, '/boxes') === 0 || strpos($searchPath, '/locations') === 0) {
    $searchContext = 'box';
    $searchPlaceholder = __('search.placeholder') . ' (' . __('nav.boxes') . ')';
}
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
            <input type="text" id="searchPaletteInput" placeholder="<?= e($searchPlaceholder) ?>" autocomplete="off" data-context="<?= $searchContext ?>">
            <kbd class="search-palette-esc">Esc</kbd>
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
/* Search Command Palette */
(function() {
    var trigger = document.getElementById('searchTrigger');
    var overlay = document.getElementById('searchPaletteOverlay');
    var palette = document.getElementById('searchPalette');
    var input = document.getElementById('searchPaletteInput');
    var body = document.getElementById('searchPaletteBody');
    if (!trigger || !overlay || !input || !body) return;

    var debounceTimer = null;
    var currentQuery = '';
    var searchContext = input.getAttribute('data-context') || 'all';

    var typeIcons = {
        game: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect></svg>',
        material: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
        box: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        tag: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        group: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>'
    };
    var typeLabels = { game: '<?= __('game.title') ?>', material: '<?= __('material.title') ?>', box: '<?= __('box.title') ?>', tag: '<?= __('tag.title') ?>', group: '<?= __('group.title') ?>' };

    function highlightMatch(text, query) {
        if (!query) return text;
        return text.replace(new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>');
    }

    function openPalette() {
        overlay.classList.add('active');
        setTimeout(function() { input.focus(); }, 50);
    }

    function closePalette() {
        overlay.classList.remove('active');
        input.value = '';
        currentQuery = '';
        body.innerHTML = '<div class="search-palette-hint"><p><?= __('search.hint') ?></p></div>';
    }

    function showResults(results, query, moreUrl) {
        if (results.length === 0) {
            body.innerHTML = '<div class="search-palette-empty"><?= __('search.no_results') ?></div>';
            return;
        }
        var html = '<div class="search-palette-results">';
        results.forEach(function(item, idx) {
            var icon = typeIcons[item.type] || '';
            var label = typeLabels[item.type] || item.type;
            var colorDot = item.color ? '<span class="color-dot" style="background:' + item.color + '"></span>' : '';
            html += '<a href="' + item.url + '" class="search-palette-item' + (idx === 0 ? ' active' : '') + '"><span class="search-palette-item-icon">' + icon + '</span><span class="search-palette-item-content"><span class="search-palette-item-name">' + colorDot + highlightMatch(item.name, query) + '</span><span class="search-palette-item-type">' + label + '</span></span></a>';
        });
        html += '</div>';
        html += '<a href="' + moreUrl + '" class="search-palette-more"><?= __('search.show_all') ?></a>';
        body.innerHTML = html;
    }

    function performSearch(query) {
        if (query.length < 2) {
            body.innerHTML = '<div class="search-palette-hint"><p><?= __('search.hint') ?></p></div>';
            return;
        }
        var url = '/api/search?q=' + encodeURIComponent(query);
        if (searchContext !== 'all') url += '&context=' + encodeURIComponent(searchContext);
        fetch(url).then(function(r) { return r.json(); }).then(function(data) {
            if (query === currentQuery) showResults(data.results, data.query, data.more_url);
        }).catch(function() {});
    }

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
            body.innerHTML = '<div class="search-palette-hint"><p><?= __('search.hint') ?></p></div>';
            return;
        }
        debounceTimer = setTimeout(function() { performSearch(q); }, 200);
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closePalette(); return; }
        var items = body.querySelectorAll('.search-palette-item, .search-palette-more');
        var activeItem = body.querySelector('.search-palette-item.active');
        var ci = Array.from(items).indexOf(activeItem);
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            ci = (ci + 1) % items.length;
            if (items[ci]) items[ci].classList.add('active');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            ci = ci <= 0 ? items.length - 1 : ci - 1;
            if (items[ci]) items[ci].classList.add('active');
        } else if (e.key === 'Enter' && activeItem) {
            e.preventDefault();
            window.location.href = activeItem.href;
        }
    });
})();
</script>
