<div class="print-header">
    <h1><?= e($material['name']) ?></h1>
    <p class="text-muted">Material</p>
</div>

<div class="print-section">
    <div class="flex gap-6">
        <?php if ($material['image_path']): ?>
            <div style="flex-shrink: 0;">
                <img src="<?= upload($material['image_path']) ?>" alt="<?= e($material['name']) ?>"
                     style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover;">
            </div>
        <?php endif; ?>

        <div class="flex-1">
            <table class="print-table">
                <tr>
                    <th style="width: 150px;"><?= __('form.name') ?></th>
                    <td><?= e($material['name']) ?></td>
                </tr>
                <?php if ($material['description']): ?>
                <tr>
                    <th><?= __('form.description') ?></th>
                    <td><?= nl2br(e($material['description'])) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?= __('material.quantity') ?></th>
                    <td><?= $material['quantity'] ?: 'Nicht angegeben' ?></td>
                </tr>
                <tr>
                    <th><?= __('material.type') ?></th>
                    <td><?= $material['is_consumable'] ? 'Verbrauchsmaterial' : 'Ausrüstung' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($games)): ?>
<div class="print-section">
    <h2>Wird verwendet in <?= count($games) ?> <?= pluralize(count($games), 'Spiel', 'Spielen') ?></h2>
    <table class="print-table">
        <thead>
            <tr>
                <th>Spiel</th>
                <th style="width: 80px; text-align: center;">Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
            <tr>
                <td><?= e($game['name']) ?></td>
                <td style="text-align: center;"><?= $game['material_quantity'] ?: '1' ?>×</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>
