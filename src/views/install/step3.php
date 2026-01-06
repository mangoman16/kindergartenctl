<h2><?= __('install.admin_user') ?></h2>
<p class="text-muted mb-6">Erstellen Sie das Administrator-Konto</p>

<form action="<?= url('/install/step3') ?>" method="POST">
    <div class="form-group">
        <label for="username" class="form-label">
            <?= __('install.admin_username') ?> <span class="required">*</span>
        </label>
        <input type="text" id="username" name="username" class="form-control <?= hasError('username', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('username') ?>" required minlength="3" maxlength="50">
        <?php if (hasError('username', $errors ?? [])): ?>
            <div class="form-error"><?= getError('username', $errors) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email" class="form-label">
            <?= __('install.admin_email') ?> <span class="required">*</span>
        </label>
        <input type="email" id="email" name="email" class="form-control <?= hasError('email', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('email') ?>" required>
        <?php if (hasError('email', $errors ?? [])): ?>
            <div class="form-error"><?= getError('email', $errors) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">
            <?= __('install.admin_password') ?> <span class="required">*</span>
        </label>
        <input type="password" id="password" name="password" class="form-control <?= hasError('password', $errors ?? []) ? 'is-invalid' : '' ?>"
               required minlength="8">
        <?php if (hasError('password', $errors ?? [])): ?>
            <div class="form-error"><?= getError('password', $errors) ?></div>
        <?php endif; ?>
        <div class="form-hint"><?= __('validation.password_min_length') ?></div>
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">
            <?= __('install.admin_password_confirm') ?> <span class="required">*</span>
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
               required minlength="8">
    </div>

    <div class="install-footer">
        <a href="<?= url('/install/step2') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?= __('install.back') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?= __('install.next') ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</form>
