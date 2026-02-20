<div class="page-header">
    <h1 class="page-title"><?= __('settings.customization') ?></h1>
</div>

<div class="settings-page">
    <form action="<?= url('/settings/customization') ?>" method="POST">
        <?= csrfField() ?>

        <div class="settings-section">
            <h2 class="settings-section-title"><?= __('settings.theme_color') ?></h2>

            <?php
            $colors = [
                '#4F46E5', '#6366F1', '#8B5CF6', '#A855F7',
                '#EC4899', '#F43F5E', '#EF4444', '#F97316',
                '#F59E0B', '#EAB308', '#22C55E', '#10B981',
                '#14B8A6', '#06B6D4', '#3B82F6', '#6B7280',
            ];
            $currentColor = userPreference('theme_color', '#4F46E5');
            ?>

            <div class="color-picker-group">
                <div class="color-swatches">
                    <?php foreach ($colors as $color): ?>
                        <label class="color-swatch">
                            <input type="radio" name="theme_color" value="<?= $color ?>" <?= $currentColor === $color ? 'checked' : '' ?>>
                            <span class="color-swatch-circle" style="background: <?= $color ?>;">
                                <?php if ($currentColor === $color): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="color-custom">
                    <label class="form-label"><?= __('settings.custom_color') ?></label>
                    <div class="color-input-wrapper">
                        <input type="color" name="theme_color_picker" id="customColorPicker" value="<?= e($currentColor) ?>" class="color-input-native">
                        <input type="text" id="customColorText" value="<?= e($currentColor) ?>" class="form-control color-input-text" placeholder="#000000" maxlength="7">
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-section">
            <h2 class="settings-section-title"><?= __('settings.theme_pattern') ?></h2>

            <div class="pattern-picker">
                <?php
                $patterns = ['none', 'dots', 'stars', 'hearts', 'clouds', 'grid', 'waves'];
                $currentPattern = userPreference('theme_pattern', 'none');
                ?>
                <?php foreach ($patterns as $pattern): ?>
                    <label class="pattern-option <?= $currentPattern === $pattern ? 'active' : '' ?>">
                        <input type="radio" name="theme_pattern" value="<?= $pattern ?>" <?= $currentPattern === $pattern ? 'checked' : '' ?>>
                        <span class="pattern-preview" data-pattern="<?= $pattern ?>"></span>
                        <span class="pattern-label"><?= __('settings.pattern_' . $pattern) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="settings-section">
            <h2 class="settings-section-title"><?= __('settings.font_size') ?></h2>
            <p class="settings-hint"><?= __('settings.font_size_hint') ?></p>

            <?php $currentFontSize = userPreference('font_size', 'medium'); ?>
            <div class="font-size-picker">
                <?php foreach (['small', 'medium', 'large'] as $size): ?>
                    <label class="font-size-option <?= $currentFontSize === $size ? 'active' : '' ?>">
                        <input type="radio" name="font_size" value="<?= $size ?>" <?= $currentFontSize === $size ? 'checked' : '' ?>>
                        <span class="font-size-preview font-size-<?= $size ?>"><?= __('settings.font_' . $size) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="settings-section">
            <h2 class="settings-section-title"><?= __('settings.sidebar_mode') ?></h2>
            <p class="settings-hint"><?= __('settings.sidebar_mode_hint') ?></p>

            <?php $compactSidebar = userPreference('compact_sidebar', 'no'); ?>
            <div class="toggle-group">
                <label class="toggle-option <?= $compactSidebar === 'no' ? 'active' : '' ?>">
                    <input type="radio" name="compact_sidebar" value="no" <?= $compactSidebar === 'no' ? 'checked' : '' ?>>
                    <span><?= __('settings.sidebar_normal') ?></span>
                </label>
                <label class="toggle-option <?= $compactSidebar === 'yes' ? 'active' : '' ?>">
                    <input type="radio" name="compact_sidebar" value="yes" <?= $compactSidebar === 'yes' ? 'checked' : '' ?>>
                    <span><?= __('settings.sidebar_compact') ?></span>
                </label>
            </div>
        </div>

        <div class="settings-section">
            <h2 class="settings-section-title"><?= __('settings.default_page') ?></h2>
            <p class="settings-hint"><?= __('settings.default_page_hint') ?></p>

            <?php $defaultPage = userPreference('default_page', '/'); ?>
            <select name="default_page" class="form-control" style="max-width: 300px;">
                <option value="/" <?= $defaultPage === '/' ? 'selected' : '' ?>><?= __('nav.dashboard') ?></option>
                <option value="/games" <?= $defaultPage === '/games' ? 'selected' : '' ?>><?= __('nav.games') ?></option>
                <option value="/materials" <?= $defaultPage === '/materials' ? 'selected' : '' ?>><?= __('nav.materials') ?></option>
                <option value="/boxes" <?= $defaultPage === '/boxes' ? 'selected' : '' ?>><?= __('nav.boxes') ?></option>
                <option value="/calendar" <?= $defaultPage === '/calendar' ? 'selected' : '' ?>><?= __('nav.calendar') ?></option>
                <option value="/groups" <?= $defaultPage === '/groups' ? 'selected' : '' ?>><?= __('nav.groups') ?></option>
            </select>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><?= __('action.save') ?></button>
            <a href="<?= url('/settings') ?>" class="btn btn-secondary"><?= __('action.back') ?></a>
        </div>
    </form>
