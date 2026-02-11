<div class="page-header">
    <h1 class="page-title"><?= __('settings.language') ?></h1>
</div>

<div class="settings-page">
    <form action="<?= url('/settings/language') ?>" method="POST">
        <?= csrfField() ?>

        <div class="settings-section">
            <div class="language-options">
                <?php $currentLang = userPreference('language', 'de'); ?>
                <label class="language-option <?= $currentLang === 'de' ? 'active' : '' ?>">
                    <input type="radio" name="language" value="de" <?= $currentLang === 'de' ? 'checked' : '' ?>>
                    <span class="language-option-flag">DE</span>
                    <span class="language-option-name">Deutsch</span>
                </label>
                <label class="language-option <?= $currentLang === 'en' ? 'active' : '' ?>">
                    <input type="radio" name="language" value="en" <?= $currentLang === 'en' ? 'checked' : '' ?>>
                    <span class="language-option-flag">EN</span>
                    <span class="language-option-name">English</span>
                </label>
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            <a href="<?= url('/settings') ?>" class="btn btn-secondary"><?= __('action.back') ?></a>
        </div>
    </form>
</div>

<script<?= cspNonce() ?>>
(function() {
    document.querySelectorAll('.language-option input').forEach(function(input) {
        input.addEventListener('change', function() {
            document.querySelectorAll('.language-option').forEach(function(o) { o.classList.remove('active'); });
            if (this.checked) this.closest('.language-option').classList.add('active');
        });
    });
})();
</script>
