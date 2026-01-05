# v0.10.4.1 - CRITICAL FIX: Tags + Description

## 🐛 Critical Bugfix Release

**v0.10.4.1** - Tags-Import korrigiert + Description-Logik gefixt

---

## ❌ Was war falsch in v0.10.4.0:

### 1. Tags wurden in Events API included
- **Problem:** Events haben KEINE Tags in ChurchTools!
- Tags gibt es nur bei **Appointments**
- Events API included fälschlicherweise `include=eventServices,tags`

### 2. Template_Data verwendete falsches description-Feld
- Verwendete: `description` (kombiniert: Event + Appointment mit Trennzeile)
- Bei **Appointments ohne Event** enthält das nur Appointment-Info
- Bei **Events mit Appointment** kombiniert mit `--- Termindetails ---`

---

## ✅ Was ist jetzt gefixt:

### 1. Tags nur in Phase 2 (Appointments API)
- **Phase 1 (Events API):** `include=eventServices` (OHNE tags)
- **Phase 2 (Appointments API):** `include=bookings,event,group,meetingRequests,tags,titleSuffix`
- Tags werden korrekt aus Appointments importiert ✅

### 2. Template_Data bevorzugt jetzt appointment_description
```php
'description' => $event['appointment_description'] ?? $event['description'] ?? ''
```
- **Bevorzugt:** `appointment_description` (appointment-spezifisch, sauber)
- **Fallback:** `description` (kombiniert, für alte Daten)

---

## 📊 Description-Felder im Detail:

### DB-Spalten (seit v0.9.1.0):
- `event_description` - Event.note (Serie/Event-Ebene)
- `appointment_description` - Appointment.note (Termin-Ebene)
- `description` - Kombiniert (deprecated, Backward Compatibility)

### Was wird importiert:

**Phase 1 (Events API):**
```php
event_description = Event.note
appointment_description = Appointment.note
description = event_description + "--- Termindetails ---" + appointment_description
```

**Phase 2 (Appointments API):**
```php
appointment_description = Appointment.subtitle + Appointment.description
description = appointment_description
```

### Was Templates jetzt nutzen:
- **Primär:** `appointment_description` (terminspezifisch, sauber)
- **Fallback:** `description` (für Kompatibilität mit alten Daten)

---

## 🔄 Upgrade-Hinweise:

### Nach dem Update:
1. **Full Sync durchführen** (Admin → Synchronisation → Events synchronisieren)
2. **Tags werden jetzt korrekt importiert** (nur aus Appointments)
3. **Descriptions sind sauber** (keine doppelten Trennzeilen mehr)

### Breaking Changes:
- Keine (Fallback auf alte `description` bleibt erhalten)

---

**Installation:** Plugin-ZIP hochladen oder Auto-Update nutzen.
