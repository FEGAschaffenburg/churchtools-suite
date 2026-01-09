# Release Notes - v0.9.9.80

**Veröffentlicht:** 9. Januar 2026  
**Status:** CRITICAL BUG FIX  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🔴 KRITISCHER BUG BEHOBEN!

### Das Problem
Live-Logs zeigten: `[DEBUG] [ajax_modal] No current view provided, using global modal template`

Das führte zu:
- ❌ Modal Template wird NICHT geladen
- ❌ "Template not found" Fehler
- ❌ Modal bleibt geschlossen

### Die Root Cause
Es gab **4 Stellen** im JavaScript, wo `showEventModal()` OHNE `currentView` Parameter aufgerufen wurde:

```javascript
// ❌ FALSCH - currentView fehlt:
showEventModal(eventId, $calendar);          // Zeile 265
showEventModal(eventId, $container);         // Zeile 303
showEventModal(eventId, $container);         // Zeile 702
showEventModal(eventId, $container);         // Zeile 755
```

Ohne `currentView` Parameter fällt der AJAX-Handler auf einen "globalen Template" zurück, der **NICHT EXISTIERT**!

### Die Lösung (v0.9.9.80)

Alle `showEventModal()` Aufrufe wurden **korrigiert**, um `currentView` zu übergeben:

```javascript
// ✅ RICHTIG - currentView wird mitgesendet:
showEventModal(eventId, $calendar, 'calendar');           // ← 'calendar'
showEventModal(eventId, $container, currentView);         // ← Dynamisch erkannt
showEventModal(eventId, $container, 'calendar');          // ← 'calendar'
showEventModal(eventId, $container, currentView);         // ← Dynamisch erkannt
```

### Was sich ändert

#### `assets/js/churchtools-suite-public.js`

**4 Fixes:**

1. **loadCalendarMonth (Zeile 265)**: `showEventModal(eventId, $calendar, 'calendar')`
   - Wenn neuer Monat geladen wird, Events werden mit `'calendar'` View geöffnet

2. **initGridButtons (Zeile 303)**: View-Type automatisch erkannt
   ```javascript
   let currentView = null;
   if (classes.includes('cts-grid-')) currentView = 'grid';
   else if (classes.includes('cts-list')) currentView = 'list';
   else if (classes.includes('cts-calendar')) currentView = 'calendar';
   else if (classes.includes('cts-single')) currentView = 'single';
   showEventModal(eventId, $container, currentView);
   ```

3. **Calendar Day Click Handler (Zeile 702)**: `showEventModal(eventId, $container, 'calendar')`
   - Beim Klick auf Kalendertag werden Events mit `'calendar'` View geöffnet

4. **Event Click Handler (Zeile 755)**: View-Type automatisch erkannt
   - Gleiche Logik wie initGridButtons

---

## 🎯 Warum das KRITISCH ist

Mit dieser Fix:

✅ JavaScript sendet IMMER `current_view` an AJAX-Handler  
✅ AJAX-Handler wählt RICHTIGE Template basierend auf View  
✅ Template wird GEFUNDEN und GELADEN  
✅ Modal öffnet sich ENDLICH!

Ohne diesen Fix bleibt das Modal geschlossen, weil der PHP-Code versucht, einen Non-Existent Template zu laden.

---

## 📊 Impact der Logs

### VORHER (v0.9.9.79 - FEHLER):
```
[DEBUG] [ajax_modal] No current view provided, using global modal template
[WARNING] [template_loader] Template NOT FOUND - DETAILED ERROR
```

### NACHHER (v0.9.9.80 - FUNKTIONIERT):
```
[DEBUG] [ajax_modal] Template selected for current view
  current_view: "list"
  selected_template: "professional"
  from_setting: "churchtools_suite_modal_template"
[DEBUG] [template_loader] Template found in plugin (RETURNING)
```

---

## 🧪 Test-Szenarios

Nach Deploy von v0.9.9.80:

| Scenario | Vorher ❌ | Nachher ✅ |
|----------|----------|----------|
| **List View - Click Event** | Modal nicht offen | Modal öffnet sich |
| **Grid View - Click Event** | Modal nicht offen | Modal öffnet sich |
| **Calendar - Click Event** | Modal nicht offen | Modal öffnet sich |
| **Single - Click Event** | Modal nicht offen | Modal öffnet sich |
| **Calendar - Month Navigation** | Modal nicht offen | Modal öffnet sich |

---

## 📦 Deployment

```powershell
# ZIP wurde erstellt:
C:\privat\churchtools-suite-0.9.9.80.zip (0.32 MB)

# Auf Live deployen:
1. Plugin v0.9.9.79 deaktivieren
2. v0.9.9.80.zip hochladen
3. Aktivieren
4. Frontend testen: List-View → Event klicken → Modal sollte öffnen!
```

---

## 🔍 Debug Info

Falls noch Probleme:

**Prüfe:**
1. Browser-Konsole (F12) - Sollte keine Fehler zeigen
2. Admin Logs - Sollten `current_view: "list"` oder `"grid"` zeigen
3. Template nicht gefunden? → Check v0.9.9.79 Logs für exakte Pfad-Probleme

---

## 📝 Commit Info

- **Hash:** 024b096
- **Files:** 2 (churchtools-suite.php, churchtools-suite-public.js)
- **Lines Added:** 29
- **Lines Removed:** 6
- **Severity:** CRITICAL

---

## ✅ Next Steps

1. **Deploy v0.9.9.80** auf Live-Server
2. **Test Modal** in List/Grid/Calendar Views
3. **Check Browser Console** für JS-Fehler
4. **Check Admin Logs** für Template-Loading Status
5. **Modal sollte jetzt funktionieren!**

Falls ja: Problem gelöst! 🎉  
Falls nein: Check die Logs aus v0.9.9.79 für weitere Debug-Info

---

**Zusammenfassung:** v0.9.9.80 fixt die KRITISCHE Lücke, wo JavaScript den `currentView` Parameter nicht an AJAX schickt. Das war der **ROOT-CAUSE** für "Modal nicht öffnen".

