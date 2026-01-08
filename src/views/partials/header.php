<header class="top-header">
    <div class="search-container">
        <form action="<?= url('/search') ?>" method="GET" class="search-form" id="headerSearchForm">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-gray-400)">
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
        <div class="user-menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span><?= e($user['username']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</header>

<script>
(function() {
    const searchInput = document.getElementById('headerSearchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchForm = document.getElementById('headerSearchForm');
    let debounceTimer = null;
    let currentQuery = '';

    if (!searchInput || !searchDropdown) return;

    // Type icons
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

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideDropdown();
        }
    });

    // Keyboard navigation
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
