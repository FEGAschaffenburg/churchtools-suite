# Release Notes v0.10.3.6

**Release-Datum:** 2. Januar 2026  
**Type:** Patch (Debug Build)

## 🔍 Debug Build - Click-to-Details Troubleshooting

**Zweck:** Erweiterte Console-Logging-Version um herauszufinden warum Click-to-Details nicht funktioniert.

### Debug-Features

**Console Logging hinzugefügt:**
```javascript
// JavaScript Load Status
console.log('[ChurchTools Suite] Public JS loaded');
console.log('[ChurchTools Suite] Init complete');

// Event Listener Registration
console.log('[ChurchTools Suite] initClickableEvents() called');
console.log('[ChurchTools Suite] Found clickable events:', count);

// Click Events
console.log('[ChurchTools Suite] Event clicked, ID:', eventId);

// Modal System
console.log('[ChurchTools Suite] showEventModal() called with ID:', eventId);
console.log('[ChurchTools Suite] Modal overlay found:', exists);
console.log('[ChurchTools Suite] Loading modal template via AJAX...');
console.log('[ChurchTools Suite] AJAX URL:', url);
console.log('[ChurchTools Suite] Modal template response:', response);
```

### Was wird geprüft?

1. **JavaScript lädt:** "Public JS loaded" Meldung
2. **Init läuft durch:** "Init complete" Meldung
3. **Event-Listener registriert:** "initClickableEvents() called"
4. **Events gefunden:** Anzahl gefundener `.cts-event-clickable` Elemente
5. **Click funktioniert:** "Event clicked, ID: X" beim Klicken
6. **Modal-System startet:** "showEventModal() called"
7. **AJAX läuft:** URL und Response-Daten
8. **Fehler:** Alle Fehler werden in Console geloggt

### Bitte testen

**Nach dem Update:**
1. Browser-Cache leeren (STRG+F5)
2. Frontend öffnen mit Liste/Grid
3. Browser Developer Console öffnen (F12 → Console Tab)
4. Auf Event klicken
5. **Screenshots machen** von allen Console-Meldungen
6. Feedback geben welche Meldungen erscheinen

### Erwartete Ausgabe (wenn alles funktioniert)

```
[ChurchTools Suite] Public JS loaded
[ChurchTools Suite] initClickableEvents() called
[ChurchTools Suite] Found clickable events: 5
[ChurchTools Suite] Init complete
--- NACH KLICK ---
[ChurchTools Suite] Event clicked, ID: 123
[ChurchTools Suite] showEventModal() called with ID: 123
[ChurchTools Suite] Modal overlay found: false
[ChurchTools Suite] Loading modal template via AJAX...
[ChurchTools Suite] AJAX URL: https://...admin-ajax.php
[ChurchTools Suite] Modal template response: {...}
[ChurchTools Suite] Modal template appended to body
```

### Mögliche Probleme & Lösungen

**Kein "Public JS loaded":**
- JavaScript wird nicht geladen
- Lösung: Cache-Problem, STRG+F5 drücken

**"Found clickable events: 0":**
- Template hat keine `.cts-event-clickable` Klasse
- Lösung: Template prüfen/korrigieren

**Kein "Event clicked" beim Klicken:**
- Event-Listener nicht aktiv
- Lösung: jQuery-Problem oder JavaScript-Fehler davor

**AJAX-Fehler:**
- AJAX-Endpoint nicht erreichbar
- Lösung: WordPress-Konfiguration prüfen

---

**Nur für Debugging!** Nach Fehleranalyse folgt produktive Version ohne Console-Logging.

---

**GitHub Tag:** v0.10.3.6  
**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.6
