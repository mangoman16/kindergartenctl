<?php require_once SRC_PATH . '/services/ChangelogService.php'; ?>

<?php if ($stats['games'] === 0): ?>
<!-- Empty State -->
<div class="card mt-6">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
            </div>
            <h3 class="empty-state-title"><?= __('dashboard.no_games_yet') ?></h3>
            <p class="empty-state-text">Beginnen Sie damit, Ihr erstes Spiel hinzuzufügen.</p>
            <a href="<?= url('/games/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('game.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title"><?= __('dashboard.title') ?></h1>
</div>

<!-- Stats Row: 4 cards -->
<div class="dash-stats-row">
    <a href="<?= url('/games') ?>" class="dash-stat-card">
        <div class="dash-stat-icon" style="background: var(--color-primary-bg); color: var(--color-primary);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-value"><?= $stats['games'] ?></div>
            <div class="dash-stat-label"><?= __('dashboard.total_games') ?></div>
        </div>
    </a>

    <a href="<?= url('/materials') ?>" class="dash-stat-card">
        <div class="dash-stat-icon" style="background: var(--color-success-bg); color: var(--color-success);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-value"><?= $stats['materials'] ?></div>
            <div class="dash-stat-label"><?= __('dashboard.total_materials') ?></div>
        </div>
    </a>

    <a href="<?= url('/boxes') ?>" class="dash-stat-card">
        <div class="dash-stat-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <path d="M3.3 7l8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-value"><?= $stats['boxes'] ?></div>
            <div class="dash-stat-label"><?= __('dashboard.total_boxes') ?></div>
        </div>
    </a>

    <a href="<?= url('/groups') ?>" class="dash-stat-card">
        <div class="dash-stat-icon" style="background: var(--color-info-bg); color: var(--color-info);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-value"><?= $stats['groups'] ?></div>
            <div class="dash-stat-label"><?= __('nav.groups') ?></div>
        </div>
    </a>
</div>

<!-- Quick Actions -->
<div class="quick-action-grid">
    <a href="<?= url('/games/create') ?>" class="quick-action-card">
        <div class="quick-action-icon" style="background: var(--color-primary-bg); color: var(--color-primary);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
        <span class="quick-action-label"><?= __('game.add_new') ?></span>
    </a>
    <a href="<?= url('/materials/create') ?>" class="quick-action-card">
        <div class="quick-action-icon" style="background: var(--color-success-bg); color: var(--color-success);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
        <span class="quick-action-label"><?= __('material.add_new') ?></span>
    </a>
    <a href="<?= url('/boxes/create') ?>" class="quick-action-card">
        <div class="quick-action-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
        <span class="quick-action-label"><?= __('box.add_new') ?></span>
    </a>
    <a href="<?= url('/groups/create') ?>" class="quick-action-card">
        <div class="quick-action-icon" style="background: var(--color-info-bg); color: var(--color-info);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                <line x1="12" y1="11" x2="12" y2="17"></line>
                <line x1="9" y1="14" x2="15" y2="14"></line>
            </svg>
        </div>
        <span class="quick-action-label"><?= __('group.add_new') ?></span>
    </a>
    <a href="<?= url('/search') ?>" class="quick-action-card">
        <div class="quick-action-icon" style="background: #fae8ff; color: #a855f7;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
        <span class="quick-action-label"><?= __('search.title') ?></span>
    </a>
</div>

