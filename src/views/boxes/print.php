<div class="print-section">
    <div class="print-section-title">Box-Informationen</div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 1cm;">
        <tr>
            <td style="width: 30%; padding: 0.2cm; border-bottom: 1pt solid #ccc; font-weight: bold;"><?= __('form.name') ?></td>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc;"><?= e($box['name']) ?></td>
        </tr>
        <?php if ($box['number']): ?>
        <tr>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc; font-weight: bold;"><?= __('box.number') ?></td>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc;"><?= e($box['number']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($box['location']): ?>
        <tr>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc; font-weight: bold;"><?= __('box.location') ?></td>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc;"><?= e($box['location']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($box['description']): ?>
        <tr>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc; font-weight: bold;"><?= __('form.description') ?></td>
            <td style="padding: 0.2cm; border-bottom: 1pt solid #ccc;"><?= nl2br(e($box['description'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<div class="print-section">
    <div class="print-section-title"><?= __('box.contents') ?> (<?= count($materials) ?>)</div>

    <?php if (empty($materials)): ?>
        <p style="font-style: italic; color: #666;"><?= __('box.empty') ?></p>
    <?php else: ?>
        <ul class="print-checklist">
            <?php foreach ($materials as $material): ?>
            <li>
                <strong><?= e($material['name']) ?></strong>
                <?php if ($material['status'] !== 'complete'): ?>
                    <span style="color: #c00; font-size: 0.9em;">
                        (<?= __('material.status.' . $material['status']) ?>)
                    </span>
                <?php endif; ?>
                <?php if ($material['description']): ?>
                    <br><span style="font-size: 0.9em; color: #666;"><?= e(truncate($material['description'], 100)) ?></span>
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
