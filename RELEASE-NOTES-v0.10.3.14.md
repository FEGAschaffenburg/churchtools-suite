# Release Notes: v0.10.3.14

**Release-Datum:** 2. Januar 2026  
**Art:** Kritischer Bugfix (JavaScript Syntax Error)  
**Status:** Production Ready ✅

---

## 🐛 Kritischer Bugfix

### JavaScript Syntax Error behoben
**Problem:** Alle JavaScript-Funktionen blockiert durch Syntax-Fehler

```
churchtools-suite-public.js:203 Uncaught SyntaxError: Unexpected string
```

**Symptome:**
- ❌ Modal-Details werden nicht angezeigt (auch wenn enable_modal aktiviert)
- ❌ Kalender-Monatswechsel funktioniert nicht
- ❌ Alle Click-Handler blockiert

**Root Cause:**
Copy-Paste Fehler in v0.10.3.11 Debug-Logging:

```javascript
error: function(xhr, status, error) {
    console.error('AJAX error loading calendar:', xhr, status, error);
    console.error('Response:', xhr.responseText);
    alert('Netzwerkfehler beim Laden des Kalenders: ' + error);
},
    alert('Netzwerkfehler beim Laden des Kalenders');  // FEHLER! Zeile 203
},
```

**Fix:**
Doppelte `alert()` Zeile entfernt (Zeile 203).

**Auswirkung:**
- ✅ JavaScript lädt ohne Fehler
- ✅ Modal-Details funktionieren wieder
- ✅ Kalender-Navigation funktioniert
- ✅ Alle Click-Handler aktiv

---

## 📋 Änderungen im Detail

### Dateien geändert
- `assets/js/churchtools-suite-public.js`
  - Zeile 203: Doppelte `alert()` Anweisung entfernt
  - Syntax-Fehler behoben

---

## 🔍 Technische Details

**Fehlerursache:**
In v0.10.3.11 wurde Debug-Logging für Kalender-AJAX hinzugefügt. Dabei wurde versehentlich eine doppelte `alert()` Anweisung eingefügt die außerhalb des `error` callbacks stand.

**JavaScript Parser:**
Browser stoppen die komplette JS-Datei wenn ein Syntax-Fehler auftritt. Alle Funktionen nach Zeile 203 waren nicht verfügbar:
- `initGridButtons()` ❌
- `initModalViews()` ❌
- `setupCalendarNavigation()` ❌

**Lösung:**
Zeile 203 komplett entfernt - `error` callback hat bereits korrektes `alert()` in Zeile 202.

---

## 📦 Deployment

**Breaking:** Ja - Komplettes JavaScript blockiert  
**Update:** SOFORT empfohlen!

**Installation:**
1. Plugin aktualisieren (Auto-Update oder manuell)
2. **Browser-Cache leeren** (STRG+F5)
3. ✅ Funktionen testen:
   - Modal-Details (Click auf Event)
   - Kalender-Navigation (Monatswechsel)

---

## ✅ Testing Durchgeführt

- ✅ JavaScript lädt ohne Console-Errors
- ✅ Modal öffnet bei Click auf Event
- ✅ Kalender-Monatswechsel funktioniert
- ✅ enable_modal Parameter wird respektiert

---

## ⚠️ Wichtig

**Nach Update Browser-Cache leeren!**
- **Chrome/Edge:** STRG+SHIFT+R oder STRG+F5
- **Firefox:** STRG+SHIFT+R
- **Safari:** CMD+SHIFT+R

Ohne Cache-Clear lädt Browser alte JavaScript-Version mit Fehler!

---

## 🎯 Nächste Schritte

- [x] Syntax-Fehler behoben
- [x] Modal-Funktionalität wiederhergestellt
- [x] Kalender-Navigation wiederhergestellt
- [ ] Console-Logging Cleanup für v0.10.4.0

---

**Entschuldigung für die Unannehmlichkeiten!** Dieser Fehler wurde in v0.10.3.11 eingeführt und ist jetzt behoben.
