<div class="page-header">
    <h1 class="page-title"><?= __('material.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/materials/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('material.add_new') ?>
        </a>
    </div>
</div>

<?php if (empty($materials)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <h3 class="empty-state-title">Noch keine Materialien vorhanden</h3>
            <p class="empty-state-text">Erstellen Sie Materialien, die für Spiele benötigt werden.</p>
            <a href="<?= url('/materials/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('material.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Materials Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>
                        <a href="<?= url('/materials?sort=name&order=' . ($currentSort === 'name' && $currentOrder === 'asc' ? 'desc' : 'asc')) ?>"
                           class="table-sort <?= $currentSort === 'name' ? 'active ' . $currentOrder : '' ?>">
                            <?= __('form.name') ?>
                        </a>
                    </th>
                    <th><?= __('form.description') ?></th>
                    <th style="width: 100px; text-align: center;"><?= __('material.quantity') ?></th>
                    <th style="width: 80px; text-align: center;"><?= __('material.type') ?></th>
                    <th style="width: 100px; text-align: center;"><?= __('nav.games') ?></th>
                    <th style="width: 150px;"><?= __('misc.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $material): ?>
                <tr>
                    <td>
                        <?php if ($material['image_path']): ?>
                            <img src="<?= upload($material['image_path']) ?>" alt="<?= e($material['name']) ?>"
                                 style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('/materials/' . $material['id']) ?>" class="font-semibold text-primary">
                            <?= e($material['name']) ?>
                        </a>
                    </td>
                    <td class="text-muted"><?= e(truncate($material['description'] ?? '', 60)) ?></td>
                    <td class="text-center"><?= $material['quantity'] ?: '-' ?></td>
                    <td class="text-center">
                        <?php if ($material['is_consumable']): ?>
                            <span class="badge badge-warning">Verbrauch</span>
                        <?php else: ?>
                            <span class="badge badge-info">Ausrüstung</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge"><?= $material['game_count'] ?></span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?= url('/materials/' . $material['id']) ?>" class="btn btn-sm btn-secondary" title="Anzeigen">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= url('/materials/' . $material['id'] . '/edit') ?>" class="btn btn-sm btn-secondary" title="Bearbeiten">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <form action="<?= url('/materials/' . $material['id'] . '/delete') ?>" method="POST"
                                  onsubmit="return confirm('<?= __('misc.confirm_delete') ?>')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Löschen">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
