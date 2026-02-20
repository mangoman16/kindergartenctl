<div class="page-header">
    <h1 class="page-title"><?= __('settings.title') ?></h1>
</div>

<div class="settings-menu">
    <a href="<?= url('/settings/customization') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #EEF2FF; color: #4F46E5;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="13.5" cy="6.5" r="2.5"></circle>
                <circle cx="17.5" cy="15.5" r="2.5"></circle>
                <circle cx="8.5" cy="15.5" r="2.5"></circle>
                <path d="M3 19.5V4.5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v15a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('settings.customization') ?></span>
            <span class="settings-menu-desc"><?= __('settings.theme_color') ?>, <?= __('settings.theme_pattern') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/settings/language') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #F0FDF4; color: #22C55E;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('settings.language') ?></span>
            <span class="settings-menu-desc"><?= userPreference('language', 'de') === 'de' ? 'Deutsch' : 'English' ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/settings/email') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #FEF3C7; color: #D97706;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('settings.email') ?></span>
            <span class="settings-menu-desc"><?= !empty($smtp['host']) ? e($smtp['host']) : __('form.optional') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/settings/debug') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #FEF2F2; color: #EF4444;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                <path d="M12 8v4"></path>
                <path d="M12 16h.01"></path>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('settings.debug') ?></span>
            <?php $debugEnabled = file_exists(ROOT_PATH . '/storage/debug.flag'); ?>
            <span class="settings-menu-desc"><?= $debugEnabled ? __('settings.debug_enabled') : __('settings.debug_disabled') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/settings/data') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #EFF6FF; color: #3B82F6;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('settings.data') ?></span>
            <span class="settings-menu-desc"><?= __('settings.clear_temp') ?>, <?= __('settings.ip_bans') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/changelog') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #FFF7ED; color: #EA580C;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('nav.changelog') ?></span>
            <span class="settings-menu-desc"><?= __('changelog.title') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>

    <a href="<?= url('/settings/help') ?>" class="settings-menu-item">
        <span class="settings-menu-icon" style="background: #F5F3FF; color: #8B5CF6;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </span>
        <span class="settings-menu-text">
            <span class="settings-menu-label"><?= __('help.title') ?></span>
            <span class="settings-menu-desc"><?= __('help.wizard_welcome') ?></span>
        </span>
        <svg class="settings-menu-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </a>
</div>
