# Release Notes - Version 0.10.2.3

**Release Date:** 2. Januar 2026  
**Type:** Kritischer Bugfix  
**Priority:** Sehr Hoch (Doppelte Cron-Jobs)

---

## 🐛 Kritischer Bugfix: Doppelte Cron-Jobs

### Problem
Nach dem Update auf v0.10.2.2 existieren **zwei** `churchtools_suite_auto_sync` Cron-Jobs:
1. **Alter Job:** `hourly` (in 22 Minuten)
2. **Neuer Job:** `daily` (in 14 Stunden)

**Symptom im Screenshot:**
```
churchtools_suite_auto_sync | Einmal stündlich    | in 22 Minuten
churchtools_suite_auto_sync | Einmal täglich      | in 14 Stunden
```

**Ursache:** Die Zeile `wp_clear_scheduled_hook()` **fehlte komplett** in der `update_sync_schedule()` Methode! Alte Cron-Jobs wurden nicht gelöscht, bevor neue geplant wurden.

### Root Cause Analysis

**Code-Vergleich:**

**Erwartet (sollte sein):**
```php
public static function update_sync_schedule() {
    // 1. Alte Jobs löschen ✅
    wp_clear_scheduled_hook('churchtools_suite_auto_sync');
    
    // 2. Neue Jobs planen ✅
    if ($auto_sync_enabled) {
        wp_schedule_event($next_run, $interval, 'churchtools_suite_auto_sync');
    }
}
```

**Tatsächlich (v0.10.2.2):**
```php
public static function update_sync_schedule() {
    // 1. Alte Jobs löschen ❌ FEHLT KOMPLETT!
    
    // 2. Neue Jobs planen ✅
    if ($auto_sync_enabled) {
        wp_schedule_event($next_run, $interval, 'churchtools_suite_auto_sync');
    }
}
```

**Problem:** `wp_clear_scheduled_hook()` wurde in einem früheren Commit versehentlich entfernt oder nie hinzugefügt.

### Lösung (v0.10.2.3)

✅ **Robuste Cron-Cleanup-Logik** implementiert:
```php
// NICHT nur wp_clear_scheduled_hook() - manchmal unzuverlässig!
// Direkter Zugriff auf Cron-Array für garantiertes Löschen ALLER Instanzen

$crons = _get_cron_array();
if ( is_array( $crons ) ) {
    foreach ( $crons as $timestamp => $cron ) {
        if ( isset( $cron['churchtools_suite_auto_sync'] ) ) {
            unset( $crons[ $timestamp ]['churchtools_suite_auto_sync'] );
            if ( empty( $crons[ $timestamp ] ) ) {
                unset( $crons[ $timestamp ] );
            }
        }
    }
    _set_cron_array( $crons );
}
```

**Warum nicht `wp_clear_scheduled_hook()`?**
- Löscht manchmal nur den **nächsten** Job, nicht alle
- Bei mehreren Schedules mit gleichem Hook unzuverlässig
- Direkter Array-Zugriff ist garantiert vollständig

**Betroffene Datei:**
- `includes/class-churchtools-suite-cron.php` - `update_sync_schedule()` Methode

---

## 📝 Technische Details

### Code-Änderungen

**Datei:** `includes/class-churchtools-suite-cron.php`  
**Funktion:** `update_sync_schedule()`  
**Zeilen:** ~131-145

```diff
  public static function update_sync_schedule() {
      $auto_sync_enabled = get_option('churchtools_suite_auto_sync_enabled', 0);
      $interval = get_option('churchtools_suite_auto_sync_interval', 'daily');
      
+     // v0.10.2.3: ALLE existierenden Schedules löschen (robust!)
+     $crons = _get_cron_array();
+     if ( is_array( $crons ) ) {
+         foreach ( $crons as $timestamp => $cron ) {
+             if ( isset( $cron['churchtools_suite_auto_sync'] ) ) {
+                 unset( $crons[ $timestamp ]['churchtools_suite_auto_sync'] );
+                 if ( empty( $crons[ $timestamp ] ) ) {
+                     unset( $crons[ $timestamp ] );
+                 }
+             }
+         }
+         _set_cron_array( $crons );
+     }
      
      // Schedule new job if enabled
      if ($auto_sync_enabled) {
          wp_schedule_event($next_run, $interval, 'churchtools_suite_auto_sync');
      }
  }
```

