# Calendar Monthly Simple - Implementierungsplan

> **View-Typ:** Calendar (Neue View, nicht Listenformat)  
> **Datum:** 7. Januar 2026  
> **Version:** 0.9.8.0

---

## 📋 Pre-Implementation Checklist

### 1. View-Typ & Name
- ✅ **View-Typ:** Neue View (Calendar)
- ✅ **View-Name:** `monthly-simple`
- ✅ **Shortcode:** `[churchtools_calendar view_type="calendar" view="monthly-simple"]`
- ✅ **CSS-Klasse:** `.cts-calendar-monthly-simple`

### 2. Visuelle Anforderungen
- ✅ **Layout:** 7-Spalten Grid (Mo-So)
- ✅ **Design:** Klassischer Monatskalender mit Zellen
- ✅ **Key-Elemente:**
  - Monat/Jahr Header mit Navigation (◀ Prev | Januar 2026 | Next ▶)
  - Wochentage-Header (Mo, Di, Mi, Do, Fr, Sa, So)
  - Datums-Zellen (7x5 oder 7x6 Grid)
  - Event-Marker in Zellen (kleine Punkte/Bars mit Kalenderfarbe)
  - Tooltip beim Hover (zeigt Event-Details)

### 3. Farbschema
- ✅ **Plugin-Mode:** #2563eb (Standard-Blau)
- ✅ **Theme-Support:** Ja (CSS-Variablen)
- ✅ **Kalenderfarben:** Individuell pro Event (aus ChurchTools)
- ✅ **Farben:**
  - Hintergrund: #ffffff (Zellen)
  - Text: #1e293b (Datum)
  - Heute: #fef3c7 (gelber Highlight)
  - Andere Monate: #f1f5f9 (ausgegraut)
  - Event-Marker: Kalenderfarbe
  - Hover: #f8fafc (Light Gray)

### 4. Abstände & Größen
- ✅ **Zellen-Größe:** 50-60px Höhe (Desktop), 40px (Mobile)
- ✅ **Font-Größe:** 14px (Datum), 12px (Event-Marker), 11px (Wochentage)
- ✅ **Padding:** 8px (Zellen), 4px (Event-Marker)
- ✅ **Border:** 1px solid #e5e7eb (Zellen-Trennung)
- ✅ **Gap:** 0px (Grid ohne Lücken)

### 5. Display-Optionen
- ✅ **Keine Display Options Toggles** (User-Anforderung)
- ✅ Event-Marker zeigen immer: Titel, Uhrzeit, Kalenderfarbe
- ✅ Tooltip zeigt: Titel, Uhrzeit, Ort, Beschreibung (gekürzt)

### 6. Interaktivität
- ✅ **Monatswechsel:** Prev/Next Buttons mit AJAX (oder Page-Reload)
- ✅ **Tooltip:** Hover über Event-Marker zeigt Details
- ✅ **Event-Click:** Configurable (modal/page/none) - via `event_action` Attribute
- ✅ **Touch:** Tooltip auch auf Mobile (Touch-Support)

### 7. Responsive Design
- ✅ **Desktop:** 7 Spalten vollständig sichtbar
- ✅ **Tablet:** Zellen kleiner, Font angepasst
- ✅ **Mobile:** Zellen noch kleiner (40px Höhe), nur Punkte als Marker

### 8. Datenstruktur
- ✅ **Events:** Array mit start_datetime, title, location_name, calendar_color, etc.
- ✅ **Datum-Grouping:** Events nach Tag gruppieren (1-31)
- ✅ **Mehrere Events pro Tag:** Max. 3 Marker, dann "+X mehr" Link
- ✅ **Monat/Jahr:** Aus URL-Parameter oder Shortcode-Attribut

### 9. Sortierung & Filtering
- ✅ **Sortierung:** Chronologisch nach start_datetime
- ✅ **Filtering:** Nur Events im angezeigten Monat
- ✅ **Mehrfach-Events:** Alle Events eines Tags im Tooltip

### 10. Testing-Umfang
- ✅ **Browser:** Chrome, Firefox, Safari, Edge
- ✅ **Geräte:** Desktop, Tablet, Mobile
- ✅ **Edge Cases:**
  - Monat mit 28/29/30/31 Tagen
  - Events über Mitternacht (Multi-Day)
  - Viele Events an einem Tag (>10)
  - Leerer Monat (keine Events)
  - Monatswechsel mit AJAX

---

## 🏗️ Implementierungsplan

### Phase 1: Template-Datei erstellen
**Datei:** `templates/calendar/monthly-simple.php`

```php
<?php
/**
 * Calendar View - Monthly Simple
 * 
 * Klassischer Monatskalender mit Event-Markern und Tooltip
 * 
 * @package ChurchTools_Suite
 * @since   0.9.8.0
 */

// Parse Attributes
$current_month = isset($_GET['cts_month']) ? intval($_GET['cts_month']) : date('n');
$current_year = isset($_GET['cts_year']) ? intval($_GET['cts_year']) : date('Y');

// Generate calendar grid (42 cells: 6 weeks × 7 days)
// Group events by day
// Render month header with navigation
// Render weekday header
// Render day cells with event markers
```

