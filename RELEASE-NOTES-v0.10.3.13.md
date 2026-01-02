# Release Notes: v0.10.3.13

**Release-Datum:** 2. Januar 2026  
**Art:** Kritischer Bugfix (Fatal Error)  
**Status:** Production Ready ✅

---

## 🐛 Kritischer Bugfix

### Fatal Error beim Plugin-Laden behoben
**Problem:** WordPress Admin komplett blockiert durch Fatal Error

```
ArgumentCountError: Too few arguments to function 
ChurchTools_Suite_Auto_Updater::add_cron_hook_display_name(), 
1 passed and exactly 2 expected
```

**Root Cause:**
- Filter `cron_request` übergibt nur 1 Parameter (`$cron_request`)
- Funktion `add_cron_hook_display_name()` erwartete 2 Parameter
- **Function Signature Mismatch** führte zu Fatal Error

**Fix:**
- Filter-Hook komplett entfernt (nicht benötigt)
- Funktion `add_cron_hook_display_name()` entfernt
- Funktion `get_cron_hook_display_name()` bleibt (für manuelle Anzeige)

**Auswirkung:**
- ✅ Plugin lädt ohne Fehler
- ✅ WordPress Admin funktioniert wieder
- ✅ Auto-Update Funktionalität bleibt erhalten

---

## 📋 Änderungen im Detail

### Dateien geändert
- `includes/class-churchtools-suite-auto-updater.php`
  - Zeile 23: Filter `cron_request` entfernt
  - Zeilen 43-50: Funktion `add_cron_hook_display_name()` entfernt
  - `get_cron_hook_display_name()` bleibt für Display-Zwecke

---

## 🔍 Technische Details

**Fehlerursache:**
WordPress Filter `cron_request` hat nur 1 Parameter:
- `$cron_request` (array) - Die Cron Request Daten

Die Funktion wurde aber mit 2 Parametern definiert:
```php
public static function add_cron_hook_display_name( $cron_request, $doing_wp_cron )
```

**Lösung:**
Filter war nicht notwendig - WordPress hat keine native Unterstützung für Custom Cron Hook Display Names. Die Funktion `get_cron_hook_display_name()` reicht für manuelle Anzeigen.

---

## 📦 Deployment

**Breaking:** Ja - Fatal Error blockiert Plugin komplett  
**Update:** SOFORT empfohlen!

**Installation:**
1. Plugin aktualisieren (Auto-Update oder manuell)
2. ✅ WordPress Admin lädt wieder

---

## ⚠️ Wichtig

Falls v0.10.3.12 bereits installiert ist und WordPress Admin nicht erreichbar:

**Option 1:** FTP/SSH Plugin deaktivieren
```bash
# Via WP-CLI
wp plugin deactivate churchtools-suite
wp plugin update churchtools-suite
wp plugin activate churchtools-suite
```

**Option 2:** Manuelles ZIP-Upload via FTP
1. ZIP entpacken
2. Per FTP nach `/wp-content/plugins/churchtools-suite/` kopieren
3. WordPress Admin sollte wieder funktionieren

---

## 🎯 Nächste Schritte

- [x] Fatal Error behoben
- [ ] Kalender-Netzwerkfehler debuggen
- [ ] Console-Logging Cleanup für v0.10.4.0
