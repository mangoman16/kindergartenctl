<?php if (!empty($breadcrumbs)): ?>
<nav class="breadcrumbs">
    <a href="<?= url('/') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        </svg>
    </a>
    <?php foreach ($breadcrumbs as $index => $crumb): ?>
        <span class="separator">/</span>
        <?php if ($crumb['url'] && $index < count($breadcrumbs) - 1): ?>
            <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
        <?php else: ?>
            <span><?= e($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
