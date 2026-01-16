<?php
/**
 * Landing Page Controller
 * Serves the public-facing landing page for unauthenticated users
 */

class LandingController extends Controller
{
    public function __construct()
    {
        // No authentication required for landing page
        // No layout - landing page has its own complete HTML
    }

    /**
     * Show landing page
     * Redirects to dashboard if user is already logged in
     */
    public function index(): void
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }

        // Show landing page
        include SRC_PATH . '/views/landing.php';
    }
}
