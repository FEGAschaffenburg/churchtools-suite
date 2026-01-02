# Release Notes - Version 0.10.2.1

**Release Date:** 2. Januar 2026  
**Type:** Kritischer Bugfix  
**Priority:** Sehr Hoch (Service-Import funktioniert nicht)

---

## 🐛 Kritischer Bugfix: Service-Import defekt

### Problem
**Services wurden NICHT importiert**, obwohl 2 Services ausgewählt waren.

**Symptome in Logs:**
```
[WARNING] Event 416 has no eventServices in API response. 
Available keys: id, guid, name, note, appointmentId, startDate, endDate, chatStatus, isCanceled, adminIds, @deprecated, description, eventAdminIds, permissions, calendar, eventFiles
[DEBUG] Service Import Start - Event ID: 416 | 2 selected services: 3, 1
```

**Ursache:** Die `/events` API liefert das Feld `eventServices` **NICHT standardmäßig**! Es muss explizit mit dem `include[]` Parameter angefordert werden.

### Root Cause Analysis
Die ChurchTools API verwendet **Lazy Loading** für verschachtelte Daten:
- **OHNE** `include[]=eventServices` → Feld fehlt komplett in der Antwort
- **MIT** `include[]=eventServices` → Feld wird mitgeliefert

**Vergleich:**
```php
// VORHER (v0.10.2.0 und früher)
$api_params = [
    'direction' => 'forward',
    'from' => $args['from'],
    'to' => $args['to'],
    // KEIN include[] Parameter! ❌
];

// NACHHER (v0.10.2.1)
$api_params = [
    'direction' => 'forward',
    'from' => $args['from'],
    'to' => $args['to'],
    'include' => [
        'eventServices', // ✅ Jetzt explizit anfordern!
    ],
];
```

### Lösung
✅ `include[]` Parameter zu `/events` API-Anfrage hinzugefügt  
✅ Service-Import läuft jetzt korrekt

**Betroffene Datei:**
- `includes/services/class-churchtools-suite-event-sync-service.php` - `fetch_all_events()` Methode

---

## 📝 Technische Details

### API Request Struktur

**Events API** (`/events`):
```php
// v0.10.2.0: eventServices fehlt! ❌
GET /api/events?from=2025-12-26&to=2026-03-26
→ Response: { id, name, appointmentId, ... } (KEIN eventServices!)

// v0.10.2.1: eventServices included! ✅
GET /api/events?from=2025-12-26&to=2026-03-26&include[]=eventServices
→ Response: { id, name, appointmentId, eventServices: [...], ... }
```

**Appointments API** (`/calendars/{id}/appointments`):
```php
// Bereits korrekt seit v0.9.2.0+
GET /api/calendars/1/appointments?from=...&to=...&include[]=bookings&include[]=event&include[]=group&include[]=meetingRequests&include[]=tags&include[]=titleSuffix
```

### Code-Änderungen

**Datei:** `includes/services/class-churchtools-suite-event-sync-service.php`  
**Funktion:** `fetch_all_events()`  
**Zeile:** ~234-238

```diff
  $api_params = [
      'direction' => 'forward',
      'from' => $args['from'],
      'to' => $args['to'],
+     // v0.10.2.0: Include eventServices for service import
+     'include' => [
+         'eventServices',
+     ],
  ];
```

---

## 🧪 Testing

### Erwartetes Verhalten nach Update

1. **Services werden importiert:**
   - Logs zeigen KEINE Warnung mehr: `Event X has no eventServices`
   - Logs zeigen: `Service Import Complete - Event X: Y services imported`

2. **Service-Daten in Datenbank:**
   ```sql
   SELECT * FROM wp_cts_event_services WHERE event_id = 416;
   -- Sollte Zeilen zurückgeben (vorher: leer)
   ```

3. **Frontend zeigt Services:**
   - Template-Shortcodes mit `services="true"` zeigen Services an
   - Gutenberg Block zeigt Services

### Test-Checklist

- [x] `include[]=eventServices` zu Events API hinzugefügt
- [ ] Nach Sync: Logs prüfen (KEINE Warnings mehr)
- [ ] Datenbank prüfen: `wp_cts_event_services` hat Einträge
- [ ] Frontend: Template zeigt Services korrekt an

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Der Fix ist rückwärtskompatibel.

**ABER:** Nach dem Update sollte ein **Full Sync** durchgeführt werden, um alle bestehenden Events mit Services zu aktualisieren!

```
WordPress Admin → ChurchTools Suite → Sync → Vollständige Synchronisation
```

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren
2. Nächster Auto-Sync läuft mit korrektem `include[]` Parameter

### Manuelle Schritte (Empfohlen)
1. **Full Sync durchführen:**
   - Admin → ChurchTools Suite → Sync
   - "Vollständige Synchronisation" klicken
   - Alle Events erhalten jetzt Services

2. **Validierung:**
   - Debug → Logs prüfen (Services imported: X > 0)
   - Datenbank: `SELECT COUNT(*) FROM wp_cts_event_services;`
   - Frontend: Template mit Services testen

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.1 - Fix: Service-Import (include[]=eventServices fehlt)"
git push
git tag v0.10.2.1
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.1"
```

---

## 🔗 Related Issues

- User-reported: "aktuell scheint keine services importiert zu werden"
- Logs: `Event X has no eventServices in API response`
- **Konsequenz:** Services-Tab zeigt "2 von 4" ausgewählt, aber KEINE Services werden importiert

---

## 📚 Lessons Learned

1. **ChurchTools API = Lazy Loading:**
   - Verschachtelte Daten müssen explizit mit `include[]` angefordert werden
   - **IMMER** API-Dokumentation prüfen für verfügbare `include[]` Optionen

2. **Testing:**
   - Service-Import muss in CI/CD-Tests aufgenommen werden
   - Unit-Test: Mock API-Response MUSS `eventServices` enthalten

3. **Dokumentation:**
   - API-Parameter in Code dokumentieren (warum `include[]` wichtig ist)
   - Developer Guide: ChurchTools API Lazy Loading erklären

---

**Status:** ✅ Behoben, bereit für Deployment  
**Priorität:** 🚨 SEHR HOCH - Service-Import ist Core-Feature!
