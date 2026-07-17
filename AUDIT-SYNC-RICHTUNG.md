# AUDIT: WordPress ↔ ChurchTools Synchronisation (v1.2.2.0)

## 🔴 KRITISCHES FINDING: NUR EINSEITIGE SYNCHRONISATION!

---

## 1. SYNCHRONISATIONS-ARCHITEKTUR

### Datenstruktur:
```
ChurchTools API
      ↓
      ├─ Phase 1: Events API → Extract → wp_cts_events Tabelle
      ├─ Phase 2: Appointments API → Extract → wp_cts_events Tabelle
      └─ Phase 3: Deletion Detection → DELETE FROM wp_cts_events

WordPress Frontend
      ↓
      └─ Liest Daten aus wp_cts_events (Shortcodes)

WordPress Admin
      ↓
      └─ Verwaltet nur Kalender-Auswahl, nicht Events selbst!
```

### Richtung:
```
❌ WordPress → ChurchTools: NICHT IMPLEMENTIERT
✅ ChurchTools → WordPress: VOLLSTÄNDIG IMPLEMENTIERT
```

---

## 2. WAS IST EINE EVENT?

### In WordPress:
```php
// Events sind NICHT WordPress Posts!
// Events sind eigene Datenbankeinträge

wp_cts_events Tabelle:
├─ id (int)
├─ event_id (ChurchTools Event ID)
├─ appointment_id (ChurchTools Appointment ID)
├─ title, description
├─ start_datetime, end_datetime
├─ location_name, address_*
├─ tags, status
├─ raw_payload (vollständige API Response)
└─ last_modified, appointment_modified
```

### WICHTIG:
🔴 **Events sind NICHT im WordPress Admin bearbeitbar!**
- Es gibt **KEINE** Event-Edit-Seite in WordPress
- Es gibt **KEINE** Event-Lösch-Funktionalität in WordPress Admin
- Events werden **NUR durch Sync aktualisiert/gelöscht**

---

## 3. LÖSCHEN: RICHTUNG UND VERHALTEN

### Szenario A: Termin wird in ChurchTools gelöscht ✅

```
ChurchTools: Termin wird gelöscht
      ↓
Nächste Sync (Phase 3):
- Local Row: appointment_id=123|start_datetime=2026-07-17 10:30
- API Keys: [List ohne 123|2026-07-17 10:30]
- Vergleich: Local Key ist nicht in API
      ↓
DELETE FROM wp_cts_events WHERE id = ?
      ↓
WordPress: Event ist weg ✅
```

**Status**: ✅ FUNKTIONIERT

### Szenario B: Event wird in WordPress Admin gelöscht ❌

```
WordPress Admin: "Termin löschen" Button
      ↓
Ergebnis: BUTTON EXISTIERT NICHT!
      ↓
Benutzer kann nicht löschen
```

**Status**: ❌ NICHT MÖGLICH (kein Admin-UI)

### Szenario C: User löscht direkt aus Datenbank ⚠️

```
Admin löscht Eintrag aus wp_cts_events (SQL)
      ↓
Nächste Sync:
- Termin ist in ChurchTools noch vorhanden
- Termin ist nicht in wp_cts_events
- Sync lädt ihn wieder!
      ↓
Event kommt zurück! ⚠️
```

**Status**: ⚠️ TERMIN KOMMT WIEDER ZURÜCK (Smart Sync verhindert Daten-Verlust)

---

## 4. ÄNDERUNGEN/UPDATES: RICHTUNG

### Szenario A: Termin wird in ChurchTools geändert ✅

```
ChurchTools: 
- Title: "Gottesdienst" → "Predigt"
- Start: 10:00 → 09:30
      ↓
Nächste Sync (Phase 1 + Phase 2):
- Extract neue Daten
- Upsert nach Composite Key
- UPDATE wp_cts_events SET title='Predigt', start_datetime='09:30'
      ↓
WordPress: Zeigt neue Daten ✅ (nach unserem v1.2.2.0 FIX!)
```

**Status**: ✅ FUNKTIONIERT (nach unserem Latest Fix)

### Szenario B: Event wird in WordPress Admin geändert ❌

```
WordPress Admin: "Event bearbeiten" Button
      ↓
Ergebnis: BUTTON EXISTIERT NICHT!
      ↓
Benutzer kann nicht bearbeiten
```

**Status**: ❌ NICHT MÖGLICH (kein Admin-UI)

### Szenario C: Datenbank wird direkt geändert ⚠️

