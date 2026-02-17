# Roadmap: KindergartenCtl Fix & Improve

## Phase 1: Critical Bugs (Crashes & Broken Features)

### 1.1 Image Upload/Cropping Stuck
**Problem:** Cropping gets stuck on "Bild zuschneiden" — `canvas.toBlob('image/webp')` silently fails on Safari and some browsers that don't support WebP encoding.
**Files:** `public/assets/js/app.js:248-269`
**Fix:**
- Switch `canvas.toBlob()` from `'image/webp'` to `'image/jpeg'` (universal support)
- Add error handling + timeout fallback so the modal never freezes
- Fix hardcoded `image.webp` filename to match actual MIME type
- Add a loading spinner / "Processing..." indicator on the crop button

### 1.2 Calendar Create 404
**Problem:** Quick-create "Eintrag hinzufügen" links to `/calendar/create` which doesn't exist. Calendar events are created via modal on `/calendar`.
**Files:** `src/views/partials/sidebar.php:76`, `src/config/routes.php`
**Fix:**
- Change the quick-create link to point to `/calendar` instead of `/calendar/create`
- Add a URL fragment or query param (e.g. `/calendar?create=1`) and have the calendar JS auto-open the create modal when that param is present

### 1.3 Design/Customization Settings Cache Issue
**Problem:** Saving customization (theme color, pattern) requires 4-5 clicks because the browser caches the HTML with old inline CSS variables.
**Files:** `src/controllers/SettingsController.php:677-707`, `src/views/layouts/main.php:30-37`
**Fix:**
- Add `Cache-Control: no-cache, no-store, must-revalidate` and `Pragma: no-cache` headers on the settings POST response/redirect
- OR (better): Convert customization save to AJAX — update CSS variables in JS immediately after successful save without full page reload

### 1.4 Game Detail View Broken
**Problem:** Multiple issues on `games/show.php`:
- Box location query uses old `b.location` varchar instead of joining `locations` table via `location_id`
- Modal backdrop uses `position: absolute` instead of `fixed` — can click through it
- Modal has hardcoded `background: white` — broken in dark mode
- Custom `.modal` / `.modal-backdrop` classes conflict with global CSS modal system
**Files:** `src/models/Game.php:138` (query), `src/views/games/show.php:390-479` (modal)
**Fix:**
- Update `Game::findWithRelations()` to JOIN `locations` table via `boxes.location_id`
- Fix modal to use the app's standard `.modal-overlay` pattern
- Change `position: absolute` → `position: fixed` on backdrop
- Replace `background: white` with `var(--color-white)`

---

## Phase 2: Data Integrity (Model $fillable Gaps)

### 2.1 Add Missing `notes` to 6 Models
**Problem:** DB has `notes TEXT NULL` columns (via migrations) for games, categories, tags, groups, locations, calendar_events — but models don't include `notes` in `$fillable`.
**Files:**
- `src/models/Game.php:34-48` — add `'notes'`
- `src/models/CalendarEvent.php:19-28` — add `'notes'` and `'event_type'`
- `src/models/Category.php:20-25` — add `'notes'`
- `src/models/Tag.php:19-24` — add `'notes'`
- `src/models/Group.php:22-26` — add `'notes'`
- `src/models/Location.php:9-12` — add `'notes'`

**Note:** No controllers or views currently use these fields, so this is a prerequisite fix. The UI for notes can be added later if desired.

---

## Phase 3: Navigation & Layout Restructuring

### 3.1 Move Changelog Into Settings
**Problem:** Changelog occupies a top-level icon rail slot but belongs under settings.
**Files:** `src/views/partials/sidebar.php:41-42,134-142`, `src/views/settings/index.php`, `src/config/routes.php:110-111`
**Fix:**
- Remove the changelog button from the icon rail in `sidebar.php`
- Remove the changelog context sidebar section
- Add a "Changelog" link to the settings menu page (`settings/index.php`)
- Keep existing routes (`/changelog`, `/changelog/clear`) working — just change the navigation path to reach them
- Update nav section detection to remove changelog case

### 3.2 Collapsible Filter UI
**Problem:** Filters on games/materials index pages are always visible, taking up space. "Zu Gruppe hinzufügen" is inside bulk actions which is confusing.
**Files:** `src/views/games/index.php:14-52`, `src/views/materials/index.php:14-27`, `public/assets/css/style.css:1358-1434`
**Fix:**
- Add a "Filter" button that toggles the `.inline-filters` section visibility
- Persist filter visibility state in localStorage
- Show an active-filter indicator (badge/dot) on the button when filters are applied
- When filters are collapsed but active, show a subtle summary line (e.g. "3 filters active")
- Move "Zu Gruppe hinzufügen" as a more discoverable action — either as a right-click context action or a persistent small button per-card

