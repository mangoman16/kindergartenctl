<?php
/**
 * Route Definitions
 */

return [
    // Installation routes (no auth required)
    'GET /install' => ['InstallController', 'index'],
    'GET /install/step1' => ['InstallController', 'step1'],
    'GET /install/step2' => ['InstallController', 'step2'],
    'POST /install/step2' => ['InstallController', 'testConnection'],
    'POST /install/step2/save' => ['InstallController', 'saveDatabase'],
    'GET /install/step3' => ['InstallController', 'step3'],
    'POST /install/step3' => ['InstallController', 'createAdmin'],
    'GET /install/step4' => ['InstallController', 'step4'],
    'POST /install/step4' => ['InstallController', 'saveEmail'],
    'POST /install/step4/skip' => ['InstallController', 'skipEmail'],
    'GET /install/complete' => ['InstallController', 'complete'],

    // Authentication routes (no auth required)
    'GET /login' => ['AuthController', 'showLogin'],
    'POST /login' => ['AuthController', 'login'],
    'GET /logout' => ['AuthController', 'logout'],
    'GET /forgot-password' => ['AuthController', 'showForgotPassword'],
    'POST /forgot-password' => ['AuthController', 'sendResetLink'],
    'GET /reset-password' => ['AuthController', 'showResetPassword'],
    'POST /reset-password' => ['AuthController', 'resetPassword'],

    // Dashboard (auth required)
    'GET /' => ['DashboardController', 'index'],
    'GET /dashboard' => ['DashboardController', 'index'],

    // Games
    'GET /games' => ['GameController', 'index'],
    'GET /games/create' => ['GameController', 'create'],
    'POST /games' => ['GameController', 'store'],
    'GET /games/{id}' => ['GameController', 'show'],
    'GET /games/{id}/edit' => ['GameController', 'edit'],
    'POST /games/{id}' => ['GameController', 'update'],
    'POST /games/{id}/delete' => ['GameController', 'delete'],
    'GET /games/{id}/print' => ['GameController', 'print'],

    // Materials
    'GET /materials' => ['MaterialController', 'index'],
    'GET /materials/create' => ['MaterialController', 'create'],
    'POST /materials' => ['MaterialController', 'store'],
    'GET /materials/{id}' => ['MaterialController', 'show'],
    'GET /materials/{id}/edit' => ['MaterialController', 'edit'],
    'POST /materials/{id}' => ['MaterialController', 'update'],
    'POST /materials/{id}/delete' => ['MaterialController', 'delete'],
    'GET /materials/{id}/print' => ['MaterialController', 'print'],

    // Boxes
    'GET /boxes' => ['BoxController', 'index'],
    'GET /boxes/create' => ['BoxController', 'create'],
    'POST /boxes' => ['BoxController', 'store'],
    'GET /boxes/{id}' => ['BoxController', 'show'],
    'GET /boxes/{id}/edit' => ['BoxController', 'edit'],
    'POST /boxes/{id}' => ['BoxController', 'update'],
    'POST /boxes/{id}/delete' => ['BoxController', 'delete'],
    'GET /boxes/{id}/print' => ['BoxController', 'print'],

    // Categories
    'GET /categories' => ['CategoryController', 'index'],
    'GET /categories/create' => ['CategoryController', 'create'],
    'POST /categories' => ['CategoryController', 'store'],
    'GET /categories/{id}/edit' => ['CategoryController', 'edit'],
    'POST /categories/{id}' => ['CategoryController', 'update'],
    'POST /categories/{id}/delete' => ['CategoryController', 'delete'],

    // Tags
    'GET /tags' => ['TagController', 'index'],
    'GET /tags/create' => ['TagController', 'create'],
    'POST /tags' => ['TagController', 'store'],
    'GET /tags/{id}/edit' => ['TagController', 'edit'],
    'POST /tags/{id}' => ['TagController', 'update'],
    'POST /tags/{id}/delete' => ['TagController', 'delete'],

    // Groups
    'GET /groups' => ['GroupController', 'index'],
    'GET /groups/create' => ['GroupController', 'create'],
    'POST /groups' => ['GroupController', 'store'],
    'GET /groups/{id}' => ['GroupController', 'show'],
    'GET /groups/{id}/edit' => ['GroupController', 'edit'],
    'POST /groups/{id}' => ['GroupController', 'update'],
    'POST /groups/{id}/delete' => ['GroupController', 'delete'],

    // Calendar
    'GET /calendar' => ['CalendarController', 'index'],

    // Search
    'GET /search' => ['SearchController', 'index'],

    // Changelog
    'GET /changelog' => ['ChangelogController', 'index'],
    'POST /changelog/clear' => ['ChangelogController', 'clear'],

    // Settings
    'GET /settings' => ['SettingsController', 'index'],
    'POST /settings/password' => ['SettingsController', 'updatePassword'],
    'POST /settings/email' => ['SettingsController', 'updateEmail'],
    'POST /settings/smtp' => ['SettingsController', 'updateSmtp'],
    'POST /settings/smtp/test' => ['SettingsController', 'testSmtp'],
    'POST /settings/unban' => ['SettingsController', 'unbanIp'],
    'POST /settings/ban' => ['SettingsController', 'banIp'],
    'POST /settings/clear-temp' => ['SettingsController', 'clearTemp'],

    // API routes
    'POST /api/upload-image' => ['ApiController', 'uploadImage'],
    'POST /api/games/toggle-favorite' => ['ApiController', 'toggleGameFavorite'],
    'POST /api/materials/toggle-favorite' => ['ApiController', 'toggleMaterialFavorite'],
    'POST /api/games/check-duplicate' => ['ApiController', 'checkGameDuplicate'],
    'POST /api/materials/check-duplicate' => ['ApiController', 'checkMaterialDuplicate'],
    'POST /api/boxes/check-duplicate' => ['ApiController', 'checkBoxDuplicate'],
    'POST /api/tags/check-duplicate' => ['ApiController', 'checkTagDuplicate'],
    'POST /api/tags/quick-create' => ['ApiController', 'quickCreateTag'],
    'GET /api/search' => ['ApiController', 'search'],
    'GET /api/games/random' => ['ApiController', 'randomGame'],
    'GET /api/calendar/events' => ['ApiController', 'getCalendarEvents'],
    'POST /api/calendar/events' => ['ApiController', 'createCalendarEvent'],
    'PUT /api/calendar/events/{id}' => ['ApiController', 'updateCalendarEvent'],
    'DELETE /api/calendar/events/{id}' => ['ApiController', 'deleteCalendarEvent'],
    'POST /api/groups/add-item' => ['ApiController', 'addGroupItem'],
    'DELETE /api/groups/remove-item' => ['ApiController', 'removeGroupItem'],
];