```
Admin ändert wp_cts_events direkt (SQL)
      ↓
Nächste Sync:
- ChurchTools hat noch alte Daten
- Sync führt UPDATE durch
- Lokale Änderung wird ÜBERSCHRIEBEN!
      ↓
Änderung ist weg! ⚠️
```

**Status**: ⚠️ ÄNDERUNG WIRD ÜBERSCHRIEBEN (Smart Sync hat ChurchTools als Master)

---

## 5. DATENFLUSS IM DETAIL

### INSERT (Neuer Termin):
```
ChurchTools: Neuer Termin "Gottesdienst" am 17.07.2026
      ↓
Phase 1 ODER Phase 2:
- extract_event_data() / extract_appointment_data()
- upsert_by_appointment_id()
  └─ Prüfe: SELECT WHERE appointment_id=123 AND start_datetime='17.07.2026 10:30'
  └─ Nicht gefunden → INSERT
      ↓
WordPress: Event ist jetzt in wp_cts_events ✅
```

### UPDATE (Termin geändert):
```
ChurchTools: Ändere Termin-Titel und Start-Zeit
      ↓
Phase 1 ODER Phase 2:
- extract_event_data() / extract_appointment_data()
- upsert_by_appointment_id()
  └─ Prüfe: SELECT WHERE appointment_id=123 AND start_datetime='17.07.2026 10:30'
  └─ Gefunden → UPDATE (v1.2.2.0: Jetzt alle Felder! ✅)
      ↓
WordPress: Event hat neue Werte ✅
```

### DELETE (Termin gelöscht):
```
ChurchTools: Termin gelöscht
      ↓
Phase 3 - detect_deleted_events():
- Baue Composite Keys aus API Response
- Vergleiche mit lokalen Rows
- Termin ist NICHT in API
  └─ DELETE FROM wp_cts_events WHERE id=?
      ↓
WordPress: Event ist weg ✅
```

---

## 6. ÄNDERUNGEN-ÜBERWACHUNG (Welche Felder werden gesynced?)

### INSERT-Zeit:
```php
// ALLE Felder werden eingefügt:
$fields = [
    'event_id', 'appointment_id', 'calendar_id',
    'title', 'description', 'event_description', 'appointment_description',
    'start_datetime', 'end_datetime', 'is_all_day',
    'location_name', 'address_*' (7 Felder),
    'tags', 'status', 'image_*' (2 Felder),
    'raw_payload', 'last_modified', 'appointment_modified'
];
```

### UPDATE-Zeit (v1.2.2.0 - NACH UNSEREM FIX):
```php
// Diese Felder werden aktualisiert:
$updateable_fields = [
    'title',                    // ✅ JA (FIX v1.2.2.0)
    'event_id',                 // ✅ JA (FIX v1.2.2.0)
    'end_datetime',             // ✅ JA (FIX v1.2.2.0)
    'is_all_day',               // ✅ JA (FIX v1.2.2.0)
    'location_name',            // ✅ JA (FIX v1.2.2.0)
    'event_description',        // ✅ JA (FIX v1.2.2.0)
    'last_modified',            // ✅ JA (FIX v1.2.2.0) - KRITISCH!
    
    'description',              // ✅ JA
    'appointment_description',  // ✅ JA
    'address_name',             // ✅ JA
    'address_street',           // ✅ JA
    'address_zip',              // ✅ JA
    'address_city',             // ✅ JA
    'address_latitude',         // ✅ JA
    'address_longitude',        // ✅ JA
    'tags',                     // ✅ JA
    'image_attachment_id',      // ✅ JA
    'image_url',                // ✅ JA
    'appointment_modified',     // ✅ JA
    'raw_payload',              // ✅ JA
    'status',                   // ✅ JA
    'updated_at'                // ✅ JA (automatisch)
];

// Diese Felder werden NICHT überschrieben:
$non_updateable_fields = [
    'appointment_id',   // ← Teil des Composite Keys!
    'calendar_id',      // ← Ändert sich nicht
    'start_datetime'    // ← Teil des Composite Keys!
];
```

---

## 7. COMPOSITE KEY FUNKTIONIERT SO:

```php
// Eindeutige Kennung pro Event:
$composite_key = $appointment_id . '|' . $start_datetime

Beispiel:
  appointment_id = "12345"
  start_datetime = "2026-07-17 10:30:00"
  composite_key = "12345|2026-07-17 10:30:00"

Verwendung:
  - Phase 1: Baue Key und nutze zum Upsert
  - Phase 2: Baue Key und nutze zum Upsert
  - Phase 3: Vergleiche Keys - Fehl-Löschungen unmöglich!
```

