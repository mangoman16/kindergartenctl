<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $type => $message): ?>
        <div class="alert alert-<?= e($type) ?>">
            <span><?= e($message) ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
