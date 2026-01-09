# View & Template Creation Guide

> **Für AI-Agenten und Entwickler:** Checklisten für neue Views und Listenformate

**Version:** 0.9.6.40  
**Datum:** 7. Januar 2026

---

## 📋 Pre-Implementation Checklist: Was muss gefragt werden?

> **Für AI-Agenten:** Diese Fragen MÜSSEN vor dem Start beantwortet werden!

### 🎯 Grundlegende Entscheidungen

#### 1. View-Typ festlegen
**Frage:** Welcher View-Typ soll erstellt werden?

- [ ] **Listenformat** (z.B. timeline, compact, modern)
  - Variante des `template="list"` Shortcodes
  - Gleiche Datenstruktur, anderes Layout
  - Schneller zu implementieren
  - Beispiele: classic, minimal, timeline, agenda

- [ ] **Neue View** (z.B. calendar, grid, slider, search)
  - Komplett neuer Template-Typ
  - Eigene Datenverarbeitung und Logik
  - Mehr Aufwand
  - Beispiele: calendar (monthly/weekly), grid (cards), slider (carousel)

**User-Antwort erforderlich:** _________________________

---

#### 2. View-Name & Identifikation
**Fragen:**
- [ ] Wie soll die View heißen? (z.B. "timeline", "compact", "calendar-monthly")
- [ ] Ist der Name eindeutig und nicht bereits vergeben?
- [ ] Ist der Name kurz, sprechend und lowercase? (keine Leerzeichen, keine Umlaute)

**User-Antworten:**
- Name: _________________________
- Shortcode: `[churchtools_calendar template="list" view="________"]`
- CSS-Klasse: `.cts-list-________`

---

#### 3. Visuelle Anforderungen
**Fragen:**
- [ ] Welches Layout-Konzept? (vertikal, horizontal, grid, cards, etc.)
- [ ] Welche Design-Inspiration? (Timeline, Kalender, Karten, etc.)
- [ ] Gibt es ein Referenz-Design oder Screenshot?
- [ ] Welche visuellen Elemente sind wichtig?
  - Datum-Anzeige (Box, Text, Timeline-Marker)
  - Titel (groß, klein, Farbe)
  - Beschreibung (vollständig, gekürzt, ausgeblendet)
  - Location (mit Icon, nur Text)
  - Services (Liste, Grid, Inline)
  - Tags (Pills, Badges, Farben)

**User-Antworten:**
- Layout: _________________________
- Referenz: _________________________
- Key-Elemente: _________________________

---

### 🎨 Style & Design

