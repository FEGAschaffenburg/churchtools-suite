# Release Notes: v0.10.3.17

**Release-Datum:** 2. Januar 2026  
**Art:** Kritischer Bugfix (Kalender-Navigation Fatal Error)  
**Status:** Production Ready ✅

---

## 🐛 Kritischer Bugfix

### Kalender-Monatswechsel Fatal Error behoben
**Problem:** AJAX-Call für Kalender-Navigation liefert 500 Internal Server Error

```
POST /wp-admin/admin-ajax.php 500 (Internal Server Error)
Response: Es gab einen kritischen Fehler auf deiner Website
```

**Symptome:**
- ❌ Kalender-Monatswechsel funktioniert nicht
- ❌ "Netzwerkfehler beim Laden des Kalenders" Alert
- ❌ WordPress Recovery Mode blockiert Request

**Root Cause:**
AJAX-Handler `ajax_load_calendar_month()` rief **falsche Methode** auf:

```php
// FEHLER (v0.10.2.7 - v0.10.3.16)
ChurchTools_Suite_Template_Loader::load_template( 'calendar', 'monthly-modern', [...] );
//                                   ^^^^^^^^^^^^^ EXISTIERT NICHT!

// KORREKT (v0.10.3.17)
ChurchTools_Suite_Template_Loader::render_template( 'calendar/monthly-modern.php', [...], false );
//                                   ^^^^^^^^^^^^^^^ RICHTIGE METHODE
```

**Warum 500 Error statt Try-Catch?**
- PHP Fatal Error: "Call to undefined method"
- WordPress Recovery Mode greift VOR unserem Try-Catch
- Request wird komplett abgeblockt

---

## ✅ **Fix**

### Methoden-Aufruf korrigiert

**VORHER (Zeile 2099):**
```php
ob_start();
ChurchTools_Suite_Template_Loader::load_template( 'calendar', 'monthly-modern', [
    'events' => $events,
    'args' => $atts,
] );
$html = ob_get_clean();
```

**Problem:**
- `load_template()` existiert nicht in `ChurchTools_Suite_Template_Loader`
- PHP Fatal Error: "Call to undefined method"
- Output Buffer bleibt offen

**NACHHER (v0.10.3.17):**
```php
$html = ChurchTools_Suite_Template_Loader::render_template( 'calendar/monthly-modern.php', [
    'events' => $events,
    'args' => $atts,
], false );
```

**Korrekturen:**
1. ✅ `render_template()` statt `load_template()` (richtige Methode)
2. ✅ Template-Pfad mit `.php` Extension (`calendar/monthly-modern.php`)
3. ✅ 3. Parameter `false` für Return statt Echo
4. ✅ Kein manueller Output Buffer nötig (Methode handled das)

---

## 📋 Änderungen im Detail

### Dateien geändert
- `admin/class-churchtools-suite-admin.php`
  - `ajax_load_calendar_month()` Zeile 2099
  - `load_template()` → `render_template()`
  - Template-Pfad korrigiert
  - Output Buffer Handling entfernt

---

## 🔍 Technische Details

### Template-Loader Methoden (class-churchtools-suite-template-loader.php):

**Verfügbare Methoden:**
```php
public static function locate_template( string $template_name )
public static function render_template( string $template_name, array $args = [], bool $echo = true )
```

**KEINE Methode:**
```php
public static function load_template( ... ) // ❌ EXISTIERT NICHT
```

### Warum wurde das nicht früher entdeckt?

1. **Kalender-Navigation ist neu** (v0.10.2.7)
2. **AJAX-Handler wurde nie getestet** (nur Initial-Load funktionierte)
3. **Try-Catch greift nicht bei Fatal Errors** (WordPress Recovery Mode)

### Richtige Template-Pfade:

```php
// FALSCH
render_template( 'calendar', 'monthly-modern', ... )

// KORREKT
render_template( 'calendar/monthly-modern.php', ... )
```

---

## 📦 Deployment

**Breaking:** Ja - Kalender-Navigation komplett blockiert seit v0.10.2.7  
**Update:** SOFORT empfohlen!

**Installation:**
1. Plugin aktualisieren (Auto-Update oder manuell)
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender-Monatswechsel testen
4. ✅ Sollte jetzt funktionieren!

---

## ✅ Testing Durchgeführt

**Kalender-Navigation:**
- ✅ Monatswechsel Vor/Zurück funktioniert
- ✅ Keine 500 Errors mehr
- ✅ HTML wird korrekt geladen
- ✅ Events werden angezeigt

**Console-Logs:**
```
[Calendar] Loading month: 2026 2 enableModal: true raw: true
[Calendar] AJAX success: {success: true, data: {html: "...", month: 2, year: 2026}}
```

---

## 🎯 Zusammenfassung

**Problem:** Fatal Error wegen falscher Methode  
**Dauer:** Seit v0.10.2.7 (Kalender-Navigation eingeführt)  
**Auswirkung:** 100% Kalender-Navigation blockiert  
**Fix:** `load_template()` → `render_template()`  

**Lesson Learned:**
- AJAX-Handler müssen getestet werden (nicht nur Initial-Load)
- Try-Catch greift nicht bei Fatal Errors (Recovery Mode greift früher)
- Template-Loader API-Dokumentation fehlt (TODO)

---

**Entschuldigung für die Unannehmlichkeiten!** Dieser Fehler existierte seit v0.10.2.7 und wurde erst jetzt durch intensives User-Testing entdeckt. Danke für die Geduld! 🙏
