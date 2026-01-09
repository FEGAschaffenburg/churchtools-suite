# Views vs. Components - Klare Trennung (v1.4.0)

**Datum:** 8. Januar 2026  
**Frage:** "sollte man die templates für die views und ander Templates nihct ncohmal trennen ?"  
**Antwort:** ✅ **Ja, definitiv!** Trennung in `views/` und `components/` macht absolut Sinn.

---

## 🎯 Warum die Trennung wichtig ist

### **Views** = Komplette Seiten/Listen
- Zeigen **mehrere Events** oder **ein Event komplett** an
- Haben eigenes **Layout und Struktur**
- **Verwenden** oft mehrere Components
- Spezialisiert auf **einen Use Case**
- Beispiele: Event-Liste, Event-Grid, Single Page, Modal

### **Components** = Wiederverwendbare Bausteine
- Zeigen **einen Aspekt** (z.B. Datum, Ort, Kalender-Info)
- Haben **kein eigenes Layout** (werden in Views eingebettet)
- Können in **mehreren Views** verwendet werden
- Sind **generisch und flexibel**
- Beispiele: Date-Badge, Location-Card, Tag-Badge, Calendar-Widget

---

## 📁 Struktur-Vergleich

### ❌ **VORHER (v0.9.9.43)** - Unklare Trennung

```
templates/
├── list/           # Was ist das? View? Component?
├── grid/           # Was ist das? View? Component?
├── single/         # OK, klar ein View
├── modal/          # OK, klar ein View
└── calendar/       # UNKLAR: Kalender-View ODER Kalender-Component?
```

**Problem:** `calendar/` kann sowohl ein View (monthly/weekly Kalender-Ansicht) als auch ein Component (Kalender-Card für Sidebar) sein!

---

### ✅ **NACHHER (v1.4.0)** - Klare Trennung

```
templates/
├── views/                    # Komplette Seiten/Listen
│   ├── event-list/           # Event-Listen-Views
│   │   ├── classic.php
│   │   ├── modern.php
│   │   └── minimal.php
│   ├── event-grid/           # Event-Grid-Views
│   │   ├── simple.php
│   │   └── background-images.php
│   ├── event-single/         # Event-Vollseiten-Views
│   │   ├── modern.php
│   │   ├── classic.php
│   │   └── classic-with-image.php
│   ├── event-modal/          # Event-Modal-Views
│   │   ├── event-detail.php
│   │   └── modern.php
│   ├── event-calendar/       # Kalender-VIEWS (monthly, weekly)
│   │   ├── monthly.php
│   │   └── weekly.php
│   └── event-timeline/       # Timeline-Views
│       └── vertical.php
│
├── components/               # Wiederverwendbare Bausteine
│   ├── calendar/             # Kalender-COMPONENTS (nicht Views!)
│   │   ├── card.php          # Kalender als Card (für Sidebar)
│   │   ├── widget.php        # Kalender-Widget
│   │   └── badge.php         # Kalender-Badge (klein)
│   ├── tag/                  # Tag-Components
│   │   ├── badge.php         # Tag-Badge (inline)
│   │   ├── card.php          # Tag-Card
│   │   └── cloud.php         # Tag-Cloud
│   ├── service/              # Service-Components
│   │   ├── list.php          # Service-Liste
│   │   ├── card.php          # Service-Card
│   │   └── person.php        # Person mit Service
│   └── partials/             # Atomare Bausteine (überall einsetzbar)
│       ├── date-badge.php    # Datum-Anzeige
│       ├── time-range.php    # Zeit-Anzeige
│       ├── location-card.php # Standort-Karte
│       ├── image-hero.php    # Hero-Bild Section
│       └── meta-card.php     # Meta-Informations-Card
│
├── system/                   # System-Templates (Backup)
│   ├── views/
│   └── components/
│
└── custom/                   # User-Templates
    ├── views/
    └── components/
```

---

## 🔍 Beispiele für Views vs. Components

### 1. **Event-Liste** (View)

**Pfad:** `templates/views/event-list/modern.php`  
**Typ:** `view/event-list`  
**Verwendet Components:**
- `components/partials/date-badge.php`
- `components/partials/image-hero.php`
- `components/calendar/badge.php`
- `components/tag/badge.php`

