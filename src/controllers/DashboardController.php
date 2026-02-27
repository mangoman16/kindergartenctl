<?php
/**
 * Dashboard Controller
 */

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    /**
     * Show dashboard
     */
    public function index(): void
    {

        $this->setTitle(__('dashboard.title'));

        // Initialize stats
        $stats = [
            'games' => 0,
            'materials' => 0,
            'boxes' => 0,
            'tags' => 0,
            'groups' => 0,
            'favorites' => 0,
            'events_this_week' => 0,
            'games_played_this_month' => 0,
        ];

        $recentGames = [];
        $recentChanges = [];
        $upcomingEvents = [];
        $favoriteGames = [];
        $recentlyPlayed = [];
        $categories = [];
        $tags = [];

        // Try to load data if database is configured
        try {
            $db = Database::getInstance();
            if ($db) {
                // Games count
                $stmt = $db->query("SELECT COUNT(*) FROM games WHERE is_active = 1");
                $stats['games'] = (int)$stmt->fetchColumn();

                // Materials count
                $stmt = $db->query("SELECT COUNT(*) FROM materials");
                $stats['materials'] = (int)$stmt->fetchColumn();

                // Boxes count
                $stmt = $db->query("SELECT COUNT(*) FROM boxes");
                $stats['boxes'] = (int)$stmt->fetchColumn();

                // Tags count
                $stmt = $db->query("SELECT COUNT(*) FROM tags");
                $stats['tags'] = (int)$stmt->fetchColumn();

                // Groups count
                $stmt = $db->query("SELECT COUNT(*) FROM groups");
                $stats['groups'] = (int)$stmt->fetchColumn();

                // Favorites count
                $stmt = $db->query("SELECT COUNT(*) FROM games WHERE is_favorite = 1 AND is_active = 1");
                $stats['favorites'] = (int)$stmt->fetchColumn();

                // Events this week
                $today = date('Y-m-d');
                $nextWeek = date('Y-m-d', strtotime('+7 days'));
                $stmt = $db->prepare("SELECT COUNT(*) FROM calendar_events WHERE start_date BETWEEN :today AND :next_week");
                $stmt->execute(['today' => $today, 'next_week' => $nextWeek]);
                $stats['events_this_week'] = (int)$stmt->fetchColumn();

                // Recent games
                $stmt = $db->query("
                    SELECT g.*, b.name as box_name
                    FROM games g
                    LEFT JOIN boxes b ON b.id = g.box_id
                    WHERE g.is_active = 1
                    ORDER BY g.created_at DESC
                    LIMIT 5
                ");
                $recentGames = $stmt->fetchAll();

                // Recent changes
                $stmt = $db->query("
                    SELECT c.*, u.username as user_name
                    FROM changelog c
                    LEFT JOIN users u ON u.id = c.user_id
                    ORDER BY c.created_at DESC
                    LIMIT 5
                ");
                $recentChanges = $stmt->fetchAll();

                // Upcoming events
                $stmt = $db->prepare("
                    SELECT ce.*, g.name as game_name
                    FROM calendar_events ce
                    LEFT JOIN games g ON g.id = ce.game_id
                    WHERE ce.start_date >= :today
                    ORDER BY ce.start_date ASC
                    LIMIT 5
                ");
                $stmt->execute(['today' => $today]);
                $upcomingEvents = $stmt->fetchAll();

                // Favorite games (for favorites section)
                require_once SRC_PATH . '/models/Game.php';
                $favoriteGames = Game::getFavorites(8);

                // Categories for random picker filter
                require_once SRC_PATH . '/models/Category.php';
                $categories = Category::getForSelect();

                // Tags for random picker filter
                require_once SRC_PATH . '/models/Tag.php';
                $tags = Tag::getForSelect();

                // Games played this month (from calendar events)
                require_once SRC_PATH . '/models/CalendarEvent.php';
                $stats['games_played_this_month'] = CalendarEvent::getGamesPlayedThisMonthCount();

                // Recently played games (from calendar)
                $recentlyPlayed = CalendarEvent::getRecentlyPlayed(5);
            }
        } catch (Exception $e) {
            // Ignore errors if tables don't exist yet
        }

        $this->render('dashboard/index', [
            'stats' => $stats,
            'recentGames' => $recentGames,
            'recentChanges' => $recentChanges,
            'upcomingEvents' => $upcomingEvents,
            'favoriteGames' => $favoriteGames,
            'recentlyPlayed' => $recentlyPlayed,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
