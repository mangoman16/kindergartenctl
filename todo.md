# Kindergarten Game Organizer - Development Progress

## Project Status Summary

The project has a solid foundation with most core features implemented. This document tracks remaining work and improvements needed.

---

## Completed Features

### Phase 1: Foundation
- [x] Project structure setup
- [x] Database connection class (PDO)
- [x] Basic routing system
- [x] Base controller and model classes
- [x] Session management
- [x] Installation wizard (4 steps)
- [x] Authentication (login, logout, remember me)
- [x] IP ban system
- [x] Main layout template
- [x] CSS framework setup

### Phase 2: Core Entities
- [x] Boxes CRUD (create, read, update, delete)
- [x] Categories CRUD
- [x] Tags CRUD
- [x] Image upload system with Cropper.js
- [x] List views with pagination
- [x] Detail views
- [x] Form validation
- [x] Duplicate detection
- [x] Changelog logging

### Phase 3: Materials & Games
- [x] Materials CRUD
- [x] Material status management
- [x] Materials-Boxes relationship
- [x] Games CRUD
- [x] Games-Materials relationship (multi-select)
- [x] Games-Categories relationship
- [x] Games-Tags relationship
- [x] Difficulty selection
- [x] Related games section

### Phase 4: Search, Filter & Groups
- [x] Global search (fulltext)
- [x] Search results page
- [x] Filter sidebar for games
- [x] Filter sidebar for materials
- [x] Groups CRUD
- [x] Add items to groups (games & materials)
- [x] Group detail view

### Phase 5: Calendar & Dashboard
- [x] FullCalendar integration
- [x] German locale setup
- [x] Calendar events CRUD
- [x] "Played" and "Planned" event types
- [x] Dashboard layout
- [x] Statistics cards
- [x] Quick action buttons
- [x] Recently added sections
- [x] Upcoming events section

### Phase 6: Settings & Print
- [x] Print views (games, materials, boxes)
- [x] Print CSS
- [x] Settings page
- [x] Changelog view with filtering

---

## Recently Completed (This Session)

### Mailer Service (SMTP) - DONE
- [x] Created `src/services/Mailer.php` class
- [x] Implemented SMTP connection using native PHP sockets
- [x] Added `sendPasswordReset()` method
- [x] Added `sendTestEmail()` method
- [x] Added `testConnection()` method
- [x] Created email template `src/views/auth/emails/password-reset.php`
- [x] Integrated with AuthController for password reset
- [x] Integrated with SettingsController for SMTP testing

### Favorites System - DONE
- [x] Added `toggleFavorite()` method to Game model
- [x] Added `toggleFavorite()` method to Material model
- [x] Added `getFavorites()` method to both models
- [x] Added API routes for favorites toggle (`POST /api/games/{id}/toggle-favorite`)
- [x] Added API routes for materials toggle (`POST /api/materials/{id}/toggle-favorite`)

### Dashboard Enhancements - DONE
- [x] Added random game picker with category/tag filters
- [x] Added favorites section showing up to 8 favorite games
- [x] Added favorites count to stats
- [x] Added `/api/games/random` endpoint

### Group Item Management API - DONE
- [x] Added `addItem()` method to Group model
- [x] Added `removeItem()` method to Group model
- [x] Added `POST /api/groups/add-item` endpoint
- [x] Added `POST /api/groups/remove-item` endpoint

### Favorites UI - DONE
- [x] Added favorite toggle button to game detail page
- [x] Added favorite toggle button to material detail page
- [x] Added favorites filter checkbox to games list page
- [x] Added favorites filter and search to materials list page
- [x] Updated Game model with is_favorite filter support
- [x] Updated Material model with filters in allWithGameCount

---

## Remaining Tasks

### High Priority

#### 1. Mailer Service (SMTP) - COMPLETED
- [x] Create `src/services/Mailer.php` class
- [x] Implement SMTP connection using socket or PHPMailer
- [x] Add `sendPasswordReset()` method
- [x] Add `testConnection()` method
- [x] Create email template for password reset (`src/views/auth/emails/password-reset.php`)

