# Release Notes v0.10.3.2

> **Version:** 0.10.3.2  
> **Release-Typ:** Patch (Bugfix)  
> **Datum:** 2. Januar 2026  
> **GitHub:** [v0.10.3.2](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.2)

---

## 🔧 Bugfix: Update-UX Verbesserungen

### Problem 1: Update-Meldung bleibt nach Installation sichtbar

**Symptom:**
- Nach erfolgreicher Installation von v0.10.3.1 blieb die Update-Meldung im Dashboard sichtbar
- "Update verfügbar" wurde weiterhin angezeigt, obwohl bereits installiert
- Page-Refresh war notwendig um Meldung zu aktualisieren

**Ursache:**
- Transient-Cache wurde nach Update-Installation nicht geleert
- `get_latest_release_info()` lieferte gecachte Daten

**Lösung:**
- Cache wird nach erfolgreicher Installation automatisch geleert
- Transients `churchtools_suite_update_info` und `churchtools_suite_release_info` werden gelöscht
- Update-Meldung verschwindet sofort nach Installation

---

### Problem 2: Falsche Weiterleitung nach Update

**Symptom:**
- Nach erfolgreicher Update-Installation: Weiterleitung zu `/wp-admin/plugins.php`
- Benutzer verlässt das ChurchTools Suite Dashboard
- Unpraktisch für schnelle Update-Tests

**Ursache:**
- JavaScript-Code leitete nach Update zu Plugin-Seite weiter
- `window.location.href = admin_url('plugins.php')`

**Lösung:**
- Dashboard wird nach Update neu geladen (Page Refresh)
- `window.location.reload()` statt Redirect
- Benutzer bleibt im ChurchTools Suite Dashboard
- Update-Meldung verschwindet sofort (durch Cache-Clearing)

---

## 📝 Änderungen

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php:**
```php
// NEU: Cache nach Update leeren (Zeile 54-55)
delete_transient( 'churchtools_suite_update_info' );
delete_transient( 'churchtools_suite_release_info' );
```

**admin/views/tab-dashboard.php:**
```php
// ALT (v0.10.3.1):
alert('Update erfolgreich! Sie werden zur Plugin-Seite weitergeleitet...');
window.location.href = '<?php echo admin_url('plugins.php'); ?>';

// NEU (v0.10.3.2):
window.location.reload(); // Dashboard refresh
```

**churchtools-suite.php:**
- Version 0.10.3.1 → 0.10.3.2

---

## 🚀 Installation

### Automatisches Update (empfohlen)
Falls Auto-Update aktiviert:
1. Update wird automatisch installiert
2. Dashboard wird automatisch neu geladen
3. Update-Meldung verschwindet sofort ✅

### Manuelles Update
1. Plugin über WordPress Admin updaten
2. Dashboard wird automatisch neu geladen
3. Fertig! ✅

---

## ✅ Testing

### Getestet auf
- WordPress 6.4+
- PHP 8.0, 8.1, 8.2

### Test-Checkliste
- [x] Update-Meldung verschwindet nach Installation
- [x] Dashboard wird nach Update neu geladen (kein Redirect zu plugins.php)
- [x] Cache wird korrekt geleert
- [x] Auto-Update funktioniert
- [x] Manuelles Update funktioniert

---

## 🔍 Technische Details

### Cache-Clearing nach Update
```php
public function ajax_run_update() {
    // ... Update durchführen ...
    
    // Cache leeren (v0.10.3.2)
    delete_transient( 'churchtools_suite_update_info' );
    delete_transient( 'churchtools_suite_release_info' );
    
    wp_send_json_success();
}
```

### Dashboard-Refresh statt Redirect
```javascript
// v0.10.3.1 (ALT):
alert('Update erfolgreich! Sie werden zur Plugin-Seite weitergeleitet...');
window.location.href = '/wp-admin/plugins.php';

// v0.10.3.2 (NEU):
window.location.reload(); // Dashboard bleibt aktiv
```

---

## 📊 Update-Flow

### Vorher (v0.10.3.1):
```
1. Update installieren
2. Alert: "Update erfolgreich!"
3. Weiterleitung zu plugins.php
4. Update-Meldung bleibt sichtbar (Cache)
5. Manueller Page-Refresh notwendig
```

### Nachher (v0.10.3.2):
```
1. Update installieren
2. Cache wird geleert
3. Dashboard neu laden
4. Update-Meldung ist weg ✅
5. Kein manueller Refresh notwendig ✅
```

---

## ⚠️ Upgrade Notes

### Von v0.10.3.1 zu v0.10.3.2:
- **Breaking Changes:** Keine
- **Neue Features:** Keine
- **Bugfixes:** Update-UX verbessert
- **Datenbank:** Keine Änderungen
- **Kompatibilität:** 100% kompatibel mit v0.10.3.1

---

## 🔗 Links

- [GitHub Release](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.2)
- [GitHub Commit](https://github.com/FEGAschaffenburg/churchtools-suite/commit/HEAD)
- [Plugin Homepage](https://plugin.feg-aschaffenburg.de)
- [Dokumentation](https://plugin.feg-aschaffenburg.de/docs/)

---

## 📊 Statistik

- **Dateien geändert:** 3
- **Neue Dateien:** 0
- **Gelöschte Dateien:** 0
- **Zeilen Code:** +5, -4
- **Commits:** 1

---

## 👥 Credits

**Entwickler:** FEG Aschaffenburg  
**Bug-Report:** Production Testing  
**Testing:** Live Environment

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
