# Release Notes v1.2.0.25

**Release Date:** April 27, 2026

## Performance Optimization

### Image Deduplication for Recurring Events

**Issue:** Images were re-imported multiple times for recurring events that share the same image URL, causing unnecessary downloads and storage duplication.

**Solution:** 
- Added `find_existing_image_by_url()` method in ImageImporter to detect if an image with the same URL was already imported
- Modified sync logic (both Event Phase 1 and Appointment Phase 2) to reuse existing images instead of re-importing
- Only downloads new images when URL changes or image doesn't exist in the system

**Benefits:**
- Significantly faster sync for recurring events
- Reduced bandwidth usage
- Optimized WordPress media library storage
- Cleaner attachment metadata tracking

**Technical Details:**
- New method queries `wp_postmeta` for `_cts_imported_from` meta key
- Maintains backward compatibility with existing image metadata
- Logs image reuse in debug output for troubleshooting
- Applies to both recurring event imports and recurring appointments

## Files Modified
- `includes/class-churchtools-suite-image-importer.php` - Added `find_existing_image_by_url()` method
- `includes/services/class-churchtools-suite-event-sync-service.php` - Updated Phase 1 and Phase 2 sync logic
- `churchtools-suite.php` - Version bump

## Compatibility
- ✅ PHP 8.0+
- ✅ WordPress 6.0+
- ✅ Backward compatible with existing database

## Notes for Users
- This update automatically optimizes future syncs
- Existing images remain unchanged
- First sync after update may show reuse of images from previous syncs