#### 4. Farbschema
**Fragen:**
- [ ] Welche Primärfarbe soll im Plugin-Mode verwendet werden? (Standard: #2563eb)
- [ ] Sollen Theme-Farben unterstützt werden? (Standard: Ja)
- [ ] Welche Farben für welche Elemente?
  - Hintergrund (Card/Container)
  - Text (Titel, Beschreibung, Meta)
  - Akzente (Marker, Badges, Borders)
  - Hover-States

**User-Antworten:**
- Plugin-Primärfarbe: _________________________
- Theme-Support: Ja/Nein
- Besondere Farbanforderungen: _________________________

---

#### 5. Abstände & Größen
**Fragen:**
- [ ] Wie groß sollen Abstände zwischen Events sein? (Standard: 16-24px)
- [ ] Wie groß soll die Schrift sein? (Standard: 14px Text, 18px Titel)
- [ ] Wie viel Padding sollen Cards/Container haben? (Standard: 12-16px)
- [ ] Border-Radius für abgerundete Ecken? (Standard: 6-8px)

**User-Antworten:**
- Event-Abstand: ________px
- Font-Größe: ________px (Text), ________px (Titel)
- Padding: ________px
- Border-Radius: ________px

---

### 📊 Funktionale Anforderungen

#### 6. Display-Optionen
**Fragen:** Welche Elemente sollen ein-/ausblendbar sein?

Standard-Toggles (MÜSSEN immer unterstützt werden):
- [ ] `show_event_description` (Event-Beschreibung)
- [ ] `show_appointment_description` (Termin-Beschreibung)
- [ ] `show_location` (Ort/Adresse)
- [ ] `show_services` (Dienste/Services)
- [ ] `show_time` (Uhrzeit)
- [ ] `show_tags` (Tags/Labels)
- [ ] `show_month_separator` (Monats-Trennlinien)

Zusätzliche View-spezifische Toggles:
- [ ] _________________________
- [ ] _________________________

---

#### 7. Interaktivität
**Fragen:**
- [ ] Soll die View interaktiv sein? (z.B. Navigation, Filtering)
- [ ] Benötigt die View JavaScript?
  - Month-Navigation (← →)
  - Filtering (Kalender, Services, Tags)
  - Expand/Collapse (Beschreibungen)
  - Modal-Integration
- [ ] AJAX-Loading für dynamische Inhalte?

**User-Antworten:**
- JavaScript benötigt: Ja/Nein
- Funktionen: _________________________

---

#### 8. Responsive Design
**Fragen:**
- [ ] Welches Mobile-Verhalten?
  - Stacking (vertikal untereinander)
  - Horizontal Scrolling
  - Column-Reduktion (Grid → List)
  - Element-Hiding (z.B. Beschreibungen verstecken)
- [ ] Breakpoint für Mobile? (Standard: 768px)
- [ ] Welche Elemente auf Mobile anders darstellen?

**User-Antworten:**
- Mobile-Verhalten: _________________________
- Breakpoint: ________px
- Anpassungen: _________________________

---

### 🔧 Technische Details

#### 9. Datenstruktur
**Fragen:**
- [ ] Welche Event-Felder werden benötigt?
  - Standard: title, start_datetime, end_datetime
  - Optional: event_description, appointment_description
  - Location: location_name, address_name, address_city
  - Meta: calendar_name, tags, services
- [ ] Werden Event-Services angezeigt? (Ja/Nein)
- [ ] Werden Tags/Labels angezeigt? (Ja/Nein)
- [ ] Spezielle Felder? (z.B. event_id, appointment_id)

**User-Antworten:**
- Pflicht-Felder: _________________________
- Optionale Felder: _________________________

---

#### 10. Sortierung & Filtering
**Fragen:**
- [ ] Wie sollen Events sortiert werden?
  - Chronologisch (Standard: start_datetime ASC)
  - Kalender-gruppiert
  - Service-gruppiert
- [ ] Gruppierung aktivieren?
  - Nach Monat (Standard bei Listen)
  - Nach Kalendar
  - Nach Tag/Woche
- [ ] Filterung vorsehen?
  - Kalender-Filter
  - Service-Filter
  - Datum-Range-Filter

**User-Antworten:**
- Sortierung: _________________________
- Gruppierung: _________________________
- Filter: _________________________

---

### 📦 Deployment & Testing

#### 11. Testing-Umfang
**Fragen:**
- [ ] Welche Browser müssen getestet werden?
  - Chrome (Pflicht)
  - Firefox (Pflicht)
  - Safari (optional)
  - Edge (optional)
- [ ] Welche Geräte?
  - Desktop (Pflicht)
  - Tablet (optional)
  - Mobile (Pflicht)
- [ ] Edge Cases testen?
  - Keine Events (Empty State)
  - Sehr lange Titel/Beschreibungen
  - Viele Tags (Overflow)
  - Fehlende Felder (location_name = null)

**User-Antworten:**
- Browser: _________________________
- Geräte: _________________________
- Edge Cases: _________________________

---

#### 12. Dokumentation
**Fragen:**
- [ ] Welche Beispiele sollen in `SHORTCODE-GUIDE.md`?
- [ ] Welche Parameter in `SHORTCODE-REFERENCE.md` dokumentieren?
- [ ] Screenshots für Dokumentation benötigt? (Ja/Nein)
- [ ] Release Notes erstellen? (Ja für neue Features)

**User-Antworten:**
- Beispiele: _________________________
- Screenshots: Ja/Nein
- Release Notes: Ja/Nein

---

### 🎯 Zusammenfassung: Minimum Required Answers

**Bevor du startest, müssen mindestens diese Fragen beantwortet sein:**

1. ✅ **View-Typ:** Listenformat oder Neue View?
2. ✅ **Name:** Wie heißt die View? (lowercase, keine Leerzeichen)
3. ✅ **Layout-Konzept:** Vertikale Liste? Grid? Timeline? Kalender?
4. ✅ **Visuelle Referenz:** Screenshot/Beispiel vorhanden?
5. ✅ **Style-Mode:** Plugin-Farben, Theme-Farben oder Custom?
6. ✅ **Display-Optionen:** Alle Standard-Toggles + welche zusätzlichen?
7. ✅ **Responsive:** Mobile-Verhalten klar definiert?
8. ✅ **JavaScript:** Benötigt? Für welche Funktionen?

**Wenn alle Antworten vorliegen → START!**

---

## 🎨 Neues Listenformat erstellen (z.B. list-timeline, list-compact)

Ein Listenformat ist eine Variante des `template="list"` Shortcodes mit unterschiedlichem Layout.

### ✅ Pflicht-Schritte

#### 1. Template-Datei erstellen
**Datei:** `templates/list/{name}.php` (z.B. `templates/list/timeline.php`)

```php
<?php
/**
 * List View - Timeline
 *
 * Vertikale Timeline mit Zeitstrahl
 *
 * @package ChurchTools_Suite
 * @since   0.x.x
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Parse boolean display parameters
$show_event_description = isset( $args['show_event_description'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
$show_time = isset( $args['show_time'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// 2. Style mode support (v0.9.6.0)
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'custom' ) {
    $primary = $args['custom_primary_color'] ?? '#2563eb';
    $text = $args['custom_text_color'] ?? '#1e293b';
    $bg = $args['custom_background_color'] ?? '#ffffff';
    $border_radius = $args['custom_border_radius'] ?? 6;
    $font_size = $args['custom_font_size'] ?? 14;
    $padding = $args['custom_padding'] ?? 12;
    $spacing = $args['custom_spacing'] ?? 8;
    
    $custom_styles = sprintf(
        '--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
        esc_attr( $primary ),
        esc_attr( $text ),
        esc_attr( $bg ),
        absint( $border_radius ),
        absint( $font_size ),
        absint( $padding ),
        absint( $spacing )
    );
}
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
    <div class="cts-list cts-list-timeline" 
        data-view="list-timeline"
        data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
        data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
        data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
        data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
        data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
        data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
        data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>">
    
        <?php if ( empty( $events ) ) : ?>
            <!-- Empty state -->
        <?php else : ?>
            <!-- Event loop -->
            <?php foreach ( $events as $event ) : ?>
                <!-- Event HTML -->
            <?php endforeach; ?>
        <?php endif; ?>
    
    </div>
</div>
```

**Pflicht-Elemente:**
- ✅ Wrapper mit `data-style-mode` Attribut
- ✅ Inline-Styles für Custom-Mode
- ✅ Container mit `data-view` und allen `data-show-*` Attributen
- ✅ Empty State für leere Event-Liste
- ✅ Alle Display-Parameter parsen
- ✅ Style-Mode-Support (plugin/theme/custom)

#### 2. CSS-Datei erstellen
**Datei:** `assets/css/list-timeline.css` (Optional, wenn nicht in public.css)

```css
/* === List Timeline View === */

/* Plugin mode: Fixed colors */
.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-list-timeline {
    /* Plugin-spezifische Farben */
}

/* Theme mode: WordPress theme colors */
.churchtools-suite-wrapper[data-style-mode="theme"] .cts-list-timeline {
    background: var(--wp--preset--color--base, #fff);
    color: var(--wp--preset--color--contrast, #000);
}

/* Custom mode: User-defined CSS variables */
.churchtools-suite-wrapper[data-style-mode="custom"] .cts-list-timeline {
    background: var(--cts-bg-color, #fff);
    color: var(--cts-text-color, #1e293b);
    font-size: var(--cts-font-size, 14px);
    border-radius: var(--cts-border-radius, 8px);
}

.churchtools-suite-wrapper[data-style-mode="custom"] .cts-timeline-marker {
    background: var(--cts-primary-color, #2563eb);
    border-radius: var(--cts-border-radius, 50%);
}

.churchtools-suite-wrapper[data-style-mode="custom"] .cts-timeline-item {
    gap: var(--cts-spacing, 16px);
    padding: var(--cts-padding, 12px);
}

/* Display toggles */
[data-show-description="0"] .cts-description {
    display: none !important;
}
```

**Pflicht-Elemente:**
- ✅ Style-Mode-Support für alle 3 Modi (plugin/theme/custom)
- ✅ CSS-Variablen-Unterstützung im Custom-Mode
- ✅ Display-Toggle-Regeln für data-show-* Attribute

#### 3. CSS in Plugin registrieren
**Datei:** `public/class-churchtools-suite-public.php`

```php
// In enqueue_styles() Methode:
if ( $view === 'timeline' ) {
    wp_enqueue_style(
        $this->plugin_name . '-list-timeline',
        plugin_dir_url( __FILE__ ) . '../assets/css/list-timeline.css',
        array( $this->plugin_name ),
        $this->version,
        'all'
    );
}
```

#### 4. Template im Shortcode registrieren
**Datei:** `includes/class-churchtools-suite-shortcodes.php`

```php
// In list_calendar() Methode - View-Validierung erweitern:
$view = $atts['view'] ?? 'classic';
$valid_views = ['classic', 'compact', 'timeline']; // NEU hinzufügen
if ( ! in_array( $view, $valid_views, true ) ) {
    $view = 'classic';
}
```

#### 5. Gutenberg Block erweitern
**Datei:** `assets/js/churchtools-suite-blocks.js`

```javascript
// In ListCalendar block - view SelectControl erweitern:
el(SelectControl, {
    label: 'View-Variante',
    value: attributes.view,
    options: [
        { label: 'Classic', value: 'classic' },
        { label: 'Compact', value: 'compact' },
        { label: 'Timeline', value: 'timeline' } // NEU
    ],
    onChange: function(newView) {
        setAttributes({ view: newView });
    }
})
```

#### 6. Testing-Checkliste
- [ ] Shortcode funktioniert: `[churchtools_calendar template="list" view="timeline"]`
- [ ] Gutenberg Block zeigt neue View-Option
- [ ] Empty State wird angezeigt bei leeren Events
- [ ] Alle Display-Toggles funktionieren (show_description, show_location, etc.)
- [ ] Style Mode "plugin" verwendet feste Farben
- [ ] Style Mode "theme" erbt WordPress-Theme-Farben
- [ ] Style Mode "custom" wendet alle 7 CSS-Variablen an
- [ ] Responsive Design auf Mobile getestet
- [ ] Browser-Kompatibilität geprüft

#### 7. Dokumentation aktualisieren
- [ ] `docs/SHORTCODE-REFERENCE.md` - View hinzufügen
- [ ] `docs/SHORTCODE-GUIDE.md` - Beispiele ergänzen
- [ ] Release Notes erstellen

---

## 🗓️ Neue View erstellen (z.B. calendar, grid, slider)

Eine View ist ein komplett anderes Template-System (nicht `template="list"`).

### ✅ Pflicht-Schritte

#### 1. Template-Ordner erstellen
**Struktur:** `templates/{view}/` (z.B. `templates/calendar/`)

Beispiel für Calendar View:
```
templates/calendar/
├── monthly.php      (Standard-Variante)
├── weekly.php       (Alternative Variante)
└── daily.php        (Alternative Variante)
```

#### 2. Haupt-Template erstellen
**Datei:** `templates/calendar/monthly.php`

```php
<?php
/**
 * Calendar View - Monthly
 *
 * Monatskalender mit Grid-Layout
 *
 * @package ChurchTools_Suite
 * @since   0.x.x
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Style mode support
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'custom' ) {
    // ... CSS-Variablen-Generierung (siehe Listenformat Punkt 1)
}

// View-spezifische Parameter
$start_of_week = $args['start_of_week'] ?? 1; // 0=Sonntag, 1=Montag
$show_week_numbers = isset( $args['show_week_numbers'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_week_numbers'] ) : false;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
    <div class="cts-calendar cts-calendar-monthly" 
        data-view="calendar-monthly"
        data-start-of-week="<?php echo esc_attr( $start_of_week ); ?>"
        data-show-week-numbers="<?php echo esc_attr( $show_week_numbers ? '1' : '0' ); ?>">
    
        <!-- Calendar Header (Month/Year + Navigation) -->
        <div class="cts-calendar-header">
            <!-- Prev/Next Buttons -->
        </div>
        
        <!-- Calendar Grid -->
        <div class="cts-calendar-grid">
            <!-- Days of Week -->
            <!-- Date Cells -->
        </div>
    
    </div>
</div>
```

#### 3. CSS-Datei erstellen
**Datei:** `assets/css/calendar.css`

```css
/* === Calendar View === */

/* Plugin mode */
.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-calendar {
    background: #fff;
    border: 1px solid #e5e7eb;
}

/* Theme mode */
.churchtools-suite-wrapper[data-style-mode="theme"] .cts-calendar {
    background: var(--wp--preset--color--base, #fff);
    color: var(--wp--preset--color--contrast, #000);
}

/* Custom mode */
.churchtools-suite-wrapper[data-style-mode="custom"] .cts-calendar {
    background: var(--cts-bg-color, #fff);
    color: var(--cts-text-color, #1e293b);
    font-size: var(--cts-font-size, 14px);
    border-radius: var(--cts-border-radius, 8px);
    padding: var(--cts-padding, 12px);
}

/* Calendar Grid Layout */
.cts-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: var(--cts-spacing, 8px);
}

/* Responsive */
@media (max-width: 768px) {
    .cts-calendar-grid {
        grid-template-columns: repeat(1, 1fr);
    }
}
```

#### 4. CSS in Plugin registrieren
**Datei:** `public/class-churchtools-suite-public.php`

```php
// In enqueue_styles() Methode:
wp_enqueue_style(
    $this->plugin_name . '-calendar',
    plugin_dir_url( __FILE__ ) . '../assets/css/calendar.css',
    array( $this->plugin_name ),
    $this->version,
    'all'
);
```

#### 5. JavaScript erstellen (optional, für Interaktivität)
**Datei:** `assets/js/calendar.js`

```javascript
(function($) {
    'use strict';

    /**
     * Calendar View - Month Navigation
     */
    $(document).ready(function() {
        $('.cts-calendar-nav-prev').on('click', function() {
            // Previous month
        });
        
        $('.cts-calendar-nav-next').on('click', function() {
            // Next month
        });
    });

})(jQuery);
```

**Registrieren in** `public/class-churchtools-suite-public.php`:

```php
// In enqueue_scripts() Methode:
wp_enqueue_script(
    $this->plugin_name . '-calendar',
    plugin_dir_url( __FILE__ ) . '../assets/js/calendar.js',
    array( 'jquery' ),
    $this->version,
    false
);
```

#### 6. Shortcode erstellen/erweitern
**Datei:** `includes/class-churchtools-suite-shortcodes.php`

```php
/**
 * Calendar Shortcode
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
public static function calendar_calendar( $atts ) {
    $atts = shortcode_atts( array(
        'view' => 'monthly',
        'start_of_week' => 1,
        'show_week_numbers' => false,
        // Style mode attributes
        'style_mode' => 'theme',
        'custom_primary_color' => '#2563eb',
        'custom_text_color' => '#1e293b',
        'custom_background_color' => '#ffffff',
        'custom_border_radius' => 6,
        'custom_font_size' => 14,
        'custom_padding' => 12,
        'custom_spacing' => 8,
    ), $atts, 'churchtools_calendar' );
    
    // Fetch events
    $events = self::fetch_events( $atts );
    
    // Load template
    $template_file = CHURCHTOOLS_SUITE_PATH . "templates/calendar/{$atts['view']}.php";
    if ( ! file_exists( $template_file ) ) {
        $template_file = CHURCHTOOLS_SUITE_PATH . 'templates/calendar/monthly.php';
    }
    
    ob_start();
    include $template_file;
    return ob_get_clean();
}
```

**Registrieren:**

```php
// In register_shortcodes() Methode:
add_shortcode( 'churchtools_calendar_view', array( $this, 'calendar_calendar' ) );
```

#### 7. Gutenberg Block erstellen
**Datei:** `assets/js/churchtools-suite-blocks.js`

```javascript
// Register new block
registerBlockType('churchtools-suite/calendar', {
    title: 'ChurchTools Calendar',
    icon: 'calendar-alt',
    category: 'widgets',
    
    attributes: {
        view: { type: 'string', default: 'monthly' },
        startOfWeek: { type: 'number', default: 1 },
        showWeekNumbers: { type: 'boolean', default: false },
        // Style attributes
        style_mode: { type: 'string', default: 'theme' },
        custom_primary_color: { type: 'string', default: '#2563eb' },
        // ... weitere style attributes
    },
    
    edit: function(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        
        return el('div', { className: props.className },
            el(InspectorControls, {},
                // View Selection
                el(PanelBody, { title: 'View-Einstellungen', initialOpen: true },
                    el(SelectControl, {
                        label: 'View-Variante',
                        value: attributes.view,
                        options: [
                            { label: 'Monthly', value: 'monthly' },
                            { label: 'Weekly', value: 'weekly' },
                            { label: 'Daily', value: 'daily' }
                        ],
                        onChange: function(newView) {
                            setAttributes({ view: newView });
                        }
                    })
                ),
                // Style Mode Panel (wie bei list-classic)
                el(PanelBody, { title: 'Farbschema', initialOpen: false },
                    // ... Style controls
                )
            ),
            el(ServerSideRender, {
                block: 'churchtools-suite/calendar',
                attributes: attributes
            })
        );
    },
    
    save: function() {
        return null; // Server-side rendering
    }
});
```

**Registrieren in** `includes/class-churchtools-suite-blocks.php`:

```php
// In register() Methode:
register_block_type( 'churchtools-suite/calendar', array(
    'editor_script' => 'churchtools-suite-blocks',
    'render_callback' => array( 'ChurchTools_Suite_Shortcodes', 'calendar_calendar' ),
    'attributes' => array(
        'view' => array( 'type' => 'string', 'default' => 'monthly' ),
        'startOfWeek' => array( 'type' => 'number', 'default' => 1 ),
        'showWeekNumbers' => array( 'type' => 'boolean', 'default' => false ),
        // Style attributes
        'style_mode' => array( 'type' => 'string', 'default' => 'theme' ),
        // ... weitere attributes
    ),
) );
```

#### 8. Testing-Checkliste
- [ ] Shortcode funktioniert: `[churchtools_calendar_view view="monthly"]`
- [ ] Gutenberg Block zeigt View-Optionen
- [ ] Alle View-Varianten funktionieren (monthly, weekly, daily)
- [ ] Style Mode "plugin" verwendet feste Farben
- [ ] Style Mode "theme" erbt WordPress-Theme-Farben
- [ ] Style Mode "custom" wendet alle 7 CSS-Variablen an
- [ ] Navigation funktioniert (Prev/Next Month)
- [ ] Events werden korrekt angezeigt
- [ ] Responsive Design auf Mobile getestet
- [ ] Browser-Kompatibilität geprüft
- [ ] Leere Zustände getestet (keine Events)

#### 9. Dokumentation aktualisieren
- [ ] `docs/SHORTCODE-REFERENCE.md` - Neue View dokumentieren
- [ ] `docs/SHORTCODE-GUIDE.md` - Beispiele hinzufügen
- [ ] `ROADMAP.md` - Feature als "Completed" markieren
- [ ] Release Notes erstellen

---

## 📋 Gemeinsame Best Practices

### Display-Toggle-Parameter (Standard bei allen Views)
Immer diese Parameter unterstützen:
- `show_event_description` (boolean, default: true)
- `show_appointment_description` (boolean, default: true)
- `show_services` (boolean, default: true)
- `show_location` (boolean, default: true)
- `show_calendar_name` (boolean, default: false)
- `show_time` (boolean, default: true)
- `show_tags` (boolean, default: false)
- `show_month_separator` (boolean, default: true)

### Style-Mode-Parameter (Standard bei allen Views)
Immer diese 3 Modi unterstützen:
- **plugin**: Feste Plugin-Farben (Standard: #2563eb)
- **theme**: WordPress-Theme-Farben (--wp--preset--color-*)
- **custom**: Benutzerdefinierte Farben + Größen (7 Parameter)

**Custom-Mode-Parameter:**
- `custom_primary_color` (string, default: '#2563eb')
- `custom_text_color` (string, default: '#1e293b')
- `custom_background_color` (string, default: '#ffffff')
- `custom_border_radius` (number, default: 6)
- `custom_font_size` (number, default: 14)
- `custom_padding` (number, default: 12)
- `custom_spacing` (number, default: 8)

### CSS-Variablen (Custom Mode)
Immer diese CSS-Variablen verwenden:
```css
--cts-primary-color: <color>;
--cts-text-color: <color>;
--cts-bg-color: <color>;
--cts-border-radius: <px>;
--cts-font-size: <px>;
--cts-padding: <px>;
--cts-spacing: <px>;
```

### Template-Struktur
```html
<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr($style_mode); ?>" style="...CSS vars...">
    <div class="cts-{template} cts-{template}-{view}" 
         data-view="{template}-{view}"
         data-show-description="1"
         data-show-location="1"
         ...>
        <!-- Content -->
    </div>
</div>
```

### CSS-Selektoren
```css
/* IMMER mit Style-Mode-Präfix! */
.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-element { }
.churchtools-suite-wrapper[data-style-mode="theme"] .cts-element { }
.churchtools-suite-wrapper[data-style-mode="custom"] .cts-element { }

/* IMMER Display-Toggles unterstützen! */
[data-show-description="0"] .cts-description { display: none !important; }
```

### Gutenberg Block
- Immer `ServerSideRender` verwenden (keine Client-Side-Rendering)
- Attribute in PHP + JS synchron halten
- InspectorControls für alle Optionen
- PanelBody für logische Gruppierung
- Help-Text bei komplexen Optionen

### Dateinamen-Konventionen
- Templates: `templates/{view}/{variant}.php` (lowercase, hyphen-separated)
- CSS: `assets/css/{view}.css` oder `assets/css/{view}-{variant}.css`
- JS: `assets/js/{view}.js`
- Klassenname: `.cts-{view}` und `.cts-{view}-{variant}`

### Performance
- CSS nur bei Bedarf laden (View-spezifisch)
- JavaScript nur bei Bedarf laden (Interaktivität)
- Template-Caching nutzen (WordPress Transients)
- Minimale DOM-Manipulationen

### Accessibility
- Semantic HTML (nav, article, time, etc.)
- ARIA-Labels bei interaktiven Elementen
- Keyboard-Navigation unterstützen
- Screen-Reader-freundlich

---

## 🔧 Troubleshooting

### Problem: Farben werden nicht angewendet
- ✅ Prüfen: CSS-Variablen im Template generiert?
- ✅ Prüfen: CSS nutzt `var(--cts-primary-color)`?
- ✅ Prüfen: `data-style-mode` Attribut gesetzt?
- ✅ Prüfen: CSS-Selektoren mit `[data-style-mode="custom"]` Präfix?

### Problem: Display-Toggles funktionieren nicht
- ✅ Prüfen: `data-show-*` Attribute im Container gesetzt?
- ✅ Prüfen: CSS-Regeln `[data-show-description="0"] { display: none; }`?
- ✅ Prüfen: Boolean-Parameter mit `parse_boolean()` geparst?

### Problem: Gutenberg Block zeigt nichts an
- ✅ Prüfen: `ServerSideRender` verwendet?
- ✅ Prüfen: Block in `class-churchtools-suite-blocks.php` registriert?
- ✅ Prüfen: Shortcode-Funktion existiert und gibt HTML zurück?

### Problem: Template nicht gefunden
- ✅ Prüfen: Dateiname korrekt (lowercase, hyphen-separated)?
- ✅ Prüfen: Pfad in `CHURCHTOOLS_SUITE_PATH . 'templates/...'`?
- ✅ Prüfen: Fallback-Template definiert?

---

## 📚 Ressourcen

**Code-Referenzen:**
- [templates/list/classic.php](../templates/list/classic.php) - Vollständiges Beispiel
- [assets/css/churchtools-suite-public.css](../assets/css/churchtools-suite-public.css) - CSS-Struktur
- [assets/js/churchtools-suite-blocks.js](../assets/js/churchtools-suite-blocks.js) - Gutenberg-Block
- [includes/class-churchtools-suite-shortcodes.php](../includes/class-churchtools-suite-shortcodes.php) - Shortcode-Logic

**Dokumentation:**
- [SHORTCODE-REFERENCE.md](SHORTCODE-REFERENCE.md) - Alle Parameter
- [SHORTCODE-GUIDE.md](SHORTCODE-GUIDE.md) - Verwendungsbeispiele
- [ROADMAP.md](../ROADMAP.md) - Geplante Views

---

## 🚀 Schritt-für-Schritt: Neue View erstellen (Minimal-Example)

> **Praxis-Beispiel:** So erstellen Sie eine neue List-View von Grund auf

### Ziel
Eine neue **Timeline-View** für `template="list"` mit vertikalem Zeitstrahl.

### Schritt 1: Template-Datei erstellen

**Datei:** `templates/list/timeline.php`

```php
<?php
/**
 * List View - Timeline
 * 
 * Vertikale Timeline mit Zeitstrahl-Marker
 * 
 * @package ChurchTools_Suite
 * @since   0.9.7.0
 * @version 0.9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Display-Parameter parsen
$show_event_description = isset( $args['show_event_description'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_time = isset( $args['show_time'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? 
    ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// 2. Style-Mode unterstützen
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'custom' ) {
    $primary = $args['custom_primary_color'] ?? '#2563eb';
    $text = $args['custom_text_color'] ?? '#1e293b';
    $bg = $args['custom_background_color'] ?? '#ffffff';
    $border_radius = $args['custom_border_radius'] ?? 6;
    $font_size = $args['custom_font_size'] ?? 14;
    $padding = $args['custom_padding'] ?? 12;
    $spacing = $args['custom_spacing'] ?? 8;
    
    $custom_styles = sprintf(
        '--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
        esc_attr( $primary ),
        esc_attr( $text ),
        esc_attr( $bg ),
        absint( $border_radius ),
        absint( $font_size ),
        absint( $padding ),
        absint( $spacing )
    );
}

// 3. Datum-Formatter (WordPress-Format verwenden)
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
    <div class="cts-list cts-list-timeline" 
         data-view="list-timeline"
         data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
         data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
         data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
         data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
         data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
         data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>">

        <?php if ( empty( $events ) ) : ?>
            <div class="cts-empty-state">
                <p><?php esc_html_e( 'Keine Termine gefunden.', 'churchtools-suite' ); ?></p>
            </div>
        <?php else : ?>
            <?php 
            $current_month = '';
            foreach ( $events as $event ) : 
                $event_month = wp_date( 'F Y', strtotime( $event->start_datetime ) );
                $show_separator = $show_month_separator && $event_month !== $current_month;
                $current_month = $event_month;
            ?>
                
                <?php if ( $show_separator ) : ?>
                    <div class="cts-month-separator">
                        <span><?php echo esc_html( $event_month ); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="cts-timeline-item">
                    <!-- Zeitstrahl-Marker -->
                    <div class="cts-timeline-marker"></div>
                    
                    <!-- Event-Content -->
                    <div class="cts-timeline-content">
                        <div class="cts-timeline-header">
                            <?php if ( $show_time ) : ?>
                                <time class="cts-time" datetime="<?php echo esc_attr( $event->start_datetime ); ?>">
                                    <?php echo esc_html( wp_date( $time_format, strtotime( $event->start_datetime ) ) ); ?>
                                </time>
                            <?php endif; ?>
                            
                            <h3 class="cts-title"><?php echo esc_html( $event->title ); ?></h3>
                        </div>
                        
                        <?php if ( $show_event_description && ! empty( $event->event_description ) ) : ?>
                            <div class="cts-description cts-event-description">
                                <?php echo wp_kses_post( wpautop( $event->event_description ) ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $show_appointment_description && ! empty( $event->appointment_description ) ) : ?>
                            <div class="cts-description cts-appointment-description">
                                <?php echo wp_kses_post( wpautop( $event->appointment_description ) ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $show_location && ! empty( $event->location_name ) ) : ?>
                            <div class="cts-location">
                                📍 <?php echo esc_html( $event->location_name ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $show_tags && ! empty( $event->tags ) ) : ?>
                            <div class="cts-tags">
                                <?php 
                                $tags = json_decode( $event->tags, true );
                                if ( is_array( $tags ) ) {
                                    foreach ( $tags as $tag ) {
                                        $tag_name = $tag['name'] ?? '';
                                        $tag_color = $tag['color'] ?? '#6b7280';
                                        echo '<span class="cts-tag" style="background-color: ' . esc_attr( $tag_color ) . ';">' . esc_html( $tag_name ) . '</span>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $show_services && ! empty( $event->services ) ) : ?>
                            <div class="cts-services">
                                <?php foreach ( $event->services as $service ) : ?>
                                    <div class="cts-service">
                                        <strong><?php echo esc_html( $service->service_name ); ?>:</strong>
                                        <?php echo esc_html( $service->person_name ); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
```

**Was wurde gemacht:**
- ✅ Alle Display-Parameter mit `parse_boolean()` geparst
- ✅ Style-Mode-Support (plugin/theme/custom) mit CSS-Variablen
- ✅ WordPress Date/Time Formats verwendet
- ✅ Empty State für leere Events
- ✅ Monats-Separator mit Logik
- ✅ Alle Conditional-Displays (description, location, services, tags)
- ✅ Semantic HTML (time, h3, etc.)

---

### Schritt 2: CSS erstellen

**Datei:** `assets/css/churchtools-suite-public.css` (am Ende hinzufügen)

```css
/* ========================================
   List View - Timeline (v0.9.7.0)
   ======================================== */

/* Container */
.cts-list-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-left: 40px;
}

/* Vertikale Linie (Zeitstrahl) */
.cts-list-timeline::before {
    content: '';
    position: absolute;
    left: 16px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

/* Monats-Separator */
.cts-list-timeline .cts-month-separator {
    margin: 24px 0 16px -40px;
    padding-left: 0;
    font-weight: 600;
    font-size: 18px;
    color: #1e293b;
}

/* Timeline Item */
.cts-timeline-item {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    position: relative;
}

/* Timeline Marker (Kreis auf der Linie) */
.cts-timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #2563eb;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
    flex-shrink: 0;
    position: absolute;
    left: -29px;
    top: 8px;
    z-index: 2;
}

/* Timeline Content */
.cts-timeline-content {
    flex: 1;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
}

/* Header (Time + Title) */
.cts-timeline-header {
    margin-bottom: 12px;
}

.cts-timeline-header .cts-time {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.cts-timeline-header .cts-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #1e293b;
}

/* Descriptions */
.cts-timeline-content .cts-description {
    margin-top: 8px;
    font-size: 14px;
    line-height: 1.6;
    color: #475569;
}

/* Location */
.cts-timeline-content .cts-location {
    margin-top: 8px;
    font-size: 14px;
    color: #64748b;
}

/* Tags */
.cts-timeline-content .cts-tags {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.cts-timeline-content .cts-tag {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    color: #fff;
    font-weight: 500;
}

/* Services */
.cts-timeline-content .cts-services {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cts-timeline-content .cts-service {
    font-size: 13px;
    color: #475569;
}

/* Empty State */
.cts-list-timeline .cts-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}

/* =====================================
   Style Mode Support
   ===================================== */

/* Plugin Mode: Feste Farben */
.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-list-timeline::before {
    background: #e5e7eb;
}

.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-timeline-marker {
    background: #2563eb;
    border-color: #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-timeline-content {
    background: #fff;
    border-color: #e5e7eb;
}

/* Theme Mode: WordPress-Theme-Farben */
.churchtools-suite-wrapper[data-style-mode="theme"] .cts-list-timeline::before {
    background: var(--wp--preset--color--tertiary, #e5e7eb);
}

.churchtools-suite-wrapper[data-style-mode="theme"] .cts-timeline-marker {
    background: var(--wp--preset--color--primary, #2563eb);
    border-color: var(--wp--preset--color--base, #fff);
}

.churchtools-suite-wrapper[data-style-mode="theme"] .cts-timeline-content {
    background: var(--wp--preset--color--base, #fff);
    border-color: var(--wp--preset--color--tertiary, #e5e7eb);
    color: var(--wp--preset--color--contrast, #1e293b);
}

/* Custom Mode: User-defined CSS-Variablen */
.churchtools-suite-wrapper[data-style-mode="custom"] .cts-list-timeline::before {
    background: color-mix(in srgb, var(--cts-primary-color, #2563eb) 20%, transparent);
}

.churchtools-suite-wrapper[data-style-mode="custom"] .cts-timeline-marker {
    background: var(--cts-primary-color, #2563eb);
    border-color: var(--cts-bg-color, #fff);
}

.churchtools-suite-wrapper[data-style-mode="custom"] .cts-timeline-content {
    background: var(--cts-bg-color, #fff);
    border-color: color-mix(in srgb, var(--cts-text-color, #1e293b) 15%, transparent);
    color: var(--cts-text-color, #1e293b);
    border-radius: var(--cts-border-radius, 8px);
    padding: var(--cts-padding, 16px);
    font-size: var(--cts-font-size, 14px);
}

.churchtools-suite-wrapper[data-style-mode="custom"] .cts-timeline-content .cts-title {
    color: var(--cts-text-color, #1e293b);
}

/* Display Toggles */
[data-show-description="0"] .cts-description {
    display: none !important;
}

[data-show-appointment-description="0"] .cts-appointment-description {
    display: none !important;
}

[data-show-location="0"] .cts-location {
    display: none !important;
}

[data-show-services="0"] .cts-services {
    display: none !important;
}

[data-show-time="0"] .cts-time {
    display: none !important;
}

[data-show-tags="0"] .cts-tags {
    display: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .cts-list-timeline {
        padding-left: 30px;
    }
    
    .cts-timeline-marker {
        left: -24px;
    }
    
    .cts-timeline-content {
        padding: 12px;
    }
    
    .cts-timeline-header .cts-title {
        font-size: 16px;
    }
}
```

**Was wurde gemacht:**
- ✅ Vertikaler Zeitstrahl mit `::before` Pseudo-Element
- ✅ Timeline-Marker als Kreis auf der Linie
- ✅ Card-basiertes Content-Design
- ✅ Style-Mode-Support (plugin/theme/custom)
- ✅ CSS-Variablen für Custom-Mode
- ✅ Display-Toggle-Regeln für alle `data-show-*` Attribute
- ✅ Responsive Design für Mobile

---

### Schritt 3: Template registrieren

**Datei:** `includes/class-churchtools-suite-shortcodes.php`

In der Methode `list_calendar()` nach der View-Validierung:

```php
// Validate view (Zeile ~320)
$view = $atts['view'] ?? 'classic';
$valid_views = ['classic', 'minimal', 'timeline']; // NEU: timeline hinzugefügt
if ( ! in_array( $view, $valid_views, true ) ) {
    $view = 'classic';
}
```

**Keine weiteren Änderungen nötig!** Die Shortcode-Methode lädt Templates automatisch über:
```php
$template_path = 'list/' . $atts['view'];
return self::render_template( $template_path, $events, $atts );
```

---

### Schritt 4: Gutenberg Block erweitern

**Datei:** `assets/js/churchtools-suite-blocks.js`

In der Block-Registrierung `churchtools-suite/events` die View-Optionen erweitern:

```javascript
// Available Views per Type (Zeile ~40)
const views = {
    list: [
        { label: __('Classic', 'churchtools-suite'), value: 'classic' },
        { label: __('Minimal', 'churchtools-suite'), value: 'minimal' },
        { label: __('Timeline', 'churchtools-suite'), value: 'timeline' } // NEU
    ]
};
```

**Fertig!** Der Block zeigt automatisch die neue Option im Dropdown.

---

### Schritt 5: Version erhöhen & Testen

**Dateien:** `churchtools-suite.php`

```php
// Version erhöhen (z.B. 0.9.6.40 → 0.9.7.0)
/**
 * Version: 0.9.7.0
 */
define( 'CHURCHTOOLS_SUITE_VERSION', '0.9.7.0' );
```

**Testing-Checkliste:**
- [ ] Shortcode: `[churchtools_calendar template="list" view="timeline"]` funktioniert
- [ ] Gutenberg Block zeigt "Timeline" Option
- [ ] Alle Display-Toggles funktionieren (Beschreibung, Ort, Services, Tags)
- [ ] Style Mode "plugin" zeigt feste Farben (blauer Marker)
- [ ] Style Mode "theme" erbt WordPress-Theme-Farben
- [ ] Style Mode "custom" wendet alle CSS-Variablen an
- [ ] Monats-Separator funktioniert
- [ ] Empty State wird angezeigt (keine Events)
- [ ] Responsive Design auf Mobile (< 768px)
- [ ] Browser-Test (Chrome, Firefox, Safari)

---

### Schritt 6: Deployment

**ZIP erstellen:**
```powershell
cd C:\privat\churchtools-suite\scripts
.\create-wp-zip.ps1 -Version "0.9.7.0"
```

**Upload zu WordPress:**
1. Admin → Plugins → Hochladen
2. ZIP-Datei auswählen
3. Plugin aktivieren
4. Cache leeren (Strg + Shift + R)

**Testen auf Live-Server:**
1. Seite mit Block öffnen
2. View auf "Timeline" umstellen
3. Display-Optionen testen
4. Farbschema-Modi testen (plugin/theme/custom)

---

### Schritt 7: Dokumentation aktualisieren

**Dateien zu aktualisieren:**

1. **`docs/SHORTCODE-REFERENCE.md`**
   ```markdown
   ### `view` (optional)
   - `classic` - Klassische Liste (Standard)
   - `minimal` - Minimalistische Liste
   - `timeline` - Vertikale Timeline **NEU in v0.9.7.0**
   ```

2. **`docs/SHORTCODE-GUIDE.md`**
   ```markdown
   ### Timeline-Ansicht
   ```php
   [churchtools_calendar template="list" view="timeline" show_services="true"]
   ```
   ```

3. **`ROADMAP.md`**
   ```markdown
   ### v0.9.7.0: Timeline View
   **Ziel:** Vertikale Timeline mit Zeitstrahl
   
   **Features:**
   - ✅ Timeline-Marker auf vertikalem Strahl
   - ✅ Card-basierte Event-Darstellung
   - ✅ Monats-Separator
   - ✅ Style-Mode-Support (plugin/theme/custom)
   - ✅ Alle Display-Toggles
   ```

4. **Release Notes erstellen:** `release-notes-v0.9.7.0.md`
   ```markdown
   # ChurchTools Suite v0.9.7.0 - Timeline View
   
   ## 🚀 Neue Features
   
   ### Timeline-Ansicht für Listen
   Neue vertikale Timeline-Darstellung mit Zeitstrahl-Marker.
   
   **Verwendung:**
   ```php
   [churchtools_calendar template="list" view="timeline"]
   ```
   
   **Features:**
   - Vertikaler Zeitstrahl mit kreisförmigen Markern
   - Card-basierte Event-Darstellung
   - Monats-Separator
   - Vollständige Style-Mode-Unterstützung
   - Responsive Design
   
   ## 📝 Änderungen
   - Neue Datei: `templates/list/timeline.php`
   - CSS-Erweiterung in `assets/css/churchtools-suite-public.css`
   - Gutenberg Block: Timeline-Option hinzugefügt
   - Shortcode: `view="timeline"` unterstützt
   
   ## 🐛 Bugfixes
   - Keine
   
   ## ⚠️ Breaking Changes
   - Keine
   ```

---

### ✅ Fertig!

Sie haben erfolgreich eine neue View erstellt. Die Timeline-View ist jetzt:
- ✅ Per Shortcode verwendbar
- ✅ Im Gutenberg Block auswählbar
- ✅ Style-Mode-kompatibel (plugin/theme/custom)
- ✅ Display-Toggle-fähig (alle show_* Parameter)
- ✅ Responsive (Mobile-optimiert)
- ✅ Dokumentiert

**Nächste Schritte:**
- User-Feedback sammeln
- Weitere View-Varianten planen (weekly, daily, etc.)
- Performance-Optimierung (CSS-Caching, Lazy-Loading)

---

**Version:** 0.9.6.40  
**Letzte Aktualisierung:** 7. Januar 2026
