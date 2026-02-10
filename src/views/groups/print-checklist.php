<div class="print-section">
    <div class="print-section-title">Vorbereitungsliste: <?= e($group['name']) ?></div>

    <?php if ($group['description']): ?>
        <p style="margin-bottom: 0.5cm; color: #666;"><?= nl2br(e($group['description'])) ?></p>
    <?php endif; ?>

    <p style="margin-bottom: 1cm;">
        <strong>Materialien gesamt:</strong> <?= $totalMaterials ?>
        <?php if (count($materialsByBox) > 0): ?>
            | <strong>Boxen:</strong> <?= count($materialsByBox) ?>
        <?php endif; ?>
    </p>
</div>

<?php if (empty($materialsByBox) && empty($noBoxMaterials)): ?>
<div class="print-section">
    <p style="font-style: italic; color: #666;">Keine Materialien in dieser Gruppe.</p>
</div>
<?php else: ?>

<?php foreach ($materialsByBox as $boxData): ?>
<div class="print-section" style="page-break-inside: avoid;">
    <div class="print-section-title" style="background: #f5f5f5; padding: 0.3cm; margin-bottom: 0.3cm;">
        Box: <?= e($boxData['box_name']) ?>
        <span style="float: right; font-weight: normal; font-size: 0.9em;">(<?= count($boxData['materials']) ?> Materialien)</span>
    </div>

    <ul class="print-checklist">
        <?php foreach ($boxData['materials'] as $material): ?>
        <li>
            <span style="display: inline-block; width: 2cm; text-align: right; margin-right: 0.5cm;">
                <?= $material['quantity'] ?>×
            </span>
            <strong><?= e($material['name']) ?></strong>
            <?php if ($material['status'] !== 'complete'): ?>
                <span style="color: #c00; font-size: 0.9em; margin-left: 0.5cm;">
                    (<?= __('material.status.' . $material['status']) ?>)
                </span>
            <?php endif; ?>
            <?php if ($material['description']): ?>
                <br><span style="margin-left: 2.5cm; font-size: 0.85em; color: #666;"><?= e(truncate($material['description'], 80)) ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endforeach; ?>

<?php if (!empty($noBoxMaterials)): ?>
<div class="print-section" style="page-break-inside: avoid;">
    <div class="print-section-title" style="background: #fff3cd; padding: 0.3cm; margin-bottom: 0.3cm;">
        Ohne Box
        <span style="float: right; font-weight: normal; font-size: 0.9em;">(<?= count($noBoxMaterials) ?> Materialien)</span>
    </div>

    <ul class="print-checklist">
        <?php foreach ($noBoxMaterials as $material): ?>
        <li>
            <span style="display: inline-block; width: 2cm; text-align: right; margin-right: 0.5cm;">
                <?= $material['quantity'] ?>×
            </span>
            <strong><?= e($material['name']) ?></strong>
            <?php if ($material['status'] !== 'complete'): ?>
                <span style="color: #c00; font-size: 0.9em; margin-left: 0.5cm;">
                    (<?= __('material.status.' . $material['status']) ?>)
                </span>
            <?php endif; ?>
            <?php if ($material['description']): ?>
                <br><span style="margin-left: 2.5cm; font-size: 0.85em; color: #666;"><?= e(truncate($material['description'], 80)) ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php endif; ?>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>

<style<?= cspNonce() ?>>
.print-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
}
.print-checklist li {
    padding: 0.2cm 0;
    border-bottom: 1pt dotted #ddd;
}
.print-checklist li:last-child {
    border-bottom: none;
}
</style>
