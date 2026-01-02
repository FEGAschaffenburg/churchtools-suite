# ChurchTools Suite - Release Notes v0.10.1.6

**Datum:** 2. Januar 2026  
**Typ:** UI-Bugfix (Subtabs-Vereinheitlichung)

---

## 🐛 Bugfix

### Subtabs jetzt wirklich vereinheitlicht
**Problem:** Trotz CSS-Update in v0.10.1.4 sahen die Subtabs im "Erweitert"-Tab noch anders aus als im "Einstellungen"-Tab.

**Ursache:**
1. **Unterschiedliche HTML-Struktur:**
   - Settings-Tab: Hatte `<div class="cts-settings">` Wrapper
   - Debug-Tab: Hatte KEINEN Wrapper
   - Resultat: Unterschiedliches Styling trotz identischem CSS

2. **Unterschiedliche CSS-Klassen:**
   - Settings-Tab: `.cts-sub-tabs` und `.cts-sub-tab`
   - Debug-Tab: `.cts-subtab-nav` und `.cts-subtab`
   - Obwohl CSS identisch war, waren es zwei separate Definitionen

**Lösung:**
1. **Wrapper hinzugefügt:**
   - Debug-Tab bekommt jetzt auch `<div class="cts-settings">` Wrapper
   - Konsistente HTML-Struktur in beiden Tabs

2. **CSS-Klassen vereinheitlicht:**
   - Debug-Tab nutzt jetzt auch `.cts-sub-tabs` und `.cts-sub-tab`
   - Alte Klassen `.cts-subtab-nav` und `.cts-subtab` entfernt
   - Nur noch EINE CSS-Definition für Subtabs

3. **Shared Partial aktualisiert:**
   - `render-subtabs.php` nutzt jetzt die gleichen Klassen wie Settings
   - Einheitliche Implementierung

**Betroffene Dateien:**
- `admin/views/admin-page.php` - Wrapper um Debug-Tab hinzugefügt
- `admin/views/partials/render-subtabs.php` - CSS-Klassen geändert
- `admin/css/churchtools-suite-admin.css` - Doppelte CSS-Definitionen entfernt

---

## 📋 Zusammenfassung

**1 UI-Bug behoben:**
- ✅ Subtabs in "Einstellungen" und "Erweitert" sehen jetzt identisch aus
- ✅ Gleiche HTML-Struktur (mit Wrapper)
- ✅ Gleiche CSS-Klassen
- ✅ Konsistente Darstellung

**Code-Cleanup:**
- ❌ Entfernt: `.cts-subtab-nav` und `.cts-subtab` CSS (nicht mehr verwendet)
- ✅ Vereinfacht: Nur noch eine Subtab-Definition

---

## 🎯 Visuelle Änderungen

**Vorher (v0.10.1.5):**
- Settings-Subtabs: Schöne Tab-Navigation mit Hover-Effekt
- Debug-Subtabs: Leicht unterschiedliches Styling, kein Wrapper

**Nachher (v0.10.1.6):**
- **Beide identisch:** Gleiche Tab-Navigation, gleiche Hover-Effekte, gleicher Wrapper
- Professionelles, konsistentes Erscheinungsbild

---

## ⚠️ Breaking Changes

**Keine Breaking Changes.**

Falls eigene Custom-CSS für `.cts-subtab-nav` oder `.cts-subtab` existiert, muss dies auf `.cts-sub-tabs` und `.cts-sub-tab` angepasst werden.

---

## 📦 Upgrade-Hinweise

**Von v0.10.1.5 → v0.10.1.6:**
- Keine Migrationen erforderlich
- Keine Datenbank-Änderungen
- Einfacher Plugin-Update über WordPress
- **Browser-Cache leeren:** Strg+F5 nach Update für neues CSS

---

**Empfehlung:** Kleines UI-Update für konsistentere Darstellung. Update empfohlen für alle Nutzer, die den erweiterten Modus verwenden.
