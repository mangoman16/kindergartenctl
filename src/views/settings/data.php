<div class="page-header">
    <h1 class="page-title"><?= __('settings.data') ?></h1>
</div>

<div class="settings-page">
    <div class="settings-section">
        <h2 class="settings-section-title"><?= __('settings.storage') ?></h2>

        <div class="storage-stats">
            <div class="storage-stat-item">
                <span class="storage-stat-label">Uploads</span>
                <span class="storage-stat-value"><?= $uploadsSize ?></span>
            </div>
            <div class="storage-stat-item">
                <span class="storage-stat-label">Temp</span>
                <span class="storage-stat-value"><?= $tempSize ?></span>
            </div>
        </div>

        <form action="<?= url('/settings/clear-temp') ?>" method="POST" style="margin-top: var(--spacing-4);">
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

    <div class="settings-actions">
        <a href="<?= url('/settings') ?>" class="btn btn-secondary"><?= __('action.back') ?></a>
    </div>
</div>
