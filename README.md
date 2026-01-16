# Kindergarten Spiele Organizer

A web application for kindergarten teachers to organize games, materials, and resources.

## System Requirements

- **PHP 8.0+** with extensions: `pdo_mysql`, `gd`, `mbstring`, `json`, `openssl`
- **MySQL 8.x** or MariaDB 10.4+
- **Apache** with `mod_rewrite` enabled (or nginx)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/kindergartenctl.git
   cd kindergartenctl
   ```

2. Set directory permissions:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   chmod -R 775 public/uploads storage temp
   ```

3. Configure your web server to point DocumentRoot to the `public/` directory

4. Open your browser and follow the installation wizard

### Shared Hosting

If you can't change DocumentRoot, upload everything to `public_html/` - the root `.htaccess` will route requests to `public/`.

## Updating

When the repository has updates:

```bash
# 1. Backup your data
mysqldump -u username -p kindergarten_organizer > backup.sql
cp -r public/uploads uploads_backup
cp src/config/database.php database.php.backup

# 2. Pull latest changes
git pull origin main

# 3. Restore your config
cp database.php.backup src/config/database.php

# 4. Check for database migrations
# Look in database/ folder for new migration files and run them:
mysql -u username -p kindergarten_organizer < database/migrations/xxx.sql

# 5. Clear cache
rm -rf storage/cache/*
```

### Update Checklist

- [ ] Backup database
- [ ] Backup uploaded files (`public/uploads/`)
- [ ] Backup config (`src/config/database.php`)
- [ ] Pull changes
- [ ] Run any new database migrations
- [ ] Clear cache
- [ ] Test the application

## Troubleshooting

**403 Forbidden**
- Ensure `mod_rewrite` is enabled
- Check file permissions (644 for files, 755 for directories)
- On shared hosting: verify the root `.htaccess` exists

**500 Internal Server Error**
- Check PHP version is 8.0+
- Check Apache error logs

**Database connection failed**
- Verify credentials in `src/config/database.php`

## License

MIT License
