<?php
declare(strict_types=1);

class CalendarService
{
    public function getEvents(string $start, string $end): ServiceResult
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $start)) $start = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $end)) $end = date('Y-m-t');

        $start = substr($start, 0, 10);
        $end = substr($end, 0, 10);

        $events = CalendarEvent::getForRange($start, $end);
        $formatted = array_map([CalendarEvent::class, 'formatForCalendar'], $events);

        return ServiceResult::ok(['events' => $formatted]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        $eventId = CalendarEvent::create($data);
        if (!$eventId) return ServiceResult::fail([], __('flash.error_creating'));

        ChangelogService::getInstance()->logCreate('event', $eventId, $data['title'], $data);

        $event = CalendarEvent::findWithRelations($eventId);
        $formatted = CalendarEvent::formatForCalendar($event);

        return ServiceResult::ok(
            ['id' => $eventId, 'event' => $formatted],
            __('flash.created', ['item' => __('calendar.event')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $event = CalendarEvent::find($id);
        if (!$event) return ServiceResult::fail([], __('calendar.event_not_found'));

        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($event, $data, ['title', 'description', 'start_date', 'end_date', 'all_day', 'color']);

        CalendarEvent::update($id, $data);

        if (!empty($changes)) {
            $changelog->logUpdate('event', $id, $data['title'], $changes);
        }

        $updated = CalendarEvent::findWithRelations($id);
        $formatted = CalendarEvent::formatForCalendar($updated);

        return ServiceResult::ok(
            ['id' => $id, 'event' => $formatted],
            __('flash.updated', ['item' => __('calendar.event')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $event = CalendarEvent::find($id);
        if (!$event) return ServiceResult::fail([], __('calendar.event_not_found'));

        ChangelogService::getInstance()->logDelete('event', $id, $event['title'], $event);
        CalendarEvent::delete($id);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('calendar.event')])
        );
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = [__('validation.title_required')];
        } elseif (mb_strlen($data['title']) > 255) {
            $errors['title'] = [__('validation.title_max_255')];
        }

        if (isset($data['description']) && mb_strlen($data['description']) > 5000) {
            $errors['description'] = [__('validation.description_max_5000')];
        }

        if (empty($data['start_date'])) {
            $errors['start_date'] = [__('validation.start_date_required')];
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $data['start_date'])) {
            $errors['start_date'] = [__('validation.invalid_date_format')];
        }

        if (!empty($data['end_date'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}(:\d{2})?)?$/', $data['end_date'])) {
                $errors['end_date'] = [__('validation.end_date_format')];
            } elseif ($data['end_date'] < ($data['start_date'] ?? '')) {
                $errors['end_date'] = [__('validation.end_before_start')];
            }
        }

        // Sanitize invalid color
        if (!empty($data['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            // Not an error — just reset
        }

        return $errors;
    }
}
