# ChurchTools Suite - Release Notes v0.10.1.5

**Datum:** 2. Januar 2026  
**Typ:** Kritischer Bugfix-Release

---

## 🐛 Kritische Bugfixes

### 1. JavaScript-Fehler beim manuellen Sync behoben
**Problem:** Beim manuellen Sync erschien Fehler "Cannot read properties of undefined (reading 'message')".

**Ursache:** 
- AJAX-Response-Struktur war verschachtelt: `response.data.stats` statt direkt `response.data`
- JavaScript versuchte auf nicht existierende Properties zuzugreifen
- Fehlende defensive Programmierung

**Lösung:**
- Prüfe ob `response.data.stats` existiert, sonst Fallback auf `response.data`
- Defensive Null-Checks für alle Response-Properties
- Bessere Error-Message-Extraktion (prüfe `message`, `error` und weitere Felder)

**Betroffene Dateien:**
- `admin/views/debug/subtab-manuelle-trigger.php`

**Code-Änderung:**
```javascript
// VORHER: Crash wenn response.data nicht das erwartete Format hat
var data = response.data || {};

// NACHHER: Prüfe verschachtelte Struktur
var stats = response.data.stats || response.data || {};
```

---

### 2. Cronjob läuft stündlich statt wie konfiguriert
**Problem:** Auto-Sync Cronjob lief immer stündlich, ignorierte die eingestellte Intervall-Konfiguration (täglich, zweimal täglich).

**Ursache:**
- `wp_schedule_event()` wurde mit `time()` (aktueller Timestamp) aufgerufen
- WordPress startete Cronjob sofort, dann wiederholte er sich entsprechend Intervall
- Aber: Erster Run war immer sofort, nicht zum gewünschten Zeitpunkt
- Resultat: Nutzer dachten, Cronjob läuft stündlich

**Lösung:**
- Neue Funktion `calculate_next_run_time($interval)` berechnet korrekten Start-Zeitpunkt
- **Hourly:** Nächste volle Stunde (z.B. 09:00, 10:00)
- **Twicedaily:** Mittag (12:00) oder Mitternacht (00:00)
- **Daily:** Täglich um 3:00 Uhr morgens
- Logging: Nächster Run-Zeitpunkt wird geloggt

**Betroffene Dateien:**
- `includes/class-churchtools-suite-cron.php`

**Code-Änderung:**
```php
// VORHER: Cronjob startet sofort
wp_schedule_event(time(), $interval, 'churchtools_suite_auto_sync');

// NACHHER: Berechne nächsten Run-Zeitpunkt basierend auf Intervall
$next_run = self::calculate_next_run_time($interval);
wp_schedule_event($next_run, $interval, 'churchtools_suite_auto_sync');
```

**Neue Funktion:**
```php
private static function calculate_next_run_time($interval) {
    switch ($interval) {
        case 'hourly':
            return strtotime('+1 hour', strtotime(date('Y-m-d H:00:00')));
        case 'twicedaily':
            $current_hour = (int) date('H');
            return $current_hour < 12 
                ? strtotime('today 12:00:00') 
                : strtotime('tomorrow 00:00:00');
        case 'daily':
            return strtotime('tomorrow 03:00:00');
        default:
            return time() + HOUR_IN_SECONDS;
    }
}
```

---

## 📋 Zusammenfassung

**2 kritische Bugs behoben:**
1. ✅ JavaScript-Fehler beim manuellen Sync (response.data.stats Struktur)
2. ✅ Cronjob läuft jetzt zum korrekten Zeitpunkt (nicht mehr sofort)

**Technische Details:**
- Defensive JavaScript-Programmierung mit Null-Checks
- Intelligente Cronjob-Zeitpunkt-Berechnung
- Logging für Cronjob-Schedule (Debug-Zwecke)

---

## 🎯 Getestet

- [x] Manueller Sync zeigt korrekte Statistiken an
- [x] Kein JavaScript-Fehler mehr bei Sync
- [x] Error-Messages werden korrekt angezeigt
- [x] Cronjob startet zur korrekten Zeit (nicht sofort)
- [x] Intervall-Konfiguration wird beachtet (hourly, twicedaily, daily)

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

**WICHTIG für Cronjob-Nutzer:**
- Nach dem Update wird beim nächsten Speichern der Sync-Einstellungen der Cronjob neu geplant
- Der nächste Run wird dann zum korrekten Zeitpunkt erfolgen (nicht mehr sofort)
- **Empfehlung:** Einstellungen → Synchronisation → Speichern (triggert Cronjob-Neuplanung)

---

## 📦 Upgrade-Hinweise

**Von v0.10.1.4 → v0.10.1.5:**
- Keine Migrationen erforderlich
- Keine Datenbank-Änderungen
- Einfacher Plugin-Update über WordPress

**Cronjob manuell neu planen (optional):**
1. Einstellungen → Synchronisation
2. Intervall prüfen (hourly, twicedaily, daily)
3. Speichern → Cronjob wird neu geplant

---

**Empfehlung:** Dieser Bugfix behebt kritische Fehler im Sync-Mechanismus. Sofortiges Update wird dringend empfohlen für alle Nutzer, die den Auto-Sync verwenden.
