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
</div>
