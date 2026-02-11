<div class="page-header">
    <h1 class="page-title"><?= __('settings.debug') ?></h1>
</div>

<div class="settings-page">
    <form action="<?= url('/settings/debug') ?>" method="POST">
        <?= csrfField() ?>

        <div class="settings-section">
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

            <input type="hidden" name="debug" value="<?= $debugEnabled ? '0' : '1' ?>">
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn <?= $debugEnabled ? 'btn-danger' : 'btn-primary' ?>">
                <?= $debugEnabled ? __('settings.debug_disable') : __('settings.debug_enable') ?>
            </button>
            <a href="<?= url('/settings') ?>" class="btn btn-secondary"><?= __('action.back') ?></a>
        </div>
    </form>
</div>
