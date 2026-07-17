# List Views - CSS Audit & BEM Naming Analyse

**Erstellt:** 16. Februar 2026  
**Plugin Version:** v1.0.6.0  
**Zweck:** Dokumentation der CSS-Struktur und BEM Naming für alle List Views

---

## 1. CSS-Dateien Übersicht

### 1.1 Haupt-CSS (Aktiv)

| Datei | Zweck | Geladen | Zeilen | Status |
|-------|-------|---------|--------|--------|
| **churchtools-suite-public.css** | Haupt-Stylesheet für alle Views | ✅ Immer | 2880 | ✅ MASTER |
| **churchtools-suite-list-modern.css** | Modernisierte CSS mit BEM | ✅ Konditional | 1699 | ✅ Modern Views |
| **churchtools-suite-single.css** | Single Event Pages | ✅ Konditional | - | ✅ Single Views |

### 1.2 Admin-CSS (Doppelte Dateien gefunden!)

| Datei | Pfad | Status |
|-------|------|--------|
| churchtools-suite-admin.css | `/admin/css/` | ✅ ORIGINAL |
| churchtools-suite-admin.css | `/assets/css/` | ❌ DUPLIKAT - LÖSCHEN! |

**AKTION ERFORDERLICH:** `/assets/css/churchtools-suite-admin.css` ist ein Duplikat und kann gelöscht werden.

### 1.3 Inline Styles Audit

**Erlaubte Inline Styles** (nur für dynamische Werte):
- ✅ Kalenderfarben: `style="background-color: <?php echo $calendar_color; ?>"`
- ✅ Tag-Farben: `style="background-color: <?php echo $tag['color']; ?>"`
- ✅ Custom Style Mode: CSS Custom Properties (--cts-primary-color, etc.)
- ✅ Modal Display: `style="display: none;"` (initialer JavaScript-State)

**Gefundene Inline Styles:** 20 Matches
- ✅ Alle sind gerechtfertigt (dynamische Farben oder JS-Initial-States)

---

## 2. BEM Naming Convention Analyse

### 2.1 BEM-konforme Classes

**Block-Level:**
```
.cts-list
.cts-event
.cts-date-box
.cts-calendar
.cts-grid
.cts-modal
```

**Element-Level (mit __):**
```
.cts-list__item
.cts-list__empty-state
.cts-date-box__month
.cts-event__title
.cts-event__description
```

**Modifier-Level (mit --):**
```
.cts-list--classic
.cts-list--minimal
.cts-list--modern
.cts-event--clickable
```

### 2.2 Legacy Naming (Nicht-BEM)

**Gefunden in Classic/Classic-with-Images:**
```css
/* ❌ LEGACY - Kein BEM */
.cts-event-classic
.cts-title-block
.cts-date-month
.cts-date-day
.cts-date-weekday
.cts-list-location
.cts-list-tags
.cts-tag-badge
```

**Gefunden in Minimal:**
```css
/* ✅ BEM-KONFORM */
.cts-list--minimal
.cts-list__item
.cts-list__empty-state
```

**Gefunden in Classic-Modern:**
```css
/* ✅ BEM-KONFORM */
.cts-list--classic
.cts-list__item
.cts-list__date
.cts-list__title
```

**Gefunden in Modern:**
```css
/* ⚠️ GEMISCHT */
.cts-list-modern         /* ❌ Legacy */
.cts-event-modern        /* ❌ Legacy */
.cts-event-header        /* ❌ Legacy */
.cts-event-date          /* ❌ Legacy */
.cts-event-time          /* ❌ Legacy */
```

### 2.3 BEM Compliance Report

| View | Template | BEM Compliance | Status |
|------|----------|----------------|--------|
| **Classic** | classic.php | ❌ 0% | Legacy Naming |
| **Classic with Images** | classic-with-images.php | ❌ 0% | Legacy Naming |
| **Classic Modern** | classic-modern.php | ✅ 95% | BEM-konform |
| **Minimal** | minimal.php | ✅ 100% | BEM-konform |
| **Modern** | modern.php | ⚠️ 60% | Teilweise BEM |

