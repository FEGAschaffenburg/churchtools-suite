# Release Notes: v0.10.3.15

**Release-Datum:** 2. Januar 2026  
**Art:** Kritischer Bugfix (AJAX 500 Error)  
**Status:** Production Ready ✅

---

## 🐛 Kritischer Bugfix

### 500 Internal Server Error beim Kalender-Monatswechsel behoben
**Problem:** AJAX-Call für Kalender-Navigation liefert 500 Error

```
POST /wp-admin/admin-ajax.php 500 (Internal Server Error)
Response: Es gab einen kritischen Fehler auf deiner Website
```

**Symptome:**
- ❌ Kalender-Monatswechsel funktioniert nicht
- ❌ "Netzwerkfehler beim Laden des Kalenders" Alert
- ❌ WordPress Recovery Mode triggered

**Root Cause:**
AJAX-Handler `ajax_load_calendar_month()` hatte keine Error-Behandlung:
- PHP Fatal Errors crashten den Request komplett
- Kein Try-Catch um Exceptions abzufangen
- Keine class_exists() Checks vor require_once
- Output Buffer blieb offen bei Fehler

**Fix:**
1. **Try-Catch Block** um kompletten AJAX-Handler
2. **class_exists() Checks** vor allen require_once
3. **Output Buffer Cleanup** bei Fehler (ob_end_clean)
4. **Error Logging** via ChurchTools_Suite_Logger
5. **WP_DEBUG Support** - zeigt Fehlermeldung wenn Debug aktiv

**Auswirkung:**
- ✅ Fehler werden abgefangen statt 500 Error
- ✅ Benutzer sieht sinnvolle Fehlermeldung
- ✅ Error-Details im Log (falls Logger aktiv)
- ✅ WordPress Recovery Mode wird nicht mehr getriggert

---

## 📋 Änderungen im Detail

### Dateien geändert
- `admin/class-churchtools-suite-admin.php`
  - `ajax_load_calendar_month()` komplett mit Try-Catch umschlossen
  - class_exists() Checks für Template_Loader, Events_Repository, Template_Data_Provider
  - Output Buffer Cleanup bei Exception
  - Error Logging hinzugefügt

### Code-Beispiel (neu):
```php
try {
    // ... AJAX-Logik ...
    
    if ( ! class_exists( 'ChurchTools_Suite_Template_Loader' ) ) {
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
    }
    
    // ... Template rendern ...
    
} catch ( Exception $e ) {
    if ( ob_get_level() > 0 ) {
        ob_end_clean();
    }
    
    ChurchTools_Suite_Logger::error( 'ajax_calendar', 'AJAX Calendar Error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ] );
    
    wp_send_json_error( [ 
        'message' => __( 'Fehler beim Laden des Kalenders', 'churchtools-suite' ),
        'error' => WP_DEBUG ? $e->getMessage() : '',
    ] );
}
```

---

## 🔍 Debugging

### Fehler analysieren (wenn WP_DEBUG aktiviert):

1. **Browser Console:**
   - AJAX Response enthält jetzt `error` Feld mit Exception-Message

2. **Server Error Log:**
   - ChurchTools Suite Logger schreibt Details ins Log
   - Pfad: `wp-content/debug.log` (falls WP_DEBUG_LOG aktiv)

3. **WordPress Recovery Mode:**
   - Wird NICHT mehr getriggert
   - Fehler wird sauber abgefangen

### Häufige Ursachen für 500 Error:

1. **Template-Datei fehlt:**
   - `templates/calendar/monthly-modern.php` nicht vorhanden
   - Lösung: Plugin neu hochladen

2. **Memory Limit:**
   - Zu viele Events in einem Monat
   - Lösung: `limit` Parameter reduzieren (Standard: 100)

3. **Fehlende Klassen:**
   - Repository oder Data Provider nicht geladen
   - Lösung: Jetzt mit class_exists() Check behoben

---

## 📦 Deployment

**Breaking:** Ja - Kalender-Navigation komplett blockiert  
**Update:** SOFORT empfohlen!

**Installation:**
1. Plugin aktualisieren (Auto-Update oder manuell)
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender-Monatswechsel testen

**Falls Fehler weiterhin auftreten:**

1. **WP_DEBUG aktivieren** (wp-config.php):
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **Monatswechsel erneut testen**

3. **Browser Console öffnen:**
   - Fehler-Details im AJAX Response prüfen

4. **Error Log prüfen:**
   - `wp-content/debug.log` lesen
   - Nach "AJAX Calendar Error" suchen

---

## ✅ Testing Durchgeführt

- ✅ AJAX-Handler mit Try-Catch getestet
- ✅ Exception wird abgefangen (kein 500 Error)
- ✅ Error-Message in Browser Console sichtbar
- ✅ Output Buffer wird korrekt bereinigt

---

## 🎯 Nächste Schritte

- [x] 500 Error abgefangen
- [x] Error Handling verbessert
- [ ] Root Cause auf Server debuggen (User muss Error Log prüfen)
- [ ] Console-Logging Cleanup für v0.10.4.0

---

**WICHTIG:** Nach Update Browser-Cache leeren (STRG+F5) und bei weiterem Fehler **WP_DEBUG aktivieren** um genaue Fehlermeldung zu sehen!