---

## Phase 4: Global Search Overhaul

### 4.1 Make Search Fully Global
**Problem:** Search is context-aware (prioritizes current section). User wants it fully global with equal treatment of all entity types.
**Files:** `src/views/partials/header.php:2-14,84-106`, `src/controllers/ApiController.php:377-476`, `public/assets/css/style.css`
**Fix:**
- Remove context detection from header.php — always send context `'all'`
- Equalize result limits across entity types in `ApiController::liveSearch()`
- Use a universal placeholder like "Search everything... (Ctrl+K)"

### 4.2 Search History & Recent Items
**Files:** `src/views/partials/header.php`, `public/assets/js/app.js` (or inline JS in header)
**Fix:**
- Store recent searches in localStorage (last 5-10 queries)
- Store recently clicked/found items in localStorage
- Show these in the command palette when opened (before user types)
- Section layout: "Recent Searches" → "Recently Found" → live results as user types
- Add a "Clear history" link

### 4.3 Advanced Search Filters
**Fix:**
- Add filter chips/buttons in the command palette: Games, Materials, Boxes, Tags, Groups, Calendar
- Allow toggling multiple filters to narrow results
- Pass selected filters to the API as a `types[]` parameter
- Update `ApiController::liveSearch()` to respect type filters
- Show result counts per type in the filter chips

---

## Phase 5: CSS & Theme Fixes

### 5.1 Fix Undefined CSS Variable
**Problem:** `--color-yellow-100` used at `style.css:1106` for `<mark>` highlight but never defined.
**Fix:** Add `--color-yellow-100: #FEF9C3;` to `:root` in style.css

### 5.2 Dark Mode Gaps
**Problem:** Several elements lack dark mode overrides.
**Files:** `public/assets/css/style.css` (dark mode section)
**Fix:** Add `[data-theme="dark"]` overrides for:
- `.mini-cal-day`, `.mini-cal-day-dot`, `.mini-cal-popover`
- `.dash-picker-select` option backgrounds
- `.detail-list` (`<dl>` on show pages)
- `.form-error` class
- `.empty-state` styling
- `.page-footer` elements

---

## Phase 6: Internationalization Fixes

### 6.1 Replace Hardcoded German Strings
**Problem:** Several views bypass `__()` with inline German text.
**Files:**
- `src/views/dashboard/index.php` — "Noch keine Spiele vorhanden", "Noch keine Änderungen.", "Kein passendes Spiel gefunden."
- `src/views/games/index.php` — "Alle Boxen", "Alle Themen"
- `src/views/games/form.php` — "-- Keine Box --"
- `src/views/games/show.php` — "Inaktiv", "Outdoor", "Spieler", "Minuten", "Nicht angegeben", "Favorit entfernen", "Als Favorit markieren"
**Fix:**
- Add translation keys to both `src/lang/de.php` and `src/lang/en.php`
- Replace all hardcoded strings with `__('key')` calls

---

## Execution Order

| Order | Phase | Priority | Estimated Scope |
|-------|-------|----------|-----------------|
| 1 | 1.1 Image crop fix | P0 | 1 file (JS) |
| 2 | 1.2 Calendar 404 | P0 | 2 files |
| 3 | 1.3 Settings cache | P0 | 1-2 files |
| 4 | 1.4 Game view fix | P0 | 2 files (model + view) |
| 5 | 2.1 Model $fillable | P1 | 6 model files |
| 6 | 3.1 Move changelog | P1 | 3 files |
| 7 | 3.2 Collapsible filters | P1 | 3 files (views + CSS) |
| 8 | 5.1 CSS variable fix | P1 | 1 file |
| 9 | 5.2 Dark mode gaps | P2 | 1 file (CSS) |
| 10 | 4.1-4.3 Search overhaul | P2 | 3-4 files |
| 11 | 6.1 i18n fixes | P2 | 4-5 files |

---

## MD Files to Update After Implementation

- **CLAUDE.md** — Update file counts, navigation structure (changelog moved), search system description
- **BUG_AUDIT.md** — Log all bugs found and fixed with file:line references
- **CODE_QUALITY.md** — Note $fillable consistency improvements
- **todo.md** — Mark completed tasks, add new ones discovered
