<?php
/**
 * =====================================================================================
 * DATE HELPERS - German/Austrian Date Formatting
 * =====================================================================================
 *
 * PURPOSE:
 * Provides date/time formatting functions for the German locale. All dates in
 * the application are stored as MySQL DATETIME/DATE but displayed in German
 * format (DD.MM.YYYY) throughout the UI.
 *
 * FUNCTION INDEX:
 * - formatDate($date)          : "15.01.2026" (DD.MM.YYYY)
 * - formatDateTime($datetime)  : "15.01.2026 14:30" (DD.MM.YYYY HH:MM)
 * - formatDateRelative($date)  : "Heute", "Gestern", "Vor 3 Tagen", "15.01.2026"
 * - formatDuration($minutes)   : "1 Std. 30 Min." or "45 Min."
 * - formatDateRange($start,$end): "15.01. - 20.01.2026" or "15.01.2026"
 *
 * AI NOTES:
 * - All functions accept MySQL date/datetime strings and return German formatted strings
 * - formatDateRelative() shows relative text for recent dates (< 7 days),
 *   absolute dates for older entries
 * - Used primarily in view templates for display purposes
 *
 * @package KindergartenOrganizer\Helpers
 * @since 1.0.0
 * =====================================================================================
 */

/**
 * German month names
 */
function getGermanMonths(): array
{
    return [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];
}

/**
 * German short month names
 */
function getGermanMonthsShort(): array
{
    return [
        1 => 'Jan.',
        2 => 'Feb.',
        3 => 'März',
        4 => 'Apr.',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Aug.',
        9 => 'Sept.',
        10 => 'Okt.',
        11 => 'Nov.',
        12 => 'Dez.',
    ];
}

/**
 * German weekday names
 */
function getGermanWeekdays(): array
{
    return [
        0 => 'Sonntag',
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Samstag',
    ];
}

/**
 * German short weekday names
 */
function getGermanWeekdaysShort(): array
{
    return [
        0 => 'So',
        1 => 'Mo',
        2 => 'Di',
        3 => 'Mi',
        4 => 'Do',
        5 => 'Fr',
        6 => 'Sa',
    ];
}

/**
 * Format date in German style
 *
 * @param mixed $date DateTime object, timestamp, or date string
 * @param string $format 'short' (24.12.2024), 'full' (Dienstag, 24. Dezember 2024),
 *                       'medium' (24. Dez. 2024), 'datetime' (24.12.2024 14:30)
 */
function formatDateGerman($date, string $format = 'short'): string
{
    if ($date === null) {
        return '';
    }

    if (is_string($date)) {
        $date = new DateTime($date);
    } elseif (is_int($date)) {
        $dateObj = new DateTime();
        $dateObj->setTimestamp($date);
        $date = $dateObj;
    }

    if (!$date instanceof DateTime) {
        return '';
    }

    $months = getGermanMonths();
    $monthsShort = getGermanMonthsShort();
    $weekdays = getGermanWeekdays();

    switch ($format) {
        case 'full':
            return sprintf(
                '%s, %d. %s %d',
                $weekdays[(int)$date->format('w')],
                (int)$date->format('j'),
                $months[(int)$date->format('n')],
                (int)$date->format('Y')
            );

        case 'medium':
            return sprintf(
                '%d. %s %d',
                (int)$date->format('j'),
                $monthsShort[(int)$date->format('n')],
                (int)$date->format('Y')
            );

        case 'datetime':
            return $date->format('d.m.Y H:i');

        case 'time':
            return $date->format('H:i');

        case 'iso':
            return $date->format('Y-m-d');

        case 'short':
        default:
            return $date->format('d.m.Y');
    }
}

/**
 * Format relative time (e.g., "vor 5 Minuten")
 */
