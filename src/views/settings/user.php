<div class="page-header">
    <h1 class="page-title"><?= __('user.settings') ?></h1>
</div>

<div class="grid grid-cols-2 gap-4" style="max-width: 800px;">
    <!-- Profile -->
    <div class="card mb-4" style="grid-column: span 2;">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.profile') ?></h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label"><?= __('form.name') ?></label>
                <input type="text" class="form-control" value="<?= e($user['username'] ?? '') ?>" disabled>
                <div class="form-hint">Benutzername kann nicht geändert werden.</div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('form.email') ?></label>
                <input type="text" class="form-control" value="<?= e($user['email'] ?? '') ?>" disabled>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.change_password') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/user/settings/password') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.current_password') ?></label>
                    <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.new_password') ?></label>
                    <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.confirm_password') ?></label>
                    <input type="password" name="new_password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            </form>
        </div>
    </div>

    <!-- Change Email -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.change_email') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/user/settings/email') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.new_email') ?></label>
                    <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.confirm_password') ?></label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            </form>
        </div>
    </div>
</div>
