<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$navSection = 'home';
if (strpos($currentPath, '/games') === 0 || strpos($currentPath, '/categories') === 0 || strpos($currentPath, '/tags') === 0 || strpos($currentPath, '/groups') === 0) {
    $navSection = 'games';
} elseif (strpos($currentPath, '/materials') === 0 || strpos($currentPath, '/boxes') === 0 || strpos($currentPath, '/locations') === 0) {
    $navSection = 'inventory';
} elseif (strpos($currentPath, '/calendar') === 0) {
    $navSection = 'calendar';
} elseif (strpos($currentPath, '/changelog') === 0 || strpos($currentPath, '/settings') === 0 || strpos($currentPath, '/user/settings') === 0) {
    $navSection = 'settings';
}
$hasContextSidebar = in_array($navSection, ['home', 'games', 'inventory', 'calendar']);
?>

<!-- Icon Rail -->
<nav class="icon-rail" id="iconRail">
    <div class="rail-top">
        <button class="rail-btn sidebar-toggle-btn" id="sidebarToggleBtn" title="<?= __('nav.toggle_sidebar') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <button class="rail-btn <?= $navSection === 'home' ? 'active' : '' ?>" data-section="home" data-href="<?= url('/') ?>" title="<?= __('nav.dashboard') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </button>

        <button class="rail-btn <?= $navSection === 'games' ? 'active' : '' ?>" data-section="games" title="<?= __('nav.games') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
        </button>

        <button class="rail-btn <?= $navSection === 'inventory' ? 'active' : '' ?>" data-section="inventory" title="<?= __('nav.inventory') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><path d="M3.3 7l8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
        </button>

        <button class="rail-btn <?= $navSection === 'calendar' ? 'active' : '' ?>" data-section="calendar" data-href="<?= url('/calendar') ?>" title="<?= __('nav.calendar') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </button>

    </div>

    <div class="rail-bottom">
        <button class="rail-btn rail-btn-create" id="quickCreateBtn" title="<?= __('action.create') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>

        <a href="<?= url('/settings') ?>" class="rail-btn <?= $navSection === 'settings' ? 'active' : '' ?>" title="<?= __('nav.settings') ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09c-.658.003-1.25.396-1.51 1z"></path></svg>
        </a>
    </div>
</nav>

<!-- Quick Create Popup -->
<div class="quick-create-popup" id="quickCreatePopup">
    <div class="quick-create-header"><?= __('action.create') ?></div>
    <a href="<?= url('/games/create') ?>" class="quick-create-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
        <?= __('game.add_new') ?>
    </a>
    <a href="<?= url('/materials/create') ?>" class="quick-create-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
        <?= __('material.add_new') ?>
    </a>
    <a href="<?= url('/boxes/create') ?>" class="quick-create-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><path d="M3.3 7l8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
        <?= __('box.add_new') ?>
    </a>
    <a href="<?= url('/groups/create') ?>" class="quick-create-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
        <?= __('group.add_new') ?>
    </a>
    <a href="<?= url('/calendar?create=1') ?>" class="quick-create-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
        <?= __('calendar.add_event') ?>
    </a>
</div>

<!-- Context Sidebar -->
<aside class="context-sidebar <?= $hasContextSidebar ? 'open' : '' ?>" id="contextSidebar" data-active="<?= $navSection ?>">
    <div class="ctx-home-link">
        <a href="<?= url('/') ?>" class="ctx-link <?= $navSection === 'home' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <?= __('nav.dashboard') ?>
        </a>
    </div>

    <div class="ctx-section <?= $navSection === 'games' ? 'visible' : '' ?>" data-for="games">
        <div class="ctx-header"><?= __('nav.games') ?></div>
        <nav class="ctx-nav">
            <a href="<?= url('/games') ?>" class="ctx-link <?= isActiveNav('/games') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <?= __('nav.games') ?>
            </a>
            <a href="<?= url('/categories') ?>" class="ctx-link <?= isActiveNav('/categories') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                <?= __('nav.categories') ?>
            </a>
            <a href="<?= url('/tags') ?>" class="ctx-link <?= isActiveNav('/tags') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                <?= __('nav.tags') ?>
            </a>
            <a href="<?= url('/groups') ?>" class="ctx-link <?= isActiveNav('/groups') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                <?= __('nav.groups') ?>
            </a>
        </nav>
    </div>

    <div class="ctx-section <?= $navSection === 'inventory' ? 'visible' : '' ?>" data-for="inventory">
        <div class="ctx-header"><?= __('nav.inventory') ?></div>
        <nav class="ctx-nav">
            <a href="<?= url('/materials') ?>" class="ctx-link <?= isActiveNav('/materials') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <?= __('nav.materials') ?>
            </a>
            <a href="<?= url('/boxes') ?>" class="ctx-link <?= isActiveNav('/boxes') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><path d="M3.3 7l8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                <?= __('nav.boxes') ?>
            </a>
            <a href="<?= url('/locations') ?>" class="ctx-link <?= isActiveNav('/locations') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <?= __('nav.locations') ?>
            </a>
        </nav>
    </div>

    <div class="ctx-section <?= $navSection === 'calendar' ? 'visible' : '' ?>" data-for="calendar">
        <div class="ctx-header"><?= __('nav.calendar') ?></div>
        <nav class="ctx-nav">
            <a href="<?= url('/calendar') ?>" class="ctx-link active">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <?= __('nav.calendar') ?>
            </a>
        </nav>
    </div>

</aside>

<!-- Mobile sidebar overlay backdrop -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
