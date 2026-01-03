# ChurchTools Suite v0.10.3.33 - AJAX Handler Fix

**Veröffentlicht:** 3. Januar 2026  
**Typ:** CRITICAL Bugfix  
**Downgrade von:** v0.10.3.32 NICHT empfohlen

---

## 🚨 KRITISCH: AJAX 500 Fehler behoben

v0.10.3.32 war funktionsfähig (JavaScript-Syntax war korrekt), **ABER** der Kalender-Monatswechsel produzierte **500 Internal Server Error** beim AJAX-Call.

### 🐛 Problem

**Symptom:**
```
POST https://test2-aschaffenburg.feg.de/wp-admin/admin-ajax.php 500 (Internal Server Error)
Response: <p>Es gab einen kritischen Fehler auf deiner Website.</p>
```

**Root Cause:**
```php
// ❌ DEFEKT in ajax_load_calendar_month() Zeile 944:
$events = $template_data->get_events_by_date_range( $first_day, $last_day, $calendar_ids );
//                        ^^^^^^^^^^^^^^^^^^^^^^^^
//                        Diese Methode existiert NICHT!
```

Die Methode `get_events_by_date_range()` existiert **nicht** in `ChurchTools_Suite_Template_Data`.

**Korrekt wäre:**
```php
// ✅ Richtige API:
$events = $template_data->get_events( [
    'from' => $first_day . ' 00:00:00',
    'to' => $last_day . ' 23:59:59',
    'calendar_ids' => $calendar_ids,
    'limit' => 1000,
] );
```

### ✅ Lösung

**Datei:** `includes/class-churchtools-suite-shortcodes.php`  
**Zeilen:** 943-947

**Vorher (v0.10.3.32):**
```php
// Fetch events for date range
$calendar_ids = $calendars_repo->get_selected_calendar_ids();
$events = $template_data->get_events_by_date_range( $first_day, $last_day, $calendar_ids );
```

**Nachher (v0.10.3.33):**
```php
// Fetch events for date range
$calendar_ids = $calendars_repo->get_selected_calendar_ids();
$events = $template_data->get_events( [
    'from' => $first_day . ' 00:00:00',
    'to' => $last_day . ' 23:59:59',
    'calendar_ids' => $calendar_ids,
    'limit' => 1000, // Calendar needs all events in month
] );
```

---

## 🔍 Technische Details

### API-Signatur

**ChurchTools_Suite_Template_Data::get_events()**

```php
/**
 * Get formatted events
 * 
 * @param array $filters {
 *     Optional. Query filters.
 *
 *     @type array  $calendar_ids ChurchTools calendar IDs
 *     @type int    $limit        Maximum number of events
 *     @type string $from         Start date (Y-m-d H:i:s)
 *     @type string $to           End date (Y-m-d H:i:s)
 *     @type string $order        Sort order (ASC|DESC)
 * }
 * @return array Formatted events data
 */
public function get_events( array $filters = [] ): array
```

### Warum 500 Error?

PHP Fatal Error: **Call to undefined method**

```
Fatal error: Uncaught Error: Call to undefined method 
ChurchTools_Suite_Template_Data::get_events_by_date_range()
in /includes/class-churchtools-suite-shortcodes.php:944
```

WordPress fängt PHP Fatal Errors ab und zeigt generischen Fehler:
```html
<p>Es gab einen kritischen Fehler auf deiner Website.</p>
```

### Warum wurde das nicht früher entdeckt?

**v0.10.3.29-31:** Fokus lag auf JavaScript-Fixes (Modal, Navigation, Syntax)  
**Kalender-Test:** Nur Frontend (JavaScript) getestet, NICHT Backend (PHP AJAX)  
**Fehlendes Testing:** Keine Integration Tests für AJAX-Handler

---

## 📋 Änderungen

### 🔧 AJAX Handler Fix

**Datei:** `includes/class-churchtools-suite-shortcodes.php`

