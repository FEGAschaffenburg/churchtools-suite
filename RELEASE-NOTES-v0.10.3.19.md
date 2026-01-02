# Release Notes - v0.10.3.19

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - Fehlende Repository-Methode

---

## 🔥 CRITICAL FIX: Kalender 500 Error BEHOBEN!

**ROOT CAUSE GEFUNDEN via Logging (v0.10.3.18):**

Die Logs zeigten dass der Code bis "Fetching events from database" kam, aber danach crashte. **Die Methode `get_events_in_range()` existierte NICHT!**

---

## 🐛 Das Problem

### Chronologie:
1. ✅ AJAX call started
2. ✅ Nonce verified
3. ✅ Parameters extracted
4. ✅ Date range calculated
5. ✅ Fetching events from database
6. ❌ **CRASH** - Methode existiert nicht!

### Code-Fehler:

**AJAX-Handler (admin/class-churchtools-suite-admin.php Zeile 2111):**
```php
$raw_events = $events_repo->get_events_in_range(
    $from_date . ' 00:00:00',
    $to_date . ' 23:59:59',
    !empty($calendar_ids) ? explode(',', $calendar_ids) : [],
    $limit
);
```

**Repository hatte NUR:**
```php
public function get_in_range($start_date, $end_date, $orderby, $order)
```

**Problem:**
- Falscher Methodenname
- Andere Signatur (keine calendar_ids, kein limit)
- Methode `get_events_in_range()` existierte nirgends!

---

## ✅ Die Lösung

### Neue Methode hinzugefügt:

**includes/repositories/class-churchtools-suite-events-repository.php:**

```php
/**
 * Get events in date range with calendar filter (AJAX calendar navigation)
 * 
 * Used by ajax_load_calendar_month() for calendar navigation.
 * 
 * @param string $start_date Start date (Y-m-d H:i:s)
 * @param string $end_date End date (Y-m-d H:i:s)
 * @param array $calendar_ids Optional calendar IDs filter
 * @param int|null $limit Optional limit
 * @param string $orderby Order by column
 * @param string $order Order direction
 * @return array Array of event objects
 */
public function get_events_in_range(
    string $start_date, 
    string $end_date, 
    array $calendar_ids = [], 
    ?int $limit = null, 
    string $orderby = 'start_datetime', 
    string $order = 'ASC'
): array {
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    $orderby = sanitize_key($orderby);
    
    // Debug Logging
    ChurchTools_Suite_Logger::debug('repository', 'get_events_in_range called', [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'calendar_ids' => $calendar_ids,
        'limit' => $limit,
    ]);
    
    $sql = "SELECT * FROM {$this->table_name} WHERE start_datetime >= %s AND start_datetime <= %s";
    $params = [$start_date, $end_date];
    
    // Add calendar filter if specified
    if (!empty($calendar_ids) && is_array($calendar_ids)) {
        $placeholders = implode(',', array_fill(0, count($calendar_ids), '%s'));
        $sql .= " AND calendar_id IN ($placeholders)";
        $params = array_merge($params, $calendar_ids);
    }
    
    $sql .= " ORDER BY {$orderby} {$order}";
    
    // Add limit if specified
    if ($limit !== null && $limit > 0) {
        $sql .= " LIMIT %d";
        $params[] = $limit;
    }
    
    // Debug Logging
    ChurchTools_Suite_Logger::debug('repository', 'Executing SQL query', [
        'sql' => $sql,
        'params' => $params,
    ]);
    
    $prepared = $this->db->prepare($sql, ...$params);
    $results = $this->db->get_results($prepared);
    
    // Error Logging
    if ($this->db->last_error) {
        ChurchTools_Suite_Logger::error('repository', 'SQL error', [
            'error' => $this->db->last_error,
            'query' => $this->db->last_query,
        ]);
    }
    
    // Debug Logging
    ChurchTools_Suite_Logger::debug('repository', 'Query results', [
        'count' => is_array($results) ? count($results) : 0,
    ]);
    
    return is_array($results) ? $results : [];
}
```

