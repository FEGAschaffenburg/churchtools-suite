# ChurchTools Suite - Shortcode Guide

Vollständige Anleitung zur Verwendung aller ChurchTools Suite Shortcodes.

---

## 📅 Calendar Views

### Monthly Calendar
```
[cts_calendar view="monthly-modern"]
[cts_calendar view="monthly-clean" calendar="2,3"]
[cts_calendar view="monthly-classic" limit="100"]
```

**Varianten:**
- `monthly-modern` - Moderner Monatskalender mit Gradient-Header
- `monthly-clean` - Minimalistischer Monatskalender
- `monthly-classic` - Klassischer Kalender
- `monthly-simple` - Vereinfachter Kalender
- `monthly-novel` - Neuartiges Design

### Weekly Calendar
```
[cts_calendar view="weekly-fluent" from="2025-12-01"]
[cts_calendar view="weekly-liquid" calendar="2"]
```

### Yearly Calendar
```
[cts_calendar view="yearly" calendar="2,3"]
```

### Daily Calendar
```
[cts_calendar view="daily" from="2025-12-15"]
[cts_calendar view="daily-liquid"]
```

**Parameter:**
- `view` - View-Variante (siehe oben)
- `calendar` - Kalender-IDs kommasepariert (z.B. "2,3")
- `limit` - Maximale Anzahl Events (Standard: 100)
- `from` - Start-Datum (Y-m-d)
- `to` - End-Datum (Y-m-d)
- `class` - Zusätzliche CSS-Klasse

---

## 📋 List Views

### Classic List
```
[cts_list view="classic"]
[cts_list view="classic" calendar="2" limit="20"]
[cts_list view="classic" show_services="true"]
```

**Varianten:**
- `classic` - Klassische Liste mit Date-Badge und Services
- `standard` - Standard-Liste
- `modern` - Moderne Card-Liste
- `minimal` - Minimalistische Liste
- `toggle` - Liste mit Toggle-Details
- `with-map` - Liste mit Kartenintegration
- `fluent` - Fluent Design Style
- `large-liquid` - Große Fluid-Liste
- `medium-liquid` - Mittlere Fluid-Liste
- `small-liquid` - Kleine Fluid-Liste

**Parameter:**
- `view` - View-Variante
- `calendar` - Kalender-IDs
- `limit` - Maximale Anzahl (Standard: 20)
- `from` / `to` - Zeitraum
- `show_services` - Services anzeigen (true/false, Standard: true)
- `class` - CSS-Klasse

---

## 🎯 Grid Views

### Simple Grid
```
[cts_grid view="simple" columns="3"]
[cts_grid view="modern" columns="4" calendar="2,3"]
[cts_grid view="colorful" limit="12"]
```

**Varianten:**
- `simple` - Einfaches Card-Grid
- `modern` - Modernes Grid
- `minimal` - Minimalistisches Grid
- `ocean` - Ocean-Theme Grid
- `classic` - Klassisches Grid
- `colorful` - Farbiges Grid mit Calendar-Colors
- `novel` - Neuartiges Design
- `with-map` - Grid mit Karten-Pins
- `large-liquid` - Großes Fluid-Grid
- `medium-liquid` - Mittleres Fluid-Grid
- `small-liquid` - Kleines Fluid-Grid
- `tile` - Kachel-Ansicht

**Parameter:**
- `view` - View-Variante
- `columns` - Spaltenanzahl (2-4, Standard: 3)
- `calendar` - Kalender-IDs
- `limit` - Maximale Anzahl (Standard: 20)
- `class` - CSS-Klasse

---

## 🎪 Modal Views

### Single Event Modal
```
[cts_modal id="2026"]
[cts_modal event_id="2026" view="single-event"]
[cts_modal id="2026" view="full-calendar"]
```

**Varianten:**
- `single-event` - Event-Detail-Modal
- `full-calendar` - Vollbild-Kalender-Modal
- `yearly` - Jahresansicht Modal
- `monthly` - Monatsansicht Modal
- `list` - Listen-Modal
- `grid` - Grid-Modal
- `daily` - Tagesansicht Modal
- `weekly` - Wochenansicht Modal

**Parameter:**
- `id` - Event-ID (local)
- `event_id` - ChurchTools Event-ID
- `view` - View-Variante
- `class` - CSS-Klasse

---

## 🎬 Slider Views

### Image Slider
```
[cts_slider view="type-1" limit="5"]
[cts_slider view="type-3" autoplay="true" interval="5000"]
```

**Varianten:**
- `type-1` - Standard-Slider
- `type-2` - Slider mit großen Bildern
- `type-3` - Slider mit Thumbnails
- `type-4` - 3D-Slider-Effekt
- `type-5` - Vertical Slider

**Parameter:**
- `view` - View-Variante
- `limit` - Anzahl Slides (Standard: 5)
- `autoplay` - Auto-Play aktivieren (true/false)
- `interval` - Intervall in ms (Standard: 5000)
- `calendar` - Kalender-IDs
- `class` - CSS-Klasse

---

## ⏱️ Countdown Views

### Event Countdown
```
[cts_countdown view="type-1"]
[cts_countdown view="type-2" event_id="2026"]
[cts_countdown view="type-3" calendar="2"]
```

**Varianten:**
- `type-1` - Flip-Clock Style
- `type-2` - Circular Progress
- `type-3` - Minimal Counter

