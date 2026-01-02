# ChurchTools Suite - Release Notes v0.10.1.9

**Datum:** 2. Januar 2026  
**Typ:** UX-Verbesserung (Cron-Display + Update-Weiterleitung)

---

## ✨ Verbesserungen

### 1. Benutzerfreundliche Cron-Job-Namen
**Problem:** Cron-Jobs zeigten nur technische Namen wie `churchtools_suite_check_updates`.

**Lösung:**
- Neue Hilfsklasse: `ChurchTools_Suite_Cron_Display`
- Benutzerfreundliche Namen für alle Cron-Jobs
- Beschreibungen für jeden Cron-Job
- Verbesserte Anzeige in Debug-Übersicht

**Cron-Job-Namen (neu):**
```
✅ Event-Synchronisation
   Synchronisiert Events automatisch gemäß Zeitplan.
   (churchtools_suite_auto_sync)

✅ Session aufrechterhalten
   Verlängert die ChurchTools-Session.
   (churchtools_suite_session_keepalive)

✅ Update-Prüfung
   Prüft auf neue Plugin-Versionen und installiert Updates automatisch.
   (churchtools_suite_check_updates)
```

**Neue Datei:**
- `includes/class-churchtools-suite-cron-display.php`

**Betroffene Dateien:**
- `includes/class-churchtools-suite-auto-updater.php` - Helper-Funktionen
- `admin/views/tab-debug-minimal.php` - Cron-Jobs-Übersicht hinzugefügt

---

### 2. Update-Weiterleitung zur Plugin-Seite
**Problem:** Nach manuellem Update blieb Nutzer auf Dashboard-Seite.

**Lösung:**
- Automatische Weiterleitung zur Plugin-Seite (`/wp-admin/plugins.php`)
- Erfolg-Message: "Update erfolgreich! Sie werden zur Plugin-Seite weitergeleitet..."
- Nutzer sieht sofort die aktualisierte Version

**Betroffene Datei:**
- `admin/views/tab-dashboard.php` - Update-Button mit Weiterleitung

---

## 📋 Zusammenfassung

**2 UX-Verbesserungen:**
- ✅ Benutzerfreundliche Cron-Job-Namen (mit Beschreibungen)
- ✅ Auto-Weiterleitung nach Update (zur Plugin-Seite)

**4 Dateien geändert:**
- `includes/class-churchtools-suite-cron-display.php` (NEU)
- `includes/class-churchtools-suite-auto-updater.php`
- `admin/views/tab-debug-minimal.php`
- `admin/views/tab-dashboard.php`

**Neue Features:**
- ✅ Cron-Jobs-Übersicht in Debug-Tab
- ✅ Zeigt nächste Ausführung + Intervall
- ✅ Helper-Funktionen für WP-CLI/Debug

---

## 🎯 Visuelle Änderungen

### Vorher (Cron-Jobs):
```
❌ churchtools_suite_check_updates
   Nächste Ausführung: Di, 06. Januar 2026 10:13
```

### Nachher (Cron-Jobs):
```
✅ Update-Prüfung
   Prüft auf neue Plugin-Versionen und installiert Updates automatisch.
   (churchtools_suite_check_updates)
   Nächste Ausführung: Di, 06. Januar 2026 10:13
   Intervall: daily
```

### Vorher (Update):
```
Alert: "Update gestartet"
[Bleibt auf Dashboard]
```

### Nachher (Update):
```
Alert: "Update erfolgreich! Sie werden zur Plugin-Seite weitergeleitet..."
[Automatische Weiterleitung nach 1s → /wp-admin/plugins.php]
[Nutzer sieht neue Version]
```

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

---

## 📦 Upgrade-Hinweise

**Wichtigkeit:** 🟢 OPTIONAL (verbessert UX)

**Upgrade-Pfad:**
- v0.10.1.8 → v0.10.1.9: Nahtlos (nur UX-Verbesserungen)

**Empfehlung:**
- Update empfohlen für bessere User Experience
- Besonders nützlich für Admins, die Cron-Jobs überwachen

---

## 🔍 Testing

**Getestet:**
- ✅ Cron-Jobs zeigen benutzerfreundliche Namen
- ✅ Debug-Tab zeigt Cron-Übersicht
- ✅ Update-Weiterleitung funktioniert
- ✅ Alle bestehenden Features funktionieren

**Smoke Tests:**
- ✅ Dashboard lädt
- ✅ Debug-Tab lädt
- ✅ Cron-Jobs scheduliert
- ✅ Update-Button funktioniert

---

## 📊 Statistik

**Geänderte Dateien:** 4  
**Neue Dateien:** 1  
**Zeilen geändert:** ~150  
**UX-Verbesserungen:** 2  
**Neue Features:** 1 (Cron-Jobs-Übersicht)

---

## 🔄 Nächste Schritte

**Phase 1 (Bugfixes) abgeschlossen:**
- v0.10.1.7 - Critical Parse Error
- v0.10.1.8 - JSON Parse Error + Cron-Display
- v0.10.1.9 - UX-Verbesserungen

**Nächste Phase:** Code Quality (v0.11.0)
- PHPDoc vervollständigen
- Code-Duplikate reduzieren
- Best Practices anwenden

**Master-Roadmap:** Auf Track für v1.0.0 (März 2026)

---

**Fix committed:** 2. Januar 2026  
**Verantwortlich:** GitHub Copilot
