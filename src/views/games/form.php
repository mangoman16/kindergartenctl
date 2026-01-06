<div class="page-header">
    <h1 class="page-title"><?= $isEdit ? __('game.edit') : __('game.create') ?></h1>
</div>

<form action="<?= $isEdit ? url('/games/' . $game['id']) : url('/games') ?>" method="POST">
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
                               value="<?= old('name', $game['name'] ?? '') ?>"
                               data-check-duplicate="games"
                               <?= $isEdit ? 'data-exclude-id="' . $game['id'] . '"' : '' ?>
                               required maxlength="255" placeholder="Name des Spiels">
                        <?php if (hasError('name', $errors ?? [])): ?>
                            <div class="form-error"><?= getError('name', $errors) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <?= __('form.description') ?>
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="4"
                                  placeholder="Kurze Beschreibung des Spiels"><?= old('description', $game['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="instructions" class="form-label">
                            <?= __('game.instructions') ?>
                        </label>
                        <textarea id="instructions" name="instructions" class="form-control" rows="6"
                                  placeholder="Spielanleitung, Regeln, Tipps..."><?= old('instructions', $game['instructions'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Spieldetails</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="min_players" class="form-label"><?= __('game.min_players') ?></label>
                            <input type="number" id="min_players" name="min_players"
                                   class="form-control" min="1" max="100"
                                   value="<?= old('min_players', $game['min_players'] ?? '') ?>"
                                   placeholder="z.B. 2">
                        </div>

                        <div class="form-group">
                            <label for="max_players" class="form-label"><?= __('game.max_players') ?></label>
                            <input type="number" id="max_players" name="max_players"
                                   class="form-control" min="1" max="100"
                                   value="<?= old('max_players', $game['max_players'] ?? '') ?>"
                                   placeholder="z.B. 10">
                        </div>

                        <div class="form-group">
                            <label for="duration_minutes" class="form-label"><?= __('game.duration') ?></label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="duration_minutes" name="duration_minutes"
                                       class="form-control" min="1" max="999"
                                       value="<?= old('duration_minutes', $game['duration_minutes'] ?? '') ?>"
                                       placeholder="z.B. 15">
                                <span class="text-muted">Min.</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="box_id" class="form-label"><?= __('nav.boxes') ?></label>
                            <select id="box_id" name="box_id" class="form-control">
                                <option value="">-- Keine Box --</option>
                                <?php foreach ($boxes as $box): ?>
                                    <option value="<?= $box['id'] ?>" <?= old('box_id', $game['box_id'] ?? '') == $box['id'] ? 'selected' : '' ?>>
                                        <?= e($box['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="form-label"><?= __('game.age_group') ?></label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">-- Keine Altersgruppe --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= old('category_id', $game['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-6 mt-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_outdoor" value="1"
                                   <?= old('is_outdoor', $game['is_outdoor'] ?? 0) ? 'checked' : '' ?>>
                            <span><?= __('game.is_outdoor') ?></span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= old('is_active', $game['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span><?= __('game.is_active') ?></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Image Upload -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><?= __('form.image') ?></h2>
                </div>
                <div class="card-body">
                    <div class="image-upload-container" data-type="games">
                        <input type="hidden" name="image_path" value="<?= e($game['image_path'] ?? '') ?>">
                        <div class="image-preview" style="width: 100%; aspect-ratio: 1; border: 2px dashed var(--color-gray-300); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden;">
                            <?php if (!empty($game['image_path'])): ?>
                                <img src="<?= upload($game['image_path']) ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
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

            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><?= __('nav.tags') ?></h2>
                </div>
                <div class="card-body">
                    <div class="tags-checkboxes" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($tags as $tag): ?>
                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                       <?= in_array($tag['id'], $selectedTags) ? 'checked' : '' ?>>
                                <span class="tag-badge-sm" style="<?= $tag['color'] ? 'background-color: ' . e($tag['color']) . '; color: white;' : '' ?>">
                                    <?= e($tag['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (empty($tags)): ?>
                        <p class="text-muted text-sm">Keine Themen vorhanden. <a href="<?= url('/tags/create') ?>">Erstellen</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Materials -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= __('nav.materials') ?></h2>
                </div>
                <div class="card-body">
                    <div id="materials-list">
                        <?php foreach ($selectedMaterials as $material): ?>
                            <div class="material-row flex items-center gap-2 mb-2">
                                <input type="number" name="materials[<?= $material['id'] ?>][quantity]"
                                       value="<?= $material['quantity'] ?>" min="1" max="99"
                                       class="form-control" style="width: 60px;">
                                <span class="flex-1"><?= e($material['name']) ?></span>
                                <input type="hidden" name="materials[<?= $material['id'] ?>][id]" value="<?= $material['id'] ?>">
                                <button type="button" class="btn btn-sm btn-danger remove-material" title="Entfernen">
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
    </div>

    <div class="form-actions mt-4">
        <a href="<?= $isEdit ? url('/games/' . $game['id']) : url('/games') ?>" class="btn btn-secondary">
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
.tag-badge-sm {
    display: inline-block;
    padding: 2px 8px;
    background: var(--color-gray-100);
    border-radius: 9999px;
    font-size: 0.813rem;
}
.material-row {
    padding: 8px;
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add material functionality
    const addMaterialSelect = document.getElementById('add-material');
    const materialsList = document.getElementById('materials-list');

    addMaterialSelect.addEventListener('change', function() {
        const materialId = this.value;
        const materialName = this.options[this.selectedIndex].dataset.name;

        if (!materialId) return;

        // Check if already added
        if (materialsList.querySelector(`input[value="${materialId}"]`)) {
            alert('Material bereits hinzugefügt');
            this.value = '';
            return;
        }

        // Add new material row
        const row = document.createElement('div');
        row.className = 'material-row flex items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="number" name="materials[${materialId}][quantity]" value="1" min="1" max="99"
                   class="form-control" style="width: 60px;">
            <span class="flex-1">${materialName}</span>
            <input type="hidden" name="materials[${materialId}][id]" value="${materialId}">
            <button type="button" class="btn btn-sm btn-danger remove-material" title="Entfernen">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        materialsList.appendChild(row);

        // Reset select
        this.value = '';
    });

    // Remove material functionality
    materialsList.addEventListener('click', function(e) {
        if (e.target.closest('.remove-material')) {
            e.target.closest('.material-row').remove();
        }
    });
});
</script>
