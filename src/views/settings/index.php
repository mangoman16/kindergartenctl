<div class="page-header">
    <h1 class="page-title"><?= __('settings.title') ?></h1>
</div>

<div class="settings-overview">
    <!-- Appearance Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="13.5" cy="6.5" r="2.5"></circle>
                <circle cx="17.5" cy="15.5" r="2.5"></circle>
                <circle cx="8.5" cy="15.5" r="2.5"></circle>
            </svg>
            <h2><?= __('settings.group_appearance') ?></h2>
        </div>
        <div class="settings-group-body">
            <a href="<?= url('/settings/customization') ?>" class="settings-group-item">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.customization') ?></span>
                    <span class="settings-group-item-desc"><?= __('settings.theme_color') ?>, <?= __('settings.theme_pattern') ?>, <?= __('settings.font_size') ?></span>
                </div>
                <svg class="settings-group-item-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.language') ?></span>
                    <span class="settings-group-item-desc"><?= userPreference('language', 'de') === 'de' ? 'Deutsch' : 'English' ?></span>
                </div>
                <form action="<?= url('/settings/language') ?>" method="POST" class="settings-inline-form">
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
                <div class="settings-toggle-pills" id="darkModeOverviewToggle">
                    <label class="settings-pill <?= $darkPref === 'system' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_overview" value="system" <?= $darkPref === 'system' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </label>
                    <label class="settings-pill <?= $darkPref === 'light' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_overview" value="light" <?= $darkPref === 'light' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line></svg>
                    </label>
                    <label class="settings-pill <?= $darkPref === 'dark' ? 'active' : '' ?>">
                        <input type="radio" name="dark_mode_overview" value="dark" <?= $darkPref === 'dark' ? 'checked' : '' ?>>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- System Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09c-.658.003-1.25.396-1.51 1z"></path>
            </svg>
            <h2><?= __('settings.group_system') ?></h2>
        </div>
        <div class="settings-group-body">
            <a href="<?= url('/settings/email') ?>" class="settings-group-item">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.email') ?></span>
                    <span class="settings-group-item-desc"><?= !empty($smtp['host']) ? e($smtp['host']) : __('settings.smtp_not_configured_short') ?></span>
                </div>
                <svg class="settings-group-item-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="<?= url('/settings/data') ?>" class="settings-group-item">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.data') ?></span>
                    <span class="settings-group-item-desc"><?= __('settings.storage') ?>, <?= __('settings.ip_bans') ?></span>
                </div>
                <svg class="settings-group-item-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <div class="settings-group-item settings-group-item-inline">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('settings.debug') ?></span>
                    <?php $debugEnabled = file_exists(ROOT_PATH . '/storage/debug.flag'); ?>
                    <span class="settings-group-item-desc"><?= $debugEnabled ? __('settings.debug_enabled') : __('settings.debug_disabled') ?></span>
                </div>
                <form action="<?= url('/settings/debug') ?>" method="POST" class="settings-inline-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="debug" value="<?= $debugEnabled ? '0' : '1' ?>">
                    <label class="settings-switch">
                        <input type="checkbox" class="js-auto-submit" <?= $debugEnabled ? 'checked' : '' ?>>
                        <span class="settings-switch-slider"></span>
                    </label>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Group -->
    <div class="settings-group">
        <div class="settings-group-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <h2><?= __('settings.group_activity') ?></h2>
        </div>
        <div class="settings-group-body">
            <a href="<?= url('/changelog') ?>" class="settings-group-item">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('nav.changelog') ?></span>
                    <span class="settings-group-item-desc"><?= __('changelog.title') ?></span>
                </div>
                <svg class="settings-group-item-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="<?= url('/settings/help') ?>" class="settings-group-item">
                <div class="settings-group-item-info">
                    <span class="settings-group-item-label"><?= __('nav.help') ?></span>
                    <span class="settings-group-item-desc"><?= __('settings.help_wizard_desc') ?></span>
                </div>
                <svg class="settings-group-item-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
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
}
a.settings-group-item:hover {
    background: var(--color-gray-50);
}
.settings-group-item:last-child {
    border-bottom: none;
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
.settings-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.settings-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.settings-switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--color-gray-300);
    transition: all var(--transition-fast);
    border-radius: 12px;
}
.settings-switch-slider::before {
    content: "";
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: all var(--transition-fast);
    border-radius: 50%;
}
.settings-switch input:checked + .settings-switch-slider {
    background-color: var(--color-primary);
}
.settings-switch input:checked + .settings-switch-slider::before {
    transform: translateX(20px);
}
.settings-inline-form {
    margin: 0;
}
</style>

<script<?= cspNonce() ?>>
(function() {
    /* Language pill auto-submit */
    document.querySelectorAll('.settings-toggle-pills:not(#darkModeOverviewToggle) .settings-pill input').forEach(function(input) {
        input.addEventListener('change', function() {
            var pills = this.closest('.settings-toggle-pills');
            pills.querySelectorAll('.settings-pill').forEach(function(p) { p.classList.remove('active'); });
            this.closest('.settings-pill').classList.add('active');
            this.closest('form').submit();
        });
    });

    /* Dark mode toggle */
    var darkToggle = document.getElementById('darkModeOverviewToggle');
    if (darkToggle) {
        var html = document.documentElement;
        function getSystemTheme() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        function applyTheme(pref) {
            html.setAttribute('data-dark-mode-pref', pref);
            html.setAttribute('data-theme', pref === 'system' ? getSystemTheme() : pref);
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
})();
</script>
