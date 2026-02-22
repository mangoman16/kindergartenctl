<?php
/**
 * Calendar Controller
 */

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Show calendar view
     */
    public function index(): void
    {
        require_once SRC_PATH . '/models/CalendarEvent.php';
        require_once SRC_PATH . '/models/Game.php';
        require_once SRC_PATH . '/models/Group.php';

        // Get events for a 3-month range (previous, current, next) for FullCalendar
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));

        $startDate = date('Y-m-01', mktime(0, 0, 0, $month - 1, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month + 1, 1, $year));
        $events = CalendarEvent::getForRange($startDate, $endDate);
        $formattedEvents = array_map([CalendarEvent::class, 'formatForCalendar'], $events);

        // Get games and groups for event creation
        $games = Game::getForSelect();
        $groups = Group::getForSelect();

        // Get upcoming events for sidebar
        $upcoming = CalendarEvent::getUpcoming(5);

        $this->setTitle(__('calendar.title'));
        $this->addBreadcrumb(__('calendar.title'));

        // Pass Austrian holidays
        require_once SRC_PATH . '/helpers/dates.php';
        $holidays = getAustrianHolidays($year);

        $this->render('calendar/index', [
            'events' => $formattedEvents,
            'games' => $games,
            'groups' => $groups,
            'upcoming' => $upcoming,
            'holidays' => $holidays,
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Get events for AJAX (FullCalendar)
     */
    public function getEvents(): void
    {
        require_once SRC_PATH . '/models/CalendarEvent.php';

        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        // Validate date formats to prevent injection
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $start)) {
            $start = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $end)) {
            $end = date('Y-m-t');
        }

        // Extract just the date part (FullCalendar may send full ISO timestamp)
        $start = substr($start, 0, 10);
        $end = substr($end, 0, 10);

        $events = CalendarEvent::getForRange($start, $end);
        $formattedEvents = array_map([CalendarEvent::class, 'formatForCalendar'], $events);

        $this->json($formattedEvents);
    }

    /**
     * Create event (AJAX)
     */
    public function store(): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/CalendarEvent.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $data = $this->getJsonInput();

        $eventData = [
            'title' => trim($data['title'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'start_date' => $data['start_date'] ?? '',
            'end_date' => $data['end_date'] ?? null,
            'all_day' => !empty($data['all_day']) ? 1 : 0,
            'color' => $data['color'] ?? null,
            'game_id' => !empty($data['game_id']) ? (int)$data['game_id'] : null,
            'group_id' => !empty($data['group_id']) ? (int)$data['group_id'] : null,
        ];

        // Validate
        if (empty($eventData['title'])) {
            $this->jsonError(__('validation.title_required'), 400);
            return;
        }

        if (mb_strlen($eventData['title']) > 255) {
            $this->jsonError(__('validation.title_max_255'), 400);
            return;
        }

        if (mb_strlen($eventData['description']) > 5000) {
            $this->jsonError(__('validation.description_max_5000'), 400);
            return;
        }

        if (empty($eventData['start_date'])) {
            $this->jsonError(__('validation.start_date_required'), 400);
            return;
        }

        // Validate date format (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $eventData['start_date'])) {
            $this->jsonError(__('validation.invalid_date_format'), 400);
            return;
        }

        // Validate end_date format and that it's >= start_date
        if (!empty($eventData['end_date'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $eventData['end_date'])) {
                $this->jsonError(__('validation.end_date_format'), 400);
                return;
            }
            if ($eventData['end_date'] < $eventData['start_date']) {
                $this->jsonError(__('validation.end_before_start'), 400);
                return;
            }
        }

        // Validate color format (hex color)
        if (!empty($eventData['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $eventData['color'])) {
            $eventData['color'] = null; // Reset invalid color
        }

        // Create event
        $eventId = CalendarEvent::create($eventData);

        if (!$eventId) {
            $this->jsonError(__('flash.error_creating'), 500);
            return;
        }

        // Log change
        ChangelogService::getInstance()->logCreate('event', $eventId, $eventData['title'], $eventData);

        $event = CalendarEvent::findWithRelations($eventId);
        $this->json([
            'success' => true,
            'event' => CalendarEvent::formatForCalendar($event),
        ]);
    }

    /**
     * Update event (AJAX)
     */
    public function update(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/CalendarEvent.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $event = CalendarEvent::find((int)$id);
        if (!$event) {
            $this->jsonError(__('calendar.event_not_found'), 404);
            return;
        }

        $data = $this->getJsonInput();

        $eventData = [
            'title' => trim((string)($data['title'] ?? $event['title'])),
            'description' => trim((string)($data['description'] ?? $event['description'] ?? '')),
            'start_date' => $data['start_date'] ?? $event['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'all_day' => isset($data['all_day']) ? (!empty($data['all_day']) ? 1 : 0) : $event['all_day'],
            'color' => $data['color'] ?? $event['color'],
            'game_id' => isset($data['game_id']) ? (!empty($data['game_id']) ? (int)$data['game_id'] : null) : $event['game_id'],
            'group_id' => isset($data['group_id']) ? (!empty($data['group_id']) ? (int)$data['group_id'] : null) : $event['group_id'],
        ];

        // Validate (same rules as store())
        if (empty($eventData['title'])) {
            $this->jsonError(__('validation.title_required'), 400);
            return;
        }

        if (mb_strlen($eventData['title']) > 255) {
            $this->jsonError(__('validation.title_max_255'), 400);
            return;
        }

        if (mb_strlen($eventData['description']) > 5000) {
            $this->jsonError(__('validation.description_max_5000'), 400);
            return;
        }

        if (empty($eventData['start_date'])) {
            $this->jsonError(__('validation.start_date_required'), 400);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $eventData['start_date'])) {
            $this->jsonError(__('validation.invalid_date_format'), 400);
            return;
        }

        if (!empty($eventData['end_date'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $eventData['end_date'])) {
                $this->jsonError(__('validation.end_date_format'), 400);
                return;
            }
            if ($eventData['end_date'] < $eventData['start_date']) {
                $this->jsonError(__('validation.end_before_start'), 400);
                return;
            }
        }

        if (!empty($eventData['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $eventData['color'])) {
            $eventData['color'] = null;
        }

        // Track changes
        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($event, $eventData, ['title', 'description', 'start_date', 'end_date', 'all_day', 'color']);

        // Update
        CalendarEvent::update((int)$id, $eventData);

        // Log change
        if (!empty($changes)) {
            $changelog->logUpdate('event', (int)$id, $eventData['title'], $changes);
        }

        $updatedEvent = CalendarEvent::findWithRelations((int)$id);
        $this->json([
            'success' => true,
            'event' => CalendarEvent::formatForCalendar($updatedEvent),
        ]);
    }

    /**
     * Delete event (AJAX)
     */
    public function delete(string $id): void
    {
        $this->requireCsrf();

        require_once SRC_PATH . '/models/CalendarEvent.php';
        require_once SRC_PATH . '/services/ChangelogService.php';

        $event = CalendarEvent::find((int)$id);
        if (!$event) {
            $this->jsonError(__('calendar.event_not_found'), 404);
            return;
        }

        // Log change before deletion
        ChangelogService::getInstance()->logDelete('event', (int)$id, $event['title'], $event);

        // Delete
        CalendarEvent::delete((int)$id);

        $this->json(['success' => true]);
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    /**
     * Send JSON error response
     */
    private function jsonError(string $message, int $status = 400): void
    {
        $this->json(['success' => false, 'error' => $message], $status);
    }
}