#### 2. Dashboard Enhancements - COMPLETED
- [x] Add random game picker with filters (category, tag)
- [x] Add favorites section (max 8 favorite games)
- [ ] Add "games played this month" statistic
- [ ] Add "recently played" section from calendar

#### 3. Favorites Functionality - COMPLETED
- [x] Add `POST /api/games/toggle-favorite` API endpoint
- [x] Add `POST /api/materials/toggle-favorite` API endpoint
- [x] Add favorite toggle buttons on detail pages
- [x] Add favorites filter on list pages

### Medium Priority

#### 4. Group Item Management API - COMPLETED
- [x] Add `POST /api/groups/add-item` endpoint
- [x] Add `POST /api/groups/remove-item` endpoint
- [x] Add "Add to group" modal on game/material detail pages

#### 5. Bulk Actions
- [ ] Add bulk selection on games list
- [ ] Add bulk selection on materials list
- [ ] Implement "Add to group" bulk action
- [ ] Implement "Add to favorites" bulk action
- [ ] Implement "Remove from favorites" bulk action

#### 6. Austrian Holidays
- [ ] Add Austrian holidays highlighting on calendar
- [ ] Create holidays calculation function (Easter-dependent holidays)

### Low Priority

#### 7. Additional Print Views
- [ ] Category games list print view
- [ ] Tag games list print view
- [ ] Group contents print view
- [ ] Preparation checklist view (materials grouped by box)

#### 8. Settings Enhancements
- [ ] Clear temp folder button
- [ ] Storage statistics display
- [ ] Items per page setting
- [ ] Default view preference (grid/list)

#### 9. Search Improvements
- [ ] Live search dropdown in header
- [ ] Highlight search terms in results

---

## Technical Debt / Improvements

- [ ] Add unit tests for core functionality
- [ ] Add input sanitization review
- [ ] Review CSRF protection coverage
- [ ] Add rate limiting for API endpoints
- [ ] Optimize database queries for large datasets
- [ ] Add database migration system
- [ ] Add composer.json with PHPMailer dependency

---

## Database Schema Verification

Verify that all tables from specification exist:
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
- [x] group_items (or group_games/group_materials)
- [x] calendar_events
- [x] changelog
- [x] ip_bans
- [x] password_resets
- [x] settings

---

## Files Structure Status

### Controllers (All Present)
- `AuthController.php` - Authentication
- `DashboardController.php` - Dashboard
- `GameController.php` - Games CRUD
- `MaterialController.php` - Materials CRUD
- `BoxController.php` - Boxes CRUD
- `CategoryController.php` - Categories CRUD
- `TagController.php` - Tags CRUD
- `GroupController.php` - Groups CRUD
- `CalendarController.php` - Calendar
- `ChangelogController.php` - Changelog
- `SearchController.php` - Search
- `SettingsController.php` - Settings
- `InstallController.php` - Installation
- `ApiController.php` - API endpoints

### Services
- `ChangelogService.php` - Present
- `ImageProcessor.php` - Present
- `Mailer.php` - Present (native PHP SMTP implementation)

### Core Classes (All Present)
- `App.php`
- `Router.php`
- `Database.php`
- `Controller.php`
- `Model.php`
- `Session.php`
- `Auth.php`
- `Validator.php`

---

## Next Steps (Recommended Order)

1. **Implement Mailer service** - Required for password reset functionality
2. **Add favorites API** - Quick win, improves user experience
3. **Enhance dashboard** - Random game picker and favorites section
4. **Add group item API** - Enables "Add to group" from detail pages
5. **Implement bulk actions** - List page improvements
6. **Add Austrian holidays** - Calendar enhancement
7. **Additional print views** - Nice to have
8. **Settings enhancements** - Polish

---

*Last updated: 2026-01-08*
