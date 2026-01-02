# Release Notes - Version 0.10.2.5

**Release Date:** 2. Januar 2026  
**Type:** Aufräumen & Code-Cleanup  
**Priority:** Niedrig (keine funktionalen Änderungen)

---

## 🧹 Code-Cleanup: Demo-Features entfernt

### Änderungen

**Ausgelagerte Features komplett entfernt:**

Die Demo- und Dokumentations-Features wurden bereits in v0.10.0.0 in das separate Projekt `churchtools-suite-plugin_homepage` ausgelagert. In diesem Release werden nun alle verwaisten Referenzen und Dateien aus dem Production-Plugin entfernt.

### Entfernte Komponenten

**1. Dokumentations-Tab (`churchtools-suite-docs`):**
- ❌ Submenu-Eintrag "📚 Dokumentation" entfernt
- ❌ `display_documentation_page()` Methode entfernt
- ❌ `admin/views/tab-documentation.php` gelöscht

**2. Demo & Live-Views Tab:**
- ❌ "🎯 Demo & Live-Views" Tab aus Shortcode Manager entfernt
- ❌ Kompletter Demo-Tab Content entfernt (Demo-Type-Grid, Stats, Checklisten)
- ❌ `display_shortcode_demo()` Methode entfernt
- ❌ `admin/views/shortcode-demo.php` gelöscht
- ❌ `admin/views/shortcode-demo-tabs.php` gelöscht

### Warum diese Änderungen?

**Architektur-Trennung (v0.10.0.0):**
- Production Plugin: Reine ChurchTools-Integration (API, Sync, Templates)
- Demo Plugin: Demobenutzer, Registrierung, erweiterte Features
- Homepage Plugin: Dokumentation, Live-Demos, Marketing-Content

**Problem vor v0.10.2.5:**
- Tab "Dokumentation" führte zu 404-Seite (View-Datei fehlte)
- "Demo & Live-Views" zeigte veraltete Inhalte
- Methoden `display_documentation_page()` und `display_shortcode_demo()` ohne Funktion
- Verwirrung für Nutzer: "Wo ist die Dokumentation?"

**Lösung:**
- Alle Demo/Doku-Referenzen komplett entfernt
- Dokumentation ist nun auf der separaten Homepage verfügbar
- Klare Trennung: Production = API Integration, Homepage = Demos & Docs

---

## 📝 Technische Details

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php:**
- Entfernt: `add_submenu_page()` für `churchtools-suite-docs`
- Entfernt: `display_documentation_page()` Methode
- Entfernt: `display_shortcode_demo()` Methode

**admin/views/shortcode-manager.php:**
- Entfernt: "Demo & Live-Views" Tab-Button
- Entfernt: Kompletter `<div id="tab-demo">` Content (~250 Zeilen)

**Gelöschte Dateien:**
- `admin/views/tab-documentation.php`
- `admin/views/shortcode-demo.php`
- `admin/views/shortcode-demo-tabs.php`

### Navigation nach v0.10.2.5

**Shortcode Manager** (`?page=churchtools-suite-shortcodes`):
- ✅ Standard Shortcodes
- ✅ Eigene Presets
- ✅ Neues Preset erstellen
- ❌ ~~Demo & Live-Views~~ (entfernt)

**Hauptnavigation** (`?page=churchtools-suite`):
- ✅ Dashboard
- ✅ Einstellungen
- ✅ Daten (Submenu)
- ✅ Synchronisation
- ✅ Erweitert (Debug)
- ❌ ~~Dokumentation~~ (entfernt)

---

## 🔧 Migration

### Automatische Schritte
1. Plugin-Update installieren (v0.10.2.5)
2. **Automatisch:** Alle verwaisten Dateien werden durch Update überschrieben
3. **Kein Neustart erforderlich**

### Manuelle Validierung (Optional)
1. **Admin → ChurchTools Suite:**
   - Prüfen: Kein "Dokumentation" Submenu-Eintrag mehr sichtbar
   - Erwartung: Nur "ChurchTools Suite" und "📋 Daten" in Submenu

2. **Admin → ChurchTools Suite → Shortcodes:**
   - Prüfen: Nur 3 Tabs (Standards, Eigene Presets, Neues Preset)
   - Erwartung: "Demo & Live-Views" Tab fehlt

---

## ⚠️ Breaking Changes

**Keine Breaking Changes für Endnutzer.**

Die entfernten Features waren bereits seit v0.10.0.0 nicht-funktional (fehlende View-Dateien). Nutzer, die auf die Dokumentation geklickt haben, erhielten bereits eine Fehlerseite.

---

## 📦 Deployment

**Git:**
```bash
git add -A
git commit -m "Release v0.10.2.5 - Entfernt Demo/Doku-Features (bereits ausgelagert)"
git push
git tag v0.10.2.5
git push --tags
```

**ZIP:**
```powershell
cd scripts
.\create-wp-zip.ps1 -Version "0.10.2.5"
```

---

## 🔗 Dokumentation (Neue Locations)

**Wo finde ich jetzt die Dokumentation?**

1. **Live-Demos & Shortcode-Übersicht:**
   - 🌐 `churchtools-suite-plugin_homepage` Projekt
   - Deploy-Target: Separate Marketing-Website

2. **API-Dokumentation:**
   - 📖 GitHub README.md
   - 📖 Inline-Code-Kommentare
   - 📖 ROADMAP.md für Features

3. **Support:**
   - GitHub Issues für Bug-Reports
   - GitHub Discussions für Fragen

---

## 📚 Lessons Learned

1. **Klare Architektur:**
   - Production Plugin = Kernfunktionen
   - Separate Projekte = Marketing/Demos
   - Keine Vermischung mehr

2. **Konsequent aufräumen:**
   - Auslagern allein reicht nicht
   - Verwaiste Referenzen müssen gelöscht werden
   - UX-Verwirrung vermeiden

3. **Deployment-Strategie:**
   - Production: Git + Auto-Update
   - Demo: SSH-only (no Git)
   - Homepage: Separates Deployment

---

**Status:** ✅ Code-Cleanup abgeschlossen  
**Priorität:** 🟢 Niedrig - Aufräumarbeit ohne funktionale Auswirkungen
