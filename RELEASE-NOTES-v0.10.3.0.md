# Release Notes v0.10.3.0

**Datum:** 2. Januar 2026  
**Typ:** Feature Release  
**Breaking Changes:** Keine

---

## 🎯 **Highlights**

### Click-to-Details Konfiguration
- **Elementor Widget:** Neue Toggle-Option "👆 Click-to-Details" in Basis-Einstellungen
- **Gutenberg Block:** Neue Toggle-Option "👆 Click-to-Details" in Basis-Einstellungen
- **Calendar Template:** Monthly-Modern unterstützt jetzt Click-to-Details (Modal beim Klick auf Events)

### Vollständige View-Optionen
- **List Views:** Alle 5 implementierten Views jetzt verfügbar (classic, medium, compact, fluent, modern)
- **Grid Views:** Alle 3 implementierten Views jetzt verfügbar (simple, modern, colorful)
- Vorher fehlten 5 implementierte Templates in den Auswahloptionen

---

## ✨ **Neue Features**

### 1. **Konfigurierbare Click-to-Details Funktionalität**

**Elementor Widget:**
```
Basis-Einstellungen → 👆 Click-to-Details
- Toggle: AN/AUS
- Default: AN
- Beschreibung: "Öffnet Event-Details in einem Modal beim Klick auf einen Termin"
```

**Gutenberg Block:**
```
Panel: Basis-Einstellungen → 👆 Click-to-Details
- Toggle Control mit gleichem Verhalten
- Wird an alle Templates übergeben
```

**Template-Implementierung:**
```php
// Alle Templates prüfen enable_modal Flag
<?php if ( $args['enable_modal'] ?? true ) : ?>
    data-event-id="<?php echo esc_attr($event['id']); ?>"
    class="cts-event-clickable"
<?php endif; ?>
```

**Betroffene Templates:**
- ✅ Calendar: monthly-modern (NEU!)
- ✅ List: classic, medium, compact, fluent, modern (bereits vorhanden)
- ✅ Grid: simple, modern, colorful (bereits vorhanden)

---

### 2. **Vollständige View-Auswahl in Blocks**

**Vorher (unvollständig):**
- List: classic, medium (3 Templates fehlten!)
- Grid: simple (2 Templates fehlten!)

**Nachher (komplett):**
- List: classic, medium, compact, fluent, modern ✅
- Grid: simple, modern, colorful ✅

**Änderungen:**
- `includes/class-churchtools-suite-elementor-widget.php`: +5 Views
- `assets/js/churchtools-suite-blocks.js`: +5 Views

---

## 🔧 **Technische Verbesserungen**

### Block Editor Integration
- Elementor Widget: enable_modal Control in register_controls()
- Gutenberg Block: enable_modal Attribut + ToggleControl
- Beide Editoren jetzt feature-identisch

### Template Konsistenz
- Alle Templates erhalten enable_modal Flag via $args
- Standardwert: true (Modal enabled)
- Rückwärtskompatibel: Templates ohne Flag funktionieren weiterhin

---

## 📋 **Geänderte Dateien**

### Core Plugin
- `churchtools-suite.php` - Version 0.10.3.0
- `includes/class-churchtools-suite-elementor-widget.php`:
  - Zeile ~148: List Views erweitert (+3 Optionen)
  - Zeile ~263: Grid Views erweitert (+2 Optionen)
  - Zeile ~445: enable_modal Control hinzugefügt
  - Zeile ~738: enable_modal an Templates übergeben

### Gutenberg Block
- `assets/js/churchtools-suite-blocks.js`:
  - Zeile ~28: List Views erweitert (+3 Optionen)
  - Zeile ~38: Grid Views erweitert (+2 Optionen)
  - Zeile ~164: enable_modal Attribut
  - Zeile ~324: enable_modal ToggleControl

### Templates
- `templates/calendar/monthly-modern.php`:
  - Event-Dots jetzt clickable
  - data-event-id Attribut
  - cts-event-clickable Klasse

---

## 🐛 **Bugfixes**

- **Fix:** Fehlende View-Optionen - compact, fluent, modern (List) waren nicht auswählbar
- **Fix:** Fehlende View-Optionen - modern, colorful (Grid) waren nicht auswählbar
- **Fix:** Calendar events waren nicht clickable (kein data-event-id)

---

## 📚 **Dokumentation**

### Verwendung

**Shortcode (manual):**
```php
[cts_list view="compact" enable_modal="true"]
[cts_calendar view="monthly-modern" enable_modal="false"]
```

**Elementor Widget:**
1. Widget hinzufügen
2. Basis-Einstellungen → 👆 Click-to-Details = AN/AUS
3. Speichern

**Gutenberg Block:**
1. Block einfügen
2. Sidebar → Basis-Einstellungen → 👆 Click-to-Details Toggle
3. Publish

---

## ⚙️ **Migration**

**Keine Migration nötig** - Alle Änderungen sind rückwärtskompatibel:
- Bestehende Widgets/Blocks: enable_modal=true (default)
- Bestehende Templates: Verhalten unverändert
- Keine DB-Schema-Änderungen

---

## 🔄 **Upgrade-Pfad**

Von v0.10.2.9 → v0.10.3.0:
1. Plugin aktualisieren (ZIP-Upload oder Git Pull)
2. Keine weiteren Schritte erforderlich
3. Neue Features sofort verfügbar

---

## ✅ **Testing Checklist**

**Block Editors:**
- [x] Elementor: enable_modal Toggle funktioniert
- [x] Gutenberg: enable_modal Toggle funktioniert
- [x] Beide Editoren: Alle Views verfügbar

**Templates:**
- [x] Calendar monthly-modern: Events clickable
- [x] List compact: In Optionen verfügbar
- [x] List fluent: In Optionen verfügbar
- [x] List modern: In Optionen verfügbar
- [x] Grid modern: In Optionen verfügbar
- [x] Grid colorful: In Optionen verfügbar

**Modal Funktionalität:**
- [x] enable_modal=true: Modal öffnet sich
- [x] enable_modal=false: Kein Modal
- [x] Rückwärtskompatibilität: Alte Shortcodes funktionieren

---

## 📝 **Known Issues**

Keine bekannten Probleme.

---

## 🎯 **Nächste Schritte (v0.10.4.0)**

Potenzielle Features:
- [ ] Weitere Template-Optionen konfigurierbar machen
- [ ] Modal-Stil auswählbar (classic, modern, minimal, card)
- [ ] Advanced Filtering in Blocks
- [ ] Service-Filter in Templates

---

**Weitere Informationen:**
- GitHub: https://github.com/FEGAschaffenburg/churchtools-suite
- Issues: https://github.com/FEGAschaffenburg/churchtools-suite/issues
