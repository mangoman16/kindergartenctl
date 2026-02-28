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
                                <span class="badge badge-danger"><?= __('game.inactive') ?></span>
                            <?php endif; ?>
                            <?php if ($game['is_outdoor']): ?>
                                <span class="badge badge-warning">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-inline">
                                        <circle cx="12" cy="12" r="5"></circle>
                                        <line x1="12" y1="1" x2="12" y2="3"></line>
                                        <line x1="12" y1="21" x2="12" y2="23"></line>
                                    </svg>
                                    <?= __('game.outdoor') ?>
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
                                    <?= $game['min_players'] ?> - <?= $game['max_players'] ?> <?= __('game.players') ?>
                                <?php elseif ($game['min_players']): ?>
                                    <?= __('misc.from') ?> <?= $game['min_players'] ?> <?= __('game.players') ?>
                                <?php elseif ($game['max_players']): ?>
                                    <?= __('misc.to') ?> <?= $game['max_players'] ?> <?= __('game.players') ?>
                                <?php else: ?>
                                    <span class="text-muted"><?= __('misc.not_specified') ?></span>
                                <?php endif; ?>
                            </dd>

                            <dt><?= __('game.duration') ?></dt>
                            <dd>
                                <?php if ($game['duration_minutes']): ?>
                                    <?= $game['duration_minutes'] ?> <?= __('game.minutes') ?>
                                <?php else: ?>
                                    <span class="text-muted"><?= __('misc.not_specified') ?></span>
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
                    <span id="favorite-text"><?= !empty($game['is_favorite']) ? __('misc.remove_from_favorites') : __('misc.add_to_favorites') ?></span>
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
                    <?php if (!empty($groups)): ?>
                    <button type="button" class="btn btn-secondary btn-block" onclick="openAddToGroupModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            <line x1="12" y1="11" x2="12" y2="17"></line>
                            <line x1="9" y1="14" x2="15" y2="14"></line>
                        </svg>
                        <?= __('group.add_to') ?>
                    </button>
                    <?php endif; ?>
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
            <div class="card-body card-body-flush">
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

<style<?= cspNonce() ?>>
.detail-list {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 8px 16px;
    margin: 0;
    padding: 0;
}
.detail-list dt {
    font-weight: 600;
    color: var(--color-gray-500);
    font-size: 0.875rem;
    padding-top: 2px;
}
.detail-list dd {
    margin: 0;
}
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

<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('favorite-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const isFavorite = this.dataset.isFavorite === '1';

            toggleBtn.disabled = true;

            const formData = new FormData();
            formData.append('csrf_token', '<?= e($csrfToken) ?>');

            fetch('<?= url('/api/games/') ?>' + gameId + '/toggle-favorite', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const newFavorite = data.is_favorite;
                    toggleBtn.dataset.isFavorite = newFavorite ? '1' : '0';
                    toggleBtn.className = 'btn btn-block ' + (newFavorite ? 'btn-warning' : 'btn-secondary');
                    document.getElementById('favorite-icon-filled').style.display = newFavorite ? '' : 'none';
                    document.getElementById('favorite-icon-outline').style.display = newFavorite ? 'none' : '';
                    document.getElementById('favorite-text').textContent = newFavorite ? '<?= __('misc.remove_from_favorites') ?>' : '<?= __('misc.add_to_favorites') ?>';
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

    // Add to group modal functionality
    const addToGroupModal = document.getElementById('add-to-group-modal');
    const addToGroupForm = document.getElementById('add-to-group-form');

    if (addToGroupForm) {
        addToGroupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('csrf_token', '<?= e($csrfToken) ?>');
            formData.append('item_type', 'game');
            formData.append('item_id', '<?= $game['id'] ?>');

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            fetch('<?= url('/api/groups/add-item') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeAddToGroupModal();
                    alert('<?= __('flash.added_to_group') ?>');
                } else {
                    alert(data.error || '<?= __('flash.error_generic') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('flash.error_generic') ?>');
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        });
    }

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddToGroupModal();
        }
    });
});

function openAddToGroupModal() {
    const modal = document.getElementById('add-to-group-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeAddToGroupModal() {
    const modal = document.getElementById('add-to-group-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php if (!empty($groups)): ?>
<!-- Add to Group Modal -->
<div id="add-to-group-modal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAddToGroupModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?= __('group.add_to') ?></h3>
            <button type="button" class="modal-close" onclick="closeAddToGroupModal()" aria-label="<?= __('action.close') ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
        </div>
        <form id="add-to-group-form">
            <div class="modal-body">
                <div class="form-group">
                    <label for="group_id"><?= __('group.select') ?></label>
                    <select name="group_id" id="group_id" class="form-control" required>
                        <option value=""><?= __('form.select_option') ?></option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddToGroupModal()"><?= __('action.cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= __('action.add') ?></button>
            </div>
        </form>
    </div>
</div>

<style<?= cspNonce() ?>>
#add-to-group-modal.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
#add-to-group-modal .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}
#add-to-group-modal .modal-content {
    position: relative;
    background: var(--color-white);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 400px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
#add-to-group-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-gray-200);
}
#add-to-group-modal .modal-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
}
#add-to-group-modal .modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-gray-500);
    line-height: 1;
}
#add-to-group-modal .modal-close:hover {
    color: var(--color-gray-700);
}
#add-to-group-modal .modal-body {
    padding: 20px;
}
#add-to-group-modal .modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 16px 20px;
    border-top: 1px solid var(--color-gray-200);
}
</style>
<?php endif; ?>
