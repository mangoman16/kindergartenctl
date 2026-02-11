<div class="page-header">
    <h1 class="page-title"><?= __('settings.email') ?></h1>
</div>

<div class="settings-page">
    <form action="<?= url('/settings/smtp') ?>" method="POST">
        <?= csrfField() ?>

        <div class="settings-section">
            <div class="form-group">
                <label class="form-label"><?= __('settings.smtp_host') ?></label>
                <input type="text" name="smtp_host" class="form-control" value="<?= e($smtp['host'] ?? '') ?>" placeholder="smtp.example.com">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_port') ?></label>
                    <input type="number" name="smtp_port" class="form-control" value="<?= e($smtp['port'] ?? '587') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_encryption') ?></label>
                    <select name="smtp_encryption" class="form-control">
                        <option value="tls" <?= ($smtp['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= empty($smtp['encryption'] ?? '') ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('settings.smtp_username') ?></label>
                <input type="text" name="smtp_username" class="form-control" value="<?= e($smtp['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('settings.smtp_password') ?></label>
                <input type="password" name="smtp_password" class="form-control" placeholder="<?= !empty($smtp['password']) ? '********' : '' ?>">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_from_email') ?></label>
                    <input type="email" name="smtp_from_email" class="form-control" value="<?= e($smtp['from_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_from_name') ?></label>
                    <input type="text" name="smtp_from_name" class="form-control" value="<?= e($smtp['from_name'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            <a href="<?= url('/settings') ?>" class="btn btn-secondary"><?= __('action.back') ?></a>
        </div>
    </form>

    <div class="settings-section" style="margin-top: var(--spacing-6);">
        <h2 class="settings-section-title"><?= __('settings.smtp_test') ?></h2>
        <form action="<?= url('/settings/smtp/test') ?>" method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label"><?= __('settings.smtp_test_email') ?></label>
                <div class="flex gap-2">
                    <input type="email" name="test_email" class="form-control" placeholder="test@example.com" required>
                    <button type="submit" class="btn btn-secondary" style="white-space: nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                        <?= __('settings.smtp_test') ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
