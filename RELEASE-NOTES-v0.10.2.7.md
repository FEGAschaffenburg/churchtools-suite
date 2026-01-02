# Release Notes v0.10.2.7

**Release-Datum:** 22. Januar 2025  
**Release-Typ:** Bugfix & UX-Verbesserung  
**Migrationsbedarf:** Nein  
**Breaking Changes:** Nein

---

## 🎯 Zusammenfassung

UX-Verbesserungen für Block-Editoren: Entfernung veralteter View-Optionen und verbesserte Namensgebung für klarere Benutzerführung.

---

## ✨ Neue Features

Keine neuen Features in diesem Release.

---

## 🔧 Verbesserungen

### Block-Editoren: Aufgeräumte View-Optionen
**Betrifft:** Elementor Widget, Gutenberg Block  
**Problem:** Nutzer konnten View-Varianten auswählen, die nicht (mehr) existieren oder nicht implementiert sind.

**Gelöst:**
- **Entfernt:** `classic-services` View (ersetzt durch `show_services` Parameter in classic View)
- **Entfernt:** Nicht-implementierte List Views (`modern`, `fluent`, `compact`)
- **Entfernt:** Nicht-implementierte Grid Views (`colorful`, `modern`)

**Verbleibende Views (implementiert):**
- ✅ List: `classic`, `medium`
- ✅ Calendar: `monthly-modern`
- ✅ Grid: `simple`
- ✅ Search: `classic`
- ✅ Widget: `upcoming`

---

### Block-Editoren: Klarere Namensgebung
**Betrifft:** Elementor Widget  
**Problem:** `view_mode` Parameter war unklar ("Ansichts-Modus" klingt nach Layout, nicht Quelle).

**Gelöst:**
- **Umbenannt:** `view_mode` → `preset_source`
- **Neue Labels:**
  - Alt: "⚙️ Standard-Views (anpassbar)" / "⭐ Eigene Presets (über Manager)"
  - **Neu:** "Standard-Ansichten" / "Eigene Presets"
- **Neue Beschreibung:** "Standard-Ansichten können hier angepasst werden. Eigene Presets werden über den Shortcode-Manager erstellt."

**Vorteil:** Nutzer verstehen sofort, dass sie zwischen Standard-Templates und selbst erstellten Presets wählen.

---

### Backward Compatibility
**Elementor Widget:** Unterstützt weiterhin alte `view_mode` Settings (Fallback zu `preset_source`).

```php
// Fallback für alte Widgets ohne preset_source (vor v0.10.2.7)
$preset_source = ! empty( $settings['preset_source'] ) ? $settings['preset_source'] : (
	! empty( $settings['view_mode'] ) ? $settings['view_mode'] : 'standard'
);
```

Bestehende Widgets bleiben funktional ohne Neueinrichtung.

---

## 🐛 Bugfixes

### Elementor Widget: Konsistente Conditional Display
**Problem:** Einige Conditions nutzten noch `view_mode`, andere schon `preset_source`.  
**Gelöst:** Alle Conditions verwenden jetzt einheitlich `preset_source`.

**Betroffene Controls:**
- `list_preset`, `calendar_preset`, `grid_preset` (Preset-Auswahl)
- `list_view`, `calendar_view`, `grid_view`, `search_view`, `widget_view` (Standard-Auswahl)
- `basic_section`, `layout_section` (Settings-Sections)
- `preset_notice` (Hinweis-Text)

---

## 🗂️ Technische Details

### Geänderte Dateien
1. **templates/list/classic-services.php** (gelöscht)
   - Obsolete View-Variante entfernt
   - Funktionalität via `show_services` Parameter erreichbar

2. **includes/class-churchtools-suite-elementor-widget.php**
   - Zeilen 145-152: Reduziert auf `classic` + `medium`
   - Zeilen 263-265: Reduziert auf `simple` (Grid)
   - Zeile 116: `view_mode` → `preset_source`
   - Zeile 127: Kommentar aktualisiert
   - Zeilen 189, 220, 247, 278, 305, 334, 362, 412, 428, 459, 543, 603: Conditions aktualisiert

3. **assets/js/churchtools-suite-blocks.js**
   - Zeilen 30-37: List Views reduziert auf `classic` + `medium`
   - Zeilen 43-46: Grid Views reduziert auf `simple`

---

## 📊 Auswirkungen

### Keine Breaking Changes
- Bestehende Elementor Widgets funktionieren weiter (Fallback)
- Gutenberg Blocks: View-Auswahl zeigt nur noch gültige Optionen
- Template-Dateien: Keine Änderungen an bestehenden Views

### Migrationspfad
Nutzer mit `classic-services` View in bestehenden Widgets:
1. Widget öffnen
2. View auf `classic` ändern
3. Parameter `show_services="true"` im Shortcode-Manager hinzufügen

---

## 🔄 Upgrade-Hinweise

### Sofort-Update möglich
- ✅ Keine Datenbank-Migration nötig
- ✅ Keine Template-Anpassungen nötig
- ✅ Keine Shortcode-Änderungen nötig
- ✅ Backward compatible

### Empfohlen für
- Alle Nutzer mit Elementor Integration
- Alle Nutzer mit Gutenberg Blocks
- Alle Nutzer mit veralteten View-Referenzen

---

## 📖 Weiterführende Dokumentation

- [Shortcode-Manager Dokumentation](docs/shortcode-manager.md)
- [Template-Übersicht](TEMPLATES-OVERVIEW.md)
- [Block-Editor Integration](docs/blocks.md)

---

**Nächstes Milestone:** v1.0.0 - Production Ready