<!-- Dashboard Widgets -->
<div class="dash-layout" id="dashLayout">
    <!-- Left Column (collapsible) -->
    <div class="dash-col-left" id="dashColLeft">
        <!-- Mini Calendar -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('nav.calendar') ?></h3>
                <a href="<?= url('/calendar') ?>" class="dash-card-link"><?= __('nav.calendar') ?> &rarr;</a>
            </div>
            <div class="dash-card-body">
                <div id="mini-cal">
                    <div class="mini-cal-nav">
                        <button type="button" id="mini-cal-prev" class="mini-cal-arrow" aria-label="Previous month">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                        <span id="mini-cal-title" class="mini-cal-month-title"></span>
                        <button type="button" id="mini-cal-next" class="mini-cal-arrow" aria-label="Next month">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </div>
                    <div class="mini-cal-weekdays">
                        <span>Mo</span><span>Di</span><span>Mi</span><span>Do</span><span>Fr</span><span>Sa</span><span>So</span>
                    </div>
                    <div id="mini-cal-days" class="mini-cal-days"></div>
                </div>
                <!-- Popover for day events -->
                <div id="mini-cal-popover" class="mini-cal-popover" style="display:none;">
                    <div class="mini-cal-popover-header">
                        <span id="mini-cal-popover-date"></span>
                        <button type="button" id="mini-cal-popover-close" class="mini-cal-popover-close" aria-label="Close">&times;</button>
                    </div>
                    <div id="mini-cal-popover-body" class="mini-cal-popover-body"></div>
                </div>
            </div>
        </div>

        <!-- Recent Changes -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('dashboard.recent_changes') ?></h3>
                <a href="<?= url('/changelog') ?>" class="dash-card-link"><?= __('misc.show_more') ?> &rarr;</a>
            </div>
            <div class="dash-card-body">
                <?php if (empty($recentChanges)): ?>
                    <p class="text-muted"><?= __('dashboard.no_changes_yet') ?></p>
                <?php else: ?>
                    <div class="dash-change-list">
                        <?php foreach (array_slice($recentChanges, 0, 4) as $change): ?>
                            <div class="dash-change-item">
                                <span class="dash-change-badge dash-change-badge--<?= $change['action'] === 'create' ? 'create' : ($change['action'] === 'delete' ? 'delete' : 'update') ?>">
                                    <?= ChangelogService::getActionLabel($change['action']) ?>
                                </span>
                                <div class="dash-change-info">
                                    <span class="dash-change-name"><?= e($change['entity_name']) ?></span>
                                    <span class="dash-change-type"><?= ChangelogService::getEntityTypeLabel($change['entity_type']) ?></span>
                                </div>
                                <span class="dash-change-time"><?= formatDate($change['created_at'], 'd.m. H:i') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Random Game Picker -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('dashboard.random_game') ?></h3>
            </div>
            <div class="dash-card-body">
                <div class="dash-picker-filters">
                    <select id="random-category" class="dash-picker-select">
                        <option value=""><?= __('misc.all') ?> <?= __('nav.categories') ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="random-tag" class="dash-picker-select">
                        <option value=""><?= __('misc.all') ?> <?= __('nav.tags') ?></option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>"><?= e($tag['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="random-game-btn" class="btn btn-primary w-full mt-4">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    <?= __('dashboard.pick_random') ?>
                </button>
                <div id="random-game-result" class="dash-picker-result" style="display: none;">
                    <a id="random-game-link" href="#" class="dash-item">
                        <img id="random-game-image" src="" alt="" class="dash-picker-result-img">
                        <div class="dash-item-info">
                            <div id="random-game-name" class="dash-item-name"></div>
                            <div id="random-game-box" class="dash-item-meta"></div>
                        </div>
                    </a>
                </div>
                <p id="random-game-empty" class="text-muted mt-4" style="display: none;">
                    <?= __('dashboard.no_random_game') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Toggle Button for Left Column -->
    <button type="button" class="dash-col-toggle" id="dashColToggle" title="Seitenleiste ein-/ausblenden">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>

    <!-- Right Column -->
    <div class="dash-col-right">
        <!-- Recently Added -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('dashboard.recent_games') ?></h3>
                <a href="<?= url('/games') ?>" class="dash-card-link"><?= __('misc.show_more') ?> &rarr;</a>
            </div>
            <div class="dash-card-body">
                <?php if (empty($recentGames)): ?>
                    <p class="text-muted"><?= __('dashboard.recently_added') ?></p>
                <?php else: ?>
                    <div class="dash-item-list">
                        <?php foreach (array_slice($recentGames, 0, 4) as $game): ?>
                            <a href="<?= url('/games/' . $game['id']) ?>" class="dash-item">
                                <?php if ($game['image_path']): ?>
                                    <img src="<?= upload($game['image_path']) ?>" alt="" class="dash-item-thumb">
                                <?php else: ?>
                                    <div class="dash-item-thumb dash-item-thumb-placeholder">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polygon points="10 8 16 12 10 16 10 8"></polygon>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="dash-item-info">
                                    <div class="dash-item-name"><?= e($game['name']) ?></div>
                                    <div class="dash-item-meta"><?= e($game['box_name'] ?? __('misc.no_box')) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recently Played -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('dashboard.recently_played') ?></h3>
                <a href="<?= url('/calendar') ?>" class="dash-card-link"><?= __('nav.calendar') ?> &rarr;</a>
            </div>
            <div class="dash-card-body">
                <?php if (empty($recentlyPlayed)): ?>
                    <p class="text-muted"><?= __('dashboard.no_played_yet') ?></p>
                <?php else: ?>
                    <div class="dash-item-list">
                        <?php foreach (array_slice($recentlyPlayed, 0, 4) as $played): ?>
                            <a href="<?= url('/games/' . $played['game_id']) ?>" class="dash-item">
                                <?php if ($played['image_path']): ?>
                                    <img src="<?= upload($played['image_path']) ?>" alt="" class="dash-item-thumb">
                                <?php else: ?>
                                    <div class="dash-item-thumb dash-item-thumb-placeholder" style="color: var(--color-success);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="dash-item-info">
                                    <div class="dash-item-name"><?= e($played['game_name']) ?></div>
                                    <div class="dash-item-meta"><?= e($played['box_name'] ?? __('misc.no_box')) ?></div>
                                </div>
                                <span class="dash-item-date"><?= formatDate($played['start_date'], 'd.m.') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Favorites -->
        <div class="card dash-card dash-card-fixed">
            <div class="dash-card-header">
                <h3 class="dash-card-title"><?= __('dashboard.favorites') ?> (<?= $stats['favorites'] ?>)</h3>
                <?php if ($stats['favorites'] > 4): ?>
                    <a href="<?= url('/games?favorites=1') ?>" class="dash-card-link"><?= __('misc.show_more') ?> &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="dash-card-body">
                <?php if (empty($favoriteGames)): ?>
                    <p class="text-muted"><?= __('dashboard.no_favorites_yet') ?></p>
                <?php else: ?>
                    <div class="dash-item-list">
                        <?php foreach (array_slice($favoriteGames, 0, 4) as $game): ?>
                            <a href="<?= url('/games/' . $game['id']) ?>" class="dash-item">
                                <?php if ($game['image_path']): ?>
                                    <img src="<?= upload($game['image_path']) ?>" alt="" class="dash-item-thumb">
                                <?php else: ?>
                                    <div class="dash-item-thumb dash-item-thumb-placeholder" style="color: var(--color-warning);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="dash-item-info">
                                    <div class="dash-item-name"><?= e($game['name']) ?></div>
                                    <div class="dash-item-meta"><?= e($game['box_name'] ?? __('misc.no_box')) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- ==================== Styles ==================== -->