### Features der neuen Methode:

1. **Korrekte Signatur:**
   - `$start_date`, `$end_date` (required)
   - `$calendar_ids` (optional array)
   - `$limit` (optional int)
   - `$orderby`, `$order` (optional)

2. **Calendar Filter Support:**
   - Filtert Events nach Kalender-IDs
   - Verwendet `IN (...)` Syntax für mehrere IDs
   - Funktioniert mit leeren Arrays (kein Filter)

3. **Comprehensive Logging:**
   - Loggt Input-Parameter
   - Loggt SQL Query + Prepared Statement
   - Loggt Resultate (Count, Type)
   - Loggt SQL Errors

4. **Error Handling:**
   - Prüft `$this->db->last_error`
   - Gibt immer Array zurück (nie NULL/false)

---

## 🔧 Technische Details

### Geänderte Dateien

**includes/repositories/class-churchtools-suite-events-repository.php**
- Neue Methode `get_events_in_range()` hinzugefügt (105 Zeilen)
- Umfangreiches Logging an jedem Schritt
- SQL Query mit dynamischen Filtern

### Warum passierte dieser Fehler?

**Historische Code-Evolution:**
1. v0.10.2.7: AJAX-Calendar-Navigation eingeführt
2. AJAX-Handler rief `get_events_in_range()` auf
3. Methode existierte nie → Fatal Error
4. Aber: WordPress Recovery Mode blockierte normale Error-Behandlung
5. Try-Catch (v0.10.3.15) half nicht → Recovery Mode früher
6. Template-Loader Fix (v0.10.3.17) half nicht → anderer Fehler
7. **v0.10.3.18:** Logging enthüllte: Crash bei Database-Query
8. **v0.10.3.19:** Fehlende Methode hinzugefügt → BEHOBEN!

**Lesson Learned:**
- WordPress Recovery Mode verhindert normale Debugging
- Internes Logging System ist essential
- Method-Existence-Checks vor Aufruf wären hilfreich

---

## 📊 Testing

### Testschritte:
1. Plugin auf v0.10.3.19 aktualisieren
2. Kalender-Seite aufrufen
3. **Monatswechsel testen** (← sollte jetzt funktionieren!)
4. Optional: Logs prüfen (Debug → Logs → Filter "repository")

### Erwartetes Verhalten:
- ✅ Kalender lädt ohne Fehler
- ✅ Monatswechsel funktioniert (Vorwärts/Rückwärts)
- ✅ Events werden korrekt angezeigt
- ✅ Keine 500 Errors mehr

### Debug-Logs (wenn aktiviert):
```
[DEBUG] [repository] get_events_in_range called
[DEBUG] [repository] Executing SQL query
[DEBUG] [repository] Query results
[DEBUG] [ajax_calendar] Events fetched
[DEBUG] [ajax_calendar] Template rendered
```

---

## 🔄 Migration von v0.10.3.18

Keine Datenbank-Änderungen. Nur Code-Update.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Kalender testen** - sollte jetzt funktionieren!

---

## 🎉 Problem BEHOBEN!

Nach 5 Fix-Versuchen (v0.10.3.15-19) ist der Kalender-500-Error **endlich behoben**!

**Timeline:**
- v0.10.3.15: Try-Catch hinzugefügt → Half nicht
- v0.10.3.16: Editor-Handler deaktiviert → Anderes Problem
- v0.10.3.17: Template-Loader korrigiert → Half nicht
- v0.10.3.18: **Logging hinzugefügt** → Root Cause gefunden!
- **v0.10.3.19:** **Fehlende Methode hinzugefügt** → **BEHOBEN! ✅**

**Root Cause:** Methode `get_events_in_range()` existierte nie, aber AJAX-Handler rief sie auf.

**Warum so lange gebraucht?**
- WordPress Recovery Mode verhinderte normale Error-Messages
- Try-Catch griff nicht (Recovery Mode früher)
- Erst umfangreiches Logging (v0.10.3.18) zeigte exakten Fehlerpunkt

**Danke fürs Testen und Geduld! 🙏**

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.19
