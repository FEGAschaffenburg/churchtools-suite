# Release Notes: v0.10.3.16

**Release-Datum:** 2. Januar 2026  
**Art:** Kritischer Bugfix (Editor-Modus)  
**Status:** Production Ready ✅

---

## 🐛 Kritischer Bugfix

### Click-Handler im Editor komplett deaktiviert
**Problem:** Events in Listen/Grids sind im Gutenberg/Elementor Editor nicht bearbeitbar

**Symptome:**
- ❌ Click auf Event öffnet sofort Modal (statt Bearbeiten zu ermöglichen)
- ❌ Block-Settings nicht erreichbar
- ❌ Drag & Drop funktioniert nicht
- ❌ Editor-Workflow komplett blockiert

**Root Cause:**
v0.10.3.11 hatte Editor-Detection, aber **nur für `initClickableEvents()`**:

```javascript
if (isEditor) {
    console.log('Editor mode - skipping click handlers');
} else {
    initClickableEvents(); // Nur diese Funktion wurde geskippt!
}

// PROBLEM: Diese liefen IMMER (auch im Editor!)
initCalendarViews();
initGridButtons();      // ← Click-Handler für [data-event-id]
initModalViews();       // ← Modal open/close
```

**`initGridButtons()` registrierte Click-Handler:**
```javascript
$(document).on('click', '[data-event-id]:not(.cts-weekly-event)', function(e) {
    // Auch im Editor aktiv! ❌
    showEventModal(eventId);
});
```

---

## ✅ **Fix**

### ALLE Click-Handler im Editor deaktiviert

**Neue Logik:**
```javascript
const isEditor = $('body').hasClass('block-editor-page') || 
                 typeof elementor !== 'undefined' ||
                 $('body').hasClass('elementor-editor-active') ||
                 $('body').hasClass('wp-admin') ||
                 window.location.href.indexOf('/wp-admin/') !== -1;

if (isEditor) {
    console.log('Editor mode - skipping ALL click handlers');
    initModalCloseHandlers(); // Nur Close-Handler (für Cleanup)
} else {
    console.log('Frontend mode - initializing ALL handlers');
    initClickableEvents();    // Click-to-details
    initGridButtons();        // Grid/List buttons
    initModalViews();         // Modal open/close
}

// Kalender-Navigation läuft immer (kein Click-Konflikt)
initCalendarViews();
```

**Verbesserungen:**
1. **Erweiterte Editor-Detection:**
   - `wp-admin` Body-Klasse
   - `/wp-admin/` in URL

2. **Neue Funktion `initModalCloseHandlers()`:**
   - Nur Close-Handler (Overlay, Button, ESC)
   - Keine Open-Handler
   - Safe für Editor

3. **`initGridButtons()` nur im Frontend:**
   - Click-Handler wird im Editor NICHT registriert
   - Events sind wieder bearbeitbar

---

## 📋 Änderungen im Detail

### Dateien geändert
- `assets/js/churchtools-suite-public.js`
  - Editor-Detection erweitert (wp-admin Checks)
  - `initGridButtons()` + `initModalViews()` nur im Frontend
  - Neue Funktion `initModalCloseHandlers()` für Editor
  - Console-Logging verbessert

---

## ✅ Testing

**Gutenberg Editor:**
- ✅ Click auf Event öffnet KEIN Modal
- ✅ Block-Settings erreichbar
- ✅ Drag & Drop funktioniert
- ✅ Console: "Editor mode detected - skipping ALL click handlers"

**Elementor Editor:**
- ✅ Click auf Event öffnet KEIN Modal
- ✅ Widget-Settings erreichbar
- ✅ Console: "Editor mode detected"

**Frontend:**
- ✅ Click auf Event öffnet Modal
- ✅ Grid Detail-Buttons funktionieren
- ✅ Console: "Frontend mode - initializing ALL handlers"

---

## 📦 Deployment

**Breaking:** Ja - Editor-Workflow war blockiert  
**Update:** SOFORT empfohlen für alle Editor-User!

**Installation:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. Gutenberg/Elementor Editor öffnen
4. ✅ Events sind jetzt bearbeitbar

---

## 🔍 Debugging

**Console-Logs prüfen:**

**Im Editor:**
```
[ChurchTools Suite] Public JS loaded
[ChurchTools Suite] Editor mode detected - skipping ALL click handlers
[ChurchTools Suite] Init complete
```

**Im Frontend:**
```
[ChurchTools Suite] Public JS loaded
[ChurchTools Suite] Frontend mode - initializing ALL handlers
[ChurchTools Suite] initClickableEvents() called
[ChurchTools Suite] Found clickable events: 5
[ChurchTools Suite] Init complete
```

**Falls Click-Handler im Editor NOCH LAUFEN:**
- Browser-Cache wurde nicht geleert
- Lösung: **STRG+SHIFT+R** (Hard Reload)

---

## 🎯 Zusammenfassung

**v0.10.3.11:** Editor-Detection nur für `initClickableEvents()` ❌  
**v0.10.3.16:** Editor-Detection für **ALLE** Click-Handler ✅

**Betroffene Funktionen:**
- `initClickableEvents()` ✅ (war schon deaktiviert)
- `initGridButtons()` ✅ (jetzt deaktiviert)
- `initModalViews()` ✅ (jetzt deaktiviert, nur Close-Handler im Editor)

**Kalender-Navigation:** Läuft weiterhin im Editor (kein Konflikt mit Bearbeiten)

---

**WICHTIG:** Nach Update **Browser-Cache leeren** (STRG+F5), sonst lädt alte JavaScript-Version!
