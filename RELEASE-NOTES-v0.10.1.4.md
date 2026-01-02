# ChurchTools Suite - Release Notes v0.10.1.4

**Datum:** 2. Januar 2026  
**Typ:** Bugfix-Release (Admin-Verbesserungen)

---

## 🐛 Bugfixes

### 1. Reset-Funktion erweitert
**Problem:** Der "Komplette Reset" Button löschte nur Daten (Events, Kalender, Services), aber keine Einstellungen.

**Lösung:**
- **Neuer Button "WIRKLICH ALLES reseten"** (dunkelrot)
  - Löscht ALLE Daten aus allen Tabellen
  - Löscht ALLE Einstellungen (ChurchTools URL, Login, Cookies, Sync-Konfiguration, etc.)
  - Kompletter Plugin-Reset - muss danach neu konfiguriert werden
- **Alter Button umbenannt**: "Kompletter Reset (Daten)" (orange)
  - Wie bisher: Löscht nur Daten, Einstellungen bleiben
- **Visuelle Trennung**: Rot für vollständigen Reset, Orange für Daten-Reset

**Betroffene Dateien:**
- `admin/views/debug/subtab-reset-cleanup.php`
- `admin/class-churchtools-suite-admin.php`

**Neue AJAX-Handler:**
- `ajax_complete_reset()` - Löscht Daten + Einstellungen

---

### 2. Subtabs CSS vereinheitlicht
**Problem:** Subtabs im "Erweitert"-Tab sahen anders aus als im "Einstellungen"-Tab.

**Lösung:**
- CSS für `.cts-sub-tabs` und `.cts-sub-tab` hinzugefügt (Settings-Subtabs)
- CSS für `.cts-subtab-nav` und `.cts-subtab` bleibt (Debug-Subtabs)
- Beide Styles sind jetzt identisch und konsistent
- Gleiche Farben, Border-Radius, Hover-Effekte

**Betroffene Dateien:**
- `admin/css/churchtools-suite-admin.css`

**Visuelle Änderungen:**
- ✅ Einheitliche Tab-Navigation in allen Bereichen
- ✅ Konsistente Hover- und Active-States
- ✅ Professionelleres Erscheinungsbild

---

### 3. Manuelles Update - Vollständige Implementierung
**Problem:** Button "Manuelles Update prüfen" zeigte nur "gestartet" an, kein "Fertig" und kein Seitenrefresh.

**Lösung:**
- **Komplettes JavaScript für manuelle Trigger implementiert**
  - Event-Sync Trigger mit Fortschrittsanzeige
  - Session Keepalive mit Status-Feedback
  - **Manuelles Update**: Navigiert zu WordPress Update-Seite mit `force-check=1`
  - Log löschen mit Bestätigung
- **Live-Feedback für alle Trigger**
  - Loading-States während AJAX-Requests
  - Success/Error Messages mit Icons
  - Auto-Reload nach erfolgreichen Aktionen

**Betroffene Dateien:**
- `admin/views/debug/subtab-manuelle-trigger.php`

**Neue Features:**
- ✅ Visuelles Feedback für alle Trigger-Buttons
- ✅ Farbcodierte Success/Error States (grün/rot)
- ✅ Auto-Reload nach Sync (2 Sekunden Delay)
- ✅ Manuelles Update nutzt WordPress-native Mechanik

---

## 📋 Zusammenfassung

**3 Admin-Bugs behoben:**
1. ✅ Reset-Funktion: Neuer "WIRKLICH ALLES reseten" Button
2. ✅ Subtabs CSS: Konsistente Darstellung in allen Tabs
3. ✅ Manuelles Update: Vollständige JavaScript-Implementierung

**Technische Details:**
- Neue AJAX-Handler: `cts_complete_reset`
- CSS-Erweiterung: `.cts-sub-tabs`/`.cts-sub-tab` Styles
- JavaScript: 140+ Zeilen für manuelle Trigger
- Löscht 13 Settings-Keys bei Complete Reset

---

## 🎯 Getestet

- [x] Reset-Buttons funktionieren (Daten vs. Komplett)
- [x] Subtabs sehen konsistent aus (Einstellungen vs. Erweitert)
- [x] Manuelle Trigger zeigen Feedback (Sync, Keepalive, Update, Logs)
- [x] Auto-Reload nach erfolgreichen Aktionen
- [x] Error-Handling für AJAX-Fehler

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

Alle Änderungen sind rückwärtskompatibel. Bestehende Funktionalität bleibt unverändert.

---

## 📦 Upgrade-Hinweise

**Von v0.10.1.3 → v0.10.1.4:**
- Keine Migrationen erforderlich
- Keine Datenbank-Änderungen
- Einfacher Plugin-Update über WordPress

**Neue Funktionen nutzen:**
1. **Reset**: Erweitert → Reset & Cleanup
   - Orange Button: Nur Daten löschen
   - Roter Button: ALLES löschen (inkl. Einstellungen)
2. **Manuelles Update**: Erweitert → Manuelle Trigger → "Manuelles Update prüfen"
3. **Subtabs**: Optisch konsistent in allen Bereichen

---

**Empfehlung:** Dieses Update behebt wichtige UX-Probleme im Admin-Bereich. Update wird empfohlen für alle Nutzer, die den erweiterten Modus verwenden.
