# Kindergarten Spiele Organizer

A web application for kindergarten teachers to organize games, materials, and resources.

## Requirements

- **PHP 8.0+** with extensions: `pdo_mysql`, `gd`, `mbstring`, `json`, `openssl`
- **MySQL 8.x** or **MariaDB 10.4+**
- **Apache** with `mod_rewrite` enabled (or nginx with equivalent config)

> **Note:** This application requires **no build tools**. No `composer install`, `npm install`, or `make` commands are needed. All dependencies are bundled or loaded via CDN.

---

## Installation

### Step 1: Download and Configure Web Server

1. **Clone or download the repository**:
   ```bash
   git clone https://github.com/mangoman16/kindergartenctl.git
   cd kindergartenctl
   ```

2. **Configure your web server** to point the document root to the `public/` directory.

### Step 2: Set Directory Permissions

```bash
# Create directories and set permissions
chmod -R 750 public/uploads storage temp src/config
chown -R www-data:www-data public/uploads storage temp src/config
```

> Replace `www-data` with your web server user (e.g., `apache`, `nginx`, `_www` on macOS).

### Step 3: Run the Installation Wizard

1. **Open your browser** and navigate to your installation URL (e.g., `http://localhost/` or your domain)

2. You will be automatically redirected to the **5-step installation wizard**:

   | Step | Description |
   |------|-------------|
   | **Step 1** | System requirements check (PHP version, extensions, directory permissions) |
   | **Step 2** | Database configuration (host, port, database name, username, password) |
   | **Step 3** | Admin user creation (username, email, password) |
   | **Step 4** | Email/SMTP configuration (optional - can be skipped) |
   | **Step 5** | Installation complete - redirects to login |

3. **Login** with the admin credentials you created in Step 3.

### Web Server Configuration Examples

#### Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName kindergarten.example.com
    DocumentRoot /path/to/kindergartenctl/public

    <Directory /path/to/kindergartenctl/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Recommended: Deny access to sensitive directories
    <Directory /path/to/kindergartenctl/src>
        Require all denied
    </Directory>
    <Directory /path/to/kindergartenctl/storage>
        Require all denied
    </Directory>
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name kindergarten.example.com;
    root /path/to/kindergartenctl/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive directories
    location ~ ^/(src|storage|temp|database)/ {
        deny all;
    }
}
```

---

## Updating

### Before You Update

1. **Create a backup** of your database and uploads:
   ```bash
   # Backup database
   mysqldump -u username -p kindergarten_organizer > backup_$(date +%Y%m%d).sql

   # Backup uploaded files
   cp -r public/uploads uploads_backup_$(date +%Y%m%d)

   # Backup configuration (optional)
   cp src/config/database.php database.php.backup
   ```

### Update Process

1. **Pull the latest changes**:
   ```bash
   git pull origin main
   ```

2. **Run database migrations** (if any):
   ```bash
   php database/migrate.php
   ```

3. **Clear cache** (if applicable):
   ```bash
   rm -rf storage/cache/*
   ```

4. **Verify permissions** are still correct:
   ```bash
   chmod -R 750 public/uploads storage temp src/config
   ```

### Rollback (if needed)

If something goes wrong:

1. **Restore the database**:
   ```bash
   mysql -u username -p kindergarten_organizer < backup_YYYYMMDD.sql
   ```

2. **Restore uploaded files**:
   ```bash
   rm -rf public/uploads
   cp -r uploads_backup_YYYYMMDD public/uploads
   ```

3. **Revert code changes**:
   ```bash
   git checkout <previous-commit-hash>
   ```

---

## Troubleshooting

### Installation Issues

| Problem | Solution |
|---------|----------|
| **Blank page / 500 error** | Check PHP error logs. Ensure PHP 8.0+ is installed. |
| **404 on all routes** | Enable `mod_rewrite` (Apache) or check nginx `try_files` config. |
| **"Directory not writable"** | Run `chmod -R 750` and `chown` commands from Step 2. |
| **Database connection failed** | Verify MySQL is running and credentials are correct. |
| **Config file not writable** | Ensure `src/config/` directory is writable by web server. |

### Common Runtime Issues

| Problem | Solution |
|---------|----------|
| **Session errors** | Check `storage/` directory permissions. |
| **Image upload fails** | Verify `public/uploads/` is writable and GD extension is installed. |
| **Email not sending** | Configure SMTP settings in Settings > Email Configuration. |

### Checking PHP Requirements

```bash
# Check PHP version
php -v

# Check installed extensions
php -m | grep -E 'pdo_mysql|gd|mbstring|json|openssl'
```

---

## Directory Structure

```
kindergartenctl/
├── public/              # Web-accessible files (document root)
│   ├── index.php        # Application entry point
│   ├── assets/          # CSS, JavaScript, images
│   └── uploads/         # User-uploaded files
├── src/
│   ├── config/          # Configuration files (generated during install)
│   ├── controllers/     # Application controllers
│   ├── core/            # Core framework classes
│   ├── helpers/         # Helper functions
│   ├── models/          # Database models
│   └── views/           # View templates
├── storage/
│   ├── cache/           # Application cache
│   └── logs/            # Log files
├── database/            # Database migrations
├── temp/                # Temporary files
└── installed.lock       # Created after successful installation
```

---

## Re-installation

To completely reinstall the application:

1. **Delete the lock file**:
   ```bash
   rm installed.lock
   ```

2. **Drop the database** (optional - for clean install):
   ```bash
   mysql -u username -p -e "DROP DATABASE kindergarten_organizer;"
   ```

3. **Delete configuration** (optional):
   ```bash
   rm src/config/database.php
   ```

4. **Open the application** in your browser to restart the installation wizard.

---

## Features

- **Games Management** - Organize educational games with metadata (age range, difficulty, players, duration)
- **Materials Tracking** - Track physical materials/components and their storage locations
- **Box/Storage System** - Organize materials into physical storage containers
- **Age Group Categories** - Categorize games by age groups (2-3, 3-4, 4-5, 5-6 years)
- **Tagging System** - Add themes/tags (colors, seasons, holidays) to games
- **Collections/Groups** - Create custom collections of games and materials
- **Calendar** - Schedule game usage and track past activities
- **Search** - Fulltext search across all entities
- **Print Views** - Print games, materials, boxes, groups, and checklists
- **Dashboard** - Statistics, quick actions, recent activity, random game picker
- **Audit Logging** - Track all changes via changelog

## License

Apache License 2.0 - See [LICENSE](LICENSE) file for details.
