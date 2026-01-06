<h2 class="auth-title"><?= __('auth.reset_password') ?></h2>
<p class="auth-subtitle"><?= __('auth.set_new_password') ?></p>

<form action="<?= url('/reset-password') ?>" method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-group">
        <label for="password" class="form-label"><?= __('auth.new_password') ?></label>
        <input type="password" id="password" name="password" class="form-control <?= hasError('password', $errors ?? []) ? 'is-invalid' : '' ?>"
               required minlength="8" autofocus>
        <?php if (hasError('password', $errors ?? [])): ?>
            <div class="form-error"><?= getError('password', $errors) ?></div>
        <?php endif; ?>
        <div class="form-hint"><?= __('validation.password_min_length') ?></div>
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label"><?= __('auth.password_confirm') ?></label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
               required minlength="8">
    </div>

    <button type="submit" class="btn btn-primary w-full" style="padding: var(--spacing-3);">
        <?= __('auth.reset_password') ?>
    </button>

    <div class="text-center mt-4">
        <a href="<?= url('/login') ?>" class="text-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?= __('action.back') ?> <?= __('auth.login') ?>
        </a>
    </div>
</form>
