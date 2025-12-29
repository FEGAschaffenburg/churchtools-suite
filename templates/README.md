# ChurchTools Suite - Templates

Dieses Verzeichnis enthält alle Frontend-Templates für die Event-Darstellung.

## Template-Override-System

### Themes können Templates überschreiben:

1. Erstelle einen Ordner `churchtools-suite/` in deinem Theme
2. Kopiere das gewünschte Template hinein
3. Passe es an deine Bedürfnisse an

**Beispiel:**
```
wp-content/
├── plugins/
│   └── churchtools-suite/
│       └── templates/
│           └── list/
│               └── classic.php  ← Original
└── themes/
    └── your-theme/
        └── churchtools-suite/
            └── list/
                └── classic.php  ← Wird bevorzugt!
```

## View-Typen

### 📅 Calendar Views (`calendar/`)
Monats-, Wochen-, Jahres- und Tagesansichten

- `monthly-modern.php` - Moderner Monatskalender
- `monthly-clean.php` - Minimalistischer Monatskalender
- `monthly-classic.php` - Klassischer Monatskalender
- `weekly-liquid.php` - Wochenansicht (fluid layout)
- `daily.php` - Tagesansicht
- `yearly.php` - Jahresübersicht

### 📋 List Views (`list/`)
Listen-Darstellungen

- `classic.php` - Klassische Liste mit Details
- `standard.php` - Standard-Liste
- `modern.php` - Moderne Liste mit Cards
- `minimal.php` - Minimalistische Liste
- `with-map.php` - Liste mit Kartenintegration
- `fluent.php` - Fluent Design Style

### 🎯 Grid Views (`grid/`)
Kachel-Darstellungen

- `simple.php` - Einfaches Grid-Layout
- `modern.php` - Modernes Card-Grid
- `minimal.php` - Minimalistisches Grid
- `colorful.php` - Farbiges Grid mit Calendar-Colors
- `with-map.php` - Grid mit Karten-Pins

### 📄 Single Event Views (`single/`) **NEU v0.7.1.0**
Einzeltermin-Ansichten für Detailseiten

- `classic.php` - Klassische Einzelansicht mit strukturierten Informationen
- `modern.php` - Moderne Ansicht mit Hero-Header und Cards
- `minimal.php` - Minimalistische Ansicht mit Fokus auf Lesbarkeit
- `card.php` - Card-basierte Ansicht mit visuellen Akzenten

**Verwendung:**
```php
// Einzelnen Termin anzeigen
[cts_event id="123" template="modern"]

// Verschiedene Templates
[cts_event id="123" template="classic"]  // Standard
[cts_event id="123" template="modern"]   // Hero + Cards
[cts_event id="123" template="minimal"]  // Text-fokussiert
[cts_event id="123" template="card"]     // Card Design
```

### 🎪 Modal Views (`modal/`)
Popup-Detailansichten

- `event-detail.php` - Event-Detail-Modal
- `full-calendar.php` - Vollbild-Kalender-Modal

### 🎬 Slider Views (`slider/`)
Karussell-Ansichten

- `type-1.php` - Standard-Slider
- `type-2.php` - Slider mit großen Bildern
- `type-3.php` - Slider mit Thumbnails
- `type-4.php` - 3D-Slider-Effekt
- `type-5.php` - Vertical Slider

### ⏱️ Countdown Views (`countdown/`)
Countdown-Timer

- `type-1.php` - Flip-Clock Style
- `type-2.php` - Circular Progress
- `type-3.php` - Minimal Counter

### 🖼️ Cover Views (`cover/`)
Hero-Section mit nächstem Event

- `classic.php` - Hero mit Hintergrundbild
- `modern.php` - Split-Screen Design
- `clean.php` - Minimal Hero
- `fluent.php` - Fluent Design Hero

### 📊 Timetable Views (`timetable/`)
Zeitplan-Ansichten

- `modern.php` - Moderner Wochenplan
- `clean.php` - Minimaler Stundenplan
- `timeline.php` - Timeline-Darstellung