### Warum Composite Key?
```
Szenario: Wiederkehrende Ereignisse (z.B. wöchentlicher Gottesdienst)

Nur appointment_id würde nicht reichen:
  - 01.07.2026 10:00 Gottesdienst
  - 08.07.2026 10:00 Gottesdienst
  - 15.07.2026 10:00 Gottesdienst
  └─ Alle haben GLEICHE appointment_id!
  └─ Nur appointment_id würde ALLE löschen, wenn eine gelöscht wird!

Mit Composite Key:
  - "123|2026-07-01 10:00" ← Erste Instanz
  - "123|2026-07-08 10:00" ← Zweite Instanz
  - "123|2026-07-15 10:00" ← Dritte Instanz
  └─ Jede wird einzeln behandelt ✅
```

---

## 8. INCREMENTAL SYNC (Nur geänderte Termini laden)

### Wie es funktioniert:
```
Normale Sync: GET /api/events?from=2026-06-10&to=2026-10-17
↓
Incremental Sync: GET /api/events?from=2026-06-10&to=2026-10-17&modified_after=2026-07-17T12:00:00
↓
API gibt nur Events zurück, die NACH 2026-07-17T12:00:00 geändert wurden
```

### Wo wird `modified_after` gespeichert?
```php
// In wp_cts_events:
last_modified = "2026-07-17 12:00:00"  ← Speichert ChurchTools API Änderungszeit

// Bei nächster Sync:
modified_after = last_modified
```

### FIX in v1.2.2.0:
```
VOR FIX:
- INSERT: last_modified wird gespeichert ✅
- UPDATE: last_modified wird NICHT aktualisiert ❌
  └─ Folge: Incremental Sync funktioniert nicht nach erstem Sync!

NACH FIX (v1.2.2.0):
- INSERT: last_modified wird gespeichert ✅
- UPDATE: last_modified wird aktualisiert ✅
  └─ Incremental Sync funktioniert jetzt korrekt!
```

---

## 9. FEHLERSZENARIEN & HANDLING

### Szenario 1: Termin in ChurchTools gelöscht, aber lokal ist kein Deletion möglich
**Lösung**: Phase 3 löscht automatisch ✅

### Szenario 2: API gibt ungültige Daten zurück
**Lösung**: `extract_event_data()` validiert und returned WP_Error ✅

### Szenario 3: Netzwerkfehler während Sync
**Lösung**: Phase 3 wird übersprungen, Daten bleiben erhalten ✅

### Szenario 4: Lokale Datenbank wird manuell geändert
**Lösung**: Nächste Sync überschreibt manuelle Änderungen (ChurchTools ist Master) ✅

### Szenario 5: Benutzer versucht Event im WordPress Admin zu löschen
**Lösung**: KEINE Admin-UI vorhanden - unmöglich ✅

---

## 10. ZUSAMMENFASSUNG

### Was funktioniert:
| Aktion | ChurchTools | WordPress | Status |
|--------|-------------|-----------|--------|
| **Neuer Termin** | Wird automatisch synced | - | ✅ |
| **Termin ändern** (Titel/Zeit/Ort) | Wird automatisch synced | - | ✅ (nach v1.2.2.0 Fix!) |
| **Termin löschen** | Wird automatisch erkannt und gelöscht | - | ✅ |
| **Termin bearbeiten** | - | NICHT MÖGLICH | ❌ |
| **Termin löschen** | - | NICHT MÖGLICH | ❌ |
| **Nur ChurchTools ist Master** | - | - | ✅ |

### Sicherheit:
- ✅ Composite Key verhindert Fehl-Löschungen
- ✅ last_modified verhindert Daten-Verlust bei Incremental Sync
- ✅ ChurchTools als Master verhindert Versehentliche Änderungen
- ✅ Alle Daten werden vollständig gespeichert (raw_payload)

### Performance (nach v1.2.2.0 Fix):
- ✅ Incremental Sync funktioniert korrekt
- ✅ Nur geänderte Termini werden geladen
- ✅ Deduplizierung via Composite Key sehr schnell

---

## Status nach v1.2.2.0: 🟢 SAUBER

**Alle kritischen Felder werden nun korrekt synchronized!**

### Was v1.2.2.0 Fixed:
1. ✅ title wird bei UPDATE synchronisiert
2. ✅ end_datetime wird bei UPDATE synchronisiert  
3. ✅ last_modified wird bei UPDATE synchronisiert (Incremental Sync funktioniert!)
4. ✅ event_description wird bei UPDATE synchronisiert
5. ✅ location_name wird bei UPDATE synchronisiert
6. ✅ is_all_day wird bei UPDATE synchronisiert

**Resultat**: Vollständige Synchronisation von ChurchTools → WordPress ✅