---

## 3. List Views - Detaillierte Übersicht

### 3.1 Classic View

**Datei:** `templates/views/event-list/classic.php`  
**CSS-Quelle:** `churchtools-suite-public.css` (Zeilen 72-220)  
**Layout:** Flexbox horizontal, einzeilig  
**Optimiert:** Ja (16.02.2026 - Datumsbox 36x36px, Services wrappable)

**HTML-Struktur:**
```html
<div class="churchtools-suite-wrapper" data-style-mode="theme|plugin|custom">
  <div class="cts-list cts-list-classic">
    
    <!-- Month Separator (optional) -->
    <div class="cts-month-separator">
      <span class="cts-month-name">Februar 2026</span>
    </div>
    
    <!-- Event Item -->
    <div class="cts-event-classic">
      
      <!-- Date Box (36x36px) -->
      <div class="cts-date-box">
        <div class="cts-date-month">FEB.</div>
        <div class="cts-date-day">17</div>
        <div class="cts-date-weekday">DI.</div>
      </div>
      
      <!-- Time -->
      <div class="cts-time">09:00 Uhr</div>
      
      <!-- Calendar Name (optional) -->
      <div class="cts-calendar-name">Jugendraum</div>
      
      <!-- Title Block (vertical 2 lines) -->
      <div class="cts-title-block">
        <div class="cts-title">Bibelkreis</div>
        <div class="cts-event-description">Bibeltext lesen...</div>
      </div>
      
      <!-- Services (wrappable 2-3 lines) -->
      <div class="cts-services">
        Leitung: Max Mustermann | Moderation: Anna Schmidt
      </div>
      
      <!-- Location (optional) -->
      <div class="cts-list-location">
        Versammlungsraum
        <span class="cts-location-info-icon" data-tooltip="...">ℹ️</span>
      </div>
      
      <!-- Tags (optional) -->
      <div class="cts-list-tags">
        <span class="cts-tag-badge" style="background-color: #6b7280;">Jugend</span>
      </div>
      
    </div>
  </div>
</div>
```

**CSS Classes:**
```css
/* Container */
.churchtools-suite-wrapper          /* Wrapper mit data-style-mode */
.cts-list-classic                   /* List container */

/* Month Separator */
.cts-month-separator                /* Month divider */
.cts-month-name                     /* Month text */

/* Event Item */
.cts-event-classic                  /* Event line (Flexbox horizontal) */

/* Date Box */
.cts-date-box                       /* 36x36px, flex column */
.cts-date-month                     /* 0.55em, uppercase */
.cts-date-day                       /* 1.0em, bold */
.cts-date-weekday                   /* 0.5em, uppercase */

/* Content */
.cts-time                           /* 110px fixed width */
.cts-calendar-name                  /* Flex-shrink: 0, badge style */
.cts-title-block                    /* Flex: 1, column direction */
.cts-title                          /* 15px, font-weight: 600 */
.cts-event-description              /* 13px, opacity: 0.85 */
.cts-appointment-description        /* 13px, opacity: 0.85 */

/* Meta */
.cts-services                       /* 280px, white-space: normal (wrappable!) */
.cts-more                           /* +2 indicator */
.cts-list-location                  /* Flex-shrink: 0 */
.cts-location-info-icon             /* Tooltip icon */
.cts-list-tags                      /* Flex-wrap */
.cts-tag-badge                      /* Background dynamisch */

/* Empty State */
.cts-list-empty                     /* No events message */
.cts-empty-icon                     /* 📅 emoji */
```

**Unterstützte Optionen:**
- ✅ show_event_description
- ✅ show_appointment_description
- ✅ show_services (wrappable!)
- ✅ show_location (mit Tooltip)
- ✅ show_tags
- ✅ show_calendar_name
- ✅ show_time
- ✅ show_month_separator
- ✅ use_calendar_colors

