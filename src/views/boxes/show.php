<div class="page-header">
    <h1 class="page-title"><?= e($box['name']) ?></h1>
    <div class="page-actions">
        <a href="<?= url('/boxes/' . $box['id'] . '/print') ?>" class="btn btn-secondary" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <?= __('action.print') ?>
        </a>
        <a href="<?= url('/boxes/' . $box['id'] . '/edit') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <?= __('action.edit') ?>
        </a>
        <form action="<?= url('/boxes/' . $box['id'] . '/delete') ?>" method="POST" style="display: inline;"
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
                <!-- Box Image and Info -->
                <div class="flex gap-6">
                    <div style="width: 200px; flex-shrink: 0;">
                        <?php if ($box['image_path']): ?>
                            <img src="<?= upload($box['image_path']) ?>" alt="<?= e($box['name']) ?>"
                                 style="width: 100%; border-radius: var(--radius-lg);">
                        <?php else: ?>
                            <div style="width: 100%; aspect-ratio: 1; background: var(--color-gray-100); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <path d="M3.3 7l8.7 5 8.7-5"></path>
                                    <path d="M12 22V12"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1">
                        <?php if ($box['number']): ?>
                        <div class="mb-4">
                            <span class="text-sm text-muted"><?= __('box.number') ?>:</span>
                            <span class="badge badge-primary ml-2">#<?= e($box['number']) ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($box['location_name'])): ?>
                        <div class="mb-4">
                            <span class="text-sm text-muted"><?= __('box.location') ?>:</span>
                            <p class="mt-1">
                                <a href="<?= url('/locations/' . $box['location_id']) ?>"><?= e($box['location_name']) ?></a>
                            </p>
                        </div>
                        <?php endif; ?>

                        <?php if ($box['description']): ?>
                        <div class="mb-4">
                            <span class="text-sm text-muted"><?= __('form.description') ?>:</span>
                            <p class="mt-1"><?= nl2br(e($box['description'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($box['notes']): ?>
                        <div class="mb-4">
                            <span class="text-sm text-muted"><?= __('form.notes') ?>:</span>
                            <p class="mt-1"><?= nl2br(e($box['notes'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="text-sm text-muted mt-4">
                            <?= __('misc.created_at') ?>: <?= formatDateGerman($box['created_at'], 'datetime') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materials in Box -->
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title"><?= __('box.contents') ?> (<?= count($materials) ?>)</h3>
                <a href="<?= url('/materials/create', ['box_id' => $box['id']]) ?>" class="btn btn-sm btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?= __('material.add_new') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($materials)): ?>
                <div class="empty-state" style="padding: var(--spacing-8);">
                    <p class="text-muted"><?= __('box.empty') ?></p>
                </div>
                <?php else: ?>
                <div class="table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= __('form.name') ?></th>
                                <th><?= __('form.status') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/materials/' . $material['id']) ?>">
                                        <?= e($material['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'complete' => ['label' => __('material.status.complete'), 'class' => 'badge-success'],
                                        'incomplete' => ['label' => __('material.status.incomplete'), 'class' => 'badge-warning'],
                                        'damaged' => ['label' => __('material.status.damaged'), 'class' => 'badge-danger'],
                                        'missing' => ['label' => __('material.status.missing'), 'class' => 'badge-danger'],
                                    ];
                                    $status = $statusLabels[$material['status']] ?? $statusLabels['complete'];
                                    ?>
                                    <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                                </td>
                                <td class="text-right">
                                    <a href="<?= url('/materials/' . $material['id']) ?>" class="btn btn-sm btn-outline">
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
                <h3 class="card-title"><?= __('misc.statistics') ?></h3>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <div style="width: 40px; height: 40px; background: var(--color-primary-bg); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold"><?= $box['material_count'] ?></div>
                        <div class="text-sm text-muted"><?= pluralize($box['material_count'], __('material.title'), __('material.title_plural')) ?></div>
                    </div>
                </div>

                <?php
                // Count materials by status
                $statusCounts = ['complete' => 0, 'incomplete' => 0, 'damaged' => 0, 'missing' => 0];
                foreach ($materials as $m) {
                    $statusCounts[$m['status']]++;
                }
                ?>

                <?php if ($statusCounts['complete'] > 0): ?>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="status-success"><?= __('material.status.complete') ?></span>
                    <span><?= $statusCounts['complete'] ?></span>
                </div>
                <?php endif; ?>

                <?php if ($statusCounts['incomplete'] > 0): ?>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="status-warning"><?= __('material.status.incomplete') ?></span>
                    <span><?= $statusCounts['incomplete'] ?></span>
                </div>
                <?php endif; ?>

                <?php if ($statusCounts['damaged'] > 0): ?>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="status-danger"><?= __('material.status.damaged') ?></span>
                    <span><?= $statusCounts['damaged'] ?></span>
                </div>
                <?php endif; ?>

                <?php if ($statusCounts['missing'] > 0): ?>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="status-danger"><?= __('material.status.missing') ?></span>
                    <span><?= $statusCounts['missing'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
