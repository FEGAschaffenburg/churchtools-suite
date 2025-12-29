# Single Event Templates - Dokumentation

## Übersicht

Das ChurchTools Suite Plugin bietet 4 verschiedene Templates für die Anzeige einzelner Termine auf Detailseiten.

## Shortcode Verwendung

```php
[cts_event id="TERMIN_ID" template="TEMPLATE_NAME"]
```

### Parameter

- **id** (erforderlich): Die ID des Termins aus der Datenbank
- **template** (optional): Template-Name (Standard: `classic`)
  - `classic` - Klassische strukturierte Ansicht
  - `modern` - Hero-Header mit Card-Design
  - `minimal` - Minimalistische Text-fokussierte Ansicht
  - `card` - Card-basiertes Design mit visuellen Akzenten

## Template-Übersicht

### 1. Classic Template

**Verwendung:** `[cts_event id="123" template="classic"]`

**Features:**
- Strukturierter Aufbau mit klarer Hierarchie
- Farbiger Kalender-Badge
- Meta-Informationen in grauen Boxen
- Icon-basierte Darstellung von Datum/Ort
- Liste der Dienste mit Personen-Icons
- Zurück-Button

**Einsatzzweck:** Standard-Detailseiten, informationsreich, seriös

**Design:**
- Maximale Breite: 800px
- Zentriert
- Klare Typografie mit großer Überschrift (36px)
- Graue Akzentfarben (#f9fafb, #e5e7eb)

---

### 2. Modern Template

**Verwendung:** `[cts_event id="123" template="modern"]`

**Features:**
- Hero-Header mit Farbverlauf des Kalenders
- Card-basierte Informationsblöcke
- Hover-Effekte auf Cards
- Grid-Layout für Team-Mitglieder
- Farbige Icons passend zur Kalenderfarbe
- "Zurück zur Übersicht"-Button

**Einsatzzweck:** Moderne Websites, Event-Seiten, Marketing-orientiert

**Design:**
- Maximale Breite: 1000px
- Großer Hero-Bereich mit Gradient
- Cards mit Schatten und Transform-Effekten
- Größere Abstände (40px padding)

---

### 3. Minimal Template

**Verwendung:** `[cts_event id="123" template="minimal"]`

**Features:**
- Reduziertes Design ohne Ablenkung
- Serif-Font für Titel (Georgia)
- Tabellarische Informationsdarstellung
- Einfache Dienste-Liste
- Fokus auf Lesbarkeit

**Einsatzzweck:** Text-lastige Seiten, Newsletter-ähnlich, Blog-Style

**Design:**
- Maximale Breite: 700px
- Zentrierte Überschrift
- Tabellarisches Info-Layout (Label: Value)
- Farbakzent: Linker Rand (#6366f1)

---

### 4. Card Template

**Verwendung:** `[cts_event id="123" template="card"]`

**Features:**
- Große Datums-Badge im Header
- Farbiger Akzent-Balken
- Info-Grid mit Icons
- Service-Tags mit Hover-Effekten
- Moderner Card-Container mit Schatten

**Einsatzzweck:** Premium-Events, Konferenzen, wichtige Termine

**Design:**
- Maximale Breite: 900px
- Großer Schatten (box-shadow)
- 6px Akzent-Bar oben
- 80x80px Datums-Badge
- Border-Radius: 16px

## Anpassungen im Theme

### Theme Override

Um ein Template anzupassen, kopiere es in dein Theme:

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

Das Plugin verwendet automatisch die Theme-Version.

### CSS Anpassungen

Die Styles sind in `assets/css/churchtools-suite-single.css` definiert.

**Beispiel: Farben ändern**

```css
/* Classic Template - Primärfarbe ändern */
.cts-single-classic .cts-single-icon {
    color: #your-color !important;
}

/* Modern Template - Hero-Gradient überschreiben */
.cts-single-modern .cts-single-hero {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%) !important;
}
```

## Verfügbare Template-Variablen

Alle Templates haben Zugriff auf:

```php
$event    // Termin-Objekt aus der Datenbank
$calendar // Kalender-Objekt (kann null sein)
$services // Array von Service-Objekten (kann leer sein)
```

### Event-Objekt Eigenschaften

```php
$event->id               // Integer: Datenbank-ID
$event->event_id         // String: ChurchTools Event-ID
$event->calendar_id      // String: Kalender-ID
$event->appointment_id   // String: Appointment-ID (optional)
$event->title            // String: Event-Titel
$event->description      // String: Beschreibung (HTML erlaubt)
$event->start_datetime   // String: Start (Y-m-d H:i:s)
$event->end_datetime     // String: Ende (Y-m-d H:i:s)
$event->is_all_day       // Boolean: Ganztägig?
$event->location_name    // String: Ort/Location
$event->status           // String: Status
$event->raw_payload      // String: JSON der ChurchTools API
$event->created_at       // String: Erstellungsdatum
$event->updated_at       // String: Letzte Änderung
```

### Calendar-Objekt Eigenschaften

```php
$calendar->id              // Integer: Datenbank-ID
$calendar->calendar_id     // String: ChurchTools Kalender-ID
$calendar->name            // String: Kalender-Name
$calendar->name_translated // String: Übersetzter Name
$calendar->color           // String: Hex-Color (#xxxxxx)
$calendar->is_selected     // Boolean: Für Sync ausgewählt?
$calendar->is_public       // Boolean: Öffentlich?
$calendar->sort_order      // Integer: Sortierung
```

### Service-Objekt Eigenschaften

```php
$service->id           // Integer: Datenbank-ID
$service->event_id     // Integer: Zugehöriger Event
$service->service_id   // String: ChurchTools Service-ID
$service->service_name // String: Service-Name (z.B. "Predigt")
$service->person_name  // String: Person (z.B. "Max Mustermann")
$service->created_at   // String: Erstellungsdatum
$service->updated_at   // String: Letzte Änderung
```

## Beispiele

### 1. Einfache Verwendung

```php
// In Seite oder Beitrag
[cts_event id="42" template="modern"]
```

### 2. Dynamische ID via PHP

```php
// In Template-Datei
<?php
$event_id = get_query_var('event_id', 0);
echo do_shortcode("[cts_event id='$event_id' template='classic']");
?>
```

### 3. In Elementor/Beaver Builder

Verwende das Shortcode-Widget:

```
[cts_event id="42" template="card"]
```

### 4. Programmatisch

```php
// In functions.php oder Plugin
echo ChurchTools_Suite_Single_Event_Shortcode::render([
    'id' => 42,
    'template' => 'minimal'
]);
```

## Responsive Design

Alle Templates sind vollständig responsiv:

- **Desktop** (> 768px): Volle Breite mit allen Features
- **Mobile** (≤ 768px): Angepasste Layouts
  - Kleinere Schriftgrößen
  - Reduzierte Paddings
  - Gestapelte Grids (statt nebeneinander)

## Browser-Unterstützung

- Chrome/Edge (modern)
- Firefox
- Safari
- Mobile Browsers (iOS, Android)

## Performance

- **CSS:** ~15 KB (minified)
- **Keine JS-Abhängigkeiten** (außer optional für Zurück-Button)
- **Lazy Loading:** Styles werden nur bei Verwendung geladen

## Fehlermeldungen

### "Fehler: Keine Event-ID angegeben"
→ Der `id` Parameter fehlt im Shortcode

### "Fehler: Event nicht gefunden"
→ Die angegebene ID existiert nicht in der Datenbank

### "Fehler: Template 'xyz' nicht gefunden"
→ Ungültiger Template-Name verwendet

## Weitere Ressourcen

- Haupt-Templates: `/templates/`
- CSS-Datei: `/assets/css/churchtools-suite-single.css`
- Shortcode-Handler: `/includes/shortcodes/class-churchtools-suite-single-event-shortcode.php`
- Template-Override Beispiel: `https://docs.churchtools-suite.de/templates`
