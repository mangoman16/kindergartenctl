<div class="category-help">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
    <span><?= __('help.category_categories') ?></span>
</div>

<div class="page-header">
    <h1 class="page-title"><?= __('category.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/categories/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('category.add_new') ?>
        </a>
    </div>
</div>

<?php if (empty($categories)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <h3 class="empty-state-title">Noch keine Altersgruppen vorhanden</h3>
            <p class="empty-state-text">Erstellen Sie Altersgruppen, um Spiele zu kategorisieren.</p>
            <a href="<?= url('/categories/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('category.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Categories Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;"></th>
                    <th><?= __('form.name') ?></th>
                    <th><?= __('form.description') ?></th>
                    <th style="width: 100px;"><?= __('category.sort_order') ?></th>
                    <th style="width: 120px;">Spiele</th>
                    <th style="width: 150px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td>
                        <?php if ($category['image_path']): ?>
                            <img src="<?= upload($category['image_path']) ?>" alt="<?= e($category['name']) ?>"
                                 style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= e($category['name']) ?></strong>
                    </td>
                    <td class="text-muted">
                        <?= e(truncate($category['description'] ?? '', 50)) ?>
                    </td>
                    <td class="text-center">
                        <?= $category['sort_order'] ?>
                    </td>
                    <td>
                        <span class="badge badge-primary"><?= $category['game_count'] ?> Spiele</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= url('/categories/' . $category['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <form action="<?= url('/categories/' . $category['id'] . '/delete') ?>" method="POST" style="display: inline;"
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
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
