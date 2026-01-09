# Template Structure Proposal (v1.4.0+)

**Status:** 📋 Proposal  
**Target Version:** v1.4.0 (Template Manager)  
**Created:** 8. Januar 2026

---

## 🎯 Problem

Aktuell (v0.9.9.43) ist die Template-Struktur nach **View-Type** organisiert, aber **nicht nach Verwendungszweck** (komplette Views vs. wiederverwendbare Komponenten):

```
templates/
├── calendar/      # Kalender-Ansichten (monthly, weekly)
├── grid/          # Event-Listen als Grid/Cards
├── list/          # Event-Listen als Liste
├── modal/         # Event-Details als Modal
└── single/        # Event-Details als Vollseite
```

**Limitierungen:**
- ❌ Keine klare Trennung zwischen **Views** (komplette Seiten) und **Components** (Bausteine)
- ❌ Keine Templates für **einzelne Komponenten** (Kalender-Card, Tag-Badge, Service-Liste)
- ❌ Keine Trennung zwischen **System-Templates** und **User-Templates**
- ❌ Keine Möglichkeit, Templates zu **aktivieren/deaktivieren**
- ❌ Keine **Preview-Funktion** für Templates
- ❌ Schwierig, **Custom Templates** zu verwalten
- ❌ Unklare Namenskonvention (grid vs. list vs. single)

**Warum ist die Trennung Views/Components wichtig?**

- **Views** sind komplette Seiten/Listen (z.B. Event-Liste, Single Page)
  - Verwenden oft mehrere Components
  - Haben eigenes Layout & Struktur
  - Sind spezialisiert auf einen Use Case

- **Components** sind wiederverwendbare Bausteine (z.B. Date-Badge, Tag-Cloud)
  - Können in mehreren Views verwendet werden
  - Haben kein eigenes Layout
  - Sind generisch & flexibel

---

## 💡 Lösung: Hierarchische Template-Struktur + Template Manager

### Neue Ordnerstruktur

```
templates/
├── views/                    # View-Templates (komplette Seiten/Listen)
│   ├── event-list/           # Event-Listen
│   │   ├── classic.php
│   │   ├── modern.php
│   │   ├── minimal.php
│   │   └── classic-with-images.php
│   ├── event-grid/           # Event-Grids
│   │   ├── simple.php
│   │   ├── background-images.php
│   │   └── masonry.php
│   ├── event-single/         # Event-Vollseiten
│   │   ├── modern.php
│   │   ├── classic.php
│   │   └── classic-with-image.php
│   ├── event-modal/          # Event-Modals
│   │   ├── event-detail.php
│   │   ├── modern.php (geplant)
│   │   └── minimal.php (geplant)
│   ├── event-calendar/       # Kalender-Ansichten (monthly, weekly)
│   │   ├── monthly.php
│   │   └── weekly.php
│   ├── event-timeline/       # Timeline-Ansichten
│   │   └── vertical.php
│   └── event-agenda/         # Agenda-Ansichten
│       └── compact.php
│
├── components/               # Wiederverwendbare Komponenten (Bausteine)
│   ├── calendar/             # Kalender-Komponenten
│   │   ├── card.php          # Kalender als Card
│   │   ├── widget.php        # Sidebar-Widget
│   │   ├── badge.php         # Kleiner Badge
│   │   └── list-item.php     # Listeneintrag
│   ├── tag/                  # Tag-Komponenten
│   │   ├── badge.php         # Tag-Badge (inline)
│   │   ├── card.php          # Tag-Card (erweitert)
│   │   └── cloud.php         # Tag-Cloud
│   ├── service/              # Service-Komponenten
│   │   ├── list.php          # Service-Liste
│   │   ├── card.php          # Service-Card
│   │   ├── badge.php         # Service-Badge
│   │   └── person.php        # Person mit Service
│   └── partials/             # Atomare Bausteine
│       ├── date-badge.php    # Datum-Anzeige
│       ├── time-range.php    # Zeit-Anzeige
│       ├── location-card.php # Standort-Karte
│       ├── image-hero.php    # Hero-Bild Section
│       ├── meta-card.php     # Meta-Informations-Card
│       └── event-excerpt.php # Event-Kurztext
│
├── system/                   # System-Templates (nicht editierbar)
│   ├── views/                # Mirror der View-Templates
│   └── components/           # Mirror der Component-Templates
│
└── custom/                   # User-Templates (editierbar/uploadbar)
    ├── views/                # Custom Views
    └── components/           # Custom Components
```

