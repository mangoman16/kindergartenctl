<div class="page-header">
    <h1 class="page-title"><?= __('settings.system') ?></h1>
</div>

<div class="settings-page">
    <!-- Debug Mode -->
    <div class="settings-section">
        <h2 class="settings-section-title"><?= __('settings.debug') ?></h2>

        <div class="debug-info-card">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                <path d="M12 8v4"></path>
                <path d="M12 16h.01"></path>
            </svg>
            <p><?= __('settings.debug_description') ?></p>
        </div>

        <?php $debugEnabled = file_exists(ROOT_PATH . '/storage/debug.flag'); ?>

        <div class="debug-status <?= $debugEnabled ? 'debug-status-on' : 'debug-status-off' ?>">
            <span class="debug-status-dot"></span>
            <span><?= $debugEnabled ? __('settings.debug_enabled') : __('settings.debug_disabled') ?></span>
        </div>

        <form action="<?= url('/settings/debug') ?>" method="POST" style="margin-top: var(--spacing-4);">
            <?= csrfField() ?>
            <input type="hidden" name="debug" value="<?= $debugEnabled ? '0' : '1' ?>">
            <button type="submit" class="btn <?= $debugEnabled ? 'btn-danger' : 'btn-primary' ?>">
                <?= $debugEnabled ? __('settings.debug_disable') : __('settings.debug_enable') ?>
            </button>
        </form>
    </div>

    <!-- IP Bans -->
    <div class="settings-section">
        <h2 class="settings-section-title"><?= __('settings.ip_bans') ?></h2>

        <?php if (empty($bans)): ?>
            <p class="text-muted"><?= __('settings.no_banned_ips') ?></p>
        <?php else: ?>
            <div class="ban-list">
                <?php foreach ($bans as $ban): ?>
                    <div class="ban-list-item">
                        <div>
                            <span class="ban-list-ip"><?= e($ban['ip_address']) ?></span>
                            <span class="text-muted text-sm"><?= e($ban['reason'] ?? '') ?></span>
                        </div>
                        <form action="<?= url('/settings/unban') ?>" method="POST" style="margin: 0;">
                            <?= csrfField() ?>
                            <input type="hidden" name="ip" value="<?= e($ban['ip_address']) ?>">
                            <button type="submit" class="btn btn-sm btn-secondary"><?= __('settings.unban') ?></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('/settings/ban') ?>" method="POST" style="margin-top: var(--spacing-4);">
            <?= csrfField() ?>
            <div class="flex gap-2">
                <input type="text" name="ip" class="form-control" placeholder="192.168.1.1" required>
                <input type="text" name="reason" class="form-control" placeholder="<?= __('form.notes') ?>">
                <button type="submit" class="btn btn-secondary" style="white-space: nowrap;"><?= __('settings.ban_ip') ?></button>
            </div>
        </form>
    </div>
</div>
