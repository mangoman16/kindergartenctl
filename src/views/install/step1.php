<h2><?= __('install.requirements') ?></h2>
<p class="text-muted mb-6">Überprüfung der Systemvoraussetzungen</p>

<ul class="requirements-list">
    <?php foreach ($requirements as $req): ?>
    <li>
        <span><?= e($req['name']) ?></span>
        <span class="requirement-check <?= $req['passed'] ? 'passed' : 'failed' ?>">
            <?php if ($req['passed']): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            <?php else: ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            <?php endif; ?>
            <?= e($req['current']) ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($allPassed): ?>
<div class="alert alert-success mt-6">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span><?= __('install.all_requirements_met') ?></span>
</div>
<?php else: ?>
<div class="alert alert-danger mt-6">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <span><?= __('install.requirements_not_met') ?></span>
</div>
<?php endif; ?>

<div class="install-footer">
    <div></div>
    <?php if ($allPassed): ?>
    <a href="<?= url('/install/step2') ?>" class="btn btn-primary">
        <?= __('install.next') ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </a>
    <?php else: ?>
    <button class="btn btn-secondary" disabled><?= __('install.next') ?></button>
    <?php endif; ?>
</div>
