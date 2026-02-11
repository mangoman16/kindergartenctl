<!DOCTYPE html>
<html lang="de">
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
?>
<style<?= cspNonce() ?>>
:root {
    --color-primary: <?= e($themeColor) ?>;
    --color-primary-dark: <?= e($themeColor) ?>cc;
    --color-primary-light: <?= e($themeColor) ?>88;
    --color-primary-bg: <?= e($themeColor) ?>11;
}
</style>
</head>
<body data-pattern="<?= e($themePattern) ?>">
    <div class="app-wrapper">
        <!-- Sidebar -->
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
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" integrity="sha384-fNwJYJkLg8Rv5cLJMCqvIjVr5lLLrs6GRQj6EZnPh5FrxhYGdBmNK2gE2IYcj7VH" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js" integrity="sha384-5krGxfMNxZFfJ+gaoBjJYe6eUI7j27Qxz5tWkzDbVkKc4O8sNcjH5L16OypC7m9a" crossorigin="anonymous"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
