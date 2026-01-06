<?php require_once SRC_PATH . '/services/ChangelogService.php'; ?>

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
    <a href="<?= url('/games') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-primary-bg); color: var(--color-primary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['games'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_games') ?></div>
    </a>

    <a href="<?= url('/materials') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-success-bg); color: var(--color-success);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['materials'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_materials') ?></div>
    </a>

    <a href="<?= url('/boxes') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <path d="M3.3 7l8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['boxes'] ?></div>
        <div class="stat-card-label"><?= __('dashboard.total_boxes') ?></div>
    </a>

    <a href="<?= url('/groups') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-info-bg); color: var(--color-info);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['groups'] ?></div>
        <div class="stat-card-label"><?= __('nav.groups') ?></div>
    </a>

    <a href="<?= url('/tags') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: #fae8ff; color: #a855f7;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['tags'] ?></div>
        <div class="stat-card-label"><?= __('nav.tags') ?></div>
    </a>

    <a href="<?= url('/calendar') ?>" class="stat-card">
        <div class="stat-card-icon" style="background: var(--color-danger-bg); color: var(--color-danger);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="stat-card-value"><?= $stats['events_this_week'] ?></div>
        <div class="stat-card-label">Termine diese Woche</div>
    </a>
</div>

<div class="grid grid-cols-2 gap-4 mt-6">
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= __('dashboard.quick_actions') ?></h3>
        </div>
        <div class="card-body">
            <div class="flex gap-3" style="flex-wrap: wrap;">
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
                <a href="<?= url('/groups/create') ?>" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?= __('group.add_new') ?>
                </a>
                <a href="<?= url('/search') ?>" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <?= __('search.title') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title"><?= __('calendar.upcoming') ?></h3>
            <a href="<?= url('/calendar') ?>" class="text-sm text-primary">Zum Kalender →</a>
        </div>
        <?php if (empty($upcomingEvents)): ?>
            <div class="card-body">
                <p class="text-muted">Keine kommenden Termine.</p>
            </div>
        <?php else: ?>
            <div class="card-body p-0">
                <ul class="simple-list">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <li>
                            <div class="flex items-center gap-3">
                                <div class="event-date-badge">
                                    <span class="event-day"><?= date('d', strtotime($event['start_date'])) ?></span>
                                    <span class="event-month"><?= formatDate($event['start_date'], 'M') ?></span>
                                </div>
                                <div>
                                    <div class="font-medium"><?= e($event['title']) ?></div>
                                    <?php if ($event['game_name']): ?>
                                        <div class="text-sm text-muted"><?= e($event['game_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mt-4">
    <!-- Recent Games -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title"><?= __('dashboard.recent_games') ?></h3>
            <a href="<?= url('/games') ?>" class="text-sm text-primary">Alle anzeigen →</a>
        </div>
        <?php if (empty($recentGames)): ?>
            <div class="card-body">
                <p class="text-muted">Noch keine Spiele hinzugefügt.</p>
            </div>
        <?php else: ?>
            <div class="card-body p-0">
                <ul class="simple-list">
                    <?php foreach ($recentGames as $game): ?>
                        <li>
                            <a href="<?= url('/games/' . $game['id']) ?>" class="flex items-center gap-3">
                                <?php if ($game['image_path']): ?>
                                    <img src="<?= upload($game['image_path']) ?>" alt=""
                                         style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polygon points="10 8 16 12 10 16 10 8"></polygon>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-medium"><?= e($game['name']) ?></div>
                                    <div class="text-sm text-muted"><?= e($game['box_name'] ?? 'Keine Box') ?></div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Changes -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title"><?= __('dashboard.recent_changes') ?></h3>
            <a href="<?= url('/changelog') ?>" class="text-sm text-primary">Alle anzeigen →</a>
        </div>
        <?php if (empty($recentChanges)): ?>
            <div class="card-body">
                <p class="text-muted">Noch keine Änderungen.</p>
            </div>
        <?php else: ?>
            <div class="card-body p-0">
                <ul class="simple-list">
                    <?php foreach ($recentChanges as $change): ?>
                        <li>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="badge badge-sm <?= $change['action'] === 'create' ? 'badge-success' : ($change['action'] === 'delete' ? 'badge-danger' : 'badge-info') ?>">
                                        <?= ChangelogService::getActionLabel($change['action']) ?>
                                    </span>
                                    <span class="ml-2"><?= e($change['entity_name']) ?></span>
                                    <span class="text-muted text-sm ml-1">(<?= ChangelogService::getEntityTypeLabel($change['entity_type']) ?>)</span>
                                </div>
                                <span class="text-muted text-sm"><?= formatDate($change['created_at'], 'd.m. H:i') ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Random Game Picker & Favorites Row -->
<div class="grid grid-cols-2 gap-4 mt-4">
    <!-- Random Game Picker -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= __('dashboard.random_game') ?></h3>
        </div>
        <div class="card-body">
            <div class="random-picker-filters mb-4">
                <div class="flex gap-3" style="flex-wrap: wrap;">
                    <select id="random-category" class="form-select form-select-sm" style="min-width: 150px;">
                        <option value="">Alle Altersgruppen</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="random-tag" class="form-select form-select-sm" style="min-width: 150px;">
                        <option value="">Alle Themen</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>"><?= e($tag['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="button" id="random-game-btn" class="btn btn-primary w-full">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                <?= __('dashboard.pick_random') ?>
            </button>
            <div id="random-game-result" class="mt-4" style="display: none;">
                <div class="random-game-card">
                    <a id="random-game-link" href="#">
                        <div class="flex gap-4 items-center">
                            <img id="random-game-image" src="" alt="" style="width: 80px; height: 80px; border-radius: var(--radius-md); object-fit: cover;">
                            <div>
                                <h4 id="random-game-name" class="font-medium text-lg"></h4>
                                <p id="random-game-box" class="text-sm text-muted"></p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <p id="random-game-empty" class="text-muted mt-4" style="display: none;">
                Kein passendes Spiel gefunden.
            </p>
        </div>
    </div>

    <!-- Favorites Section -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="card-title"><?= __('dashboard.favorites') ?> (<?= $stats['favorites'] ?>)</h3>
            <?php if ($stats['favorites'] > 8): ?>
                <a href="<?= url('/games?favorites=1') ?>" class="text-sm text-primary">Alle anzeigen</a>
            <?php endif; ?>
        </div>
        <?php if (empty($favoriteGames)): ?>
            <div class="card-body">
                <p class="text-muted">Noch keine Favoriten. Markieren Sie Spiele als Favorit, um sie hier zu sehen.</p>
            </div>
        <?php else: ?>
            <div class="card-body p-0">
                <ul class="simple-list">
                    <?php foreach ($favoriteGames as $game): ?>
                        <li>
                            <a href="<?= url('/games/' . $game['id']) ?>" class="flex items-center gap-3">
                                <?php if ($game['image_path']): ?>
                                    <img src="<?= upload($game['image_path']) ?>" alt=""
                                         style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-warning);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-medium"><?= e($game['name']) ?></div>
                                    <div class="text-sm text-muted"><?= e($game['box_name'] ?? 'Keine Box') ?></div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const randomBtn = document.getElementById('random-game-btn');
    const categorySelect = document.getElementById('random-category');
    const tagSelect = document.getElementById('random-tag');
    const resultDiv = document.getElementById('random-game-result');
    const emptyDiv = document.getElementById('random-game-empty');

    if (randomBtn) {
        randomBtn.addEventListener('click', function() {
            const params = new URLSearchParams();
            if (categorySelect.value) params.append('category_id', categorySelect.value);
            if (tagSelect.value) params.append('tag_id', tagSelect.value);

            randomBtn.disabled = true;
            randomBtn.innerHTML = '<span class="spinner-sm"></span> Suche...';

            fetch('<?= url('/api/games/random') ?>?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.game) {
                        document.getElementById('random-game-name').textContent = data.game.name;
                        document.getElementById('random-game-box').textContent = data.game.box_name ? 'Box: ' + data.game.box_name : '';
                        document.getElementById('random-game-link').href = '<?= url('/games/') ?>' + data.game.id;

                        const img = document.getElementById('random-game-image');
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
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.style.display = 'none';
                    emptyDiv.style.display = 'block';
                })
                .finally(() => {
                    randomBtn.disabled = false;
                    randomBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> <?= __('dashboard.pick_random') ?>';
                });
        });
    }
});
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
}
.stat-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.simple-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.simple-list li {
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-gray-100);
}
.simple-list li:last-child {
    border-bottom: none;
}
.simple-list a {
    color: inherit;
    text-decoration: none;
}
.simple-list a:hover {
    color: var(--color-primary);
}
.event-date-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 40px;
    padding: 4px;
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
}
.event-date-badge .event-day {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--color-primary);
}
.event-date-badge .event-month {
    font-size: 0.625rem;
    text-transform: uppercase;
    color: var(--color-gray-500);
}
</style>
