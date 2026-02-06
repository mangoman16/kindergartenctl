# Kindergarten Game Organizer - Development Progress

## Project Status Summary

The project has a comprehensive foundation with most core features implemented. This document tracks remaining work and improvements needed.

---

## Completed Features

### Phase 1: Foundation
- [x] Project structure setup
- [x] Database connection class (PDO)
- [x] Basic routing system (107 routes)
- [x] Base controller and model classes
- [x] Session management (secure cookies, regeneration)
- [x] Installation wizard (5 steps: Requirements, Database, Admin, SMTP, Complete)
- [x] Authentication (login, logout, remember me)
- [x] IP ban system (temporary/permanent)
- [x] Main layout template
- [x] CSS framework setup (custom CSS with variables)

### Phase 2: Core Entities
- [x] Boxes CRUD (create, read, update, delete)
- [x] Categories CRUD
- [x] Tags CRUD (with color support)
- [x] Image upload system with Cropper.js (WebP conversion)
- [x] List views with pagination
- [x] Detail views
- [x] Form validation (Validator class)
- [x] Duplicate detection (API endpoints)
- [x] Changelog logging (ChangelogService)

### Phase 3: Materials & Games
- [x] Materials CRUD
- [x] Material status management
- [x] Materials-Boxes relationship
- [x] Games CRUD
- [x] Games-Materials relationship (many-to-many)
- [x] Games-Categories relationship
- [x] Games-Tags relationship
- [x] Difficulty selection
- [x] Related games section
- [x] Favorites functionality (toggle, filter, API)

### Phase 4: Search, Filter & Groups
- [x] Global search (fulltext)
- [x] Search results page (tabbed by entity type)
- [x] Filter sidebar for games (box, category, tag, outdoor, active, favorites)
- [x] Filter sidebar for materials (box, status, favorites, search)
- [x] Groups CRUD
- [x] Add items to groups (games & materials with quantities)
- [x] Group detail view
- [x] Group item management API (add/remove)
- [x] "Add to group" modal on game/material detail pages

### Phase 5: Calendar & Dashboard
- [x] FullCalendar integration
- [x] German locale setup
- [x] Austrian holidays (fixed + Easter-dependent)
- [x] Calendar events CRUD
- [x] Event types with colors
- [x] Dashboard layout
- [x] Statistics cards (games, materials, boxes, tags, groups, favorites, events)
- [x] Quick action buttons
- [x] Recently added sections
- [x] Upcoming events section
- [x] Favorites section on dashboard
- [x] Random game picker with category/tag filters

### Phase 6: Settings & Print
- [x] Print views (games, materials, boxes)
- [x] Print CSS
- [x] Settings page
- [x] Changelog view with filtering
- [x] Clear changelog functionality
- [x] Password change
- [x] Email change
- [x] IP ban management (view, add, unban)
- [x] Storage statistics display
- [x] Clear temp folder button

### Phase 7: Email & Password Reset
- [x] Mailer service (native PHP SMTP sockets)
- [x] Password reset token generation
- [x] Password reset email template
- [x] Forgot password flow
- [x] Reset password form

---

## Recently Completed (January 2026)

### Dashboard Enhancements
- [x] Add "games played this month" statistic
- [x] Add "recently played" section from calendar events

### SMTP Configuration in Settings
- [x] Add SMTP settings section to settings page
- [x] Add SMTP test button in settings

### Bulk Actions
- [x] Add bulk selection checkboxes on games list (with "Mehrfachauswahl" toggle)
- [x] Add bulk selection checkboxes on materials list
- [x] Implement "Add to group" bulk action (with group selection modal)
- [x] Implement "Add to favorites" bulk action
- [x] Implement "Remove from favorites" bulk action

### Additional Print Views
- [x] Category games list print view
- [x] Tag games list print view
- [x] Group contents print view
- [x] Preparation checklist view (materials grouped by box)

### User Preferences
- [x] Items per page setting (user-configurable)
- [x] Default view preference (grid/list toggle)

### Search Improvements
- [x] Live search dropdown in header
- [x] Highlight search terms in results

---

## Recently Fixed (February 2026)

