<div class="page-header">
    <h1 class="page-title"><?= __('dashboard.title') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/games/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('game.add_new') ?>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-primary-bg); color: var(--color-primary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['games'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_games') ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-success-bg); color: var(--color-success);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['materials'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_materials') ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <path d="M3.3 7l8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['boxes'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_boxes') ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-danger-bg); color: var(--color-danger);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['favorites'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.favorites') ?></div>
    </div>
</div>

<div class="grid grid-cols-2">
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= __('dashboard.quick_actions') ?></h3>
        </div>
        <div class="card-body">
            <div class="flex gap-4" style="flex-wrap: wrap;">
                <a href="<?= url('/games/create') ?>" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?= __('game.add_new') ?>
                </a>
                <a href="<?= url('/materials/create') ?>" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?= __('material.add_new') ?>
                </a>
                <a href="<?= url('/boxes/create') ?>" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?= __('box.add_new') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Random Game Picker -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= __('dashboard.random_game') ?></h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">Finden Sie ein zufälliges Spiel basierend auf Ihren Kriterien.</p>
            <a href="<?= url('/games') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                </svg>
                <?= __('dashboard.pick_random') ?>
            </a>
        </div>
    </div>
</div>

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
            <h3 class="empty-state-title">Noch keine Spiele vorhanden</h3>
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
<?php endif; ?>
