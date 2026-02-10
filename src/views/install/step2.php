<h2><?= __('install.database') ?></h2>
<p class="text-muted mb-6">Geben Sie die Datenbankverbindungsdaten ein</p>

<form action="<?= url('/install/step2/save') ?>" method="POST" id="dbForm">
    <div class="form-row">
        <div class="form-group">
            <label for="host" class="form-label">
                <?= __('install.db_host') ?> <span class="required">*</span>
            </label>
            <input type="text" id="host" name="host" class="form-control <?= hasError('host', $errors ?? []) ? 'is-invalid' : '' ?>"
                   value="<?= old('host', 'localhost') ?>" required>
            <?php if (hasError('host', $errors ?? [])): ?>
                <div class="form-error"><?= getError('host', $errors) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="port" class="form-label">
                <?= __('install.db_port') ?>
            </label>
            <input type="number" id="port" name="port" class="form-control"
                   value="<?= old('port', '3306') ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="database" class="form-label">
            <?= __('install.db_name') ?> <span class="required">*</span>
        </label>
        <input type="text" id="database" name="database" class="form-control <?= hasError('database', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('database', 'kindergarten') ?>" required>
        <?php if (hasError('database', $errors ?? [])): ?>
            <div class="form-error"><?= getError('database', $errors) ?></div>
        <?php endif; ?>
        <div class="form-hint">Die Datenbank wird erstellt, falls sie nicht existiert.</div>
    </div>

    <div class="form-group">
        <label for="username" class="form-label">
            <?= __('install.db_user') ?> <span class="required">*</span>
        </label>
        <input type="text" id="username" name="username" class="form-control <?= hasError('username', $errors ?? []) ? 'is-invalid' : '' ?>"
               value="<?= old('username') ?>" required>
        <?php if (hasError('username', $errors ?? [])): ?>
            <div class="form-error"><?= getError('username', $errors) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">
            <?= __('install.db_password') ?>
        </label>
        <input type="password" id="password" name="password" class="form-control"
               value="<?= old('password') ?>">
    </div>

    <div class="form-group">
        <button type="button" id="testConnection" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <?= __('install.test_connection') ?>
        </button>
        <span id="testResult" class="ml-4"></span>
    </div>

    <div class="install-footer">
        <a href="<?= url('/install/step1') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?= __('install.back') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?= __('install.next') ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</form>

<script<?= cspNonce() ?>>
document.getElementById('testConnection').addEventListener('click', function() {
    const form = document.getElementById('dbForm');
    const formData = new FormData(form);
    const resultSpan = document.getElementById('testResult');

    resultSpan.textContent = 'Teste...';
    resultSpan.style.color = '';

    fetch('<?= url('/install/step2') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        resultSpan.textContent = data.message;
        resultSpan.style.color = data.success ? 'var(--color-success)' : 'var(--color-danger)';
    })
    .catch(error => {
        resultSpan.textContent = 'Fehler beim Testen der Verbindung';
        resultSpan.style.color = 'var(--color-danger)';
    });
});
</script>
