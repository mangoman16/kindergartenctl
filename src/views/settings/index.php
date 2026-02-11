<div class="page-header">
    <h1 class="page-title"><?= __('settings.title') ?></h1>
</div>

<div class="grid grid-cols-2 gap-4">
    <!-- Language -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.language') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/language') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <select name="language" class="form-control" onchange="this.form.submit()">
                        <option value="de" <?= userPreference('language', 'de') === 'de' ? 'selected' : '' ?>>Deutsch</option>
                        <option value="en" <?= userPreference('language', 'de') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Customization -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.customization') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/customization') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.theme_color') ?></label>
                    <div class="flex gap-2 flex-wrap">
                        <?php
                        $colors = ['#4F46E5', '#EC4899', '#F59E0B', '#22C55E', '#3B82F6', '#8B5CF6', '#EF4444', '#14B8A6'];
                        $currentColor = userPreference('theme_color', '#4F46E5');
                        foreach ($colors as $color): ?>
                            <label style="cursor: pointer;">
                                <input type="radio" name="theme_color" value="<?= $color ?>" <?= $currentColor === $color ? 'checked' : '' ?> style="display: none;">
                                <span style="display: block; width: 36px; height: 36px; border-radius: 50%; background: <?= $color ?>; border: 3px solid <?= $currentColor === $color ? 'var(--color-gray-800)' : 'transparent' ?>; transition: border-color 0.2s;"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.theme_pattern') ?></label>
                    <select name="theme_pattern" class="form-control">
                        <option value="none" <?= userPreference('theme_pattern', 'none') === 'none' ? 'selected' : '' ?>><?= __('settings.pattern_none') ?></option>
                        <option value="dots" <?= userPreference('theme_pattern', 'none') === 'dots' ? 'selected' : '' ?>><?= __('settings.pattern_dots') ?></option>
                        <option value="stars" <?= userPreference('theme_pattern', 'none') === 'stars' ? 'selected' : '' ?>><?= __('settings.pattern_stars') ?></option>
                        <option value="hearts" <?= userPreference('theme_pattern', 'none') === 'hearts' ? 'selected' : '' ?>><?= __('settings.pattern_hearts') ?></option>
                        <option value="clouds" <?= userPreference('theme_pattern', 'none') === 'clouds' ? 'selected' : '' ?>><?= __('settings.pattern_clouds') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            </form>
        </div>
    </div>

    <!-- SMTP Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.email') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/smtp') ?>" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_host') ?></label>
                    <input type="text" name="smtp_host" class="form-control" value="<?= e($smtp['host'] ?? '') ?>" placeholder="smtp.example.com">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label"><?= __('settings.smtp_port') ?></label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= e($smtp['port'] ?? '587') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('settings.smtp_encryption') ?></label>
                        <select name="smtp_encryption" class="form-control">
                            <option value="tls" <?= ($smtp['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= empty($smtp['encryption'] ?? '') ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_username') ?></label>
                    <input type="text" name="smtp_username" class="form-control" value="<?= e($smtp['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= __('settings.smtp_password') ?></label>
                    <input type="password" name="smtp_password" class="form-control" placeholder="<?= !empty($smtp['password']) ? '********' : '' ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label"><?= __('settings.smtp_from_email') ?></label>
                        <input type="email" name="smtp_from_email" class="form-control" value="<?= e($smtp['from_email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('settings.smtp_from_name') ?></label>
                        <input type="text" name="smtp_from_name" class="form-control" value="<?= e($smtp['from_name'] ?? '') ?>">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
                    <a href="<?= url('/settings/smtp/test') ?>" class="btn btn-secondary"><?= __('settings.smtp_test') ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Debug Mode -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.debug') ?></h2>
        </div>
        <div class="card-body">
            <p class="text-muted text-sm mb-4">Aktiviert die Anzeige von PHP-Fehlern und SQL-Fehlermeldungen. Nur für Entwicklung!</p>
            <form action="<?= url('/settings/debug') ?>" method="POST">
                <?= csrfField() ?>
                <?php $debugEnabled = file_exists(ROOT_PATH . '/storage/debug.flag'); ?>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="debug" value="1" <?= $debugEnabled ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span><?= $debugEnabled ? __('settings.debug_enabled') : __('settings.debug') ?></span>
                </label>
            </form>
        </div>
    </div>

    <!-- Help -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('help.title') ?></h2>
        </div>
        <div class="card-body">
            <p class="text-muted text-sm mb-4"><?= __('help.wizard_welcome') ?></p>
            <a href="<?= url('/settings/help') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <?= __('help.wizard_title') ?>
            </a>
        </div>
    </div>

    <!-- Data Management -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.data') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/clear-temp') ?>" method="POST">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <?= __('settings.clear_temp') ?>
                </button>
            </form>
        </div>
    </div>
</div>
