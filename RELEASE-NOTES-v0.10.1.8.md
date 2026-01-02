# ChurchTools Suite - Release Notes v0.10.1.8

**Datum:** 2. Januar 2026  
**Typ:** Bugfix (Cron-Display + AJAX-Robustheit)

---

## 🐛 Bugfixes

### 1. Cron Job "churchtools_suite_check_updates" - Display-Text
**Problem:** Der Auto-Updater Cron Job hatte keinen benutzerfreundlichen Anzeigenamen.

**Lösung:**
- Filter `add_cron_display_info()` hinzugefügt (für zukünftige Erweiterungen)
- "Weekly" → "Wöchentlich" (deutschsprachig)

**Betroffene Datei:**
- `includes/class-churchtools-suite-auto-updater.php`

---

### 2. Kalender-Sync Fehler: "Unexpected token '<'"
**Problem:** JavaScript-Fehler beim Kalender-Sync wenn Server keine JSON-Antwort sendet.

```
Fehler: Unexpected token '<', ...
```

**Ursache:**
- `fetch().then(r => r.json())` versucht HTML-Fehlerseiten als JSON zu parsen
- Bei PHP-Fehlern sendet Server HTML statt JSON
- Parse-Error führt zu kryptischer Fehlermeldung

**Lösung:**
- **Content-Type Check:** Prüft ob Response wirklich JSON ist
- **Error Handling:** Zeigt bei Non-JSON den tatsächlichen Fehler
- **Console Logging:** Erster Teil der Response wird geloggt (Debugging)
- **Auto-Reload:** Nach erfolgreichem Sync wird Seite neu geladen

**Verbesserungen:**
```javascript
// VORHER
.then(r => r.json())

// NACHHER
.then(function(r) {
    if (!r.ok) throw new Error('Server-Fehler: ' + r.status);
    const contentType = r.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
        return r.text().then(text => {
            console.error('Non-JSON Response:', text.substring(0, 500));
            throw new Error('Server hat keine gültige JSON-Antwort gesendet');
        });
    }
    return r.json();
})
```

**Betroffene Dateien:**
- `admin/views/tab-calendars.php` - Kalender-Sync
- `admin/views/tab-dashboard.php` - Manual Sync (2x)

**Zusatzfeature:**
- Nach erfolgreichem Sync: Automatisches Page-Reload nach 1.5s
- Nutzer sieht sofort die synchronisierten Kalender

---

## 📋 Zusammenfassung

**2 Bugs behoben:**
- ✅ Cron-Display verbessert (deutschsprachig)
- ✅ AJAX-Fehlerbehandlung robuster (verhindert "Unexpected token" Fehler)

**3 Dateien geändert:**
- `includes/class-churchtools-suite-auto-updater.php`
- `admin/views/tab-calendars.php`
- `admin/views/tab-dashboard.php`

**Neue Features:**
- ✅ Auto-Reload nach erfolgreichem Sync
- ✅ Bessere Fehlermeldungen (zeigt echten Fehler statt Parse-Error)
- ✅ Console-Logging für Debugging

---

## 🎯 Visuelle Änderungen

**Vorher:**
```
❌ Fehler: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

**Nachher:**
```
❌ Server hat keine gültige JSON-Antwort gesendet (möglicherweise PHP-Fehler)
[Console zeigt: <!DOCTYPE html>... Parse error in line 184...]
```

Nutzer kann jetzt den tatsächlichen Fehler im Browser-Console sehen!

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

---

## 📦 Upgrade-Hinweise

**Wichtigkeit:** 🟡 EMPFOHLEN (verbessert User Experience)

**Upgrade-Pfad:**
- v0.10.1.7 → v0.10.1.8: Nahtlos (nur Bugfixes)

**Empfehlung:**
- Alle Installationen updaten
- Besonders wichtig wenn Nutzer Sync-Probleme melden

---

## 🔍 Testing

**Getestet:**
- ✅ Kalender-Sync funktioniert
- ✅ Fehlerbehandlung bei Non-JSON-Responses
- ✅ Auto-Reload nach Sync
- ✅ Cron-Jobs scheduliert

**Smoke Tests:**
- ✅ Dashboard lädt
- ✅ Kalender-Tab lädt
- ✅ Sync durchführen
- ✅ Fehlermeldungen leserlich

---

## 📊 Statistik

**Geänderte Dateien:** 3  
**Zeilen geändert:** ~50  
**Bugfixes:** 2  
**Neue Features:** 2 (Auto-Reload, bessere Errors)

---

## 🔄 Nächste Schritte

**Phase 1 (Bugfixes) läuft weiter:**
- Weitere Edge Cases testen
- Bugs sammeln für v0.11.0
- Testing-Checkliste durcharbeiten

**Master-Roadmap:** Auf Track für v1.0.0 (März 2026)

---

**Fix committed:** 2. Januar 2026  
**Verantwortlich:** GitHub Copilot
