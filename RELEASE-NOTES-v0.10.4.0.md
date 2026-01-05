# v0.10.4.0 - Alle Templates mit vollständigem Field-Support + Tags

## 🎉 Milestone Release - Template Migration Complete

**v0.10.4.0** - Alle Templates unterstützen jetzt ALLE Event-Felder + Tags aus ChurchTools

---

## ✨ Neue Features

### 1. **Universeller Toggle-Support in ALLEN Templates**
Alle 5 Haupt-Templates haben jetzt vollständigen Toggle-Support:

**Verfügbare Toggles:**
- ✅ `enable_modal` - Modal-Dialog für Event-Details
- ✅ `show_time` - Uhrzeit anzeigen
- ✅ `show_location` - Standort anzeigen  
- ✅ `show_description` - Beschreibung anzeigen
- ✅ `show_services` - Dienste/Mitarbeiter anzeigen
- ✅ `show_calendar_name` - Kalender-Name anzeigen

**Aktualisierte Templates:**
- 📋 **list/compact.php** - Kompakte Liste (jetzt mit allen Feldern)
- 📋 **list/fluent.php** - Microsoft Fluent Design
- 📋 **list/medium.php** - Medium-Density Liste
- 📦 **widget/upcoming.php** - Upcoming Events Widget
- 🔍 **search/classic.php** - Event-Suche

---

### 2. **🏷️ Tags-Support (ChurchTools-Integration)**

**Was ist neu:**
- Tags werden aus ChurchTools importiert (Events API + Appointments API)
- Automatische **Color-Normalisierung** (ChurchTools-Farbnamen → Hex-Codes):
  - `basic` → `#6b7280` (Grau)
  - `red` → `#ef4444` (Rot)
  - `orange` → `#f97316` (Orange)
  - `yellow` → `#eab308` (Gelb)
  - `green` → `#22c55e` (Grün)
  - `blue` → `#3b82f6` (Blau)
  - `indigo`, `purple`, `pink` etc.
- Farbcodierte Badges in allen Templates
- Kompakte Darstellung in Widgets (max. 2 Tags + Zähler)

---

## 🔧 Technische Änderungen

### Events API - Tags jetzt included
**Datei:** `includes/services/class-churchtools-suite-event-sync-service.php`

**Vorher (v0.10.3.x):**
```php
'include' => 'eventServices'
```

**Jetzt (v0.10.4.0):**
```php
'include' => 'eventServices,tags'
```

### Neue Helper-Funktion: `normalize_tag_colors()`
Konvertiert ChurchTools-Farbnamen automatisch zu Hex-Codes beim Import.

---

## 🎨 Template-Details

### list/compact.php
**Neu hinzugefügt:**
- Description (wp_trim_words für Kompaktheit)
- Services (Badge-Style)
- Calendar Name (mit Emoji-Präfix 📅)
- Tags (farbcodiert)

### list/fluent.php & list/medium.php
**Neu hinzugefügt:**
- Calendar Name
- Tags (gleicher Style wie compact)

### widget/upcoming.php
**Neu hinzugefügt:**
- Description (max. 10 Wörter)
- Services (nur erster Service + Zähler)
- Calendar Name
- Tags (max. 2 Tags + Zähler für Platzersparnis)

### search/classic.php
**Neu hinzugefügt:**
- Time (Uhrzeit)
- Services (max. 2 Services)
- Calendar Name
- Tags (alle Tags)

---

## 📦 Deployment & Migration

### **WICHTIG: Nach dem Update**

1. **Full Sync durchführen:**
   - Admin → ChurchTools Suite → Synchronisation
   - Klicke auf "Events Synchronisieren"
   - Dadurch werden Tags aus ChurchTools importiert

2. **Toggles in Gutenberg/Elementor aktivieren:**
   - Öffne deine Event-Blocks/Widgets
   - Aktiviere die gewünschten Toggles:
     - `show_services` → Mitarbeiter-Info anzeigen
     - `show_calendar_name` → Kalender-Badge
     - `show_description` → Event-Beschreibung
   - Tags werden automatisch angezeigt (wenn vorhanden)

3. **Shortcode Manager deaktiviert:**
   - Der Shortcode Manager ist nicht mehr im Admin-Menü sichtbar
   - Alle Toggle-Einstellungen erfolgen direkt über Gutenberg/Elementor
   - Grund: Vermeidung von Duplikation & bessere UX

---

## 🐛 Bugfixes

### Tags wurden nicht importiert (v0.10.3.x)
**Problem:** Events API included keine Tags  
**Fix:** `include=eventServices,tags` in Phase 1 + Phase 2

### Color-Werte nicht CSS-kompatibel
**Problem:** ChurchTools sendet Farben als Namen (`basic`, `red`)  
**Fix:** `normalize_tag_colors()` konvertiert automatisch zu Hex-Codes

---

## 📊 Statistik

**Code-Änderungen:**
- 10 Dateien geändert
- 702 Zeilen hinzugefügt
- 9 Zeilen entfernt
- 5 Templates komplett migriert
- 1 neue Helper-Funktion

**Templates mit vollständigem Support:**
- ✅ list/compact.php (100%)
- ✅ list/fluent.php (100%)
- ✅ list/medium.php (100%)
- ✅ widget/upcoming.php (100%)
- ✅ search/classic.php (100%)

---

## 🔄 Upgrade-Pfad

**Von v0.10.3.x → v0.10.4.0:**
1. Plugin-ZIP hochladen ODER Auto-Update nutzen
2. Full Sync durchführen (Admin → Synchronisation)
3. Toggles in Blocks aktivieren (Gutenberg/Elementor)
4. Fertig! 🎉

**Breaking Changes:** Keine  
**Backwards Compatible:** Ja (alte Templates funktionieren weiter)

---

## 📝 Credits

**Migration-Plan:** `MIGRATION-PLAN-v0.10.4.0.md`  
**Automation-Script:** `scripts/update-templates-v0.10.4.0.ps1` (erstellt, aber nicht verwendet)  
**Approach:** Manuelle Template-by-Template Migration für maximale Sicherheit

---

**Installation:** Plugin-ZIP hochladen oder Auto-Update nutzen.

**Support:** GitHub Issues → https://github.com/FEGAschaffenburg/churchtools-suite/issues
