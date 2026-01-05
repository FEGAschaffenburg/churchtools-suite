# Release Notes v0.10.4.15

**Datum:** 22. Januar 2025  
**Typ:** UI Cleanup  
**Scope:** Gutenberg Block Editor

---

## 🎯 Ziel

Entfernung des Preset-Systems aus dem Gutenberg Block Editor, da der Shortcode Manager in v0.10.4.0 deaktiviert wurde.

---

## ✅ Änderungen

### Gutenberg Block Editor (assets/js/churchtools-suite-blocks.js)

**Entfernte Features:**
- ❌ `usePresets()` Hook komplett entfernt
- ❌ `isPresetView` und `isStandardView` Logik entfernt
- ❌ Preset-Modus UI-Hinweise entfernt (blaue Info-Box)
- ❌ Alle `!isPresetView &&` Bedingungen aus Panels entfernt
- ❌ Preset-Merging in `getViewOptions()` entfernt

**Vereinfachte Funktionen:**
```javascript
// VORHER:
function getViewOptions(viewType, presets) {
    const standard = standardViewOptions[viewType] || [];
    const userPresets = presets.filter(p => p.shortcode_tag === viewType)
        .map(p => ({ value: 'preset_' + p.id, label: p.name }));
    return [...standard, ...(userPresets.length ? [{...}, ...userPresets] : [])];
}

// NACHHER:
function getViewOptions(viewType, presets) {
    const standard = standardViewOptions[viewType] || [];
    return standard; // Nur Standard-Views
}
```

**UI-Vereinfachungen:**
- Alle Panels (Ansicht, Basis, Anzeige, Filter) jetzt immer sichtbar
- Keine Preset-Hinweise mehr ("⚙️ Preset-Modus" Info-Box entfernt)
- Kalender-Auswahl immer verfügbar
- Preview-Bereich immer sichtbar

---

## 🔍 Kontext

**Warum diese Änderung?**
- Shortcode Manager wurde in v0.10.4.0 deaktiviert
- Preset-System ist ohne Shortcode Manager nicht nutzbar
- UI zeigte leere "Meine Presets" Dropdown-Option
- Dead Code und verwirrende UI-Elemente entfernt

**Rückwärtskompatibilität:**
- ✅ Bestehende Blocks funktionieren weiterhin
- ✅ Standard-Views (list, grid, calendar, etc.) unverändert
- ✅ Alle Shortcode-Parameter weiterhin verfügbar

---

## 📋 Testing

### Erforderliche Tests:
1. Gutenberg Block Editor öffnen
2. ChurchTools Suite Block hinzufügen
3. Prüfen:
   - ✅ Kein JavaScript-Fehler in Console
   - ✅ View-Dropdown zeigt nur Standard-Views
   - ✅ Keine "--- Meine Presets ---" Separator
   - ✅ Alle Panels sichtbar (Ansicht, Basis, Anzeige, Filter)
   - ✅ Kalender-Auswahl funktioniert
   - ✅ Preview zeigt Events korrekt

### Browser Cache:
- ⚠️ Nutzer müssen ggf. Cache leeren (Ctrl+F5)
- Console sollte zeigen: `✅ ChurchTools Suite Blocks JS geladen! Version 0.10.4.13`

---

## 🔧 Technische Details

**Geänderte Dateien:**
- `churchtools-suite.php` - Version 0.10.4.14 → 0.10.4.15
- `assets/js/churchtools-suite-blocks.js` - Preset-System entfernt

**Entfernte Code-Zeilen:** ~50-60 Zeilen
- `usePresets()` Hook: ~22 Zeilen
- Preset-Logik in `getViewOptions()`: ~8 Zeilen
- `isPresetView` Checks: ~15-20 Zeilen
- Preset UI-Hinweise: ~10 Zeilen

**JavaScript Cleanup-Methode:**
```powershell
# Alle Zeilen mit isPresetView entfernen
(Get-Content "assets\js\churchtools-suite-blocks.js") | 
    Where-Object { $_ -notmatch 'isPresetView' } | 
    Set-Content "assets\js\churchtools-suite-blocks.js"
```

---

## 📦 Deployment

```powershell
git add .
git commit -m "v0.10.4.15 - Preset-System aus Gutenberg Block entfernt"
git tag v0.10.4.15
git push && git push --tags
.\scripts\create-wp-zip.ps1 -Version "0.10.4.15"
gh release create v0.10.4.15 --title "v0.10.4.15 - UI Cleanup" `
    --notes-file RELEASE-NOTES-v0.10.4.15.md `
    C:\privat\churchtools-suite-0.10.4.15.zip
```

---

## 🐛 Bekannte Probleme

Keine bekannten Probleme.

---

**Vorherige Version:** [v0.10.4.14](RELEASE-NOTES-v0.10.4.14.md)  
**Nächste geplante Version:** v0.10.5.0 (weitere Features)