**Parameter:**
- `view` - View-Variante
- `event_id` - Spezifisches Event (optional)
- `calendar` - Kalender-IDs (falls kein event_id)
- `class` - CSS-Klasse

**Hinweis:** Ohne `event_id` wird automatisch das nächste anstehende Event verwendet.

---

## 🖼️ Cover Views

### Hero Section
```
[cts_cover view="classic"]
[cts_cover view="modern" calendar="2"]
[cts_cover view="clean" event_id="2026"]
```

**Varianten:**
- `classic` - Hero mit Hintergrundbild
- `modern` - Split-Screen Design
- `clean` - Minimal Hero
- `fluent` - Fluent Design Hero
- `liquid` - Fluid Layout Hero

**Parameter:**
- `view` - View-Variante
- `event_id` - Spezifisches Event (optional)
- `calendar` - Kalender-IDs
- `class` - CSS-Klasse

---

## 📊 Timetable Views

### Weekly Schedule
```
[cts_timetable view="modern"]
[cts_timetable view="timeline" from="2025-12-01" to="2025-12-31"]
```

**Varianten:**
- `modern` - Moderner Wochenplan
- `clean` - Minimaler Stundenplan
- `timeline` - Timeline-Darstellung

**Parameter:**
- `view` - View-Variante
- `from` / `to` - Zeitraum
- `calendar` - Kalender-IDs
- `class` - CSS-Klasse

---

## 🎠 Carousel Views

### Touch Carousel
```
[cts_carousel view="type-1" limit="10"]
[cts_carousel view="type-3" autoplay="true"]
```

**Varianten:**
- `type-1` - Standard Carousel
- `type-2` - 3D Carousel
- `type-3` - Infinite Loop Carousel
- `type-4` - Netflix-Style Carousel

**Parameter:**
- `view` - View-Variante
- `limit` - Anzahl Items (Standard: 10)
- `autoplay` - Auto-Play aktivieren
- `interval` - Intervall in ms
- `calendar` - Kalender-IDs
- `class` - CSS-Klasse

---

## 📱 Single Event Views

### Event Detail
```
[cts_single id="2026"]
[cts_single event_id="2026" view="fluent"]
[cts_single id="2026" view="liquid"]
```

**Varianten:**
- `default` - Standard Event-Detail
- `fluent` - Fluent Design Detail
- `liquid` - Fluid Layout Detail

**Parameter:**
- `id` - Event-ID (local)
- `event_id` - ChurchTools Event-ID
- `view` - View-Variante
- `class` - CSS-Klasse

---

## 🗺️ Map Views

### Google Maps Integration
```
[cts_map view="standard"]
[cts_map view="advanced" calendar="2,3" zoom="12"]
[cts_map view="liquid" center="49.8728,9.1342"]
```

**Varianten:**
- `standard` - Standard Google Maps
- `advanced` - Erweiterte Karte mit Clustern
- `liquid` - Responsive Fluid Map

**Parameter:**
- `view` - View-Variante
- `calendar` - Kalender-IDs
- `zoom` - Zoom-Level (1-20, Standard: 12)
- `center` - Karten-Zentrum (Lat,Lng)
- `class` - CSS-Klasse

---

## 🔍 Search Views

### Event Search
```
[cts_search view="bar"]
[cts_search view="advanced" calendar="2"]
[cts_search view="bar" placeholder="Termine suchen..."]
```

**Varianten:**
- `bar` - Suchleiste mit Autocomplete
- `advanced` - Erweiterte Suche mit Filtern

**Parameter:**
- `view` - View-Variante
- `calendar` - Kalender-IDs
- `placeholder` - Placeholder-Text
- `class` - CSS-Klasse

---

## 🧩 Widget Views

### Sidebar Widgets
```
[cts_widget view="upcoming-events" limit="5"]
[cts_widget view="calendar-widget"]
[cts_widget view="countdown-widget" calendar="2"]
```

**Varianten:**
- `upcoming-events` - Nächste Termine
- `calendar-widget` - Mini-Kalender
- `countdown-widget` - Countdown-Widget

**Parameter:**
- `view` - View-Variante
- `limit` - Anzahl Events (Standard: 5)
- `calendar` - Kalender-IDs
- `class` - CSS-Klasse

---

## 🔄 Legacy Compatibility

### Altes Plugin-Format
```
[cts_events]
[cts_events calendar="2" limit="20"]
```

**Hinweis:** Der `[cts_events]` Shortcode wird automatisch auf `[cts_list view="classic"]` gemappt für Rückwärtskompatibilität.

---

## 🎨 Theme Override

Alle Templates können im Theme überschrieben werden:

1. Erstelle Ordner: `wp-content/themes/your-theme/churchtools-suite/`
2. Kopiere gewünschtes Template aus Plugin
3. Passe Template an

**Beispiel:**
```
/themes/your-theme/
└── churchtools-suite/
    ├── calendar/
    │   └── monthly-modern.php
    ├── list/
    │   └── classic.php
    └── grid/
        └── simple.php
```

Templates im Theme haben **immer Vorrang** vor Plugin-Templates.

---

## 📖 Weitere Dokumentation

- **Template Development:** Siehe `templates/README.md`
- **Filter & Hooks:** Siehe Plugin-Dokumentation
- **Beispiele:** Siehe `examples/` Ordner

**Support:** https://github.com/FEGAschaffenburg/churchtools-suite/issues
