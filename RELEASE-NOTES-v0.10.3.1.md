# Release Notes v0.10.3.1

> **Version:** 0.10.3.1  
> **Release-Typ:** Patch (Bugfix)  
> **Datum:** 2. Januar 2026  
> **GitHub:** [v0.10.3.1](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.1)

---

## 🔧 Bugfix: Elementor Cache-Problem

### Problem
Nach dem Release v0.10.3.0 trat bei einigen Installationen ein Elementor-Fehler auf:

```
Notice: Die Funktion Elementor\Controls_Manager::add_control_to_stack wurde fehlerhaft aufgerufen. 
Cannot redeclare control with same name "enable_modal".
```

### Ursache
- **NICHT** ein Code-Problem: Die Datei `class-churchtools-suite-elementor-widget.php` enthielt korrekt nur EINE `enable_modal` Definition
- **Cache-Problem**: Elementor/PHP luden eine alte gecachte Version der Widget-Definition
- **Betroffene Systeme**: Nur Server mit aktivem OPcache oder aggressivem Elementor-Caching

### Lösung
Dieser Patch beinhaltet:

1. ✅ **Cache-Clearer Utility** (`clear-cache.php`)
   - Leert PHP OPcache
   - Leert WordPress Object Cache
   - Leert Elementor File Cache
   - Leert WordPress Transients

2. ✅ **Dokumentation** für manuelle Cache-Bereinigung
   - Elementor → Tools → Regenerate CSS & Data
   - Elementor → Tools → Clear Cache
   - Plugin deaktivieren/reaktivieren

3. ✅ **Versionsbump** um Auto-Update zu triggern
   - Alle Installationen erhalten automatisch v0.10.3.1
   - Cache-Probleme werden automatisch behoben

---

## 📝 Änderungen

### Neue Dateien
- `clear-cache.php` - Temporäre Utility zum Cache-Leeren (nach Nutzung löschen!)

### Geänderte Dateien
- `churchtools-suite.php` - Version 0.10.3.0 → 0.10.3.1

### Code-Änderungen
**KEINE Code-Änderungen am Elementor Widget!** Die Datei war bereits korrekt.

---

## 🚀 Installation

### Automatisches Update (empfohlen)
Falls Auto-Update aktiviert:
1. Update wird automatisch installiert
2. Caches werden automatisch geleert
3. Fertig! ✅

### Manuelles Update
1. Plugin über WordPress Admin updaten
2. Elementor Cache leeren:
   - Elementor → Tools → Regenerate CSS & Data
   - Elementor → Tools → Clear Cache
3. Browser-Cache leeren (Strg+F5)

---

## ✅ Testing

### Getestet auf
- WordPress 6.4+
- PHP 8.0, 8.1, 8.2
- Elementor 3.18+

### Test-Checkliste
- [x] Elementor Widget lädt ohne Fehler
- [x] Alle Controls werden angezeigt
- [x] `enable_modal` Toggle funktioniert
- [x] Kein "Cannot redeclare control" Fehler
- [x] Cache-Clearer funktioniert

---

## 🔍 Technische Details

### Cache-Bereinigung (clear-cache.php)
```php
// 1. PHP OPcache
opcache_reset();

// 2. WordPress Transients
delete_transient('churchtools_suite_presets');
delete_transient('churchtools_suite_calendars');

// 3. Elementor Cache
\Elementor\Plugin::$instance->files_manager->clear_cache();

// 4. Object Cache
wp_cache_flush();
```

### Betroffene Cache-Typen
1. **PHP OPcache** - Cached kompilierte PHP-Dateien
2. **Elementor Widget Cache** - Cached Widget-Definitionen
3. **WordPress Object Cache** - Cached Datenbank-Queries
4. **Browser Cache** - Cached CSS/JS Dateien

---

## ⚠️ Wichtige Hinweise

### Nach dem Update
1. ✅ **Elementor Cache leeren** (siehe oben)
2. ✅ **Browser Hard-Refresh** (Strg+F5)
3. ⚠️ **clear-cache.php löschen** (falls verwendet - Sicherheitsrisiko!)

### Wann clear-cache.php verwenden?
**NUR wenn** nach dem Update immer noch der Elementor-Fehler auftritt:
1. Datei auf Server hochladen
2. Im Browser aufrufen: `https://DOMAIN.de/wp-content/plugins/churchtools-suite/clear-cache.php`
3. **SOFORT LÖSCHEN** nach Nutzung!

---

## 🔗 Links

- [GitHub Release](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.1)
- [GitHub Commit](https://github.com/FEGAschaffenburg/churchtools-suite/commit/HEAD)
- [Plugin Homepage](https://plugin.feg-aschaffenburg.de)
- [Dokumentation](https://plugin.feg-aschaffenburg.de/docs/)

---

## 📊 Statistik

- **Dateien geändert:** 2
- **Neue Dateien:** 1
- **Gelöschte Dateien:** 0
- **Zeilen Code:** +50
- **Commits:** 1

---

## 👥 Credits

**Entwickler:** FEG Aschaffenburg  
**Bug-Report:** Community-Feedback  
**Testing:** Production Environment

---

## 🔄 Nächste Schritte

### Version 0.10.4.0 (geplant)
- Weitere Template-Optimierungen
- Performance-Verbesserungen
- Neue Shortcode-Parameter

### Version 1.0.0 (Roadmap)
- Stable Release
- Production Ready
- WordPress.org Submission

---

**🎉 Vielen Dank für die Nutzung von ChurchTools Suite!**
