# Kindergarten Game Organizer - Complete Project Specification

## Project Overview

**Project Name:** Kindergarten Game Organizer (Kindergarten Spiele Organizer)
**Purpose:** A web application for a kindergarten teacher to organize games, materials, and resources
**Technology Stack:** PHP 8.x, MySQL 8.x, HTML5, CSS3, JavaScript
**Language:** German (Frontend), English (Code/Comments)
**Target Scale:** Dozens to hundreds of games
**Users:** Single user (with authentication system)
**Access:** Online only (no offline mode needed)
**Mobile:** Not required (desktop-focused)

-----

## Core Entities & Relationships

### Entity Overview

|Entity             |Description                                                  |
|-------------------|-------------------------------------------------------------|
|**Users**          |Authentication and user management                           |
|**Categories**     |Age groups (e.g., 2-3, 3-4, 4-6 years)                       |
|**Boxes**          |Physical storage containers with locations                   |
|**Tags**           |Themes/categories for games (colors, seasons, holidays, etc.)|
|**Materials**      |Physical game materials/components                           |
|**Games**          |The games themselves                                         |
|**Groups**         |Virtual collections of games and/or materials                |
|**Calendar Events**|Usage tracking (past and future)                             |
|**Changelog**      |System-wide change history                                   |
|**IP Bans**        |Security/brute force protection                              |

### Relationship Rules

- A **material** belongs to exactly ONE box
- A **game** can have MULTIPLE materials
- A **material** can be used in MULTIPLE games (many-to-many)
- A **game** can be suitable for MULTIPLE age categories (many-to-many)
- A **game** can have MULTIPLE tags (many-to-many)
- A **group** can contain MULTIPLE games AND/OR materials (polymorphic)
- **Calendar events** link to games (what was played/planned)

-----

## Database Schema

### Table: `users`

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(255) NULL,
    remember_token_expires_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `categories` (Age Groups)