</div>

<script<?= cspNonce() ?>>
(function() {
    var picker = document.getElementById('customColorPicker');
    var textInput = document.getElementById('customColorText');
    var radios = document.querySelectorAll('input[name="theme_color"]');

    if (!picker || !textInput) return;

    picker.addEventListener('input', function() {
        textInput.value = this.value;
        radios.forEach(function(r) { r.checked = false; });
    });

    textInput.addEventListener('input', function() {
        var val = this.value.trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            picker.value = val;
            radios.forEach(function(r) { r.checked = false; });
        }
    });

    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                picker.value = this.value;
                textInput.value = this.value;
            }
        });
    });

    // On form submit, apply changes immediately via AJAX and update CSS live
    document.querySelector('.settings-page form').addEventListener('submit', function(e) {
        e.preventDefault();

        var checked = document.querySelector('input[name="theme_color"]:checked');
        var colorVal;
        if (checked) {
            colorVal = checked.value;
        } else {
            colorVal = textInput.value.trim();
            if (!(/^#[0-9A-Fa-f]{6}$/.test(colorVal))) {
                colorVal = picker.value;
            }
        }

        var patternChecked = document.querySelector('input[name="theme_pattern"]:checked');
        var patternVal = patternChecked ? patternChecked.value : 'none';

        // Apply changes immediately in browser
        document.documentElement.style.setProperty('--color-primary', colorVal);
        document.documentElement.style.setProperty('--color-primary-dark', colorVal + 'cc');
        document.documentElement.style.setProperty('--color-primary-light', colorVal + '88');
        document.documentElement.style.setProperty('--color-primary-bg', colorVal + '11');
        document.body.setAttribute('data-pattern', patternVal);

        // Apply font size
        var fontChecked = document.querySelector('input[name="font_size"]:checked');
        if (fontChecked) {
            document.documentElement.setAttribute('data-font-size', fontChecked.value);
        }

        // Submit form normally (server-side save)
        if (!checked) {
            var existing = this.querySelector('input[type="hidden"][name="theme_color"]');
            if (existing) existing.remove();
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'theme_color';
            hidden.value = colorVal;
            this.appendChild(hidden);
        }

        // Submit via fetch so we can control the redirect
        var formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData
        }).then(function() {
            // Show success feedback without full reload
            var btn = document.querySelector('.settings-actions .btn-primary');
            var originalText = btn.textContent;
            btn.textContent = '<?= __('settings.saved') ?>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.textContent = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }).catch(function() {
            // Fallback: submit normally
            e.target.submit();
        });
    });

    document.querySelectorAll('.pattern-option input').forEach(function(input) {
        input.addEventListener('change', function() {
            document.querySelectorAll('.pattern-option').forEach(function(o) { o.classList.remove('active'); });
            if (this.checked) this.closest('.pattern-option').classList.add('active');
        });
    });

    // Font size picker
    document.querySelectorAll('.font-size-option input').forEach(function(input) {
        input.addEventListener('change', function() {
            document.querySelectorAll('.font-size-option').forEach(function(o) { o.classList.remove('active'); });
            if (this.checked) this.closest('.font-size-option').classList.add('active');
        });
    });

    // Toggle group
    document.querySelectorAll('.toggle-group input').forEach(function(input) {
        input.addEventListener('change', function() {
            var group = this.closest('.toggle-group');
            group.querySelectorAll('.toggle-option').forEach(function(o) { o.classList.remove('active'); });
            if (this.checked) this.closest('.toggle-option').classList.add('active');
        });
    });
})();
</script>
