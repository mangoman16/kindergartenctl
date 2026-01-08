<div class="page-header">
    <h1 class="page-title"><?= __('game.title_plural') ?></h1>
    <div class="page-actions">
        <a href="<?= url('/games/create') ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('game.add_new') ?>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= url('/games') ?>" method="GET" class="filter-form">
            <div class="grid grid-cols-5 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label"><?= __('form.search') ?></label>
                    <input type="text" name="q" class="form-control" placeholder="<?= __('misc.search_placeholder') ?>"
                           value="<?= e($filters['search'] ?? '') ?>">
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.boxes') ?></label>
                    <select name="box" class="form-control">
                        <option value="">Alle Boxen</option>
                        <?php foreach ($boxes as $box): ?>
                            <option value="<?= $box['id'] ?>" <?= ($filters['box_id'] ?? '') == $box['id'] ? 'selected' : '' ?>>
                                <?= e($box['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.categories') ?></label>
                    <select name="category" class="form-control">
                        <option value="">Alle Altersgruppen</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label"><?= __('nav.tags') ?></label>
                    <select name="tag" class="form-control">
                        <option value="">Alle Themen</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" <?= ($filters['tag_id'] ?? '') == $tag['id'] ? 'selected' : '' ?>>
                                <?= e($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label">&nbsp;</label>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-secondary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            Filtern
                        </button>
                        <a href="<?= url('/games') ?>" class="btn btn-secondary">Zurücksetzen</a>
                    </div>
                </div>
            </div>
            <div class="flex gap-4 mt-3">
                <label class="form-check">
                    <input type="checkbox" name="favorites" value="1" <?= !empty($filters['is_favorite']) ? 'checked' : '' ?>>
                    <span class="form-check-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" style="color: var(--color-warning); vertical-align: -2px;">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        Nur Favoriten
                    </span>
                </label>
            </div>
        </form>
    </div>
</div>

<?php if (empty($games)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="10 8 16 12 10 16 10 8"></polygon>
                </svg>
            </div>
            <h3 class="empty-state-title">Noch keine Spiele vorhanden</h3>
            <p class="empty-state-text">Erstellen Sie Ihr erstes Spiel, um loszulegen.</p>
            <a href="<?= url('/games/create') ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= __('game.add_new') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Bulk Actions Bar -->
<div id="bulk-actions-bar" class="bulk-actions-bar" style="display: none;">
    <div class="flex items-center gap-4">
        <label class="form-check">
            <input type="checkbox" id="select-all-checkbox">
            <span class="form-check-label">Alle auswählen</span>
        </label>
        <span id="selected-count" class="text-muted">0 ausgewählt</span>
    </div>
    <div class="flex gap-2">
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-add-group" title="Zu Gruppe hinzufügen">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            Zu Gruppe
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-add-favorites" title="Als Favorit markieren">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            Favoriten +
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-remove-favorites" title="Aus Favoriten entfernen">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                <line x1="4" y1="4" x2="20" y2="20"></line>
            </svg>
            Favoriten -
        </button>
        <button type="button" class="btn btn-sm btn-secondary" id="bulk-cancel">Abbrechen</button>
    </div>
</div>

<!-- Games Grid -->
<div class="flex items-center justify-between mb-3">
    <div class="text-muted"><?= count($games) ?> <?= pluralize(count($games), 'Spiel', 'Spiele') ?> gefunden</div>
    <button type="button" class="btn btn-sm btn-secondary" id="toggle-selection-mode">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 11 12 14 22 4"></polyline>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg>
        Mehrfachauswahl
    </button>
</div>

<div class="grid grid-cols-4 gap-4" id="games-grid">
    <?php foreach ($games as $game): ?>
    <div class="card game-card <?= !$game['is_active'] ? 'opacity-60' : '' ?>" data-game-id="<?= $game['id'] ?>">
        <label class="game-card-checkbox" style="display: none;">
            <input type="checkbox" class="game-select-checkbox" value="<?= $game['id'] ?>">
            <span class="checkmark"></span>
        </label>
        <a href="<?= url('/games/' . $game['id']) ?>" class="game-card-image">
            <?php if ($game['image_path']): ?>
                <img src="<?= upload($game['image_path']) ?>" alt="<?= e($game['name']) ?>">
            <?php else: ?>
                <div class="game-card-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10 8 16 12 10 16 10 8"></polygon>
                    </svg>
                </div>
            <?php endif; ?>
            <?php if ($game['is_outdoor']): ?>
                <span class="game-card-badge" title="Outdoor-Spiel">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                </span>
            <?php endif; ?>
        </a>
        <div class="card-body">
            <h3 class="game-card-title">
                <a href="<?= url('/games/' . $game['id']) ?>"><?= e($game['name']) ?></a>
            </h3>

            <?php if ($game['box_name']): ?>
                <div class="text-sm text-muted mb-2">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    <?= e($game['box_name']) ?>
                    <?php if ($game['box_label']): ?>
                        <span class="badge badge-sm ml-1"><?= e($game['box_label']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($game['category_name']): ?>
                <div class="text-sm text-muted mb-2">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <?= e($game['category_name']) ?>
                </div>
            <?php endif; ?>

            <div class="flex gap-2 mt-2">
                <?php if ($game['min_players'] || $game['max_players']): ?>
                    <span class="badge badge-sm" title="Spieleranzahl">
                        <?php if ($game['min_players'] && $game['max_players']): ?>
                            <?= $game['min_players'] ?>-<?= $game['max_players'] ?>
                        <?php elseif ($game['min_players']): ?>
                            ab <?= $game['min_players'] ?>
                        <?php else: ?>
                            bis <?= $game['max_players'] ?>
                        <?php endif; ?>
                        Spieler
                    </span>
                <?php endif; ?>

                <?php if ($game['duration_minutes']): ?>
                    <span class="badge badge-sm" title="Dauer">
                        <?= $game['duration_minutes'] ?> Min.
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.game-card { position: relative; }
.game-card-image {
    display: block;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    background: var(--color-gray-100);
}
.game-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s;
}
.game-card:hover .game-card-image img {
    transform: scale(1.05);
}
.game-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-400);
}
.game-card-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--color-warning);
    color: white;
    padding: 4px;
    border-radius: var(--radius-md);
}
.game-card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}
.game-card-title a {
    color: inherit;
    text-decoration: none;
}
.game-card-title a:hover {
    color: var(--color-primary);
}
.opacity-60 { opacity: 0.6; }

