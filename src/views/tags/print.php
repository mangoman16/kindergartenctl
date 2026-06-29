<div class="print-section">
    <div class="print-section-title"><?= __('tag.title') ?>: <?= e($tag['name']) ?></div>

    <?php if ($tag['description']): ?>
        <p class="print-lead"><?= nl2br(e($tag['description'])) ?></p>
    <?php endif; ?>

    <table class="print-kv">
        <tr>
            <td class="print-strong"><?= __('game.count') ?>:</td>
            <td><?= $tag['game_count'] ?? count($games) ?> Spiele</td>
        </tr>
    </table>
</div>

<div class="print-section">
    <div class="print-section-title"><?= __('nav.games') ?> (<?= count($games) ?>)</div>

    <?php if (empty($games)): ?>
        <p class="print-empty">Keine Spiele mit diesem Thema.</p>
    <?php else: ?>
        <table class="print-table print-w-full">
            <thead>
                <tr>
                    <th class="print-col-40"><?= __('form.name') ?></th>
                    <th class="print-col-20"><?= __('game.players') ?></th>
                    <th class="print-col-20"><?= __('game.duration') ?></th>
                    <th class="print-col-20">Spielort</th>
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
