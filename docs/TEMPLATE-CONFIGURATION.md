# Template Configuration System (v0.9.9.43)

## Overview

ChurchTools Suite verwendet ein flexibles Template-System für die Darstellung von Events. Es gibt zwei Hauptkontexte:

1. **Single Page**: Vollständige Event-Ansicht auf eigener Seite
2. **Modal**: Overlay-Ansicht in Listen

## Konfiguration

### Admin-Einstellungen

**Pfad:** `Einstellungen > Templates`

Dort können folgende Standard-Templates konfiguriert werden:

- **Standard Single Template**: Wird verwendet für:
  - URL-Parameter: `?event_id=123`
  - Shortcode ohne Template-Parameter: `[cts_event id="123"]`
  
- **Standard Modal Template**: Wird verwendet für:
  - Listen-Shortcodes mit `event_action="modal"`
  - Beispiel: `[cts_events view="list" event_action="modal"]`

### WordPress-Optionen

Templates werden in folgenden Optionen gespeichert:

```php
// Single Page Template
get_option( 'churchtools_suite_single_template', 'modern' );

// Modal Template
get_option( 'churchtools_suite_modal_template', 'event-detail' );
```

## Single Page Templates

### Verfügbare Templates

1. **Modern** (Standard)
   - Pfad: `templates/single/modern.php`
   - Features: Hero-Bild, Meta-Karten, Sidebar mit Karte
   - Responsive: 3 Breakpoints
   - Best for: Moderne, bildlastige Events

2. **Klassisch**
   - Pfad: `templates/single/classic.php`
   - Features: Datum-Badge links, einfaches Layout
   - Responsive: 2 Breakpoints
   - Best for: Minimalistisches Design

3. **Klassisch mit Bild**
   - Pfad: `templates/single/classic-with-image.php`
   - Features: Wie Klassisch, aber mit Event-Bild unter Datum
   - Responsive: 2 Breakpoints
   - Best for: Kombination aus Struktur und Visuals

### Shortcode-Override

Das Standard-Template kann pro Event überschrieben werden:

```php
[cts_event id="123" template="classic"]
```

**Priorität:**
1. Shortcode `template`-Parameter (höchste Priorität)
2. WordPress-Option `churchtools_suite_single_template`
3. Fallback: `'modern'`

### Template-Validierung

Nur folgende Template-Namen sind erlaubt:
- `modern`
- `classic`
- `classic-with-image`

Ungültige Werte werden auf `'modern'` zurückgesetzt.

## Modal Templates

### Verfügbare Templates

1. **Standard Detail** (einziges Template)
   - Pfad: `templates/modal/event-detail.php`
   - Features: AJAX-Laden, Overlay-Darstellung
   - Responsive: Volle Breite auf Mobile

### Erweiterung

Zukünftige Versionen könnten weitere Modal-Templates hinzufügen:
- `modal/modern.php` - Hero-Stil für Modals
- `modal/minimal.php` - Kompakte Darstellung

## Template-Struktur

### Single Page Template

**Minimale Struktur:**

```php
<?php
/**
 * Single Event Template: [Template Name]
 *
 * @package ChurchTools_Suite
 * @since   [Version]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Event-Daten sind verfügbar als $event (Objekt)
// Optional: $calendar (Objekt)
?>

<div class="cts-single-event">
    <h1><?php echo esc_html( $event->title ); ?></h1>
    <!-- Event-Details -->
</div>
```

### Modal Template

**Minimale Struktur:**

```php
<?php
/**
 * Modal Event Template
 *
 * @package ChurchTools_Suite
 * @since   [Version]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="cts-modal-content" data-event-id="">
    <!-- Event-Daten werden via AJAX geladen -->
    <div class="cts-modal-loading">Laden...</div>
</div>
```

## Implementation Details

### Single Event Shortcode

**Datei:** `includes/shortcodes/class-churchtools-suite-single-event-shortcode.php`

```php
public static function render( $atts ): string {
    // Get default from settings
    $default_template = get_option( 'churchtools_suite_single_template', 'modern' );
    
    $atts = shortcode_atts( [
        'id'       => 0,
        'template' => $default_template,
    ], $atts, 'cts_event' );
    
    // Validate template
    $template = self::validate_template( $atts['template'] );
    
    // Load template
    return self::load_template( $template, $event );
}
```

### Modal AJAX Handler

**Datei:** `admin/class-churchtools-suite-admin.php`

```php
public function ajax_get_modal_template() {
    check_ajax_referer( 'churchtools_suite_public', 'nonce' );
    
    // Get configured modal template
    $modal_template = get_option( 'churchtools_suite_modal_template', 'event-detail' );
    $template_path = 'modal/' . sanitize_file_name( $modal_template ) . '.php';
    
    ob_start();
    ChurchTools_Suite_Template_Loader::render_template( $template_path, [], true );
    $html = ob_get_clean();
    
    wp_send_json_success( ['html' => $html] );
}
```

## Best Practices

### Template-Entwicklung

1. **Fallback-Werte**: Immer Fallbacks für fehlende Daten
2. **Escaping**: Alle Ausgaben escapen (`esc_html`, `esc_attr`, etc.)
3. **Responsive**: Mobile-First Design
4. **Performance**: Bilder lazy-loaden, CSS inline für Critical Path
5. **Accessibility**: ARIA-Labels, semantisches HTML

### Settings-Integration

1. **Option-Namen**: Präfix `churchtools_suite_`
2. **Defaults**: Immer Fallback-Werte definieren
3. **Validierung**: Nur erlaubte Template-Namen akzeptieren
4. **Sanitization**: `sanitize_file_name()` für Pfade

### Upgrade-Pfad

Bei Hinzufügen neuer Templates:

1. Template-Datei erstellen: `templates/[type]/[name].php`
2. Template zur Liste hinzufügen: `subtab-templates.php`
3. Validierung erweitern: `validate_template()`
4. Dokumentation aktualisieren

## Changelog

### v0.9.9.43 (Current)
- ✅ Template-Konfiguration via Admin-Einstellungen
- ✅ WordPress-Optionen für Single + Modal Templates
- ✅ Settings-UI unter "Einstellungen > Templates"
- ✅ Single Page: Liest aus `churchtools_suite_single_template`
- ✅ Modal: Liest aus `churchtools_suite_modal_template`
- ✅ Shortcode-Override funktioniert weiterhin

### v0.9.9.40
- Modern Template als Standard gesetzt (hardcoded)

### v0.9.9.39
- Modern Template erstellt

## Siehe auch

- [TEMPLATES-OVERVIEW.md](../../churchtools-suite-demo/TEMPLATES-OVERVIEW.md) - Template-Features
- [Single Event Handler](../includes/class-churchtools-suite-single-event-handler.php) - URL-Parameter Handling
- [Template Loader](../includes/class-churchtools-suite-template-loader.php) - Template-Rendering
