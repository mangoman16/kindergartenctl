<div class="page-header">
    <h1 class="page-title"><?= e($location['name']) ?></h1>
    <div class="page-actions">
        <a href="<?= url('/locations/' . $location['id'] . '/edit') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <?= __('action.edit') ?>
        </a>
        <form action="<?= url('/locations/' . $location['id'] . '/delete') ?>" method="POST" style="display: inline;"
              onsubmit="return confirm('<?= __('misc.confirm_delete') ?>')">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <?= __('action.delete') ?>
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-3">
    <!-- Main Info -->
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="card-body">
                <div class="flex gap-6">
                    <div style="width: 100px; flex-shrink: 0;">
                        <div style="width: 100%; aspect-ratio: 1; background: var(--color-gray-100); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1">
                        <?php if ($location['description']): ?>
                        <div class="mb-4">
                            <span class="text-sm text-muted"><?= __('form.description') ?>:</span>
                            <p class="mt-1"><?= nl2br(e($location['description'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="text-sm text-muted mt-4">
                            <?= __('misc.created_at') ?>: <?= formatDateGerman($location['created_at'], 'datetime') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boxes at this Location -->
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title"><?= __('location.boxes_at_location') ?> (<?= count($boxes) ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($boxes)): ?>
                <div class="empty-state" style="padding: var(--spacing-8);">
                    <p class="text-muted"><?= __('location.no_boxes') ?></p>
                </div>
                <?php else: ?>
                <div class="table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= __('form.name') ?></th>
                                <th><?= __('box.number') ?></th>
                                <th><?= __('material.title_plural') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($boxes as $box): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/boxes/' . $box['id']) ?>">
                                        <?= e($box['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($box['number']): ?>
                                        <span class="badge badge-gray">#<?= e($box['number']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $box['material_count'] ?></td>
                                <td class="text-right">
                                    <a href="<?= url('/boxes/' . $box['id']) ?>" class="btn btn-sm btn-outline">
                                        <?= __('action.view') ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= __('location.statistics') ?></h3>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div style="width: 40px; height: 40px; background: var(--color-primary-bg); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold"><?= $location['box_count'] ?></div>
                        <div class="text-sm text-muted"><?= pluralize((int)$location['box_count'], 'Box', 'Boxen') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
