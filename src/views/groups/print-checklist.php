<div class="print-section">
    <div class="print-section-title">Vorbereitungsliste: <?= e($group['name']) ?></div>

    <?php if ($group['description']): ?>
        <p class="checklist-lead"><?= nl2br(e($group['description'])) ?></p>
    <?php endif; ?>

    <p class="checklist-summary">
        <strong>Materialien gesamt:</strong> <?= $totalMaterials ?>
        <?php if (count($materialsByBox) > 0): ?>
            | <strong>Boxen:</strong> <?= count($materialsByBox) ?>
        <?php endif; ?>
    </p>
</div>

<?php if (empty($materialsByBox) && empty($noBoxMaterials)): ?>
<div class="print-section">
    <p class="print-empty">Keine Materialien in dieser Gruppe.</p>
</div>
<?php else: ?>

<?php foreach ($materialsByBox as $boxData): ?>
<div class="print-section checklist-section">
    <div class="print-section-title checklist-box-head">
        Box: <?= e($boxData['box_name']) ?>
        <span class="checklist-count">(<?= count($boxData['materials']) ?> Materialien)</span>
    </div>

    <ul class="print-checklist">
        <?php foreach ($boxData['materials'] as $material): ?>
        <li>
            <span class="checklist-qty">
                <?= $material['quantity'] ?>×
            </span>
            <strong><?= e($material['name']) ?></strong>
            <?php if ($material['status'] !== 'complete'): ?>
                <span class="checklist-status">
                    (<?= __('material.status.' . $material['status']) ?>)
                </span>
            <?php endif; ?>
            <?php if ($material['description']): ?>
                <br><span class="checklist-desc"><?= e(truncate($material['description'], 80)) ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endforeach; ?>

<?php if (!empty($noBoxMaterials)): ?>
<div class="print-section checklist-section">
    <div class="print-section-title checklist-box-head-warn">
        Ohne Box
        <span class="checklist-count">(<?= count($noBoxMaterials) ?> Materialien)</span>
    </div>

    <ul class="print-checklist">
        <?php foreach ($noBoxMaterials as $material): ?>
        <li>
            <span class="checklist-qty">
                <?= $material['quantity'] ?>×
            </span>
            <strong><?= e($material['name']) ?></strong>
            <?php if ($material['status'] !== 'complete'): ?>
                <span class="checklist-status">
                    (<?= __('material.status.' . $material['status']) ?>)
                </span>
            <?php endif; ?>
            <?php if ($material['description']): ?>
                <br><span class="checklist-desc"><?= e(truncate($material['description'], 80)) ?></span>
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
.checklist-lead { margin-bottom: 0.5cm; color: #666; }
.checklist-summary { margin-bottom: 1cm; }
.checklist-section { page-break-inside: avoid; }
.checklist-box-head { background: #f5f5f5; padding: 0.3cm; margin-bottom: 0.3cm; }
.checklist-box-head-warn { background: #fff3cd; padding: 0.3cm; margin-bottom: 0.3cm; }
.checklist-count { float: right; font-weight: normal; font-size: 0.9em; }
.checklist-qty { display: inline-block; width: 2cm; text-align: right; margin-right: 0.5cm; }
.checklist-status { color: #c00; font-size: 0.9em; margin-left: 0.5cm; }
.checklist-desc { margin-left: 2.5cm; font-size: 0.85em; color: #666; }
</style>
