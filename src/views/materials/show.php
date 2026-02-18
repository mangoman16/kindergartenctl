<div class="page-header">
    <h1 class="page-title"><?= e($material['name']) ?></h1>
    <div class="page-actions">
        <a href="<?= url('/materials/' . $material['id'] . '/print') ?>" class="btn btn-secondary" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <?= __('action.print') ?>
        </a>
        <a href="<?= url('/materials/' . $material['id'] . '/edit') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <?= __('action.edit') ?>
        </a>
    </div>
</div>

<div class="grid grid-cols-3">
    <!-- Material Details -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <h2 class="card-title"><?= __('misc.details') ?></h2>
        </div>
        <div class="card-body">
            <div class="flex gap-6">
                <?php if ($material['image_path']): ?>
                    <div style="flex-shrink: 0;">
                        <img src="<?= upload($material['image_path']) ?>" alt="<?= e($material['name']) ?>"
                             style="width: 150px; height: 150px; border-radius: var(--radius-lg); object-fit: cover;">
                    </div>
                <?php endif; ?>

                <div class="flex-1">
                    <dl class="detail-list">
                        <dt><?= __('form.name') ?></dt>
                        <dd><?= e($material['name']) ?></dd>

                        <?php if ($material['description']): ?>
                            <dt><?= __('form.description') ?></dt>
                            <dd><?= nl2br(e($material['description'])) ?></dd>
                        <?php endif; ?>

                        <dt><?= __('material.quantity') ?></dt>
                        <dd><?= $material['quantity'] ?: 'Nicht angegeben' ?></dd>

                        <dt><?= __('material.type') ?></dt>
                        <dd>
                            <?php if ($material['is_consumable']): ?>
                                <span class="badge badge-warning">Verbrauchsmaterial</span>
                            <?php else: ?>
                                <span class="badge badge-info">Ausrüstung</span>
                            <?php endif; ?>
                        </dd>

                        <dt><?= __('nav.games') ?></dt>
                        <dd><?= $material['game_count'] ?> <?= pluralize($material['game_count'], 'Spiel', 'Spiele') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Favorite Toggle -->
        <div class="card mb-4">
            <div class="card-body">
                <button type="button" id="favorite-toggle" class="btn btn-block <?= !empty($material['is_favorite']) ? 'btn-warning' : 'btn-secondary' ?>"
                        data-material-id="<?= $material['id'] ?>" data-is-favorite="<?= !empty($material['is_favorite']) ? '1' : '0' ?>">
                    <svg id="favorite-icon-filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" style="<?= empty($material['is_favorite']) ? 'display:none;' : '' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <svg id="favorite-icon-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="<?= !empty($material['is_favorite']) ? 'display:none;' : '' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span id="favorite-text"><?= !empty($material['is_favorite']) ? 'Favorit entfernen' : 'Als Favorit markieren' ?></span>
                </button>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __('misc.actions') ?></h2>
            </div>
            <div class="card-body">
                <div class="flex flex-col gap-2">
                    <a href="<?= url('/materials/' . $material['id'] . '/edit') ?>" class="btn btn-secondary btn-block">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <?= __('action.edit') ?>
                </a>
                <a href="<?= url('/materials/' . $material['id'] . '/print') ?>" class="btn btn-secondary btn-block" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <?= __('action.print') ?>
                </a>
                <form action="<?= url('/materials/' . $material['id'] . '/delete') ?>" method="POST"
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
                    Zur Gruppe hinzufügen
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($games)): ?>
<!-- Games using this material -->
<div class="card mt-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('material.used_in_games') ?></h2>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th><?= __('form.name') ?></th>
                    <th style="width: 100px; text-align: center;"><?= __('material.quantity') ?></th>
                    <th style="width: 100px;"></th>
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
                    <td class="text-center">
                        <?= $game['material_quantity'] ?: '1' ?>×
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
</div>
<?php endif; ?>

<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('favorite-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const materialId = this.dataset.materialId;
            const isFavorite = this.dataset.isFavorite === '1';

            toggleBtn.disabled = true;

            const formData = new FormData();
            formData.append('csrf_token', '<?= csrf() ?>');

            fetch('<?= url('/api/materials/') ?>' + materialId + '/toggle-favorite', {
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
    const addToGroupForm = document.getElementById('add-to-group-form');

    if (addToGroupForm) {
        addToGroupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('csrf_token', '<?= csrf() ?>');
            formData.append('item_type', 'material');
            formData.append('item_id', '<?= $material['id'] ?>');

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
            <button type="button" class="modal-close" onclick="closeAddToGroupModal()">&times;</button>
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
.modal {
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
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}
.modal-content {
    position: relative;
    background: white;
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 400px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-gray-200);
}
.modal-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-gray-500);
    line-height: 1;
}
.modal-close:hover {
    color: var(--color-gray-700);
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 16px 20px;
    border-top: 1px solid var(--color-gray-200);
}
</style>
<?php endif; ?>
