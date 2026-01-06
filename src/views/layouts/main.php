<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'Kindergarten Spiele Organizer') ?></title>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>" media="print">

    <!-- Cropper.js (for image uploads) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">

    <!-- Choices.js (for multi-selects) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">

    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
</head>
<body>
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
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
