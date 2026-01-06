<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Drucken') ?> - Kindergarten Spiele Organizer</title>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">

    <style>
        @media screen {
            body {
                max-width: 800px;
                margin: 2rem auto;
                padding: 2rem;
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div class="print-title"><?= e($printTitle ?? $pageTitle ?? '') ?></div>
        <div class="print-date"><?= formatDateGerman(new DateTime(), 'full') ?></div>
    </div>

    <?= $content ?>

    <div class="print-footer">
        <p>Kindergarten Spiele Organizer</p>
    </div>

    <?php if (!isset($autoPrint) || $autoPrint): ?>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    <?php endif; ?>
</body>
</html>
