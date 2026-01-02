# Release Notes - v0.10.3.20

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - AJAX Calendar Teil 2

---

## 🔥 CRITICAL FIX: Nicht existierende Template_Data_Provider Methode

**v0.10.3.19 kam viel weiter, crashte aber beim Formatieren!**

---

## 🐛 Das Problem

### Logs zeigten (v0.10.3.19):
```
✅ get_events_in_range called
✅ Executing SQL query
✅ Query results
✅ Events fetched
✅ Loading Template_Data_Provider class
❌ CRASH - "Formatting events" fehlt!
```

### Code-Fehler:

**AJAX-Handler (admin/class-churchtools-suite-admin.php):**
```php
// Klassen-Name FALSCH!
if (!class_exists('ChurchTools_Suite_Template_Data_Provider')) {
    require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-data-provider.php';
}

// Methode EXISTIERT NICHT!
$events = ChurchTools_Suite_Template_Data_Provider::format_events_for_template($raw_events);
```

**Probleme:**
1. ❌ Klasse heißt `ChurchTools_Suite_Template_Data` (NICHT `_Provider`)
2. ❌ Datei heißt `class-churchtools-suite-template-data.php` (NICHT `_provider`)
3. ❌ Statische Methode `format_events_for_template()` existiert NICHT
4. ❌ Klasse ist NICHT statisch, braucht Instanz!

---

## ✅ Die Lösung

### Vereinfachter Ansatz:

Events sind bereits **DB-Objekte** aus Repository → brauchen nur Array-Konvertierung!

**Neuer Code:**
```php
// Formatiere Events für Template
// Events sind schon DB-Objekte, müssen nur in Array konvertiert werden
ChurchTools_Suite_Logger::debug('ajax_calendar', 'Converting events to array');
$events = [];
foreach ($raw_events as $event) {
    $events[] = (array) $event;
}

ChurchTools_Suite_Logger::debug('ajax_calendar', 'Events converted', [
    'count' => count($events),
]);
```

**Warum das funktioniert:**
- Repository gibt DB-Objekte zurück (stdClass)
- Template erwartet Arrays
- Einfache Konvertierung: `(array) $event`
- KEINE komplexe Formatierung nötig für AJAX

---

## 🔧 Technische Details

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php**
- `ajax_load_calendar_month()` - Template_Data_Provider entfernt
- Direkte Array-Konvertierung statt statischer Methode
- Logging für Conversion-Step

### Warum passierte dieser Fehler?

**Code-Evolution:**
1. Früher gab es vermutlich eine statische Methode
2. Wurde refactored zu Klassen-basiertem Ansatz
3. AJAX-Handler wurde nie aktualisiert
4. Methode existierte nie in aktueller Codebase

---

## 📊 Testing

### Erwartetes Verhalten:
- ✅ Events werden geladen
- ✅ In Arrays konvertiert
- ✅ Template rendert korrekt
- ✅ Kalender-Navigation funktioniert

### Debug-Logs (wenn aktiviert):
```
[DEBUG] [ajax_calendar] Events fetched (count: X)
[DEBUG] [ajax_calendar] Converting events to array
[DEBUG] [ajax_calendar] Events converted (count: X)
[DEBUG] [ajax_calendar] Rendering template
[DEBUG] [ajax_calendar] Template rendered
```

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.15: Try-Catch → Half nicht
- v0.10.3.17: Template-Loader → Half nicht
- v0.10.3.18: **Logging** → Root Cause Detector
- v0.10.3.19: **get_events_in_range()** hinzugefügt → Kam viel weiter!
- **v0.10.3.20:** **Template_Data_Provider** entfernt → **Sollte jetzt funktionieren!** ✅

---

## 🔄 Migration von v0.10.3.19

Keine Datenbank-Änderungen. Nur Code-Update.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Kalender testen** - Monatswechsel sollte jetzt funktionieren!

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.20