**Code-Beispiel:**
```php
<?php foreach ( $events as $event ) : ?>
    <div class="event-item">
        <?php 
        // Verwendet Components
        get_template_part( 'components/partials/date-badge', null, ['event' => $event] );
        get_template_part( 'components/partials/image-hero', null, ['event' => $event] );
        get_template_part( 'components/calendar/badge', null, ['calendar' => $event->calendar] );
        ?>
        <h3><?php echo esc_html( $event->title ); ?></h3>
    </div>
<?php endforeach; ?>
```

---

### 2. **Kalender-Widget** (Component)

**Pfad:** `templates/components/calendar/widget.php`  
**Typ:** `component/calendar`  
**Verwendet in:** Sidebar, Footer, Custom Views

**Code-Beispiel:**
```php
<div class="cts-calendar-widget">
    <div class="calendar-icon"><?php echo $calendar->icon; ?></div>
    <h4><?php echo esc_html( $calendar->name ); ?></h4>
    <span class="event-count"><?php echo $calendar->event_count; ?> Events</span>
</div>
```

**Verwendung:**
```php
// In Sidebar
[cts_calendar_card id="main" template="widget"]

// In Custom View
get_template_part( 'components/calendar/widget', null, ['calendar' => $calendar] );
```

---

### 3. **Date-Badge** (Component/Partial)

**Pfad:** `templates/components/partials/date-badge.php`  
**Typ:** `component/partial`  
**Verwendet in:** Alle Event-Views (List, Grid, Single, Modal)

**Code-Beispiel:**
```php
<div class="cts-date-badge">
    <span class="day"><?php echo $date->format( 'd' ); ?></span>
    <span class="month"><?php echo $date->format( 'M' ); ?></span>
    <span class="year"><?php echo $date->format( 'Y' ); ?></span>
</div>
```

**Verwendung:**
```php
// In jedem Event-View
get_template_part( 'components/partials/date-badge', null, ['event' => $event] );
```

---

## 🎨 Vorteile der Trennung

### Für Entwickler:
- ✅ **Klare Semantik**: View vs. Component sofort erkennbar
- ✅ **DRY-Prinzip**: Components wiederverwendbar in mehreren Views
- ✅ **Modularität**: Components unabhängig testbar
- ✅ **Wartbarkeit**: Änderung an Component → alle Views profitieren
- ✅ **Dokumentation**: Struktur selbsterklärend

### Für Designer:
- ✅ **Konsistenz**: Components sehen überall gleich aus
- ✅ **Flexibilität**: Views können Components frei kombinieren
- ✅ **Einfachheit**: Components sind klein und überschaubar
- ✅ **Anpassbarkeit**: Nur Component ändern, nicht alle Views

### Für Administratoren:
- ✅ **Übersichtlichkeit**: Klar getrennte Template-Typen
- ✅ **Kontrolle**: Views und Components separat aktivieren/deaktivieren
- ✅ **Upload**: ZIP kann Views UND Components enthalten
- ✅ **Preview**: Unterschiedliche Vorschau-Modi für Views vs. Components

---

## 📊 Template-Kategorien im Detail

### View-Templates

| Kategorie | Ordner | Beispiele | Beschreibung |
|-----------|--------|-----------|--------------|
| Event-Liste | `views/event-list/` | classic, modern, minimal | Zeigt mehrere Events als Liste |
| Event-Grid | `views/event-grid/` | simple, background-images | Zeigt mehrere Events als Grid |
| Event-Single | `views/event-single/` | modern, classic | Zeigt ein Event auf Vollseite |
| Event-Modal | `views/event-modal/` | event-detail, modern | Zeigt ein Event im Modal |
| Event-Calendar | `views/event-calendar/` | monthly, weekly | Zeigt Events in Kalender-Ansicht |
| Event-Timeline | `views/event-timeline/` | vertical, horizontal | Zeigt Events auf Zeitachse |
| Event-Agenda | `views/event-agenda/` | compact, detailed | Zeigt Events als Tagesordnung |

### Component-Templates

| Kategorie | Ordner | Beispiele | Beschreibung |
|-----------|--------|-----------|--------------|
| Kalender | `components/calendar/` | card, widget, badge | Kalender-Info-Komponenten |
| Tag | `components/tag/` | badge, card, cloud | Tag-Darstellungen |
| Service | `components/service/` | list, card, person | Service-Komponenten |
| Partials | `components/partials/` | date-badge, location-card | Atomare Bausteine |

