# Kindergarten Spiele Organizer

A web application for kindergarten teachers to organize games, materials, and resources.

## Requirements

- PHP 8.0+ with extensions: `pdo_mysql`, `gd`, `mbstring`, `json`, `openssl`
- MySQL 8.x or MariaDB 10.4+
- Apache with `mod_rewrite` enabled (or nginx)

## Installation

### Quick Installation

1. **Download or clone the repository**:
   ```bash
   git clone https://github.com/yourusername/kindergartenctl.git
   cd kindergartenctl
   ```

2. **Configure your web server** to point to the `public/` directory

3. **Set directory permissions**:
   ```bash
   chmod -R 775 public/uploads storage temp
   ```

4. **Open your browser** and navigate to your installation URL

5. **Follow the installation wizard** (4 steps):
   - Requirements check
   - Database configuration
   - Admin user creation
   - SMTP configuration (optional)

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName kindergarten.example.com
    DocumentRoot /path/to/kindergartenctl/public

    <Directory /path/to/kindergartenctl/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration

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

    location ~ /\. {
        deny all;
    }
}
```

## Update

1. **Backup your data**:
   ```bash
   mysqldump -u username -p kindergarten_organizer > backup.sql
   cp -r public/uploads uploads_backup
   ```

2. **Pull the latest changes**:
   ```bash
   git pull origin main
   ```

3. **Run database migrations** (if any):
   ```bash
   php database/migrate.php
   ```

4. **Clear cache**:
   ```bash
   rm -rf storage/cache/*
   ```

## License

MIT License
