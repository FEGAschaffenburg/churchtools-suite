# Release Notes - v0.10.3.27

**Release-Datum:** 2. Januar 2026  
**Typ:** BUGFIX - Tooltip-Optionen Fix

---

## 🐛 KRITISCHER BUGFIX: Tooltip-Optionen funktionieren jetzt WIRKLICH!

**v0.10.3.26 hatte ein Problem:** Die Schalter im Gutenberg-Block hatten **KEINE Wirkung**! 😱

---

## 🔧 Was war das Problem?

### Issue #1: Fehlende Attribute im Shortcode-Handler

**Vorher (v0.10.3.26):**
```php
// calendar_shortcode() hatte NUR:
'show_services' => true,
'show_description' => true,
'show_location' => true,
```

**Problem:**
- ❌ `show_time` fehlte komplett
- ❌ `show_calendar_name` fehlte komplett
- ❌ Alle Defaults waren auf `true` (falsch!)

**Jetzt (v0.10.3.27):**
```php
// Alle 5 Optionen mit korrekten Defaults:
'show_time' => true,              // ✅ Standardmäßig AN
'show_description' => false,      // ✅ Standardmäßig AUS
'show_location' => false,         // ✅ Standardmäßig AUS
'show_services' => false,         // ✅ Standardmäßig AUS
'show_calendar_name' => false,    // ✅ Standardmäßig AUS
```

---

### Issue #2: Block Normalisierung unvollständig

**Vorher:**
```php
// normalize_block_attributes() prüfte NUR:
if ( ! isset( $parsed_block['attrs']['show_services'] ) ) { ... }
if ( ! isset( $parsed_block['attrs']['show_description'] ) ) { ... }
if ( ! isset( $parsed_block['attrs']['show_location'] ) ) { ... }
```

**Problem:**
- ❌ Alte Blocks (gespeichert vor v0.10.3.26) hatten **KEINE** `show_time` oder `show_calendar_name` Attribute
- ❌ Diese Blocks zeigten dann IMMER die alten Defaults

**Jetzt:**
```php
// Alle 5 Optionen werden normalisiert:
if ( ! isset( $parsed_block['attrs']['show_time'] ) ) {
    $parsed_block['attrs']['show_time'] = true;
}
if ( ! isset( $parsed_block['attrs']['show_calendar_name'] ) ) {
    $parsed_block['attrs']['show_calendar_name'] = false;
}
```

---

## ✅ Was funktioniert jetzt?

### 1. **Gutenberg-Block Schalter**
- ☑️ **Uhrzeit anzeigen** → Checkbox funktioniert!
- ☐ **Beschreibung anzeigen** → Checkbox funktioniert!
- ☐ **Ort anzeigen** → Checkbox funktioniert!
- ☐ **Services anzeigen** → Checkbox funktioniert!
- ☐ **Kalender-Name anzeigen** → Checkbox funktioniert!

### 2. **Tooltip-Anzeige**
```
Wenn ALLE Optionen aktiviert sind:

7. FEB 26 - Gottesdienst
🕐 10:00 Uhr - 12:00 Uhr
📍 Gemeindehaus Aschaffenburg
📅 Gottesdienst
👤 Predigt: Max Mustermann, Musik: Anna Schmidt
📝 Mit Predigt von Pastor Schmidt und anschließendem...
```

### 3. **Alte Blocks kompatibel**
- Alte Blocks (gespeichert vor v0.10.3.26) bekommen automatisch die korrekten Defaults
- Keine manuelle Anpassung notwendig

---

## 🧪 Testing

### Test 1: Standard (nur Uhrzeit)
1. Block einfügen
2. **Alle Checkboxen AUS** (außer "Uhrzeit anzeigen" bleibt AN)
3. Speichern + Vorschau
4. **Erwartung:** Tooltip zeigt nur Datum + Titel + Uhrzeit

### Test 2: Alle Optionen AN
1. Block öffnen
2. **Alle Checkboxen AKTIVIEREN**
3. Speichern + Vorschau
4. **Erwartung:** Tooltip zeigt: Datum + Titel + Zeit + Ort + Kalender + Services + Beschreibung

### Test 3: Alter Block
1. Seite mit **altem Block** (vor v0.10.3.26) öffnen
2. **NICHT bearbeiten** - einfach ansehen
3. **Erwartung:** Tooltip zeigt nur Datum + Titel + Uhrzeit (korrekte Defaults)

---

## 🔄 Migration

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren auf v0.10.3.27
2. **Browser-Cache leeren** (STRG+F5)
3. Gutenberg-Editor öffnen
4. **Block NICHT neu erstellen** - alte Blocks funktionieren automatisch!
5. Optionen testen: Checkboxen aktivieren/deaktivieren
6. Speichern + Tooltip prüfen

---

## 🎯 Geänderte Dateien

### 1. `includes/class-churchtools-suite-shortcodes.php`
**Änderung:** Alle 5 Optionen mit korrekten Defaults

```php
// Vorher (v0.10.3.26):
$atts = shortcode_atts( [
    'show_services' => true,      // ❌ Falsch
    'show_description' => true,    // ❌ Falsch
    'show_location' => true,       // ❌ Falsch
    // show_time fehlte!          // ❌ Fehlt
    // show_calendar_name fehlte! // ❌ Fehlt
], $atts, 'cts_calendar' );

// Jetzt (v0.10.3.27):
$atts = shortcode_atts( [
    'show_time' => true,           // ✅ Korrekt
    'show_description' => false,   // ✅ Korrekt
    'show_location' => false,      // ✅ Korrekt
    'show_services' => false,      // ✅ Korrekt
    'show_calendar_name' => false, // ✅ Korrekt
], $atts, 'cts_calendar' );
```

### 2. `includes/class-churchtools-suite-blocks.php`
**Änderung:** Block-Normalisierung für alle 5 Optionen

```php
// NEU (v0.10.3.27):
if ( ! isset( $parsed_block['attrs']['show_time'] ) ) {
    $parsed_block['attrs']['show_time'] = true;
}
if ( ! isset( $parsed_block['attrs']['show_calendar_name'] ) ) {
    $parsed_block['attrs']['show_calendar_name'] = false;
}
```

---

## 📊 Warum waren die Defaults falsch?

**v0.10.3.26 Fehler:**
- Ich habe in v0.10.3.26 die Tooltip-Logik implementiert
- Aber die **Shortcode-Defaults** waren noch von der alten Implementierung
- Alte Implementierung: Alles `true` (alle Infos immer anzeigen)
- Neue Implementierung: Nur Zeit `true`, Rest `false` (Tooltip minimal)

**v0.10.3.27 Fix:**
- Shortcode-Defaults korrigiert: Nur `show_time` = `true`
- Block-Normalisierung ergänzt: Alte Blocks bekommen korrekte Defaults
- Jetzt sind alle 5 Optionen konsistent:
  - Gutenberg-Block Attribute ✅
  - Shortcode-Handler Defaults ✅
  - Block-Normalisierung ✅
  - AJAX-Handler ✅
  - Template ✅

---

## 🎉 Jetzt funktioniert ALLES!

**v0.10.3.27 Checklist:**
- ✅ Alle 5 Optionen im Shortcode-Handler
- ✅ Korrekte Defaults (nur Zeit AN)
- ✅ Block-Normalisierung für alte Blocks
- ✅ Checkboxen ändern tatsächlich das Verhalten
- ✅ Tooltip zeigt nur aktivierte Infos
- ✅ AJAX-Navigation behält Optionen bei
- ✅ Alte Blocks funktionieren automatisch

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.27
