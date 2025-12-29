# ChurchTools Suite v0.7.2.0 - Single Event Templates

## 🎉 Neue Features

### 4 Professionelle Single Event Templates

Wir haben 4 vollständig neue Templates für die Anzeige einzelner Termine erstellt:

1. **Classic** - Strukturierte Standard-Ansicht
2. **Modern** - Hero-Header mit Cards
3. **Minimal** - Text-fokussiert und lesbar
4. **Card** - Premium Card-Design

### Shortcode Verwendung

```php
[cts_event id="123" template="classic"]
[cts_event id="123" template="modern"]
[cts_event id="123" template="minimal"]
[cts_event id="123" template="card"]
```

## 📁 Neue Dateien

### Templates
- `templates/single/classic.php` - Klassisches Template
- `templates/single/modern.php` - Modernes Template
- `templates/single/minimal.php` - Minimalistisches Template
- `templates/single/card.php` - Card-basiertes Template

### CSS
- `assets/css/churchtools-suite-single.css` - Styles für alle Single Templates

### Shortcode Handler
- `includes/shortcodes/class-churchtools-suite-single-event-shortcode.php` - Shortcode-Logik

### Dokumentation
- `docs/SINGLE-EVENT-TEMPLATES.md` - Vollständige Dokumentation
- `docs/single-event-templates-demo.html` - Visuelle Demo (öffne im Browser!)

## 🎨 Template Features

### Classic Template
- ✅ Strukturierter Aufbau
- ✅ Farbiger Kalender-Badge
- ✅ Icon-basierte Meta-Infos
- ✅ Übersichtliche Dienste-Liste
- 📏 Max-Width: 800px

### Modern Template
- ✅ Hero-Header mit Farbverlauf
- ✅ Card-basierte Info-Blöcke
- ✅ Hover-Effekte
- ✅ Grid-Layout für Team
- 📏 Max-Width: 1000px

### Minimal Template
- ✅ Serif-Font für Lesbarkeit
- ✅ Tabellarische Info-Darstellung
- ✅ Reduziertes Design
- ✅ Fokus auf Text
- 📏 Max-Width: 700px

### Card Template
- ✅ Große Datums-Badge (80x80px)
- ✅ 6px Farbiger Akzent-Balken
- ✅ Info-Grid mit Icons
- ✅ Service-Tags mit Hover
- 📏 Max-Width: 900px

## 🔧 Technische Details

### Verfügbare Template-Variablen

```php
$event    // Event-Objekt aus DB
$calendar // Kalender-Objekt (kann null sein)
$services // Array von Service-Objekten
```

### Event-Objekt Properties

```php
$event->id               // DB-ID
$event->event_id         // ChurchTools ID
$event->title            // Titel
$event->description      // Beschreibung (HTML)
$event->start_datetime   // Start (Y-m-d H:i:s)
$event->end_datetime     // Ende (Y-m-d H:i:s)
$event->is_all_day       // Boolean
$event->location_name    // Ort
```

### Calendar-Objekt Properties

```php
$calendar->name          // Name
$calendar->color         // Hex-Color (#xxxxxx)
$calendar->calendar_id   // ChurchTools ID
```

### Service-Objekt Properties

```php
$service->service_name   // Service-Name (z.B. "Predigt")
$service->person_name    // Person (z.B. "Max Mustermann")
```

## 🎯 Verwendungsbeispiele

### 1. In WordPress Seite/Beitrag

```
[cts_event id="42" template="modern"]
```

### 2. In PHP Template

```php
<?php
$event_id = get_query_var('event_id');
echo do_shortcode("[cts_event id='$event_id' template='classic']");
?>
```

### 3. Programmatisch

```php
echo ChurchTools_Suite_Single_Event_Shortcode::render([
    'id' => 42,
    'template' => 'card'
]);
```

## 🎨 Theme Override

Templates können im Theme überschrieben werden:

```
wp-content/
└── themes/
    └── dein-theme/
        └── churchtools-suite/
            └── single/
                ├── classic.php
                ├── modern.php
                ├── minimal.php
                └── card.php
```

## 📱 Responsive Design

Alle Templates sind vollständig responsiv:
- Desktop (>768px): Volle Features
- Mobile (≤768px): Angepasste Layouts

## ⚡ Performance

- CSS: ~15 KB (minified)
- Keine JS-Abhängigkeiten
- Lazy Loading: Nur bei Verwendung geladen

## 🚀 Installation & Update

1. **ZIP hochladen:**
   - WordPress Admin → Plugins → Installieren
   - ZIP: `churchtools-suite-0.7.2.0.zip` hochladen

2. **Aktivieren:**
   - Plugin aktivieren

3. **Testen:**
   - Seite erstellen
   - Shortcode einfügen: `[cts_event id="123" template="modern"]`

## 📊 Migration von v0.7.1.0

Keine Breaking Changes! Automatisches Update möglich.

## ✨ Nächste Schritte

1. **Demo ansehen:**
   - Öffne `docs/single-event-templates-demo.html` im Browser

2. **Dokumentation lesen:**
   - `docs/SINGLE-EVENT-TEMPLATES.md`

3. **Templates testen:**
   - Erstelle Test-Seiten mit allen 4 Templates
   - Vergleiche Designs

4. **Anpassen:**
   - CSS-Variablen überschreiben
   - Templates ins Theme kopieren

## 🐛 Fehlerbehebung

### "Fehler: Keine Event-ID angegeben"
→ `id` Parameter fehlt: `[cts_event id="123"]`

### "Fehler: Event nicht gefunden"
→ Event-ID existiert nicht in DB

### "Fehler: Template 'xyz' nicht gefunden"
→ Ungültiger Template-Name (erlaubt: classic, modern, minimal, card)

## 📞 Support

- Dokumentation: `docs/SINGLE-EVENT-TEMPLATES.md`
- Demo: `docs/single-event-templates-demo.html`
- Template-Verzeichnis: `templates/single/`

## 🎁 Bonus Features

- ✅ **Auto-Erkennung:** Plugin nutzt automatisch Theme-Override
- ✅ **Farbsystem:** Kalender-Farben werden automatisch verwendet
- ✅ **Icons:** SVG-Icons (keine Font-Abhängigkeiten)
- ✅ **Accessibility:** Semantisches HTML mit ARIA-Labels

---

**Version:** 0.7.2.0  
**Datum:** 18. Dezember 2024  
**Neue Dateien:** 9  
**Neue Templates:** 4  
**ZIP-Größe:** 0.23 MB
