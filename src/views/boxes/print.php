<div class="print-section">
    <div class="print-section-title">Box-Informationen</div>

    <table class="print-info-table">
        <tr>
            <td class="print-col-30 print-cell-head"><?= __('form.name') ?></td>
            <td class="print-cell"><?= e($box['name']) ?></td>
        </tr>
        <?php if ($box['number']): ?>
        <tr>
            <td class="print-cell-head"><?= __('box.number') ?></td>
            <td class="print-cell"><?= e($box['number']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($box['location']): ?>
        <tr>
            <td class="print-cell-head"><?= __('box.location') ?></td>
            <td class="print-cell"><?= e($box['location']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($box['description']): ?>
        <tr>
            <td class="print-cell-head"><?= __('form.description') ?></td>
            <td class="print-cell"><?= nl2br(e($box['description'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<div class="print-section">
    <div class="print-section-title"><?= __('box.contents') ?> (<?= count($materials) ?>)</div>

    <?php if (empty($materials)): ?>
        <p class="print-empty"><?= __('box.empty') ?></p>
    <?php else: ?>
        <ul class="print-checklist">
            <?php foreach ($materials as $material): ?>
            <li>
                <strong><?= e($material['name']) ?></strong>
                <?php if ($material['status'] !== 'complete'): ?>
                    <span class="print-alert print-small">
                        (<?= __('material.status.' . $material['status']) ?>)
                    </span>
                <?php endif; ?>
                <?php if ($material['description']): ?>
                    <br><span class="print-small print-muted"><?= e(truncate($material['description'], 100)) ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php if ($box['notes']): ?>
<div class="print-section">
    <div class="print-section-title"><?= __('form.notes') ?></div>
    <p><?= nl2br(e($box['notes'])) ?></p>
</div>
<?php endif; ?>
