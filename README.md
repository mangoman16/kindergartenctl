# Kindergarten Spiele Organizer

A modern web application for kindergarten teachers to organize games, materials, and resources efficiently.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

## Features

- **Game Management** - Organize games with descriptions, images, difficulty levels, and age categories
- **Material Tracking** - Keep track of all game materials and their condition (complete, incomplete, damaged, missing)
- **Box Organization** - Organize materials in physical storage boxes with location tracking
- **Calendar Integration** - Plan game activities and track what was played when
- **Search & Filtering** - Full-text search across all entities with advanced filtering
- **Tags & Categories** - Categorize games by age groups and themes
- **Groups** - Create virtual collections of games and materials
- **Changelog** - Audit trail of all changes made to the system
- **Image Upload** - Upload and crop images for games, materials, and boxes
- **Random Game Picker** - Pick a random game based on filters
- **Print Views** - Print game details, material checklists, and box contents
- **German Interface** - Full German language support (Austrian locale)

## Requirements

### Server Requirements

- **PHP 8.0+** with the following extensions:
  - PDO with MySQL driver (`pdo_mysql`)
  - GD Library (`gd`) - for image processing
  - Multibyte String (`mbstring`)
  - JSON (`json`)
  - OpenSSL (`openssl`) - for secure token generation
- **MySQL 8.x** or MariaDB 10.4+
- **Apache** with `mod_rewrite` enabled (or nginx with equivalent configuration)
- **Composer** (optional, for running tests)

### Recommended

- PHP memory limit: 128MB+
- Max upload size: 10MB+
- HTTPS enabled for production

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
   chmod -R 755 public/uploads
   chmod -R 755 storage
   chmod -R 755 temp
   ```

4. **Open your browser** and navigate to your installation URL

5. **Follow the installation wizard** (4 steps):
   - Requirements check
   - Database configuration
   - Admin user creation
   - SMTP configuration (optional)

### Manual Installation

If you prefer manual installation or the wizard isn't working:

1. **Create the database**:
   ```sql
   CREATE DATABASE kindergarten_organizer
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
   ```

2. **Import the schema**:
   ```bash
   mysql -u username -p kindergarten_organizer < database/schema.sql
   ```

3. **Create the database configuration file** at `src/config/database.php`:
   ```php
   <?php
   return [
       'host' => 'localhost',
       'port' => 3306,
       'database' => 'kindergarten_organizer',
       'username' => 'your_username',
       'password' => 'your_password',
   ];
   ```

4. **Create an admin user** (run this SQL):
   ```sql
   INSERT INTO users (username, email, password_hash, created_at)
   VALUES (
       'admin',
       'admin@example.com',
       '$2y$10$your_bcrypt_hash_here',
       NOW()
   );
   ```

   Generate the password hash with PHP:
   ```php
   echo password_hash('YourPassword123', PASSWORD_DEFAULT);
   ```

5. **Create the lock file** to prevent re-installation:
   ```bash
   touch installed.lock
   ```

### Apache Configuration

Ensure your Apache virtual host is configured correctly:

```apache
<VirtualHost *:80>
    ServerName kindergarten.example.com
    DocumentRoot /path/to/kindergartenctl/public

    <Directory /path/to/kindergartenctl/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/kindergarten-error.log
    CustomLog ${APACHE_LOG_DIR}/kindergarten-access.log combined
