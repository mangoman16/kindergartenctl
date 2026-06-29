<div class="print-section">
    <div class="print-section-title"><?= __('group.title') ?>: <?= e($group['name']) ?></div>

    <?php if ($group['description']): ?>
        <p class="print-lead"><?= nl2br(e($group['description'])) ?></p>
    <?php endif; ?>

    <table class="print-kv">
        <tr>
            <td class="print-strong"><?= __('nav.games') ?>:</td>
            <td><?= $group['game_count'] ?? count($games) ?></td>
            <td class="print-strong"><?= __('nav.materials') ?>:</td>
            <td><?= $group['material_count'] ?? count($materials) ?></td>
        </tr>
    </table>
</div>

<?php if (!empty($games)): ?>
<div class="print-section">
    <div class="print-section-title"><?= __('nav.games') ?> (<?= count($games) ?>)</div>

    <table class="print-table print-w-full">
        <thead>
            <tr>
                <th class="print-col-40"><?= __('form.name') ?></th>
                <th class="print-col-20"><?= __('game.players') ?></th>
                <th class="print-col-20"><?= __('game.duration') ?></th>
                <th class="print-col-20">Box</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
            <tr>
                <td>
                    <strong><?= e($game['name']) ?></strong>
                    <?php if ($game['description']): ?>
                        <br><span class="print-desc"><?= e(truncate($game['description'], 80)) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($game['min_players'] && $game['max_players']): ?>
                        <?= $game['min_players'] ?> - <?= $game['max_players'] ?>
                    <?php elseif ($game['min_players']): ?>
                        ab <?= $game['min_players'] ?>
                    <?php elseif ($game['max_players']): ?>
                        bis <?= $game['max_players'] ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= $game['duration_minutes'] ? $game['duration_minutes'] . ' Min.' : '-' ?></td>
                <td>
                    <?php if ($game['box_name']): ?>
                        <?= e($game['box_name']) ?>
                        <?= $game['box_label'] ? ' (' . e($game['box_label']) . ')' : '' ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($materials)): ?>
<div class="print-section">
    <div class="print-section-title"><?= __('nav.materials') ?> (<?= count($materials) ?>)</div>

    <table class="print-table print-w-full">
        <thead>
            <tr>
                <th class="print-col-15">Anzahl</th>
                <th class="print-col-35"><?= __('form.name') ?></th>
                <th class="print-col-25">Status</th>
                <th class="print-col-25">Box</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materials as $material): ?>
            <tr>
                <td class="print-center"><?= $material['quantity'] ?>×</td>
                <td>
                    <strong><?= e($material['name']) ?></strong>
                    <?php if ($material['description']): ?>
                        <br><span class="print-desc"><?= e(truncate($material['description'], 60)) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($material['status'] !== 'complete'): ?>
                        <span class="print-danger"><?= __('material.status.' . $material['status']) ?></span>
                    <?php else: ?>
                        <?= __('material.status.complete') ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($material['box_id']): ?>
                        <?= e($material['box_name'] ?? '-') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (empty($games) && empty($materials)): ?>
<div class="print-section">
    <p class="print-empty">Diese Gruppe enthält keine Spiele oder Materialien.</p>
</div>
<?php endif; ?>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>