**Key Features:**
- Datumsbox: **36x36px** (kompakt)
- Title-Block: **2-zeilig vertikal** (Titel über Beschreibung)
- Services: **Kann 2-3 Zeilen umbrechen** (white-space: normal)
- Layout: **Horizontal einreihig** (Flexbox)

---

### 3.2 Classic with Images

**Datei:** `templates/views/event-list/classic-with-images.php`  
**CSS-Quelle:** `churchtools-suite-public.css` (Zeilen 220-415)  
**Layout:** Flexbox horizontal mit Event-Bild  
**Status:** ⚠️ NICHT synchronisiert mit classic.php Optimierungen

**Unterschiede zu Classic:**
```html
<!-- zusätzliches Element nach Date Box -->
<div class="cts-event-image-thumb">
  <img src="..." alt="Event Image">
</div>

<!-- Title-Block verwendet noch spans statt divs -->
<div class="cts-title-block">
  <span class="cts-title">...</span>  <!-- ❌ sollte div sein -->
  <span class="cts-event-description">...</span>  <!-- ❌ sollte div sein -->
</div>
```

**CSS Classes (zusätzlich):**
```css
.cts-event-image-thumb              /* Thumbnail container */
.cts-location                       /* Statt .cts-list-location */
.cts-location-icon                  /* 📍 emoji */
.cts-tags                           /* Statt .cts-list-tags */
.cts-tag                            /* Statt .cts-tag-badge */
.cts-tag-more                       /* Statt separate class */
.cts-more-indicator                 /* Statt .cts-more */
```

**⚠️ INKONSISTENZEN:**
1. Title-Block verwendet `<span>` statt `<div>` (nicht 2-zeilig!)
2. Andere CSS-Klassennamen für gleiche Elemente
3. Datumsbox hat gleiche Optimierungen NICHT erhalten

---

### 3.3 Minimal View

**Datei:** `templates/views/event-list/minimal.php`  
**CSS-Quelle:** `churchtools-suite-public.css` (Zeilen 415-650)  
**Layout:** Ultra-kompakte einzeilige Liste für Sidebars  
**BEM Compliance:** ✅ 100%

**HTML-Struktur:**
```html
<div class="churchtools-suite-wrapper" data-style-mode="theme|plugin|custom">
  <div class="cts-list cts-list--minimal">
    
    <!-- Month Separator -->
    <div class="cts-month-separator">
      <time class="cts-month-separator__text">Februar 2026</time>
    </div>
    
    <!-- Event Item -->
    <article class="cts-list__item">
      
      <!-- Date (inline) -->
      <time class="cts-list__date">
        <span class="cts-list__date-day">17</span>
        <span class="cts-list__date-month">Feb</span>
      </time>
      
      <!-- Time (optional) -->
      <span class="cts-list__time">09:00</span>
      
      <!-- Title -->
      <h3 class="cts-list__title">Bibelkreis</h3>
      
      <!-- Description (optional, truncated 80 chars) -->
      <p class="cts-list__description">Bibeltext lesen...</p>
      
    </article>
  </div>
</div>
```

**CSS Classes (BEM):**
```css
/* Container */
.cts-list--minimal                  /* Block with Modifier */

/* Month Separator */
.cts-month-separator                /* Block */
.cts-month-separator__text          /* Element */

/* List Item */
.cts-list__item                     /* Element */

/* Date */
.cts-list__date                     /* Element */
.cts-list__date-day                 /* Sub-element */
.cts-list__date-month               /* Sub-element */

/* Content */
.cts-list__time                     /* Element */
.cts-list__title                    /* Element */
.cts-list__description              /* Element */

/* Empty State */
.cts-list__empty-state              /* Element */
```

