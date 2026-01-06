<h2 class="auth-title"><?= __('auth.forgot_password') ?></h2>
<p class="auth-subtitle">Geben Sie Ihre E-Mail-Adresse ein, um einen Link zum Zurücksetzen zu erhalten.</p>

<form action="<?= url('/forgot-password') ?>" method="POST">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="email" class="form-label"><?= __('auth.email') ?></label>
        <input type="email" id="email" name="email" class="form-control <?= hasError('email', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('email') ?>" required autofocus>
        <?php if (hasError('email', $errors ?? [])): ?>
            <div class="form-error"><?= getError('email', $errors) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-full" style="padding: var(--spacing-3);">
        <?= __('auth.send_reset_link') ?>
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