---

## 🔧 Template Manager Konzept

### Admin-Bereich: Template-Verwaltung

**Neue Admin-Seite:** `ChurchTools Suite > Templates`

#### Features:

1. **Template-Bibliothek**
   - Liste aller verfügbaren Templates
   - Gruppierung nach Typ (Event, Calendar, Tag, Service)
   - Status: Aktiv / Inaktiv / System / Custom

2. **Template-Vorschau**
   - Screenshot jedes Templates
   - Live-Preview mit Test-Daten
   - Responsive-Vorschau (Desktop/Tablet/Mobile)

3. **Template-Einstellungen**
   - Pro Template konfigurierbare Optionen
   - Farben, Schriften, Layout-Optionen
   - Speicherung in `wp_postmeta` oder `wp_options`

4. **Template-Upload**
   - ZIP-Upload für Custom Templates
   - Validierung der Template-Struktur
   - Sicherheits-Check (PHP-Code-Scanning)

5. **Template-Aktivierung**
   - Ein/Aus-Schalter pro Template
   - Nur aktive Templates in Dropdowns sichtbar
   - Fallback auf System-Template wenn deaktiviert

6. **Template-Editor** (optional, v2.0+)
   - Inline-Code-Editor mit Syntax-Highlighting
   - Nur für Custom Templates
   - Versionierung (Git-ähnlich)

---

## 📊 Template-Metadaten

Jedes Template sollte Header-Kommentare enthalten:

### View-Template Beispiel

```php
<?php
/**
 * Template Name: Modern Event List
 * Template Type: view/event-list
 * Template Category: view
 * Description: Moderne Event-Liste mit Hero-Bildern und Gradient-Overlay
 * Version: 1.0.0
 * Author: ChurchTools Suite
 * Author URI: https://example.com
 * Tags: modern, hero, image, gradient, list
 * Requires: image-helper, calendar-helper
 * Uses Components: partials/date-badge, partials/image-hero
 * Preview: /assets/previews/view-event-list-modern.jpg
 * Customizable: true
 * System: false
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="cts-event-list modern">
    <?php foreach ( $events as $event ) : ?>
        <!-- Verwendet Components -->
        <?php get_template_part( 'components/partials/image-hero', null, ['event' => $event] ); ?>
        <?php get_template_part( 'components/partials/date-badge', null, ['event' => $event] ); ?>
    <?php endforeach; ?>
</div>
```

### Component-Template Beispiel

```php
<?php
/**
 * Template Name: Date Badge
 * Template Type: component/partial
 * Template Category: component
 * Description: Datum-Badge mit Tag, Monat, Jahr
 * Version: 1.0.0
 * Author: ChurchTools Suite
 * Tags: date, badge, atomic, reusable
 * Requires: none
 * Preview: /assets/previews/component-date-badge.jpg
 * Customizable: true
 * System: false
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $event wird als Parameter übergeben
$date = new DateTime( $event->start_datetime );
?>

<div class="cts-date-badge">
    <span class="day"><?php echo $date->format( 'd' ); ?></span>
    <span class="month"><?php echo $date->format( 'M' ); ?></span>
    <span class="year"><?php echo $date->format( 'Y' ); ?></span>
</div>
```

---

## 🗄️ Datenbank-Schema

### Neue Tabelle: `wp_cts_templates`

