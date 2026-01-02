# Release Notes: ChurchTools Suite v0.10.1.0

**Release Date:** 2. Januar 2026  
**Type:** Feature Release  
**Status:** Production Ready

---

## 🎯 Hauptfeature: Alle Templates in Page Buildern verfügbar

### Was ist neu?

Mit **v0.10.1.0** sind nun **alle 27 vorhandenen Templates** in **Gutenberg** und **Elementor** auswählbar!

Bisher konnten Nutzer nur 3 View-Typen (Liste, Kalender, Raster) in den Page Buildern verwenden. Jetzt stehen **13 View-Typen** mit **allen Varianten** zur Verfügung.

---

## ✨ Neue Features

### 📦 Gutenberg Block (13 View-Typen)

Alle View-Typen sind jetzt im Block-Editor verfügbar:

- **📋 Liste** (6 Varianten: classic, classic-services, modern, medium, fluent, compact)
- **📅 Kalender** (1 Variante: monthly-modern)
- **▦ Raster** (3 Varianten: simple, colorful, modern)
- **🔍 Suche** (1 Variante: classic)
- **📱 Widget** (1 Variante: upcoming)
- **🎬 Slider** (1 Variante: classic)
- **🧱 Masonry** (1 Variante: classic)
- **📒 Agenda** (1 Variante: classic)
- **🏢 Timetable** (1 Variante: classic)
- **🎠 Carousel** (1 Variante: classic)
- **⏱️ Countdown** (1 Variante: classic)
- **🏞️ Cover** (1 Variante: classic)
- **🗺️ Karte** (1 Variante: classic)

**Gesamt:** 27 Templates verfügbar

### 🎨 Elementor Widget (13 View-Typen)

Alle View-Typen + Varianten sind im Elementor Widget auswählbar:

- **Liste:** classic, classic-services, modern, medium, fluent, compact
- **Kalender:** monthly-modern
- **Raster:** simple, colorful, modern
- **Alle anderen:** je 1 classic-Variante

### 🎯 Icons & UX-Verbesserungen

- Jeder View-Typ hat ein passendes Icon (📋, 📅, ▦, 🔍, etc.)
- Default-Views für jeden Typ definiert
- Conditional Controls (nur relevante Optionen werden angezeigt)

---

## 🔧 Technische Änderungen

### Geänderte Dateien

**`assets/js/churchtools-suite-blocks.js`**
- `standardViewOptions` erweitert (3 → 13 View-Typen)
- Icons für alle View-Typen hinzugefügt
- Default-Views definiert

**`includes/class-churchtools-suite-elementor-widget.php`**
- 10 neue View-Controls hinzugefügt (search, widget, slider, masonry, agenda, timetable, carousel, countdown, cover, map)
- Switch-Statement für alle Shortcode-Handler erweitert
- Alle View-Varianten in Dropdowns verfügbar

**`churchtools-suite.php`**
- Version: 0.10.0.0 → 0.10.1.0

---

## 🚀 Upgrade-Anleitung

### Voraussetzungen

- WordPress 6.0+
- PHP 8.0+
- ChurchTools Suite v0.10.0.0 oder höher

### Installation

**Option 1: Automatisches Update (wenn Auto-Update aktiv)**
- Plugin wird automatisch aktualisiert

**Option 2: Manuelles Update**
1. Backup erstellen
2. Altes Plugin deaktivieren (NICHT löschen)
3. Neues ZIP hochladen und aktivieren
4. Einstellungen prüfen

**Option 3: Git Pull (für Server-Installation)**
```bash
cd /var/www/.../wp-content/plugins/churchtools-suite
git pull origin main
```

---

## 📚 Nutzung

### Gutenberg Block

1. **Block hinzufügen:** `ChurchTools Events`
2. **Ansichtstyp wählen:** Liste, Kalender, Raster, Suche, Widget, Slider, etc.
3. **Variante wählen:** Gewünschte Template-Variante
4. **Konfigurieren:** Kalender, Limit, Anzeigeoptionen
5. **Veröffentlichen:** Block ist sofort live

### Elementor Widget

1. **Widget suchen:** `ChurchTools Events`
2. **Ansichtstyp wählen:** Dropdown mit allen 13 Typen
3. **Variante wählen:** Entsprechende Dropdown-Auswahl
4. **Anpassen:** Alle Parameter verfügbar
5. **Veröffentlichen:** Widget ist sofort live

---

## 🐛 Bekannte Probleme

Keine bekannten Probleme in diesem Release.

---

## 📈 Statistik

- **Templates gesamt:** 27
- **View-Typen:** 13
- **Gutenberg:** ✅ Alle verfügbar
- **Elementor:** ✅ Alle verfügbar
- **Shortcodes:** ✅ Alle funktional

---

## 🔗 Links

- **GitHub:** https://github.com/FEGAschaffenburg/churchtools-suite
- **Dokumentation:** [ROADMAP.md](ROADMAP.md)
- **Issues:** https://github.com/FEGAschaffenburg/churchtools-suite/issues

---

## 🙏 Credits

Entwickelt von **FEG Aschaffenburg**  
ChurchTools API Integration  
WordPress Plugin Development

---

**Vollständiges Changelog:** [CHANGELOG.md](docs/CHANGELOG.md)
