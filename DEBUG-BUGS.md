# Bug Fixing Report - v1.2.2.0

## Bug #1: Shortcuts funktionieren nicht ✅ FIXED

### Root Cause
Views in `shortcode-manager.php` waren zu restriktiv.

### Fix Applied
- **cts_list**: Erweitert von 3 auf 11 Views
  - classic, standard, modern, minimal, toggle, with-map, fluent, large-liquid, medium-liquid, small-liquid, medium
- **cts_calendar**: Erweitert von 1 auf 8 Views
  - monthly-modern, monthly-clean, monthly-classic, weekly-fluent, weekly-liquid, yearly, daily, daily-liquid

### Files Changed
- [admin/views/shortcode-manager.php](admin/views/shortcode-manager.php) - Lines 37-42, 74-79

### Status
✅ **FIXED** - All shortcode views now properly registered

---

## Bug #2: Termine werden nicht synchronisiert  🔧 IN PROGRESS

### Root Causes Identified

#### 2a: No calendars selected
- If `get_selected_calendar_ids()` returns empty array
- Sync aborts with "Keine Kalender ausgewählt"
- **Fix**: Ensure calendars are synced first in admin

#### 2b: API structure issues
- `extract_event_data()` and `extract_appointment_data()` have complex fallback logic
- May fail if API returns unexpected structure
- **Fix**: Add more robust error handling

#### 2c: Missing appointment_id
- If `build_appointment_composite_key()` returns empty string
- Sync marks event as skipped
- **Fix**: Better null checking

### Investigation Steps
1. Check if any calendars are saved in `wp_cts_calendars` table
2. Check if any calendars are marked as selected
3. Check WordPress debug log for API errors
4. Verify ChurchTools API connection via test_connection endpoint

### Files to Monitor
- `includes/services/class-churchtools-suite-event-sync-service.php`
  - Line 105-120: Calendar selection check
  - Line 290+: Phase 1 event processing  
  - Line 650+: Phase 2 appointment processing
  - Line 823+: Event data extraction
  - Line 1000+: Appointment data extraction

### Recommended Fixes (TODO)
1. Add calendar selection validator in admin UI
2. Improve error handling in data extraction
3. Add retry logic for failed API calls
4. Better logging for composite key failures

---

## Testing Checklist

- [ ] Go to WordPress Admin → ChurchTools → Calendars
- [ ] Check if any calendars are listed
- [ ] Select at least one calendar
- [ ] Go to Sync tab
- [ ] Click "Termine synchronisieren"
- [ ] Check for error messages
- [ ] Go to Events tab
- [ ] Verify events are displayed
- [ ] Test all shortcodes: `[cts_list]`, `[cts_grid]`, `[cts_calendar]`, `[cts_countdown]`, `[cts_carousel]`

---

## Version
- **Plugin**: v1.2.2.0
- **Bug Fix Date**: 17. Juli 2026
- **Status**: Partial Fix (Bug #1 complete, Bug #2 needs calendar selection)
