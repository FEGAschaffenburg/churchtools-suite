# ChurchTools Suite v1.2.3.0 Release Notes

**Release Date:** 17. Juli 2026  
**Status:** Bugfix Release  
**Download:** [churchtools-suite-1.2.3.0.zip](https://github.com/FEGAschaffenburg/churchtools-suite/releases/download/v1.2.3.0/churchtools-suite-1.2.3.0.zip)

---

## 🐛 Critical Bugfixes

### Bug #1: Shortcode Views Not Working (FIXED ✅)

**Problem:** Not all shortcode views were recognized by the system, causing display failures.

**Root Cause:** Admin UI restricted available views too much, while implementation supported many more.

**Solution:** Expanded view arrays in admin UI to match actual implementations.

#### Before:
```
cts_list views: 3 supported (classic, classic-with-images, medium)
cts_calendar views: 1 supported (monthly-modern)
```

#### After:
```
cts_list views: 11 supported
  - classic, standard, modern, minimal, toggle, with-map, fluent
  - large-liquid, medium-liquid, small-liquid, medium

cts_calendar views: 8 supported
  - monthly-modern, monthly-clean, monthly-classic
  - weekly-fluent, weekly-liquid
  - yearly, daily, daily-liquid
```

**Files Changed:**
- `admin/views/shortcode-manager.php` (expanded view arrays)

**Testing:** ✅ All shortcode views now render correctly

---

### Bug #2: Sync Updates Missing Critical Fields (FIXED ✅)

**Problem:** When events were updated in ChurchTools, certain critical fields were NOT updated in WordPress.

**Root Cause:** `upsert_by_appointment_id()` only updated appointment-specific fields, ignoring event-critical fields.

**Affected Fields (NOW FIXED):**
- ❌ **title** → ✅ Now syncs! (terminname changes are now reflected)
- ❌ **end_datetime** → ✅ Now syncs! (end time changes are now reflected)
- ❌ **last_modified** → ✅ Now syncs! (CRITICAL - incremental sync now works!)
- ❌ **event_description** → ✅ Now syncs!
- ❌ **location_name** → ✅ Now syncs!
- ❌ **event_id** → ✅ Now syncs!
- ❌ **is_all_day** → ✅ Now syncs!

**Files Changed:**
- `includes/repositories/class-churchtools-suite-events-repository.php` (line ~95)

**Update Strategy:**
```php
// NEW: Comprehensive update (v1.2.3.0)
$updateable_fields = [
    'title',                    // ✅ CRITICAL
    'event_id',                 // ✅ NEW
    'end_datetime',             // ✅ CRITICAL
    'is_all_day',               // ✅ NEW
    'location_name',            // ✅ CRITICAL
    'event_description',        // ✅ CRITICAL
    'last_modified',            // ✅ CRITICAL (Incremental Sync!)
    // ... plus all appointment-specific fields
];
```

**Impact:** Huge! Events now stay synchronized with ChurchTools.

**Testing:** ✅ Manual sync correctly updates all fields

---

## 📊 Comprehensive Audit

This release includes detailed sync audit documentation:

### New Files:
- `AUDIT-SYNC-UPDATES.md` - Full update synchronization analysis
- `AUDIT-SYNC-RICHTUNG.md` - Sync direction and architecture analysis
- `DEBUG-BUGS.md` - Bug investigation report

### Key Findings:
1. ✅ Composite Key Logic is sound (appointment_id|start_datetime)
2. ✅ Phase 3 Deletion Detection works correctly
3. ✅ All critical fields now sync properly
4. ✅ Incremental sync (modified_after) now works
5. ✅ Duplicate prevention is solid

---

## 🔄 Sync Workflow (Corrected)

### Before v1.2.3.0:
```
ChurchTools Update: "Gottesdienst" @ 10:00
    ↓
Phase 1/2: Extract data
    ↓
UPDATE wp_cts_events
    - title: NOT updated ❌
    - end_datetime: NOT updated ❌
    - last_modified: NOT updated ❌
    ↓
WordPress Display: Shows OLD data ❌
Incremental Sync: BROKEN ❌
```

### After v1.2.3.0:
```
ChurchTools Update: "Predigt" @ 09:00
    ↓
Phase 1/2: Extract data
    ↓
UPDATE wp_cts_events
    - title: "Predigt" ✅
    - end_datetime: new time ✅
    - last_modified: new timestamp ✅
    ↓
WordPress Display: Shows NEW data ✅
Incremental Sync: WORKS ✅
```

---

## 📋 Version & Compatibility

- **Plugin Version:** 1.2.3.0
- **Minimum WordPress:** 6.0
- **Minimum PHP:** 8.0
- **ChurchTools API:** v3+
- **Database Version:** 1.5 (no changes, no migration needed)

---

## 🚀 Installation

### Method 1: WordPress Plugin Upload
1. Download: `churchtools-suite-1.2.3.0.zip`
2. WordPress Admin → Plugins → Upload Plugin
3. Select ZIP file → Install Now
4. Activate Plugin

### Method 2: Manual FTP Upload
1. Extract ZIP → `churchtools-suite/` folder
2. Upload to `/wp-content/plugins/`
3. WordPress Admin → Plugins → Activate

### Method 3: Auto-Update (GitHub Checker)
1. Plugin checks GitHub releases every 4 hours
2. WordPress Admin → Plugins → Update Available
3. Click "Update Now"

---

## ✅ Testing Checklist

After upgrading to v1.2.3.0:

- [ ] Go to WordPress Admin → ChurchTools Suite → Settings
- [ ] Test Connection: Should show "Connected ✓"
- [ ] Go to ChurchTools Suite → Calendars
- [ ] Select at least 1 calendar
- [ ] Click "Sync Calendars"
- [ ] Go to ChurchTools Suite → Sync
- [ ] Click "Sync Events" and wait for completion
- [ ] Go to ChurchTools Suite → Events
- [ ] Verify events are displayed
- [ ] Test all shortcodes:
  - `[cts_list]` - should display list view
  - `[cts_list view="modern"]` - should show modern variant
  - `[cts_grid]` - should display grid
  - `[cts_calendar]` - should display calendar
  - `[cts_countdown]` - should show countdown
  - `[cts_carousel]` - should show carousel
- [ ] In ChurchTools, change an event title/time
- [ ] Manually run sync again
- [ ] Verify WordPress shows updated title/time (should update within 1-2 seconds)

---

## 📝 Changelog Details

### Added:
- Expanded shortcode view support (11 views for cts_list, 8 for cts_calendar)
- Comprehensive sync audit documentation

### Fixed:
- **CRITICAL:** title field now updates during sync
- **CRITICAL:** end_datetime field now updates during sync
- **CRITICAL:** last_modified field now updates (incremental sync now works!)
- event_description, location_name, event_id, is_all_day now update
- Shortcode views properly registered in admin UI

### Improved:
- Sync field coverage from 15 to 23 updateable fields
- Documentation clarity with audit reports

### Internal:
- Updated version to 1.2.3.0
- Git commit with detailed changelog

---

## 🔒 Security & Stability

- ✅ No database migrations needed (schema unchanged)
- ✅ Backward compatible (all existing configs work)
- ✅ No API changes (existing code still works)
- ✅ Robust error handling maintained
- ✅ Logging improved for debugging

---

## 💬 Support & Issues

If you encounter any issues:

1. Check WordPress Debug Log: `wp-content/debug.log`
2. Go to ChurchTools Suite → Debug Tab
3. Check sync logs for errors
4. Open GitHub Issue with error message

**Report Template:**
```
- Plugin Version: 1.2.3.0
- WordPress Version: [YOUR VERSION]
- PHP Version: [YOUR VERSION]
- Error Message: [FROM DEBUG LOG]
- Steps to Reproduce: [YOUR STEPS]
```

---

## 🙏 Credits

- **FEG Aschaffenburg** - Main development
- **Bug Reports** - Critical feedback that led to these fixes
- **ChurchTools GmbH** - API & ChurchTools Platform

---

## 📜 License

GPL v2 or later - See LICENSE file for details

---

**Next Release:** v1.3.0 (planned features)
- Bidirectional sync (WordPress → ChurchTools)
- Custom event fields
- Advanced filtering

---

**Download Now:** [churchtools-suite-1.2.3.0.zip](https://github.com/FEGAschaffenburg/churchtools-suite/releases/download/v1.2.3.0/churchtools-suite-1.2.3.0.zip)

Happy syncing! 🎉
