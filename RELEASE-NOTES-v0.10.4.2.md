# v0.10.4.2 - Bugfix: Description + Tags-Logging

## 🐛 Bugfix Release

**v0.10.4.2** - Kombinierte Description wird aktualisiert + Enhanced Tags-Logging

---

## ❌ Problem in v0.10.4.1:

### 1. Kombinierte `description` wurde nicht mehr aktualisiert
- **Problem:** Phase 2 (Appointments Sync) überschreibt nur spezifische Felder
- `description`-Spalte (kombiniert) war NICHT in der Update-Liste
- **Folge:** Events aus Phase 1 behielten kombinierte Description, aber Appointments aus Phase 2 hatten nur appointment_description

### 2. Tags waren NULL - aber warum?
- Tags sollten in Phase 2 importiert werden
- Code sah korrekt aus (`tags` in `$appointment_fields`)
- **Verdacht:** ChurchTools API returned keine Tags ODER Tags-Array ist leer

---

## ✅ Was ist jetzt gefixt:

### 1. `description` wird in Phase 2 ebenfalls aktualisiert
**Datei:** `includes/repositories/class-churchtools-suite-events-repository.php`

**Vorher:**
```php
$appointment_fields = [
    'appointment_description',
    'address_name',
    'tags',
    // ...
];
```

**Jetzt:**
```php
$appointment_fields = [
    'description', // ✅ NEU: Kombinierte description bei Appointments aktualisieren
    'appointment_description',
    'address_name',
    'tags',
    // ...
];
```

**Effekt:**
- Phase 1 (Events): Speichert `description` (Event + Appointment kombiniert)
- Phase 2 (Appointments): **Aktualisiert** `description` mit sauberer appointment_description
- **Keine doppelten Trennzeilen mehr!**

### 2. Enhanced Tags-Debug-Logging
**Datei:** `includes/services/class-churchtools-suite-event-sync-service.php`

**Wenn Tags gefunden:**
```php
ChurchTools_Suite_Logger::debug(
    'event_sync',
    sprintf('Appointment %s - Tags gefunden und normalisiert', $appointment_id),
    [
        'raw_tags' => $appointment['tags'],
        'normalized_count' => count($this->normalize_tag_colors($appointment['tags'])),
        'json_length' => strlen($tags),
    ]
);
```

**Wenn Tags NICHT gefunden:**
```php
ChurchTools_Suite_Logger::warning(
    'event_sync',
    sprintf('Appointment %s - KEINE TAGS in API-Response', $appointment_id),
    [
        'has_tags_key' => isset($appointment['tags']),
        'tags_is_array' => isset($appointment['tags']) && is_array($appointment['tags']),
        'tags_empty' => isset($appointment['tags']) ? empty($appointment['tags']) : 'NOT_SET',
        'appointment_keys' => array_keys($appointment),
    ]
);
```

**Effekt:**
- Logs zeigen jetzt GENAU, ob Tags in API-Response enthalten sind
- Hilft beim Debugging: ChurchTools sendet keine Tags vs. Code extrahiert nicht

---

## 🔍 Debugging nach dem Update:

### Nach dem Sync:
1. **Logs prüfen** (Admin → ChurchTools Suite → Erweitert → Logs)
   - Suche nach: `"Tags gefunden und normalisiert"`
   - Suche nach: `"KEINE TAGS in API-Response"`

2. **Falls "KEINE TAGS":**
   - ChurchTools API returned keine Tags
   - **Mögliche Ursachen:**
     - Tags sind nicht in ChurchTools gesetzt
     - `include=tags` fehlt in API-Request (sollte aber da sein!)
     - API-Version-Problem

3. **Falls "Tags gefunden":**
   - Tags werden korrekt extrahiert und normalisiert
   - Sollten in DB `tags`-Spalte als JSON erscheinen
   - Falls trotzdem NULL → Repository-Problem (unwahrscheinlich)

### SQL-Check:
```sql
-- Tags prüfen
SELECT id, title, tags, appointment_description, description 
FROM wp_cts_events 
WHERE appointment_id IS NOT NULL 
ORDER BY id DESC 
LIMIT 10;
```

**Erwartung:**
- `tags`: `[{"id":34,"name":"Gottesdienst","color":"#6b7280"}]` (oder NULL wenn keine Tags in ChurchTools)
- `appointment_description`: Saubere Appointment-Description
- `description`: Gleich wie `appointment_description` (kein "--- Termindetails ---")

---

## 🔄 Upgrade-Hinweise:

### Nach dem Update v0.10.4.2:
1. **Plugin updaten** (Auto-Update oder ZIP hochladen)
2. **Full Sync durchführen** (Admin → Synchronisation)
3. **Logs prüfen** (Erweitert → Logs → Suche nach "Tags" oder "KEINE TAGS")
4. **DB prüfen** (SQL-Query oben ausführen)

### Falls Tags immer noch NULL:
- **Prüfe ChurchTools:** Sind Tags bei deinen Appointments gesetzt?
- **Prüfe Logs:** Was sagen die neuen Debug-Messages?
- **API-Test:** Manueller curl-Request (wie du oben gezeigt hast) - enthalten Tags?

---

**Installation:** Plugin-ZIP hochladen oder Auto-Update nutzen.

**Support:** GitHub Issues → https://github.com/FEGAschaffenburg/churchtools-suite/issues
