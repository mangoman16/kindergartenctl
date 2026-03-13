<div class="page-header">
    <h1 class="page-title"><?= __('user.settings') ?></h1>
</div>

<div class="settings-overview">
    <!-- Profile & Preferences Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <h2><?= __('settings.group_profile') ?></h2>
        </div>
        <div class="settings-group-body">
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('form.name') ?></span>
                    <span class="settings-group-item-desc"><?= __('user.username_readonly') ?></span>
                </div>
                <span class="settings-group-item-value"><?= e($user['username'] ?? '') ?></span>
            </div>
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('form.email') ?></span>
                </div>
                <span class="settings-group-item-value"><?= e($user['email'] ?? '') ?></span>
            </div>
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.language') ?></span>
                </div>
                <form action="<?= url('/user/settings/language') ?>" method="POST" class="settings-inline-form">
                    <?= csrfField() ?>
                    <?php $currentLang = userPreference('language', 'de'); ?>
                    <div class="settings-toggle-pills">
                        <label class="settings-pill <?= $currentLang === 'de' ? 'active' : '' ?>">
                            <input type="radio" name="language" value="de" <?= $currentLang === 'de' ? 'checked' : '' ?>>
                            DE
                        </label>
                        <label class="settings-pill <?= $currentLang === 'en' ? 'active' : '' ?>">
                            <input type="radio" name="language" value="en" <?= $currentLang === 'en' ? 'checked' : '' ?>>
                            EN
                        </label>
                    </div>
                </form>
            </div>
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.dark_mode') ?></span>
                </div>
                <?php $darkPref = userPreference('dark_mode_preference', 'system'); ?>
                <div class="settings-toggle-pills" id="darkModeUserToggle">
                    <label class="settings-pill <?= $darkPref === 'system' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_user" value="system" <?= $darkPref === 'system' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </label>
                    <label class="settings-pill <?= $darkPref === 'light' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_user" value="light" <?= $darkPref === 'light' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>
                    </label>
                    <label class="settings-pill <?= $darkPref === 'dark' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_user" value="dark" <?= $darkPref === 'dark' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <h2><?= __('settings.group_security') ?></h2>
        </div>
        <div class="settings-group-body">
            <div class="settings-group-item-expandable">
                <button type="button" class="settings-group-item settings-expand-trigger" data-target="changePasswordSection">
                    <div class="settings-group-item-info">
                        <span class="settings-group-item-label"><?= __('settings.change_password') ?></span>
                    </div>
                    <svg class="settings-expand-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="settings-expand-content" id="changePasswordSection">
                    <form action="<?= url('/user/settings/password') ?>" method="POST">
                        <?= csrfField() ?>
                        <div class="settings-expand-form">
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
                        </div>
                    </form>
                </div>
            </div>
            <div class="settings-group-item-expandable">
                <button type="button" class="settings-group-item settings-expand-trigger" data-target="changeEmailSection">
                    <div class="settings-group-item-info">
                        <span class="settings-group-item-label"><?= __('settings.change_email') ?></span>
                        <span class="settings-group-item-desc"><?= e($user['email'] ?? '') ?></span>
                    </div>
                    <svg class="settings-expand-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="settings-expand-content" id="changeEmailSection">
                    <form action="<?= url('/user/settings/email') ?>" method="POST">
                        <?= csrfField() ?>
                        <div class="settings-expand-form">
                            <div class="form-group">
                                <label class="form-label"><?= __('settings.new_email') ?></label>
                                <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required autocomplete="email">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?= __('settings.confirm_password') ?></label>
                                <input type="password" name="password" class="form-control" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <h2><?= __('user.management') ?></h2>
        </div>
        <div class="settings-group-body">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                <div class="settings-group-item settings-group-item-inline">
                    <div class="settings-group-item-info" style="flex-direction: row; align-items: center; gap: var(--spacing-3);">
                        <span class="user-list-avatar"><?= strtoupper(mb_substr($u['username'], 0, 1)) ?></span>
                        <div>
                            <span class="settings-group-item-label"><?= e($u['username']) ?></span>
                            <span class="settings-group-item-desc"><?= e($u['email']) ?></span>
                        </div>
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
            <?php endif; ?>

            <div class="settings-group-item-expandable">
                <button type="button" class="settings-group-item settings-expand-trigger" data-target="createUserSection" id="showCreateUserBtn">
                    <div class="settings-group-item-info">
                        <span class="settings-group-item-label" style="color: var(--color-primary);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <?= __('user.create_new') ?>
                        </span>
                    </div>
                    <svg class="settings-expand-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="settings-expand-content" id="createUserSection">
                    <form action="<?= url('/user/settings/create-user') ?>" method="POST">
                        <?= csrfField() ?>
                        <div class="settings-expand-form">
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
                            <button type="submit" class="btn btn-primary"><?= __('user.create_new') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style<?= cspNonce() ?>>
