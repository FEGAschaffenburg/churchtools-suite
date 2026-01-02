# Release Notes v0.10.3.5

**Release-Datum:** 2. Januar 2026  
**Type:** Patch (Bug Fix)

## 🐛 Bug Fix - Click-to-Details funktioniert jetzt!

### Problem
In v0.10.3.0 wurde "Click-to-Details" in alle Views eingebaut (Klasse `cts-event-clickable` und `data-event-id` Attribut), aber es fehlte der JavaScript Event-Listener der auf diese Klicks reagiert.

**Symptom:**  
Events hatten zwar die CSS-Klasse und das Attribut, aber beim Klicken passierte nichts - keine Modal-Anzeige.

### Ursache
Das JavaScript in `assets/js/churchtools-suite-public.js` hatte:
- ✅ Modal-System (`showEventModal()`, `loadEventData()`, `closeModal()`)
- ✅ Modal Event-Listener (ESC, Background-Click, Close-Button)
- ❌ **FEHLTE:** Event-Listener für `.cts-event-clickable` Klicks

### Lösung
**Neue Funktion `initClickableEvents()` hinzugefügt:**
```javascript
function initClickableEvents() {
    // Event-Delegation für dynamisch geladene Events
    $(document).on('click', '.cts-event-clickable', function(e) {
        e.preventDefault();
        const eventId = $(this).data('event-id');
        if (eventId) {
            showEventModal(eventId);
        }
    });
    
    // Keyboard accessibility (Enter/Space)
    $(document).on('keydown', '.cts-event-clickable', function(e) {
        if (e.keyCode === 13 || e.keyCode === 32) {
            e.preventDefault();
            const eventId = $(this).data('event-id');
            if (eventId) {
                showEventModal(eventId);
            }
        }
    });
}
```

**Aufruf in DOM Ready:**
```javascript
$(function() {
    initCalendarViews();
    initGridButtons();
    initModalViews();
    initClickableEvents(); // v0.10.3.0: Click-to-details
});
```

---

## ✅ Betroffene Views (alle funktionieren jetzt)

**Listen:**
- ✅ Liste - Classic
- ✅ Liste - Compact
- ✅ Liste - Medium
- ✅ Liste - Fluent

**Grids:**
- ✅ Grid - Simple
- ✅ Grid - Modern
- ✅ Grid - Colorful

**Weitere:**
- ✅ Widget - Upcoming Events
- ✅ Search - Classic
- ✅ Calendar - Monthly Modern (Eventdots)

---

## 🎯 Features

**Mouse Click:**
- Klick auf Event öffnet Modal mit Details
- Event-Delegation unterstützt dynamisch geladene Events (z.B. AJAX)

**Keyboard Accessibility:**
- Enter oder Space öffnet Modal
- ESC schließt Modal (bereits vorhanden)
- Fokus-Navigation mit Tab (bereits durch `tabindex="0"` in Templates)

---

## 📦 Update-Anleitung

### Automatisch (empfohlen)
1. Dashboard → ChurchTools Suite
2. "Jetzt installieren" (bleibt im Dashboard dank v0.10.3.4)
3. Browser-Cache leeren (CTRL+F5) um neues JavaScript zu laden

### Manuell
1. ZIP von GitHub herunterladen
2. Alte Version deaktivieren
3. Neue Version hochladen & aktivieren
4. **Wichtig:** Browser-Cache leeren!

---

## 🧪 Testen

1. Shortcode einfügen: `[churchtools_events view="list" template="classic"]`
2. Frontend öffnen
3. Auf Event klicken → Modal öffnet sich mit Details
4. ESC drücken → Modal schließt sich
5. Tab-Navigation → Events sind fokussierbar
6. Enter/Space → Modal öffnet sich

---

## 🔗 Änderungshistorie

**v0.10.3.5:** Click-to-Details Event-Listener hinzugefügt  
**v0.10.3.4:** Redirect nach Update korrigiert  
**v0.10.3.3:** Auto-Update Level Enforcement  
**v0.10.3.2:** Cache-Clearing + Dashboard Reload  
**v0.10.3.1:** Gutenberg & Elementor Fixes  
**v0.10.3.0:** Click-to-Details Configuration (Template-Struktur)  

---

**GitHub Tag:** v0.10.3.5  
**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.5
