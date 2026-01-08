<div class="print-section">
    <div class="print-section-title"><?= __('tag.title') ?>: <?= e($tag['name']) ?></div>

    <?php if ($tag['description']): ?>
        <p style="margin-bottom: 1cm; color: #666;"><?= nl2br(e($tag['description'])) ?></p>
    <?php endif; ?>

    <table style="width: 100%; margin-bottom: 0.5cm;">
        <tr>
            <td style="font-weight: bold;"><?= __('game.count') ?>:</td>
            <td><?= $tag['game_count'] ?? count($games) ?> Spiele</td>
        </tr>
    </table>
</div>

<div class="print-section">
    <div class="print-section-title"><?= __('nav.games') ?> (<?= count($games) ?>)</div>

    <?php if (empty($games)): ?>
        <p style="font-style: italic; color: #666;">Keine Spiele mit diesem Thema.</p>
    <?php else: ?>
        <table class="print-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 40%;"><?= __('form.name') ?></th>
                    <th style="width: 20%;"><?= __('game.players') ?></th>
                    <th style="width: 20%;"><?= __('game.duration') ?></th>
                    <th style="width: 20%;">Spielort</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td>
                        <strong><?= e($game['name']) ?></strong>
                        <?php if ($game['description']): ?>
                            <br><span style="font-size: 0.85em; color: #666;"><?= e(truncate($game['description'], 80)) ?></span>
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
                    <td><?= $game['is_outdoor'] ? 'Draußen' : 'Drinnen' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="print-footer">
    <p>Gedruckt am <?= formatDate(date('Y-m-d'), 'd.m.Y') ?></p>
</div>
