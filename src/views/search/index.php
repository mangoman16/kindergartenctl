<div class="page-header">
    <h1 class="page-title"><?= __('search.title') ?></h1>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= url('/search') ?>" method="GET">
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="text" name="q" class="form-control form-control-lg"
                           value="<?= e($query) ?>" placeholder="<?= __('misc.search_placeholder') ?>"
                           autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <?= __('search.submit') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($query)): ?>
<!-- Type Filters -->
<div class="flex gap-2 mb-4">
    <?php
    $types = [
        'all' => ['label' => 'Alle', 'count' => array_sum($counts)],
        'games' => ['label' => __('nav.games'), 'count' => $counts['games'] ?? 0],
        'materials' => ['label' => __('nav.materials'), 'count' => $counts['materials'] ?? 0],
        'boxes' => ['label' => __('nav.boxes'), 'count' => $counts['boxes'] ?? 0],
        'tags' => ['label' => __('nav.tags'), 'count' => $counts['tags'] ?? 0],
        'groups' => ['label' => __('nav.groups'), 'count' => $counts['groups'] ?? 0],
    ];
    foreach ($types as $key => $data):
    ?>
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=' . $key) ?>"
           class="btn <?= $type === $key ? 'btn-primary' : 'btn-secondary' ?>">
            <?= $data['label'] ?>
            <span class="badge ml-1"><?= $data['count'] ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Results -->
<?php $totalResults = array_sum($counts); ?>
<?php if ($totalResults === 0): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </div>
            <h3 class="empty-state-title"><?= __('search.no_results') ?></h3>
            <p class="empty-state-text">Versuchen Sie einen anderen Suchbegriff.</p>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Games Results -->
<?php if (!empty($results['games']) && ($type === 'all' || $type === 'games')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('nav.games') ?> (<?= count($results['games']) ?>)</h2>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th><?= __('form.name') ?></th>
                    <th><?= __('nav.boxes') ?></th>
                    <th><?= __('game.age_group') ?></th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($results['games'], 0, $type === 'all' ? 5 : 50) as $game): ?>
                <tr>
                    <td>
                        <?php if ($game['image_path']): ?>
                            <img src="<?= upload($game['image_path']) ?>" alt=""
                                 style="width: 40px; height: 40px; border-radius: var(--radius-md); object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: var(--color-gray-100); border-radius: var(--radius-md);"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('/games/' . $game['id']) ?>" class="font-semibold text-primary">
                            <?= e($game['name']) ?>
                        </a>
                    </td>
                    <td><?= e($game['box_name'] ?? '-') ?></td>
                    <td><?= e($game['category_name'] ?? '-') ?></td>
                    <td>
                        <a href="<?= url('/games/' . $game['id']) ?>" class="btn btn-sm btn-secondary"><?= __('action.view') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($type === 'all' && count($results['games']) > 5): ?>
    <div class="card-footer">
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=games') ?>">
            Alle <?= count($results['games']) ?> Spiele anzeigen →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Materials Results -->
<?php if (!empty($results['materials']) && ($type === 'all' || $type === 'materials')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('nav.materials') ?> (<?= count($results['materials']) ?>)</h2>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('form.name') ?></th>
                    <th><?= __('form.description') ?></th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($results['materials'], 0, $type === 'all' ? 5 : 50) as $material): ?>
                <tr>
                    <td>
                        <a href="<?= url('/materials/' . $material['id']) ?>" class="font-semibold text-primary">
                            <?= e($material['name']) ?>
                        </a>
                    </td>
                    <td class="text-muted"><?= e(truncate($material['description'] ?? '', 60)) ?></td>
                    <td>
                        <a href="<?= url('/materials/' . $material['id']) ?>" class="btn btn-sm btn-secondary"><?= __('action.view') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($type === 'all' && count($results['materials']) > 5): ?>
    <div class="card-footer">
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=materials') ?>">
            Alle <?= count($results['materials']) ?> Materialien anzeigen →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Boxes Results -->
<?php if (!empty($results['boxes']) && ($type === 'all' || $type === 'boxes')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('nav.boxes') ?> (<?= count($results['boxes']) ?>)</h2>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('form.name') ?></th>
                    <th><?= __('box.label') ?></th>
                    <th><?= __('box.location') ?></th>
                    <th><?= __('nav.materials') ?></th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($results['boxes'], 0, $type === 'all' ? 5 : 50) as $box): ?>
                <tr>
                    <td>
                        <a href="<?= url('/boxes/' . $box['id']) ?>" class="font-semibold text-primary">
                            <?= e($box['name']) ?>
                        </a>
                    </td>
                    <td><?= e($box['label'] ?? '-') ?></td>
                    <td><?= e($box['location'] ?? '-') ?></td>
                    <td><span class="badge"><?= $box['material_count'] ?></span></td>
                    <td>
                        <a href="<?= url('/boxes/' . $box['id']) ?>" class="btn btn-sm btn-secondary"><?= __('action.view') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($type === 'all' && count($results['boxes']) > 5): ?>
    <div class="card-footer">
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=boxes') ?>">
            Alle <?= count($results['boxes']) ?> Boxen anzeigen →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Tags Results -->
<?php if (!empty($results['tags']) && ($type === 'all' || $type === 'tags')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('nav.tags') ?> (<?= count($results['tags']) ?>)</h2>
    </div>
    <div class="card-body">
        <div class="flex flex-wrap gap-2">
            <?php foreach (array_slice($results['tags'], 0, $type === 'all' ? 10 : 50) as $tag): ?>
                <a href="<?= url('/games?tag=' . $tag['id']) ?>" class="tag-badge"
                   style="<?= $tag['color'] ? 'background-color: ' . e($tag['color']) . '; color: white;' : '' ?>">
                    <?= e($tag['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($type === 'all' && count($results['tags']) > 10): ?>
    <div class="card-footer">
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=tags') ?>">
            Alle <?= count($results['tags']) ?> Themen anzeigen →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Groups Results -->
<?php if (!empty($results['groups']) && ($type === 'all' || $type === 'groups')): ?>
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('nav.groups') ?> (<?= count($results['groups']) ?>)</h2>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('form.name') ?></th>
                    <th><?= __('nav.games') ?></th>
                    <th><?= __('nav.materials') ?></th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($results['groups'], 0, $type === 'all' ? 5 : 50) as $group): ?>
                <tr>
                    <td>
                        <a href="<?= url('/groups/' . $group['id']) ?>" class="font-semibold text-primary">
                            <?= e($group['name']) ?>
                        </a>
                    </td>
                    <td><span class="badge"><?= $group['game_count'] ?></span></td>
                    <td><span class="badge"><?= $group['material_count'] ?></span></td>
                    <td>
                        <a href="<?= url('/groups/' . $group['id']) ?>" class="btn btn-sm btn-secondary"><?= __('action.view') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($type === 'all' && count($results['groups']) > 5): ?>
    <div class="card-footer">
        <a href="<?= url('/search?q=' . urlencode($query) . '&type=groups') ?>">
            Alle <?= count($results['groups']) ?> Gruppen anzeigen →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
<?php endif; ?>

<style>
.tag-badge {
    display: inline-block;
    padding: 6px 14px;
    background: var(--color-gray-100);
    border-radius: 9999px;
    font-size: 0.875rem;
    text-decoration: none;
    color: inherit;
    transition: opacity 0.2s;
}
.tag-badge:hover {
    opacity: 0.8;
}
</style>
