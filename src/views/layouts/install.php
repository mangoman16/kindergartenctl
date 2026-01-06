<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle ?? 'Installation') ?> - Kindergarten Spiele Organizer</title>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <div class="install-wrapper">
        <div class="install-card">
            <div class="install-header">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem;">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <h1>Kindergarten Spiele Organizer</h1>
                <p><?= __('install.title') ?></p>
            </div>

            <?php if (isset($currentStep)): ?>
            <div class="install-steps">
                <?php
                $steps = [
                    1 => 'Voraussetzungen',
                    2 => 'Datenbank',
                    3 => 'Administrator',
                    4 => 'E-Mail',
                    5 => 'Fertig'
                ];
                foreach ($steps as $num => $label):
                    $class = '';
                    if ($num < $currentStep) $class = 'completed';
                    if ($num == $currentStep) $class = 'active';
                ?>
                <div class="install-step <?= $class ?>">
                    <span class="install-step-number">
                        <?php if ($num < $currentStep): ?>
                            &#10003;
                        <?php else: ?>
                            <?= $num ?>
                        <?php endif; ?>
                    </span>
                    <span class="install-step-label"><?= e($label) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="install-body">
                <!-- Flash Messages -->
                <?php
                $flashMessages = Session::getFlash();
                if (!empty($flashMessages)):
                    foreach ($flashMessages as $type => $message):
                ?>
                <div class="alert alert-<?= e($type) ?>">
                    <?= e($message) ?>
                </div>
                <?php
                    endforeach;
                endif;
                ?>

                <!-- Content -->
                <?= $content ?>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
