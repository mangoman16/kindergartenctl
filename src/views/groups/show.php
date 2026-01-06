<div class="page-header">
    <h1 class="page-title"><?= e($group['name']) ?></h1>
    <div class="page-actions">
        <a href="<?= url('/groups/' . $group['id'] . '/edit') ?>" class="btn btn-primary">
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
        <!-- Group Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="flex gap-6">
                    <?php if ($group['image_path']): ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?= upload($group['image_path']) ?>" alt="<?= e($group['name']) ?>"
                                 style="width: 150px; height: 100px; border-radius: var(--radius-lg); object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <?php if ($group['description']): ?>
                            <p><?= nl2br(e($group['description'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Keine Beschreibung vorhanden.</p>
                        <?php endif; ?>

                        <div class="flex gap-4 mt-4">
                            <div class="stat-box">
                                <div class="stat-value"><?= $group['game_count'] ?></div>
                                <div class="stat-label"><?= pluralize($group['game_count'], 'Spiel', 'Spiele') ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?= $group['material_count'] ?></div>
                                <div class="stat-label"><?= pluralize($group['material_count'], 'Material', 'Materialien') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Games -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('nav.games') ?> (<?= count($games) ?>)</h2>
            </div>
            <?php if (empty($games)): ?>
                <div class="card-body">
                    <p class="text-muted">Keine Spiele in dieser Gruppe.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th><?= __('form.name') ?></th>
                                <th><?= __('nav.boxes') ?></th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($games as $game): ?>
                            <tr>
                                <td>
                                    <?php if ($game['image_path']): ?>
                                        <img src="<?= upload($game['image_path']) ?>" alt="<?= e($game['name']) ?>"
                                             style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polygon points="10 8 16 12 10 16 10 8"></polygon>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('/games/' . $game['id']) ?>" class="font-semibold text-primary">
                                        <?= e($game['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($game['box_name']): ?>
                                        <?= e($game['box_name']) ?>
                                        <?php if ($game['box_label']): ?>
                                            <span class="badge badge-sm"><?= e($game['box_label']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('/games/' . $game['id']) ?>" class="btn btn-sm btn-secondary">
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

        <!-- Materials -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __('nav.materials') ?> (<?= count($materials) ?>)</h2>
            </div>
            <?php if (empty($materials)): ?>
                <div class="card-body">
                    <p class="text-muted">Keine Materialien in dieser Gruppe.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center;"><?= __('material.quantity') ?></th>
                                <th><?= __('form.name') ?></th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                            <tr>
                                <td class="text-center"><?= $material['quantity'] ?>×</td>
                                <td>
                                    <a href="<?= url('/materials/' . $material['id']) ?>" class="font-semibold text-primary">
                                        <?= e($material['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= url('/materials/' . $material['id']) ?>" class="btn btn-sm btn-secondary">
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

    <!-- Sidebar -->
    <div>
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __('misc.actions') ?></h2>
            </div>
            <div class="card-body">
                <div class="flex flex-col gap-2">
                    <a href="<?= url('/groups/' . $group['id'] . '/edit') ?>" class="btn btn-secondary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <?= __('action.edit') ?>
                    </a>
                    <form action="<?= url('/groups/' . $group['id'] . '/delete') ?>" method="POST"
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
    </div>
</div>

<style>
.stat-box {
    text-align: center;
    padding: 12px 24px;
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
}
.stat-label {
    font-size: 0.875rem;
    color: var(--color-gray-500);
}
</style>