```diff
  // Fetch events for date range
  $calendar_ids = $calendars_repo->get_selected_calendar_ids();
- $events = $template_data->get_events_by_date_range( $first_day, $last_day, $calendar_ids );
+ $events = $template_data->get_events( [
+     'from' => $first_day . ' 00:00:00',
+     'to' => $last_day . ' 23:59:59',
+     'calendar_ids' => $calendar_ids,
+     'limit' => 1000, // Calendar needs all events in month
+ ] );
```

**Impact:**
- ✅ Kalender-Monatswechsel funktioniert wieder
- ✅ AJAX-Call erfolgreich
- ✅ Keine 500 Errors mehr

### 📦 Version Bump

- `churchtools-suite.php`: Version **0.10.3.32** → **0.10.3.33**
- `CHURCHTOOLS_SUITE_VERSION`: Konstante aktualisiert

---

## 🧪 Testing

### ✅ Erfolgreich getestet

1. **Kalender-Navigation:**
   - ✅ Monat vor/zurück klickbar
   - ✅ AJAX-Call erfolgreich (200 OK)
   - ✅ Kalender-Grid wird geladen
   - ✅ Events angezeigt

2. **Browser Console:**
   - ✅ Keine JavaScript-Fehler
   - ✅ Keine 500 AJAX-Fehler
   - ✅ Navigation-Logs zeigen korrekten Monat

3. **Server Response:**
   - ✅ JSON erfolgreich zurückgegeben
   - ✅ `success: true`
   - ✅ HTML enthält Kalender-Grid

---

## 🎯 Status nach v0.10.3.33

### ✅ Funktionsfähig

- JavaScript-Syntax korrekt (seit v0.10.3.32)
- AJAX-Handler funktional (seit v0.10.3.33)
- Kalender-Navigation vollständig
- Modal-Display funktioniert
- Event-Handler korrekt

### 🚧 Bekannte Einschränkungen

- Keine bekannten Bugs
- Kalender Minimal Implementation vollständig:
  - ✅ Monatswechsel (vor/zurück)
  - ✅ Event-Anzeige (Grunddaten)
  - ✅ Klick → Popup/Modal

---

## 🔄 Update-Anleitung

### Für End-User (WordPress-Installation)

1. **Update prüfen:**
   - WordPress-Backend → Dashboard → Updates
   - Auto-Updater erkennt v0.10.3.33

2. **Sofort updaten:**
   - Update installieren
   - Browser-Cache leeren: `STRG+SHIFT+R`

3. **Testen:**
   - Seite mit Kalender öffnen
   - Browser Console öffnen (F12)
   - Monat vor/zurück klicken
   - ✅ Sollte funktionieren ohne Fehler

### Für Entwickler

```bash
# Git Update
cd c:\privat\churchtools-suite
git pull origin main

# WordPress ZIP erstellen
cd scripts
.\create-wp-zip.ps1 -Version "0.10.3.33"

# Deployment (falls nötig)
# ZIP liegt in C:\privat\churchtools-suite-0.10.3.33.zip
```

---

## 📚 Lessons Learned

### 1. Method Signature Research

**Problem:** Code ruft nicht-existierende Methode auf  
**Prevention:** 
- IDE-Autocomplete nutzen
- `grep` vor dem Schreiben prüfen
- Type Hints beachten

### 2. Integration Testing

**Problem:** AJAX-Handler wurde nicht getestet  
**Prevention:**
- Browser-Testing nach jedem Fix
- AJAX-Calls in DevTools überwachen
- PHP Error Log prüfen

### 3. API Documentation

**Problem:** Methodennamen falsch erinnert  
**Prevention:**
- Inline PHPDoc lesen
- Repository-Code prüfen
- Nie aus dem Gedächtnis coden

---

## 🔗 Weitere Informationen

- **GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.33
- **Previous Release:** v0.10.3.32 (JavaScript Syntax Fix)
- **Roadmap:** Siehe `ROADMAP.md`

---

## ✨ Credits

**Bug Report:** User Feedback  
**Analysis:** GitHub Copilot  
**Fix:** GitHub Copilot  
**Testing:** Manuell im Browser

---

**WICHTIG:** Bitte sofort auf v0.10.3.33 updaten! v0.10.3.32 ist funktionsunfähig.
