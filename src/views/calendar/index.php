<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">

<div class="page-header">
    <h1 class="page-title"><?= __('calendar.title') ?></h1>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" id="add-event-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('calendar.add_event') ?>
        </button>
    </div>
</div>

<div class="grid grid-cols-4 gap-4">
    <!-- Calendar -->
    <div style="grid-column: span 3;">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Upcoming Events -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><?= __('calendar.upcoming') ?></h2>
            </div>
            <?php if (empty($upcoming)): ?>
                <div class="card-body">
                    <p class="text-muted text-sm">Keine kommenden Termine.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <ul class="event-list">
                        <?php foreach ($upcoming as $event): ?>
                            <li class="event-item" data-event-id="<?= $event['id'] ?>">
                                <div class="event-date">
                                    <span class="event-day"><?= date('d', strtotime($event['start_date'])) ?></span>
                                    <span class="event-month"><?= formatDate($event['start_date'], 'M') ?></span>
                                </div>
                                <div class="event-info">
                                    <div class="event-title"><?= e($event['title']) ?></div>
                                    <?php if ($event['game_name']): ?>
                                        <div class="event-meta text-muted text-sm">
                                            Spiel: <?= e($event['game_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($event['group_name']): ?>
                                        <div class="event-meta text-muted text-sm">
                                            Gruppe: <?= e($event['group_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Legend -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Farben</h2>
            </div>
            <div class="card-body">
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-color" style="background: #3788d8;"></span>
                        Standard
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #22c55e;"></span>
                        Outdoor
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #f59e0b;"></span>
                        Fest/Feier
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #ef4444;"></span>
                        Wichtig
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="event-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title"><?= __('calendar.add_event') ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="event-form">
                <input type="hidden" name="id" id="event-id">

                <div class="form-group">
                    <label for="event-title" class="form-label">Titel <span class="required">*</span></label>
                    <input type="text" id="event-title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="event-description" class="form-label">Beschreibung</label>
                    <textarea id="event-description" name="description" class="form-control" rows="2"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="event-start" class="form-label">Start <span class="required">*</span></label>
                        <input type="datetime-local" id="event-start" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="event-end" class="form-label">Ende</label>
                        <input type="datetime-local" id="event-end" name="end_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="event-allday" name="all_day">
                        <span>Ganztägig</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="event-game" class="form-label">Spiel verknüpfen</label>
                        <select id="event-game" name="game_id" class="form-control">
                            <option value="">-- Kein Spiel --</option>
                            <?php foreach ($games as $game): ?>
                                <option value="<?= $game['id'] ?>"><?= e($game['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event-group" class="form-label">Gruppe verknüpfen</label>
                        <select id="event-group" name="group_id" class="form-control">
                            <option value="">-- Keine Gruppe --</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group['id'] ?>"><?= e($group['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="event-color" class="form-label">Farbe</label>
                    <div class="color-options">
                        <label class="color-option">
                            <input type="radio" name="color" value="#3788d8" checked>
                            <span style="background: #3788d8;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="color" value="#22c55e">
                            <span style="background: #22c55e;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="color" value="#f59e0b">
                            <span style="background: #f59e0b;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="color" value="#ef4444">
                            <span style="background: #ef4444;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="color" value="#8b5cf6">
                            <span style="background: #8b5cf6;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="color" value="#ec4899">
                            <span style="background: #ec4899;"></span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" id="delete-event-btn" style="display: none;">
                        Löschen
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" class="btn btn-secondary modal-cancel">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style<?= cspNonce() ?>>
.event-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.event-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--color-gray-100);
    cursor: pointer;
}
.event-item:last-child {
    border-bottom: none;
}
.event-item:hover {
    background: var(--color-gray-50);
}
.event-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 40px;
}
.event-day {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-primary);
}
.event-month {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-gray-500);
}
.event-title {
    font-weight: 500;
}

.legend-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
}
.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.color-options {
    display: flex;
    gap: 8px;
}
.color-option {
    cursor: pointer;
}
.color-option input {
    display: none;
}
.color-option span {
    display: block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 3px solid transparent;
    transition: border-color 0.2s;
}
.color-option input:checked + span {
    border-color: var(--color-gray-800);
}

/* Modal Styles */
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
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
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