.settings-overview {
    max-width: 640px;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}
.settings-group {
    background: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.settings-group-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-4) var(--spacing-5);
    border-bottom: 1px solid var(--color-gray-100);
    color: var(--color-gray-700);
}
.settings-group-header h2 {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
    color: var(--color-gray-500);
}
.settings-group-body {
    display: flex;
    flex-direction: column;
}
.settings-group-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-4) var(--spacing-5);
    border-bottom: 1px solid var(--color-gray-50);
    text-decoration: none;
    color: inherit;
    transition: background var(--transition-fast);
    background: none;
    border-left: none;
    border-right: none;
    border-top: none;
    width: 100%;
    cursor: default;
    font-family: inherit;
    font-size: inherit;
    text-align: left;
}
a.settings-group-item:hover,
button.settings-group-item:hover {
    background: var(--color-gray-50);
    cursor: pointer;
}
.settings-group-item:last-child,
.settings-group-item-expandable:last-child .settings-group-item {
    border-bottom: none;
}
.settings-group-item-expandable:not(:last-child) {
    border-bottom: 1px solid var(--color-gray-50);
}
.settings-group-item-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.settings-group-item-label {
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-900);
    font-size: var(--font-size-base);
}
.settings-group-item-desc {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}
.settings-group-item-value {
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    font-weight: var(--font-weight-medium);
}
.settings-group-item-arrow {
    color: var(--color-gray-400);
    flex-shrink: 0;
}
.settings-toggle-pills {
    display: flex;
    gap: 2px;
    background: var(--color-gray-100);
    border-radius: var(--radius-lg);
    padding: 2px;
}
.settings-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-500);
    transition: all var(--transition-fast);
    white-space: nowrap;
}
.settings-pill input {
    display: none;
}
.settings-pill:hover {
    color: var(--color-gray-700);
}
.settings-pill.active {
    background: var(--color-white);
    color: var(--color-gray-900);
    box-shadow: var(--shadow-sm);
}
.settings-inline-form {
    margin: 0;
}
.settings-expand-trigger {
    cursor: pointer !important;
}
.settings-expand-chevron {
    color: var(--color-gray-400);
    flex-shrink: 0;
    transition: transform var(--transition-fast);
}
.settings-expand-trigger.expanded .settings-expand-chevron {
    transform: rotate(180deg);
}
.settings-expand-content {
    display: none;
    padding: 0 var(--spacing-5) var(--spacing-5);
}
.settings-expand-content.open {
    display: block;
}
.settings-expand-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    max-width: 400px;
}
.user-list-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
    flex-shrink: 0;
}
</style>

<script<?= cspNonce() ?>>
(function() {
    /* Language pill auto-submit */
    document.querySelectorAll('.settings-toggle-pills:not(#darkModeUserToggle) .settings-pill input').forEach(function(input) {
        input.addEventListener('change', function() {
            var pills = this.closest('.settings-toggle-pills');
            pills.querySelectorAll('.settings-pill').forEach(function(p) { p.classList.remove('active'); });
            this.closest('.settings-pill').classList.add('active');
            this.closest('form').submit();
        });
    });

    /* Dark mode toggle */
    var darkToggle = document.getElementById('darkModeUserToggle');
    if (darkToggle) {
        var html = document.documentElement;
        function getSystemTheme() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        function applyTheme(pref) {
            html.setAttribute('data-dark-mode-pref', pref);
            html.setAttribute('data-theme', pref === 'system' ? getSystemTheme() : pref);
        }
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
                if (html.getAttribute('data-dark-mode-pref') === 'system') {
                    html.setAttribute('data-theme', getSystemTheme());
                }
            });
        }
        darkToggle.querySelectorAll('.settings-pill input').forEach(function(input) {
            input.addEventListener('change', function() {
                var mode = this.value;
                darkToggle.querySelectorAll('.settings-pill').forEach(function(p) { p.classList.remove('active'); });
                this.closest('.settings-pill').classList.add('active');
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
    }

    /* Expandable sections */
    document.querySelectorAll('.settings-expand-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            var targetId = this.dataset.target;
            var content = document.getElementById(targetId);
            if (!content) return;
            var isOpen = content.classList.contains('open');
            // Close all other sections
            document.querySelectorAll('.settings-expand-content.open').forEach(function(el) {
                el.classList.remove('open');
                var parentTrigger = el.closest('.settings-group-item-expandable').querySelector('.settings-expand-trigger');
                if (parentTrigger) parentTrigger.classList.remove('expanded');
            });
            if (!isOpen) {
                content.classList.add('open');
                this.classList.add('expanded');
            }
        });
    });
})();
</script>
