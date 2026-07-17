# AUDIT: ChurchTools → WordPress Updates (v1.2.2.0)

## 1. UPDATE-STRATEGIE - ÜBERBLICK ✅

### Workflow:
```
Phase 1: Events API        →  extract_event_data()        →  upsert_by_appointment_id()  ✅
Phase 2: Appointments API  →  extract_appointment_data()   →  upsert_by_appointment_id()  ✅
Phase 3: Deletion Detection →  detect_deleted_events()      →  DELETE from table          ✅
```

---

## 2. COMPOSITE KEY LOGIK ✅ SAUBER

### Key-Konstruktion:
```php
$composite_key = $appointment_id . '|' . $start_datetime
Beispiel: "12345|2026-07-17 10:30:00"
```

### Verwendet an 3 Stellen:
1. **Phase 1** (Line 442): Building key während Event-Processing
2. **Phase 2** (Line 682): Building key während Appointment-Processing  
3. **Phase 3** (Line 530-570): Vergleich lokaler Rows mit API Keys

### Prüfung: ✅ KORREKT
- Format consistent: `appointment_id|start_datetime`
- Format-Konvertierung: `format_datetime()` normalisiert zu MySQL format
- Fallback-Logik: Wenn appointment_id/start_datetime leer → Key ist ''
- Phase 3 ignoriert leere Keys → kein Problem bei fehlenden Daten

---

## 3. INSERT vs UPDATE LOGIK ✅ ROBUST

### Datenbankoperation in `upsert_by_appointment_id()`:

```php
// STEP 1: Check by COMPOSITE KEY
SELECT id WHERE appointment_id = ? AND start_datetime = ?

// STEP 2a: EXISTS → SELECTIVE UPDATE
UPDATE wp_cts_events SET [SELECTIVE FIELDS] WHERE id = ?

// STEP 2b: NOT EXISTS → INSERT
INSERT INTO wp_cts_events VALUES (...)
```

### Prüfung: ✅ FUNKTIONIERT
- Composite Key Prüfung ist sauber
- UPDATE wird korrekt priorisiert vor INSERT
- Selective Update verhindert Datenüberschreibungen

---

## 4. SELECTIVE UPDATE - FELDER ⚠️ ÜBERPRÜFUNG NÖTIG

### Welche Felder werden bei UPDATE überschrieben:

```php
$appointment_fields = [
    'description',              // ✅ Kombinierte Description (neu v0.10.4.2)
    'appointment_description',  // ✅ Appointment-Level (neu v0.9.1.0)
    'address_name',            // ✅ Ort-Name/meetingAt (neu v0.9.2.0)
    'address_street',          // ✅ Straße (neu v0.9.2.0)
    'address_zip',             // ✅ PLZ (neu v0.9.2.0)
    'address_city',            // ✅ Stadt (neu v0.9.2.0)
    'address_latitude',        // ✅ GPS Breitengrad (neu v0.9.2.0)
    'address_longitude',       // ✅ GPS Längengrad (neu v0.9.2.0)
    'tags',                    // ✅ JSON Array (neu v0.9.2.0)
    'image_attachment_id',     // ✅ WordPress Anhang ID (neu v0.10.5.0)
    'image_url',               // ✅ Fallback URL (neu v0.10.5.0)
    'appointment_modified',    // ✅ Änderungszeit (neu v0.8.1.0)
    'raw_payload',             // ✅ Vollständige API Response
    'status',                  // ✅ Status (active/inactive)
    'updated_at',              // ✅ Lokale Update-Zeit
];
```

### Welche Felder werden NICHT überschrieben:

```php
// PROBLEM: Diese Felder bleiben unverändert!
'event_id'              // ✗ Wird nicht aktualisiert
'calendar_id'           // ✗ Wird nicht aktualisiert
'appointment_id'        // ✗ Wird nicht aktualisiert (Teil des Keys!)
'title'                 // ✗ Wird NICHT aktualisiert!
'start_datetime'        // ✗ Wird NICHT aktualisiert (Teil des Keys!)
'end_datetime'          // ✗ Wird NICHT aktualisiert!
'is_all_day'            // ✗ Wird NICHT aktualisiert!
'location_name'         // ✗ Wird NICHT aktualisiert!
'event_description'     // ✗ Wird NICHT aktualisiert!
'last_modified'         // ✗ Wird NICHT aktualisiert!
```

### WARNUNG ⚠️: 
Diese Felder werden NICHT sync'd bei Updates:
- **title**: Wenn Terminname in ChurchTools geändert wird, wird die WordPress-Kopie NICHT aktualisiert!
- **end_datetime**: Wenn Endzeit geändert wird, wird die WordPress-Kopie NICHT aktualisiert!
- **location_name**: Wenn Ort-String geändert wird, wird die WordPress-Kopie NICHT aktualisiert!
- **event_description**: Wenn Event-Description geändert wird, wird sie NICHT aktualisiert!

---

## 5. LÖSCHLOGIK (PHASE 3) ✅ SAUBER

### Ablauf:
```
1. Hole alle lokalen Rows aus Datum-Range + Calendar
2. Baue Set aus API Composite Keys (Phase 1 + Phase 2)
3. Für jede lokale Row:
   - Baue lokalen Key: appointment_id|start_datetime
   - Wenn Key NICHT in API-Set: LÖSCHE aus Datenbank
4. Clean linked services vor Delete (event_services table)
```

### Prüfung: ✅ FUNKTIONIERT SAUBER
- Keine Fehl-Löschungen möglich (Composite Key ist präzise)
- Services werden sauber vorher gelöscht
- Logging ist detailliert

---

## 6. EDGE CASES & POTENZIELLE BUGS

