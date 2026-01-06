<div class="print-header">
    <h1><?= e($game['name']) ?></h1>
    <?php if ($game['box_name']): ?>
        <p class="text-muted">Box: <?= e($game['box_name']) ?> <?= $game['box_label'] ? '(' . e($game['box_label']) . ')' : '' ?></p>
    <?php endif; ?>
</div>

<div class="print-section">
    <div class="flex gap-6">
        <?php if ($game['image_path']): ?>
            <div style="flex-shrink: 0;">
                <img src="<?= upload($game['image_path']) ?>" alt="<?= e($game['name']) ?>"
                     style="width: 150px; height: 150px; border-radius: 8px; object-fit: cover;">
            </div>
        <?php endif; ?>

        <div class="flex-1">
            <table class="print-table">
                <tr>
                    <th style="width: 150px;"><?= __('game.age_group') ?></th>
                    <td><?= $game['category_name'] ? e($game['category_name']) : 'Alle' ?></td>
                </tr>
                <tr>
                    <th><?= __('game.players') ?></th>
                    <td>
                        <?php if ($game['min_players'] && $game['max_players']): ?>
                            <?= $game['min_players'] ?> - <?= $game['max_players'] ?> Spieler
                        <?php elseif ($game['min_players']): ?>
                            ab <?= $game['min_players'] ?> Spieler
                        <?php elseif ($game['max_players']): ?>
                            bis <?= $game['max_players'] ?> Spieler
                        <?php else: ?>
                            Beliebig
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __('game.duration') ?></th>
                    <td><?= $game['duration_minutes'] ? $game['duration_minutes'] . ' Minuten' : 'Variabel' ?></td>
                </tr>
                <tr>
                    <th>Spielort</th>
                    <td><?= $game['is_outdoor'] ? 'Draußen / Outdoor' : 'Drinnen' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php if ($game['description']): ?>
<div class="print-section">
    <h2><?= __('form.description') ?></h2>
    <p><?= nl2br(e($game['description'])) ?></p>
</div>
<?php endif; ?>

<?php if ($game['instructions']): ?>
<div class="print-section">
    <h2><?= __('game.instructions') ?></h2>
    <div class="instructions-text">
        <?= nl2br(e($game['instructions'])) ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($game['materials'])): ?>
<div class="print-section">
    <h2><?= __('nav.materials') ?></h2>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 60px;">Anzahl</th>
                <th>Material</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($game['materials'] as $material): ?>
            <tr>
                <td style="text-align: center;"><?= $material['quantity'] ?>×</td>
                <td><?= e($material['name']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($game['tags'])): ?>
<div class="print-section">
    <h2><?= __('nav.tags') ?></h2>
    <p>
        <?php foreach ($game['tags'] as $i => $tag): ?>
            <?= $i > 0 ? ', ' : '' ?><?= e($tag['name']) ?>
        <?php endforeach; ?>
    </p>
</div>
<?php endif; ?>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>

<style>
.instructions-text {
    white-space: pre-wrap;
    line-height: 1.6;
}
</style>
