# Release Notes: v0.10.3.12

**Release-Datum:** 22. Januar 2025  
**Art:** Kritischer Bugfix (Gutenberg Block)  
**Status:** Production Ready ✅

---

## 🐛 Kritische Bugfixes

### Gutenberg Block Fehler behoben
**Problem:** Block konnte nicht geladen werden - "Ungültige(r) Parameter: attributes"

**Root Cause:**
- JavaScript definierte `enable_modal` Attribut (Zeile 171)
- PHP Block-Registrierung hatte es NICHT
- **Attribute Mismatch** zwischen JS und PHP führte zu Editor-Fehler

**Fix:**
```php
// includes/class-churchtools-suite-blocks.php (Zeile 213)
'enable_modal' => [ 'type' => 'boolean', 'default' => true ],
```

**Auswirkung:**
- ✅ Gutenberg Editor lädt Block wieder korrekt
- ✅ enable_modal Parameter funktioniert jetzt in Gutenberg UND Shortcodes
- ✅ Keine "Ungültige(r) Parameter" Fehler mehr

---

## 📋 Änderungen im Detail

### Dateien geändert
- `includes/class-churchtools-suite-blocks.php`
  - Zeile 213: `enable_modal` Attribut hinzugefügt
  - Synchronisiert mit JavaScript-Definition

---

## ✅ Testing Durchgeführt

- ✅ Gutenberg Editor lädt Block ohne Fehler
- ✅ enable_modal Toggle funktioniert
- ✅ Block Preview zeigt Events korrekt
- ✅ Keine JavaScript Console-Errors

---

## 🔍 Technische Details

**Fehlerursache:**
WordPress Gutenberg validiert Block-Attribute strikt. Wenn JavaScript ein Attribut definiert das in PHP fehlt (oder umgekehrt), bricht die Block-Registrierung ab.

**Lösung:**
Attribute-Definitionen müssen **exakt** übereinstimmen:
- **JavaScript:** `enable_modal: { type: 'boolean', default: true }`
- **PHP:** `'enable_modal' => [ 'type' => 'boolean', 'default' => true ]`

---

## 📦 Deployment

**Keine Breaking Changes** - Safe Update

**Installation:**
1. Plugin aktualisieren (Auto-Update oder manuell)
2. Gutenberg Editor öffnen
3. ChurchTools Events Block einfügen
4. ✅ Block lädt sofort

---

## 🎯 Nächste Schritte

- [ ] User-Feedback zu Kalender-Monatswechsel (Debug-Logs in v0.10.3.11)
- [ ] Console-Logging Cleanup für v0.10.4.0
- [ ] Performance-Testing mit großen Datenmengen

---

**Wichtig:** Nach Update ggf. Browser-Cache leeren für JavaScript-Aktualisierung!
