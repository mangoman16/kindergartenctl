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
