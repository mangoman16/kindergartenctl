<header class="top-header">
    <div class="search-container">
        <form action="<?= url('/search') ?>" method="GET" class="search-form" id="headerSearchForm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="q" id="headerSearchInput" placeholder="<?= __('search.placeholder') ?>" autocomplete="off">
        </form>
        <div class="search-dropdown" id="searchDropdown"></div>
    </div>

    <div class="header-actions">
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
    const btn = document.getElementById('userMenuBtn');
    const dropdown = document.getElementById('userDropdown');
    if (!btn || !dropdown) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('open');
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
</script>

<script<?= cspNonce() ?>>
(function() {
    const searchInput = document.getElementById('headerSearchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchForm = document.getElementById('headerSearchForm');
    let debounceTimer = null;
    let currentQuery = '';

    if (!searchInput || !searchDropdown) return;

    const typeIcons = {
        game: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><line x1="6" y1="12" x2="6" y2="12"></line><line x1="10" y1="12" x2="10" y2="12"></line></svg>',
        material: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
        box: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        tag: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        group: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>'
    };

    const typeLabels = {
        game: '<?= __('game.title') ?>',
        material: '<?= __('material.title') ?>',
        box: '<?= __('box.title') ?>',
        tag: '<?= __('tag.title') ?>',
        group: '<?= __('group.title') ?>'
    };

    function highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function showDropdown(results, query, moreUrl) {
        if (results.length === 0) {
            searchDropdown.innerHTML = '<div class="search-dropdown-empty"><?= __('search.no_results') ?></div>';
            searchDropdown.classList.add('active');
            return;
        }

        let html = '<div class="search-dropdown-results">';
        results.forEach(item => {
            const icon = typeIcons[item.type] || '';
            const label = typeLabels[item.type] || item.type;
            const colorDot = item.color ? `<span class="color-dot" style="background:${item.color}"></span>` : '';

            html += `<a href="${item.url}" class="search-dropdown-item">
                <span class="search-item-icon">${icon}</span>
                <span class="search-item-content">
                    <span class="search-item-name">${colorDot}${highlightMatch(item.name, query)}</span>
                    <span class="search-item-type">${label}</span>
                </span>
            </a>`;
        });
        html += '</div>';

        html += `<a href="${moreUrl}" class="search-dropdown-more"><?= __('search.show_all') ?></a>`;

        searchDropdown.innerHTML = html;
        searchDropdown.classList.add('active');
    }

    function hideDropdown() {
        searchDropdown.classList.remove('active');
    }

    function performSearch(query) {
        if (query.length < 2) {
            hideDropdown();
            return;
        }

        fetch('/api/search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (query === currentQuery) {
                    showDropdown(data.results, data.query, data.more_url);
                }
            })
            .catch(err => {
                console.error('Search error:', err);
                hideDropdown();
            });
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        currentQuery = query;

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            hideDropdown();
            return;
        }

        debounceTimer = setTimeout(() => performSearch(query), 200);
    });

    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideDropdown();
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = searchDropdown.querySelectorAll('.search-dropdown-item, .search-dropdown-more');
        const activeItem = searchDropdown.querySelector('.search-dropdown-item.active');
        let currentIndex = Array.from(items).indexOf(activeItem);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            currentIndex = (currentIndex + 1) % items.length;
            items[currentIndex]?.classList.add('active');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeItem) activeItem.classList.remove('active');
            currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
            items[currentIndex]?.classList.add('active');
        } else if (e.key === 'Enter' && activeItem) {
            e.preventDefault();
            window.location.href = activeItem.href;
        } else if (e.key === 'Escape') {
            hideDropdown();
        }
    });
})();
</script>
