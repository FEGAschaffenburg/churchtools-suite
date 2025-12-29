# Assets-Struktur - ChurchTools Suite

## Version 0.6.0.0 - Zentrale Asset-Verwaltung

### Neue Ordnerstruktur

Alle CSS- und JavaScript-Dateien liegen jetzt zentral unter `assets/`:

```
churchtools-suite/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin-Bereich Styles
│   │   └── public.css         # Frontend Styles (alle Templates)
│   ├── js/
│   │   ├── admin.js           # Admin-Bereich Scripts
│   │   ├── blocks.js          # Gutenberg Block Editor
│   │   └── public.js          # Frontend Scripts
│   └── images/
│       └── [Plugin-Icons und Bilder]
├── admin/
│   └── views/                 # Nur noch PHP-Views
├── templates/
│   └── list/, grid/, etc.     # Nur noch PHP-Templates (kein CSS)
└── includes/
    └── [PHP-Klassen]
```

### Vorteile der neuen Struktur

#### 1. **Zentrale Verwaltung**
- Alle Assets an einem Ort
- Einfacheres Build-Management
- Klare Trennung: PHP in `includes/`, Assets in `assets/`

#### 2. **Performance**
- CSS/JS werden zentral geladen (nicht inline in Templates)
- Browser-Caching möglich
- Minifizierung vorbereitet
- Reduzierte Dateigröße der Templates

#### 3. **Wartbarkeit**
- Ein CSS für alle List-Views
- Styles nur einmal definieren
- Keine CSS-Duplikate mehr
- Einfachere Updates

#### 4. **Skalierbarkeit**
- Vorbereitet für Sass/SCSS-Preprocessing
- Build-Tools-Integration möglich (z.B. Webpack, Gulp)
- Separate Production/Development Builds

### Asset-Loading

#### Admin-Assets
**Geladen in:** `admin/class-churchtools-suite-admin.php`

```php
// CSS
wp_enqueue_style(
    'churchtools-suite-admin',
    CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-admin.css',
    [],
    $this->version
);

// JavaScript
wp_enqueue_script(
    'churchtools-suite-admin',
    CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-admin.js',
    [ 'jquery' ],
    $this->version,
    true
);
```

#### Public-Assets (Frontend)
**Geladen in:** `includes/class-churchtools-suite.php`

```php
// CSS
wp_enqueue_style(
    'churchtools-suite-public',
    CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-public.css',
    [],
    $this->version,
    'all'
);

// JavaScript
wp_enqueue_script(
    'churchtools-suite-public',
    CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-public.js',
    [ 'jquery' ],
    $this->version,
    true
);
```

**Conditional Loading:**
- Assets werden nur geladen, wenn ChurchTools-Shortcodes oder Blocks verwendet werden
- Prüfung via `has_shortcode()` und `has_block()`

#### Gutenberg Blocks
**Geladen in:** `includes/class-churchtools-suite-blocks.php`

```php
wp_enqueue_script(
    'churchtools-suite-blocks',
    CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-blocks.js',
    [ 'wp-blocks', 'wp-element', 'wp-block-editor', ... ],
    CHURCHTOOLS_SUITE_VERSION,
    false
);
```

### CSS-Struktur

#### `assets/css/admin.css`
- Admin-Tabs Styling
- Formulare und Buttons
- Tabellen (Calendars, Events, Services)
- Dashboard Widgets
- Responsive Admin-Layout

#### `assets/css/public.css`
- **List Views:** Classic, Medium, Modern
- **Grid Views:** Simple, Modern, Colorful
- **Calendar Views:** Monthly, Weekly, Daily
- **Empty States:** Gemeinsam für alle Views
- **Responsive Breakpoints:** Mobile, Tablet, Desktop
- **Theme Integration:** CSS Custom Properties

**Struktur:**
```css
/* Base Styles */
.churchtools-suite-wrapper { ... }

/* List Classic */
.cts-list-classic { ... }
.cts-event-classic { ... }

/* List Medium */
.cts-list-medium { ... }
.cts-event-medium { ... }

/* List Modern */
.cts-list-modern { ... }
.cts-event-modern { ... }

/* Empty States (gemeinsam) */
.cts-list-empty { ... }

/* Responsive */
@media (max-width: 768px) { ... }
```

### JavaScript-Struktur

#### `assets/js/admin.js`
- AJAX-Handlers für Sync-Operationen
- Calendar/Service Selection
- Connection Test
- Tab-Navigation
- Debug-Tools

#### `assets/js/public.js`
- Calendar-Navigation
- Event Detail Modals
- Grid View Interactions
- Filter/Search Functionality
- AJAX Event Loading

#### `assets/js/blocks.js`
- Gutenberg Block Registration
- Block Editor Controls
- Preview Rendering
- Server-Side Rendering Integration

### Migration von v0.5.x → v0.6.0

**Gelöscht:**
- ❌ `admin/css/` (verschoben nach `assets/css/`)
- ❌ `admin/js/` (verschoben nach `assets/js/`)
- ❌ `public/css/` (verschoben nach `assets/css/`)
- ❌ `public/js/` (verschoben nach `assets/js/`)

**Neue Dateien:**
- ✅ `assets/css/admin.css`
- ✅ `assets/css/public.css`
- ✅ `assets/js/admin.js`
- ✅ `assets/js/public.js`
- ✅ `assets/js/blocks.js`

**Templates bereinigt:**
- Alle `<style>`-Blöcke entfernt
- Nur noch HTML-Struktur
- CSS zentral in `public.css`

### Entwickler-Hinweise

#### CSS-Änderungen
Alle Styles für Frontend-Views bearbeiten in:
```
assets/css/public.css
```

Admin-Styles bearbeiten in:
```
assets/css/admin.css
```

#### JavaScript-Änderungen
Frontend-Interaktionen:
```
assets/js/public.js
```

Admin-Funktionen:
```
assets/js/admin.js
```

Gutenberg Blocks:
```
assets/js/blocks.js
```

#### Template-Entwicklung
Templates enthalten **nur noch PHP/HTML**:
```php
<?php /* Keine <style> Tags mehr! */ ?>
<div class="cts-event-classic">
    <!-- HTML-Struktur -->
</div>
```

Styles werden automatisch aus `public.css` geladen.

### Zukünftige Erweiterungen

#### Geplante Features (v0.7.0+)
- [ ] Sass/SCSS Preprocessing
- [ ] CSS/JS Minifizierung
- [ ] Separate Production Builds
- [ ] Source Maps für Development
- [ ] Webpack/Gulp Integration
- [ ] Critical CSS Extraction
- [ ] Lazy-Loading für große Views

#### Build-System (Future)
```bash
# Development
npm run dev

# Production Build
npm run build

# Watch Mode
npm run watch
```

### Changelog

**v0.6.0.0** (17. Dezember 2025)
- ✅ Assets zentral unter `assets/css/` und `assets/js/`
- ✅ Templates bereinigt (kein Inline-CSS)
- ✅ Alle Enqueue-Pfade aktualisiert
- ✅ `public/` Ordner entfernt
- ✅ Verbesserte Wartbarkeit

**v0.5.13.0**
- CSS aus Templates extrahiert
- Inline-CSS in `public.css` zusammengeführt

**v0.5.1.0**
- Initial: `public/css/` und `public/js/` Struktur

---

**Stand:** Version 0.6.0.0 (17. Dezember 2025)
