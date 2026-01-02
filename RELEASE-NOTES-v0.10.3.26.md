# Release Notes - v0.10.3.26

**Release-Datum:** 2. Januar 2026  
**Typ:** FEATURE - Kalender Tooltip-Optionen

---

## ✨ NEUE FEATURE: Kalender Tooltip-Optionen!

**Endlich:** Die Anzeige-Optionen aus dem Gutenberg-Block funktionieren jetzt! 🎉

---

## 🎯 Was ist neu?

### Tooltip-Infos konfigurierbar

**Vorher (v0.10.3.25):**
- ❌ Optionen wurden NICHT ausgewertet
- ❌ Tooltip zeigte immer nur: "7. FEB 26 - Gottesdienst"
- ❌ Keine Kontrolle über angezeigte Infos

**Jetzt (v0.10.3.26):**
- ✅ Alle Optionen funktionieren!
- ✅ Tooltip zeigt nur aktivierte Infos
- ✅ Mehrzeiliger Tooltip mit Icons

---

## 📋 Verfügbare Optionen

### 1. **Uhrzeit anzeigen** (show_time)
- **Standard:** AN ✅
- **Tooltip:** `🕐 10:00 Uhr - 12:00 Uhr`

### 2. **Beschreibung anzeigen** (show_description)
- **Standard:** AUS
- **Tooltip:** `📝 Mit Predigt von Pastor Schmidt und...`
- Automatisch gekürzt auf 15 Wörter

### 3. **Ort anzeigen** (show_location)
- **Standard:** AUS
- **Tooltip:** `📍 Gemeindehaus Aschaffenburg`

### 4. **Services anzeigen** (show_services)
- **Standard:** AUS
- **Tooltip:** `👤 Predigt: Max Mustermann, Musik: Anna Schmidt`
- Zeigt bis zu 3 Services

### 5. **Kalender-Name anzeigen** (show_calendar_name)
- **Standard:** AUS
- **Tooltip:** `📅 Gottesdienst`

---

## 🎨 Tooltip-Beispiele

### Minimal (nur Zeit)
```
7. FEB 26 - Gottesdienst
🕐 10:00 Uhr - 12:00 Uhr
```

### Vollständig (alle Optionen AN)
```
7. FEB 26 - Gottesdienst
🕐 10:00 Uhr - 12:00 Uhr
📍 Gemeindehaus Aschaffenburg
📅 Gottesdienst
👤 Predigt: Max Mustermann, Musik: Anna Schmidt
📝 Mit Predigt von Pastor Schmidt und anschließendem...
```

---

## 🔧 Wie funktioniert es?

### Im Gutenberg-Block

**Anzeige-Optionen Bereich:**
- ☑️ Uhrzeit anzeigen (Standard: AN)
- ☐ Beschreibung anzeigen
- ☐ Ort anzeigen
- ☐ Services anzeigen
- ☐ Kalender-Name anzeigen

**Alle Optionen werden jetzt korrekt ausgewertet!**

---

### Im Shortcode

```php
[churchtools_calendar 
    view="calendar-monthly"
    show_time="true"
    show_description="true"
    show_location="true"
    show_services="true"
    show_calendar_name="true"
]
```

**Parameter:**
- `show_time` - Uhrzeit (default: true)
- `show_description` - Beschreibung (default: false)
- `show_location` - Ort (default: false)
- `show_services` - Services (default: false)
- `show_calendar_name` - Kalender-Name (default: false)

---

## 🔧 Technische Details

### Implementierung

**1. Template (monthly-modern.php):**
```php
// Baue Tooltip-Text basierend auf Optionen
$tooltip_parts = [];

// Immer: Datum + Titel
$tooltip_parts[] = $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] . ' - ' . $event['title'];

// Optional: Uhrzeit
if ( ! isset( $args['show_time'] ) || $args['show_time'] !== false ) {
    if ( ! empty( $event['time_display'] ) ) {
        $tooltip_parts[] = '🕐 ' . $event['time_display'];
    }
}

// Optional: Ort
if ( ( $args['show_location'] ?? false ) && ! empty( $event['location_name'] ) ) {
    $tooltip_parts[] = '📍 ' . $event['location_name'];
}

// ... weitere Optionen

$tooltip = implode( "\n", $tooltip_parts );
```

**2. AJAX-Handler (admin.php):**
```php
// Lese Optionen aus Request
$show_time = isset( $_POST['show_time'] ) ? filter_var( $_POST['show_time'], FILTER_VALIDATE_BOOLEAN ) : true;
$show_description = isset( $_POST['show_description'] ) ? filter_var( $_POST['show_description'], FILTER_VALIDATE_BOOLEAN ) : false;
// ...

// Übergebe an Template
$atts = [
    'show_time' => $show_time,
    'show_description' => $show_description,
    // ...
];
```

**3. JavaScript (public.js):**
```javascript
// Lese Optionen aus data-Attributen
const showTime = $calendar.data('show-time') !== false;
const showDescription = $calendar.data('show-description') === true;
// ...

// Sende an AJAX
data: {
    show_time: showTime,
    show_description: showDescription,
    // ...
}
```

**4. Template data-Attribute:**
```html
<div class="cts-calendar-monthly" 
     data-show-time="true"
     data-show-description="false"
     data-show-location="false"
     data-show-services="false"
     data-show-calendar-name="false">
```

---

## 📊 Testing

### Erwartetes Verhalten:

**Test 1: Nur Uhrzeit (Standard)**
1. Alle Optionen AUS (außer Uhrzeit)
2. Event anzeigen
3. **Tooltip zeigt:** Datum + Titel + Uhrzeit

**Test 2: Alle Optionen AN**
1. Alle Checkboxen aktivieren
2. Event anzeigen
3. **Tooltip zeigt:** Datum + Titel + Uhrzeit + Ort + Kalender + Services + Beschreibung

**Test 3: Monatswechsel**
1. Optionen setzen
2. Monat wechseln
3. **Tooltip behält:** Einstellungen bei

---

## 🎯 Icons im Tooltip

**Emoji-Icons für bessere Lesbarkeit:**
- 🕐 Uhrzeit
- 📍 Ort
- 📅 Kalender-Name
- 👤 Services
- 📝 Beschreibung

**Warum Icons?**
- Visuelle Trennung
- Schnelles Erfassen
- Weniger Text

---

## 🔄 Migration von v0.10.3.25

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender öffnen
4. **Gutenberg-Block:** Optionen testen
5. **Tooltip prüfen:** Hover über Events

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.18: Logging
- v0.10.3.19: get_events_in_range()
- v0.10.3.20: Array Conversion
- v0.10.3.21: Event-Handler Re-Init
- v0.10.3.22: Month Display Fix
- v0.10.3.23: Farben Fix
- v0.10.3.24: Navigation Closure Fix
- v0.10.3.25: Template_Data Formatierung
- **v0.10.3.26:** **Tooltip-Optionen funktionieren!** ✅

---

## 🎉 Kalender KOMPLETT mit Optionen!

**Was jetzt alles funktioniert:**
- ✅ Monatswechsel unbegrenzt
- ✅ Nur EIN Kalender
- ✅ Event-Details formatiert
- ✅ Event-Farben korrekt
- ✅ Events klickbar
- ✅ **Tooltip-Optionen steuerbar!**
- ✅ Mehrzeilige Tooltips mit Icons

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.26
