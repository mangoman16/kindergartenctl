<div class="page-header">
    <h1 class="page-title"><?= __('help.wizard_title') ?></h1>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <div id="help-wizard">
            <div class="help-step active" data-step="0">
                <div style="text-align: center; padding: 20px 0;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="1.5" style="margin: 0 auto 16px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <h2 style="margin-bottom: 8px;"><?= __('help.wizard_welcome') ?></h2>
                </div>
            </div>

            <?php
            $steps = [
                ['icon' => 'home', 'title' => __('help.step_dashboard'), 'text' => __('help.step_dashboard_text')],
                ['icon' => 'play', 'title' => __('help.step_games'), 'text' => __('help.step_games_text')],
                ['icon' => 'package', 'title' => __('help.step_materials'), 'text' => __('help.step_materials_text')],
                ['icon' => 'box', 'title' => __('help.step_boxes'), 'text' => __('help.step_boxes_text')],
                ['icon' => 'users', 'title' => __('help.step_categories'), 'text' => __('help.step_categories_text')],
                ['icon' => 'tag', 'title' => __('help.step_tags'), 'text' => __('help.step_tags_text')],
                ['icon' => 'calendar', 'title' => __('help.step_calendar'), 'text' => __('help.step_calendar_text')],
                ['icon' => 'folder', 'title' => __('help.step_groups'), 'text' => __('help.step_groups_text')],
                ['icon' => 'settings', 'title' => __('help.step_settings'), 'text' => __('help.step_settings_text')],
            ];
            foreach ($steps as $i => $step): ?>
            <div class="help-step" data-step="<?= $i + 1 ?>" style="display: none;">
                <div style="padding: 20px 0;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <span style="background: var(--color-primary-bg); color: var(--color-primary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem;"><?= $i + 1 ?></span>
                        <h2 style="margin: 0;"><?= e($step['title']) ?></h2>
                    </div>
                    <p style="font-size: 1rem; line-height: 1.6; color: var(--color-gray-600);"><?= e($step['text']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Progress -->
        <div style="margin-top: 16px;">
            <div style="display: flex; gap: 4px; margin-bottom: 16px;">
                <?php for ($i = 0; $i <= count($steps); $i++): ?>
                    <div class="help-progress-dot" data-dot="<?= $i ?>" style="flex: 1; height: 4px; border-radius: 2px; background: <?= $i === 0 ? 'var(--color-primary)' : 'var(--color-gray-200)' ?>;"></div>
                <?php endfor; ?>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" id="help-prev" style="visibility: hidden;"><?= __('action.back') ?></button>
                <span class="text-muted text-sm" id="help-counter">1 / <?= count($steps) + 1 ?></span>
                <button type="button" class="btn btn-primary" id="help-next"><?= __('action.next') ?></button>
            </div>
        </div>
    </div>
</div>

<script<?= cspNonce() ?>>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 0;
    const totalSteps = <?= count($steps) ?>;
    const prevBtn = document.getElementById('help-prev');
    const nextBtn = document.getElementById('help-next');
    const counter = document.getElementById('help-counter');

    function showStep(step) {
        document.querySelectorAll('.help-step').forEach(el => el.style.display = 'none');
        document.querySelector(`.help-step[data-step="${step}"]`).style.display = 'block';

        document.querySelectorAll('.help-progress-dot').forEach((dot, i) => {
            dot.style.background = i <= step ? 'var(--color-primary)' : 'var(--color-gray-200)';
        });

        prevBtn.style.visibility = step === 0 ? 'hidden' : 'visible';
        nextBtn.textContent = step === totalSteps ? '<?= __('help.finish') ?>' : '<?= __('action.next') ?>';
        counter.textContent = (step + 1) + ' / ' + (totalSteps + 1);
        currentStep = step;
    }

    prevBtn.addEventListener('click', () => { if (currentStep > 0) showStep(currentStep - 1); });
    nextBtn.addEventListener('click', () => {
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        } else {
            window.location.href = '<?= url('/settings') ?>';
        }
    });
});
</script>