```sql
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,           -- e.g., "2-3 Jahre", "3-4 Jahre"
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FULLTEXT INDEX ft_categories (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `boxes`

```sql
CREATE TABLE boxes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    number VARCHAR(20) NULL,                     -- Box number/code
    location VARCHAR(255) NULL,                  -- Physical location description
    description TEXT NULL,
    notes TEXT NULL,
    image_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_box_name (name),
    FULLTEXT INDEX ft_boxes (name, location, description, notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `tags` (Themes)

```sql
CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,           -- e.g., "Farben", "Jahreszeiten", "Weihnachten"
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    color VARCHAR(7) NULL,                       -- Optional hex color for UI display
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FULLTEXT INDEX ft_tags (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `materials`

```sql
CREATE TABLE materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    notes TEXT NULL,
    box_id INT UNSIGNED NULL,                    -- Which box contains this material
    status ENUM('complete', 'incomplete', 'damaged', 'missing') DEFAULT 'complete',
    image_path VARCHAR(255) NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_material_name (name),
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE SET NULL,
    FULLTEXT INDEX ft_materials (name, description, notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `games`

```sql
CREATE TABLE games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    notes TEXT NULL,
    difficulty TINYINT UNSIGNED DEFAULT 1,       -- 1, 2, or 3
    image_path VARCHAR(255) NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'archived', 'needs_materials') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_game_name (name),
    FULLTEXT INDEX ft_games (name, description, notes),
    INDEX idx_difficulty (difficulty),
    INDEX idx_favorite (is_favorite),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Junction Table: `game_materials`

```sql
CREATE TABLE game_materials (
    game_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (game_id, material_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Junction Table: `game_categories`

```sql
CREATE TABLE game_categories (
    game_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (game_id, category_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Junction Table: `game_tags`

```sql
CREATE TABLE game_tags (
    game_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (game_id, tag_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `groups` (Virtual Collections)

```sql
CREATE TABLE groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FULLTEXT INDEX ft_groups (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Junction Table: `group_items` (Polymorphic)

```sql
CREATE TABLE group_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    item_type ENUM('game', 'material') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_group_item (group_id, item_type, item_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    INDEX idx_item (item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `calendar_events`

```sql
CREATE TABLE calendar_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,                 -- Event title (can be custom or game name)
    description TEXT NULL,
    event_date DATE NOT NULL,
    event_type ENUM('played', 'planned') NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    INDEX idx_date (event_date),
    INDEX idx_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `changelog`

```sql
CREATE TABLE changelog (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    entity_type VARCHAR(50) NOT NULL,            -- 'game', 'material', 'box', etc.
    entity_id INT UNSIGNED NOT NULL,
    entity_name VARCHAR(200) NULL,               -- Store name for reference after deletion
    action ENUM('create', 'update', 'delete', 'move') NOT NULL,
    changes JSON NULL,                           -- {"field": {"old": "x", "new": "y"}}
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `ip_bans`

```sql
CREATE TABLE ip_bans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,             -- Supports IPv6
    failed_attempts INT UNSIGNED DEFAULT 0,
    last_attempt_at DATETIME NULL,
    banned_until DATETIME NULL,                  -- NULL = not banned (unless permanent)
    is_permanent BOOLEAN DEFAULT FALSE,
    reason VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_ip (ip_address),
    INDEX idx_banned (banned_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `password_resets`

```sql
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,            -- Hashed token (plain sent via email)
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,                       -- NULL = not yet used
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `settings` (Application Settings)

```sql
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

-----

## Directory Structure

```
/kindergarten-organizer/
│
├── /public/                          # Web root (document root)
│   ├── index.php                     # Main entry point / router
│   ├── install.php                   # Installation wizard
│   ├── .htaccess                     # Apache URL rewriting
│   │
│   ├── /assets/
│   │   ├── /css/
│   │   │   ├── style.css             # Main styles
│   │   │   ├── print.css             # Print-specific styles
│   │   │   └── cropper.min.css       # Cropper.js styles
│   │   │
│   │   ├── /js/
│   │   │   ├── app.js                # Main application JS
│   │   │   ├── cropper.min.js        # Cropper.js library
│   │   │   ├── calendar.js           # Calendar functionality
│   │   │   └── search.js             # Search/filter functionality
│   │   │
│   │   └── /images/
│   │       └── /ui/                  # UI assets (logo, icons, etc.)
│   │
│   └── /uploads/                     # User uploaded images
│       ├── /games/
│       │   ├── /thumbs/              # 150x150 thumbnails
│       │   └── /full/                # 600x600 full size
│       ├── /materials/
│       │   ├── /thumbs/
│       │   └── /full/
│       ├── /boxes/
│       │   ├── /thumbs/
│       │   └── /full/
│       ├── /categories/
│       │   ├── /thumbs/
│       │   └── /full/
│       ├── /tags/
│       │   ├── /thumbs/
│       │   └── /full/
│       └── /groups/
│           ├── /thumbs/
│           └── /full/
│
├── /src/                             # Application source code
│   │
│   ├── /config/
│   │   ├── config.php                # Main configuration
│   │   ├── database.php              # Database configuration
│   │   └── routes.php                # Route definitions
│   │
│   ├── /controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── GameController.php
│   │   ├── MaterialController.php
│   │   ├── BoxController.php
│   │   ├── CategoryController.php
│   │   ├── TagController.php
│   │   ├── GroupController.php
│   │   ├── CalendarController.php
│   │   ├── ChangelogController.php
│   │   ├── SearchController.php
│   │   ├── SettingsController.php
│   │   └── InstallController.php
│   │
│   ├── /models/
│   │   ├── User.php
│   │   ├── PasswordReset.php
│   │   ├── Game.php
│   │   ├── Material.php
│   │   ├── Box.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Group.php
│   │   ├── CalendarEvent.php
│   │   ├── Changelog.php
│   │   └── IpBan.php
│   │
│   ├── /views/
│   │   ├── /layouts/
│   │   │   ├── main.php              # Main layout template
│   │   │   ├── auth.php              # Login layout (minimal)
│   │   │   ├── install.php           # Installation layout
│   │   │   └── print.php             # Print layout
│   │   │
│   │   ├── /auth/
│   │   │   ├── login.php
│   │   │   ├── forgot-password.php   # Request password reset
│   │   │   ├── reset-password.php    # Enter new password
│   │   │   └── /emails/
│   │   │       └── password-reset.php # Email template
│   │   │
│   │   ├── /dashboard/
│   │   │   └── index.php
│   │   │
│   │   ├── /games/
│   │   │   ├── index.php             # List view
│   │   │   ├── show.php              # Detail view
│   │   │   ├── form.php              # Create/Edit form
│   │   │   └── print.php             # Print view
│   │   │
│   │   ├── /materials/
│   │   │   ├── index.php
│   │   │   ├── show.php
│   │   │   ├── form.php
│   │   │   └── print.php
│   │   │
│   │   ├── /boxes/
│   │   │   ├── index.php
│   │   │   ├── show.php              # Shows box contents
│   │   │   ├── form.php
│   │   │   └── print.php
│   │   │
│   │   ├── /categories/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   │
│   │   ├── /tags/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   │
│   │   ├── /groups/
│   │   │   ├── index.php
│   │   │   ├── show.php
│   │   │   └── form.php
│   │   │
│   │   ├── /calendar/
│   │   │   └── index.php
│   │   │
│   │   ├── /changelog/
│   │   │   └── index.php
│   │   │
│   │   ├── /settings/
│   │   │   └── index.php
│   │   │
│   │   ├── /install/
│   │   │   ├── step1.php             # Welcome/Requirements
│   │   │   ├── step2.php             # Database connection
│   │   │   ├── step3.php             # Create admin user
│   │   │   ├── step4.php             # Email/SMTP setup (optional)
│   │   │   └── complete.php          # Success
│   │   │
│   │   ├── /partials/
│   │   │   ├── header.php
│   │   │   ├── sidebar.php
│   │   │   ├── footer.php
│   │   │   ├── flash-messages.php
│   │   │   ├── pagination.php
│   │   │   ├── image-upload.php      # Reusable image upload component
│   │   │   ├── search-bar.php
│   │   │   └── filter-panel.php
│   │   │
│   │   └── /components/
│   │       ├── game-card.php         # Game card for grid view
│   │       ├── material-card.php
│   │       ├── box-card.php
│   │       ├── related-games.php     # "Related games" section
│   │       └── preparation-list.php  # Materials checklist for a game
│   │
│   ├── /core/
│   │   ├── App.php                   # Application bootstrap
│   │   ├── Router.php                # URL routing
│   │   ├── Controller.php            # Base controller
│   │   ├── Model.php                 # Base model
│   │   ├── Database.php              # Database connection (PDO)
│   │   ├── Session.php               # Session management
│   │   ├── Auth.php                  # Authentication helper
│   │   ├── Validator.php             # Input validation
│   │   ├── ImageProcessor.php        # Image upload/crop/resize
│   │   ├── Mailer.php                # SMTP email sending
│   │   └── ChangelogService.php      # Changelog logging
│   │
│   ├── /helpers/
│   │   ├── functions.php             # Global helper functions
│   │   ├── dates.php                 # Date formatting (Austrian)
│   │   └── security.php              # Security helpers
│   │
│   └── /lang/
│       └── de.php                    # German translations
│
├── /temp/                            # Temporary files (original uploads)
│   └── .gitkeep
│
├── /storage/
│   ├── /logs/                        # Application logs
│   │   └── .gitkeep
│   └── /cache/                       # Cache files
│       └── .gitkeep
│
├── .htaccess                         # Root htaccess (deny access)
├── composer.json                     # PHP dependencies (if any)
└── README.md                         # Project documentation
```

-----

## Features Specification

### 1. Installation Wizard (`/install`)

**Purpose:** First-time setup when application is deployed

**Steps:**

1. **Welcome & Requirements Check**
- Check PHP version (8.0+)
- Check required extensions: PDO, pdo_mysql, GD or Imagick, mbstring, json
- Check directory permissions (uploads, temp, storage)
- Display pass/fail for each requirement
1. **Database Configuration**
- Form fields: Host, Port, Database Name, Username, Password
- Test connection button
- Run schema creation (all tables)
- Store config in `/src/config/database.php`
1. **Create Admin User**
- Username
- Email
- Password (with confirmation)
- Password strength requirements: min 8 chars
1. **Email Configuration (Optional)**
- SMTP Host
- SMTP Port (default: 587)
- SMTP Username
- SMTP Password
- Encryption (TLS/SSL)
- From Email
- From Name
- “Test” button
- “Überspringen” (Skip) button - can configure later in settings
1. **Complete**
- Success message
- Create `installed.lock` file to prevent re-installation
- Redirect to login

**Security:**

- If `installed.lock` exists, redirect install.php to home
- Sanitize all database config inputs

-----

### 2. Authentication System

**Login Page (`/login`)**

- Username/Email field
- Password field
- “Angemeldet bleiben” (Remember me) checkbox
- Login button
- “Passwort vergessen?” (Forgot password?) link
- Display error messages inline

**Forgot Password Page (`/forgot-password`)**

- Email field
- “Link senden” (Send link) button
- Back to login link
- Success message (same for existing/non-existing email - security)

**Reset Password Page (`/reset-password?token=xxx`)**

- New password field
- Confirm password field
- “Passwort ändern” (Change password) button
- Error if token invalid/expired
- Redirect to login on success

**Session Management:**

- Standard session lifetime: 24 hours of inactivity
- With “Remember me”: 30 days via secure cookie token
- Session regeneration on login
- Logout clears session and remember token

**IP Ban System:**

- Track failed login attempts per IP
- After 5 failed attempts: ban IP for 15 minutes
- After 10 failed attempts: permanent ban
- Successful login resets failed attempt counter
- Admin can view/manage banned IPs in settings
- Admin can unban IPs (including permanent bans) in settings

**Remember Me Implementation:**

- Generate secure random token (64 chars)
- Store hashed token in database with expiry
- Store plain token in secure, httponly cookie
- On page load: if no session but cookie exists, validate and auto-login

**Password Reset via Email (SMTP):**

*Flow:*

1. User clicks “Passwort vergessen?” on login page
1. User enters email address
1. System checks if email exists in users table
1. If exists: generate secure token, store hash in `password_resets`, send email with reset link
1. If not exists: show same success message (prevent email enumeration)
1. User clicks link in email → `/reset-password?token=xxx`
1. System validates token (exists, not expired, not used)
1. User enters new password (with confirmation)
1. System updates password, marks token as used
1. Redirect to login with success message

*Token Rules:*

- Token: 64 character random string
- Store hashed (like passwords)
- Expires after 1 hour
- Single use only
- Delete expired tokens periodically (cleanup job or on-demand)

*SMTP Configuration (stored in database `settings` table or config file):*

- `smtp_host` - e.g., “smtp.gmail.com”
- `smtp_port` - e.g., 587
- `smtp_username` - email account
- `smtp_password` - app password (encrypted in DB)
- `smtp_encryption` - “tls” or “ssl”
- `smtp_from_email` - sender email address
- `smtp_from_name` - e.g., “Kindergarten Organizer”

*Email Template (Password Reset):*

```
Betreff: Passwort zurücksetzen

Hallo,

Du hast eine Anfrage zum Zurücksetzen deines Passworts gestellt.

Klicke auf den folgenden Link, um ein neues Passwort zu erstellen:
{reset_link}

Dieser Link ist 1 Stunde gültig.

Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren.

Viele Grüße,
Kindergarten Spiele Organizer
```

-----

### 3. Dashboard (`/dashboard`)

**Statistics Panel:**

- Total games count
- Total materials count
- Total boxes count
- Favorite games count
- Games played this month

**Quick Actions:**

- “Neues Spiel” (New Game) button
- “Neues Material” (New Material) button
- “Neue Box” (New Box) button

**Random Game Picker:**

- Filter dropdowns: Age category, Tag, Difficulty
- “Zufälliges Spiel” (Random Game) button
- Display random game card matching filters
- Click card to go to game detail

**Recently Added:**

- Last 5 games added (cards)
- Last 5 materials added (cards)

**Recently Played:**

- Games from calendar with `played` type in last 30 days
- Show game name, date played

**Favorites Section:**

- Grid of favorite games (max 8, link to see all)

**Upcoming Events:**

- Next 5 planned calendar events

-----

### 4. Games Management

#### 4.1 Games List (`/games`)

**Display:**

- Grid view of game cards (default)
- Optional list view toggle
- Each card shows: thumbnail, name, difficulty stars, favorite icon

**Filtering Sidebar:**

- Search box (fulltext)
- Age category multi-select
- Tags multi-select
- Difficulty select (1, 2, 3, or all)
- Status select (active, archived, needs materials)
- Favorites only checkbox

**Sorting:**

- Name (A-Z, Z-A)
- Date added (newest, oldest)
- Difficulty
- Most recently played

**Pagination:**

- 24 items per page
- Page numbers with prev/next

**Bulk Actions:**

- Select multiple games
- Add to group
- Add to favorites
- Remove from favorites

#### 4.2 Game Detail (`/games/{id}`)

**Header Section:**

- Large image (or placeholder)
- Game name (h1)
- Difficulty display (1-3 stars or boxes)
- Favorite toggle button
- Status badge
- Edit button
- Delete button (with confirmation)
- Print button

**Information Panel:**

- Description (full text)
- Notes (collapsible if long)
- Age categories (linked chips)
- Tags (linked chips)
- Date added
- Last modified

**Materials Section:**

- List of all materials needed
- Each shows: name, status, box location
- Quick link to material detail
- Visual indicator if material is damaged/missing

**Preparation Checklist:**

- Printable view of materials grouped by box
- “Alles in Box 3: Material A, Material B”
- “Alles in Box 7: Material C”

**Related Games Section:**

- **Same age group:** 4-6 game cards
- **Similar tags:** 4-6 game cards
- **Uses same materials:** 4-6 game cards

**Calendar History:**

- When this game was played (last 10 entries)
- Add to calendar button (opens modal)

#### 4.3 Game Form (`/games/create`, `/games/{id}/edit`)

**Fields:**

- Name* (text, max 150)
- Description (textarea, rich text optional)
- Notes (textarea)
- Difficulty* (radio: 1, 2, 3)
- Status (select: active, archived, needs_materials)
- Age Categories* (multi-select checkboxes)
- Tags (multi-select checkboxes with option to add new inline)
- Materials (searchable multi-select)
- Image upload with cropper

**Duplicate Detection:**

- On name blur, check for existing games with same name
- Show warning if duplicate found
- Allow save anyway with confirmation

**Validation:**

- Name required, max 150 chars
- Difficulty required, must be 1, 2, or 3
- At least one age category required

-----

### 5. Materials Management

#### 5.1 Materials List (`/materials`)

**Display:**

- Grid view of material cards
- Each card: thumbnail, name, box name, status indicator

**Filtering:**

- Search box
- Box filter (select)
- Status filter (complete, incomplete, damaged, missing)
- Favorites only

**Sorting:**

- Name, Date added, Box

#### 5.2 Material Detail (`/materials/{id}`)

**Header:**

- Image
- Name
- Status badge (color coded)
- Favorite toggle
- Edit, Delete, Print buttons

**Information:**

- Description
- Notes
- Box (linked to box detail)
- Date added

**Used In Games:**

- List of games that use this material
- Game cards with links

**Move History:**

- From changelog: when was this moved between boxes

#### 5.3 Material Form

**Fields:**

- Name* (text)
- Description (textarea)
- Notes (textarea)
- Box (select dropdown)
- Status* (select: complete, incomplete, damaged, missing)
- Image upload with cropper

**Duplicate Detection:**

- Check for existing material with same name

-----

### 6. Boxes Management

#### 6.1 Boxes List (`/boxes`)

**Display:**

- Grid of box cards
- Each shows: image, name, number, location, material count

**Sorting:**

- Name, Number, Material count

#### 6.2 Box Detail (`/boxes/{id}`)

**Header:**

- Image
- Name
- Number
- Location
- Edit, Delete, Print buttons

**Description & Notes**

**Contents Section:**

- Grid/list of all materials in this box
- Each material linked to detail
- Status indicators
- Quick action: remove material from box

**Print View:**

- Box name, number, location
- Complete list of contents

#### 6.3 Box Form

**Fields:**

- Name* (text)
- Number (text)
- Location (text)
- Description (textarea)
- Notes (textarea)
- Image upload

**Duplicate Detection:**

- Check for existing box with same name

-----

### 7. Categories Management (`/categories`)

**List View:**

- Table or cards
- Image, Name, Description, Game count
- Edit, Delete buttons per row

**Form Fields:**

- Name* (e.g., “2-3 Jahre”)
- Description (textarea)
- Sort order (number)
- Image upload

**Duplicate Detection:**

- Check for existing category with same name

-----

### 8. Tags Management (`/tags`)

**List View:**

- Cards or table
- Image, Name, Description, Color swatch, Game count
- Edit, Delete buttons

**Form Fields:**

- Name* (e.g., “Weihnachten”)
- Description (textarea)
- Color (color picker, optional)
- Image upload

**Inline Creation:**

- From game form, ability to type new tag name and create instantly

**Duplicate Detection:**

- Check for existing tag with same name

-----

### 9. Groups Management (`/groups`)

**Purpose:** Virtual collections that can contain games AND materials

**List View:**

- Cards showing group image, name, item count
- Edit, Delete buttons

**Group Detail (`/groups/{id}`):**

- Header with image, name, description
- Section: Games in this group (cards)
- Section: Materials in this group (cards)
- Remove item from group button per item

**Form Fields:**

- Name*
- Description
- Image upload

**Adding Items to Groups:**

- From game/material detail page: “Add to group” button
- From game/material list: bulk action
- From group detail: “Add items” button opens modal with search

-----

### 10. Calendar (`/calendar`)

**Display:**

- Full calendar view (monthly default)
- Week and day views available
- Austrian calendar (Monday start, DD.MM.YYYY format)
- Austrian holidays highlighted (read-only)

**Event Types:**

- **Played (gespielt):** Past events, green color
- **Planned (geplant):** Future events, blue color

**Event Display:**

- Show game name on calendar
- Click to view event details

**Event Modal (View):**

- Game name (linked to game detail)
- Date
- Event type
- Notes
- Edit button
- Delete button

**Event Form (Add/Edit):**

- Date picker
- Game (searchable select - optional, can have custom event)
- Title (auto-filled if game selected)
- Event type (played/planned)
- Notes

**“Add Game to Calendar” Button:**

- Available on game detail page
- Pre-selects the game
- Lets user choose date and type

**Filtering:**

- By month/year navigation
- By event type

**Export:**

- Option to export calendar data (future enhancement)

-----

### 11. Search (`/search`)

**Global Search Bar (in header):**

- Present on all pages
- Searches across: games, materials, boxes, tags, categories, groups
- Dropdown shows categorized results as you type
- Enter to go to full search results page

**Full Search Results Page:**

- Tabs: All, Games, Materials, Boxes, Tags, Categories, Groups
- Results show relevant info per type
- Click to go to detail page

**Fulltext Search Implementation:**

- Use MySQL FULLTEXT indexes
- Search in name, description, notes fields
- Minimum 3 characters to search
- Boolean mode for advanced users (optional)

-----

### 12. Changelog (`/changelog`)

**Display:**

- Table with columns: Date/Time, User, Action, Entity Type, Entity Name, Changes
- Expandable row to see full change details (JSON formatted nicely)

**Filtering:**

- Date range picker
- Entity type filter
- Action filter (create, update, delete, move)

**Actions:**

- Clear changelog button (with confirmation)
- Only clears, doesn’t delete table

**What Gets Logged:**

- All create, update, delete operations
- Material moves between boxes (special “move” action)
- Store old and new values for updates

-----

### 13. Settings (`/settings`)

**Sections:**

**User Settings:**

- Change password
- Change email

**Email Settings (SMTP):**

- SMTP Host
- SMTP Port
- SMTP Username
- SMTP Password (masked input)
- Encryption (TLS/SSL dropdown)
- From Email Address
- From Name
- “Test verbindung” (Test connection) button - sends test email to admin

**Security:**

- View banned IPs (show attempts count, ban type)
- Unban IP button (works for both temporary and permanent)
- Failed attempts count per IP
- Permanent ban IP form (manual)

**Data Management:**

- Clear temp folder button
- Clear changelog button
- View storage statistics (images, database size)

**Application Settings:**

- Items per page (default 24)
- Default view (grid/list)

-----

### 14. Print Views

**Available Print Views:**

- Single game detail
- Game materials checklist (grouped by box)
- Single material detail
- Box contents list
- Category games list
- Tag games list
- Group contents

**Print Styling:**

- Clean, black and white friendly
- Logo at top
- Proper margins
- No navigation elements
- Page breaks where appropriate

**Implementation:**

- Separate print.css
- Print button triggers window.print()
- Or: dedicated print routes with print layout

-----

## Image Handling Specification

### Upload Flow

1. User clicks upload button
1. File input opens, user selects image
1. Image loads into Cropper.js interface
1. User adjusts crop area (square ratio enforced)
1. User clicks “Zuschneiden” (Crop)
1. JavaScript creates canvas with cropped area
1. Canvas converts to Blob (WebP format if supported, else JPEG)
1. Blob uploads to server via AJAX

### Server-Side Processing

**Endpoint:** `/api/upload-image`

**Process:**

1. Receive uploaded file
1. Validate: file type (jpg, png, webp, gif), max size (10MB)
1. Generate unique filename: `{entity}_{timestamp}_{random}.webp`
1. Create two versions:
- **Thumbnail:** 150x150px, quality 80%
- **Full:** 600x600px, quality 85%
1. Save to appropriate directory based on entity type
1. Delete temp file
1. Return JSON with paths

**Libraries:**

- PHP GD Library (or Imagick if available)
- WebP format preferred, JPEG fallback

### Directory Structure

```
/public/uploads/
├── /games/
│   ├── /thumbs/     (150x150)
│   └── /full/       (600x600)
├── /materials/
├── /boxes/
├── /categories/
├── /tags/
└── /groups/
```

### Placeholder Images

- Default placeholder image for each entity type
- Displayed when no image uploaded
- Located in `/public/assets/images/ui/`

-----

## German Language Strings

All UI text in German. Key translations:

```php
<?php
// /src/lang/de.php

return [
    // Navigation
    'nav.dashboard' => 'Übersicht',
    'nav.games' => 'Spiele',
    'nav.materials' => 'Material',
    'nav.boxes' => 'Boxen',
    'nav.categories' => 'Altersgruppen',
    'nav.tags' => 'Themen',
    'nav.groups' => 'Gruppen',
    'nav.calendar' => 'Kalender',
    'nav.changelog' => 'Änderungsprotokoll',
    'nav.settings' => 'Einstellungen',
    'nav.logout' => 'Abmelden',
    
    // Common Actions
    'action.save' => 'Speichern',
    'action.cancel' => 'Abbrechen',
    'action.edit' => 'Bearbeiten',
    'action.delete' => 'Löschen',
    'action.create' => 'Erstellen',
    'action.add' => 'Hinzufügen',
    'action.remove' => 'Entfernen',
    'action.search' => 'Suchen',
    'action.filter' => 'Filtern',
    'action.print' => 'Drucken',
    'action.upload' => 'Hochladen',
    'action.crop' => 'Zuschneiden',
    'action.clear' => 'Leeren',
    
    // Forms
    'form.name' => 'Name',
    'form.description' => 'Beschreibung',
    'form.notes' => 'Notizen',
    'form.image' => 'Bild',
    'form.status' => 'Status',
    'form.required' => 'Pflichtfeld',
    
    // Games
    'game.title' => 'Spiel',
    'game.title_plural' => 'Spiele',
    'game.difficulty' => 'Schwierigkeit',
    'game.difficulty.1' => 'Leicht',
    'game.difficulty.2' => 'Mittel',
    'game.difficulty.3' => 'Schwer',
    'game.materials' => 'Benötigtes Material',
    'game.categories' => 'Altersgruppen',
    'game.tags' => 'Themen',
    'game.related' => 'Ähnliche Spiele',
    'game.add_new' => 'Neues Spiel',
    'game.status.active' => 'Aktiv',
    'game.status.archived' => 'Archiviert',
    'game.status.needs_materials' => 'Material fehlt',
    
    // Materials
    'material.title' => 'Material',
    'material.title_plural' => 'Materialien',
    'material.box' => 'In Box',
    'material.used_in' => 'Verwendet in',
    'material.add_new' => 'Neues Material',
    'material.status.complete' => 'Vollständig',
    'material.status.incomplete' => 'Unvollständig',
    'material.status.damaged' => 'Beschädigt',
    'material.status.missing' => 'Fehlt',
    
    // Boxes
    'box.title' => 'Box',
    'box.title_plural' => 'Boxen',
    'box.number' => 'Nummer',
    'box.location' => 'Standort',
    'box.contents' => 'Inhalt',
    'box.add_new' => 'Neue Box',
    
    // Categories
    'category.title' => 'Altersgruppe',
    'category.title_plural' => 'Altersgruppen',
    'category.add_new' => 'Neue Altersgruppe',
    
    // Tags
    'tag.title' => 'Thema',
    'tag.title_plural' => 'Themen',
    'tag.add_new' => 'Neues Thema',
    
    // Groups
    'group.title' => 'Gruppe',
    'group.title_plural' => 'Gruppen',
    'group.add_new' => 'Neue Gruppe',
    'group.add_items' => 'Einträge hinzufügen',
    
    // Calendar
    'calendar.title' => 'Kalender',
    'calendar.event_played' => 'Gespielt',
    'calendar.event_planned' => 'Geplant',
    'calendar.add_event' => 'Eintrag hinzufügen',
    
    // Dashboard
    'dashboard.title' => 'Übersicht',
    'dashboard.total_games' => 'Spiele gesamt',
    'dashboard.total_materials' => 'Materialien gesamt',
    'dashboard.total_boxes' => 'Boxen gesamt',
    'dashboard.favorites' => 'Favoriten',
    'dashboard.recently_added' => 'Zuletzt hinzugefügt',
    'dashboard.recently_played' => 'Zuletzt gespielt',
    'dashboard.upcoming' => 'Geplante Spiele',
    'dashboard.random_game' => 'Zufälliges Spiel',
    'dashboard.pick_random' => 'Spiel auswählen',
    
    // Changelog
    'changelog.title' => 'Änderungsprotokoll',
    'changelog.action.create' => 'Erstellt',
    'changelog.action.update' => 'Geändert',
    'changelog.action.delete' => 'Gelöscht',
    'changelog.action.move' => 'Verschoben',
    'changelog.clear_all' => 'Protokoll leeren',
    
    // Auth
    'auth.login' => 'Anmelden',
    'auth.logout' => 'Abmelden',
    'auth.username' => 'Benutzername',
    'auth.email' => 'E-Mail',
    'auth.password' => 'Passwort',
    'auth.password_confirm' => 'Passwort bestätigen',
    'auth.remember_me' => 'Angemeldet bleiben',
    'auth.login_failed' => 'Anmeldung fehlgeschlagen',
    'auth.ip_banned' => 'Zu viele Fehlversuche. Bitte warten Sie.',
    'auth.ip_banned_permanent' => 'Diese IP-Adresse wurde dauerhaft gesperrt.',
    'auth.forgot_password' => 'Passwort vergessen?',
    'auth.reset_password' => 'Passwort zurücksetzen',
    'auth.send_reset_link' => 'Link senden',
    'auth.reset_link_sent' => 'Falls ein Konto mit dieser E-Mail existiert, wurde ein Link zum Zurücksetzen gesendet.',
    'auth.reset_token_invalid' => 'Dieser Link ist ungültig oder abgelaufen.',
    'auth.password_reset_success' => 'Dein Passwort wurde erfolgreich geändert. Du kannst dich jetzt anmelden.',
    'auth.new_password' => 'Neues Passwort',
    'auth.set_new_password' => 'Neues Passwort setzen',
    
    // Search
    'search.placeholder' => 'Suchen...',
    'search.no_results' => 'Keine Ergebnisse gefunden',
    'search.results_for' => 'Ergebnisse für',
    
    // Misc
    'misc.favorite' => 'Favorit',
    'misc.favorites' => 'Favoriten',
    'misc.add_to_favorites' => 'Zu Favoriten hinzufügen',
    'misc.remove_from_favorites' => 'Aus Favoriten entfernen',
    'misc.created_at' => 'Erstellt am',
    'misc.updated_at' => 'Geändert am',
    'misc.confirm_delete' => 'Möchten Sie diesen Eintrag wirklich löschen?',
    'misc.yes' => 'Ja',
    'misc.no' => 'Nein',
    'misc.all' => 'Alle',
    'misc.none' => 'Keine',
    'misc.items' => 'Einträge',
    
    // Validation
    'validation.required' => 'Dieses Feld ist erforderlich',
    'validation.duplicate' => 'Ein Eintrag mit diesem Namen existiert bereits',
    'validation.max_length' => 'Maximal :max Zeichen erlaubt',
    'validation.passwords_dont_match' => 'Passwörter stimmen nicht überein',
    'validation.password_min_length' => 'Passwort muss mindestens 8 Zeichen haben',
    
    // Email / SMTP Settings
    'settings.email' => 'E-Mail Einstellungen',
    'settings.smtp_host' => 'SMTP Server',
    'settings.smtp_port' => 'Port',
    'settings.smtp_username' => 'Benutzername',
    'settings.smtp_password' => 'Passwort',
    'settings.smtp_encryption' => 'Verschlüsselung',
    'settings.smtp_from_email' => 'Absender E-Mail',
    'settings.smtp_from_name' => 'Absender Name',
    'settings.smtp_test' => 'Verbindung testen',
    'settings.smtp_test_success' => 'Test-E-Mail erfolgreich gesendet!',
    'settings.smtp_test_failed' => 'Verbindung fehlgeschlagen: :error',
    
    // Installation
    'install.title' => 'Installation',
    'install.welcome' => 'Willkommen',
    'install.requirements' => 'Systemvoraussetzungen',
    'install.database' => 'Datenbank',
    'install.admin_user' => 'Administrator',
    'install.complete' => 'Fertig',
    'install.next' => 'Weiter',
    'install.test_connection' => 'Verbindung testen',
];
```

-----

## Austrian Calendar & Date Formatting

### Date Display Format

- Full date: `DD.MM.YYYY` (e.g., “24.12.2024”)
- With weekday: `Montag, 24. Dezember 2024`
- Short month: `24. Dez. 2024`

### Time Format

- 24-hour: `HH:MM` (e.g., “14:30”)

### Week Start

- Monday (not Sunday)

### Austrian Holidays (for calendar highlighting)

```php
// Feiertage Österreich
$holidays = [
    '01-01' => 'Neujahr',
    '01-06' => 'Heilige Drei Könige',
    // Easter-dependent (calculate):
    // Ostersonntag, Ostermontag, Christi Himmelfahrt, Pfingstsonntag, Pfingstmontag, Fronleichnam
    '05-01' => 'Staatsfeiertag',
    '08-15' => 'Mariä Himmelfahrt',
    '10-26' => 'Nationalfeiertag',
    '11-01' => 'Allerheiligen',
    '12-08' => 'Mariä Empfängnis',
    '12-25' => 'Christtag',
    '12-26' => 'Stefanitag',
];
```

### PHP Date Functions

```php
// Helper for German date formatting
function formatDateGerman(DateTime $date, string $format = 'full'): string {
    $months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 
               'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    $days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    
    switch ($format) {
        case 'full':
            return $days[$date->format('w')] . ', ' . $date->format('j') . '. ' . 
                   $months[$date->format('n') - 1] . ' ' . $date->format('Y');
        case 'short':
            return $date->format('d.m.Y');
        case 'datetime':
            return $date->format('d.m.Y H:i');
        default:
            return $date->format('d.m.Y');
    }
}
```

-----

## Security Implementation

### Password Hashing

```php
// Use PHP's password_hash with default algorithm (currently bcrypt)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verification
if (password_verify($password, $hash)) {
    // Success
}
```

### Session Security

```php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // If HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 86400);  // 24 hours

// Regenerate ID on login
session_regenerate_id(true);
```

### CSRF Protection

```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate on POST
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token mismatch');
}
```

### Input Sanitization

- All user input escaped before output: `htmlspecialchars($input, ENT_QUOTES, 'UTF-8')`
- Use PDO prepared statements for all database queries
- Validate file uploads (type, size)
- Sanitize filenames

### IP Ban Logic

```php
function checkIpBan(string $ip): ?string {
    // Check if IP is banned
    $ban = getBanRecord($ip);
    
    if ($ban && $ban['is_permanent']) {
        return 'permanent';
    }
    
    if ($ban && $ban['banned_until'] && strtotime($ban['banned_until']) > time()) {
        return 'temporary';
    }
    
    return null;
}

function recordFailedAttempt(string $ip): void {
    $ban = getBanRecord($ip);
    $attempts = ($ban['failed_attempts'] ?? 0) + 1;
    
    if ($attempts >= 10) {
        // Permanent ban after 10 attempts
        $banUntil = null;
        $isPermanent = true;
    } elseif ($attempts >= 5) {
        // 15 minute ban after 5 attempts
        $banUntil = date('Y-m-d H:i:s', time() + 900);
        $isPermanent = false;
    } else {
        $banUntil = null;
        $isPermanent = false;
    }
    
    // Update or insert ban record
    upsertBanRecord($ip, $attempts, $banUntil, $isPermanent);
}

function resetFailedAttempts(string $ip): void {
    // Reset counter on successful login
    deleteBanRecord($ip);
}
```

### SMTP Mailer Implementation

```php
class Mailer {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption; // 'tls' or 'ssl'
    private string $fromEmail;
    private string $fromName;
    
    public function __construct(array $config) {
        $this->host = $config['smtp_host'];
        $this->port = (int) $config['smtp_port'];
        $this->username = $config['smtp_username'];
        $this->password = $config['smtp_password'];
        $this->encryption = $config['smtp_encryption'];
        $this->fromEmail = $config['smtp_from_email'];
        $this->fromName = $config['smtp_from_name'];
    }
    
    public function send(string $to, string $subject, string $body): bool {
        // Use PHPMailer or native sockets
        // Return true on success, false on failure
    }
    
    public function sendPasswordReset(string $to, string $resetLink): bool {
        $subject = 'Passwort zurücksetzen';
        $body = $this->renderTemplate('password-reset', [
            'reset_link' => $resetLink
        ]);
        return $this->send($to, $subject, $body);
    }
    
    public function testConnection(): bool {
        // Attempt SMTP connection without sending
        // Return true if connection successful
    }
}
```

**Recommendation:** Use PHPMailer library for SMTP

```json
// composer.json
{
    "require": {
        "phpmailer/phpmailer": "^6.8"
    }
}
```

**Password Reset Token Generation:**

```php
function generatePasswordResetToken(int $userId): string {
    // Generate secure random token
    $token = bin2hex(random_bytes(32)); // 64 chars
    
    // Hash for storage (like passwords - never store plain)
    $tokenHash = hash('sha256', $token);
    
    // Store in database
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    
    // Insert into password_resets table
    insertPasswordReset($userId, $tokenHash, $expiresAt);
    
    // Return plain token (this goes in the email link)
    return $token;
}

function validatePasswordResetToken(string $token): ?int {
    $tokenHash = hash('sha256', $token);
    
    $reset = getPasswordResetByHash($tokenHash);
    
    if (!$reset) {
        return null; // Token not found
    }
    
    if ($reset['used_at'] !== null) {
        return null; // Already used
    }
    
    if (strtotime($reset['expires_at']) < time()) {
        return null; // Expired
    }
    
    return $reset['user_id'];
}

function markPasswordResetUsed(string $token): void {
    $tokenHash = hash('sha256', $token);
    // UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?
}
```

-----

## JavaScript Libraries

### Required Libraries

|Library                      |Version|Purpose                 |
|-----------------------------|-------|------------------------|
|**Cropper.js**               |1.6.x  |Image cropping          |
|**FullCalendar**             |6.x    |Calendar display        |
|**Choices.js** or **Select2**|Latest |Searchable multi-selects|

### Optional Enhancements

|Library               |Purpose             |
|----------------------|--------------------|
|**SortableJS**        |Drag-drop reordering|
|**Lightbox/GLightbox**|Image preview popups|
|**Toastify**          |Toast notifications |

### CDN Sources

```html
<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>

<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/de.global.min.js"></script>

<!-- Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
```

-----

## CSS Framework Decision

**Recommendation:** Custom CSS with CSS Variables (no framework)

**Reasoning:**

- Full control over styling
- No unused CSS bloat
- Easier to maintain for single developer
- German text works better with custom sizing

**CSS Structure:**

```css
:root {
    /* Colors */
    --color-primary: #4F46E5;
    --color-primary-dark: #4338CA;
    --color-success: #22C55E;
    --color-warning: #F59E0B;
    --color-danger: #EF4444;
    --color-gray-100: #F3F4F6;
    --color-gray-200: #E5E7EB;
    --color-gray-700: #374151;
    --color-gray-900: #111827;
    
    /* Spacing */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    
    /* Border radius */
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 1rem;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    
    /* Transitions */
    --transition-fast: 150ms ease;
    --transition-normal: 300ms ease;
}
```

-----

## API Endpoints (AJAX)

For dynamic functionality, these AJAX endpoints are needed:

```
POST   /api/upload-image          # Image upload with crop data
POST   /api/games/toggle-favorite # Toggle game favorite
POST   /api/materials/toggle-favorite
POST   /api/games/check-duplicate # Check for duplicate name
POST   /api/materials/check-duplicate
POST   /api/boxes/check-duplicate
POST   /api/tags/check-duplicate
POST   /api/tags/quick-create     # Create tag inline
GET    /api/search                # Global search (returns JSON)
GET    /api/games/random          # Random game with filters
POST   /api/calendar/events       # Create calendar event
PUT    /api/calendar/events/{id}  # Update calendar event
DELETE /api/calendar/events/{id}  # Delete calendar event
GET    /api/calendar/events       # Get events for date range
POST   /api/groups/add-item       # Add game/material to group
DELETE /api/groups/remove-item    # Remove item from group
```

-----

## Development Phases

### Phase 1: Foundation (Week 1)

- [x] Project structure setup
- [ ] Database connection class (PDO)
- [ ] Basic routing system
- [ ] Base controller and model classes
- [ ] Session management
- [ ] Installation wizard
- [ ] Authentication (login, logout, remember me)
- [ ] IP ban system
- [ ] Main layout template
- [ ] CSS framework setup

### Phase 2: Core Entities - Boxes & Categories (Week 2)

- [ ] Boxes CRUD (create, read, update, delete)
- [ ] Categories CRUD
- [ ] Tags CRUD
- [ ] Image upload system with Cropper.js
- [ ] List views with pagination
- [ ] Detail views
- [ ] Form validation
- [ ] Duplicate detection
- [ ] Changelog logging

### Phase 3: Materials & Games (Week 3-4)

- [ ] Materials CRUD
- [ ] Material status management
- [ ] Materials-Boxes relationship
- [ ] Games CRUD
- [ ] Games-Materials relationship (multi-select)
- [ ] Games-Categories relationship
- [ ] Games-Tags relationship
- [ ] Difficulty selection
- [ ] Favorites functionality
- [ ] Related games section
- [ ] Preparation checklist view

### Phase 4: Search, Filter & Groups (Week 5)

- [ ] Global search (fulltext)
- [ ] Search results page
- [ ] Filter sidebar for games
- [ ] Filter sidebar for materials
- [ ] Groups CRUD
- [ ] Add items to groups (games & materials)
- [ ] Group detail view

### Phase 5: Calendar (Week 6)

- [ ] FullCalendar integration
- [ ] German locale setup
- [ ] Austrian holidays
- [ ] Calendar events CRUD
- [ ] “Played” and “Planned” event types
- [ ] Add game to calendar modal
- [ ] Event detail modal

### Phase 6: Dashboard & Polish (Week 7)

- [ ] Dashboard layout
- [ ] Statistics cards
- [ ] Random game picker
- [ ] Recently added sections
- [ ] Recently played section
- [ ] Upcoming events section
- [ ] Favorites section
- [ ] Quick action buttons

### Phase 7: Final Features (Week 8)

- [ ] Print views (games, materials, boxes)
- [ ] Print CSS
- [ ] Settings page
- [ ] Changelog view with filtering
- [ ] Clear changelog functionality
- [ ] Temp folder cleanup
- [ ] Final testing
- [ ] Bug fixes
- [ ] Documentation

-----

## Appendix: Full Feature Checklist

### Authentication & Security

- [ ] Login page
- [ ] Logout functionality
- [ ] Remember me (30-day cookie)
- [ ] Session management (24h default)
- [ ] Forgot password link on login
- [ ] Password reset request page
- [ ] Password reset email (SMTP)
- [ ] Password reset form (new password)
- [ ] Reset token generation and hashing
- [ ] Reset token expiry (1 hour)
- [ ] Reset token single-use validation
- [ ] IP tracking on login attempts
- [ ] IP ban after 5 failed attempts (15 minutes)
- [ ] Permanent IP ban after 10 failed attempts
- [ ] View banned IPs in settings
- [ ] Unban IP (temporary and permanent)
- [ ] SMTP configuration in settings
- [ ] SMTP test email button
- [ ] CSRF protection on all forms
- [ ] Password hashing (bcrypt)
- [ ] Secure session cookies

### Installation

- [ ] Requirements check (PHP version, extensions)
- [ ] Directory permissions check
- [ ] Database connection form
- [ ] Test connection button
- [ ] Schema creation
- [ ] Admin user creation
- [ ] installed.lock file
- [ ] Redirect if already installed

### Dashboard

- [ ] Statistics cards (games, materials, boxes, favorites)
- [ ] Quick action buttons
- [ ] Random game picker with filters
- [ ] Recently added games
- [ ] Recently added materials
- [ ] Recently played games (from calendar)
- [ ] Upcoming planned games
- [ ] Favorites grid

### Games

- [ ] List view (grid)
- [ ] List view (optional table toggle)
- [ ] Filtering: search, category, tag, difficulty, status, favorites
- [ ] Sorting: name, date, difficulty, recently played
- [ ] Pagination (24 per page)
- [ ] Detail view
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Difficulty selection (1-3)
- [ ] Status selection
- [ ] Multi-select categories
- [ ] Multi-select tags (with inline create)
- [ ] Multi-select materials
- [ ] Image upload with cropper
- [ ] Duplicate name detection
- [ ] Favorite toggle
- [ ] Related games (same category)
- [ ] Related games (similar tags)
- [ ] Related games (same materials)
- [ ] Materials preparation checklist
- [ ] Calendar history
- [ ] Add to calendar button
- [ ] Print view
- [ ] Bulk add to group

### Materials

- [ ] List view (grid)
- [ ] Filtering: search, box, status, favorites
- [ ] Sorting: name, date, box
- [ ] Pagination
- [ ] Detail view
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Status selection (complete, incomplete, damaged, missing)
- [ ] Box selection
- [ ] Image upload with cropper
- [ ] Duplicate name detection
- [ ] Favorite toggle
- [ ] Used in games list
- [ ] Move history (from changelog)
- [ ] Print view

### Boxes

- [ ] List view (grid/cards)
- [ ] Sorting: name, number, material count
- [ ] Detail view with contents
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Image upload with cropper
- [ ] Duplicate name detection
- [ ] Contents list
- [ ] Print view (contents list)

### Categories (Age Groups)

- [ ] List view
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Image upload
- [ ] Sort order field
- [ ] Duplicate name detection
- [ ] Game count display

### Tags (Themes)

- [ ] List view
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Image upload
- [ ] Color picker (optional)
- [ ] Inline creation from game form
- [ ] Duplicate name detection
- [ ] Game count display

### Groups

- [ ] List view
- [ ] Create form
- [ ] Edit form
- [ ] Delete with confirmation
- [ ] Image upload
- [ ] Detail view with games and materials
- [ ] Add items modal (search games/materials)
- [ ] Remove item from group
- [ ] Add to group from game/material detail
- [ ] Bulk add from list views

### Calendar

- [ ] Monthly view (default)
- [ ] Weekly view
- [ ] Day view
- [ ] German locale
- [ ] Monday week start
- [ ] Austrian holidays highlighted
- [ ] Event creation
- [ ] Event editing
- [ ] Event deletion
- [ ] “Played” event type (green)
- [ ] “Planned” event type (blue)
- [ ] Link event to game
- [ ] Event notes
- [ ] View event details modal
- [ ] Navigation (prev/next month)

### Search

- [ ] Global search bar in header
- [ ] Live search dropdown
- [ ] Full search results page
- [ ] Tabs for entity types
- [ ] Fulltext search in name, description, notes
- [ ] Minimum 3 character search
- [ ] Highlight search terms in results

### Changelog

- [ ] List view with table
- [ ] Date/time column
- [ ] User column
- [ ] Action column (create, update, delete, move)
- [ ] Entity type column
- [ ] Entity name column
- [ ] Expandable change details (JSON)
- [ ] Filter by date range
- [ ] Filter by entity type
- [ ] Filter by action type
- [ ] Clear all button with confirmation

### Settings

- [ ] Change password
- [ ] Change email
- [ ] View banned IPs
- [ ] Unban IP button
- [ ] Permanent ban form
- [ ] Clear temp folder
- [ ] Clear changelog
- [ ] Storage statistics
- [ ] Items per page setting
- [ ] Default view preference

### Image Handling

- [ ] Cropper.js integration
- [ ] Square crop enforcement
- [ ] Client-side preview
- [ ] Upload via AJAX
- [ ] Server-side processing (GD)
- [ ] WebP conversion (with JPEG fallback)
- [ ] Thumbnail generation (150x150)
- [ ] Full size generation (600x600)
- [ ] Organized directory structure
- [ ] Placeholder images
- [ ] Temp folder for originals (manual cleanup)

### Print Views

- [ ] Game detail print
- [ ] Game materials checklist print
- [ ] Material detail print
- [ ] Box contents print
- [ ] Print CSS (clean, B&W friendly)
- [ ] Print button functionality

### General UI

- [ ] German language throughout
- [ ] Responsive sidebar navigation
- [ ] Breadcrumbs
- [ ] Flash messages (success, error, warning)
- [ ] Confirmation dialogs for destructive actions
- [ ] Loading indicators for AJAX
- [ ] Form validation messages
- [ ] Date formatting (DD.MM.YYYY)
- [ ] Consistent card design
- [ ] Status color coding
- [ ] Difficulty indicators

-----

## Notes for Implementation

1. **Start with database:** Create all tables first, even if not immediately used
1. **Build auth early:** Login system blocks access to everything else
1. **Image upload reusable:** Create as a component used everywhere
1. **Changelog automatic:** Implement as a service called by all save operations
1. **German from start:** Use translation strings from day one
1. **Test with real data:** Create sample games/materials during development

-----

*This specification is complete and ready for implementation with Claude Code.*