/* Bulk Actions */
.bulk-actions-bar {
    background: var(--color-primary);
    color: white;
    padding: 12px 16px;
    border-radius: var(--radius-lg);
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideDown 0.2s ease;
}
.bulk-actions-bar .form-check-label { color: white; }
.bulk-actions-bar .text-muted { color: rgba(255,255,255,0.8); }
.bulk-actions-bar .btn-secondary {
    background: rgba(255,255,255,0.2);
    border-color: transparent;
    color: white;
}
.bulk-actions-bar .btn-secondary:hover {
    background: rgba(255,255,255,0.3);
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Game Card Checkbox */
.game-card-checkbox {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 10;
    cursor: pointer;
}
.game-card-checkbox input {
    display: none;
}
.game-card-checkbox .checkmark {
    display: block;
    width: 24px;
    height: 24px;
    background: white;
    border: 2px solid var(--color-gray-300);
    border-radius: var(--radius-md);
    transition: all 0.2s;
}
.game-card-checkbox input:checked + .checkmark {
    background: var(--color-primary);
    border-color: var(--color-primary);
}
.game-card-checkbox input:checked + .checkmark::after {
    content: '';
    display: block;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin: 3px auto;
}
.game-card.selected {
    outline: 3px solid var(--color-primary);
    outline-offset: -3px;
}
.selection-mode .game-card-checkbox { display: block !important; }
.selection-mode .game-card { cursor: pointer; }
</style>

<!-- Add to Group Modal -->
<div id="add-to-group-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Zu Gruppe hinzufügen</h3>
            <button type="button" class="modal-close" onclick="closeGroupModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Gruppe auswählen</label>
                <select id="bulk-group-select" class="form-control">
                    <option value="">-- Gruppe wählen --</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeGroupModal()">Abbrechen</button>
            <button type="button" class="btn btn-primary" onclick="confirmBulkAddToGroup()">Hinzufügen</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-selection-mode');
    const bulkBar = document.getElementById('bulk-actions-bar');
    const gamesGrid = document.getElementById('games-grid');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const selectedCountEl = document.getElementById('selected-count');
    const cancelBtn = document.getElementById('bulk-cancel');

    let selectionMode = false;
    let selectedIds = new Set();

    // Toggle selection mode
    toggleBtn?.addEventListener('click', function() {
        selectionMode = !selectionMode;
        gamesGrid.classList.toggle('selection-mode', selectionMode);
        bulkBar.style.display = selectionMode ? 'flex' : 'none';
        toggleBtn.style.display = selectionMode ? 'none' : 'inline-flex';
        if (!selectionMode) {
            clearSelection();
        }
    });

    // Cancel selection mode
    cancelBtn?.addEventListener('click', function() {
        selectionMode = false;
        gamesGrid.classList.remove('selection-mode');
        bulkBar.style.display = 'none';
        toggleBtn.style.display = 'inline-flex';
        clearSelection();
    });

    // Handle card clicks in selection mode
    gamesGrid?.addEventListener('click', function(e) {
        if (!selectionMode) return;

        const card = e.target.closest('.game-card');
        if (!card) return;

        // If clicking a link, prevent navigation in selection mode
        if (e.target.closest('a')) {
            e.preventDefault();
        }

        const checkbox = card.querySelector('.game-select-checkbox');
        const gameId = card.dataset.gameId;

        checkbox.checked = !checkbox.checked;
        card.classList.toggle('selected', checkbox.checked);

        if (checkbox.checked) {
            selectedIds.add(gameId);
        } else {
            selectedIds.delete(gameId);
        }

        updateSelectedCount();
    });

    // Select all checkbox
    selectAllCheckbox?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.game-select-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            const card = cb.closest('.game-card');
            card.classList.toggle('selected', this.checked);
            if (this.checked) {
                selectedIds.add(cb.value);
            } else {
                selectedIds.delete(cb.value);
            }
        });
        updateSelectedCount();
    });

    function clearSelection() {
        selectedIds.clear();
        document.querySelectorAll('.game-select-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('.game-card').classList.remove('selected');
        });
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        updateSelectedCount();
    }

    function updateSelectedCount() {
        if (selectedCountEl) {
            selectedCountEl.textContent = selectedIds.size + ' ausgewählt';
        }
    }

    // Bulk add to favorites
    document.getElementById('bulk-add-favorites')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('Keine Spiele ausgewählt');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/games/') ?>' + id + '/toggle-favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ favorite: true })
            });
        }

        alert(selectedIds.size + ' Spiele zu Favoriten hinzugefügt');
        location.reload();
    });

    // Bulk remove from favorites
    document.getElementById('bulk-remove-favorites')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('Keine Spiele ausgewählt');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/games/') ?>' + id + '/toggle-favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ favorite: false })
            });
        }

        alert(selectedIds.size + ' Spiele aus Favoriten entfernt');
        location.reload();
    });

    // Bulk add to group
    document.getElementById('bulk-add-group')?.addEventListener('click', async function() {
        if (selectedIds.size === 0) return alert('Keine Spiele ausgewählt');

        // Load groups
        const response = await fetch('<?= url('/api/groups') ?>');
        const data = await response.json();

        const select = document.getElementById('bulk-group-select');
        select.innerHTML = '<option value="">-- Gruppe wählen --</option>';
        data.forEach(group => {
            select.innerHTML += `<option value="${group.id}">${group.name}</option>`;
        });

        document.getElementById('add-to-group-modal').style.display = 'flex';
    });

    // Make functions globally accessible for modal
    window.closeGroupModal = function() {
        document.getElementById('add-to-group-modal').style.display = 'none';
    };

    window.confirmBulkAddToGroup = async function() {
        const groupId = document.getElementById('bulk-group-select').value;
        if (!groupId) return alert('Bitte Gruppe auswählen');

        for (const id of selectedIds) {
            await fetch('<?= url('/api/groups/add-item') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Session::get('csrf_token') ?>'
                },
                body: JSON.stringify({ group_id: groupId, item_type: 'game', item_id: id })
            });
        }

        closeGroupModal();
        alert(selectedIds.size + ' Spiele zur Gruppe hinzugefügt');
        location.reload();
    };

    window.selectedIds = selectedIds;
});
</script>
