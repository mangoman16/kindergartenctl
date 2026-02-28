<div class="page-header">
    <h1 class="page-title"><?= __('material.title_plural') ?></h1>
    <div class="page-actions">
        <button type="button" id="toggle-selection-mode" class="btn btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <?= __('bulk.multi_select') ?>
        </button>
        <a href="<?= url('/materials/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('material.add_new') ?>
        </a>
    </div>
</div>

<!-- Inline Filters -->
<form action="<?= url('/materials') ?>" method="GET" class="inline-filters">
    <label class="inline-filter-check">
        <input type="checkbox" name="favorites" value="1" <?= !empty($filters['is_favorite']) ? 'checked' : '' ?> onchange="this.form.submit()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" style="color: var(--color-warning);">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
        </svg>
        <span><?= __('misc.favorites_only') ?></span>
    </label>
    <?php if (!empty($filters['search']) || !empty($filters['is_favorite'])): ?>
        <a href="<?= url('/materials') ?>" class="inline-filter-reset"><?= __('action.reset') ?></a>
    <?php endif; ?>
    <input type="hidden" name="q" value="<?= e($filters['search'] ?? '') ?>">
</form>

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
            <h3 class="empty-state-title"><?= __('material.empty_title') ?></h3>
            <p class="empty-state-text"><?= __('material.empty_text') ?></p>
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

<!-- Bulk Actions Bar -->
<div id="bulk-actions-bar" class="bulk-actions-bar" style="display: none;">
    <div class="flex items-center gap-4">
        <span id="selected-count" class="text-muted">0 <?= __('bulk.selected') ?></span>
    </div>
    <div class="flex gap-2">
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-add-group" title="<?= __('group.add_to') ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            <?= __('bulk.add_to_group') ?>
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-add-favorites" title="<?= __('misc.add_to_favorites') ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <?= __('bulk.add_favorites') ?>
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-remove-favorites" title="<?= __('misc.remove_from_favorites') ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                <line x1="4" y1="4" x2="20" y2="20"></line>
            </svg>
            <?= __('bulk.remove_favorites') ?>
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-cancel"><?= __('action.cancel') ?></button>
    </div>
</div>

<!-- Materials Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px; display: none;" class="bulk-select-col">
                        <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                    </th>
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
                <tr data-material-id="<?= $material['id'] ?>">
                    <td class="bulk-select-col" style="display: none;">
                        <input type="checkbox" class="material-select-checkbox form-check-input" value="<?= $material['id'] ?>">
                    </td>
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
                            <span class="badge badge-warning"><?= __('material.consumable_badge') ?></span>
                        <?php else: ?>
                            <span class="badge badge-info"><?= __('material.equipment_badge') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge"><?= $material['game_count'] ?></span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="<?= url('/materials/' . $material['id']) ?>" class="btn btn-sm btn-secondary" title="<?= __('action.view') ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= url('/materials/' . $material['id'] . '/edit') ?>" class="btn btn-sm btn-secondary" title="<?= __('action.edit') ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <form action="<?= url('/materials/' . $material['id'] . '/delete') ?>" method="POST"
                                  onsubmit="return confirm('<?= __('misc.confirm_delete') ?>')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="<?= __('action.delete') ?>">
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

<!-- Add to Group Modal -->
<div id="add-to-group-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?= __('group.add_to') ?></h3>
            <button type="button" class="modal-close" onclick="closeGroupModal()" aria-label="<?= __('action.close') ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label"><?= __('group.select') ?></label>
                <select id="bulk-group-select" class="form-control">
                    <option value=""><?= __('form.select_option') ?></option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeGroupModal()"><?= __('action.cancel') ?></button>
            <button type="button" class="btn btn-primary" onclick="confirmBulkAddToGroup()"><?= __('action.add') ?></button>
        </div>
    </div>
</div>