### Bug Fixes
- [x] Fixed ChangelogService.php - Changed u.name to u.username (users table has username, not name)
- [x] Fixed Database::saveConfig() - Added comprehensive error handling with detailed messages
- [x] Fixed finishInstallation() - Added error handling for installed.lock file creation
- [x] Fixed App.php - Sessions now start for all routes including /install/* for flash messages

### Documentation Updates
- [x] Updated README.md - Complete rewrite with proper 5-step installation guide
- [x] Added comprehensive AI-friendly comments to ChangelogService.php
- [x] Updated SECURITY_AUDIT.md - Marked password field bug as fixed

---

## Previously Fixed (January 2026)

### Database Schema Alignment
- [x] Fixed games table schema - added missing columns: instructions, min_players, max_players, duration_minutes, is_outdoor, is_active, box_id, category_id
- [x] Added quantity column to game_materials junction table
- [x] Created proper group_games and group_materials tables (replaced polymorphic group_items)
- [x] Added label column to boxes table
- [x] Updated fulltext index on games table to include instructions field
- [x] Updated Game model fillable array with difficulty and is_favorite
- [x] Updated Box model fillable array with label field
- [x] Added missing German translations (game.instructions, game.min_players, etc.)
- [x] Added label field to box form view

---

## Technical Debt / Improvements

- [x] Add unit tests for core functionality (PHPUnit setup with Validator and Security tests)
- [x] Input sanitization review (cleanInput, cleanHtml verified across controllers)
- [x] CSRF protection coverage (verified on all store/update/delete endpoints)
- [x] Add rate limiting for API endpoints (rateLimit method in ApiController)
- [x] Optimize database queries for large datasets (migration adds composite indexes)
- [x] Add database migration system (database/Migration.php + migrate.php CLI)

---

## Architecture Summary

### Controllers (14)
- AuthController, DashboardController, GameController, MaterialController
- BoxController, CategoryController, TagController, GroupController
- CalendarController, ChangelogController, SearchController
- SettingsController, InstallController, ApiController

### Models (9)
- Game, Material, Box, Category, Tag, Group
- CalendarEvent, User, PasswordReset

### Core Classes (9)
- App, Router, Database, Controller, Model
- Session, Auth, Validator, Logger

### Services (4)
- ChangelogService (audit logging)
- ImageProcessor (WebP conversion, thumbnails)
- Mailer (SMTP email)
- TransactionService (data integrity verification)

### API Endpoints (20+)
- Image upload/delete
- Duplicate checking
- Search/autocomplete (tags, materials, games)
- Quick-create (tags, materials)
- Dropdown data (boxes, categories, tags, materials)
- Favorites toggle
- Random game picker
- Group item management
- Calendar events CRUD

---

## Database Tables

All tables from specification are present:
- [x] users
- [x] categories
- [x] boxes
- [x] tags
- [x] materials
- [x] games
- [x] game_materials
- [x] game_categories
- [x] game_tags
- [x] groups
- [x] group_games / group_materials
- [x] calendar_events
- [x] changelog
- [x] ip_bans
- [x] password_resets
- [x] settings

---

## Recently Fixed (February 2026 - Code Quality Review)

### Security Fixes
- [x] Fixed ApiController auth bypass - isPublicEndpoint() used str_contains() allowing bypass via query params (e.g., /api/upload?x=/api/health). Now uses parse_url() + exact path match.
- [x] Fixed unvalidated image_path in all controllers - Added sanitizeImagePath() to Controller base class. Validates format with regex to prevent path traversal attacks via crafted image paths.
- [x] Fixed ApiController removeItemFromGroup() - Added missing item_type whitelist validation (was present in addItemToGroup but missing in remove).
- [x] Fixed User model $fillable - Removed password_hash and remember_token from mass-assignable fields to prevent mass-assignment attacks.

### Bug Fixes
- [x] Fixed CalendarEvent::getForRange() - PDO named parameters were reused (:start, :end) which fails with EMULATE_PREPARES=false. Now uses distinct params (:start1-3, :end1-3).
- [x] Fixed Material::allWithGameCount() - Same reused PDO parameter issue with :search.
- [x] Fixed Game::search() incompatible method signature - Renamed to searchGames() to avoid PHP 8.0+ deprecation for overriding Model::search() with different signature.
- [x] Fixed Game::duplicate() - Missing difficulty and is_favorite columns in INSERT caused duplicated games to lose these fields.
- [x] Fixed Game::updateTags() and updateMaterials() - Delete-then-insert was not wrapped in transaction, risking data loss on crash. Now transactional.
- [x] Fixed Tag::quickCreate() and Material::quickCreate() - Trim was applied AFTER nameExists() check, creating potential duplicates when name had leading/trailing spaces.
- [x] Fixed Group::addGame() incorrect comment - Said "INSERT IGNORE" but code used SELECT FOR UPDATE.
- [x] Fixed Group::addItem()/removeItem() - in_array() calls now use strict comparison (true as 3rd arg).
- [x] Fixed PasswordReset::cleanupExpired() comment - Said "expired tokens" but also deletes used tokens.

### Code Quality
- [x] Added comprehensive AI-readable comments to all core classes, helpers, services, and models
- [x] Fixed README.md license inconsistency (said MIT but LICENSE file is Apache 2.0)
- [x] Updated architecture summary with correct class/service counts

---

*Last updated: 2026-02-06*
