<div class="page-header">
    <h1 class="page-title"><?= e($game['name']) ?></h1>
    <div class="page-actions">
        <a href="<?= url('/games/' . $game['id'] . '/print') ?>" class="btn btn-secondary" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <?= __('action.print') ?>
        </a>
        <a href="<?= url('/games/' . $game['id'] . '/edit') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <?= __('action.edit') ?>
        </a>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <!-- Main Content -->
    <div style="grid-column: span 2;">
        <!-- Game Details Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex gap-6">
                    <!-- Image -->
                    <div style="flex-shrink: 0;">
                        <?php if ($game['image_path']): ?>
                            <img src="<?= upload($game['image_path']) ?>" alt="<?= e($game['name']) ?>"
                                 style="width: 200px; height: 200px; border-radius: var(--radius-lg); object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 200px; height: 200px; background: var(--color-gray-100); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <?php if (!$game['is_active']): ?>
                                <span class="badge badge-danger">Inaktiv</span>
                            <?php endif; ?>
                            <?php if ($game['is_outdoor']): ?>
                                <span class="badge badge-warning">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;">
                                        <circle cx="12" cy="12" r="5"></circle>
                                        <line x1="12" y1="1" x2="12" y2="3"></line>
                                        <line x1="12" y1="21" x2="12" y2="23"></line>
                                    </svg>
                                    Outdoor
                                </span>
                            <?php endif; ?>
                        </div>

                        <dl class="detail-list">
                            <?php if ($game['box_name']): ?>
                                <dt><?= __('nav.boxes') ?></dt>
                                <dd>
                                    <a href="<?= url('/boxes/' . $game['box_id']) ?>"><?= e($game['box_name']) ?></a>
                                    <?php if ($game['box_label']): ?>
                                        <span class="badge ml-1"><?= e($game['box_label']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($game['box_location']): ?>
                                        <span class="text-muted ml-2">(<?= e($game['box_location']) ?>)</span>
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>

                            <?php if ($game['category_name']): ?>
                                <dt><?= __('game.age_group') ?></dt>
                                <dd><?= e($game['category_name']) ?></dd>
                            <?php endif; ?>

                            <dt><?= __('game.players') ?></dt>
                            <dd>
                                <?php if ($game['min_players'] && $game['max_players']): ?>
                                    <?= $game['min_players'] ?> - <?= $game['max_players'] ?> Spieler
                                <?php elseif ($game['min_players']): ?>
                                    ab <?= $game['min_players'] ?> Spieler
                                <?php elseif ($game['max_players']): ?>
                                    bis <?= $game['max_players'] ?> Spieler
                                <?php else: ?>
                                    <span class="text-muted">Nicht angegeben</span>
                                <?php endif; ?>
                            </dd>

                            <dt><?= __('game.duration') ?></dt>
                            <dd>
                                <?php if ($game['duration_minutes']): ?>
                                    <?= $game['duration_minutes'] ?> Minuten
                                <?php else: ?>
                                    <span class="text-muted">Nicht angegeben</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <?php if ($game['description']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('form.description') ?></h2>
            </div>
            <div class="card-body">
                <?= nl2br(e($game['description'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <?php if ($game['instructions']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('game.instructions') ?></h2>
            </div>
            <div class="card-body">
                <?= nl2br(e($game['instructions'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Favorite Toggle -->
        <div class="card mb-4">
            <div class="card-body">
                <button type="button" id="favorite-toggle" class="btn btn-block <?= !empty($game['is_favorite']) ? 'btn-warning' : 'btn-secondary' ?>"
                        data-game-id="<?= $game['id'] ?>" data-is-favorite="<?= !empty($game['is_favorite']) ? '1' : '0' ?>">
                    <svg id="favorite-icon-filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" style="<?= empty($game['is_favorite']) ? 'display:none;' : '' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <svg id="favorite-icon-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="<?= !empty($game['is_favorite']) ? 'display:none;' : '' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span id="favorite-text"><?= !empty($game['is_favorite']) ? 'Favorit entfernen' : 'Als Favorit markieren' ?></span>
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('misc.actions') ?></h2>
            </div>
            <div class="card-body">
                <div class="flex flex-col gap-2">
                    <a href="<?= url('/games/' . $game['id'] . '/edit') ?>" class="btn btn-secondary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <?= __('action.edit') ?>
                    </a>
                    <a href="<?= url('/games/' . $game['id'] . '/print') ?>" class="btn btn-secondary btn-block" target="_blank">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <?= __('action.print') ?>
                    </a>
                    <form action="<?= url('/games/' . $game['id'] . '/duplicate') ?>" method="POST">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-secondary btn-block">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            <?= __('action.duplicate') ?>
                        </button>
                    </form>
                    <form action="<?= url('/games/' . $game['id'] . '/delete') ?>" method="POST"
                          onsubmit="return confirm('<?= __('misc.confirm_delete') ?>')">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-danger btn-block">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            <?= __('action.delete') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tags -->
        <?php if (!empty($game['tags'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('nav.tags') ?></h2>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($game['tags'] as $tag): ?>
                        <a href="<?= url('/games?tag=' . $tag['id']) ?>" class="tag-badge"
                           style="<?= $tag['color'] ? 'background-color: ' . e($tag['color']) . '; color: white;' : '' ?>">
                            <?= e($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Materials -->
        <?php if (!empty($game['materials'])): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __('nav.materials') ?></h2>
            </div>
            <div class="card-body p-0">
                <ul class="material-list">
                    <?php foreach ($game['materials'] as $material): ?>
                        <li>
                            <span class="material-quantity"><?= $material['quantity'] ?>×</span>
                            <a href="<?= url('/materials/' . $material['id']) ?>"><?= e($material['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.tag-badge {
    display: inline-block;
    padding: 4px 12px;
    background: var(--color-gray-100);
    border-radius: 9999px;
    font-size: 0.875rem;
    text-decoration: none;
    color: inherit;
    transition: opacity 0.2s;
}
.tag-badge:hover {
    opacity: 0.8;
}
.material-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.material-list li {
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-gray-100);
}
.material-list li:last-child {
    border-bottom: none;
}
.material-quantity {
    display: inline-block;
    min-width: 30px;
    color: var(--color-gray-500);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('favorite-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const isFavorite = this.dataset.isFavorite === '1';

            toggleBtn.disabled = true;

            const formData = new FormData();
            formData.append('csrf_token', '<?= csrf() ?>');

            fetch('<?= url('/api/games/') ?>' + gameId + '/toggle-favorite', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newFavorite = data.is_favorite;
                    toggleBtn.dataset.isFavorite = newFavorite ? '1' : '0';
                    toggleBtn.className = 'btn btn-block ' + (newFavorite ? 'btn-warning' : 'btn-secondary');
                    document.getElementById('favorite-icon-filled').style.display = newFavorite ? '' : 'none';
                    document.getElementById('favorite-icon-outline').style.display = newFavorite ? 'none' : '';
                    document.getElementById('favorite-text').textContent = newFavorite ? 'Favorit entfernen' : 'Als Favorit markieren';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                toggleBtn.disabled = false;
            });
        });
    }
});
</script>
