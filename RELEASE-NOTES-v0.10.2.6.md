# Release Notes - Version 0.10.2.6

**Release Date:** 2. Januar 2026  
**Type:** Bugfix (UX)  
**Priority:** Niedrig (kosmetisches Problem)

---

## 🐛 Bugfix: Update-Cron zeigt schönen Namen

### Problem

Im Dashboard wurden Cronjob-Namen inkonsistent angezeigt:

- ✅ Event-Sync: "Event-Sync" + Beschreibung
- ✅ Session Keepalive: "Session Keepalive" + Beschreibung  
- ❌ Update-Prüfung: "churchtools_suite_check_updates" (technischer Name, KEINE Beschreibung)

**Root Cause:**

Dashboard-Code suchte nach `churchtools_suite_auto_update` (falscher Hook-Name!), aber der tatsächliche Hook heißt `churchtools_suite_check_updates`.

```php
// Alter Code (FALSCH)
} elseif ( $hook_name === 'churchtools_suite_auto_update' ) { // ❌ Falscher Hook-Name!
    $label = __( 'Auto-Update', 'churchtools-suite' );
    $desc = __( 'Prüft und installiert Updates automatisch.', 'churchtools-suite' );
}

// Tatsächlicher Hook
const CRON_HOOK = 'churchtools_suite_check_updates'; // ✅ ChurchTools_Suite_Auto_Updater
```

### Lösung (v0.10.2.6)

**Verwendet jetzt `ChurchTools_Suite_Cron_Display` Helper-Klasse:**

Die Helper-Klasse existierte bereits seit v0.10.1.9 mit allen korrekten Hook-Namen, wurde aber im Dashboard-Code nicht genutzt!

```php
// Neuer Code (KORREKT)
$label = class_exists( 'ChurchTools_Suite_Cron_Display' ) 
    ? ChurchTools_Suite_Cron_Display::get_cron_display_name( $hook_name )
    : $hook_name;
$desc = class_exists( 'ChurchTools_Suite_Cron_Display' )
    ? ChurchTools_Suite_Cron_Display::get_cron_description( $hook_name )
    : '';
```

**Vorteile:**

1. ✅ Zentrale Definition aller Cron-Namen
2. ✅ Konsistente Anzeige überall (Dashboard, Debug-Tab, WP-CLI)
3. ✅ Automatisch korrekt bei neuen Cron-Jobs
4. ✅ Keine Hardcoding mehr im Dashboard

---

## 📝 Technische Details

### Geänderte Dateien

**admin/views/tab-dashboard.php:**
- Entfernt: Hardcodierte if/elseif Labels für Cronjobs
- Neu: Verwendet `ChurchTools_Suite_Cron_Display::get_cron_display_name()`
- Neu: Verwendet `ChurchTools_Suite_Cron_Display::get_cron_description()`

### Cron-Namen nach v0.10.2.6

**Dashboard Cronjobs Card:**

| Hook-Name (technisch) | Anzeigename | Beschreibung |
|----------------------|-------------|--------------|
| `churchtools_suite_auto_sync` | Event-Synchronisation | Synchronisiert Events automatisch gemäß Zeitplan. |
| `churchtools_suite_session_keepalive` | Session aufrechterhalten | Verlängert die ChurchTools-Session. |
| `churchtools_suite_check_updates` | **Update-Prüfung** ✅ | Prüft auf neue Plugin-Versionen und installiert Updates automatisch. |

**Vorher (v0.10.2.5):**
```
⏰ churchtools_suite_check_updates
(keine Beschreibung)
```

**Nachher (v0.10.2.6):**
```
⏰ Update-Prüfung
Prüft auf neue Plugin-Versionen und installiert Updates automatisch.
```

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren (v0.10.2.6)
2. **Automatisch:** Dashboard zeigt korrekten Cron-Namen
3. **Kein Cache-Clear erforderlich**

### Manuelle Validierung
1. **Admin → ChurchTools Suite → Dashboard:**
   - Scrollen zu "Cronjobs" Card
   - Prüfen: ALLE 3 Cronjobs haben benutzerfreundliche Namen
   - Erwartung: "Update-Prüfung" statt "churchtools_suite_check_updates"

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Rein kosmetische Verbesserung.

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.6 - Fix: Update-Cron zeigt schönen Namen (ChurchTools_Suite_Cron_Display)"
git push
git tag v0.10.2.6
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.6"
```

---

## 🔗 Related Files

**Helper-Klasse (bereits existiert seit v0.10.1.9):**
- `includes/class-churchtools-suite-cron-display.php`
  * `get_cron_display_name()` - Benutzerfreundliche Namen
  * `get_cron_description()` - Beschreibungen
  * `format_cron_event()` - Formatierung für WP-CLI

**Verwendung:**
- `admin/views/tab-dashboard.php` - Dashboard Cronjobs Card (NEU in v0.10.2.6)
- `admin/views/tab-debug-minimal.php` - Debug-Tab Cron-Liste

---

## 📚 Lessons Learned

1. **Code-Duplikation vermeiden:**
   - Helper-Klasse existierte bereits, wurde aber nicht genutzt
   - Dashboard hatte eigene (fehlerhafte) Hardcoding
   - Lösung: Zentrale Helper-Klasse überall verwenden

2. **Hook-Namen dokumentieren:**
   - Technischer Name ≠ Anzeigename
   - Änderungen an Hook-Namen → Update ALLER Referenzen
   - `churchtools_suite_auto_update` war alter/veralteter Name

3. **Testing:**
   - UX-Testing auf Dashboard nicht vollständig
   - Cronjob-Namen sollten in Checklist aufgenommen werden

---

**Status:** ✅ Fix angewendet, bereit für Deployment  
**Priorität:** 🟡 Niedrig - Kosmetisches Problem, keine Funktionalität betroffen
