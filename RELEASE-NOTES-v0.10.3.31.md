# Release Notes - Version 0.10.3.31

**Release Date:** 3. Januar 2026  
**Status:** CRITICAL Bug Fix (Hotfix)

---

## 🐛 Critical Bug Fix

### Calendar Navigation Event Handlers Fixed
**Problem:** v0.10.3.30 hatte Navigation-Buttons ohne funktionierende Event-Handler nach AJAX-Reload.

**Root Cause:**
```javascript
function setupCalendarNavigation($calendar) {
    // Verhindere mehrfache Handler-Registrierung
    if ($calendar.data('navigation-setup')) {
        console.log('[Calendar] Navigation already set up, skipping');
        return; // ❌ FEHLER: Handler werden nicht erneut attached!
    }
    $calendar.data('navigation-setup', true);
    // ...
}
```

**Symptome:**
- ❌ Monatswechsel-Buttons (Prev/Next) reagieren nicht auf Klick
- ❌ Keine Console-Logs bei Button-Klick
- ❌ Kein AJAX-Request wird ausgelöst
- ❌ Buttons haben keine aktiven Event-Handler nach Grid-Replacement

**Lösung:**
1. **navigation-setup Check entfernt** - Flag verhinderte Re-Initialisierung
2. **`.off('click')` vor `.on('click')`** - Alte Handler werden sauber entfernt
3. **setupCalendarNavigation() nach AJAX** - Handler werden bei jedem Grid-Update neu attached

**Dateien geändert:**
- `assets/js/churchtools-suite-public.js` - Navigation-Setup ohne Flag-Check, mit Handler-Cleanup

---

## 📋 Technical Details

### JavaScript Changes

**VORHER (v0.10.3.30 - DEFEKT):**
```javascript
function setupCalendarNavigation($calendar) {
    if ($calendar.data('navigation-setup')) {
        return; // ❌ Blocks re-initialization!
    }
    $calendar.data('navigation-setup', true);
    
    $calendar.find('.cts-prev-month, .cts-next-month').on('click', ...);
}

// AJAX success:
setupCalendarNavigation($calendar); // ❌ Wird blockiert durch Flag!
```

**NACHHER (v0.10.3.31 - FIXED):**
```javascript
function setupCalendarNavigation($calendar) {
    // Remove old handlers to prevent duplicates
    $calendar.find('.cts-prev-month, .cts-next-month').off('click');
    
    $calendar.find('.cts-prev-month, .cts-next-month').on('click', ...);
}

// AJAX success:
setupCalendarNavigation($calendar); // ✅ Funktioniert jetzt!
```

### Why `.off()` instead of Flag?

**Flag-Ansatz (FALSCH):**
- Verhindert Re-Initialisierung komplett
- Handler gehen verloren bei DOM-Updates
- Funktioniert nicht mit dynamischem Content

**`.off()` Ansatz (RICHTIG):**
- Entfernt alte Handler sauber
- Erlaubt Re-Initialisierung
- Verhindert Duplikate durch explizites Cleanup
- Standard jQuery Pattern für dynamische Events

---

## ✅ What's Fixed

- ✅ Navigation-Buttons haben nach AJAX-Reload funktionierende Handler
- ✅ Monatswechsel funktioniert (Prev/Next Clicks werden registriert)
- ✅ Console-Logs erscheinen bei Navigation-Klicks
- ✅ AJAX-Requests werden korrekt ausgelöst
- ✅ Keine doppelten Event-Handler (durch `.off()` cleanup)

---

## 🔄 Migration Notes

**Keine Änderungen:**
- Keine DB-Migrationen
- Keine Settings-Änderungen
- Keine Template-Änderungen

**Testing:**
1. Browser-Cache leeren (CTRL+SHIFT+R)
2. Kalender-Seite aufrufen
3. Browser-Konsole öffnen (F12)
4. Prev/Next Button klicken
5. Erwartete Logs:
   ```
   [Calendar] Navigation clicked: prev New date: 2025 12
   [Calendar] Loading month: 2025 12 enableModal: true
   [Calendar] AJAX success: {success: true, data: {...}}
   [Calendar] Re-attaching navigation handlers
   [Calendar] Setting up navigation for calendar
   ```

---

## 🎯 Minimal Calendar - NOW WORKING

**Features (alle funktional):**
1. ✅ **Monatswechsel** - Prev/Next Buttons mit Event-Handlern
2. ✅ **Grunddaten-Anzeige** - Datum, Titel, Zeit
3. ✅ **Click → Popup** - Modal mit Details

**Event Handler Flow:**
```
Page Load → setupCalendarNavigation() → Handlers attached
User clicks Prev → AJAX request → Grid replaced
AJAX success → setupCalendarNavigation() → Handlers RE-attached ✅
```

---

## 🔗 Related Issues

**Fixed:**
- Navigation-Buttons ohne Event-Handler nach v0.10.3.30
- Flag-Check blockierte Re-Initialisierung

**Previous Releases:**
- v0.10.3.30 - Grid-only replacement (Navigation blieb erhalten, aber ohne Handler)
- v0.10.3.29 - Modal Toggle Respect

---

## 📚 Lessons Learned

**Anti-Pattern:**
```javascript
if ($element.data('initialized')) return; // ❌ FALSCH bei dynamischem Content!
```

**Best Practice:**
```javascript
$element.off('click').on('click', handler); // ✅ RICHTIG für Re-Initialisierung
```

**Regel:** Bei AJAX-ersetztem Content IMMER Event-Handler neu attachieren, nie mit Flag blockieren!

---

**Changelog:** CRITICAL HOTFIX - Calendar navigation event handlers fixed (removed blocking flag)
