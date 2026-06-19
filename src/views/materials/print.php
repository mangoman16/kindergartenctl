<div class="print-header">
    <h1><?= e($material['name']) ?></h1>
    <p class="text-muted">Material</p>
</div>

<div class="print-section">
    <div class="flex gap-6">
        <?php if ($material['image_path']): ?>
            <div class="print-no-shrink">
                <img src="<?= upload($material['image_path']) ?>" alt="<?= e($material['name']) ?>"
                     class="print-img print-img-120">
            </div>
        <?php endif; ?>

        <div class="flex-1">
            <table class="print-table">
                <tr>
                    <th class="print-col-150px"><?= __('form.name') ?></th>
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
                <th class="print-col-80px print-center">Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
            <tr>
                <td><?= e($game['name']) ?></td>
                <td class="print-center"><?= $game['material_quantity'] ?: '1' ?>×</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>
