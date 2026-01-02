# Release Notes - Version 0.10.2.2

**Release Date:** 2. Januar 2026  
**Type:** Bugfix  
**Priority:** Mittel (UX-Verbesserung)

---

## 🐛 Bugfixes

### 1. Cron-Zeitzone Fix
**Problem:** Die Cron-Ausführungszeit im Debug-Tab wurde in **UTC/Server-Zeit** angezeigt statt in **WordPress-Zeitzone**.

**Beispiel:**
- **Server-Zeit (UTC):** Fr, 02. Jan 2026 13:00
- **WordPress-Zeit (Europe/Berlin):** Fr, 02. Jan 2026 14:00

**Lösung:** 
✅ Zeitanzeige verwendet jetzt `get_date_from_gmt()` für korrekte WordPress-Zeitzone  
✅ Cron-Zeiten werden in der konfigurierten WP-Zeitzone angezeigt

**Betroffene Datei:**
- `admin/views/tab-debug-minimal.php` - Cron-Zeitanzeige

---

### 2. Auto-Reschedule nach Plugin-Update
**Problem:** Nach einem Plugin-Update blieben Cron-Jobs auf **alten Intervallen** hängen (z.B. `hourly` statt `daily`).

**Ursache:** WordPress plant Cron-Jobs NUR beim erstmaligen Aktivieren des Plugins. Bei Updates werden bestehende Schedules NICHT automatisch aktualisiert.

**Lösung:**
✅ Nach jedem Plugin-Update werden Cron-Jobs **automatisch neu geplant**  
✅ Version-Tracking verhindert unnötiges Re-Scheduling  
✅ Logging für Transparenz

**Betroffene Datei:**
- `includes/class-churchtools-suite.php` - `maybe_reschedule_crons()` Methode

**Ablauf nach Update:**
1. Plugin erkennt neue Version
2. Ruft `ChurchTools_Suite_Cron::update_sync_schedule()` auf
3. Löscht alte Cron-Jobs (`wp_clear_scheduled_hook()`)
4. Plant neue Jobs mit aktuellen Intervallen
5. Speichert Version-Tracking

---

## 📝 Technische Details

### Zeitzone-Konvertierung

**Vorher (v0.10.2.1):**
```php
// FALSCH: Zeigt UTC-Zeit!
echo date_i18n( 'D, d. M Y H:i', $next_run );
```

**Nachher (v0.10.2.2):**
```php
// KORREKT: Konvertiert UTC → WordPress-Zeitzone
$local_time = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next_run ), 'Y-m-d H:i:s' );
echo date_i18n( 'D, d. M Y H:i', strtotime( $local_time ) );
```

### Auto-Reschedule Logik

**Datei:** `includes/class-churchtools-suite.php`  
**Funktion:** `maybe_reschedule_crons()`

```php
private function maybe_reschedule_crons(): void {
    $last_version = get_option( 'churchtools_suite_last_cron_reschedule_version', '0.0.0' );
    
    // Nur bei neuer Version
    if ( version_compare( $last_version, $this->version, '<' ) ) {
        ChurchTools_Suite_Cron::update_sync_schedule();
        update_option( 'churchtools_suite_last_cron_reschedule_version', $this->version );
        
        // Log
        ChurchTools_Suite_Logger::info( 'cron', 
            sprintf( 'Cron-Jobs nach Update auf v%s neu geplant', $this->version )
        );
    }
}
```

**Version-Tracking:**
- Option: `churchtools_suite_last_cron_reschedule_version`
- Speichert letzte Version, die Crons neu geplant hat
- Verhindert mehrfaches Re-Scheduling bei gleicher Version

---

## 🧪 Testing

### Erwartetes Verhalten nach Update

1. **Zeitzone korrekt:**
   - Debug → Übersicht → Cron-Jobs
   - "Nächste Ausführung" zeigt WordPress-Zeitzone
   - **NICHT** mehr UTC/Server-Zeit

2. **Auto-Reschedule:**
   - Nach Update automatisch neue Cron-Jobs geplant
   - Logs zeigen: `Cron-Jobs nach Update auf v0.10.2.2 neu geplant`
   - Debug-Tab zeigt korrektes Intervall (`daily` statt `hourly`)

3. **Intervall korrekt:**
   - Event-Synchronisation: `daily` (24 Stunden)
   - Nächste Ausführung: Morgen 03:00 Uhr (WordPress-Zeit)

### Test-Checklist

- [x] Zeitzone-Konvertierung implementiert
- [x] Auto-Reschedule Logik hinzugefügt
- [x] Version-Tracking für Re-Scheduling
- [ ] Nach Update: Debug-Tab prüfen (Zeitzone + Intervall)
- [ ] Logs prüfen: Re-Schedule-Eintrag vorhanden

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Beide Fixes sind rückwärtskompatibel.

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren
2. **Automatisch:** Cron-Jobs werden neu geplant
3. **Automatisch:** Zeitzone wird korrekt konvertiert

### Manuelle Validierung (Empfohlen)
1. **Debug-Tab prüfen:**
   - WordPress Admin → ChurchTools Suite → Erweitert
   - Cron-Jobs-Tabelle prüfen
   - "Event-Synchronisation" sollte `daily` zeigen
   - Zeitanzeige sollte WordPress-Zeitzone verwenden

2. **Logs prüfen:**
   - Debug → Logs
   - Suche: `Cron-Jobs nach Update auf v0.10.2.2 neu geplant`

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.2 - Fix: Cron-Zeitzone + Auto-Reschedule nach Update"
git push
git tag v0.10.2.2
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.2"
```

---

## 🔗 Related Issues

- User-reported: "immer noch auf stündlich und keine WordPress Server Zeit"
- Screenshot: Cron läuft `hourly` statt `daily` (alte Version aktiv)
- Zeitzone: UTC statt Europe/Berlin

---

## 📚 Zusammenfassung

### Was wurde behoben?
1. **Zeitzone:** Cron-Zeiten werden jetzt in WordPress-Zeitzone angezeigt
2. **Auto-Update:** Cron-Jobs werden nach Plugin-Update automatisch neu geplant

### Was muss der User tun?
1. **Plugin-Update installieren** (v0.10.2.2)
2. **Nichts weiter!** → Alles läuft automatisch

### Was ändert sich?
- Debug-Tab zeigt jetzt **korrekte WordPress-Zeit**
- Nach Update: Cron-Jobs automatisch auf `daily` (24h) umgestellt
- Keine manuellen Schritte erforderlich

---

**Status:** ✅ Behoben, bereit für Deployment  
**Priorität:** 🟡 Mittel - Verbessert UX und Zuverlässigkeit