<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {
    const bulkBar = document.getElementById('bulk-actions-bar');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const selectedCountEl = document.getElementById('selected-count');
    const cancelBtn = document.getElementById('bulk-cancel');
    const toggleBtn = document.getElementById('toggle-selection-mode');

    let selectionMode = false;
    let selectedIds = new Set();

    // Toggle selection mode from the header button
    toggleBtn?.addEventListener('click', function() {
        if (selectionMode) {
            disableSelectionMode();
        } else {
            enableSelectionMode();
        }
    });

    // Checkbox change handlers
    document.querySelectorAll('.material-select-checkbox, #select-all-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.id === 'select-all-checkbox') {
                document.querySelectorAll('.material-select-checkbox').forEach(mcb => {
                    mcb.checked = this.checked;
                    const row = mcb.closest('tr');
                    row.classList.toggle('selected', this.checked);
                    if (this.checked) selectedIds.add(mcb.value);
                    else selectedIds.delete(mcb.value);
                });
            } else {
                const row = this.closest('tr');
                row.classList.toggle('selected', this.checked);
                if (this.checked) selectedIds.add(this.value);
                else selectedIds.delete(this.value);
            }
            updateSelectedCount();
        });
    });

    function enableSelectionMode() {
        selectionMode = true;
        toggleBtn?.classList.add('active');
        document.querySelector('.table')?.classList.add('selection-mode');
        document.querySelectorAll('.bulk-select-col').forEach(col => col.style.display = 'table-cell');
        bulkBar.style.display = 'flex';
    }

    function disableSelectionMode() {
        selectionMode = false;
        toggleBtn?.classList.remove('active');
        document.querySelector('.table')?.classList.remove('selection-mode');
        document.querySelectorAll('.bulk-select-col').forEach(col => col.style.display = 'none');
        bulkBar.style.display = 'none';
        clearSelection();
    }

    cancelBtn?.addEventListener('click', disableSelectionMode);

    function clearSelection() {
        selectedIds.clear();
        document.querySelectorAll('.material-select-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('tr').classList.remove('selected');
        });
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        updateSelectedCount();
    }

    function updateSelectedCount() {
        if (selectedCountEl) {
            selectedCountEl.textContent = selectedIds.size + ' <?= __('bulk.selected') ?>';
        }
    }

    // Bulk add to favorites
    document.getElementById('bulk-add-favorites')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('<?= __('bulk.no_items_selected') ?>');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/materials/') ?>' + id + '/toggle-favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ favorite: true })
            });
        }

        alert('<?= __('bulk.added_to_favorites') ?>');
        location.reload();
    });

    // Bulk remove from favorites
    document.getElementById('bulk-remove-favorites')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('<?= __('bulk.no_items_selected') ?>');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/materials/') ?>' + id + '/toggle-favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ favorite: false })
            });
        }

        alert('<?= __('bulk.removed_from_favorites') ?>');
        location.reload();
    });

    // Bulk add to group
    document.getElementById('bulk-add-group')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('<?= __('bulk.no_items_selected') ?>');

        // Load groups
        try {
            const response = await fetch('<?= url('/api/groups') ?>');
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();

            const groups = Array.isArray(data.groups) ? data.groups : (Array.isArray(data) ? data : []);
            const select = document.getElementById('bulk-group-select');
            select.innerHTML = '<option value=""><?= __('form.select_option') ?></option>';
            groups.forEach(group => {
                if (group.id && group.name) {
                    const opt = document.createElement('option');
                    opt.value = group.id;
                    opt.textContent = group.name;
                    select.appendChild(opt);
                }
            });

            document.getElementById('add-to-group-modal').style.display = 'flex';
        } catch (error) {
            console.error('Failed to load groups:', error);
            alert('<?= __('flash.error') ?>');
        }
    });

    window.closeGroupModal = function() {
        document.getElementById('add-to-group-modal').style.display = 'none';
    };

    window.confirmBulkAddToGroup = async function() {
        const groupId = document.getElementById('bulk-group-select').value;
        if (!groupId) return alert('<?= __('bulk.select_group') ?>');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/groups/add-item') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ group_id: groupId, item_type: 'material', item_id: id })
            });
        }

        closeGroupModal();
        alert('<?= __('bulk.added_to_group') ?>');
        location.reload();
    };
});
</script>
<?php endif; ?>
