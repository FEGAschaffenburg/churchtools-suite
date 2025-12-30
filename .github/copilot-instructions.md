# ChurchTools Suite - AI Coding Guide

## Project Overview
WordPress plugin for syncing ChurchTools calendars, events, and appointments. Two codebases exist:
- **churchtools-suite**: Active development (v0.3.8.4, PHP 8.0+, WP 6.0+)
- **repro-ct-suite**: Legacy/prototype version (v1.0.0.10, PHP 7.4+, WP 5.0+)

Focus development on `churchtools-suite/` unless explicitly working on legacy code.

## Architecture

### Core Components
1. **Main Plugin Class** (`includes/class-churchtools-suite.php`)
   - Entry point loaded by `churchtools-suite.php`
   - Uses Loader pattern to register hooks
   - Runs migrations on every init via `ChurchTools_Suite_Migrations::run_migrations()`

2. **Repository Pattern** (extends `ChurchTools_Suite_Repository_Base`)
   - `ChurchTools_Suite_Calendars_Repository`: Manages `wp_cts_calendars` table
   - `ChurchTools_Suite_Events_Repository`: Manages `wp_cts_events` table
   - Base provides: `get_all()`, `get_by_id()`, CRUD operations with prepared statements
   - Tables use plugin prefix: `CHURCHTOOLS_SUITE_DB_PREFIX` = `'cts_'`

3. **Service Layer** 
   - `ChurchTools_Suite_Event_Sync_Service`: 2-phase event sync (Events API → events with appointments, Appointments API → standalone appointments)
   - `ChurchTools_Suite_CT_Client`: Cookie-based authentication with ChurchTools API
   - Session keep-alive runs hourly via WP-Cron

4. **Admin Interface** (`admin/class-churchtools-suite-admin.php`)
   - Tab-based UI: Dashboard, Settings, Calendars, Events, Sync, Debug
   - Views in `admin/views/tab-*.php`
   - AJAX handlers registered directly (not via `init` hook) in `register_ajax_handlers()`

### Database Schema Evolution
- Migration system tracks version in `wp_options['churchtools_suite_db_version']`
- Current schema: v1.1
- Events table: `event_id` (ChurchTools event ID), `appointment_id` (for standalone appointments), `raw_payload` (full API response)
- **Critical**: v0.3.7.x renamed `external_id` → `event_id`. See `MIGRATION-GUIDE.md`

## Development Patterns

### AJAX Endpoints
All AJAX actions prefixed with `cts_`:
```php
add_action('wp_ajax_cts_test_connection', [$this, 'ajax_test_connection']);
add_action('wp_ajax_cts_sync_calendars', [$this, 'ajax_sync_calendars']);
add_action('wp_ajax_cts_sync_events', [$this, 'ajax_sync_events']);
add_action('wp_ajax_cts_save_calendar_selection', [$this, 'ajax_save_calendar_selection']);
```
Always verify nonce and permissions before processing.

### Versioning Convention
Version format: `Major.Minor.Patch.Build`
- Increment Build for minimal changes (e.g., 0.3.8.3 → 0.3.8.4)
- Update in both main plugin file header AND constant definition

### Data Synchronization
Event sync uses configurable time ranges:
- `churchtools_suite_sync_days_past`: Default 7 days
- `churchtools_suite_sync_days_future`: Default 90 days
- Sync intelligently handles events with appointments (1:N) vs standalone appointments
- Duplicate prevention via `appointment_id` tracking across phases

### ChurchTools API Patterns
Authentication uses cookie-based sessions (not token-based):
1. Login via `/api/login` with username/password
2. Store cookies in `wp_options['churchtools_suite_ct_cookies']`
3. Include cookies in all subsequent API calls
4. Session keep-alive pings hourly via `churchtools_suite_session_keepalive` cron hook

Key API endpoints:
- `/api/events?from=YYYY-MM-DD&to=YYYY-MM-DD` (Phase 1: events with appointments)
- `/api/calendars/{id}/appointments?from=YYYY-MM-DD&to=YYYY-MM-DD` (Phase 2: standalone)

### WordPress Integration
- Uses WordPress date/time formats from Settings → General
- Timezone-aware: `get_date_from_gmt()` for UTC → local conversion
- Auto-sync via WP-Cron: `churchtools_suite_auto_sync` hook (hourly/daily configurable)

## Critical Files

### Must-read for major changes:
- [includes/class-churchtools-suite-migrations.php](../includes/class-churchtools-suite-migrations.php) - Schema versioning
- [includes/services/class-churchtools-suite-event-sync-service.php](../includes/services/class-churchtools-suite-event-sync-service.php) - Sync logic
- [MIGRATION-GUIDE.md](../MIGRATION-GUIDE.md) - Breaking changes
- [ROADMAP.md](../ROADMAP.md) - Feature planning and implementation stages

### Admin UI structure:
- [admin/views/admin-page.php](../admin/views/admin-page.php) - Tab navigation
- [admin/css/churchtools-suite-admin.css](../admin/css/churchtools-suite-admin.css) - UI styling
- [admin/js/churchtools-suite-admin.js](../admin/js/churchtools-suite-admin.js) - AJAX interactions

## Build & Deploy


### Creating WordPress-compatible ZIP:
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.3.8.5"
```
- Archives old ZIPs to `C:\privat\archiv\`
- Normalizes paths to forward slashes (WordPress requirement)
- Excludes: `.git`, `scripts`, `tests`, `node_modules`, `composer.*`
- Output: `C:\privat\churchtools-suite-{version}.zip`

**WICHTIG:** Nach jedem neuen ZIP-Release muss auch ein neues GitHub-Release/Tag erstellt werden (z.B. `git tag v0.9.3.0; git push; git push --tags` und Release im Web anlegen). Erst dann erkennt die Auto-Update-Funktion die neue Version!

### Testing:
1. Install plugin in local WordPress
2. Test connection: Settings → ChurchTools → Test Connection
3. Sync calendars: Calendars tab → Sync
4. Sync events: Sync tab → Sync Events
5. Check Debug tab for errors

## Common Tasks

### Adding a new migration:
1. Increment `ChurchTools_Suite_Migrations::DB_VERSION`
2. Add migration method named `migrate_to_{version}` (underscores for dots)
3. Make migrations idempotent (safe to run multiple times)

### Adding a new AJAX endpoint:
1. Add action in `admin/class-churchtools-suite-admin.php::register_ajax_handlers()`
2. Create handler method prefixed with `ajax_`
3. Verify nonce: `check_ajax_referer('churchtools_suite_admin', 'nonce')`
4. Check permissions: `current_user_can('manage_options')`
5. Return JSON: `wp_send_json_success()` or `wp_send_json_error()`

### Adding a new repository:
1. Extend `ChurchTools_Suite_Repository_Base`
2. Define `$table` in constructor (e.g., `'cts_new_table'`)
3. Add table creation in migrations
4. Implement domain-specific query methods

## Debugging

Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs:
- `wp-content/debug.log` for PHP errors
- Admin → Debug tab for sync statistics and API responses
- Phase 1/2 sync logging shows event structures and appointment tracking
