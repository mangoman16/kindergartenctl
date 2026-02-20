<h2><?= __('install.email_setup') ?></h2>
<p class="text-muted mb-6"><?= __('install.email_optional') ?></p>

<form action="<?= url('/install/step4') ?>" method="POST">
    <div class="form-row">
        <div class="form-group">
            <label for="smtp_host" class="form-label">
                <?= __('settings.smtp_host') ?>
            </label>
            <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                   value="<?= old('smtp_host') ?>" placeholder="smtp.example.com">
        </div>

        <div class="form-group">
            <label for="smtp_port" class="form-label">
                <?= __('settings.smtp_port') ?>
            </label>
            <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                   value="<?= old('smtp_port', '587') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="smtp_username" class="form-label">
                <?= __('settings.smtp_username') ?>
            </label>
            <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                   value="<?= old('smtp_username') ?>">
        </div>

        <div class="form-group">
            <label for="smtp_password" class="form-label">
                <?= __('settings.smtp_password') ?>
            </label>
            <input type="password" id="smtp_password" name="smtp_password" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="smtp_encryption" class="form-label">
            <?= __('settings.smtp_encryption') ?>
        </label>
        <select id="smtp_encryption" name="smtp_encryption" class="form-control">
            <option value="tls" <?= old('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
            <option value="ssl" <?= old('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="smtp_from_email" class="form-label">
                <?= __('settings.smtp_from_email') ?>
            </label>
            <input type="email" id="smtp_from_email" name="smtp_from_email" class="form-control"
                   value="<?= old('smtp_from_email') ?>" placeholder="noreply@example.com">
        </div>

        <div class="form-group">
            <label for="smtp_from_name" class="form-label">
                <?= __('settings.smtp_from_name') ?>
            </label>
            <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control"
                   value="<?= old('smtp_from_name', 'Kindergarten Spiele Organizer') ?>">
        </div>
    </div>

    <div class="install-footer">
        <a href="<?= url('/install/step3') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?= __('install.back') ?>
        </a>
        <div class="install-footer-right">
            <button type="button" class="btn btn-secondary" id="skipEmailBtn">
                <?= __('install.email_skip') ?>
            </button>
            <button type="submit" class="btn btn-primary">
                <?= __('install.next') ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </div>
    </div>
</form>

<form id="skipEmailForm" action="<?= url('/install/step4/skip') ?>" method="POST" style="display:none;"></form>
<script<?= cspNonce() ?>>
document.getElementById('skipEmailBtn').addEventListener('click', function() {
    document.getElementById('skipEmailForm').submit();
});
</script>
