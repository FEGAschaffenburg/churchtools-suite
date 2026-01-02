# Testing Checklist v0.10.1.7

> **Version:** 0.10.1.7  
> **Bugfix:** Parse Error in Cron  
> **Test-Datum:** 2. Januar 2026

---

## 🎯 Test-Ziel

Verifizieren, dass der Critical Bugfix funktioniert und keine neuen Probleme entstanden sind.

---

## ✅ Smoke Tests (Must-Have)

### 1. Plugin Activation
- [ ] Plugin kann aktiviert werden (keine Fatal Errors)
- [ ] Admin-Menü erscheint
- [ ] Keine PHP-Errors im Debug-Log

**Erwartetes Ergebnis:** Plugin lädt ohne Fehler

---

### 2. Dashboard
- [ ] Dashboard-Tab lädt
- [ ] Statistiken werden angezeigt
- [ ] Keine JavaScript-Errors in Console

**Erwartetes Ergebnis:** Dashboard zeigt Sync-Status

---

### 3. Settings
- [ ] Settings-Tab lädt
- [ ] ChurchTools-Verbindung testbar
- [ ] Einstellungen speicherbar
- [ ] Subtabs funktionieren (Verbindung, Synchronisation, etc.)

**Erwartetes Ergebnis:** Settings funktional

---

### 4. Sync
- [ ] Sync-Tab lädt
- [ ] "Jetzt synchronisieren" Button funktioniert
- [ ] Events werden synchronisiert
- [ ] Keine Errors während Sync

**Erwartetes Ergebnis:** Sync läuft durch

---

### 5. Cron Jobs (HAUPTTEST!)
- [ ] `churchtools_suite_auto_sync` Hook existiert
- [ ] `churchtools_suite_session_keepalive` Hook existiert
- [ ] Keine Parse Errors in Cron-Datei
- [ ] Cron-Jobs sind scheduliert

**Test-Commands:**
```powershell
# Auf Server via SSH:
wp cron event list --fields=hook,next_run,recurrence

# Erwartete Hooks:
# - churchtools_suite_auto_sync
# - churchtools_suite_session_keepalive
```

**Erwartetes Ergebnis:** Beide Cron-Jobs scheduliert, keine Errors

---

### 6. Frontend (Demo-Seiten)
- [ ] Template-Seiten laden
- [ ] Events werden angezeigt
- [ ] Keine JavaScript-Errors
- [ ] Modal funktioniert (bei clickable events)

**Test-URLs:**
```
https://plugin.feg-aschaffenburg.de/demos/calendar-monthly-modern/
https://plugin.feg-aschaffenburg.de/demos/grid-modern/
https://plugin.feg-aschaffenburg.de/demos/list-classic/
```

**Erwartetes Ergebnis:** Alle Templates rendern korrekt

---

### 7. Demo-Registration
- [ ] Backend-Demo-Seite lädt
- [ ] Registrierungsformular funktioniert
- [ ] E-Mail-Verifizierung wird verschickt
- [ ] Auto-Login nach Verifizierung

**Test-URL:**
```
https://plugin.feg-aschaffenburg.de/backend-demo/
```

**Erwartetes Ergebnis:** Registrierungsflow komplett funktional

---

## 🔍 Detaillierte Tests (Optional)

### 8. Event Services
- [ ] Services-Tab lädt
- [ ] Services synchronisierbar
- [ ] Service-Auswahl speicherbar
- [ ] Services erscheinen in Events

---

### 9. Advanced/Debug
- [ ] Debug-Tab lädt (wenn Advanced Mode aktiv)
- [ ] Subtabs funktionieren
- [ ] Logs werden angezeigt
- [ ] Manuelle Trigger funktionieren

---

### 10. Performance
- [ ] Dashboard lädt in < 2s
- [ ] Sync von 100 Events in < 5s
- [ ] Frontend-Templates laden in < 1s

---

## 🐛 Bug-Tracking

### Gefundene Bugs
*Hier dokumentieren, wenn Bugs gefunden werden:*

| # | Severity | Beschreibung | Reproduktion | Status |
|---|----------|--------------|--------------|--------|
| - | - | - | - | - |

---

## 📊 Test-Ergebnisse

### Zusammenfassung
```
Getestete Features:  ___ / 10
Erfolgreiche Tests:  ___ / ___
Fehlgeschlagene:     ___
Gefundene Bugs:      ___
```

### Status
- [ ] ✅ Alle Tests bestanden
- [ ] ⚠️ Minor Issues (nicht blockierend)
- [ ] 🔴 Critical Issues (blockierend)

---

## 🚀 Deployment-Status

### GitHub
- ✅ Commit: e7b1f6f
- ✅ Push: main branch
- ✅ Tag: v0.10.1.7

### Server
- [ ] Plugin updated (via FTP/SSH)
- [ ] Plugin re-aktiviert
- [ ] Caches geleert

---

## 📝 Notizen

**Test-Environment:**
- Server: plugin.feg-aschaffenburg.de
- WordPress: 6.x
- PHP: 8.x
- ChurchTools: API v3

**Test-Durchführer:** ___________  
**Test-Datum:** 2. Januar 2026  
**Test-Dauer:** ___ Minuten

---

## ✅ Sign-Off

**Getestet von:** ___________  
**Datum:** ___________  
**Status:** [ ] PASSED  [ ] FAILED  [ ] BLOCKED

**Kommentare:**
