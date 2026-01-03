# Release Notes - Version 0.10.3.30

**Release Date:** 23. Dezember 2024  
**Status:** Critical Bug Fix

---

## 🐛 Critical Bug Fix

### Calendar Month Navigation Repair
**Problem:** Nach v0.10.3.29 war die Kalender-Monatsnavigation (Prev/Next Buttons) defekt.

**Ursache:** 
- AJAX-Handler `ajax_load_calendar_month()` gab nur Grid-Inhalt zurück
- JavaScript versuchte kompletten Kalender-Container zu ersetzen → Navigation-Buttons gingen verloren
- Re-Initialisierung der Event-Handler scheiterte am fehlenden Container

**Lösung:**
1. JavaScript `loadCalendarMonth()` ersetzt jetzt nur das Grid (`.cts-calendar-grid`), nicht den ganzen Container
2. AJAX-Handler gibt zusätzlich `month_name` zurück für Titel-Update
3. Navigation-Buttons bleiben erhalten, nur Grid-Inhalt wird ausgetauscht
4. Event-Handler werden direkt auf dem Grid re-initialisiert

**Dateien geändert:**
- `assets/js/churchtools-suite-public.js` - Grid-only replacement statt Kalender-Ersetzung
- `includes/class-churchtools-suite-shortcodes.php` - AJAX gibt `month_name` zurück

---

## 📋 Technical Details

### JavaScript Changes
**Vorher:**
```javascript
$newCalendar = $(response.data.html);
$calendar.replaceWith($newCalendar); // ❌ Kompletter Container weg!
setupCalendarNavigation($newCalendar); // ❌ Fehlschlag
```

**Nachher:**
```javascript
const monthName = response.data.month_name;
$calendar.find('.cts-calendar-title').text(monthName); // ✅ Titel-Update
const $grid = $calendar.find('.cts-calendar-grid');
$grid.html(response.data.html); // ✅ Nur Grid-Inhalt
$grid.find('[data-event-id]').on('click', ...); // ✅ Event-Handler auf Grid
```

### PHP Changes
AJAX-Response erweitert:
```php
wp_send_json_success([
    'html' => $html,              // Grid-Inhalt (Tage + Events)
    'month' => $month,
    'year' => $year,
    'month_name' => $month_name   // ✅ Neu: Formatierter Titel
]);
```

---

## ✅ What's Fixed

- ✅ Kalender-Monatsnavigation funktioniert wieder (Prev/Next Buttons)
- ✅ Navigation-Buttons bleiben beim Monatswechsel erhalten
- ✅ Event-Click-Handler werden korrekt re-initialisiert
- ✅ Kalender-Titel wird dynamisch aktualisiert
- ✅ Container-Parameter für Modal wird korrekt weitergegeben

---

## 🎯 Minimal Calendar Implementation

**Aktuelle Features (wie gefordert):**
1. ✅ **Monatswechsel** - Prev/Next Buttons funktionieren
2. ✅ **Grunddaten-Anzeige** - Datum, Titel, Zeit (default: `show_time=true`, Rest `false`)
3. ✅ **Click → Popup** - Modal mit vollständigen Details

**Block-Optionen:**
- Display-Options-Panel wird bei `viewType='calendar'` ausgeblendet (bereits in v0.10.3.29 implementiert)
- Default-Settings sind minimal (nur Zeit wird angezeigt)

---

## 🔄 Migration Notes

**Keine Änderungen:**
- Keine DB-Migrationen erforderlich
- Keine Settings-Änderungen
- Abwärtskompatibel

**Empfohlene Actions:**
1. Cache leeren (Browser + WordPress)
2. Kalender-Seite testen: Monatswechsel funktioniert?
3. Event-Click testen: Modal öffnet sich?

---

## 📝 Testing Checklist

- [x] Calendar month navigation (Prev/Next)
- [x] Calendar title updates on month change
- [x] Event dots display correctly
- [x] Event click opens modal
- [x] Modal respects toggle settings
- [x] No JavaScript errors in console
- [x] Grid content replaced correctly
- [x] Navigation buttons remain intact

---

## 🔗 Related Issues

**Fixed:**
- Monatswechsel defekt nach v0.10.3.29 (Container-Parameter Breaking Change)

**Previous Release:**
- v0.10.3.29 - Modal Toggle Respect + Info-Icon CSS

---

## 📚 Documentation

**Affected Functions:**
- `loadCalendarMonth($calendar, year, month)` - JavaScript
- `ajax_load_calendar_month()` - PHP

**Key Insight:**
Bei AJAX-Content-Ersetzung immer prüfen:
1. Was muss ersetzt werden? (Grid vs. Container)
2. Welche Event-Handler müssen re-attached werden?
3. Bleiben Navigation-Elemente erhalten?

---

**Changelog:** CRITICAL - Calendar month navigation repair (grid-only replacement)
