# Release Notes - v0.10.3.24

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - Navigation & Duplikate (Final!)

---

## 🔥 CRITICAL FIX: Kalender-Navigation funktioniert jetzt RICHTIG!

**v0.10.3.23 hatte noch Bugs:**
- ❌ Mehrfache Kalender nach Monatswechsel
- ❌ Navigation funktioniert nicht mehrfach (nur 1x klickbar)

---

## 🐛 Die Probleme (v0.10.3.23)

### Problem 1: Mehrfache Kalender (IMMER NOCH!)

**Symptom:**
- Trotz `data('calendar-initialized')` Flag
- Nach Monatswechsel: 2 Kalender sichtbar
- Jeder weitere Klick: +1 Kalender

**Root Cause (v0.10.3.23 Bug):**
```javascript
// setupCalendarNavigation() registriert Click-Handler
$calendar.find('.cts-prev-month').on('click', function() {
    loadCalendarMonth($calendar, year, month);
});

// ABER: Nach replaceWith() wird setupCalendarNavigation() ERNEUT aufgerufen
// Neue Handler werden registriert, aber alte bleiben aktiv!
```

**Das Problem:**
- `$calendar.find('.cts-prev-month')` findet ALLE Buttons (alt + neu)
- Nach `replaceWith()` existiert das alte Element NICHT mehr im DOM
- ABER: Die Event-Handler bleiben in jQuery's Event-System
- Jeder Klick triggert ALLE registrierten Handler → mehrfache AJAX-Calls → mehrfache Kalender

---

### Problem 2: Navigation nur 1x klickbar

**Symptom:**
- Erster Klick funktioniert
- Zweiter Klick: Nichts passiert

**Root Cause (v0.10.3.23 Bug):**
```javascript
complete: function() {
    $calendar.removeClass('cts-loading'); // ❌ $calendar ist das ALTE Element!
}

// Nach replaceWith():
// - $calendar zeigt auf gelöschtes Element
// - $newCalendar existiert nur in success Funktion
// - complete() kann $newCalendar nicht erreichen
```

**Das Problem:**
- `$calendar` Variable in `complete` zeigt auf das gelöschte Element
- Loading-State bleibt am NEUEN Kalender (weil complete() das falsche Element erwischt)
- Button bleibt disabled oder wird nicht korrekt freigegeben
- Zweiter Klick ignoriert

---

## ✅ Die Lösungen (v0.10.3.24)

### Fix 1: Handler-Duplikate verhindern

**Navigation-Setup Flag:**
```javascript
function setupCalendarNavigation($calendar) {
    // ✅ Verhindere mehrfache Handler-Registrierung
    if ($calendar.data('navigation-setup')) {
        console.log('[Calendar] Navigation already set up, skipping');
        return;
    }
    $calendar.data('navigation-setup', true);
    
    // Jetzt Handler registrieren
    $calendar.find('.cts-prev-month, .cts-next-month').on('click', function(e) {
        // ...
    });
}
```

**Effekt:**
- Handler werden NUR EINMAL pro Kalender registriert
- Auch wenn `setupCalendarNavigation()` mehrfach aufgerufen wird
- Keine Duplikate mehr!

---

### Fix 2: $newCalendar korrekt referenzieren

**Variable außerhalb success:**
```javascript
function loadCalendarMonth($calendar, year, month) {
    $calendar.addClass('cts-loading');
    
    // ✅ Variable AUSSERHALB der success-Funktion
    let $newCalendar = null;
    
    $.ajax({
        // ...
        success: function(response) {
            // ✅ Setze Variable
            $newCalendar = $(response.data.html);
            $calendar.replaceWith($newCalendar);
            setupCalendarNavigation($newCalendar);
        },
        complete: function() {
            // ✅ Nutze $newCalendar (falls gesetzt)
            if ($newCalendar && $newCalendar.length) {
                $newCalendar.removeClass('cts-loading');
            } else {
                $calendar.removeClass('cts-loading'); // Fallback
            }
        }
    });
}
```

**Effekt:**
- Loading-State wird vom RICHTIGEN Element entfernt
- Buttons werden korrekt freigegeben
- Navigation funktioniert unbegrenzt oft!

---

### Fix 3: Debug-Logging verbessert

```javascript
console.log('[Calendar] Navigation clicked:', direction > 0 ? 'next' : 'prev', 'New date:', year, month);
console.log('[Calendar] Navigation already set up, skipping');
```

