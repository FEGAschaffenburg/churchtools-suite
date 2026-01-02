# Release Notes v0.10.3.9

**Datum:** 2. Januar 2026  
**Typ:** Bugfix (Critical)

---

## 🐛 Kritische Bugfixes

### Problem 1: `enable_modal` Parameter wurde ignoriert
**Symptom:** Events waren immer clickable, auch wenn Shortcode `enable_modal="false"` setzte.

**Root Cause:**
- Templates hatten `cts-event-clickable` Klasse hart codiert
- Kalender-Monatswechsel gab `enable_modal` nicht an AJAX weiter
- JavaScript extrahierte `enable_modal` nicht aus data-Attribut

**Behobene Templates:**
- ✅ `list/classic.php`
- ✅ `list/medium.php`
- ✅ `list/modern.php`
- ✅ `list/fluent.php`
- ✅ `list/compact.php`
- ✅ `grid/simple.php`
- ✅ `grid/modern.php`
- ✅ `grid/colorful.php`
- ✅ `widget/upcoming.php`
- ✅ `search/classic.php`

**Änderungen:**
```php
// VORHER:
<div class="cts-event-modern cts-event-clickable" data-event-id="...">

// NACHHER:
<?php $enable_modal = $args['enable_modal'] ?? true; ?>
<div class="cts-event-modern <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" 
     <?php if ( $enable_modal ) : ?>data-event-id="..."<?php endif; ?>>
```

**Kalender-Template:**
```php
// VORHER:
<div class="cts-calendar cts-calendar-monthly" 
     data-calendar-ids="..." data-limit="100">

// NACHHER:
<div class="cts-calendar cts-calendar-monthly" 
     data-calendar-ids="..." data-limit="100"
     data-enable-modal="<?php echo esc_attr( $args['enable_modal'] ?? true ? 'true' : 'false' ); ?>">
```

**JavaScript:**
```javascript
// VORHER:
const calendarIds = $calendar.data('calendar-ids') || '';
const limit = $calendar.data('limit') || 100;

// NACHHER:
const calendarIds = $calendar.data('calendar-ids') || '';
const limit = $calendar.data('limit') || 100;
const enableModal = $calendar.data('enable-modal') !== undefined ? $calendar.data('enable-modal') : true;
```

**AJAX-Handler:**
```php
// VORHER:
$calendar_ids = isset( $_POST['calendar_ids'] ) ? sanitize_text_field( $_POST['calendar_ids'] ) : '';
$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 100;

// NACHHER:
$calendar_ids = isset( $_POST['calendar_ids'] ) ? sanitize_text_field( $_POST['calendar_ids'] ) : '';
$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 100;
$enable_modal = isset( $_POST['enable_modal'] ) ? filter_var( $_POST['enable_modal'], FILTER_VALIDATE_BOOLEAN ) : true;

$atts = [
    'view' => 'monthly-modern',
    'enable_modal' => $enable_modal,
    // ...
];
```

---

### Problem 2: Fehler beim Kalender-Monatswechsel
**Symptom:** Endlos-Ladekreis beim Klick auf Vor/Zurück-Buttons.

**Root Cause:** 
- `enable_modal` Parameter fehlte im AJAX-Call
- Template konnte nach Reload nicht initialisiert werden
- Kalender blieb im Loading-State stecken

**Lösung:**
- ✅ `enable_modal` wird jetzt korrekt übertragen
- ✅ Kalender re-initialisiert sich nach AJAX-Reload
- ✅ Loading-State wird korrekt entfernt

---

## ✅ Testing

**Testfälle:**
1. ✅ Shortcode `[churchtools_events view="list-modern" enable_modal="false"]` → Keine Klicks möglich
2. ✅ Shortcode `[churchtools_events view="list-modern" enable_modal="true"]` → Klicks öffnen Modal
3. ✅ Kalender-Monatswechsel mit `enable_modal="false"` → Kein Click-to-Details
4. ✅ Kalender-Monatswechsel mit `enable_modal="true"` → Click-to-Details funktioniert

---

## 🔧 Geänderte Dateien

**Templates (10):**
- `templates/list/classic.php`
- `templates/list/medium.php`
- `templates/list/modern.php`
- `templates/list/fluent.php`
- `templates/list/compact.php`
- `templates/grid/simple.php`
- `templates/grid/modern.php`
- `templates/grid/colorful.php`
- `templates/widget/upcoming.php`
- `templates/search/classic.php`
- `templates/calendar/monthly-modern.php`

**Backend:**
- `admin/class-churchtools-suite-admin.php` (ajax_load_calendar_month)

**Frontend:**
- `assets/js/churchtools-suite-public.js` (loadCalendarMonth)

---

## 📊 Impact

**Betroffene Shortcode-Parameter:**
- `enable_modal` - Jetzt korrekt in ALLEN Templates respektiert

**User-Experience:**
- ✅ Volle Kontrolle über Click-to-Details Verhalten
- ✅ Kalender-Navigation funktioniert einwandfrei
- ✅ Konsistentes Verhalten über alle Views

---

## 🚀 Deployment

```bash
git add .
git commit -m "v0.10.3.9 - CRITICAL: enable_modal Parameter + Calendar Navigation Fix"
git tag v0.10.3.9
git push origin main
git push origin v0.10.3.9
```

---

**Vorherige Version:** v0.10.3.8 (Modal Design Enhancement)  
**Nächste geplante Version:** v0.10.4.0 (Console-Logging entfernen)
