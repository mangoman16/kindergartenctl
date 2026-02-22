<!DOCTYPE html>
<?php $darkModePref = userPreference('dark_mode_preference', 'system'); ?>
<?php $currentLang = userPreference('language', 'de'); ?>
<?php $fontSize = userPreference('font_size', 'medium'); ?>
<html lang="<?= e($currentLang) ?>" data-theme="<?= e($darkModePref === 'dark' ? 'dark' : ($darkModePref === 'light' ? 'light' : 'light')) ?>" data-dark-mode-pref="<?= e($darkModePref) ?>" data-font-size="<?= e($fontSize) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'KindergartenOrganizer') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>" media="print">

    <!-- Cropper.js (for image uploads) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" integrity="sha384-PgJGpkxMIBLQdGLdWLsAxR8DNjunFr7RHulTxs/bJ/Ej2w3tpKhmv4dkvJVDnMx8" crossorigin="anonymous">

    <!-- Choices.js (for multi-selects) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css" integrity="sha384-a6Gc97sfYtY2RLuI0DRdWhlsFWvsFcj/+dCotZRJajCwx8/2cBBqn5k+FaatZ3cL" crossorigin="anonymous">

    <meta name="csrf-token" content="<?= e($csrfToken) ?>">

<?php
$themeColor = userPreference('theme_color', '#4F46E5');
$themePattern = userPreference('theme_pattern', 'none');
$fontSize = userPreference('font_size', 'medium');
$compactSidebar = userPreference('compact_sidebar', 'no');
?>
<script<?= cspNonce() ?>>
(function(){var p=document.documentElement.getAttribute('data-dark-mode-pref');if(p==='system'){var m=window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches;document.documentElement.setAttribute('data-theme',m?'dark':'light');}})();
</script>
<style<?= cspNonce() ?>>
:root {
    --color-primary: <?= e($themeColor) ?>;
    --color-primary-dark: <?= e($themeColor) ?>cc;
    --color-primary-light: <?= e($themeColor) ?>88;
    --color-primary-bg: <?= e($themeColor) ?>11;
}
</style>
</head>
<body data-pattern="<?= e($themePattern) ?>"<?= $compactSidebar === 'yes' ? ' data-compact-sidebar' : '' ?>>
    <div class="app-wrapper">
        <!-- Sidebar (Icon Rail + Context Sidebar) -->
        <?php include SRC_PATH . '/views/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <?php include SRC_PATH . '/views/partials/header.php'; ?>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                <?php include SRC_PATH . '/views/partials/flash-messages.php'; ?>

                <!-- Breadcrumbs -->
                <?php if (!empty($breadcrumbs)): ?>
                    <?php include SRC_PATH . '/views/partials/breadcrumbs.php'; ?>
                <?php endif; ?>

                <!-- Main Content -->
                <?= $content ?>
            </div>

            <!-- Footer -->
            <?php include SRC_PATH . '/views/partials/footer.php'; ?>
        </main>

        <!-- Help Panel -->
        <?php include SRC_PATH . '/views/partials/help-panel.php'; ?>
    </div>

    <!-- Scripts -->
    <script<?= cspNonce() ?>>
    window.AppTranslations = <?= json_encode([
        'confirm_default' => __('js.confirm_default'),
        'select_placeholder' => __('js.select_placeholder'),
        'no_results' => __('js.no_results'),
        'no_options' => __('js.no_options'),
        'click_to_select' => __('js.click_to_select'),
        'invalid_image_format' => __('js.invalid_image_format'),
        'image_too_large' => __('js.image_too_large'),
        'upload_error' => __('js.upload_error'),
        'action_close' => __('action.close'),
        'crop_title' => __('js.crop_title'),
        'crop_cancel' => __('js.crop_cancel'),
        'crop_apply' => __('js.crop_apply'),
        'crop_processing' => __('js.crop_processing'),
        'crop_error' => __('js.crop_error'),
        'duplicate_exists' => __('js.duplicate_exists'),
    ], JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" integrity="sha384-fNwJYJkLg8Rv5cLJMCqvIjVr5lLLrs6GRQj6EZnPh5FrxhYGdBmNK2gE2IYcj7VH" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js" integrity="sha384-5krGxfMNxZFfJ+gaoBjJYe6eUI7j27Qxz5tWkzDbVkKc4O8sNcjH5L16OypC7m9a" crossorigin="anonymous"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