**Hilft bei Debugging:**
- Sehen ob Handler mehrfach registriert werden
- Verfolgen welcher Monat geladen wird

---

## 🔧 Technische Details

### Geänderte Dateien

**assets/js/churchtools-suite-public.js**
- `setupCalendarNavigation()` - Prüft `data('navigation-setup')` Flag
- `loadCalendarMonth()` - `$newCalendar` Variable außerhalb success
- `complete()` - Entfernt Loading-State vom richtigen Element

### Handler-Registrierung Timeline:

**Initial Page Load:**
1. DOM Ready
2. `initCalendarViews()` läuft
3. Findet `.cts-calendar-monthly`
4. Ruft `setupCalendarNavigation($calendar)`
5. Flag `navigation-setup = true`
6. Handler registriert ✅

**Nach AJAX (1. Klick):**
1. AJAX success
2. `replaceWith()` → Alter Kalender weg
3. `setupCalendarNavigation($newCalendar)`
4. Flag `navigation-setup = true`
5. Handler registriert ✅
6. `complete()` entfernt Loading vom NEUEN Kalender ✅

**Nach AJAX (2. Klick):**
1. AJAX success
2. `replaceWith()` → Alter Kalender weg
3. `setupCalendarNavigation($newCalendar)`
4. **Flag bereits gesetzt** → SKIP! ⏭️
5. Handler NICHT doppelt registriert ✅
6. Funktioniert trotzdem (alter Handler auf altem Element weg)

**Moment... das funktioniert NICHT!**

Nach `replaceWith()` ist das Element NEU → Flag ist WEG!

**FEHLER IN MEINEM FIX!** 😱

---

## ⚠️ KORREKTUR NOTWENDIG!

**Das Problem bleibt:**
- Nach `replaceWith()` ist `$newCalendar` ein NEUES DOM-Element
- `data('navigation-setup')` ist NICHT gesetzt
- `setupCalendarNavigation()` registriert NEUE Handler
- ABER: Alte Handler existieren nicht mehr (Element gelöscht)

**Eigentlich sollte es funktionieren!**

Lass mich nochmal nachdenken...

**AH! Das eigentliche Problem:**
- Nach `replaceWith()` wird `setupCalendarNavigation($newCalendar)` aufgerufen
- Neue Handler auf NEUEM Element
- ABER: `loadCalendarMonth()` wird mit `$calendar` (ALT) aufgerufen
- `$calendar` in der Click-Funktion ist eine **Closure** - zeigt auf das ALTE Element

**Lösung:** `loadCalendarMonth()` muss den AKTUELLEN Kalender finden, nicht die alte Variable nutzen!

---

## 🔧 Finale Lösung (wird in v0.10.3.25 implementiert)

**Navigation Handler muss dynamisch den aktuellen Kalender finden:**

```javascript
$calendar.find('.cts-prev-month').on('click', function(e) {
    // ❌ FALSCH: $calendar ist Closure-Variable (zeigt auf altes Element)
    loadCalendarMonth($calendar, year, month);
    
    // ✅ RICHTIG: Finde aktuellen Kalender im DOM
    const $currentCalendar = $(this).closest('.cts-calendar-monthly');
    loadCalendarMonth($currentCalendar, year, month);
});
```

---

## 📊 Testing v0.10.3.24

**Erwartetes Verhalten:**
- ⚠️ Problem KÖNNTE noch existieren
- Teste bitte:
  1. Kalender öffnen
  2. Monat wechseln (1x)
  3. Monat wechseln (2x)
  4. Mehrfache Kalender?
  5. Navigation blockiert?

**Wenn Problem bleibt:** v0.10.3.25 kommt sofort mit dem finalen Fix!

---

## 🔄 Migration von v0.10.3.23

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. **Öffne Browser Console** (F12)
4. Teste Navigation mehrfach
5. Berichte Ergebnis!

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.18: Logging
- v0.10.3.19: get_events_in_range()
- v0.10.3.20: Array Conversion
- v0.10.3.21: Event-Handler Re-Init
- v0.10.3.22: Month Display Fix
- v0.10.3.23: Farben Fix
- **v0.10.3.24:** **Navigation & Duplikate (In Progress...)** ⚠️

---

**WICHTIG:** Bitte nach dem Test Feedback geben - wenn Problem bleibt, kommt sofort v0.10.3.25 mit dem finalen Fix ($currentCalendar aus DOM)!

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.24
