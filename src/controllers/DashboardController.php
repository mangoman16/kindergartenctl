<?php
/**
 * Dashboard Controller
 */

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $this->requireAuth();

        $this->setTitle(__('dashboard.title'));

        // Get statistics (will be populated when models exist)
        $stats = [
            'games' => 0,
            'materials' => 0,
            'boxes' => 0,
            'favorites' => 0,
        ];

        // Try to load stats if database is configured
        try {
            $db = Database::getInstance();
            if ($db) {
                // Games count
                $stmt = $db->query("SELECT COUNT(*) FROM games");
                $stats['games'] = (int)$stmt->fetchColumn();

                // Materials count
                $stmt = $db->query("SELECT COUNT(*) FROM materials");
                $stats['materials'] = (int)$stmt->fetchColumn();

                // Boxes count
                $stmt = $db->query("SELECT COUNT(*) FROM boxes");
                $stats['boxes'] = (int)$stmt->fetchColumn();

                // Favorites count
                $stmt = $db->query("SELECT COUNT(*) FROM games WHERE is_favorite = 1");
                $stats['favorites'] = (int)$stmt->fetchColumn();
            }
        } catch (Exception $e) {
            // Ignore errors if tables don't exist yet
        }

        $this->render('dashboard/index', [
            'stats' => $stats,
        ]);
    }
}
