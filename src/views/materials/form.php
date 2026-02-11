<div class="page-header">
    <h1 class="page-title"><?= $isEdit ? __('material.edit') : __('material.create') ?></h1>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="<?= $isEdit ? url('/materials/' . $material['id']) : url('/materials') ?>" method="POST">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="name" class="form-label">
                    <?= __('form.name') ?> <span class="required">*</span>
                    <span class="help-tooltip" data-help="<?= e(__('help.field_name')) ?>">?</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-control <?= hasError('name', $errors ?? []) ? 'is-invalid' : '' ?>"
                       value="<?= old('name', $material['name'] ?? '') ?>"
                       data-check-duplicate="materials"
                       <?= $isEdit ? 'data-exclude-id="' . $material['id'] . '"' : '' ?>
                       required maxlength="100" placeholder="z.B. Bälle, Seile, Stifte">
                <?php if (hasError('name', $errors ?? [])): ?>
                    <div class="form-error"><?= getError('name', $errors) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">
                    <?= __('form.description') ?>
                </label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Optionale Beschreibung des Materials"><?= old('description', $material['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label for="quantity" class="form-label">
                        <?= __('material.quantity') ?>
                        <span class="help-tooltip" data-help="<?= e(__('help.field_quantity')) ?>">?</span>
                    </label>
                    <input type="number" id="quantity" name="quantity"
                           class="form-control" style="width: 100px;"
                           value="<?= old('quantity', $material['quantity'] ?? 0) ?>"
                           min="0">
                    <div class="form-hint">Verfügbare Anzahl (0 = unbekannt)</div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= __('material.type') ?> <span class="help-tooltip" data-help="<?= e(__('help.field_consumable')) ?>">?</span></label>
                    <div class="mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_consumable" value="1"
                                   <?= old('is_consumable', $material['is_consumable'] ?? 0) ? 'checked' : '' ?>>
                            <span><?= __('material.is_consumable') ?></span>
                        </label>
                        <div class="form-hint mt-1">Verbrauchsmaterial wird nach Nutzung aufgebraucht</div>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label class="form-label"><?= __('form.image') ?></label>
                <div class="image-upload-container" data-type="materials">
                    <input type="hidden" name="image_path" value="<?= e($material['image_path'] ?? '') ?>">
                    <div class="image-preview" style="width: 120px; height: 120px; border: 2px dashed var(--color-gray-300); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden;">
                        <?php if (!empty($material['image_path'])): ?>
                            <img src="<?= upload($material['image_path']) ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin: 0 auto;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <div class="text-xs mt-1">Bild</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                    <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="this.previousElementSibling.click()">
                        <?= __('action.upload') ?>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= url('/materials') ?>" class="btn btn-secondary">
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
    </div>
</div>