```sql
CREATE TABLE wp_cts_templates (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    template_slug varchar(100) NOT NULL,
    template_name varchar(255) NOT NULL,
    template_type varchar(50) NOT NULL,      -- view/event-list, component/calendar-card
    template_category varchar(20) NOT NULL,  -- 'view' oder 'component'
    template_path varchar(500) NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    is_system tinyint(1) DEFAULT 0,          -- System-Template (nicht löschbar)
    is_custom tinyint(1) DEFAULT 0,          -- User-Template (editierbar)
    uses_components text DEFAULT NULL,       -- JSON: Liste verwendeter Components
    settings longtext DEFAULT NULL,          -- JSON: Template-spezifische Einstellungen
    preview_url varchar(500) DEFAULT NULL,
    version varchar(20) DEFAULT '1.0.0',
    author varchar(255) DEFAULT NULL,
    description text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY template_slug (template_slug),
    KEY template_type (template_type),
    KEY template_category (template_category),
    KEY is_active (is_active)
);
```

---

## 🎨 Template-Registrierung

### PHP-API für Template-Registrierung

```php
// View-Template registrieren
ChurchTools_Suite_Template_Manager::register_template([
    'slug' => 'modern-event-list',
    'name' => 'Modern Event List',
    'type' => 'view/event-list',
    'category' => 'view',
    'path' => 'templates/views/event-list/modern.php',
    'is_system' => true,
    'uses_components' => [
        'partials/date-badge',
        'partials/image-hero',
        'calendar/badge',
    ],
    'settings' => [
        'show_image' => true,
        'show_tags' => true,
        'gradient_color' => '#2563eb',
    ],
    'preview' => 'assets/previews/view-event-list-modern.jpg',
]);

// Component-Template registrieren
ChurchTools_Suite_Template_Manager::register_template([
    'slug' => 'date-badge',
    'name' => 'Date Badge',
    'type' => 'component/partial',
    'category' => 'component',
    'path' => 'templates/components/partials/date-badge.php',
    'is_system' => true,
    'settings' => [
        'show_year' => true,
        'format' => 'short',
    ],
    'preview' => 'assets/previews/component-date-badge.jpg',
]);

// Template abrufen
$template = ChurchTools_Suite_Template_Manager::get_template('modern-event-list');

// View rendern
ChurchTools_Suite_Template_Manager::render('modern-event-list', [
    'events' => $events,
    'calendar' => $calendar,
]);

// Component rendern (in View)
ChurchTools_Suite_Template_Manager::render_component('date-badge', [
    'event' => $event,
]);
```

---

## 🔄 Migration von v0.9.9.43 → v1.4.0

### Automatische Migration

1. **Ordnerstruktur migrieren**
   ```
   # Alt (v0.9.9.43)
   templates/list/modern.php
   templates/grid/simple.php
   templates/single/modern.php
   templates/modal/event-detail.php
   
   # Neu (v1.4.0)
   templates/views/event-list/modern.php
   templates/views/event-grid/simple.php
   templates/views/event-single/modern.php
   templates/views/event-modal/event-detail.php
   ```

2. **Templates in DB registrieren**
   - Alle bestehenden Templates scannen
   - Metadaten aus Header extrahieren
   - Kategorie bestimmen (view vs. component)
   - In `wp_cts_templates` eintragen

3. **Einstellungen migrieren**
   ```php
   // Alt
   churchtools_suite_single_template = 'modern'
   
   // Neu
   churchtools_suite_default_template_view_event_single = 'modern-event-single'
   ```

4. **Shortcode-Kompatibilität**
   ```php
   // Alt (weiterhin unterstützt via Alias)
   [cts_events view="list" template="modern"]
   
   // Neu (empfohlen)
   [cts_events template="modern-event-list"]
   
   // Template-Typ automatisch erkannt
   [cts_event id="123" template="modern-event-single"]  // View
   [cts_calendar_card template="widget"]                 // Component
   ```

5. **Component-Integration**
   - Bestehende Views analysieren
   - Wiederholte Code-Blöcke identifizieren
   - Als Components extrahieren
   - Views aktualisieren mit `get_template_part()`

---

## 📋 Implementierungs-Phasen

