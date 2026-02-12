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
    <div class="search-container">
        <form action="<?= url('/search') ?>" method="GET" class="search-form" id="headerSearchForm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="q" id="headerSearchInput" placeholder="<?= e($searchPlaceholder) ?>" autocomplete="off" data-context="<?= $searchContext ?>">
            <?php if ($searchContext !== 'all'): ?>
                <input type="hidden" name="type" value="<?= $searchContext ?>">
            <?php endif; ?>
        </form>
        <div class="search-dropdown" id="searchDropdown"></div>
    </div>

    <div class="header-actions">
        <button class="header-icon-btn help-toggle-btn" id="helpToggleBtn" title="<?= __('help.title') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><circle cx="12" cy="17" r="0.5" fill="currentColor"></circle></svg>
        </button>

        <button class="header-icon-btn" id="darkModeToggle" title="<?= __('settings.dark_mode') ?>" data-pref="<?= e(userPreference('dark_mode_preference', 'system')) ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sun"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-moon"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-system"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
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
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
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
    var toggle = document.getElementById('darkModeToggle');
    if (!toggle) return;
    var html = document.documentElement;
    var currentPref = toggle.getAttribute('data-pref') || 'system';

    function getSystemTheme() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(pref) {
        currentPref = pref;
        toggle.setAttribute('data-pref', pref);
        html.setAttribute('data-dark-mode-pref', pref);
        if (pref === 'system') {
            html.setAttribute('data-theme', getSystemTheme());
        } else {
            html.setAttribute('data-theme', pref);
        }
    }

    // Listen for system theme changes when in system mode
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            if (currentPref === 'system') {
                html.setAttribute('data-theme', getSystemTheme());
            }
        });
    }

    toggle.addEventListener('click', function() {
        // Cycle: system -> light -> dark -> system
        var nextPref = currentPref === 'system' ? 'light' : (currentPref === 'light' ? 'dark' : 'system');
        applyTheme(nextPref);
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            fetch('<?= url('/settings/dark-mode') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrfMeta.content},
                body: 'csrf_token=' + encodeURIComponent(csrfMeta.content) + '&dark_mode_preference=' + encodeURIComponent(nextPref)
            });
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

(function() {
    var contextSidebar = document.getElementById('contextSidebar');
    document.querySelectorAll('.rail-btn[data-section]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var section = this.dataset.section;
            var href = this.dataset.href;
            if (section === 'home') { window.location.href = href; return; }
            if (!contextSidebar) { if (href) window.location.href = href; return; }
            if (contextSidebar.classList.contains('open') && contextSidebar.dataset.active === section && href) { window.location.href = href; return; }
            contextSidebar.querySelectorAll('.ctx-section').forEach(function(s) { s.classList.remove('visible'); });
            var targetSection = contextSidebar.querySelector('.ctx-section[data-for="' + section + '"]');
            if (targetSection) { targetSection.classList.add('visible'); contextSidebar.classList.add('open'); contextSidebar.dataset.active = section; }
            if (href) { window.location.href = href; return; }
            document.querySelectorAll('.rail-btn[data-section]').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });
})();
</script>

<script<?= cspNonce() ?>>
(function() {
    var searchInput = document.getElementById('headerSearchInput');
    var searchDropdown = document.getElementById('searchDropdown');
    var debounceTimer = null;
    var currentQuery = '';
    if (!searchInput || !searchDropdown) return;

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

    function showDropdown(results, query, moreUrl) {
        if (results.length === 0) { searchDropdown.innerHTML = '<div class="search-dropdown-empty"><?= __('search.no_results') ?></div>'; searchDropdown.classList.add('active'); return; }
        var html = '<div class="search-dropdown-results">';
        results.forEach(function(item) {
            var icon = typeIcons[item.type] || '';
            var label = typeLabels[item.type] || item.type;
            var colorDot = item.color ? '<span class="color-dot" style="background:' + item.color + '"></span>' : '';
            html += '<a href="' + item.url + '" class="search-dropdown-item"><span class="search-item-icon">' + icon + '</span><span class="search-item-content"><span class="search-item-name">' + colorDot + highlightMatch(item.name, query) + '</span><span class="search-item-type">' + label + '</span></span></a>';
        });
        html += '</div><a href="' + moreUrl + '" class="search-dropdown-more"><?= __('search.show_all') ?></a>';
        searchDropdown.innerHTML = html;
        searchDropdown.classList.add('active');
    }

    function hideDropdown() { searchDropdown.classList.remove('active'); }

    var searchContext = searchInput.getAttribute('data-context') || 'all';

    function performSearch(query) {
        if (query.length < 2) { hideDropdown(); return; }
        var url = '/api/search?q=' + encodeURIComponent(query);
        if (searchContext !== 'all') url += '&context=' + encodeURIComponent(searchContext);
        fetch(url).then(function(r) { return r.json(); }).then(function(data) { if (query === currentQuery) showDropdown(data.results, data.query, data.more_url); }).catch(function() { hideDropdown(); });
    }

    searchInput.addEventListener('input', function() { var q = this.value.trim(); currentQuery = q; clearTimeout(debounceTimer); if (q.length < 2) { hideDropdown(); return; } debounceTimer = setTimeout(function() { performSearch(q); }, 200); });
    searchInput.addEventListener('focus', function() { var q = this.value.trim(); if (q.length >= 2) performSearch(q); });
    document.addEventListener('click', function(e) { if (!e.target.closest('.search-container')) hideDropdown(); });
    searchInput.addEventListener('keydown', function(e) {
        var items = searchDropdown.querySelectorAll('.search-dropdown-item, .search-dropdown-more');
        var activeItem = searchDropdown.querySelector('.search-dropdown-item.active');
        var ci = Array.from(items).indexOf(activeItem);
        if (e.key === 'ArrowDown') { e.preventDefault(); if (activeItem) activeItem.classList.remove('active'); ci = (ci + 1) % items.length; if (items[ci]) items[ci].classList.add('active'); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); if (activeItem) activeItem.classList.remove('active'); ci = ci <= 0 ? items.length - 1 : ci - 1; if (items[ci]) items[ci].classList.add('active'); }
        else if (e.key === 'Enter' && activeItem) { e.preventDefault(); window.location.href = activeItem.href; }
        else if (e.key === 'Escape') hideDropdown();
    });
})();
</script>