</VirtualHost>
```

### Nginx Configuration

For nginx users:

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

## Configuration

### Main Configuration

The main configuration file is located at `src/config/config.php`. Key settings:

| Setting | Default | Description |
|---------|---------|-------------|
| `app.name` | Kindergarten Spiele Organizer | Application name |
| `app.debug` | `false` | Enable debug mode (set to `false` in production!) |
| `app.timezone` | Europe/Vienna | Application timezone |
| `session.lifetime` | 86400 (24h) | Session lifetime in seconds |
| `session.remember_lifetime` | 2592000 (30d) | Remember me cookie lifetime |
| `security.csrf_lifetime` | 3600 (1h) | CSRF token lifetime |
| `security.password_min_length` | 8 | Minimum password length |
| `upload.max_size` | 10485760 (10MB) | Maximum upload file size |

### SMTP Configuration

Email settings can be configured during installation or later in Settings:

- **SMTP Host** - e.g., `smtp.gmail.com`
- **SMTP Port** - e.g., `587` for TLS, `465` for SSL
- **SMTP Username** - Your email account
- **SMTP Password** - App password (for Gmail, generate one in account settings)
- **Encryption** - TLS or SSL
- **From Email** - Sender email address
- **From Name** - Sender display name

## Security Features

This application implements comprehensive security measures:

### Authentication & Authorization

- **Password Hashing**: Bcrypt with automatic cost adjustment
- **Password Complexity**: Minimum 8 characters, requires uppercase, lowercase, and number
- **Session Security**: HTTPOnly, Secure, SameSite cookies with periodic regeneration
- **Remember Me**: Secure token-based auto-login with 30-day expiry
- **IP Banning**: Automatic temporary (5 attempts = 15 min) and permanent (10 attempts) bans
- **Password Reset**: Secure tokens with 1-hour expiry, single use

### Input/Output Security

- **SQL Injection Prevention**: PDO prepared statements throughout
- **XSS Prevention**: Output escaping with `htmlspecialchars()`
- **CSRF Protection**: Token validation on all state-changing operations
- **HTML Sanitization**: Strips dangerous tags and event handlers from rich text
- **Open Redirect Prevention**: URL validation on all redirects

### File Upload Security

- **MIME Type Validation**: Server-side detection (not trusting client)
- **Extension Whitelist**: Only image types allowed (JPEG, PNG, GIF, WebP)
- **Image Verification**: Validates actual image content
- **Content Scanning**: Checks for embedded PHP/JavaScript
- **Image Reprocessing**: Converts to WebP, removes metadata

### Additional Security

- **Rate Limiting**: Configurable rate limits with file locking
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, CSP, etc.
- **Debug Protection**: Debug functions disabled in production

## Directory Structure

```
kindergartenctl/
├── public/                  # Web root
│   ├── index.php           # Application entry point
│   ├── .htaccess           # Apache URL rewriting & security headers
│   ├── assets/             # Static assets (CSS, JS)
│   └── uploads/            # User-uploaded images
│       ├── games/
│       ├── materials/
│       ├── boxes/
│       ├── categories/
│       ├── tags/
│       └── groups/
├── src/                    # Application source code
│   ├── config/             # Configuration files
│   ├── controllers/        # Request handlers
│   ├── models/             # Data models
│   ├── services/           # Business logic services
│   ├── core/               # Framework classes
│   ├── views/              # View templates
│   ├── helpers/            # Helper functions
│   └── lang/               # Translations
├── database/               # Database schema & migrations
├── storage/                # Logs and cache
│   ├── logs/
│   └── cache/
├── temp/                   # Temporary files
└── tests/                  # PHPUnit tests
```

## Database Schema

The application uses the following main tables:

| Table | Purpose |
|-------|---------|
| `users` | User accounts and authentication |
| `games` | Game definitions |
| `materials` | Physical game materials |
| `boxes` | Storage containers |
| `categories` | Age groups |
| `tags` | Themes/categories for games |
| `groups` | Virtual collections |
| `game_materials` | Game-Material relationships |
| `game_categories` | Game-Category relationships |
| `game_tags` | Game-Tag relationships |
| `calendar_events` | Played/planned events |
| `changelog` | Audit log |
| `ip_bans` | Brute force protection |
| `password_resets` | Password reset tokens |
| `settings` | Application settings |

## API Endpoints

The application provides JSON API endpoints for AJAX functionality:

### Search & Autocomplete
- `GET /api/search?q={query}` - Global search
- `GET /api/tags/search?q={query}` - Search tags
- `GET /api/materials/search?q={query}` - Search materials
- `GET /api/games/search?q={query}` - Search games

### Data Operations
- `POST /api/upload-image` - Upload image with crop data
- `POST /api/delete-image` - Delete uploaded image
- `GET /api/games/random` - Get random game with filters
- `POST /api/games/{id}/toggle-favorite` - Toggle favorite status

### Calendar
- `GET /api/calendar/events` - Get events for date range
- `POST /api/calendar/events` - Create event
- `PUT /api/calendar/events/{id}` - Update event
- `DELETE /api/calendar/events/{id}` - Delete event

### Health Check
- `GET /api/health` - Application health status

## Testing

Run tests with PHPUnit:

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Or directly with PHPUnit
./vendor/bin/phpunit
```

## Troubleshooting

### Common Issues

**"500 Internal Server Error"**
- Check Apache error logs
- Verify PHP version is 8.0+
- Ensure `mod_rewrite` is enabled
- Check file permissions

**"Database connection failed"**
- Verify database credentials in `src/config/database.php`
- Ensure MySQL is running
- Check database user permissions

**"Images not uploading"**
- Verify `public/uploads` is writable
- Check PHP `upload_max_filesize` and `post_max_size`
- Ensure GD library is installed

**"CSRF token mismatch"**
- Clear browser cookies and try again
- Check if session is working
- Verify session save path is writable

**"Emails not sending"**
- Verify SMTP settings in Settings
- For Gmail, use app password instead of regular password
- Check PHP error log for SMTP errors

### Getting Help

- Check the [Issues](https://github.com/yourusername/kindergartenctl/issues) page
- Review the `storage/logs/` directory for errors
- Enable debug mode temporarily to see detailed errors

## Development

### Local Development Setup

1. Clone the repository
2. Configure a local web server (Apache/nginx) or use PHP's built-in server:
   ```bash
   cd public
   php -S localhost:8000
   ```
3. Create a local database and run the installation wizard
4. Enable debug mode in `src/config/config.php` for detailed errors

### Code Style

- PHP: PSR-12 coding standard
- JavaScript: ES6+ with strict mode
- CSS: BEM methodology with CSS custom properties

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## Security Audit

This application has undergone comprehensive security auditing. See `SECURITY_AUDIT_2026-01-16.md` for the full audit report.

### Security Checklist

- [x] SQL Injection Prevention
- [x] XSS Prevention
- [x] CSRF Protection
- [x] Authentication Security
- [x] Session Management
- [x] Password Security
- [x] File Upload Validation
- [x] Path Traversal Prevention
- [x] Error Handling
- [x] Security Headers
- [x] Rate Limiting
- [x] Input Validation

## Changelog

### v1.0.0 (2026-01-16)

- Initial release
- Complete game, material, and box management
- Calendar integration with Austrian holidays
- Full German language support
- Comprehensive security implementation
- Installation wizard
- SMTP email configuration
- Image upload with cropping
- Search and filtering
- Changelog/audit trail

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- [Cropper.js](https://fengyuanchen.github.io/cropperjs/) - Image cropping
- [FullCalendar](https://fullcalendar.io/) - Calendar UI
- [Choices.js](https://choices-js.github.io/Choices/) - Multi-select dropdowns

---

**Made with care for kindergarten teachers everywhere.**
