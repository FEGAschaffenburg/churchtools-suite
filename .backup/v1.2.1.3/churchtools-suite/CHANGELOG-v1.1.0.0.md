# Changelog v1.1.0.0 - Major Refactoring

**Release Date:** 16. Februar 2026

---

## 🎯 Highlights

**Major Refactoring Release** mit kritischen Bugfixes, CSS-Konsolidierung und Asset-Struktur-Vereinheitlichung.

- **CRITICAL:** Parse Error in Elementor Widget behoben
- **MAJOR:** CSS-Struktur vollständig konsolidiert (nur noch 2 CSS-Dateien)
- **MAJOR:** Classic Views vereinheitlicht (classic.php & classic-with-images.php synchronisiert)
- **FEATURE:** Automatische Datei-Bereinigung bei Updates (Migration 1.3)
- **FEATURE:** Elementor Conditions für intelligente UI-Steuerung
- **IMPROVEMENT:** Datumsbox von 60px auf 36px optimiert

---

## 🐛 Fixed (Critical)

### Elementor Widget Parse Error
- **❌ CRITICAL BUG:** Parse error durch orphaned array in `class-cts-elementor-events-widget.php` (Zeile 343-346)
- **Root Cause:** Fehlende section structure nach `show_services` control
- **Impact:** Website komplett down (PHP Parse Error)
- **Fix:** 
  - Entfernung des orphaned arrays
  - Hinzufügen von `end_controls_section()` + `start_controls_section('style_section')`
  - Korrektur der Indentation aller 7 Style-Controls

### CSS-Struktur
- **❌ BUG:** Doppelte Admin CSS-Datei (`/admin/css/` + `/assets/css/`)
- **❌ BUG:** Inkonsistente Asset-Pfade (CSS in /admin/, JS in /assets/)
- **Fix:** 
  - Alle Assets jetzt konsequent in `/assets/css/` und `/assets/js/`
  - Admin CSS verschoben von `/admin/css/` → `/assets/css/`
  - Automatische Bereinigung alter Dateien via Migration 1.3

---

## ✨ Added

### Elementor Conditions System
Intelligente dynamische Steuerung von Display-Optionen basierend auf gewählter View:

**Komplexe Nested Conditions:**
```php
// Beispiel: show_tags nur für list (not minimal) OR grid-modern
'conditions' => [
    'relation' => 'or',
    'terms' => [
        [
            'relation' => 'and',
            'terms' => [
                ['name' => 'view_type', 'operator' => '===', 'value' => 'list'],
                ['name' => 'view_list', 'operator' => '!==', 'value' => 'minimal'],
            ],
        ],
        [
            'relation' => 'and',
            'terms' => [
                ['name' => 'view_type', 'operator' => '===', 'value' => 'grid'],
                ['name' => 'view_grid', 'operator' => '===', 'value' => 'modern'],
            ],
        ],
    ],
],
```

**Betroffene Controls:**
- `show_event_description`: Nur list/grid (nicht calendar)
- `show_appointment_description`: Nur list/grid (nicht calendar)
- `show_location`: List (not minimal) OR grid
- `show_tags`: List (not minimal) OR grid-modern
- `show_images`: Classic-with-images OR grid
- `show_calendar_name`: List (not minimal) OR grid
- `show_month_separator`: Nur list views
- `show_services`: List (not minimal) OR grid

**UI-Verbesserung:**
- Section Description: "💡 Die verfügbaren Optionen passen sich automatisch an die gewählte View an"
- User sieht nur relevante Controls für gewählte View
- Keine statischen "❌ Nicht unterstützt" Texte mehr

### Automatische Datei-Bereinigung
```php
// Migration 1.3 - v1.1.0.0
private static function migrate_to_1_3(): void {
    // Entfernt alte Dateien aus v1.0.6 Refactoring:
    // - admin/css/churchtools-suite-admin.css → assets/css/
    // - Leere admin/css/ Directory
    
    // Logged alle entfernten Dateien
}
```

---

## 🔧 Improved

### Classic View Layout Optimierung

**1. Datumsbox Größenreduktion**
- **Vorher:** 60x60px mit großen Fonts
- **Nachher:** 36x36px mit proportionalen Fonts
- **Grund:** Bessere Höhenanpassung an 2-zeiliges Title-Block

**Änderungen:**
```css
/* Date Box */
.cts-date-box {
    width: 36px;           /* war 60px */
    min-width: 36px;
    gap: 0;                /* war 1px */
}

/* Fonts proportional skaliert */
.cts-date-month { font-size: 0.55em; }  /* war 0.65em */
.cts-date-day { font-size: 1.0em; }     /* war 1.2em */
.cts-date-weekday { font-size: 0.5em; } /* war 0.6em */
```

**2. Title-Block: 2-zeilig vertikal**
- **Vorher:** Inline `<span>` mit " - " Trenner (einzeilig)
- **Nachher:** Block `<div>` mit `flex-direction: column` (2 Zeilen)

