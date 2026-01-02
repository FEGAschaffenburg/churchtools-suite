# Release Notes - v0.10.3.28

**Release-Datum:** 2. Januar 2026  
**Typ:** BUGFIX - JavaScript Defaults Fix

---

## 🐛 NOCH EIN BUGFIX: JavaScript Defaults korrigiert!

**v0.10.3.27 hatte IMMER NOCH ein Problem:** Die Defaults in JavaScript stimmten NICHT mit PHP überein! 😱

---

## 🔧 Was war DIESES Problem?

### JavaScript vs. PHP Defaults

**JavaScript (v0.10.3.27 - FALSCH):**
```javascript
show_description: { type: 'boolean', default: true },   // ❌ Falsch!
show_location: { type: 'boolean', default: true },      // ❌ Falsch!
show_services: { type: 'boolean', default: true },      // ❌ Falsch!
show_calendar_name: { type: 'boolean', default: false }, // ✅ Korrekt
show_time: { type: 'boolean', default: true },          // ✅ Korrekt
```

**PHP (v0.10.3.27 - KORREKT):**
```php
'show_time' => true,           // ✅
'show_description' => false,   // ✅
'show_location' => false,      // ✅
'show_services' => false,      // ✅
'show_calendar_name' => false, // ✅
```

**Problem:**
- Wenn ein **neuer Block** erstellt wurde, nutzte er die **JavaScript-Defaults** (alle TRUE)
- Dadurch waren 3 Checkboxen **fälschlicherweise aktiviert**
- PHP erwartete aber alle FALSE (außer show_time)
- **Mismatch zwischen Frontend (JS) und Backend (PHP)!**

---

## ✅ Fix (v0.10.3.28)

**JavaScript Defaults korrigiert:**
```javascript
// v0.10.3.28: Tooltip options - defaults MUST match PHP (only show_time=true)
show_description: { type: 'boolean', default: false },  // ✅ Jetzt FALSE
show_location: { type: 'boolean', default: false },     // ✅ Jetzt FALSE
show_services: { type: 'boolean', default: false },     // ✅ Jetzt FALSE
show_calendar_name: { type: 'boolean', default: false }, // ✅ Bleibt FALSE
show_time: { type: 'boolean', default: true },          // ✅ Bleibt TRUE
```

**Jetzt konsistent:**
- JavaScript Default = PHP Default = `false` (außer show_time)
- Neue Blocks haben korrekte Initial-Werte
- Checkboxen zeigen korrekten Zustand
- Tooltip zeigt nur aktivierte Optionen

---

## 🧪 Testing

### Test 1: NEUEN Block einfügen
1. **Block-Editor öffnen**
2. **"ChurchTools Events" Block hinzufügen**
3. **Ansicht:** Kalender → monthly-modern wählen
4. **Anzeige-Optionen prüfen:**
   - ☑️ **Uhrzeit anzeigen** sollte AN sein
   - ☐ **Beschreibung anzeigen** sollte AUS sein
   - ☐ **Ort anzeigen** sollte AUS sein
   - ☐ **Services anzeigen** sollte AUS sein
   - ☐ **Kalender-Name anzeigen** sollte AUS sein
5. **Speichern** + Seite ansehen
6. **Tooltip prüfen:** Sollte NUR Datum + Titel + Uhrzeit zeigen

### Test 2: Checkboxen umschalten
1. Block öffnen
2. **ALLE Checkboxen AKTIVIEREN**
3. Speichern + Seite ansehen
4. **Tooltip prüfen:** Sollte ALLES zeigen (Zeit, Beschreibung, Ort, Services, Kalender)

### Test 3: Alter Block
1. Seite mit altem Block öffnen (vor v0.10.3.28)
2. **Block NICHT neu erstellen**
3. Tooltip prüfen: Sollte korrekt funktionieren (normalisiert durch PHP)

---

## 🔄 Migration

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren auf v0.10.3.28
2. **Browser-Cache leeren** (STRG+F5) ← **KRITISCH!**
3. **Vorhandene Blocks:** Funktionieren automatisch (PHP-Normalisierung)
4. **Neue Blocks:** Haben jetzt korrekte Defaults

---

## 🎯 Geänderte Datei

### `assets/js/churchtools-suite-blocks.js`

**Zeilen 169-173 (Attribute Defaults):**
```javascript
// VORHER (v0.10.3.27):
show_description: { type: 'boolean', default: true },  // ❌
show_location: { type: 'boolean', default: true },     // ❌
show_services: { type: 'boolean', default: true },     // ❌

// JETZT (v0.10.3.28):
show_description: { type: 'boolean', default: false }, // ✅
show_location: { type: 'boolean', default: false },    // ✅
show_services: { type: 'boolean', default: false },    // ✅
```

---

## 📊 Warum waren die JS-Defaults falsch?

**Historischer Kontext:**
- **v0.5.x:** Alle Optionen waren `true` (alles anzeigen)
- **v0.10.3.26:** PHP-Defaults auf `false` geändert (nur Tooltip-Infos)
- **v0.10.3.27:** PHP-Shortcode gefixt
- **v0.10.3.28:** **JavaScript-Defaults gefixt** ← Hier!

**Problem:**
- JavaScript-Datei wurde bei v0.10.3.26/27 NICHT aktualisiert
- Neue Blocks nutzten alte Defaults (alle `true`)
- Mismatch zwischen Frontend (JS) und Backend (PHP)

---

## 🎉 Jetzt WIRKLICH konsistent!

**v0.10.3.28 Konsistenz-Check:**
- ✅ JavaScript Default: nur `show_time` = `true`
- ✅ PHP Default: nur `show_time` = `true`
- ✅ Block Attribute: alle 5 Optionen
- ✅ Block Normalisierung: alte Blocks funktionieren
- ✅ Shortcode Handler: alle 5 Optionen
- ✅ AJAX Handler: empfängt alle 5 Optionen
- ✅ Template: nutzt alle 5 Optionen
- ✅ **Checkboxen funktionieren jetzt WIRKLICH!**

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.28