/* FullCalendar customization */
.fc {
    font-family: inherit;
}
.fc-toolbar-title {
    font-size: 1.25rem !important;
}
.fc-button-primary {
    background-color: var(--color-primary) !important;
    border-color: var(--color-primary) !important;
}
.fc-button-primary:hover {
    background-color: var(--color-primary-dark) !important;
    border-color: var(--color-primary-dark) !important;
}
.fc-daygrid-day.fc-day-today {
    background-color: rgba(79, 70, 229, 0.1) !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('event-modal');
    const form = document.getElementById('event-form');
    const csrfToken = '<?= e(Session::getCsrfToken()) ?>';
    let calendar;
    let currentEvent = null;

    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'de',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            list: 'Liste'
        },
        events: <?= json_encode($events) ?>,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,

        // Click on date to create event
        dateClick: function(info) {
            openModal(null, info.dateStr);
        },

        // Click on event to edit
        eventClick: function(info) {
            openModal(info.event);
        },

        // Drag to reschedule
        eventDrop: function(info) {
            updateEvent(info.event.id, {
                start_date: info.event.start.toISOString(),
                end_date: info.event.end ? info.event.end.toISOString() : null
            });
        },

        // Resize event
        eventResize: function(info) {
            updateEvent(info.event.id, {
                start_date: info.event.start.toISOString(),
                end_date: info.event.end ? info.event.end.toISOString() : null
            });
        }
    });
    calendar.render();

    // Add event button
    document.getElementById('add-event-btn').addEventListener('click', () => openModal(null));

    // Sidebar event click
    document.querySelectorAll('.event-item').forEach(item => {
        item.addEventListener('click', function() {
            const eventId = this.dataset.eventId;
            const calEvent = calendar.getEventById(eventId);
            if (calEvent) {
                openModal(calEvent);
            }
        });
    });

    // Open modal
    function openModal(event, defaultDate = null) {
        currentEvent = event;

        if (event) {
            // Edit mode
            document.getElementById('modal-title').textContent = 'Termin bearbeiten';
            document.getElementById('event-id').value = event.id;
            document.getElementById('event-title').value = event.title;
            document.getElementById('event-description').value = event.extendedProps.description || '';
            document.getElementById('event-start').value = formatDateTimeLocal(event.start);
            document.getElementById('event-end').value = event.end ? formatDateTimeLocal(event.end) : '';
            document.getElementById('event-allday').checked = event.allDay;
            document.getElementById('event-game').value = event.extendedProps.game_id || '';
            document.getElementById('event-group').value = event.extendedProps.group_id || '';

            // Set color
            const color = event.backgroundColor || '#3788d8';
            const colorInput = form.querySelector(`input[name="color"][value="${color}"]`);
            if (colorInput) colorInput.checked = true;

            document.getElementById('delete-event-btn').style.display = 'block';
        } else {
            // Create mode
            document.getElementById('modal-title').textContent = '<?= __('calendar.add_event') ?>';
            form.reset();
            document.getElementById('event-id').value = '';

            if (defaultDate) {
                document.getElementById('event-start').value = defaultDate + 'T09:00';
            }

            document.getElementById('delete-event-btn').style.display = 'none';
        }

        modal.style.display = 'flex';
    }

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
        currentEvent = null;
    }

    modal.querySelector('.modal-close').addEventListener('click', closeModal);
    modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);
    modal.querySelector('.modal-cancel').addEventListener('click', closeModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.all_day = document.getElementById('event-allday').checked;

        const eventId = document.getElementById('event-id').value;
        const url = eventId ? `/api/calendar/events/${eventId}` : '/api/calendar/events';
        const method = eventId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                if (eventId && currentEvent) {
                    // Update existing event
                    currentEvent.remove();
                }
                calendar.addEvent(result.event);
                closeModal();
                location.reload(); // Refresh to update sidebar
            } else {
                alert(result.error || 'Fehler beim Speichern.');
            }
        } catch (err) {
            alert('Fehler beim Speichern.');
        }
    });

    // Delete event
    document.getElementById('delete-event-btn').addEventListener('click', async function() {
        if (!currentEvent || !confirm('Termin wirklich löschen?')) return;

        try {
            const response = await fetch(`/api/calendar/events/${currentEvent.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': csrfToken }
            });

            const result = await response.json();

            if (result.success) {
                currentEvent.remove();
                closeModal();
                location.reload();
            } else {
                alert(result.error || 'Fehler beim Löschen.');
            }
        } catch (err) {
            alert('Fehler beim Löschen.');
        }
    });

    // Update event (drag/resize)
    async function updateEvent(eventId, data) {
        try {
            await fetch(`/api/calendar/events/${eventId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(data)
            });
        } catch (err) {
            console.error('Failed to update event', err);
        }
    }

    // Format date for datetime-local input
    function formatDateTimeLocal(date) {
        const d = new Date(date);
        const pad = n => n.toString().padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }
});
</script>
