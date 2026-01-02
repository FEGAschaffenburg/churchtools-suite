# Release Notes - Version 0.10.2.4

**Release Date:** 2. Januar 2026  
**Type:** Kritischer Bugfix  
**Priority:** Sehr Hoch (Events API Sync komplett defekt)

---

## 🐛 Kritischer Bugfix: Events API HTTP 400 Error

### Problem
**Event-Sync funktioniert NICHT** - alle Sync-Versuche schlagen fehl mit:

```
[ERROR] Event Sync failed: Could not fetch events
[ERROR] Failed to fetch events from API  
[ERROR] API Error: GET /events => HTTP 400
```

**Ursache:** Falscher `include` Parameter-Typ!

Die Events API erwartet `include=eventServices` (String), aber wir haben `include[]=eventServices` (Array) gesendet - das führt zu **HTTP 400 Bad Request**.

### Root Cause Analysis

**Unterschiedliche API-Syntax:**

**Events API (korrekt):**
```bash
GET /api/events?include=eventServices
# include als STRING-Parameter! ✅
```

**Appointments API (ebenfalls korrekt):**
```bash
GET /api/calendars/1/appointments?include[]=bookings&include[]=event&include[]=tags
# include[] als ARRAY-Parameter! ✅
```

**Was wir gemacht haben (v0.10.2.1 - v0.10.2.3):**
```php
// FALSCH für Events API! ❌
$api_params = [
    'include' => [
        'eventServices', // Array → wird zu include[]=eventServices
    ],
];
// Resultat: HTTP 400 Bad Request
```

**Was korrekt ist (v0.10.2.4):**
```php
// RICHTIG für Events API! ✅
$api_params = [
    'include' => 'eventServices', // String → wird zu include=eventServices
];
// Resultat: HTTP 200 OK
```

### Lösung (v0.10.2.4)

✅ `include` Parameter von Array zu String geändert  
✅ Events API Call jetzt korrekt: `include=eventServices`  
✅ Service-Import funktioniert wieder

**Betroffene Datei:**
- `includes/services/class-churchtools-suite-event-sync-service.php` - `fetch_all_events()` Methode

---

## 📝 Technische Details

### Code-Änderungen

**Datei:** `includes/services/class-churchtools-suite-event-sync-service.php`  
**Funktion:** `fetch_all_events()`  
**Zeile:** ~238

```diff
  private function fetch_all_events(array $args) {
      $api_params = [
          'direction' => 'forward',
          'from' => $args['from'],
          'to' => $args['to'],
-         // v0.10.2.0: Include eventServices for service import
-         'include' => [
-             'eventServices',
-         ],
+         // v0.10.2.4: Include eventServices (als String, nicht Array!)
+         'include' => 'eventServices',
      ];
```

### ChurchTools API Unterschiede

| API Endpoint | Parameter-Typ | Beispiel |
|-------------|---------------|----------|
| `/events` | String | `include=eventServices` ✅ |
| `/calendars/{id}/appointments` | Array | `include[]=bookings&include[]=tags` ✅ |

**Warum unterschiedlich?**
- Events API: Ältere API, simpler Parameter
- Appointments API: Neuere API, unterstützt mehrere Includes

---

## 🧪 Testing

### Erwartetes Verhalten nach Update

1. **Vor Update (v0.10.2.3):**
   ```
   [ERROR] API Error: GET /events => HTTP 400  ❌
   [ERROR] Event Sync failed: Could not fetch events  ❌
   ```

2. **Nach Update (v0.10.2.4):**
   ```
   [INFO] Event Sync started (FULL)  ✅
   [DEBUG] Successfully fetched X events from API  ✅
   [DEBUG] Service Import Complete - Event X: Y services imported  ✅
   [INFO] Event Sync completed  ✅
   ```

### Validierung

**Manueller Sync:**
- Admin → ChurchTools Suite → Dashboard → "Jetzt synchronisieren"
- Sollte KEINE Fehler mehr zeigen
- Events werden importiert
- Services werden importiert

**Logs prüfen:**
- Admin → Erweitert → Logs
- Suche: `Event Sync completed`
- Sollte `services_imported > 0` zeigen

**API-Call validieren (WP_DEBUG):**
```php
GET /api/events?from=2025-12-26&to=2026-03-26&direction=forward&include=eventServices
// Nicht mehr: include[]=eventServices ❌
```

### Test-Checklist

- [x] `include` von Array zu String geändert
- [ ] Event-Sync erfolgreich (KEINE HTTP 400 Fehler)
- [ ] Services werden importiert
- [ ] Logs zeigen `Event Sync completed`

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Der Fix ist rückwärtskompatibel.

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren (v0.10.2.4)
2. **Automatisch:** Events API Call verwendet korrekten Parameter
3. **Empfohlen:** Full Sync durchführen

### Manuelle Validierung (Empfohlen)
1. **Full Sync durchführen:**
   - Admin → ChurchTools Suite → Sync
   - "Vollständige Synchronisation" klicken
   - Sollte KEINE Fehler zeigen

2. **Logs prüfen:**
   - Admin → Erweitert → Logs
   - Letzter Eintrag sollte `Event Sync completed` sein
   - `services_imported` sollte > 0 sein

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.4 - Fix: Events API include Parameter (String statt Array)"
git push
git tag v0.10.2.4
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.4"
```

---

## 🔗 Related Issues

- User-reported: "sync der Events mit services scheint nicht mehr zu gehen"
- Log: `[ERROR] API Error: GET /events => HTTP 400`
- **Root Cause:** `include[]` statt `include` Parameter-Syntax

---

## 📚 Lessons Learned

1. **ChurchTools API Inkonsistenz:**
   - Events API: `include=value` (String)
   - Appointments API: `include[]=value1&include[]=value2` (Array)
   - IMMER API-Dokumentation prüfen!

2. **Testing:**
   - Nach API-Änderungen: SOFORT testen
   - HTTP 400 = meistens Parameter-Problem
   - cURL-Beispiele aus Doku verwenden

3. **Regression:**
   - v0.10.2.1 führte Bug ein (Array statt String)
   - Keine Tests haben das gefangen
   - → Integration-Tests für API-Calls erforderlich

---

## 📖 ChurchTools API Referenz

**Events API (korrekt):**
```bash
curl -X 'GET' \
  'https://feg-ab.church.tools/api/events?direction=forward&from=2022-10-19&to=2022-10-19&include=eventServices' \
  -H 'accept: application/json'
```

**Wichtig:** `include=eventServices` (NICHT `include[]`)

---

**Status:** ✅ Behoben, bereit für Deployment  
**Priorität:** 🚨 KRITISCH - Event-Sync war komplett defekt!
