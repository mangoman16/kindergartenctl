<h2 class="auth-title"><?= __('auth.login') ?></h2>
<p class="auth-subtitle"><?= __('auth.login_to_continue') ?></p>

<form action="<?= url('/login') ?>" method="POST">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="login" class="form-label"><?= __('auth.username') ?> / <?= __('auth.email') ?></label>
        <input type="text" id="login" name="login" class="form-control <?= hasError('login', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('login') ?>" required autofocus>
        <?php if (hasError('login', $errors ?? [])): ?>
            <div class="form-error"><?= getError('login', $errors) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label"><?= __('auth.password') ?></label>
        <input type="password" id="password" name="password" class="form-control <?= hasError('password', $errors ?? []) ? 'is-invalid' : '' ?>"
               required>
        <?php if (hasError('password', $errors ?? [])): ?>
            <div class="form-error"><?= getError('password', $errors) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input">
            <label for="remember" class="form-check-label"><?= __('auth.remember_me') ?></label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-full" style="padding: var(--spacing-3);">
        <?= __('auth.login') ?>
    </button>

    <div class="text-center mt-4">
        <a href="<?= url('/forgot-password') ?>" class="text-sm">
            <?= __('auth.forgot_password') ?>
        </a>
    </div>
</form>
