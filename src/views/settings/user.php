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
                <div class="form-hint"><?= __('user.username_readonly') ?></div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('form.email') ?></label>
                <input type="text" class="form-control" value="<?= e($user['email'] ?? '') ?>" disabled>
            </div>
        </div>
    </div>

    <!-- Language Selection -->
    <div class="card mb-4" style="grid-column: span 2;">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.language') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/user/settings/language') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.language') ?></label>
                    <select name="language" class="form-control">
                        <option value="de" <?= userPreference('language', 'de') === 'de' ? 'selected' : '' ?>><?= __('settings.language_de') ?></option>
                        <option value="en" <?= userPreference('language', 'de') === 'en' ? 'selected' : '' ?>><?= __('settings.language_en') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            </form>
        </div>
    </div>

    <!-- Dark Mode -->
    <div class="card mb-4" style="grid-column: span 2;">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.dark_mode') ?></h2>
        </div>
        <div class="card-body">
            <?php $darkPref = userPreference('dark_mode_preference', 'system'); ?>
            <div class="dark-mode-options" id="darkModeOptions">
                <button type="button" class="dark-mode-option <?= $darkPref === 'system' ? 'active' : '' ?>" data-mode="system">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    <span class="dark-mode-option-label"><?= __('settings.dark_mode_system') ?></span>
                </button>
                <button type="button" class="dark-mode-option <?= $darkPref === 'light' ? 'active' : '' ?>" data-mode="light">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <span class="dark-mode-option-label"><?= __('settings.dark_mode_light') ?></span>
                </button>
                <button type="button" class="dark-mode-option <?= $darkPref === 'dark' ? 'active' : '' ?>" data-mode="dark">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    <span class="dark-mode-option-label"><?= __('settings.dark_mode_dark') ?></span>
                </button>
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

<!-- User Management -->
<div style="max-width: 800px;">
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('user.management') ?></h2>
            <button type="button" class="btn btn-primary btn-sm" id="showCreateUserForm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('user.create_new') ?>
            </button>
        </div>
        <div class="card-body">
            <!-- Existing Users List -->
            <?php if (!empty($users)): ?>
            <div class="user-list">
                <?php foreach ($users as $u): ?>
                <div class="user-list-item">
                    <div class="user-list-avatar"><?= strtoupper(mb_substr($u['username'], 0, 1)) ?></div>
                    <div class="user-list-info">
                        <div class="font-medium"><?= e($u['username']) ?></div>
                        <div class="text-sm text-muted"><?= e($u['email']) ?></div>
                    </div>
                    <?php if ((int)$u['id'] !== Auth::id()): ?>
                    <form action="<?= url('/user/settings/delete-user') ?>" method="POST" style="margin:0;" onsubmit="return confirm('<?= __('user.confirm_delete') ?>');">
                        <?= csrfField() ?>
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><?= __('action.delete') ?></button>
                    </form>
                    <?php else: ?>
                    <span class="badge badge-primary"><?= __('user.current') ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Create User Form (hidden by default) -->
            <div id="createUserForm" style="display: none; margin-top: 1.5rem;">
                <form action="<?= url('/user/settings/create-user') ?>" method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label"><?= __('auth.username') ?> <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" required minlength="3" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('auth.email') ?> <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('auth.password') ?> <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('auth.password_confirm') ?> <span class="required">*</span></label>
                        <input type="password" name="password_confirm" class="form-control" required minlength="8">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= __('user.create_new') ?></button>
                        <button type="button" class="btn btn-secondary" id="cancelCreateUser"><?= __('action.cancel') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style<?= cspNonce() ?>>
.dark-mode-options {
    display: flex;
    gap: 0.75rem;
}
.dark-mode-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 2px solid var(--color-gray-200);
    border-radius: var(--radius-xl);
    background: var(--color-gray-50);
    cursor: pointer;
    transition: all var(--transition-fast);
    color: var(--color-gray-500);
}
.dark-mode-option:hover {
    border-color: var(--color-gray-300);
    color: var(--color-gray-700);
}
.dark-mode-option.active {
    border-color: var(--color-primary);
    background: var(--color-primary-bg);
    color: var(--color-primary);
}
.dark-mode-option-label {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}
.user-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.user-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--color-gray-50);
    border-radius: var(--radius-lg);
}
.user-list-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}
.user-list-info {
    flex: 1;
    min-width: 0;
}
</style>

<script<?= cspNonce() ?>>
(function() {
    const showBtn = document.getElementById('showCreateUserForm');
    const form = document.getElementById('createUserForm');
    const cancelBtn = document.getElementById('cancelCreateUser');

    if (showBtn && form) {
        showBtn.addEventListener('click', function() {
            form.style.display = 'block';
            showBtn.style.display = 'none';
        });
    }
    if (cancelBtn && form && showBtn) {
        cancelBtn.addEventListener('click', function() {
            form.style.display = 'none';
            showBtn.style.display = '';
        });
    }
})();

/* Dark mode toggle */
(function() {
    var options = document.getElementById('darkModeOptions');
    if (!options) return;
    var html = document.documentElement;

    function getSystemTheme() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(pref) {
        html.setAttribute('data-dark-mode-pref', pref);
        if (pref === 'system') {
            html.setAttribute('data-theme', getSystemTheme());
        } else {
            html.setAttribute('data-theme', pref);
        }
    }

    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            if (html.getAttribute('data-dark-mode-pref') === 'system') {
                html.setAttribute('data-theme', getSystemTheme());
            }
        });
    }

    options.querySelectorAll('.dark-mode-option').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var mode = this.dataset.mode;
            options.querySelectorAll('.dark-mode-option').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            applyTheme(mode);
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                fetch('<?= url('/settings/dark-mode') ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrfMeta.content},
                    body: 'csrf_token=' + encodeURIComponent(csrfMeta.content) + '&dark_mode_preference=' + encodeURIComponent(mode)
                });
            }
        });
    });
})();
</script>