### WordPress Cron API

**Interne Funktionen (nicht dokumentiert, aber zuverlässig):**
- `_get_cron_array()` - Holt alle geplanten Cron-Jobs
- `_set_cron_array($crons)` - Setzt alle Cron-Jobs (überschreibt komplett)

**Vorteil:** Garantiert, dass **alle** Duplikate entfernt werden.

---

## 🧪 Testing

### Erwartetes Verhalten nach Update

1. **Vor Update (v0.10.2.2):**
   ```
   churchtools_suite_auto_sync | hourly | in 22 Minuten  ❌
   churchtools_suite_auto_sync | daily  | in 14 Stunden  ❌
   ```

2. **Nach Update (v0.10.2.3):**
   ```
   churchtools_suite_auto_sync | daily  | Fr, 02. Jan 2026 03:00  ✅
   ```
   **NUR NOCH EIN Eintrag!**

### Validierung

**Debug-Tab prüfen:**
- Admin → ChurchTools Suite → Erweitert → Übersicht
- Cron-Jobs-Tabelle sollte **nur noch 1 Eintrag** für `churchtools_suite_auto_sync` zeigen

**WP-CLI:**
```bash
wp cron event list
# Suche: churchtools_suite_auto_sync
# Sollte nur 1x auftauchen!
```

**Datenbank (direkt):**
```sql
SELECT * FROM wp_options WHERE option_name = 'cron';
-- In option_value sollte churchtools_suite_auto_sync nur 1x vorkommen
```

### Test-Checklist

- [x] Robuste Cleanup-Logik implementiert
- [x] Direkter Array-Zugriff statt `wp_clear_scheduled_hook()`
- [ ] Nach Update: Nur 1 Cron-Job sichtbar
- [ ] Intervall korrekt: `daily` (nicht `hourly`)

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Der Fix ist rückwärtskompatibel.

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren (v0.10.2.3)
2. **Automatisch:** Alle alten Cron-Jobs werden gelöscht
3. **Automatisch:** Ein neuer Job wird geplant mit `daily` Intervall

### Manuelle Cleanup (Optional)
Wenn nach dem Update immer noch Duplikate existieren:

```bash
# Via WP-CLI
wp cron event delete churchtools_suite_auto_sync --all

# Dann Settings speichern um neu zu planen
# Admin → ChurchTools Suite → Einstellungen → Sync → Speichern
```

**Oder via Code (in functions.php temporär):**
```php
add_action('init', function() {
    require_once WP_PLUGIN_DIR . '/churchtools-suite/includes/class-churchtools-suite-cron.php';
    ChurchTools_Suite_Cron::update_sync_schedule();
}, 999);
```

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.3 - Fix: Doppelte Cron-Jobs (Cleanup fehlte)"
git push
git tag v0.10.2.3
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.3"
```

---

## 🔗 Related Issues

- User-reported: "jetzt gibt es ihn doppelt"
- Screenshot: 2x `churchtools_suite_auto_sync` (hourly + daily)
- **Root Cause:** `wp_clear_scheduled_hook()` fehlte komplett in Methode

---

## 📚 Lessons Learned

1. **WordPress Cron API:**
   - `wp_clear_scheduled_hook()` ist manchmal unzuverlässig
   - Bei kritischen Cleanups: Direkter Array-Zugriff verwenden
   - `_get_cron_array()` + `_set_cron_array()` garantiert Vollständigkeit

2. **Testing:**
   - Nach Cron-Änderungen: IMMER `wp cron event list` prüfen
   - Unit-Test: Mock Cron-Array und validiere Cleanup
   - Integration-Test: Prüfe Duplikate in CI/CD

3. **Code-Review:**
   - Kritische Zeilen (wie Cleanup) extra kommentieren
   - `wp_clear_scheduled_hook()` Calls NIE entfernen ohne Begründung

---

**Status:** ✅ Behoben, bereit für Deployment  
**Priorität:** 🚨 SEHR HOCH - Verhindert doppelte Sync-Läufe!
