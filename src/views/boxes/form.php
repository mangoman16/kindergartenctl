<div class="page-header">
    <h1 class="page-title"><?= $isEdit ? __('box.edit') : __('box.create') ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="<?= $isEdit ? url('/boxes/' . $box['id']) : url('/boxes') ?>" method="POST">
            <?= csrfField() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">
                        <?= __('form.name') ?> <span class="required">*</span>
                        <span class="help-tooltip" data-help="<?= e(__('help.field_name')) ?>">?</span>
                    </label>
                    <input type="text" id="name" name="name"
                           class="form-control <?= hasError('name', $errors ?? []) ? 'is-invalid' : '' ?>"
                           value="<?= old('name', $box['name'] ?? '') ?>"
                           data-check-duplicate="boxes"
                           <?= $isEdit ? 'data-exclude-id="' . $box['id'] . '"' : '' ?>
                           required maxlength="100">
                    <?php if (hasError('name', $errors ?? [])): ?>
                        <div class="form-error"><?= getError('name', $errors) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="number" class="form-label">
                        <?= __('box.number') ?>
                        <span class="help-tooltip" data-help="<?= e(__('help.field_box_number')) ?>">?</span>
                    </label>
                    <input type="text" id="number" name="number"
                           class="form-control"
                           value="<?= old('number', $box['number'] ?? '') ?>"
                           maxlength="20" placeholder="<?= __('form.placeholder.box_number') ?>">
                </div>

                <div class="form-group">
                    <label for="label" class="form-label">
                        <?= __('box.label') ?>
                        <span class="help-tooltip" data-help="<?= e(__('help.field_box_label')) ?>">?</span>
                    </label>
                    <input type="text" id="label" name="label"
                           class="form-control"
                           value="<?= old('label', $box['label'] ?? '') ?>"
                           maxlength="50" placeholder="<?= __('form.placeholder.box_label') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="location_id" class="form-label">
                    <?= __('box.location') ?>
                    <span class="help-tooltip" data-help="<?= e(__('help.field_box_location')) ?>">?</span>
                </label>
                <select id="location_id" name="location_id" class="form-control">
                    <option value=""><?= __('form.select_option') ?></option>
                    <?php foreach ($locations ?? [] as $loc): ?>
                        <option value="<?= $loc['id'] ?>" <?= old('location_id', $box['location_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>>
                            <?= e($loc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">
                    <?= __('form.description') ?>
                </label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= old('description', $box['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">
                    <?= __('form.notes') ?>
                </label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?= old('notes', $box['notes'] ?? '') ?></textarea>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label class="form-label"><?= __('form.image') ?></label>
                <div class="image-upload-container" data-type="boxes">
                    <input type="hidden" name="image_path" value="<?= e($box['image_path'] ?? '') ?>">
                    <div class="image-preview" style="width: 120px; height: 120px; border: 2px dashed var(--color-gray-300); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden;">
                        <?php if (!empty($box['image_path'])): ?>
                            <img src="<?= upload($box['image_path']) ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin: 0 auto;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <div class="text-sm mt-2"><?= __('form.upload_image') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                    <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="this.previousElementSibling.click()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <?= __('action.upload') ?>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= $isEdit ? url('/boxes/' . $box['id']) : url('/boxes') ?>" class="btn btn-secondary">
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