**Unterstützte Optionen:**
- ✅ show_event_description (80 char truncate)
- ✅ show_appointment_description (80 char truncate)
- ✅ show_time
- ✅ show_month_separator
- ❌ show_calendar_name (not supported)
- ❌ show_location (not supported)
- ❌ show_services (not supported)
- ❌ show_tags (not supported)
- ❌ show_images (not supported)
- ❌ use_calendar_colors (not supported)

**Key Features:**
- Semantic HTML: `<article>`, `<time>`, `<h3>`
- BEM Naming: 100% konform
- Text Truncation: 80 Zeichen mit ... Suffix
- Minimal Width: Optimiert für Sidebars (320px+)

---

### 3.4 Classic Modern

**Datei:** `templates/views/event-list/classic-modern.php`  
**CSS-Quelle:** `churchtools-suite-list-modern.css`  
**Layout:** CSS Grid mit BEM Naming  
**BEM Compliance:** ✅ 95%

**HTML-Struktur:**
```html
<div class="churchtools-suite-wrapper" data-style-mode="theme|plugin|custom">
  <div class="cts-list cts-list--classic">
    
    <!-- Event Item using CSS Grid -->
    <article class="cts-list__item">
      
      <!-- Date Box (Grid area: date) -->
      <div class="cts-list__date">
        <span class="cts-list__date-month">FEB.</span>
        <span class="cts-list__date-day">17</span>
        <span class="cts-list__date-weekday">DI.</span>
      </div>
      
      <!-- Time (Grid area: time) -->
      <time class="cts-list__time">09:00 Uhr</time>
      
      <!-- Calendar (Grid area: calendar) -->
      <span class="cts-list__calendar">Jugendraum</span>
      
      <!-- Title (Grid area: title) -->
      <h3 class="cts-list__title">Bibelkreis</h3>
      
      <!-- Description (Grid area: description) -->
      <p class="cts-list__description">Bibeltext lesen...</p>
      
      <!-- Services (Grid area: services) -->
      <div class="cts-list__services">
        <span class="cts-list__service">Leitung: Max</span>
      </div>
      
      <!-- Location (Grid area: location) -->
      <address class="cts-list__location">Versammlungsraum</address>
      
      <!-- Tags (Grid area: tags) -->
      <div class="cts-list__tags">
        <span class="cts-list__tag" style="--tag-color: #6b7280">Jugend</span>
      </div>
      
    </article>
  </div>
</div>
```

**CSS Grid Template:**
```css
.cts-list__item {
  display: grid;
  grid-template-areas:
    "date time calendar title services location tags";
  grid-template-columns: 
    var(--cts-date-box-size)
    var(--cts-time-width)
    var(--cts-calendar-width)
    1fr
    var(--cts-services-width)
    var(--cts-location-width)
    auto;
  gap: var(--cts-list-gap);
  padding: var(--cts-list-padding);
}
```

**CSS Custom Properties:**
```css
.cts-list--classic {
  /* Spacing */
  --cts-list-spacing: clamp(0.5rem, 2vw, 1rem);
  --cts-list-gap: clamp(0.75rem, 2vw, 1.25rem);
  --cts-list-padding: clamp(0.75rem, 2vw, 1rem);
  
  /* Sizing */
  --cts-date-box-size: clamp(60px, 10vw, 80px);
  --cts-time-width: clamp(100px, 15vw, 140px);
  --cts-calendar-width: clamp(100px, 12vw, 120px);
  --cts-services-width: clamp(180px, 20vw, 280px);
  --cts-location-width: clamp(120px, 15vw, 180px);
  
  /* Typography */
  --cts-font-size-base: clamp(0.875rem, 1.5vw, 1rem);
  --cts-font-size-small: clamp(0.75rem, 1.2vw, 0.875rem);
  --cts-font-size-title: clamp(0.9375rem, 1.8vw, 1.0625rem);
  
  /* Colors */
  --cts-color-primary: var(--wp--preset--color--primary, #2563eb);
  --cts-color-text: var(--wp--preset--color--contrast, #1e293b);
  --cts-color-text-light: var(--wp--preset--color--secondary, #64748b);
  --cts-color-border: rgba(0, 0, 0, 0.08);
  --cts-color-hover: var(--wp--preset--color--tertiary, #f8fafc);
}
```