<style<?= cspNonce() ?>>
/* === Stats Row === */
.dash-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 0.5rem;
}
.dash-stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.dash-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.dash-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-xl);
    flex-shrink: 0;
}
.dash-stat-info {
    display: flex;
    flex-direction: column;
}
.dash-stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    line-height: 1.2;
    color: var(--color-gray-900);
}
.dash-stat-label {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-top: 2px;
}

/* === Quick Actions Grid === */
.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.75rem;
    margin-top: 1.25rem;
}
.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.625rem;
    padding: 1.25rem 0.75rem;
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: var(--color-gray-700);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.quick-action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: var(--color-gray-900);
}
.quick-action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-xl);
}
.quick-action-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    text-align: center;
    line-height: 1.3;
}

/* === Dashboard Layout === */
.dash-layout {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 0;
    margin-top: 1.5rem;
    align-items: start;
}
.dash-col-left {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-right: 0.5rem;
    transition: width 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
    overflow: hidden;
}
.dash-col-left.collapsed {
    width: 0 !important;
    opacity: 0;
    padding: 0;
    pointer-events: none;
}
.dash-layout.left-collapsed {
    grid-template-columns: 0 auto 1fr;
}
.dash-col-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-left: 0.5rem;
}
.dash-col-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 40px;
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    color: var(--color-gray-400);
    cursor: pointer;
    margin-top: 1rem;
    transition: color 0.15s ease, background 0.15s ease;
    flex-shrink: 0;
    align-self: start;
}
.dash-col-toggle:hover {
    color: var(--color-gray-700);
    background: var(--color-gray-50);
}
.dash-col-toggle svg {
    transition: transform 0.3s ease;
}
.dash-layout.left-collapsed .dash-col-toggle svg {
    transform: rotate(180deg);
}

