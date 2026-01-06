<header class="top-header">
    <form action="<?= url('/search') ?>" method="GET" class="search-form">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-gray-400)">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" name="q" placeholder="<?= __('search.placeholder') ?>" autocomplete="off">
    </form>

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