**CSS Classes (BEM):**
```css
/* Container */
.cts-list--classic                  /* Block with Modifier */

/* List Item */
.cts-list__item                     /* Element (Grid container) */

/* Grid Areas */
.cts-list__date                     /* Element (Grid area: date) */
.cts-list__date-month               /* Sub-element */
.cts-list__date-day                 /* Sub-element */
.cts-list__date-weekday             /* Sub-element */
.cts-list__time                     /* Element (Grid area: time) */
.cts-list__calendar                 /* Element (Grid area: calendar) */
.cts-list__title                    /* Element (Grid area: title) */
.cts-list__description              /* Element (Grid area: description) */
.cts-list__services                 /* Element (Grid area: services) */
.cts-list__service                  /* Sub-element */
.cts-list__location                 /* Element (Grid area: location) */
.cts-list__tags                     /* Element (Grid area: tags) */
.cts-list__tag                      /* Sub-element */
```

**Responsive Behavior:**
```css
/* Container Queries (Falls unterstützt) */
@container (max-width: 768px) {
  .cts-list__item {
    grid-template-areas:
      "date title"
      "date meta";
    grid-template-columns: var(--cts-date-box-size) 1fr;
  }
}

/* Media Query Fallback */
@media (max-width: 768px) {
  .cts-list__item {
    grid-template-areas:
      "date title"
      "date meta";
    grid-template-columns: var(--cts-date-box-size) 1fr;
  }
}
```

**Key Features:**
- CSS Grid Layout (modern)
- CSS Custom Properties (flexible sizing)
- Container Queries mit Fallback
- BEM Naming 95%
- Semantic HTML
- clamp() für responsive Sizing

---

### 3.5 Modern View

**Datei:** `templates/views/event-list/modern.php`  
**CSS-Quelle:** `churchtools-suite-public.css` (Zeilen 654-742)  
**Layout:** Card-basiert mit visueller Hierarchie  
**BEM Compliance:** ⚠️ 60%

**HTML-Struktur:**
```html
<div class="churchtools-suite-wrapper" data-style-mode="theme|plugin|custom">
  <div class="cts-list cts-list-modern">
    
    <!-- Event Card -->
    <div class="cts-event-modern">
      
      <!-- Header Section -->
      <div class="cts-event-header">
        
        <!-- Date -->
        <div class="cts-event-date">
          <span class="cts-date-day">17</span>
          <span class="cts-date-month">Feb</span>
        </div>
        
        <!-- Time -->
        <div class="cts-event-time">09:00 - 12:00 Uhr</div>
      </div>
      
      <!-- Body Section -->
      <div class="cts-event-body">
        <h3 class="cts-event-title">Bibelkreis</h3>
        <p class="cts-event-description">Bibeltext lesen...</p>
      </div>
      
      <!-- Meta Section -->
      <div class="cts-event-meta">
        <span class="cts-meta-calendar">Jugendraum</span>
        <span class="cts-meta-location">📍 Versammlungsraum</span>
        <span class="cts-meta-services">Leitung: Max</span>
      </div>
      
    </div>
  </div>
</div>
```

**CSS Classes:**
```css
/* Container */
.cts-list-modern                    /* ❌ Legacy (sollte .cts-list--modern) */

/* Event Card */
.cts-event-modern                   /* ❌ Legacy (sollte .cts-event--modern) */

/* Sections */
.cts-event-header                   /* ❌ Legacy (sollte .cts-event__header) */
.cts-event-body                     /* ❌ Legacy (sollte .cts-event__body) */
.cts-event-meta                     /* ❌ Legacy (sollte .cts-event__meta) */

/* Header Elements */
.cts-event-date                     /* ❌ Legacy */
.cts-date-day                       /* ✅ Reused from Classic */
.cts-date-month                     /* ✅ Reused from Classic */
.cts-event-time                     /* ❌ Legacy */

/* Body Elements */
.cts-event-title                    /* ❌ Legacy */
.cts-event-description              /* ✅ Reused from Classic */

/* Meta Elements */
.cts-meta-calendar                  /* ❌ Legacy */
.cts-meta-location                  /* ❌ Legacy */
.cts-meta-services                  /* ❌ Legacy */
```