/* === Fixed-height Dashboard Cards === */
.dash-card-fixed {
    height: 340px;
    display: flex;
    flex-direction: column;
}
.dash-card-fixed .dash-card-body {
    flex: 1;
    overflow: hidden;
}

/* === Dashboard Cards === */
.dash-card {
    border: none !important;
}
.dash-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem 0 1.5rem;
}
.dash-card-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-900);
    margin: 0;
}
.dash-card-link {
    font-size: var(--font-size-sm);
    color: var(--color-primary);
    text-decoration: none;
    font-weight: var(--font-weight-medium);
    white-space: nowrap;
}
.dash-card-link:hover {
    opacity: 0.8;
}
.dash-card-body {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
}

/* === Item Lists (no borders) === */
.dash-item-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.dash-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.625rem 0.75rem;
    border-radius: var(--radius-xl);
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
}
.dash-item:hover {
    background: var(--color-gray-50);
}
.dash-item-thumb {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    object-fit: cover;
    flex-shrink: 0;
}
.dash-item-thumb-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-gray-100);
    color: var(--color-gray-400);
}
.dash-item-info {
    flex: 1;
    min-width: 0;
}
.dash-item-name {
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    color: var(--color-gray-900);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-item-meta {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
    margin-top: 2px;
}
.dash-item-date {
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
    flex-shrink: 0;
    font-weight: var(--font-weight-medium);
}

/* === Changes List === */
.dash-change-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.dash-change-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.75rem;
    border-radius: var(--radius-xl);
    transition: background 0.15s ease;
}
.dash-change-item:hover {
    background: var(--color-gray-50);
}
.dash-change-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    border-radius: var(--radius-full);
    flex-shrink: 0;
    letter-spacing: 0.01em;
}
.dash-change-badge--create {
    background: var(--color-success-bg);
    color: var(--color-success-dark);
}
.dash-change-badge--update {
    background: var(--color-info-bg);
    color: var(--color-info-dark);
}
.dash-change-badge--delete {
    background: var(--color-danger-bg);
    color: var(--color-danger-dark);
}
.dash-change-info {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: baseline;
    gap: 0.375rem;
}
.dash-change-name {
    font-weight: var(--font-weight-medium);
    font-size: var(--font-size-sm);
    color: var(--color-gray-800);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-change-type {
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
    flex-shrink: 0;
}
.dash-change-time {
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
    flex-shrink: 0;
}

/* === Mini Calendar === */
#mini-cal {
    position: relative;
}
.mini-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.mini-cal-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: var(--radius-lg);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--color-gray-500);
    transition: background 0.15s ease, color 0.15s ease;
}
.mini-cal-arrow:hover {
    background: var(--color-gray-100);
    color: var(--color-gray-800);
}
.mini-cal-month-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-900);
}
.mini-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    margin-bottom: 0.375rem;
}
.mini-cal-weekdays span {
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-400);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0;
}
.mini-cal-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}
.mini-cal-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0;
    min-height: 38px;
    border-radius: var(--radius-lg);
    cursor: default;
    position: relative;
    font-size: var(--font-size-sm);
    color: var(--color-gray-700);
    transition: background 0.15s ease;
}
.mini-cal-day.empty {
    cursor: default;
}
.mini-cal-day:not(.empty):hover {
    background: var(--color-gray-50);
    cursor: pointer;
}
.mini-cal-day.today .mini-cal-day-num {
    background: var(--color-primary);
    color: var(--color-white);
    border-radius: var(--radius-full);
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
}
.mini-cal-day.other-month {
    color: var(--color-gray-300);
}
.mini-cal-day-num {
    font-size: var(--font-size-sm);
    line-height: 1;
}
.mini-cal-day-dot {
    width: 5px;
    height: 5px;
    border-radius: var(--radius-full);
    background: var(--color-primary);
    margin-top: 3px;
}