### 🎠 Carousel Views (`carousel/`)
Touch-fähige Karussells

- `type-1.php` - Standard Carousel
- `type-2.php` - 3D Carousel
- `type-3.php` - Infinite Loop Carousel
- `type-4.php` - Netflix-Style Carousel

### 📱 Single Event (`single/`)
Detailansichten

- `default.php` - Standard Event-Detail
- `fluent.php` - Fluent Design Detail
- `liquid.php` - Fluid Layout Detail

### 🗺️ Map Views (`map/`)
Karten-Integration

- `standard.php` - Standard Google Maps
- `advanced.php` - Erweiterte Karte mit Clustern
- `liquid.php` - Responsive Fluid Map

### 🔍 Search (`search/`)
Suchfunktionen

- `bar.php` - Suchleiste mit Autocomplete
- `advanced.php` - Erweiterte Suche mit Filtern

### 🧩 Widgets (`widgets/`)
Sidebar-Widgets

- `upcoming-events.php` - Nächste Termine
- `calendar-widget.php` - Kalender-Widget
- `countdown-widget.php` - Countdown-Widget

## Template-Variablen

Alle Templates erhalten folgende Variablen:

```php
$events  // Array mit Event-Daten
$args    // Shortcode-Attribute
```

### Event-Datenstruktur

```php
[
    'id' => 123,
    'event_id' => '2026',
    'calendar_id' => '2',
    'calendar_name' => 'Gottesdienst',
    'title' => 'Sonntagsgottesdienst',
    'description' => 'Beschreibung...',
    'start_datetime' => '2025-12-15 10:00:00',
    'end_datetime' => '2025-12-15 11:30:00',
    'location_name' => 'Gemeindehaus',
    'status' => 'active',
    'services' => [
        [
            'service_id' => '1',
            'service_name' => 'Moderation',
            'person_name' => 'Andrea Keller',
        ],
        // ...
    ],
]
```

## Hooks & Filter

### Actions

```php
// Vor Template-Rendering
do_action( 'churchtools_suite_before_template', $template_name, $args );

// Nach Template-Rendering
do_action( 'churchtools_suite_after_template', $template_name, $args );
```

### Filter

```php
// Template-Pfad ändern
apply_filters( 'churchtools_suite_template_path', $path, $template_name, $args );

// Template-Output ändern
apply_filters( 'churchtools_suite_template_output', $output, $template_name, $args );

// Event-Daten vor Template
apply_filters( 'churchtools_suite_template_events', $events, $template_name, $args );
```

## Best Practices

1. **Escape-Funktionen nutzen:**
   ```php
   <?php echo esc_html( $event['title'] ); ?>
   <?php echo esc_attr( $event['id'] ); ?>
   <?php echo esc_url( $event['url'] ); ?>
   ```

2. **WordPress-Funktionen verwenden:**
   ```php
   <?php echo wp_kses_post( $event['description'] ); ?>
   <?php echo date_i18n( get_option( 'date_format' ), $timestamp ); ?>
   ```

3. **Responsive Design:**
   - Mobile-first approach
   - Flexbox/Grid für Layouts
   - Touch-freundliche Buttons

4. **Accessibility:**
   - ARIA-Labels verwenden
   - Keyboard-Navigation unterstützen
   - Semantisches HTML

## Entwicklung

### Neues Template erstellen:

1. Template-Datei in passendem Verzeichnis anlegen
2. PHP-Docblock mit verfügbaren Variablen hinzufügen
3. HTML-Struktur mit BEM-Konvention aufbauen
4. CSS in `public/css/templates/` ergänzen
5. JavaScript in `public/js/templates/` ergänzen (falls nötig)

### Template testen:

```php
// In functions.php oder Plugin
ChurchTools_Suite_Template_Loader::render_template( 'list/classic', [
    'events' => $test_events,
    'args' => [ 'limit' => 10 ],
] );
```

## Support

- GitHub: https://github.com/FEGAschaffenburg/churchtools-suite
- Issues: https://github.com/FEGAschaffenburg/churchtools-suite/issues
