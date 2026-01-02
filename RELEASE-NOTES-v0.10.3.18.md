# Release Notes - v0.10.3.18

**Release-Datum:** 2. Januar 2026  
**Typ:** Bugfix - Erweiterte Debug-Ausgaben

---

## 🔍 Zusammenfassung

Umfassendes AJAX-Logging für Kalender-Navigation hinzugefügt. Jeder Schritt wird geloggt um den Root Cause des 500 Errors beim Monatswechsel zu identifizieren.

---

## ✨ Änderungen

### AJAX Debug Logging (CRITICAL FIX)

**Problem:**
- Kalender-Monatswechsel wirft 500 Error
- v0.10.3.15 Try-Catch half nicht (Recovery Mode)
- v0.10.3.17 Template-Loader Fix half nicht
- Root Cause unklar ohne detaillierte Logs

**Analyse:**
- WordPress Recovery Mode blockiert Request komplett
- Try-Catch greift nicht weil Fehler früher auftritt
- Internes Logging System sollte helfen Root Cause zu finden

**Lösung:**
Umfassendes Logging in `ajax_load_calendar_month()` hinzugefügt:

1. **AJAX Call Start:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'AJAX call started', [
       'POST' => $_POST,
       'nonce_isset' => isset($_POST['nonce']),
   ]);
   ```

2. **Nonce Verification:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Nonce verified');
   ```

3. **Parameter Extraction:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Parameters extracted', [
       'year' => $year,
       'month' => $month,
   ]);
   ```

4. **Date Range Calculation:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Date range calculated', [
       'from' => $from_date,
       'to' => $to_date,
       'calendar_ids' => $calendar_ids,
       'limit' => $limit,
   ]);
   ```

5. **Class Loading:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Loading Template_Loader class');
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Loading Events_Repository class');
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Loading Template_Data_Provider class');
   ```

6. **Database Query:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Fetching events from database');
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Events fetched', [
       'count' => count($raw_events),
   ]);
   ```

7. **Event Formatting:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Formatting events');
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Events formatted', [
       'count' => count($events),
   ]);
   ```

8. **Template Rendering:**
   ```php
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Rendering template', [
       'template' => 'calendar/monthly-modern.php',
       'events_count' => count($events),
       'args' => $atts,
   ]);
   ChurchTools_Suite_Logger::debug('ajax_calendar', 'Template rendered', [
       'html_length' => strlen($html),
   ]);
   ```

9. **Error Handling:**
   ```php
   ChurchTools_Suite_Logger::error('ajax_calendar', 'AJAX Calendar Error', [
       'error' => $e->getMessage(),
       'trace' => $e->getTraceAsString(),
   ]);
   ```

10. **Enhanced Error Response:**
    ```php
    wp_send_json_error([
        'message' => 'Fehler beim Laden des Kalenders',
        'error' => WP_DEBUG ? $e->getMessage() : '',
        'trace' => WP_DEBUG ? $e->getTraceAsString() : '',
    ]);
    ```

**Debugging-Strategie:**
1. User testet Kalender-Navigation erneut
2. Letzter erfolgreicher Log-Eintrag zeigt exakt wo Fehler auftritt:
   - Wenn "AJAX call started" fehlt: Request kommt nicht an
   - Wenn "Nonce verified" fehlt: Nonce-Problem
   - Wenn "Events fetched" fehlt: Database-Problem
   - Wenn "Template rendered" fehlt: Template-Problem
3. Log-Level: `debug` (muss in Settings aktiviert sein)
4. Kategorie: `ajax_calendar`

**Logs abrufen:**
- **Admin:** Debug → Logs → Filter "ajax_calendar"
- **Datei:** `/wp-content/uploads/churchtools-suite/debug.log`
- **Browser:** Response enthält Error + Trace wenn WP_DEBUG aktiv

---

## 🔧 Technische Details

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php**
- `ajax_load_calendar_month()` - 15 neue Debug-Statements
- Logging an JEDEM kritischen Punkt
- Error-Trace in JSON Response (wenn WP_DEBUG aktiv)

### Logger-Nutzung

**ChurchTools_Suite_Logger API:**
```php
ChurchTools_Suite_Logger::debug($category, $message, $context);
ChurchTools_Suite_Logger::error($category, $message, $context);
```

**Kategorien:**
- `ajax_calendar` - Alle Kalender-AJAX Requests

**Context-Daten:**
- POST-Parameter
- Berechnete Werte
- Array-Counts
- Error-Details

---

## 🎯 Erwartete Resultate

1. **Logs zeigen exakten Fehlerpunkt:**
   - Letzter erfolgreicher Log-Eintrag = direkt vor Fehler
   - Context-Daten helfen Root Cause zu verstehen

2. **Mögliche Fehlerquellen identifizieren:**
   - Class-Loading schlägt fehl
   - Database-Query crasht
   - Template-Rendering crasht
   - Memory Limit überschritten

3. **Fix basierend auf Logs:**
   - Je nachdem WO es crasht, wird passender Fix entwickelt

---

## 📊 Testing

### Testschritte:
1. Plugin auf v0.10.3.18 aktualisieren
2. Debug-Modus aktivieren (Settings → Debug-Einstellungen)
3. Kalender-Navigation testen (Monatswechsel)
4. Debug-Tab öffnen → Logs → Filter "ajax_calendar"
5. Letzte 10-15 Log-Einträge analysieren

### Erwartetes Verhalten:
- Bei Erfolg: Alle Debug-Statements bis "Template rendered"
- Bei Fehler: Letzter erfolgreicher Log zeigt Fehlerpunkt

---

## 🔄 Migration von v0.10.3.17

Keine Datenbank-Änderungen. Nur Code-Update.

**Update-Prozess:**
1. Plugin aktualisieren (Auto-Update oder ZIP)
2. Debug-Modus aktivieren falls noch nicht geschehen
3. Kalender testen und Logs prüfen

---

## 🐛 Bekannte Probleme

**Kalender-500-Error (UNDER INVESTIGATION):**
- v0.10.3.15: Try-Catch half nicht
- v0.10.3.17: Template-Loader korrigiert, half nicht
- **v0.10.3.18:** Umfassendes Logging um Root Cause zu finden

**Nächste Schritte:**
1. Logs analysieren
2. Exakten Fehlerpunkt identifizieren
3. Spezifischen Fix entwickeln
4. v0.10.3.19 mit finalem Fix

---

## 📝 Kontext

**Bug-Historie:**
- v0.10.2.7: Kalender-Navigation eingeführt
- v0.10.3.15: Try-Catch Error Handling (half nicht)
- v0.10.3.17: Template-Loader Fix (half nicht)
- **v0.10.3.18:** Debug-Logging (Find Root Cause)

**Warum Logging?**
- WordPress Recovery Mode blockiert normale Error-Behandlung
- Try-Catch greift nicht weil Fehler früher auftritt
- Internes Logging läuft VOR Recovery Mode
- Zeigt exakt wo Code crasht

**Root Cause Hypothesen:**
1. ❓ Template-Datei hat PHP-Fehler
2. ❓ Events-Formatierung crasht
3. ❓ Database-Query crasht
4. ❓ Class-Loading schlägt fehl
5. ❓ Memory Limit überschritten

**Logs werden Hypothese beweisen/widerlegen!**

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.18
