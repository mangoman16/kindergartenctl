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
    'POST /logout' => ['AuthController', 'logout'],
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
    'POST /games/{id}/duplicate' => ['GameController', 'duplicate'],

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
    'GET /categories/{id}/print' => ['CategoryController', 'print'],

    // Tags
    'GET /tags' => ['TagController', 'index'],
    'GET /tags/create' => ['TagController', 'create'],
    'POST /tags' => ['TagController', 'store'],
    'GET /tags/{id}/edit' => ['TagController', 'edit'],
    'POST /tags/{id}' => ['TagController', 'update'],
    'POST /tags/{id}/delete' => ['TagController', 'delete'],
    'GET /tags/{id}/print' => ['TagController', 'print'],

    // Groups
    'GET /groups' => ['GroupController', 'index'],
    'GET /groups/create' => ['GroupController', 'create'],
    'POST /groups' => ['GroupController', 'store'],
    'GET /groups/{id}' => ['GroupController', 'show'],
    'GET /groups/{id}/edit' => ['GroupController', 'edit'],
    'POST /groups/{id}' => ['GroupController', 'update'],
    'POST /groups/{id}/delete' => ['GroupController', 'delete'],
    'GET /groups/{id}/print' => ['GroupController', 'print'],
    'GET /groups/{id}/print-checklist' => ['GroupController', 'printChecklist'],

    // Calendar
    'GET /calendar' => ['CalendarController', 'index'],

    // Search
    'GET /search' => ['SearchController', 'index'],

    // Changelog
    'GET /changelog' => ['ChangelogController', 'index'],
    'POST /changelog/clear' => ['ChangelogController', 'clear'],

    // Settings
    'GET /settings' => ['SettingsController', 'index'],
    'GET /settings/customization' => ['SettingsController', 'showCustomization'],
    'GET /settings/language' => ['SettingsController', 'showLanguage'],
    'GET /settings/email' => ['SettingsController', 'showEmail'],
    'GET /settings/debug' => ['SettingsController', 'showDebug'],
    'GET /settings/data' => ['SettingsController', 'showData'],
    'GET /settings/help' => ['SettingsController', 'help'],
    'POST /settings/password' => ['SettingsController', 'updatePassword'],
    'POST /settings/email' => ['SettingsController', 'updateEmail'],
    'POST /settings/preferences' => ['SettingsController', 'updatePreferences'],
    'POST /settings/smtp' => ['SettingsController', 'updateSmtp'],
    'POST /settings/smtp/test' => ['SettingsController', 'testSmtp'],
    'POST /settings/unban' => ['SettingsController', 'unbanIp'],
    'POST /settings/ban' => ['SettingsController', 'banIp'],
    'POST /settings/clear-temp' => ['SettingsController', 'clearTemp'],
    'POST /settings/language' => ['SettingsController', 'updateLanguage'],
    'POST /settings/customization' => ['SettingsController', 'updateCustomization'],
    'POST /settings/debug' => ['SettingsController', 'toggleDebug'],
    'POST /settings/dark-mode' => ['SettingsController', 'toggleDarkMode'],

    // User settings / profile
    'GET /user/settings' => ['SettingsController', 'userSettings'],
    'POST /user/settings/password' => ['SettingsController', 'updatePassword'],
    'POST /user/settings/email' => ['SettingsController', 'updateEmail'],
    'POST /user/settings/language' => ['SettingsController', 'updateUserLanguage'],
    'POST /user/settings/create-user' => ['SettingsController', 'createUser'],
    'POST /user/settings/delete-user' => ['SettingsController', 'deleteUser'],

    // API routes
    'GET /api/health' => ['ApiController', 'health'],

    // Image upload
    'POST /api/upload-image' => ['ApiController', 'uploadImage'],
    'POST /api/delete-image' => ['ApiController', 'deleteImage'],

    // Duplicate checking
    'GET /api/check-duplicate' => ['ApiController', 'checkDuplicate'],

    // Search / autocomplete
    'GET /api/search' => ['ApiController', 'liveSearch'],
    'GET /api/tags/search' => ['ApiController', 'searchTags'],
    'GET /api/materials/search' => ['ApiController', 'searchMaterials'],
    'GET /api/games/search' => ['ApiController', 'searchGames'],

    // Quick create
    'POST /api/tags/quick-create' => ['ApiController', 'quickCreateTag'],
    'POST /api/materials/quick-create' => ['ApiController', 'quickCreateMaterial'],

    // Dropdown data
    'GET /api/boxes' => ['ApiController', 'getBoxes'],
    'GET /api/categories' => ['ApiController', 'getCategories'],
    'GET /api/tags' => ['ApiController', 'getTags'],
    'GET /api/materials' => ['ApiController', 'getMaterials'],

    // Entity games
    'GET /api/boxes/{id}/games' => ['ApiController', 'getBoxGames'],
    'GET /api/categories/{id}/games' => ['ApiController', 'getCategoryGames'],
    'GET /api/tags/{id}/games' => ['ApiController', 'getTagGames'],

    // Calendar events
    'GET /api/calendar/events' => ['CalendarController', 'getEvents'],
    'POST /api/calendar/events' => ['CalendarController', 'store'],
    'PUT /api/calendar/events/{id}' => ['CalendarController', 'update'],
    'DELETE /api/calendar/events/{id}' => ['CalendarController', 'delete'],

    // Favorites toggle
    'POST /api/games/{id}/toggle-favorite' => ['ApiController', 'toggleGameFavorite'],
    'POST /api/materials/{id}/toggle-favorite' => ['ApiController', 'toggleMaterialFavorite'],

    // Random game
    'GET /api/games/random' => ['ApiController', 'getRandomGame'],

    // Group items management
    'GET /api/groups' => ['ApiController', 'getGroups'],
    'POST /api/groups/add-item' => ['ApiController', 'addItemToGroup'],
    'POST /api/groups/remove-item' => ['ApiController', 'removeItemFromGroup'],
];