### ✅ SAUBER BEHANDELT:
1. **Recurring Events**: Composite Key verhindert Duplikate
2. **Standalone Appointments** (Phase 2 only): Wird als separate Rows behandelt
3. **Deleted Events in ChurchTools**: Phase 3 erkennt sie und löscht
4. **API Struktur-Variationen**: Multiple Fallback-Pfade in extract_*()

### ⚠️ POTENZIELLE PROBLEME:

#### Problem 1: Title wird nicht aktualisiert
**Symptom**: Terminname in ChurchTools geändert, aber WordPress zeigt alten Namen
**Ursache**: `title` ist nicht in $appointment_fields
**Impact**: KRITISCH für Frontend
**Fix**: title zu appointment_fields hinzufügen

#### Problem 2: end_datetime wird nicht aktualisiert
**Symptom**: Endzeit in ChurchTools geändert, aber WordPress zeigt alte Zeit
**Ursache**: `end_datetime` ist nicht in $appointment_fields
**Impact**: KRITISCH für Zeitanzeige
**Fix**: end_datetime zu appointment_fields hinzufügen

#### Problem 3: event_description wird bei Phase 2 nicht übernommen
**Symptom**: Event hat zwei Descriptions, aber Appointment-Description wird nicht verwendet
**Ursache**: Selective Update berücksichtigt event_description nicht
**Impact**: MITTEL - nur bei Multi-Description Szenarien

#### Problem 4: Kein Re-Import bei bereits importiertem Bild
**Symptom**: Bild in ChurchTools geändert, aber WordPress zeigt altes Bild
**Ursache**: `image_attachment_id` wird nur aktualisiert wenn URL neu ist
**Fix**: Bild-URL Vergleich implementieren

#### Problem 5: last_modified wird nicht aktualisiert
**Symptom**: Incremental Sync funktioniert nicht korrekt
**Ursache**: `last_modified` ist nicht in $appointment_fields
**Impact**: KRITISCH für Incremental Sync
**Fix**: last_modified zu appointment_fields hinzufügen

---

## 7. INKREMENTELLE SYNC (modified_after) ⚠️ ÜBERPRÜFUNG

### Logik:
```php
// Phase 1: Suche Events nach letzter Änderung
/api/events?from=...&to=...&modified_after=2026-07-17T10:00:00
```

### Prüfung: ❌ PROBLEM GEFUNDEN

**Issue**: `last_modified` wird in `extract_event_data()` extrahiert (Line 863-870), aber:
1. Wird in INSERT eingespeichert ✅
2. Wird in UPDATE NICHT synchronisiert ❌ (nicht in $appointment_fields)

**Folge**: Nach erstem Sync bleibt `last_modified` unverändert, obwohl Event in ChurchTools aktualisiert wurde

**Impact**: Incremental Sync bricht nach wenigen Durchläufen
- Erste Sync: last_modified = API value
- Zweite Sync: last_modified bleibt alt, wird nicht aktualisiert
- Dritte Sync: modified_after Parameter wird mit alten Daten gebaut
- Ergebnis: Veraltete Events werden als "ungeändert" erkannt

---

## 8. FEHLERBEHANDLUNG ✅ ROBUST

### Logged Errors:
- ✅ Missing appointment_id
- ✅ Missing start_datetime
- ✅ UPDATE database errors
- ✅ Composite key conflicts
- ✅ Image import failures

### Handled WP_Errors:
- ✅ extract_event_data() returns WP_Error
- ✅ extract_appointment_data() returns WP_Error
- ✅ Database operations tracked

---

## 9. ZUSAMMENFASSUNG

### Was funktioniert sauber:
✅ Composite Key Logik (Duplikate verhindert)
✅ Löschprüfung (Phase 3)
✅ Address/Tags/Bilder werden aktualisiert
✅ Fehlerbehandlung robust
✅ Service-Deletion sauber

### Was NICHT funktioniert:
⚠️ **title** wird nicht aktualisiert
⚠️ **end_datetime** wird nicht aktualisiert
⚠️ **event_description** wird nicht aktualisiert
⚠️ **last_modified** wird nicht aktualisiert (Incremental Sync bricht!)
⚠️ **location_name** wird nicht aktualisiert
⚠️ Bild-Update-Erkennung (bei URL-Änderung)

### Kritikalität:
🔴 **KRITISCH**: last_modified (Incremental Sync funktioniert nicht)
🟡 **HOCH**: title, end_datetime (Frontend zeigt veraltete Daten)
🟡 **MITTEL**: event_description, location_name

---

## 10. EMPFOHLENE FIXES

### Fix 1: Alle UPDATE-Felder erweitern (KRITISCH)
File: `includes/repositories/class-churchtools-suite-events-repository.php` Line 95

```php
$appointment_fields = [
    'title',                        // ← HINZUFÜGEN
    'event_id',                     // ← HINZUFÜGEN
    'end_datetime',                 // ← HINZUFÜGEN
    'is_all_day',                   // ← HINZUFÜGEN
    'location_name',                // ← HINZUFÜGEN
    'event_description',            // ← HINZUFÜGEN
    'last_modified',                // ← HINZUFÜGEN (CRITICAL für Incremental Sync!)
    
    // Vorhandene Felder
    'description',
    'appointment_description',
    'address_name',
    // ... rest
];
```

### Fix 2: last_modified IMMER aktualisieren
```php
// Nach Phase 1/2, vor upsert:
if (!empty($event_data['last_modified'])) {
    $event_data['last_modified'] = $this->format_datetime($event_data['last_modified']);
}
```

### Fix 3: Bild-URL Vergleich
```php
if ($existing && $existing->image_url !== $external_image_url) {
    // Bild hat sich geändert → Re-Import
}
```

---

## Status: 
🔴 **NICHT SAUBER** - Critical Fields werden nicht synced!
