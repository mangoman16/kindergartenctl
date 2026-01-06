<?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
<nav class="pagination">
    <ul class="pagination-list">
        <?php if ($pagination['page'] > 1): ?>
            <li>
                <a href="<?= e($paginationUrl . '?page=' . ($pagination['page'] - 1)) ?>" class="pagination-link">
                    &laquo; <?= __('action.back') ?>
                </a>
            </li>
        <?php endif; ?>

        <?php
        $start = max(1, $pagination['page'] - 2);
        $end = min($pagination['totalPages'], $pagination['page'] + 2);

        if ($start > 1): ?>
            <li>
                <a href="<?= e($paginationUrl . '?page=1') ?>" class="pagination-link">1</a>
            </li>
            <?php if ($start > 2): ?>
                <li class="pagination-ellipsis">...</li>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li>
                <a href="<?= e($paginationUrl . '?page=' . $i) ?>"
                   class="pagination-link <?= $i === $pagination['page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($end < $pagination['totalPages']): ?>
            <?php if ($end < $pagination['totalPages'] - 1): ?>
                <li class="pagination-ellipsis">...</li>
            <?php endif; ?>
            <li>
                <a href="<?= e($paginationUrl . '?page=' . $pagination['totalPages']) ?>" class="pagination-link">
                    <?= $pagination['totalPages'] ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
            <li>
                <a href="<?= e($paginationUrl . '?page=' . ($pagination['page'] + 1)) ?>" class="pagination-link">
                    <?= __('action.next') ?> &raquo;
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
