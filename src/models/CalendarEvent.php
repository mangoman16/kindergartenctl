<?php
/**
 * =====================================================================================
 * CALENDAR EVENT MODEL - Activity Scheduling
 * =====================================================================================
 *
 * Events on the calendar linked to games or groups. getForRange() uses distinct
 * PDO params because EMULATE_PREPARES=false prohibits reusing named parameters.
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * CalendarEvent Model
 */

class CalendarEvent extends Model
{
    protected static string $table = 'calendar_events';
    protected static array $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'all_day',
        'color',
        'game_id',
        'group_id',
    ];

    /**
     * Get events for a date range (inclusive).
     *
     * AI NOTE: Uses distinct named parameters for each occurrence of start/end
     * because PDO native prepared statements (ATTR_EMULATE_PREPARES=false) do not
     * support reusing the same named parameter multiple times in a single query.
     *
     * The query covers three overlap cases:
     * 1. Event starts within the range
     * 2. Event ends within the range
     * 3. Event spans the entire range (starts before, ends after)
     */
    public static function getForRange(string $start, string $end): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT ce.*,
                   g.name as game_name,
                   gr.name as group_name
            FROM calendar_events ce
            LEFT JOIN games g ON g.id = ce.game_id
            LEFT JOIN groups gr ON gr.id = ce.group_id
            WHERE (ce.start_date BETWEEN :start1 AND :end1)
               OR (ce.end_date BETWEEN :start2 AND :end2)
               OR (ce.start_date <= :start3 AND ce.end_date >= :end3)
            ORDER BY ce.start_date ASC
        ");
        $stmt->execute([
            'start1' => $start, 'end1' => $end,
            'start2' => $start, 'end2' => $end,
            'start3' => $start, 'end3' => $end,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get events for a specific month
     */
    public static function getForMonth(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        return self::getForRange($start, $end);
    }

    /**
     * Get today's events
     */
    public static function getToday(): array
    {
        $today = date('Y-m-d');
        return self::getForRange($today, $today);
    }

    /**
     * Get upcoming events
     */
    public static function getUpcoming(int $limit = 5): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT ce.*,
                   g.name as game_name,
                   gr.name as group_name
            FROM calendar_events ce
            LEFT JOIN games g ON g.id = ce.game_id
            LEFT JOIN groups gr ON gr.id = ce.group_id
            WHERE ce.start_date >= CURDATE()
            ORDER BY ce.start_date ASC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find event with relations
     */
    public static function findWithRelations(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT ce.*,
                   g.name as game_name,
                   gr.name as group_name
            FROM calendar_events ce
            LEFT JOIN games g ON g.id = ce.game_id
            LEFT JOIN groups gr ON gr.id = ce.group_id
            WHERE ce.id = :id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Format event for FullCalendar
     */
    public static function formatForCalendar(array $event): array
    {
        $formatted = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['start_date'],
            'allDay' => (bool)$event['all_day'],
        ];

        if ($event['end_date']) {
            $formatted['end'] = $event['end_date'];
        }

        if ($event['color']) {
            $formatted['backgroundColor'] = $event['color'];
            $formatted['borderColor'] = $event['color'];
        }

        // Add extra data
        $formatted['extendedProps'] = [
            'description' => $event['description'],
            'game_id' => $event['game_id'],
            'game_name' => $event['game_name'] ?? null,
            'group_id' => $event['group_id'],
            'group_name' => $event['group_name'] ?? null,
        ];

        return $formatted;
    }

    /**
     * Get events count for dashboard
     */
    public static function getThisWeekCount(): int
    {
        $db = self::getDb();

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+7 days'));

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM calendar_events
            WHERE start_date BETWEEN :start AND :end
        ");
        $stmt->execute(['start' => $start, 'end' => $end]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get count of games played this month (events with game_id in current month)
     */
    public static function getGamesPlayedThisMonthCount(): int
    {
        $db = self::getDb();

        $start = date('Y-m-01');
        $end = date('Y-m-t');

        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT game_id) FROM calendar_events
            WHERE game_id IS NOT NULL
              AND start_date BETWEEN :start AND :end
        ");
        $stmt->execute(['start' => $start, 'end' => $end]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recently played games from calendar events
     */
    public static function getRecentlyPlayed(int $limit = 5): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT ce.*, g.name as game_name, g.image_path, b.name as box_name
            FROM calendar_events ce
            INNER JOIN games g ON g.id = ce.game_id
            LEFT JOIN boxes b ON b.id = g.box_id
            WHERE ce.game_id IS NOT NULL
              AND ce.start_date <= CURDATE()
            ORDER BY ce.start_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
