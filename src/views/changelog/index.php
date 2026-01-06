<?php
require_once SRC_PATH . '/services/ChangelogService.php';
?>
<div class="page-header">
    <h1 class="page-title"><?= __('changelog.title') ?></h1>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= url('/changelog') ?>" method="GET" class="flex gap-4 items-end">
            <div class="form-group mb-0">
                <label class="form-label">Typ</label>
                <select name="type" class="form-control">
                    <option value="">Alle Typen</option>
                    <option value="game" <?= $filterType === 'game' ? 'selected' : '' ?>>Spiele</option>
                    <option value="material" <?= $filterType === 'material' ? 'selected' : '' ?>>Materialien</option>
                    <option value="box" <?= $filterType === 'box' ? 'selected' : '' ?>>Boxen</option>
                    <option value="category" <?= $filterType === 'category' ? 'selected' : '' ?>>Altersgruppen</option>
                    <option value="tag" <?= $filterType === 'tag' ? 'selected' : '' ?>>Themen</option>
                    <option value="group" <?= $filterType === 'group' ? 'selected' : '' ?>>Gruppen</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Aktion</label>
                <select name="action" class="form-control">
                    <option value="">Alle Aktionen</option>
                    <option value="create" <?= $filterAction === 'create' ? 'selected' : '' ?>>Erstellt</option>
                    <option value="update" <?= $filterAction === 'update' ? 'selected' : '' ?>>Aktualisiert</option>
                    <option value="delete" <?= $filterAction === 'delete' ? 'selected' : '' ?>>Gelöscht</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-secondary">Filtern</button>
                <a href="<?= url('/changelog') ?>" class="btn btn-secondary">Zurücksetzen</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($entries)): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <h3 class="empty-state-title">Keine Änderungen vorhanden</h3>
            <p class="empty-state-text">Änderungen an Spielen, Materialien und anderen Elementen werden hier protokolliert.</p>
        </div>
    </div>
</div>
<?php else: ?>

<div class="text-muted mb-3"><?= $total ?> <?= pluralize($total, 'Eintrag', 'Einträge') ?></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 160px;">Datum</th>
                    <th style="width: 100px;">Aktion</th>
                    <th style="width: 120px;">Typ</th>
                    <th>Element</th>
                    <th>Benutzer</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                <tr>
                    <td class="text-muted">
                        <?= formatDate($entry['created_at'], 'd.m.Y H:i') ?>
                    </td>
                    <td>
                        <?php
                        $actionClass = match($entry['action']) {
                            'create' => 'badge-success',
                            'update' => 'badge-info',
                            'delete' => 'badge-danger',
                            default => ''
                        };
                        ?>
                        <span class="badge <?= $actionClass ?>">
                            <?= ChangelogService::getActionLabel($entry['action']) ?>
                        </span>
                    </td>
                    <td>
                        <?= ChangelogService::getEntityTypeLabel($entry['entity_type']) ?>
                    </td>
                    <td>
                        <strong><?= e($entry['entity_name']) ?></strong>
                        <?php if ($entry['action'] !== 'delete'): ?>
                            <?php
                            $url = match($entry['entity_type']) {
                                'game' => url('/games/' . $entry['entity_id']),
                                'material' => url('/materials/' . $entry['entity_id']),
                                'box' => url('/boxes/' . $entry['entity_id']),
                                'category' => url('/categories/' . $entry['entity_id'] . '/edit'),
                                'tag' => url('/tags/' . $entry['entity_id'] . '/edit'),
                                'group' => url('/groups/' . $entry['entity_id']),
                                default => null
                            };
                            if ($url): ?>
                                <a href="<?= $url ?>" class="text-sm ml-2">anzeigen →</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= e($entry['user_name'] ?? 'System') ?></td>
                    <td>
                        <?php if ($entry['changes']): ?>
                            <button type="button" class="btn btn-sm btn-secondary changelog-details"
                                    data-changes="<?= e($entry['changes']) ?>" title="Details anzeigen">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="flex justify-center mt-4">
    <nav class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="<?= url('/changelog?page=' . ($currentPage - 1) . ($filterType ? '&type=' . $filterType : '') . ($filterAction ? '&action=' . $filterAction : '')) ?>"
               class="pagination-link">← Zurück</a>
        <?php endif; ?>

        <span class="pagination-info">Seite <?= $currentPage ?> von <?= $totalPages ?></span>

        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= url('/changelog?page=' . ($currentPage + 1) . ($filterType ? '&type=' . $filterType : '') . ($filterAction ? '&action=' . $filterAction : '')) ?>"
               class="pagination-link">Weiter →</a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<!-- Cleanup Section -->
<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Changelog bereinigen</h2>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Alte Changelog-Einträge löschen, um Speicherplatz freizugeben.</p>
        <form action="<?= url('/changelog/clear') ?>" method="POST" class="flex items-end gap-3"
              onsubmit="return confirm('Sind Sie sicher? Diese Aktion kann nicht rückgängig gemacht werden.')">
            <?= csrfField() ?>
            <div class="form-group mb-0">
                <label class="form-label">Einträge behalten für</label>
                <select name="keep_days" class="form-control">
                    <option value="30">30 Tage</option>
                    <option value="90">90 Tage</option>
                    <option value="180">6 Monate</option>
                    <option value="365" selected>1 Jahr</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Alte Einträge löschen
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Details Modal -->
<div id="changelog-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Änderungsdetails</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <table class="table" id="changes-table">
                <thead>
                    <tr>
                        <th>Feld</th>
                        <th>Vorher</th>
                        <th>Nachher</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<style>
.pagination {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.pagination-link {
    padding: 8px 16px;
    background: var(--color-gray-100);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: inherit;
}
.pagination-link:hover {
    background: var(--color-gray-200);
}
.pagination-info {
    color: var(--color-gray-500);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}
.modal-content {
    position: relative;
    background: white;
    border-radius: var(--radius-lg);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: auto;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-gray-200);
}
.modal-title {
    margin: 0;
    font-size: 1.125rem;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-gray-400);
}
.modal-close:hover {
    color: var(--color-gray-600);
}
.modal-body {
    padding: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('changelog-modal');
    const changesTable = document.getElementById('changes-table').querySelector('tbody');

    // Show modal on click
    document.querySelectorAll('.changelog-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const changes = JSON.parse(this.dataset.changes);
            changesTable.innerHTML = '';

            for (const [field, values] of Object.entries(changes)) {
                const row = document.createElement('tr');
                if (typeof values === 'object' && values.old !== undefined) {
                    row.innerHTML = `
                        <td><strong>${field}</strong></td>
                        <td class="text-muted">${values.old || '-'}</td>
                        <td>${values.new || '-'}</td>
                    `;
                } else {
                    row.innerHTML = `
                        <td><strong>${field}</strong></td>
                        <td colspan="2">${typeof values === 'object' ? JSON.stringify(values) : values}</td>
                    `;
                }
                changesTable.appendChild(row);
            }

            modal.style.display = 'flex';
        });
    });

    // Close modal
    modal.querySelector('.modal-close').addEventListener('click', () => modal.style.display = 'none');
    modal.querySelector('.modal-backdrop').addEventListener('click', () => modal.style.display = 'none');
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.style.display = 'none'; });
});
</script>
