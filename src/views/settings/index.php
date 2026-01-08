<div class="page-header">
    <h1 class="page-title"><?= __('settings.title') ?></h1>
</div>

<div class="grid grid-cols-2 gap-4">
    <!-- Profile Settings -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.profile') ?></h2>
        </div>
        <div class="card-body">
            <dl class="detail-list">
                <dt><?= __('form.name') ?></dt>
                <dd><?= e($user['name']) ?></dd>

                <dt><?= __('form.email') ?></dt>
                <dd><?= e($user['email']) ?></dd>

                <dt>Registriert seit</dt>
                <dd><?= formatDate($user['created_at'], 'd.m.Y') ?></dd>
            </dl>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.change_password') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/password') ?>" method="POST">
                <?= csrfField() ?>

                <div class="form-group">
                    <label for="current_password" class="form-label"><?= __('settings.current_password') ?></label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label"><?= __('settings.new_password') ?></label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8">
                    <div class="form-hint">Mindestens 8 Zeichen</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label"><?= __('settings.confirm_password') ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= __('settings.change_password') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Change Email -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.change_email') ?></h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/settings/email') ?>" method="POST">
                <?= csrfField() ?>

                <div class="form-group">
                    <label for="email" class="form-label"><?= __('settings.new_email') ?></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= e($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="password_email" class="form-label"><?= __('form.password') ?></label>
                    <input type="password" id="password_email" name="password" class="form-control" required>
                    <div class="form-hint">Zur Bestätigung</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= __('settings.change_email') ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Storage Info -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('settings.storage') ?></h2>
        </div>
        <div class="card-body">
            <dl class="detail-list">
                <dt>Uploads</dt>
                <dd><?= $uploadsSize ?></dd>

                <dt>Temporäre Dateien</dt>
                <dd><?= $tempSize ?></dd>
            </dl>

            <form action="<?= url('/settings/clear-temp') ?>" method="POST" class="mt-4"
                  onsubmit="return confirm('Temporäre Dateien wirklich löschen?')">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Temp-Dateien löschen
                </button>
            </form>
        </div>
    </div>
</div>

<!-- SMTP / Email Settings -->
<div class="card mt-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('settings.email') ?></h2>
    </div>
    <div class="card-body">
        <form action="<?= url('/settings/smtp') ?>" method="POST">
            <?= csrfField() ?>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="smtp_host" class="form-label"><?= __('settings.smtp_host') ?></label>
                    <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                           value="<?= e($smtpConfig['smtp_host'] ?? '') ?>" placeholder="smtp.example.com">
                </div>

                <div class="form-group">
                    <label for="smtp_port" class="form-label"><?= __('settings.smtp_port') ?></label>
                    <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                           value="<?= e($smtpConfig['smtp_port'] ?? 587) ?>">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="smtp_user" class="form-label"><?= __('settings.smtp_username') ?></label>
                    <input type="text" id="smtp_user" name="smtp_user" class="form-control"
                           value="<?= e($smtpConfig['smtp_user'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="smtp_pass" class="form-label"><?= __('settings.smtp_password') ?></label>
                    <input type="password" id="smtp_pass" name="smtp_pass" class="form-control"
                           placeholder="<?= !empty($smtpConfig['smtp_pass']) ? '••••••••' : '' ?>">
                    <div class="form-hint">Leer lassen um bestehendes Passwort zu behalten</div>
                </div>
            </div>

            <div class="form-group">
                <label for="smtp_encryption" class="form-label"><?= __('settings.smtp_encryption') ?></label>
                <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                    <option value="tls" <?= ($smtpConfig['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= ($smtpConfig['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="smtp_from" class="form-label"><?= __('settings.smtp_from_email') ?></label>
                    <input type="email" id="smtp_from" name="smtp_from" class="form-control"
                           value="<?= e($smtpConfig['smtp_from'] ?? '') ?>" placeholder="noreply@example.com">
                </div>

                <div class="form-group">
                    <label for="smtp_from_name" class="form-label"><?= __('settings.smtp_from_name') ?></label>
                    <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control"
                           value="<?= e($smtpConfig['smtp_from_name'] ?? 'Kindergarten Spiele Organizer') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Speichern
            </button>
        </form>

        <hr class="my-4">

        <!-- Test SMTP -->
        <form action="<?= url('/settings/smtp/test') ?>" method="POST" class="flex gap-3 items-end">
            <?= csrfField() ?>
            <div class="form-group mb-0 flex-1">
                <label class="form-label"><?= __('settings.smtp_test') ?></label>
                <input type="email" name="test_email" class="form-control"
                       value="<?= e($user['email'] ?? '') ?>" placeholder="test@example.com" required>
            </div>
            <button type="submit" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Test-E-Mail senden
            </button>
        </form>
    </div>
</div>

<!-- IP Bans -->
<div class="card mt-4">
    <div class="card-header">
        <h2 class="card-title"><?= __('settings.ip_bans') ?></h2>
    </div>
    <div class="card-body">
        <!-- Add new ban -->
        <form action="<?= url('/settings/ban') ?>" method="POST" class="flex gap-3 items-end mb-4">
            <?= csrfField() ?>
            <div class="form-group mb-0">
                <label class="form-label">IP-Adresse</label>
                <input type="text" name="ip" class="form-control" placeholder="z.B. 192.168.1.100" required
                       pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
            </div>
            <div class="form-group mb-0 flex-1">
                <label class="form-label">Grund</label>
                <input type="text" name="reason" class="form-control" placeholder="Optional">
            </div>
            <button type="submit" class="btn btn-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
                IP sperren
            </button>
        </form>

        <?php if (empty($bans)): ?>
            <p class="text-muted">Keine IP-Sperren aktiv.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>IP-Adresse</th>
                        <th>Grund</th>
                        <th>Fehlversuche</th>
                        <th>Gesperrt am</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bans as $ban): ?>
                    <tr>
                        <td><code><?= e($ban['ip_address']) ?></code></td>
                        <td><?= e($ban['reason'] ?? '-') ?></td>
                        <td><?= $ban['failed_attempts'] ?? 0 ?></td>
                        <td class="text-muted"><?= formatDate($ban['created_at'], 'd.m.Y H:i') ?></td>
                        <td>
                            <form action="<?= url('/settings/unban') ?>" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="ip" value="<?= e($ban['ip_address']) ?>">
                                <button type="submit" class="btn btn-sm btn-secondary">Entsperren</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