### Phase 1: Struktur-Refactoring (v1.4.0)
- [ ] Neue Ordnerstruktur erstellen
- [ ] Bestehende Templates migrieren
- [ ] Migration-Script schreiben
- [ ] Kompatibilitäts-Layer für alte Pfade

### Phase 2: Template-Manager Backend (v1.4.1)
- [ ] Template-Datenbank-Tabelle
- [ ] Template-Registration-API
- [ ] Template-Scanner (automatisches Erkennen)
- [ ] Template-Validator

### Phase 3: Template-Manager UI (v1.4.2)
- [ ] Admin-Seite "Templates"
- [ ] Template-Liste mit Gruppierung
- [ ] Aktivieren/Deaktivieren-Toggle
- [ ] Template-Einstellungen-Seite

### Phase 4: Template-Upload (v1.4.3)
- [ ] ZIP-Upload-Funktion
- [ ] Template-Validator für Uploads
- [ ] Sicherheits-Scanner
- [ ] Custom-Templates-Verwaltung

### Phase 5: Template-Previews (v1.4.4)
- [ ] Screenshot-Generator
- [ ] Live-Preview mit Test-Daten
- [ ] Responsive-Vorschau
- [ ] Template-Galerie

### Phase 6: Komponenten-Templates (v1.5.0)
- [ ] Calendar-Komponenten erstellen
- [ ] Tag-Komponenten erstellen
- [ ] Service-Komponenten erstellen
- [ ] Partial-Templates erstellen

---

## 🎯 Vorteile

### Für Administratoren:
- ✅ Zentrale Verwaltung aller Templates
- ✅ Ein/Aus-Schalter pro Template
- ✅ Preview vor Aktivierung
- ✅ Template-Einstellungen ohne Code-Änderungen
- ✅ Custom Templates hochladen

### Für Entwickler:
- ✅ Klare Template-Hierarchie
- ✅ Wiederverwendbare Komponenten
- ✅ Template-API für programmatische Nutzung
- ✅ Versionierung und Abhängigkeiten
- ✅ Einfaches Erstellen neuer Templates

### Für User:
- ✅ Mehr Template-Optionen
- ✅ Konsistente Darstellung
- ✅ Schnellere Ladezeiten (nur aktive Templates laden)
- ✅ Bessere Anpassbarkeit

---

## 🚀 Beispiel-Use-Cases

### 1. Kalender-Widget in Sidebar (Component)

```php
[cts_calendar_card id="main" template="widget"]
```

**Template:** `templates/components/calendar/widget.php`  
**Kategorie:** Component  
**Typ:** component/calendar

---

### 2. Tag-Cloud auf Startseite (Component)

```php
[cts_tag_cloud template="cloud" count="20"]
```

**Template:** `templates/components/tag/cloud.php`  
**Kategorie:** Component  
**Typ:** component/tag

---

### 3. Service-Liste mit Personen (Component)

```php
[cts_service_list event_id="123" template="person"]
```

**Template:** `templates/components/service/person.php`  
**Kategorie:** Component  
**Typ:** component/service

---

### 4. Event-Liste mit modernem Design (View)

```php
[cts_events template="modern-event-list"]
```

**Template:** `templates/views/event-list/modern.php`  
**Kategorie:** View  
**Typ:** view/event-list  
**Verwendet Components:**
- `components/partials/date-badge.php`
- `components/partials/image-hero.php`
- `components/calendar/badge.php`

---

### 5. Custom Event-Grid hochladen (View + Components)

**User:** Designer hat Custom Grid-Template mit eigenen Components erstellt

**ZIP-Struktur:**
```
my-custom-grid.zip
├── view/
│   └── event-grid.php          # Main View Template
├── components/
│   ├── custom-card.php         # Custom Card Component
│   └── custom-badge.php        # Custom Badge Component
├── assets/
│   ├── style.css
│   └── preview.jpg
└── template.json               # Metadaten
```