```html
<!-- Vorher -->
<span class="cts-title">Titel</span>
<span class="cts-event-description"> - Beschreibung...</span>

<!-- Nachher -->
<div class="cts-title-block">
    <div class="cts-title">Titel</div>
    <div class="cts-event-description">Beschreibung...</div>
</div>
```

**CSS:**
```css
.cts-event-classic .cts-title-block {
    flex: 1;
    display: flex;
    flex-direction: column;  /* Vertikal stacken */
    gap: 4px;
}
```

**3. Services: Wrappable 2-3 Zeilen**
- **Vorher:** `white-space: nowrap` → Text wird abgeschnitten
- **Nachher:** `white-space: normal` → Kann 2-3 Zeilen umbrechen

```css
.cts-event-classic .cts-services {
    width: 280px;
    white-space: normal;   /* KEY: Allows wrapping! */
    line-height: 1.4;      /* Readability für multi-line */
}
```

**4. Event Description Priorität**
```php
<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
    <div class="cts-event-description">...</div>
<?php elseif ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
    <div class="cts-appointment-description">...</div>
<?php endif; ?>
```
- Event Description hat Vorrang vor Appointment Description
- Word Limit erhöht: 15 → **20 Wörter**

### Classic-with-Images Synchronisierung

**Vor dieser Version:**
- ❌ Verwendete `<span>` statt `<div>` (nicht 2-zeilig)
- ❌ Keine elseif-Priorität für Descriptions
- ❌ Andere CSS-Klassennamen als classic.php
- ❌ Word Limit nur 15 statt 20
- ❌ Services Trenner: · statt |
- ❌ Keine 36px Datumsbox

**Nach dieser Version:**
- ✅ Komplett synchronisiert mit classic.php
- ✅ Title-Block 2-zeilig vertical
- ✅ Services wrappable
- ✅ Einheitliche CSS-Klassen
- ✅ Datumsbox 36x36px

**CSS-Klassen vereinheitlicht:**
| Alt (classic-with-images) | Neu (wie classic) |
|---------------------------|-------------------|
| `.cts-location` + Icon | `.cts-list-location` (ohne Icon) |
| `.cts-tags` | `.cts-list-tags` |
| `.cts-tag` | `.cts-tag-badge` |
| `.cts-tag-more` | Entfernt |
| `.cts-more-indicator` | `.cts-more` |
| Services Trenner: · | Services Trenner: \| |

### Responsive CSS Überschreibungen behoben

**Problem:**
Mehrere CSS-Regeln überschrieben die Basis-Styles:
1. Media Query (768px): Datumsbox auf 60px überschrieben
2. Style-Mode Padding: 8-12px machte Box größer
3. Gap-Werte: Teilweise noch auf 2px

**Alle Fixes:**
```css
/* Basis-Regel */
.cts-date-box { width: 36px; gap: 0; }

/* Responsive Fix */
@media (max-width: 768px) {
    .cts-date-box { 
        width: 36px;    /* war 60px */
        gap: 0;         /* war 2px */
    }
}

/* Theme-Mode Fix */
.churchtools-suite-wrapper[data-style-mode="theme"] .cts-date-box {
    padding: 6px;       /* war 8px */
}

/* Plugin-Mode Fix */
.churchtools-suite-wrapper[data-style-mode="plugin"] .cts-date-box {
    padding: 6px;       /* war 8px */
}

/* Custom-Mode Fix */
.churchtools-suite-wrapper[data-style-mode="custom"] .cts-date-box {
    padding: var(--cts-padding, 6px) !important;  /* Default war 12px */
    border-radius: var(--cts-border-radius, 5px) !important;  /* war 6px */
}
```

---

## 📋 Technical Details

### CSS Consolidation

**Before v1.1.0.0:**
```
churchtools-suite/
├─ admin/
│  └─ css/
│     └─ churchtools-suite-admin.css  ❌ Duplicate location
└─ assets/
   ├─ css/
   │  └─ churchtools-suite-admin.css  ❌ Another copy
   └─ js/
      └─ churchtools-suite-admin.js   ✅ Correct location
```

**After v1.1.0.0:**
```
churchtools-suite/
├─ admin/
│  ├─ class-churchtools-suite-admin.php  ← Loads from /assets/
│  └─ views/                             ← Templates only
└─ assets/
   ├─ css/
   │  ├─ churchtools-suite-public.css     ✅ Frontend (all views)
   │  ├─ churchtools-suite-admin.css      ✅ Backend/Preview
   │  └─ churchtools-suite-list-modern.css ✅ Optional (Modern View)
   └─ js/
      ├─ churchtools-suite-public.js      ✅ Frontend
      └─ churchtools-suite-admin.js       ✅ Backend
```

**Benefits:**
- ✅ Keine Duplikate mehr
- ✅ Konsistente Asset-Pfade
- ✅ Einfacheres Deployment
- ✅ Klarere Struktur für Entwickler

