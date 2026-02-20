<div class="page-header">
    <h1 class="page-title"><?= __('tag.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/tags/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('tag.add_new') ?>
        </a>
    </div>
</div>

<?php if (empty($tags)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <h3 class="empty-state-title"><?= __('tag.empty_title') ?></h3>
            <p class="empty-state-text"><?= __('tag.empty_text') ?></p>
            <a href="<?= url('/tags/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('tag.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Tags Grid -->
<div class="grid grid-cols-4 gap-4">
    <?php foreach ($tags as $tag): ?>
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-3">
                <?php if ($tag['image_path']): ?>
                    <img src="<?= upload($tag['image_path']) ?>" alt="<?= e($tag['name']) ?>"
                         style="width: 48px; height: 48px; border-radius: var(--radius-md); object-fit: cover;">
                <?php elseif ($tag['color']): ?>
                    <div style="width: 48px; height: 48px; background: <?= e($tag['color']) ?>; border-radius: var(--radius-md);"></div>
                <?php else: ?>
                    <div style="width: 48px; height: 48px; background: var(--color-gray-100); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 class="font-semibold mb-0"><?= e($tag['name']) ?></h3>
                    <span class="text-sm text-muted"><?= $tag['game_count'] ?> <?= pluralize($tag['game_count'], 'Spiel', 'Spiele') ?></span>
                </div>
            </div>

            <?php if ($tag['description']): ?>
                <p class="text-sm text-muted mb-3"><?= e(truncate($tag['description'], 80)) ?></p>
            <?php endif; ?>

            <div class="flex gap-2">
                <a href="<?= url('/tags/' . $tag['id'] . '/edit') ?>" class="btn btn-sm btn-secondary flex-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <?= __('action.edit') ?>
                </a>
                <form action="<?= url('/tags/' . $tag['id'] . '/delete') ?>" method="POST"
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