function formatTimeAgo($date): string
{
    if ($date === null) {
        return '';
    }

    if (is_string($date)) {
        $date = new DateTime($date);
    } elseif (is_int($date)) {
        $dateObj = new DateTime();
        $dateObj->setTimestamp($date);
        $date = $dateObj;
    }

    $now = new DateTime();
    $diff = $now->getTimestamp() - $date->getTimestamp();

    if ($diff < 60) {
        return 'gerade eben';
    }

    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return sprintf('vor %d %s', $minutes, $minutes === 1 ? 'Minute' : 'Minuten');
    }

    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return sprintf('vor %d %s', $hours, $hours === 1 ? 'Stunde' : 'Stunden');
    }

    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return sprintf('vor %d %s', $days, $days === 1 ? 'Tag' : 'Tagen');
    }

    if ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return sprintf('vor %d %s', $weeks, $weeks === 1 ? 'Woche' : 'Wochen');
    }

    // More than a month, show actual date
    return formatDateGerman($date, 'short');
}

/**
 * Get Austrian holidays for a given year
 */
function getAustrianHolidays(int $year): array
{
    $holidays = [];

    // Fixed holidays
    $fixed = [
        '01-01' => 'Neujahr',
        '01-06' => 'Heilige Drei Könige',
        '05-01' => 'Staatsfeiertag',
        '08-15' => 'Mariä Himmelfahrt',
        '10-26' => 'Nationalfeiertag',
        '11-01' => 'Allerheiligen',
        '12-08' => 'Mariä Empfängnis',
        '12-25' => 'Christtag',
        '12-26' => 'Stefanitag',
    ];

    foreach ($fixed as $date => $name) {
        $holidays[$year . '-' . $date] = $name;
    }

    // Calculate Easter-dependent holidays
    $easterTimestamp = easter_date($year);
    $easter = new DateTime();
    $easter->setTimestamp($easterTimestamp);

    // Easter Sunday
    $holidays[$easter->format('Y-m-d')] = 'Ostersonntag';

    // Easter Monday (+1)
    $easterMonday = clone $easter;
    $easterMonday->modify('+1 day');
    $holidays[$easterMonday->format('Y-m-d')] = 'Ostermontag';

    // Ascension Day (+39)
    $ascension = clone $easter;
    $ascension->modify('+39 days');
    $holidays[$ascension->format('Y-m-d')] = 'Christi Himmelfahrt';

    // Whit Sunday (+49)
    $whitSunday = clone $easter;
    $whitSunday->modify('+49 days');
    $holidays[$whitSunday->format('Y-m-d')] = 'Pfingstsonntag';

    // Whit Monday (+50)
    $whitMonday = clone $easter;
    $whitMonday->modify('+50 days');
    $holidays[$whitMonday->format('Y-m-d')] = 'Pfingstmontag';

    // Corpus Christi (+60)
    $corpusChristi = clone $easter;
    $corpusChristi->modify('+60 days');
    $holidays[$corpusChristi->format('Y-m-d')] = 'Fronleichnam';

    ksort($holidays);

    return $holidays;
}

/**
 * Check if a date is an Austrian holiday
 */
function isAustrianHoliday($date): ?string
{
    if (is_string($date)) {
        $date = new DateTime($date);
    }

    $year = (int)$date->format('Y');
    $holidays = getAustrianHolidays($year);

    $dateStr = $date->format('Y-m-d');

    return $holidays[$dateStr] ?? null;
}

/**
 * Get the calendar week number
 */
function getWeekNumber($date): int
{
    if (is_string($date)) {
        $date = new DateTime($date);
    }

    return (int)$date->format('W');
}

/**
 * Get start of week (Monday) for a date
 */
function getWeekStart($date): DateTime
{
    if (is_string($date)) {
        $date = new DateTime($date);
    } else {
        $date = clone $date;
    }

    $dayOfWeek = (int)$date->format('N');
    $date->modify('-' . ($dayOfWeek - 1) . ' days');

    return $date;
}

/**
 * Get end of week (Sunday) for a date
 */
function getWeekEnd($date): DateTime
{
    $start = getWeekStart($date);
    $start->modify('+6 days');

    return $start;
}

/**
 * Parse a German date string to DateTime
 */
function parseGermanDate(string $dateStr): ?DateTime
{
    // Try DD.MM.YYYY format
    $date = DateTime::createFromFormat('d.m.Y', $dateStr);
    if ($date !== false) {
        return $date;
    }

    // Try DD.MM.YY format
    $date = DateTime::createFromFormat('d.m.y', $dateStr);
    if ($date !== false) {
        return $date;
    }

    // Try ISO format
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($date !== false) {
        return $date;
    }

    return null;
}
