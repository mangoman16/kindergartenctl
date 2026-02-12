<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$helpPage = 'dashboard';
if (strpos($currentPath, '/games') === 0) $helpPage = 'games';
elseif (strpos($currentPath, '/materials') === 0) $helpPage = 'materials';
elseif (strpos($currentPath, '/boxes') === 0) $helpPage = 'boxes';
elseif (strpos($currentPath, '/locations') === 0) $helpPage = 'locations';
elseif (strpos($currentPath, '/categories') === 0) $helpPage = 'categories';
elseif (strpos($currentPath, '/tags') === 0) $helpPage = 'tags';
elseif (strpos($currentPath, '/groups') === 0) $helpPage = 'groups';
elseif (strpos($currentPath, '/calendar') === 0) $helpPage = 'calendar';
elseif (strpos($currentPath, '/changelog') === 0) $helpPage = 'changelog';
elseif (strpos($currentPath, '/settings') === 0) $helpPage = 'settings';
elseif (strpos($currentPath, '/user/settings') === 0) $helpPage = 'user_settings';
elseif (strpos($currentPath, '/search') === 0) $helpPage = 'search';
?>

<aside class="help-panel" id="helpPanel" data-current-page="<?= $helpPage ?>">
    <div class="help-panel-header">
        <h2 class="help-panel-title"><?= __('help.title') ?></h2>
        <button class="help-panel-close" id="helpPanelClose" aria-label="Close">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <nav class="help-toc">
        <a href="#help-dashboard" class="help-toc-item <?= $helpPage === 'dashboard' ? 'active' : '' ?>" data-page="dashboard"><?= __('nav.dashboard') ?></a>
        <a href="#help-games" class="help-toc-item <?= $helpPage === 'games' ? 'active' : '' ?>" data-page="games"><?= __('nav.games') ?></a>
        <a href="#help-materials" class="help-toc-item <?= $helpPage === 'materials' ? 'active' : '' ?>" data-page="materials"><?= __('nav.materials') ?></a>
        <a href="#help-boxes" class="help-toc-item <?= $helpPage === 'boxes' ? 'active' : '' ?>" data-page="boxes"><?= __('nav.boxes') ?></a>
        <a href="#help-locations" class="help-toc-item <?= $helpPage === 'locations' ? 'active' : '' ?>" data-page="locations"><?= __('nav.locations') ?></a>
        <a href="#help-categories" class="help-toc-item <?= $helpPage === 'categories' ? 'active' : '' ?>" data-page="categories"><?= __('nav.categories') ?></a>
        <a href="#help-tags" class="help-toc-item <?= $helpPage === 'tags' ? 'active' : '' ?>" data-page="tags"><?= __('nav.tags') ?></a>
        <a href="#help-groups" class="help-toc-item <?= $helpPage === 'groups' ? 'active' : '' ?>" data-page="groups"><?= __('nav.groups') ?></a>
        <a href="#help-calendar" class="help-toc-item <?= $helpPage === 'calendar' ? 'active' : '' ?>" data-page="calendar"><?= __('nav.calendar') ?></a>
        <a href="#help-changelog" class="help-toc-item <?= $helpPage === 'changelog' ? 'active' : '' ?>" data-page="changelog"><?= __('nav.changelog') ?></a>
        <a href="#help-settings" class="help-toc-item <?= $helpPage === 'settings' ? 'active' : '' ?>" data-page="settings"><?= __('nav.settings') ?></a>
    </nav>

    <div class="help-content">
        <section id="help-dashboard" class="help-section" data-page="dashboard">
            <h3><?= __('nav.dashboard') ?></h3>
            <p><?= __('help.guide_dashboard') ?></p>
        </section>

        <section id="help-games" class="help-section" data-page="games">
            <h3><?= __('nav.games') ?></h3>
            <p><?= __('help.guide_games') ?></p>
        </section>

        <section id="help-materials" class="help-section" data-page="materials">
            <h3><?= __('nav.materials') ?></h3>
            <p><?= __('help.guide_materials') ?></p>
        </section>

        <section id="help-boxes" class="help-section" data-page="boxes">
            <h3><?= __('nav.boxes') ?></h3>
            <p><?= __('help.guide_boxes') ?></p>
        </section>

        <section id="help-locations" class="help-section" data-page="locations">
            <h3><?= __('nav.locations') ?></h3>
            <p><?= __('help.guide_locations') ?></p>
        </section>

        <section id="help-categories" class="help-section" data-page="categories">
            <h3><?= __('nav.categories') ?></h3>
            <p><?= __('help.guide_categories') ?></p>
        </section>

        <section id="help-tags" class="help-section" data-page="tags">
            <h3><?= __('nav.tags') ?></h3>
            <p><?= __('help.guide_tags') ?></p>
        </section>

        <section id="help-groups" class="help-section" data-page="groups">
            <h3><?= __('nav.groups') ?></h3>
            <p><?= __('help.guide_groups') ?></p>
        </section>

        <section id="help-calendar" class="help-section" data-page="calendar">
            <h3><?= __('nav.calendar') ?></h3>
            <p><?= __('help.guide_calendar') ?></p>
        </section>

        <section id="help-changelog" class="help-section" data-page="changelog">
            <h3><?= __('nav.changelog') ?></h3>
            <p><?= __('help.guide_changelog') ?></p>
        </section>

        <section id="help-settings" class="help-section" data-page="settings">
            <h3><?= __('nav.settings') ?></h3>
            <p><?= __('help.guide_settings') ?></p>
        </section>
    </div>
</aside>