**⚠️ BEM Refactoring Empfohlen:**
```css
/* Vorschlag für BEM-konformes Refactoring */
.cts-list--modern                   /* Block Modifier */
.cts-event--modern                  /* Block Modifier */
.cts-event__header                  /* Element */
.cts-event__body                    /* Element */
.cts-event__meta                    /* Element */
.cts-event__date                    /* Element */
.cts-event__time                    /* Element */
.cts-event__title                   /* Element */
```

---

## 4. CSS Loading Strategie

### 4.1 Aktuelle Implementierung

```php
// includes/class-churchtools-suite.php (Zeile 218-226)
$css_modern_path = CHURCHTOOLS_SUITE_PATH . 'assets/css/churchtools-suite-list-modern.css';
if ( file_exists( $css_modern_path ) ) {
	wp_enqueue_style(
		'churchtools-suite-list-modern',
		CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-list-modern.css',
		[ 'churchtools-suite-public' ],
		$this->version,
		'all'
	);
}
```

**Status:** ✅ Konditional geladen (wenn classic-modern.php verwendet wird)

### 4.2 CSS Hierarchie

```
1. churchtools-suite-public.css      (IMMER geladen, 2880 Zeilen)
   ├─ Classic View CSS
   ├─ Classic-with-Images CSS
   ├─ Minimal View CSS (teilweise)
   ├─ Modern View CSS
   ├─ Calendar View CSS
   ├─ Grid View CSS
   └─ Modal CSS

2. churchtools-suite-list-modern.css (KONDITIONAL, 1699 Zeilen)
   └─ Classic Modern View CSS (BEM)

3. churchtools-suite-single.css      (KONDITIONAL)
   └─ Single Event Page CSS
```

---

## 5. Handlungsempfehlungen

### 5.1 Priorität 1: SOFORT

1. **❌ LÖSCHEN:** `/assets/css/churchtools-suite-admin.css` (Duplikat)
2. **🔄 SYNCHRONISIEREN:** `classic-with-images.php` mit `classic.php` Optimierungen:
   - Title-Block von `<span>` zu `<div>` ändern
   - Datumsbox auf 36x36px
   - Services wrappable machen
   - CSS-Klassennamen vereinheitlichen

### 5.2 Priorität 2: KURZFRISTIG

3. **🏗️ BEM REFACTORING:** `modern.php` auf BEM umstellen:
   ```
   .cts-list-modern    → .cts-list--modern
   .cts-event-modern   → .cts-event--modern
   .cts-event-header   → .cts-event__header
   .cts-event-body     → .cts-event__body
   .cts-event-meta     → .cts-event__meta
   ```

4. **📝 DOKUMENTATION:** Template-Dokumentation vervollständigen
   - Welche View verwendet welche CSS-Datei
   - BEM vs. Legacy Klassennamen
   - Unterstützte Shortcode-Optionen pro View

### 5.3 Priorität 3: MITTELFRISTIG

5. **🔄 BEM MIGRATION:** Classic View auf BEM umstellen:
   - Erstelle `classic-v2.php` mit BEM Naming
   - Behalte `classic.php` als Legacy-Fallback
   - Migriere schrittweise existierende Shortcodes

6. **🧹 CSS KONSOLIDIERUNG:** 
   - Extrahiere gemeinsame Components (Date Box, Tags, etc.)
   - Erstelle Shared Utilities in separater CSS-Datei
   - Reduziere Code-Duplikation zwischen Views

### 5.4 Priorität 4: LANGFRISTIG

