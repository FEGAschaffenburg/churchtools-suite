# Release Notes - v0.10.3.23

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - Doppelte Kalender + Farben

---

## 🔥 CRITICAL FIX: Doppelte Kalender + Event-Farben!

**v0.10.3.22 hatte zwei neue Bugs:**
- ❌ Kalender werden mehrfach angezeigt (2x, 3x, 4x...)
- ❌ Event-Farben werden nicht übernommen (alle blau)

---

## 🐛 Die Probleme

### Problem 1: Mehrfache Kalender

**Symptom:**
- Nach Monatswechsel erscheinen 2 identische Kalender
- Jeder weitere Klick verdoppelt die Anzahl
- Screenshot zeigt 2x "Januar 2026" Grid

**Root Cause:**
```javascript
// DOM Ready: initCalendarViews() läuft auf ALLE Kalender
$(function() {
    initCalendarViews(); // ❌ Findet ALLE .cts-calendar-monthly
});

// Nach AJAX:
function initCalendarViews() {
    $('.cts-calendar-monthly').each(function() {
        setupCalendarNavigation($(this)); // ❌ Auch auf alten Kalender!
    });
}
```

**Problem:**
- `replaceWith()` ersetzt DOM-Element
- JavaScript `$('.cts-calendar-monthly')` findet ALLE (alt + neu)
- Navigation wird mehrfach registriert
- Jeder Klick triggert mehrere AJAX-Calls

---

### Problem 2: Event-Farben fehlen

**Symptom:**
- Alle Events haben gleiche blaue Farbe (#667eea)
- Template erwartet `calendar_color` aber Events haben es nicht

**Root Cause:**
```php
// admin/class-churchtools-suite-admin.php - ajax_load_calendar_month()
$events = [];
foreach ( $raw_events as $event ) {
    $events[] = (array) $event; // ❌ Nur DB-Felder, KEINE calendar_color
}
```

**Problem:**
- DB speichert nur `calendar_id` (nicht `color`)
- Template monthly-modern.php nutzt `$event['calendar_color']`
- Ohne Farbe wird Fallback `#667eea` genommen → alle blau

---

## ✅ Die Lösungen

### Fix 1: Doppelte Kalender vermeiden

**Markiere initialisierte Kalender:**
```javascript
function initCalendarViews() {
    $('.cts-calendar-monthly').each(function() {
        const $calendar = $(this);
        // ✅ Skip wenn bereits initialisiert
        if ($calendar.data('calendar-initialized')) {
            console.log('[Calendar] Already initialized, skipping');
            return;
        }
        $calendar.data('calendar-initialized', true);
        setupCalendarNavigation($calendar);
    });
}

// Nach AJAX - markiere neuen Kalender
const $newCalendar = $(response.data.html);
$calendar.replaceWith($newCalendar);

// ✅ Markiere als initialisiert
$newCalendar.data('calendar-initialized', true);

// Nur auf DIESEM Kalender
setupCalendarNavigation($newCalendar);
```

**Effekt:**
- Jeder Kalender wird NUR EINMAL initialisiert
- Nach AJAX: Alter Kalender weg, neuer Kalender initialisiert
- Keine Duplikate mehr!

---

### Fix 2: Kalender-Farben hinzufügen

**Lade Kalender-Farbe für jedes Event:**
```php
// Lade Calendars Repository für Farben
if ( ! class_exists( 'ChurchTools_Suite_Calendars_Repository' ) ) {
    require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
}
$calendars_repo = new ChurchTools_Suite_Calendars_Repository();

$events = [];
foreach ( $raw_events as $event ) {
    $event_array = (array) $event;
    
    // ✅ Füge Kalender-Farbe hinzu
    if ( ! empty( $event->calendar_id ) ) {
        $calendar = $calendars_repo->get_by_calendar_id( $event->calendar_id );
        $event_array['calendar_color'] = $calendar ? $calendar->color : '#667eea';
    } else {
        $event_array['calendar_color'] = '#667eea'; // Default
    }
    
    $events[] = $event_array;
}
```

**Effekt:**
- Jedes Event hat `calendar_color`
- Template nutzt Kalender-Farbe
- Verschiedene Kalender → verschiedene Farben!

---

## 🔧 Technische Details

### Geänderte Dateien

**assets/js/churchtools-suite-public.js**
- `initCalendarViews()` - Prüft `data('calendar-initialized')`
- `loadCalendarMonth()` - Markiert neuen Kalender als initialized

**admin/class-churchtools-suite-admin.php**
- `ajax_load_calendar_month()` - Lädt Calendars Repository
- Schleife über Events: Fügt `calendar_color` hinzu

---

## 📊 Testing

### Erwartetes Verhalten:
- ✅ Nur EIN Kalender sichtbar
- ✅ Monatswechsel zeigt weiterhin nur EIN Kalender
- ✅ Events haben verschiedene Farben (je nach Kalender)
- ✅ Farben entsprechen ChurchTools-Kalendern

### Console Debug:
```
[Calendar] Already initialized, skipping
[Calendar] Loading month: 2026 2
[Calendar] Re-initializing event handlers for new calendar
[Calendar] Month loaded successfully
```

### Farben-Test:
- Öffne Kalender
- Events sollten unterschiedliche Farben haben
- Kalender "Bibelkreis" → z.B. lila
- Kalender "Gottesdienst" → z.B. blau
- etc.

---

## 🔄 Migration von v0.10.3.22

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender öffnen
4. Monat wechseln → sollte nur EIN Kalender bleiben
5. Events sollten verschiedene Farben haben

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.18: Logging
- v0.10.3.19: get_events_in_range()
- v0.10.3.20: Array Conversion
- v0.10.3.21: Event-Handler Re-Init
- v0.10.3.22: Month Display Fix
- **v0.10.3.23:** **Doppelte Kalender + Farben Fix** ✅

---

## 🎉 Kalender FINAL funktional!

**Was funktioniert jetzt:**
- ✅ Monatswechsel vorwärts/rückwärts
- ✅ Titel zeigt richtigen Monat
- ✅ Grid zeigt richtige Tage
- ✅ **NUR EIN Kalender sichtbar**
- ✅ **Events haben richtige Farben**
- ✅ Events klickbar
- ✅ Modal funktioniert

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.23