### Phase 2: CSS Styles hinzufügen
**Datei:** `assets/css/churchtools-suite-public.css`

```css
/* Calendar Monthly Simple (v0.9.8.0) */
.cts-calendar-monthly-simple {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

.cts-calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px 8px 0 0;
}

.cts-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid #e5e7eb;
}

.cts-calendar-day-cell {
    min-height: 60px;
    border: 1px solid #e5e7eb;
    padding: 8px;
    position: relative;
}

.cts-calendar-day-cell:hover {
    background: #f8fafc;
}

.cts-event-marker {
    width: 100%;
    padding: 2px 4px;
    margin: 2px 0;
    background: var(--calendar-color, #2563eb);
    border-radius: 3px;
    font-size: 11px;
    color: #fff;
    cursor: pointer;
}

/* Tooltip */
.cts-event-tooltip {
    position: absolute;
    z-index: 1000;
    background: #1e293b;
    color: #fff;
    padding: 12px;
    border-radius: 6px;
    min-width: 200px;
    max-width: 300px;
}
```

### Phase 3: Gutenberg Block erweitern
**Datei:** `assets/js/churchtools-suite-blocks.js`

```javascript
// Add calendar viewType
const viewTypes = [
    { label: 'Liste', value: 'list' },
    { label: 'Kalender', value: 'calendar' } // NEU
];

// Add calendar views
const views = {
    list: [
        { label: 'Classic', value: 'classic' },
        { label: 'Minimal', value: 'minimal' },
        { label: 'Modern', value: 'modern' }
    ],
    calendar: [
        { label: 'Monat (Simple)', value: 'monthly-simple' } // NEU
    ]
};
```

### Phase 4: Shortcode Handler erweitern
**Datei:** `includes/class-churchtools-suite-shortcodes.php`

```php
// Validate view_type
$allowed_view_types = ['list', 'calendar'];
if (!in_array($atts['view_type'], $allowed_view_types, true)) {
    return '<p>⚠️ View-Type nicht verfügbar</p>';
}

// Load correct template
if ($atts['view_type'] === 'calendar') {
    $template_path = CHURCHTOOLS_SUITE_PATH . 
        'templates/calendar/' . $atts['view'] . '.php';
}
```

### Phase 5: JavaScript für Tooltip
**Datei:** `assets/js/churchtools-suite-public.js`

```javascript
// Tooltip on event marker hover
document.addEventListener('DOMContentLoaded', function() {
    const markers = document.querySelectorAll('.cts-event-marker');
    
    markers.forEach(marker => {
        marker.addEventListener('mouseenter', function(e) {
            // Show tooltip with event details
        });
        
        marker.addEventListener('mouseleave', function(e) {
            // Hide tooltip
        });
    });
});
```

### Phase 6: AJAX Monatswechsel (Optional)
**Datei:** `admin/class-churchtools-suite-admin.php`

```php
// AJAX Handler für Monatswechsel
add_action('wp_ajax_nopriv_cts_load_month', [$this, 'ajax_load_month']);

public function ajax_load_month() {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    
    // Load events for month
    // Return HTML
}
```

---

## 🚀 Implementierungs-Reihenfolge

1. ✅ **Pre-Implementation Checklist** (Dieses Dokument)
2. ⏳ **Template erstellen** (`templates/calendar/monthly-simple.php`)
3. ⏳ **CSS Styles** (`assets/css/churchtools-suite-public.css`)
4. ⏳ **Gutenberg Block** (viewType erweitern)
5. ⏳ **Shortcode Handler** (view_type validation)
6. ⏳ **JavaScript Tooltip** (`assets/js/churchtools-suite-public.js`)
7. ⏳ **Testing** (alle Browser/Geräte)
8. ⏳ **Dokumentation** (SHORTCODE-REFERENCE.md)

---

## 📝 Notizen

### Technische Entscheidungen
- **Monatswechsel:** Zuerst mit URL-Parametern (`?cts_month=1&cts_year=2026`), später optional AJAX
- **Tooltip:** Pure CSS mit `:hover` + JavaScript für bessere Positionierung
- **Multi-Day Events:** Nur am Start-Tag anzeigen (kein Spanning über mehrere Tage)
- **Max Events pro Tag:** 3 Marker sichtbar, dann "+X mehr" Link zu Modal/Page

### Offene Fragen
- [ ] Sollen Multi-Day Events über mehrere Zellen gespannt werden?
- [ ] AJAX Monatswechsel oder Page-Reload?
- [ ] Tooltip-Content: Welche Felder genau? (Titel, Uhrzeit, Ort, Services?)