---

## 🔄 Migration-Beispiele

### Beispiel 1: Event-Liste

```
# Alt (v0.9.9.43)
templates/list/modern.php

# Neu (v1.4.0)
templates/views/event-list/modern.php
```

### Beispiel 2: Event-Grid

```
# Alt
templates/grid/simple.php

# Neu
templates/views/event-grid/simple.php
```

### Beispiel 3: Kalender-Ansicht (View)

```
# Alt
templates/calendar/monthly.php

# Neu (als VIEW)
templates/views/event-calendar/monthly.php
```

### Beispiel 4: Kalender-Card (Component)

```
# Alt: Existierte nicht!

# Neu (als COMPONENT)
templates/components/calendar/card.php
```

**Wichtig:** `calendar/` war vorher UNKLAR (View oder Component?). Jetzt:
- **View:** `views/event-calendar/` (Kalender-Ansicht mit allen Events)
- **Component:** `components/calendar/` (Kalender-Info-Card für Sidebar)

---

## 🚀 Shortcode-Unterscheidung

### Views (komplette Seiten)

```php
// Event-Liste
[cts_events template="modern-event-list"]        # View: views/event-list/modern.php

// Event-Grid
[cts_events template="simple-event-grid"]        # View: views/event-grid/simple.php

// Single Page
[cts_event id="123" template="modern-event-single"] # View: views/event-single/modern.php

// Kalender-Ansicht (View!)
[cts_events_calendar template="monthly"]          # View: views/event-calendar/monthly.php
```

### Components (Bausteine)

```php
// Kalender-Card (Component!)
[cts_calendar_card id="main" template="widget"]  # Component: components/calendar/widget.php

// Tag-Cloud (Component!)
[cts_tag_cloud template="cloud"]                 # Component: components/tag/cloud.php

// Service-Liste (Component!)
[cts_service_list event_id="123"]                # Component: components/service/list.php
```

---

## 📦 Template-Upload mit Views + Components

**ZIP-Struktur:**
```
my-custom-template.zip
├── views/
│   └── event-list/
│       └── custom.php          # Custom View Template
├── components/
│   ├── custom-card.php         # Custom Card Component
│   └── custom-badge.php        # Custom Badge Component
├── assets/
│   ├── style.css
│   ├── view-preview.jpg        # Preview für View
│   └── component-preview.jpg   # Preview für Components
└── template.json
```

**template.json:**
```json
{
  "name": "My Custom Template Package",
  "version": "1.0.0",
  "views": [
    {
      "slug": "custom-event-list",
      "name": "Custom Event List",
      "type": "view/event-list",
      "category": "view",
      "path": "views/event-list/custom.php",
      "uses_components": ["custom-card", "custom-badge", "partials/date-badge"],
      "preview": "assets/view-preview.jpg"
    }
  ],
  "components": [
    {
      "slug": "custom-card",
      "name": "Custom Card",
      "type": "component/custom",
      "category": "component",
      "path": "components/custom-card.php",
      "preview": "assets/component-preview.jpg"
    },
    {
      "slug": "custom-badge",
      "name": "Custom Badge",
      "type": "component/custom",
      "category": "component",
      "path": "components/custom-badge.php"
    }
  ]
}
```

**Ergebnis nach Upload:**
- ✅ 1 View registriert: `custom-event-list`
- ✅ 2 Components registriert: `custom-card`, `custom-badge`
- ✅ Abhängigkeiten erkannt: View verwendet Components
- ✅ Beide Kategorien im Template-Manager sichtbar

---

## ✅ Fazit

**Views/Components Trennung ist essentiell für:**

1. **Klarheit**: Sofort erkennbar was View, was Component ist
2. **Wiederverwendbarkeit**: Components in mehreren Views nutzbar
3. **Wartbarkeit**: Component-Änderung → alle Views profitieren
4. **Erweiterbarkeit**: Neue Components → sofort in allen Views verfügbar
5. **Upload-System**: Views und Components separat hochladbar
6. **Template-Manager**: Getrennte Listen für Views und Components

**Status:** ✅ In Proposal `TEMPLATE-STRUCTURE-PROPOSAL.md` vollständig umgesetzt

**Roadmap:** v1.4.0 (Template Manager & Structure Refactoring)
