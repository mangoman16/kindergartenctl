<div class="text-center">
    <div style="width: 80px; height: 80px; background: var(--color-success-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-6);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--color-success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
    </div>

    <h2><?= __('install.complete_title') ?></h2>
    <p class="text-muted mb-6"><?= __('install.complete_text') ?></p>

    <a href="<?= url('/login') ?>" class="btn btn-primary btn-lg">
        <?= __('install.go_to_login') ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </a>
</div>
