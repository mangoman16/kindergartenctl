<div class="page-header">
    <h1 class="page-title"><?= $isEdit ? __('group.edit') : __('group.create') ?></h1>
</div>

<form action="<?= $isEdit ? url('/groups/' . $group['id']) : url('/groups') ?>" method="POST">
    <?= csrfField() ?>

    <div class="grid grid-cols-3 gap-4">
        <!-- Main Form -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Grundinformationen</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <?= __('form.name') ?> <span class="required">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="form-control <?= hasError('name', $errors ?? []) ? 'is-invalid' : '' ?>"
                               value="<?= old('name', $group['name'] ?? '') ?>"
                               data-check-duplicate="groups"
                               <?= $isEdit ? 'data-exclude-id="' . $group['id'] . '"' : '' ?>
                               required maxlength="100" placeholder="z.B. Sommerfest, Turntag">
                        <?php if (hasError('name', $errors ?? [])): ?>
                            <div class="form-error"><?= getError('name', $errors) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <?= __('form.description') ?>
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="Beschreibung der Gruppe"><?= old('description', $group['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Games Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><?= __('nav.games') ?></h2>
                </div>
                <div class="card-body">
                    <div id="games-list">
                        <?php foreach ($selectedGames as $game): ?>
                            <div class="item-row flex items-center gap-2 mb-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted cursor-move">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                                <span class="flex-1"><?= e($game['name']) ?></span>
                                <input type="hidden" name="games[]" value="<?= $game['id'] ?>">
                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Entfernen">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3">
                        <select id="add-game" class="form-control">
                            <option value="">Spiel hinzufügen...</option>
                            <?php foreach ($games as $game): ?>
                                <option value="<?= $game['id'] ?>" data-name="<?= e($game['name']) ?>">
                                    <?= e($game['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Materials Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= __('nav.materials') ?></h2>
                </div>
                <div class="card-body">
                    <div id="materials-list">
                        <?php foreach ($selectedMaterials as $material): ?>
                            <div class="item-row flex items-center gap-2 mb-2">
                                <input type="number" name="materials[<?= $material['id'] ?>][quantity]"
                                       value="<?= $material['quantity'] ?>" min="1" max="99"
                                       class="form-control" style="width: 60px;">
                                <span class="flex-1"><?= e($material['name']) ?></span>
                                <input type="hidden" name="materials[<?= $material['id'] ?>][id]" value="<?= $material['id'] ?>">
                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Entfernen">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3">
                        <select id="add-material" class="form-control">
                            <option value="">Material hinzufügen...</option>
                            <?php foreach ($materials as $material): ?>
                                <option value="<?= $material['id'] ?>" data-name="<?= e($material['name']) ?>">
                                    <?= e($material['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Image Upload -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= __('form.image') ?></h2>
                </div>
                <div class="card-body">
                    <div class="image-upload-container" data-type="groups">
                        <input type="hidden" name="image_path" value="<?= e($group['image_path'] ?? '') ?>">
                        <div class="image-preview" style="width: 100%; aspect-ratio: 16/9; border: 2px dashed var(--color-gray-300); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden;">
                            <?php if (!empty($group['image_path'])): ?>
                                <img src="<?= upload($group['image_path']) ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin: 0 auto;">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <div class="mt-2">Bild hochladen</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                        <button type="button" class="btn btn-secondary btn-block mt-3" onclick="this.previousElementSibling.click()">
                            <?= __('action.upload') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions mt-4">
        <a href="<?= $isEdit ? url('/groups/' . $group['id']) : url('/groups') ?>" class="btn btn-secondary">
            <?= __('action.cancel') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            <?= __('action.save') ?>
        </button>
    </div>
</form>

<style>
.item-row {
    padding: 8px 12px;
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add game functionality
    const addGameSelect = document.getElementById('add-game');
    const gamesList = document.getElementById('games-list');

    addGameSelect.addEventListener('change', function() {
        const gameId = this.value;
        const gameName = this.options[this.selectedIndex].dataset.name;

        if (!gameId) return;

        // Check if already added
        if (gamesList.querySelector(`input[value="${gameId}"]`)) {
            alert('Spiel bereits hinzugefügt');
            this.value = '';
            return;
        }

        const row = document.createElement('div');
        row.className = 'item-row flex items-center gap-2 mb-2';
        row.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted cursor-move">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            <span class="flex-1">${gameName}</span>
            <input type="hidden" name="games[]" value="${gameId}">
            <button type="button" class="btn btn-sm btn-danger remove-item" title="Entfernen">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        gamesList.appendChild(row);
        this.value = '';
    });

    // Add material functionality
    const addMaterialSelect = document.getElementById('add-material');
    const materialsList = document.getElementById('materials-list');

    addMaterialSelect.addEventListener('change', function() {
        const materialId = this.value;
        const materialName = this.options[this.selectedIndex].dataset.name;

        if (!materialId) return;

        if (materialsList.querySelector(`input[value="${materialId}"]`)) {
            alert('Material bereits hinzugefügt');
            this.value = '';
            return;
        }

        const row = document.createElement('div');
        row.className = 'item-row flex items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="number" name="materials[${materialId}][quantity]" value="1" min="1" max="99"
                   class="form-control" style="width: 60px;">
            <span class="flex-1">${materialName}</span>
            <input type="hidden" name="materials[${materialId}][id]" value="${materialId}">
            <button type="button" class="btn btn-sm btn-danger remove-item" title="Entfernen">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        materialsList.appendChild(row);
        this.value = '';
    });

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
});
</script>
