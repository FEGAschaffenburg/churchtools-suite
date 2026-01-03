# Release Notes - v0.10.3.29

**Release-Datum:** 3. Januar 2026  
**Typ:** BUGFIX - Calendar Modal Toggle Respect

---

## 🐛 BUGFIX: Kalender-Modal respektiert jetzt Toggles!

**Problem:** Im Kalender-View wurden die optionalen Felder (Beschreibung, Ort, Services, Kalender-Name) im Popup **IMMER** angezeigt, auch wenn die Toggles ausgeschaltet waren!

**Root Cause:**
Die `displayEventData()` Funktion zeigte alle verfügbaren Daten an, ohne die Toggle-Einstellungen (`show_description`, `show_location`, `show_services`, `show_calendar_name`) vom Kalender-Container zu prüfen.

---

## ✅ Fix (v0.10.3.29)

### JavaScript Änderungen

**1. Container-Parameter durchgereicht:**
```javascript
// VORHER: Keine Container-Info
function showEventModal(eventId) { ... }

// NACHHER: Container mit Settings wird durchgereicht
function showEventModal(eventId, $container) {
  loadEventData(eventId, $overlay, $container);
}
```

**2. Display-Logik respektiert jetzt Toggles:**
```javascript
function displayEventData(event, $container) {
  // Extract display options from container
  const showDescription = $container ? 
    ($container.data('show-description') === true) : true;
  const showLocation = $container ? 
    ($container.data('show-location') === true) : true;
  const showServices = $container ? 
    ($container.data('show-services') === true) : true;
  const showCalendarName = $container ? 
    ($container.data('show-calendar-name') === true) : true;
  
  // Nur anzeigen wenn Toggle AN und Daten vorhanden
  if (showDescription && event.description) {
    $('#cts-modal-description-wrapper').show();
  } else {
    $('#cts-modal-description-wrapper').hide();
  }
  
  // ... gleiches Prinzip für Location, Services, Calendar Name
}
```

**3. Alle Modal-Aufrufe aktualisiert:**
- ✅ Kalender-Navigation (Month Prev/Next)
- ✅ Kalender-Day Click
- ✅ Grid Detail Buttons
- ✅ List Clickable Events
- ✅ Keyboard Navigation
- ✅ Generic Event-ID Clicks

### Verhalten

**Toggle AUS:**
- ❌ Beschreibung wird NICHT angezeigt (auch wenn vorhanden)
- ❌ Ort wird NICHT angezeigt (auch wenn vorhanden)
- ❌ Services werden NICHT angezeigt (auch wenn vorhanden)
- ❌ Kalender-Name wird NICHT angezeigt (auch wenn vorhanden)

**Toggle AN:**
- ✅ Beschreibung wird angezeigt (wenn vorhanden)
- ✅ Ort wird angezeigt (wenn vorhanden)
- ✅ Services werden angezeigt (wenn vorhanden)
- ✅ Kalender-Name wird angezeigt (wenn vorhanden)

**Default-Werte (wenn kein Container gefunden):**
- ✅ Alle Toggles werden als `true` behandelt (Fallback für Legacy-Aufrufe)

### CSS Ergänzung: Info-Icon Styling

**Problem:** Das Info-Icon (ⓘ) bei Adressen hatte kein CSS-Styling!

**Fix:** CSS für `.cts-info-popup` hinzugefügt:
```css
.cts-info-popup {
  display: inline-block;
  width: 18px;
  height: 18px;
  background: #667eea;
  color: #fff;
  border-radius: 50%;
  cursor: help;
  /* Zeigt Tooltip mit vollständiger Adresse */
}
```

**Verhalten:**
- Info-Icon erscheint bei Ort-Anzeige, wenn Zusatzinformationen vorhanden
- Tooltip zeigt: Straße, PLZ, Stadt
- Nur sichtbar wenn `show_location` Toggle AN ist

---

## 🎯 Betroffene Komponenten

- ✅ **Calendar Monthly Modern** (`templates/calendar/monthly-modern.php`)
- ✅ **Grid Views** (`templates/grid/*.php`)
- ✅ **List Views** (`templates/list/*.php`)
- ✅ **Alle anderen Views mit Modal**

---

## 🧪 Test-Szenarien

### Szenario 1: Toggles ausgeschaltet
1. Kalender öffnen
2. Alle Toggles **ausschalten** (Beschreibung, Ort, Services)
3. Event anklicken
4. ✅ **Ergebnis:** Nur Titel, Datum/Zeit, KEIN Ort, KEINE Beschreibung, KEINE Services

### Szenario 2: Nur Ort aktiviert
1. Toggle "Ort anzeigen" **AN**
2. Alle anderen Toggles **AUS**
3. Event anklicken
4. ✅ **Ergebnis:** Titel, Datum/Zeit, Ort - KEINE Beschreibung, KEINE Services

### Szenario 3: Alle Toggles aktiviert
1. Alle Toggles **AN**
2. Event anklicken
3. ✅ **Ergebnis:** Alle verfügbaren Informationen werden angezeigt

---

## 📝 Technische Details

### Container-Erkennung
```javascript
// Suche nächsten Parent-Container mit Settings
const $container = $(this).closest('[data-show-description]');

// Calendar hat: data-show-description, data-show-location, etc.
// Grid/List haben auch diese Attributes
// Falls nicht gefunden: $container ist undefined → Fallback zu true
```

### Backward Compatibility
- ✅ Alte Modal-Aufrufe ohne Container funktionieren weiter (zeigen alle Daten)
- ✅ Kein Breaking Change für externe Integrationen

---

## 🔧 Geänderte Dateien

- `assets/js/churchtools-suite-public.js` (8 Funktionen aktualisiert)
  - `showEventModal()` - Parameter `$container` hinzugefügt
  - `loadEventData()` - Parameter `$container` hinzugefügt
  - `displayEventData()` - Toggle-Logik implementiert
  - `initGridButtons()` - Container übergeben
  - `initClickableEvents()` - Container übergeben (2x)
  - Calendar Navigation Click Handler - Container übergeben
  - Day Click Handler - Container übergeben
  - Generic Event-ID Click Handler - Container übergeben

- `assets/css/churchtools-suite-public.css`
  - CSS für `.cts-info-popup` hinzugefügt (Info-Icon bei Adressen)

---

## 🎉 Zusammenfassung

**v0.10.3.29 behebt das letzte Modal-Problem:**
- Das Popup respektiert jetzt die Toggle-Einstellungen vom Kalender/Grid/List
- Konsistentes Verhalten über alle Views hinweg
- Bessere User Experience - User kontrolliert, was im Modal angezeigt wird

---

**Nächste Version:** v1.0.0 - Production Ready Testing Phase
