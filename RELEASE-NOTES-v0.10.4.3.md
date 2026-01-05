# Release Notes - v0.10.4.3

**Release-Datum:** 5. Januar 2026  
**Typ:** Debug-Patch  
**Priorität:** Diagnostisch

---

## 🔍 Übersicht

Diagnostischer Release zur Analyse warum ChurchTools API keine Tags sendet. Fügt umfangreiche Raw-Response-Logging hinzu.

---

## 🐛 Debug-Erweiterungen

### Raw API Response Logging

**Problem:**
- v0.10.4.2 Logs zeigten: "KEINE TAGS in API-Response" für ALLE Appointments
- ChurchTools sendet keine Tags, obwohl `include=tags` Parameter gesetzt ist
- Ursache unklar: API-Berechtigungen, API-Version, oder Feature nicht aktiviert?

**Lösung:**
- **Neue Logging in `extract_appointment_data()`:**
  - Loggt komplettes `$appointment` Array (Raw-Payload)
  - Zeigt alle verfügbaren Keys
  - Zeigt tags-Wert (wenn vorhanden)
  
- **Neue Logging nach Appointments API-Call:**
  - Loggt erste Appointment als Sample
  - Zeigt vollständige Struktur der API-Response
  - Hilft zu verstehen was ChurchTools wirklich sendet

**Verwendung:**
```
1. Plugin aktualisieren auf v0.10.4.3
2. Sync durchführen
3. Logs prüfen nach:
   - "RAW APPOINTMENT DATA for ID XXX"
   - "FIRST APPOINTMENT SAMPLE"
4. Vollständiges API-Payload ist im Log sichtbar
```

---

## 📝 Geänderte Dateien

- `includes/services/class-churchtools-suite-event-sync-service.php`:
  - Enhanced logging in `extract_appointment_data()` (Zeile ~890)
  - Raw response logging nach Appointments API-Call (Zeile ~545)
- `churchtools-suite.php`: Version bump zu 0.10.4.3

---

## 🎯 Ziel

Herausfinden warum ChurchTools keine Tags sendet:
1. **API-Berechtigungen:** User hat keine Rechte für Tags
2. **Include-Parameter:** ChurchTools ignoriert `include=tags`
3. **API-Version:** Ältere ChurchTools Version ohne Tags-Support
4. **Feature-Toggle:** Tags-Feature nicht aktiviert für Kalender

---

## 🔄 Migration

Keine Datenbank-Änderungen.

---

## ⚠️ Breaking Changes

Keine.

---

## 🧪 Testing

1. Plugin auf v0.10.4.3 aktualisieren
2. Sync durchführen
3. Debug-Logs öffnen: ChurchTools Suite → Erweitert → Logs
4. Suchen nach: "RAW APPOINTMENT DATA"
5. Komplettes API-Payload prüfen

---

## 📚 Technische Details

**Log-Output Beispiel (erwartet):**
```
[DEBUG] RAW APPOINTMENT DATA for ID 5299
{
  "raw_appointment": { ... FULL PAYLOAD ... },
  "has_tags_key": false,
  "tags_value": "NOT_SET",
  "appointment_keys": ["base", "calculated", "appointment", ...]
}

[DEBUG] FIRST APPOINTMENT SAMPLE (Calendar 2)
{
  "sample_appointment": { ... FULL STRUCTURE ... },
  "sample_keys": ["base", "calculated", "appointment", ...],
  "total_appointments": 15
}
```

**Nächste Schritte:**
- Basierend auf Log-Output API-Request anpassen
- Evtl. andere Include-Parameter testen
- Evtl. separaten Tags-Endpoint verwenden (`GET /tags`)
- Evtl. ChurchTools Permissions prüfen

---

## 🔗 Links

- **GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.4.3
- **Vorherige Version:** v0.10.4.2
- **Issue:** Tags werden nicht aus ChurchTools importiert

---

**Hinweis:** Dies ist ein reiner Debug-Release. Keine funktionalen Änderungen, nur erweiterte Logging für Diagnose.
