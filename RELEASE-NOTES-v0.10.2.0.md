# Release Notes - Version 0.10.2.0

**Release Date:** 2. Januar 2026  
**Type:** Bugfix  
**Priority:** Hoch (Cron-Intervall-Fix)

---

## 🐛 Kritischer Bugfix: Auto-Sync Intervall

### Problem
Der Auto-Sync Cron-Job lief **stündlich** (`hourly`), obwohl im UI **"Täglich"** ausgewählt war. 

**Ursache:** WordPress kennt **kein** natives `daily` Intervall! Es gibt nur:
- `hourly` (1 Stunde)
- `twicedaily` (12 Stunden)

Wenn `daily` als Intervall übergeben wurde, fiel WordPress auf `hourly` zurück.

### Lösung
✅ Eigenes `daily` (24h) Intervall zu Custom Cron Schedules hinzugefügt  
✅ Default-Werte konsistent auf `'daily'` gesetzt (vorher teils `'hourly'`)  
✅ Dokumentation im Code ergänzt

**Betroffene Dateien:**
- `includes/class-churchtools-suite-cron.php` - Custom `daily` Intervall hinzugefügt
- `admin/views/settings/subtab-sync.php` - Default konsistent

---

## 📝 Technische Details

### Vorher
```php
// WordPress native Intervalle (KEIN 'daily'!)
hourly      → 3600 Sekunden (1 Stunde)
twicedaily  → 43200 Sekunden (12 Stunden)
```

**Problem:** `daily` wurde nicht erkannt → Fallback auf `hourly`

### Nachher
```php
// Custom Cron Intervalle (seit v0.10.2.0)
daily       → 86400 Sekunden (24 Stunden) ✅ NEU
cts_2days   → 172800 Sekunden (2 Tage)
cts_3days   → 259200 Sekunden (3 Tage)
cts_weekly  → 604800 Sekunden (7 Tage)
cts_2weeks  → 1209600 Sekunden (14 Tage)
cts_monthly → 2592000 Sekunden (30 Tage)
```

---

## 🔧 Migration

### Automatische Anpassung
Nach dem Update wird beim nächsten Seitenaufruf:
1. Cron-Hook neu registriert (mit korrektem `daily` Intervall)
2. Bestehende Schedules aktualisiert
3. Nächster Lauf korrekt berechnet (03:00 Uhr morgens)

### Manuelle Prüfung
**Empfohlen:** Nach Update prüfen:
1. WordPress Admin → ChurchTools Suite → Debug → Übersicht
2. Cron-Jobs-Tabelle prüfen
3. "Event-Synchronisation" sollte **"daily"** zeigen (nicht "hourly")

**Oder via WP-CLI:**
```bash
wp cron event list
# Suche: churchtools_suite_auto_sync
# Interval sollte "daily" sein
```

---

## ⚠️ Breaking Changes

Keine Breaking Changes. Der Fix ist rückwärtskompatibel.

---

## 🧪 Testing Checklist

- [x] Custom `daily` Intervall registriert
- [x] Auto-Sync auf `daily` umgestellt (wenn in UI ausgewählt)
- [x] Default-Werte konsistent (`'daily'` statt `'hourly'`)
- [x] Cron läuft um 03:00 Uhr (nicht stündlich)
- [x] Debug-Tab zeigt korrektes Intervall

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.0 - Fix: Auto-Sync 'daily' Intervall"
git push
git tag v0.10.2.0
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.0"
```

---

## 🔗 Related Issues

- User-reported: "Cronjob sync ist falsch eingestellt"
- Screenshot: Cron läuft stündlich statt täglich
- Diskrepanz: UI zeigt "Täglich", Cron zeigt "hourly"

---

**Status:** ✅ Behoben, bereit für Deployment