7. **📦 CSS MODULARISIERUNG:**
   ```
   /assets/css/
   ├─ churchtools-suite-public.css        (Main bundle)
   ├─ components/
   │  ├─ date-box.css
   │  ├─ tags.css
   │  ├─ calendar-badge.css
   │  └─ modals.css
   └─ views/
      ├─ list-classic.css
      ├─ list-minimal.css
      ├─ list-modern.css
      ├─ grid.css
      └─ calendar.css
   ```

8. **🎨 CSS VARIABLES STANDARDISIERUNG:**
   - Alle Views nutzen gleiche CSS Custom Properties
   - Theme-Integration verbessern
   - Dark Mode Support vorbereiten

---

## 6. BEM Naming Standard

### 6.1 Namenskonvention

**Block:**
```css
.cts-list          /* Haupt-Container */
.cts-event         /* Event-Container */
.cts-modal         /* Modal-Container */
```

**Element (Block__Element):**
```css
.cts-list__item           /* List Item */
.cts-event__title         /* Event Title */
.cts-event__description   /* Event Description */
.cts-modal__header        /* Modal Header */
```

**Modifier (Block--Modifier oder Block__Element--Modifier):**
```css
.cts-list--classic        /* Classic List Style */
.cts-list--minimal        /* Minimal List Style */
.cts-event--clickable     /* Clickable Event */
.cts-tag--primary         /* Primary Tag Style */
```

**Sub-Element (Block__Element-subelement):**
```css
.cts-date__day            /* Date Day */
.cts-date__month          /* Date Month */
.cts-location__icon       /* Location Icon */
```

### 6.2 Naming Pattern

```
.cts-{block}                          /* Block */
.cts-{block}--{modifier}              /* Block Modifier */
.cts-{block}__{element}               /* Element */
.cts-{block}__{element}--{modifier}   /* Element Modifier */
.cts-{block}__{element}-{subelement}  /* Sub-element (no __) */
```

### 6.3 Beispiele

**Korrekt:**
```html
<!-- Block with Modifier -->
<div class="cts-list cts-list--classic">
  
  <!-- Element -->
  <article class="cts-list__item">
    
    <!-- Element with Sub-elements -->
    <div class="cts-list__date">
      <span class="cts-list__date-day">17</span>
      <span class="cts-list__date-month">Feb</span>
    </div>
    
    <!-- Element with Modifier -->
    <h3 class="cts-list__title cts-list__title--highlighted">Event</h3>
  </article>
</div>
```

**Falsch (Legacy):**
```html
<!-- ❌ Mischung aus Hyphen ohne BEM-Logik -->
<div class="cts-list-classic">
  <div class="cts-event-classic">
    <div class="cts-date-box">
      <div class="cts-date-day">17</div>
    </div>
  </div>
</div>
```

---

## 7. Zusammenfassung

### 7.1 Aktueller Stand

| Metrik | Wert | Status |
|--------|------|--------|
| **Haupt-CSS-Dateien** | 3 | ✅ Gut |
| **Duplikate gefunden** | 1 | ❌ admin CSS doppelt |
| **BEM Compliance** | 52% | ⚠️ Gemischt |
| **Views mit BEM** | 2/5 | ⚠️ Ausbaufähig |
| **Inline Styles** | 20 | ✅ Alle gerechtfertigt |
| **Code Duplikation** | Hoch | ⚠️ Konsolidierung nötig |

### 7.2 Ziel-Zustand

| Metrik | Ziel | Strategie |
|--------|------|-----------|
| **BEM Compliance** | 100% | Schrittweise Migration |
| **CSS Modularität** | Hoch | Component-basiert |
| **Code Duplikation** | Niedrig | Shared Components |
| **Dokumentation** | Vollständig | Developer Guide |
| **Performance** | Optimiert | Conditional Loading |

---

**Nächster Schritt:** Priorität 1 und 2 abarbeiten (Duplikat löschen, classic-with-images.php synchronisieren, modern.php BEM refactoring)
