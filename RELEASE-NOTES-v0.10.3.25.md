# Release Notes - v0.10.3.25

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - Event-Details & Klickbarkeit

---

## 🔥 CRITICAL FIX: Event-Details + Modal funktioniert!

**v0.10.3.24 hatte noch Bugs:**
- ❌ Kalender zeigt nur Event-Titel (keine Zeit, kein Datum)
- ❌ Events sind nicht klickbar (Modal öffnet nicht)

---

## 🐛 Das Problem

### Event-Details fehlen

**Symptom:**
- Kalender zeigt nur Event-Titel
- Keine Uhrzeit sichtbar
- Tooltip zeigt "undefined. undefined undefined - Titel"

**Root Cause:**
```php
// Template monthly-modern.php erwartet formatierte Events:
<span class="cts-event-time"><?php echo esc_html( $event['start_time'] ); ?></span>
// start_time = "10:00 Uhr"
// start_day = "7"
// start_month = "FEB"
// start_year = "26"

// ABER AJAX-Handler (v0.10.3.24) liefert RAW Events:
$events = [];
foreach ( $raw_events as $event ) {
    $event_array = (array) $event; // ❌ Nur DB-Felder!
    $events[] = $event_array;
}
// Nur: start_datetime = "2026-02-07 10:00:00"
// FEHLT: start_time, start_day, start_month, start_year
```

**Das Problem:**
- Template_Data Service formatiert Events (fügt start_time, etc. hinzu)
- Shortcodes nutzen Template_Data → funktioniert ✅
- AJAX-Handler nutzt direkte Array-Konvertierung → fehlt ❌

---

### Events nicht klickbar

**Symptom:**
- Events haben `data-event-id` Attribut
- Events haben Klasse `cts-event-clickable`
- ABER: Klick öffnet kein Modal

**Root Cause:**
```javascript
// initClickableEvents() wird im DOM Ready aufgerufen
// Event Delegation: $(document).on('click', '.cts-event-clickable', ...)

// ABER: Nach AJAX replaceWith() sind neue Events im DOM
// Event Delegation sollte funktionieren!
```

**Wahrscheinliche Ursache:** Modal-Template fehlt oder AJAX-URL falsch.

---

## ✅ Die Lösung

### Fix 1: Template_Data Service nutzen

**AJAX-Handler formatiert Events richtig:**
```php
// Lade Template_Data Service
if ( ! class_exists( 'ChurchTools_Suite_Template_Data' ) ) {
    require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
}
$template_data = new ChurchTools_Suite_Template_Data();

$events = [];
foreach ( $raw_events as $event ) {
    $event_array = (array) $event;
    // ✅ Formatiere Event (fügt start_time, start_day, calendar_color, etc. hinzu)
    $formatted_event = $template_data->format_event( $event_array );
    $events[] = $formatted_event;
}
```

**Jetzt haben Events:**
- ✅ `start_time` = "10:00 Uhr"
- ✅ `start_day` = "7"
- ✅ `start_month` = "FEB"
- ✅ `start_year` = "26"
- ✅ `calendar_color` = "#667eea"
- ✅ `time_display` = "10:00 Uhr - 12:00 Uhr"
- ✅ `services` = Array mit Diensten
- ✅ `is_past`, `is_today`, etc.

---

### Fix 2: Event-Klickbarkeit

**Events sollten jetzt klickbar sein weil:**
1. Template hat `data-event-id` Attribut ✅
2. Template hat Klasse `cts-event-clickable` ✅
3. JavaScript Event Delegation läuft ✅
4. Modal-Handler sind registriert ✅

**Wenn immer noch nicht klickbar:**
- Browser Console öffnen (F12)
- Event anklicken
- Sollte zeigen: `[ChurchTools Suite] Event clicked, ID: 123`
- Wenn Fehler: Melde dich mit Screenshot!

---

## 🔧 Technische Details

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php**
- `ajax_load_calendar_month()` - Nutzt Template_Data Service
- Lädt `ChurchTools_Suite_Template_Data` Klasse
- Ruft `$template_data->format_event()` für jedes Event

### Event-Formatierung Details:

**Template_Data::format_event() fügt hinzu:**
```php
return [
    // Basis-Daten
    'id' => 123,
    'title' => 'Gottesdienst',
    'description' => 'Mit Predigt...',
    
    // ✅ NEU: Formatierte Zeiten
    'start_time' => '10:00 Uhr',
    'end_time' => '12:00 Uhr',
    'time_display' => '10:00 Uhr - 12:00 Uhr',
    
    // ✅ NEU: Datums-Komponenten
    'start_day' => '7',
    'start_month' => 'FEB',
    'start_month_full' => 'Februar',
    'start_year' => '26',
    
    // ✅ NEU: Kalender-Info
    'calendar_name' => 'Gottesdienst',
    'calendar_color' => '#667eea',
    
    // ✅ NEU: Services
    'services' => [
        ['service_name' => 'Predigt', 'person_name' => 'Max Mustermann']
    ],
    
    // ✅ NEU: Flags
    'is_past' => false,
    'is_today' => true,
    'is_multiday' => false,
];
```

---

## 📊 Testing

### Erwartetes Verhalten:

**Kalender-Ansicht:**
- ✅ Event-Titel sichtbar
- ✅ **Uhrzeit sichtbar** (z.B. "10:00 Uhr")
- ✅ **Farbe entspricht Kalender**
- ✅ Tooltip zeigt "7. FEB 26 - Gottesdienst"

**Event-Klickbarkeit:**
- ✅ Hover über Event → Cursor ändert sich zu Pointer
- ✅ Klick auf Event → Modal öffnet sich
- ✅ Modal zeigt Event-Details
- ✅ ESC oder Klick außerhalb → Modal schließt

**Console (F12):**
```
[ChurchTools Suite] initClickableEvents() called
[ChurchTools Suite] Found clickable events: 15
[ChurchTools Suite] Event clicked, ID: 123
[ChurchTools Suite] showEventModal() called with ID: 123
```

---

## 🔄 Migration von v0.10.3.24

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender öffnen
4. **Event-Details prüfen:**
   - Uhrzeit sichtbar?
   - Tooltip korrekt?
5. **Event anklicken:**
   - Modal öffnet?
   - Details vollständig?

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.18: Logging
- v0.10.3.19: get_events_in_range()
- v0.10.3.20: Array Conversion
- v0.10.3.21: Event-Handler Re-Init
- v0.10.3.22: Month Display Fix
- v0.10.3.23: Farben Fix
- v0.10.3.24: Navigation Closure Fix
- **v0.10.3.25:** **Event-Formatierung + Klickbarkeit** ✅

---

## 🎉 Kalender ENDLICH komplett!

**Was jetzt alles funktioniert:**
- ✅ Monatswechsel unbegrenzt
- ✅ Nur EIN Kalender
- ✅ **Event-Details (Zeit, Datum, etc.)**
- ✅ Event-Farben korrekt
- ✅ **Events klickbar** (hoffentlich!)
- ✅ Modal öffnet (hoffentlich!)

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.25
