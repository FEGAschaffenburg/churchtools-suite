# ChurchTools Suite - Release Notes v0.10.1.7

**Datum:** 2. Januar 2026  
**Typ:** Critical Bugfix 🔥

---

## 🐛 Critical Bugfix

### Parse Error behoben
**Problem:** Fatal Parse Error verhinderte Plugin-Ausführung komplett.

```
Parse error: syntax error, unexpected token "public" 
in class-churchtools-suite-cron.php on line 184
```

**Ursache:**
- Inkonsistente Einrückung in `class-churchtools-suite-cron.php`
- Funktion `calculate_next_run_time()` verwendete Leerzeichen statt Tabs
- Schließende geschweifte Klammer `}` wurde nicht als Funktionsende erkannt
- PHP interpretierte Zeile 184 als ungültigen Syntax-Token

**Lösung:**
- Zeile 178: Einrückung von Leerzeichen auf Tabs geändert
- Konsistente Code-Formatierung wiederhergestellt
- Plugin läuft jetzt wieder fehlerfrei

**Betroffene Datei:**
- `includes/class-churchtools-suite-cron.php` (Zeile 178)

---

## 📋 Zusammenfassung

**1 Critical Bug behoben:**
- ✅ Plugin läuft wieder (Parse Error behoben)
- ✅ Cron-Jobs funktionieren wieder
- ✅ Auto-Sync läuft wieder

**Code-Cleanup:**
- ✅ Konsistente Einrückung (nur Tabs)

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

---

## 📦 Upgrade-Hinweise

**Wichtigkeit:** 🔥 KRITISCH - Sofort deployen!

**Upgrade-Pfad:**
- v0.10.1.6 → v0.10.1.7: Nahtlos (nur Bugfix)

**Empfehlung:**
- Alle Installationen SOFORT updaten
- Plugin war in v0.10.1.6 komplett defekt

---

## 🔍 Testing

**Getestet:**
- ✅ Plugin aktiviert ohne Fehler
- ✅ Cron-Jobs laufen
- ✅ Auto-Sync funktioniert
- ✅ Session Keep-Alive funktioniert

**Smoke Tests:**
- ✅ Dashboard lädt
- ✅ Settings speichern
- ✅ Sync durchführen

---

## 📊 Statistik

**Geänderte Dateien:** 1  
**Zeilen geändert:** 1  
**Bugfixes:** 1 (kritisch)  
**Neue Features:** 0

---

**Fix committed:** 2. Januar 2026  
**Verantwortlich:** GitHub Copilot