/* Calendar Popover */
.mini-cal-popover {
    position: absolute;
    z-index: 50;
    background: var(--color-white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 0;
    min-width: 220px;
    max-width: 280px;
    overflow: hidden;
}
.mini-cal-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: var(--color-gray-50);
}
.mini-cal-popover-header span {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-800);
}
.mini-cal-popover-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--color-gray-400);
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.mini-cal-popover-close:hover {
    color: var(--color-gray-700);
}
.mini-cal-popover-body {
    padding: 0.5rem 0;
}
.mini-cal-popover-event {
    display: block;
    padding: 0.5rem 1rem;
    text-decoration: none;
    color: var(--color-gray-800);
    font-size: var(--font-size-sm);
    transition: background 0.15s ease;
}
.mini-cal-popover-event:hover {
    background: var(--color-gray-50);
}
.mini-cal-popover-event-title {
    font-weight: var(--font-weight-medium);
}
.mini-cal-popover-event-game {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
    margin-top: 1px;
}
.mini-cal-popover-empty {
    padding: 0.75rem 1rem;
    font-size: var(--font-size-sm);
    color: var(--color-gray-400);
}

/* === Random Picker === */
.dash-picker-filters {
    display: flex;
    gap: 0.75rem;
}
.dash-picker-select {
    flex: 1;
    padding: 0.5rem 0.75rem;
    font-size: var(--font-size-sm);
    color: var(--color-gray-700);
    background: var(--color-gray-50);
    border: none;
    border-radius: var(--radius-lg);
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%239CA3AF' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2rem;
    transition: background-color 0.15s ease;
}
.dash-picker-select:hover {
    background: var(--color-gray-100);
}
.dash-picker-select:focus {
    box-shadow: 0 0 0 2px var(--color-primary-bg);
}
.dash-picker-result {
    margin-top: 1rem;
}
.dash-picker-result .dash-item {
    background: var(--color-gray-50);
    border-radius: var(--radius-xl);
    padding: 0.75rem;
}
.dash-picker-result .dash-item:hover {
    background: var(--color-gray-100);
}
.dash-picker-result-img {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-lg);
    object-fit: cover;
    flex-shrink: 0;
}

/* === Responsive === */
@media (max-width: 1200px) {
    .dash-stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .quick-action-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .dash-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .dash-col-left {
        padding-right: 0;
    }
    .dash-col-right {
        padding-left: 0;
    }
    .dash-col-toggle {
        display: none;
    }
    .dash-card-fixed {
        height: auto;
        min-height: 280px;
    }
}
@media (max-width: 768px) {
    .dash-stats-row {
        grid-template-columns: 1fr;
    }
    .quick-action-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .dash-picker-filters {
        flex-direction: column;
    }
}
</style>

