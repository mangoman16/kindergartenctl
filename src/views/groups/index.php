<div class="category-help">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
    <span><?= __('help.category_groups') ?></span>
</div>

<div class="page-header">
    <h1 class="page-title"><?= __('group.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/groups/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('group.add_new') ?>
        </a>
    </div>
</div>

<?php if (empty($groups)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <h3 class="empty-state-title">Noch keine Gruppen vorhanden</h3>
            <p class="empty-state-text">Erstellen Sie Gruppen, um Spiele und Materialien für bestimmte Aktivitäten zusammenzufassen.</p>
            <a href="<?= url('/groups/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('group.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Groups Grid -->
<div class="grid grid-cols-3 gap-4">
    <?php foreach ($groups as $group): ?>
    <div class="card">
        <a href="<?= url('/groups/' . $group['id']) ?>" class="group-card-image">
            <?php if ($group['image_path']): ?>
                <img src="<?= upload($group['image_path']) ?>" alt="<?= e($group['name']) ?>">
            <?php else: ?>
                <div class="group-card-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
            <?php endif; ?>
        </a>
        <div class="card-body">
            <h3 class="group-card-title">
                <a href="<?= url('/groups/' . $group['id']) ?>"><?= e($group['name']) ?></a>
            </h3>

            <?php if ($group['description']): ?>
                <p class="text-muted text-sm mb-3"><?= e(truncate($group['description'], 80)) ?></p>
            <?php endif; ?>

            <div class="flex gap-3">
                <span class="badge" title="Spiele">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10 8 16 12 10 16 10 8"></polygon>
                    </svg>
                    <?= $group['game_count'] ?>
                </span>
                <span class="badge" title="Materialien">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    <?= $group['material_count'] ?>
                </span>
            </div>
        </div>
        <div class="card-footer">
            <div class="flex gap-2">
                <a href="<?= url('/groups/' . $group['id']) ?>" class="btn btn-sm btn-secondary flex-1">
                    <?= __('action.view') ?>
                </a>
                <a href="<?= url('/groups/' . $group['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </a>
                <form action="<?= url('/groups/' . $group['id'] . '/delete') ?>" method="POST"
                      onsubmit="return confirm('<?= __('misc.confirm_delete') ?>')">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style<?= cspNonce() ?>>
.group-card-image {
    display: block;
    aspect-ratio: 16/9;
    overflow: hidden;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    background: var(--color-gray-100);
}
.group-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s;
}
.group-card-image:hover img {
    transform: scale(1.05);
}
.group-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-400);
}
.group-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}
.group-card-title a {
    color: inherit;
    text-decoration: none;
}
.group-card-title a:hover {
    color: var(--color-primary);
}
</style>
