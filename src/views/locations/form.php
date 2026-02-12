<div class="page-header">
    <h1 class="page-title"><?= $isEdit ? __('location.edit') : __('location.create') ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="<?= $isEdit ? url('/locations/' . $location['id']) : url('/locations') ?>" method="POST">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="name" class="form-label">
                    <?= __('form.name') ?> <span class="required">*</span>
                    <span class="help-tooltip" data-help="<?= e(__('help.field_location_name')) ?>">?</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-control <?= hasError('name', $errors ?? []) ? 'is-invalid' : '' ?>"
                       value="<?= old('name', $location['name'] ?? '') ?>"
                       data-check-duplicate="locations"
                       <?= $isEdit ? 'data-exclude-id="' . $location['id'] . '"' : '' ?>
                       required maxlength="150"
                       placeholder="<?= e(__('location.name_placeholder')) ?>">
                <?php if (hasError('name', $errors ?? [])): ?>
                    <div class="form-error"><?= getError('name', $errors) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">
                    <?= __('form.description') ?>
                </label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="<?= e(__('location.description_placeholder')) ?>"><?= old('description', $location['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= $isEdit ? url('/locations/' . $location['id']) : url('/locations') ?>" class="btn btn-secondary">
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