<!-- ==================== Scripts ==================== -->
<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {

    // ============================
    // Mini Calendar Widget
    // ============================
    (function() {
        var calEl = document.getElementById('mini-cal');
        if (!calEl) return;

        var titleEl = document.getElementById('mini-cal-title');
        var daysEl = document.getElementById('mini-cal-days');
        var prevBtn = document.getElementById('mini-cal-prev');
        var nextBtn = document.getElementById('mini-cal-next');
        var popover = document.getElementById('mini-cal-popover');
        var popoverDate = document.getElementById('mini-cal-popover-date');
        var popoverBody = document.getElementById('mini-cal-popover-body');
        var popoverClose = document.getElementById('mini-cal-popover-close');

        var now = new Date();
        var currentYear = now.getFullYear();
        var currentMonth = now.getMonth();
        var viewYear = currentYear;
        var viewMonth = currentMonth;

        // Seed events from PHP
        var eventsCache = {};
        <?php
        $jsEvents = [];
        foreach ($upcomingEvents as $ev) {
            $dateKey = date('Y-m-d', strtotime($ev['start_date']));
            $jsEvents[] = [
                'date' => $dateKey,
                'title' => $ev['title'],
                'game_name' => $ev['game_name'] ?? '',
            ];
        }
        ?>
        var seedEvents = <?= json_encode($jsEvents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        seedEvents.forEach(function(ev) {
            if (!eventsCache[ev.date]) eventsCache[ev.date] = [];
            eventsCache[ev.date].push(ev);
        });

        var monthNames = [
            'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
            'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'
        ];

        function pad(n) { return n < 10 ? '0' + n : '' + n; }

        function dateKey(y, m, d) {
            return y + '-' + pad(m + 1) + '-' + pad(d);
        }

        function fetchEvents(y, m) {
            var firstDay = new Date(y, m, 1);
            var lastDay = new Date(y, m + 1, 0);
            var startStr = y + '-' + pad(m + 1) + '-01';
            var endStr = y + '-' + pad(m + 1) + '-' + pad(lastDay.getDate());

            fetch('<?= url('/api/calendar/events') ?>?start=' + startStr + '&end=' + endStr)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data && Array.isArray(data)) {
                        data.forEach(function(ev) {
                            var dk = ev.start_date ? ev.start_date.substring(0, 10) : null;
                            if (dk) {
                                if (!eventsCache[dk]) eventsCache[dk] = [];
                                // Avoid duplicates
                                var exists = eventsCache[dk].some(function(e) {
                                    return e.title === ev.title && e.date === dk;
                                });
                                if (!exists) {
                                    eventsCache[dk].push({
                                        date: dk,
                                        title: ev.title || '',
                                        game_name: ev.game_name || ''
                                    });
                                }
                            }
                        });
                        renderDays();
                    }
                })
                .catch(function() {});
        }

        function renderDays() {
            daysEl.innerHTML = '';
            titleEl.textContent = monthNames[viewMonth] + ' ' + viewYear;

            var firstOfMonth = new Date(viewYear, viewMonth, 1);
            var dayOfWeek = firstOfMonth.getDay(); // 0=Sun
            // Convert to Monday-start: 0=Mon ... 6=Sun
            var startOffset = (dayOfWeek === 0) ? 6 : dayOfWeek - 1;

            var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            var todayStr = dateKey(currentYear, currentMonth, now.getDate());

            // Empty cells for start offset
            for (var i = 0; i < startOffset; i++) {
                var emptyCell = document.createElement('div');
                emptyCell.className = 'mini-cal-day empty';
                daysEl.appendChild(emptyCell);
            }

            for (var d = 1; d <= daysInMonth; d++) {
                var cell = document.createElement('div');
                cell.className = 'mini-cal-day';
                var dk = dateKey(viewYear, viewMonth, d);

                if (dk === todayStr) {
                    cell.classList.add('today');
                }

                var numEl = document.createElement('span');
                numEl.className = 'mini-cal-day-num';
                numEl.textContent = d;
                cell.appendChild(numEl);

                if (eventsCache[dk] && eventsCache[dk].length > 0) {
                    var dot = document.createElement('span');
                    dot.className = 'mini-cal-day-dot';
                    cell.appendChild(dot);
                }

                cell.dataset.date = dk;
                cell.dataset.day = d;
                cell.addEventListener('click', onDayClick);
                daysEl.appendChild(cell);
            }
        }

        function onDayClick(e) {
            var cell = e.currentTarget;
            var dk = cell.dataset.date;
            var dayNum = cell.dataset.day;

            // Format popover date header
            var parts = dk.split('-');
            popoverDate.textContent = parseInt(parts[2], 10) + '. ' + monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];

            popoverBody.innerHTML = '';
            var events = eventsCache[dk];
            if (events && events.length > 0) {
                events.forEach(function(ev) {
                    var a = document.createElement('a');
                    a.className = 'mini-cal-popover-event';
                    a.href = '<?= url('/calendar') ?>';
                    var titleDiv = document.createElement('div');
                    titleDiv.className = 'mini-cal-popover-event-title';
                    titleDiv.textContent = ev.title;
                    a.appendChild(titleDiv);
                    if (ev.game_name) {
                        var gameDiv = document.createElement('div');
                        gameDiv.className = 'mini-cal-popover-event-game';
                        gameDiv.textContent = ev.game_name;
                        a.appendChild(gameDiv);
                    }
                    popoverBody.appendChild(a);
                });
            } else {
                var empty = document.createElement('div');
                empty.className = 'mini-cal-popover-empty';
                empty.textContent = '<?= __('calendar.no_events') ?>';
                popoverBody.appendChild(empty);
            }

            // Position popover near clicked cell
            var calRect = calEl.getBoundingClientRect();
            var cellRect = cell.getBoundingClientRect();
            var left = cellRect.left - calRect.left + cellRect.width / 2;
            var top = cellRect.bottom - calRect.top + 4;

            // Clamp so popover stays inside calendar card
            popover.style.display = 'block';
            var popWidth = popover.offsetWidth;
            if (left + popWidth / 2 > calEl.offsetWidth) {
                left = calEl.offsetWidth - popWidth - 8;
            } else {
                left = left - popWidth / 2;
            }
            if (left < 0) left = 0;

            popover.style.left = left + 'px';
            popover.style.top = top + 'px';
        }

        popoverClose.addEventListener('click', function() {
            popover.style.display = 'none';
        });

        // Close popover on outside click
        document.addEventListener('click', function(e) {
            if (!calEl.contains(e.target)) {
                popover.style.display = 'none';
            }
        });

        prevBtn.addEventListener('click', function() {
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            popover.style.display = 'none';
            fetchEvents(viewYear, viewMonth);
            renderDays();
        });

        nextBtn.addEventListener('click', function() {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            popover.style.display = 'none';
            fetchEvents(viewYear, viewMonth);
            renderDays();
        });

        // Initial render + fetch current month events
        renderDays();
        fetchEvents(viewYear, viewMonth);
    })();

    // ============================
    // Random Game Picker
    // ============================
    (function() {
        var randomBtn = document.getElementById('random-game-btn');
        var categorySelect = document.getElementById('random-category');
        var tagSelect = document.getElementById('random-tag');
        var resultDiv = document.getElementById('random-game-result');
        var emptyDiv = document.getElementById('random-game-empty');

        if (!randomBtn) return;

        randomBtn.addEventListener('click', function() {
            var params = new URLSearchParams();
            if (categorySelect && categorySelect.value) params.append('category_id', categorySelect.value);
            if (tagSelect && tagSelect.value) params.append('tag_id', tagSelect.value);

            randomBtn.disabled = true;
            randomBtn.innerHTML = '<span class="spinner-sm"></span> Suche...';

            fetch('<?= url('/api/games/random') ?>?' + params.toString())
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.game) {
                        document.getElementById('random-game-name').textContent = data.game.name;
                        document.getElementById('random-game-box').textContent = data.game.box_name ? 'Box: ' + data.game.box_name : '';
                        document.getElementById('random-game-link').href = '<?= url('/games/') ?>' + data.game.id;

                        var img = document.getElementById('random-game-image');
                        if (data.game.image_path) {
                            img.src = data.game.image_path;
                            img.style.display = 'block';
                        } else {
                            img.style.display = 'none';
                        }

                        resultDiv.style.display = 'block';
                        emptyDiv.style.display = 'none';
                    } else {
                        resultDiv.style.display = 'none';
                        emptyDiv.style.display = 'block';
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    resultDiv.style.display = 'none';
                    emptyDiv.style.display = 'block';
                })
                .finally(function() {
                    randomBtn.disabled = false;
                    randomBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> <?= __('dashboard.pick_random') ?>';
                });
        });
    })();

    // ============================
    // Dashboard Left Column Toggle
    // ============================
    (function() {
        var toggleBtn = document.getElementById('dashColToggle');
        var layout = document.getElementById('dashLayout');
        var colLeft = document.getElementById('dashColLeft');
        if (!toggleBtn || !layout || !colLeft) return;

        // Restore state from localStorage
        var collapsed = localStorage.getItem('dashLeftCollapsed') === '1';
        if (collapsed) {
            layout.classList.add('left-collapsed');
            colLeft.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', function() {
            var isCollapsed = layout.classList.toggle('left-collapsed');
            colLeft.classList.toggle('collapsed');
            localStorage.setItem('dashLeftCollapsed', isCollapsed ? '1' : '0');
        });
    })();

});
</script>
