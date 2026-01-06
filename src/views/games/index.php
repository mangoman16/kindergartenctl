<div class="page-header">
    <h1 class="page-title"><?= __('game.title_plural') ?></h1>
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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= url('/games') ?>" method="GET" class="filter-form">
            <div class="grid grid-cols-5 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label"><?= __('form.search') ?></label>
                    <input type="text" name="q" class="form-control" placeholder="<?= __('misc.search_placeholder') ?>"
                           value="<?= e($filters['search'] ?? '') ?>">
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.boxes') ?></label>
                    <select name="box" class="form-control">
                        <option value="">Alle Boxen</option>
                        <?php foreach ($boxes as $box): ?>
                            <option value="<?= $box['id'] ?>" <?= ($filters['box_id'] ?? '') == $box['id'] ? 'selected' : '' ?>>
                                <?= e($box['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.categories') ?></label>
                    <select name="category" class="form-control">
                        <option value="">Alle Altersgruppen</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.tags') ?></label>
                    <select name="tag" class="form-control">
                        <option value="">Alle Themen</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" <?= ($filters['tag_id'] ?? '') == $tag['id'] ? 'selected' : '' ?>>
                                <?= e($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label">&nbsp;</label>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            Filtern
                        </button>
                        <a href="<?= url('/games') ?>" class="btn btn-secondary">Zurücksetzen</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($games)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
            </div>
            <h3 class="empty-state-title">Noch keine Spiele vorhanden</h3>
            <p class="empty-state-text">Erstellen Sie Ihr erstes Spiel, um loszulegen.</p>
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
<!-- Games Grid -->
<div class="text-muted mb-3"><?= count($games) ?> <?= pluralize(count($games), 'Spiel', 'Spiele') ?> gefunden</div>

<div class="grid grid-cols-4 gap-4">
    <?php foreach ($games as $game): ?>
    <div class="card game-card <?= !$game['is_active'] ? 'opacity-60' : '' ?>">
        <a href="<?= url('/games/' . $game['id']) ?>" class="game-card-image">
            <?php if ($game['image_path']): ?>
                <img src="<?= upload($game['image_path']) ?>" alt="<?= e($game['name']) ?>">
            <?php else: ?>
                <div class="game-card-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10 8 16 12 10 16 10 8"></polygon>
                    </svg>
                </div>
            <?php endif; ?>
            <?php if ($game['is_outdoor']): ?>
                <span class="game-card-badge" title="Outdoor-Spiel">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </span>
            <?php endif; ?>
        </a>
        <div class="card-body">
            <h3 class="game-card-title">
                <a href="<?= url('/games/' . $game['id']) ?>"><?= e($game['name']) ?></a>
            </h3>

            <?php if ($game['box_name']): ?>
                <div class="text-sm text-muted mb-2">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    <?= e($game['box_name']) ?>
                    <?php if ($game['box_label']): ?>
                        <span class="badge badge-sm ml-1"><?= e($game['box_label']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($game['category_name']): ?>
                <div class="text-sm text-muted mb-2">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <?= e($game['category_name']) ?>
                </div>
            <?php endif; ?>

            <div class="flex gap-2 mt-2">
                <?php if ($game['min_players'] || $game['max_players']): ?>
                    <span class="badge badge-sm" title="Spieleranzahl">
                        <?php if ($game['min_players'] && $game['max_players']): ?>
                            <?= $game['min_players'] ?>-<?= $game['max_players'] ?>
                        <?php elseif ($game['min_players']): ?>
                            ab <?= $game['min_players'] ?>
                        <?php else: ?>
                            bis <?= $game['max_players'] ?>
                        <?php endif; ?>
                        Spieler
                    </span>
                <?php endif; ?>

                <?php if ($game['duration_minutes']): ?>
                    <span class="badge badge-sm" title="Dauer">
                        <?= $game['duration_minutes'] ?> Min.
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.game-card { position: relative; }
.game-card-image {
    display: block;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    background: var(--color-gray-100);
}
.game-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s;
}
.game-card:hover .game-card-image img {
    transform: scale(1.05);
}
.game-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-400);
}
.game-card-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--color-warning);
    color: white;
    padding: 4px;
    border-radius: var(--radius-md);
}
.game-card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}
.game-card-title a {
    color: inherit;
    text-decoration: none;
}
.game-card-title a:hover {
    color: var(--color-primary);
}
.opacity-60 { opacity: 0.6; }
</style>
