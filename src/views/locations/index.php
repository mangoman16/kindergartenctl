<div class="page-header">
    <h1 class="page-title"><?= __('location.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/locations/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('location.add_new') ?>
        </a>
    </div>
</div>

<!-- Sorting -->
<div class="card mb-6">
    <div class="card-body" style="padding: var(--spacing-3) var(--spacing-5);">
        <div class="flex items-center gap-4">
            <span class="text-sm text-muted">Sortieren nach:</span>
            <?php
            $sortOptions = [
                'name' => 'Name',
                'created_at' => 'Datum',
            ];
            foreach ($sortOptions as $key => $label):
                $isActive = $currentSort === $key;
                $newDir = ($isActive && $currentDir === 'ASC') ? 'DESC' : 'ASC';
            ?>
            <a href="<?= url('/locations', ['sort' => $key, 'dir' => $newDir]) ?>"
               class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-secondary' ?>">
                <?= e($label) ?>
                <?php if ($isActive): ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?php if ($currentDir === 'ASC'): ?>
                            <polyline points="18 15 12 9 6 15"></polyline>
                        <?php else: ?>
                            <polyline points="6 9 12 15 18 9"></polyline>
                        <?php endif; ?>
                    </svg>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (empty($locations)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <h3 class="empty-state-title"><?= __('location.empty') ?></h3>
            <p class="empty-state-text"><?= __('location.empty_text') ?></p>
            <a href="<?= url('/locations/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('location.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Locations Grid -->
<div class="grid grid-cols-4">
    <?php foreach ($locations as $location): ?>
    <a href="<?= url('/locations/' . $location['id']) ?>" class="item-card">
        <div class="item-card-image">
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
        </div>
        <div class="item-card-content">
            <h3 class="item-card-title"><?= e($location['name']) ?></h3>
            <div class="item-card-meta">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                <span><?= $location['box_count'] ?> <?= pluralize((int)$location['box_count'], 'Box', 'Boxen') ?></span>
            </div>
            <?php if ($location['description']): ?>
                <div class="item-card-meta mt-2">
                    <span><?= e(truncate($location['description'], 50)) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