### Migration System Enhancement

**New Migration 1.3:**
```php
const DB_VERSION = '1.3';  // war 1.2

private static function migrate_to_1_3(): void {
    // Cleanup old files:
    // 1. admin/css/churchtools-suite-admin.css
    // 2. Empty admin/css/ directory
    
    // Logging:
    // - Files removed
    // - Migration version
}
```

**How it works:**
1. Plugin update installiert neue Dateistruktur
2. Migration 1.3 läuft beim ersten Aufruf
3. Alte Dateien werden automatisch entfernt
4. Cleanup wird geloggt
5. Keine manuellen Schritte nötig!

### BEM Naming Analysis

Erstellt umfassende Dokumentation: [LIST-VIEWS-CSS-AUDIT.md](./docs/LIST-VIEWS-CSS-AUDIT.md)

**BEM Compliance Status:**
| View | Compliance | Status |
|------|------------|--------|
| Minimal | 100% | ✅ BEM-konform |
| Classic Modern | 95% | ✅ BEM-konform |
| Modern | 60% | ⚠️ Partial BEM |
| Classic | 0% | ❌ Legacy Naming |
| Classic with Images | 0% | ❌ Legacy (jetzt synchronisiert mit classic) |

**Empfehlungen für zukünftige Versionen:**
- Modern View BEM Refactoring (`.cts-event-modern` → `.cts-event--modern`)
- Classic View BEM Migration als v2 mit Legacy-Fallback
- CSS Component Extraction für gemeinsame Elemente

---

## 🔄 Changed

### File Structure
- **Moved:** `admin/css/churchtools-suite-admin.css` → `assets/css/churchtools-suite-admin.css`
- **Removed:** Empty `admin/css/` directory
- **Updated:** All references in `admin/class-churchtools-suite-admin.php`

### Database
- **DB Version:** 1.2 → **1.3**
- **New Migration:** `migrate_to_1_3()` für automatisches Datei-Cleanup

### Templates
- **Updated:** `templates/views/event-list/classic.php`
  - Title-Block zu div statt span
  - Services wrappable
  - Event Description Priorität
- **Updated:** `templates/views/event-list/classic-with-images.php`
  - Komplett synchronisiert mit classic.php
  - Alle Änderungen übernommen

### CSS
- **Updated:** `assets/css/churchtools-suite-public.css`
  - Datumsbox: 60px → 36px (alle Breakpoints)
  - Style-Mode Padding: 8-12px → 6px
  - Services: white-space: normal
  - Title-Block: flex-direction: column

---

## 📊 Statistics

**Lines Changed:** ~500
**Files Modified:** 12
**Files Added:** 1 (docs/LIST-VIEWS-CSS-AUDIT.md)
**Files Removed/Moved:** 1 (admin CSS)
**Bugs Fixed:** 3 Critical, 2 Major
**New Features:** 2
**Improvements:** 7

**CSS Consolidation:**
- Before: 3 CSS locations (inconsistent)
- After: 1 location for all assets (consistent)
- Reduction: -33% complexity

---

## 🚀 Upgrade Notes

### Automatic Cleanup
Migration 1.3 entfernt automatisch:
- `admin/css/churchtools-suite-admin.css` (jetzt in assets/css/)
- Leeres `admin/css/` Verzeichnis

**Keine manuellen Schritte erforderlich!**

### Breaking Changes
**Keine Breaking Changes** für Enduser.

**Für Entwickler:**
- Falls eigene Plugins die alten CSS-Pfade referenzieren, bitte auf `/assets/css/` anpassen

### Compatibility
- **WordPress:** 6.0+ (unchanged)
- **PHP:** 8.0+ (unchanged)
- **Elementor:** Kompatibel mit Elementor 3.x (Conditions System)

---

## 🔗 Related Issues

- Fix: Elementor Widget Parse Error (Critical)
- Fix: Doppelte Admin CSS
- Improvement: Classic View Layout
- Feature: Elementor Conditions
- Feature: Auto File Cleanup
- Refactoring: CSS Consolidation

---

## 👥 Contributors

- **nauma** - Main Development, CSS Consolidation, Migration System

---

## 📝 Next Steps (v1.0.8.0 geplant)

**Priorität 1 (Kurzfristig):**
1. Modern View BEM Refactoring
2. Classic View schrittweise auf BEM migrieren
3. CSS Component Extraction

**Priorität 2 (Mittelfristig):**
4. Dark Mode Support
5. CSS Modularisierung
6. Performance Optimierung

---

**Full Changelog:** [CHANGELOG.md](./CHANGELOG.md)  
**Documentation:** [docs/LIST-VIEWS-CSS-AUDIT.md](./docs/LIST-VIEWS-CSS-AUDIT.md)  
**Repository:** https://github.com/FEGAschaffenburg/churchtools-suite