**template.json:**
```json
{
  "name": "My Custom Grid",
  "slug": "my-custom-grid",
  "type": "view/event-grid",
  "category": "view",
  "version": "1.0.0",
  "components": [
    {
      "slug": "custom-card",
      "type": "component/custom",
      "path": "components/custom-card.php"
    },
    {
      "slug": "custom-badge",
      "type": "component/custom",
      "path": "components/custom-badge.php"
    }
  ],
  "uses_components": [
    "custom-card",
    "custom-badge",
    "partials/date-badge"
  ]
}
```

**Schritte:**
1. ZIP über `Templates > Hochladen` uploaden
2. System erkennt View + Components
3. Registriert beide in DB
4. Aktivieren im Template-Manager
5. Verwenden: `[cts_events template="my-custom-grid"]`

---

## 📝 Template.json Format

```json
{
  "name": "My Custom Grid",
  "slug": "my-custom-grid",
  "type": "event/grid",
  "version": "1.0.0",
  "author": "John Doe",
  "author_uri": "https://example.com",
  "description": "A beautiful custom grid layout",
  "tags": ["grid", "custom", "modern"],
  "requires": {
    "php": "8.0",
    "wordpress": "6.0",
    "churchtools-suite": "0.9.9"
  },
  "assets": {
    "css": ["style.css"],
    "js": ["script.js"],
    "preview": "preview.jpg"
  },
  "settings": {
    "columns": {
      "type": "number",
      "default": 3,
      "label": "Number of Columns"
    },
    "show_image": {
      "type": "boolean",
      "default": true,
      "label": "Show Event Image"
    },
    "primary_color": {
      "type": "color",
      "default": "#2563eb",
      "label": "Primary Color"
    }
  }
}
```

---

## 🔒 Sicherheit

### Template-Upload-Validierung

1. **Dateiformat**: Nur `.php`, `.css`, `.js`, `.json`, `.jpg`, `.png`
2. **PHP-Code-Scanning**: 
   - Keine `eval()`, `exec()`, `system()`
   - Keine Datei-Uploads (`move_uploaded_file()`)
   - Keine Datenbank-Direktzugriff (nur WP-API)
3. **Größenlimit**: Max. 5 MB pro Template-ZIP
4. **Sandbox**: Custom Templates laufen in eingeschränkter Umgebung

---

## 📊 Performance

### Template-Caching

```php
// Template-Rendering mit Cache
$cache_key = 'cts_template_' . md5($template_slug . serialize($data));
$output = wp_cache_get($cache_key);

if (false === $output) {
    $output = ChurchTools_Suite_Template_Manager::render($template_slug, $data);
    wp_cache_set($cache_key, $output, 'cts_templates', HOUR_IN_SECONDS);
}

echo $output;
```

### Lazy-Loading

- Nur aktive Templates laden
- Template-Assets on-demand laden
- Preview-Bilder lazy-loaden

---

## 🎓 Dokumentation

Neue Dokumentations-Seiten:

1. **TEMPLATE-DEVELOPMENT-GUIDE.md**
   - Template erstellen
   - Template-Header-Format
   - Best Practices

2. **TEMPLATE-API-REFERENCE.md**
   - Template-Registration
   - Template-Rendering
   - Helper-Funktionen

3. **CUSTOM-TEMPLATES-TUTORIAL.md**
   - Schritt-für-Schritt Anleitung
   - Beispiel-Templates
   - Troubleshooting

---

## 🔮 Future Enhancements (v2.0+)

- **Template Marketplace**: Templates kaufen/verkaufen
- **Template-Editor**: Visueller Drag & Drop Editor
- **Template-Versioning**: Git-ähnliche Versionskontrolle
- **Template-Themes**: Mehrere Templates als Bundle
- **AI-Template-Generator**: Template aus Beschreibung generieren

---

## 📞 Feedback

Dieses Proposal ist offen für Feedback. Bitte kommentieren:
- Fehlen wichtige Template-Typen?
- Ist die Struktur zu komplex?
- Welche Features sind am wichtigsten?

---

**Letzte Aktualisierung:** 8. Januar 2026  
**Status:** 📋 Proposal (Diskussion)  
**Next Steps:** Community Feedback → Roadmap Finalisierung
