# v0.10.4.13 - List Views Cleanup

**Release Date:** 5. Januar 2026  
**Type:** Cleanup  
**Branch:** production → deployment

---

## 🎯 Summary

Standard-List-Views auf **Classic** und **Medium** reduziert. Compact, Fluent und Modern wurden entfernt, da sie noch nicht vollständig implementiert sind.

**Impact:** Klarere UI, keine Verwirrung durch unfertige Templates

---

## 🔧 Changes

### **Gutenberg Block**
**Verfügbare List-Views:**
- ✅ Classic
- ✅ Medium
- ❌ ~~Compact~~ (entfernt)
- ❌ ~~Fluent~~ (entfernt)
- ❌ ~~Modern~~ (entfernt)

### **Elementor Widget**
**Verfügbare List-Views:**
- ✅ Classic
- ✅ Medium
- ❌ ~~Compact~~ (entfernt)
- ❌ ~~Fluent~~ (entfernt)
- ❌ ~~Modern~~ (entfernt)

### **Shortcodes**
Shortcodes akzeptieren weiterhin alle View-Namen (für zukünftige Erweiterungen), aber UI zeigt nur fertige Templates.

---

## 📋 Affected Files

**1. Gutenberg Block (`assets/js/churchtools-suite-blocks.js`):**
```javascript
const standardViewOptions = {
    list: [
        { label: '--- Standard Views ---', value: '', disabled: true },
        { label: 'Classic', value: 'classic' },
        { label: 'Medium', value: 'medium' }
    ],
```

**2. Elementor Widget (`includes/class-churchtools-suite-elementor-widget.php`):**
```php
// List Views (nur Standard - v0.10.4.13: Nur Classic und Medium verfügbar)
$list_options = [
    'classic' => __( 'Classic', 'churchtools-suite' ),
    'medium'  => __( 'Medium', 'churchtools-suite' ),
];
```

---

## 🎨 UI Before/After

### **Before:**
```
List-Variante:
  - Classic
  - Medium
  - Compact     ← nicht fertig
  - Fluent      ← nicht fertig
  - Modern      ← nicht fertig
```

### **After:**
```
List-Variante:
  - Classic     ✅ vollständig
  - Medium      ✅ vollständig
```

---

## 🚀 Future Enhancements

Wenn Compact, Fluent oder Modern vollständig implementiert sind:
1. Template fertigstellen (`templates/list/compact.php`, etc.)
2. View zurück in UI-Listen einfügen
3. Testen und dokumentieren

---

## ✅ Benefits

- **Klarere UI:** Nur funktionierende Views werden angezeigt
- **Keine Verwirrung:** User wählen keine unfertigen Templates
- **Wartbarkeit:** Klare Trennung zwischen fertigen und unfertigen Features

---

## 🧪 Testing Checklist

**Developer Testing:**
- [x] Gutenberg Block zeigt nur Classic und Medium
- [x] Elementor Widget zeigt nur Classic und Medium
- [x] Bestehende Shortcodes mit view="compact" funktionieren weiterhin (Template existiert)

**User Testing:**
- [ ] Gutenberg Block öffnen → Nur Classic/Medium sichtbar
- [ ] Elementor Widget öffnen → Nur Classic/Medium sichtbar
- [ ] Bestehende Seiten mit alten Views (falls vorhanden) funktionieren weiterhin

---

## 📚 Related

**Templates verfügbar (aber nicht in UI):**
- `templates/list/compact.php` ✓ existiert
- `templates/list/fluent.php` ✓ existiert
- `templates/list/modern.php` ✓ existiert

Diese können weiterhin per Shortcode verwendet werden:
```
[cts_list view="compact"]
```

**UI zeigt sie nur nicht als Option an.**

---

## 🚀 Deployment

**Files Changed:**
- `assets/js/churchtools-suite-blocks.js` (6 Zeilen entfernt)
- `includes/class-churchtools-suite-elementor-widget.php` (3 Zeilen entfernt)
- `churchtools-suite.php` (Version bump)

**Database Changes:** None

**Migration Required:** No

**Backwards Compatible:** Yes (alte Shortcodes funktionieren weiterhin)

**Breaking Changes:** None (nur UI-Änderung)

---

**Previous Version:** v0.10.4.12  
**Next Version:** TBD
