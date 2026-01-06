# v0.10.4.45 - Migration System für Block Attributes

**Problem:**
- Alte Blocks verwendeten noch `show_description` Attribut (Legacy)
- Runtime-Konvertierung war nicht sauber
- Bessere Lösung: Einmalige Migration beim Update

**Fix - Migration 2.3:**
- Automatische Konvertierung: `show_description` → `show_event_description` + `show_appointment_description`
- Findet alle Pages mit ChurchTools Blocks (published, draft, pending, private)
- Konvertiert Attribute direkt in `post_content`
- Unterstützt verschachtelte Blocks (Group, Column, etc.) - Rekursive Konvertierung
- Läuft einmal automatisch bei Plugin-Aktivierung
- Database Version: 2.2 → 2.3

**Technische Implementierung:**
- `class-churchtools-suite-migrations.php::migrate_to_2_3()`
- SQL-Query findet alle betroffenen Pages
- `parse_blocks()` + `serialize_blocks()` für saubere Konvertierung
- `migrate_blocks_recursive()` für verschachtelte Block-Strukturen
- Logging für Migration-Statistiken

**Auswirkung:**
- Keine Legacy-Code-Reste mehr im Runtime-Code
- Alle bestehenden Blocks automatisch auf neue Attribute umgestellt
- Saubere Code-Basis für zukünftige Entwicklung

---

# v0.10.4.44 - Beschreibungs-Toggle Fix (KRITISCH)

**Problem:**
- Beschreibungs-Toggles (Event-Beschreibung, Termin-Beschreibung) funktionierten NICHT im Frontend
- Andere Toggles (Ort, Services, Zeit, Tags) funktionierten korrekt
- Root Cause: Default-Werte wurden NACH String-Konvertierung gesetzt
- Gutenberg speichert Standard-Werte nicht → Attribute waren undefined
- Templates bekamen keine Beschreibungs-Attribute

**Fix:**
- Defaults werden jetzt in 2 Schritten gesetzt:
  1. STEP 1: Defaults für undefined Attribute setzen
  2. STEP 2: Boolean → String Konvertierung
- Reihenfolge: Defaults BEFORE Conversion (war umgekehrt)

**Technische Änderung:**
- `class-churchtools-suite-blocks.php::render_block()` refactored
- Foreach-Schleife in 2 separate Schleifen aufgeteilt
- Garantiert, dass Defaults VOR Konvertierung gesetzt werden

**Testing:**
- Alle Toggles jetzt funktional (inkl. Beschreibungen)
- Frontend zeigt korrekte Werte basierend auf Toggle-Einstellungen

---

# v0.10.4.43 - Toggle Visibility Fix (Gutenberg)

**Problem:**
- Toggle-Steuerelemente (Event-Beschreibung, Ort, Services, etc.) waren in Gutenberg nur für List/Grid/Search/Widget Views sichtbar
- Calendar-Views hatten KEINE Toggles im Gutenberg-Editor
- Elementor hatte bereits alle Toggles für ALLE Views (korrekt)
- Inkonsistentes Verhalten zwischen Gutenberg und Elementor

**Fix:**
- Bedingung `attributes.viewType !== 'calendar'` entfernt in `churchtools-suite-blocks.js`
- Panel "👁️ Anzeige-Optionen" jetzt in Gutenberg für ALLE Views sichtbar (inkl. Calendar)
- Konsistentes Verhalten zwischen Gutenberg und Elementor

**Auswirkung:**
- ✅ Keine Breaking Changes - bestehende Seiten funktionieren weiter
- ✅ Default-Werte bleiben gleich
- ✅ Neue Funktionalität: Calendar-Views können jetzt auch Toggles nutzen

**Testing:**
- Siehe `TESTING-CHECKLIST-v0.10.4.43.md` für vollständige Test-Matrix
- Scan-Skript: `scripts/scan-existing-pages.php` zum Finden aller betroffenen Seiten

---

# v0.10.4.11 - Tag Filtering & Display

**Features:**
- Tag-Filterung mit AND-Logik (Event muss ALLE Tags haben)
- Tag-Anzeige als farbige Badges
- Parameter: filter_tags und show_tags

**Verwendung:**
```
[cts_events_list filter_tags="Gottesdienst,Alpha" show_tags="true"]
```

**AND-Logik:**
- filter_tags="Gottesdienst,Alpha" → Nur Events mit **BEIDEN** Tags
- Event mit nur "Gottesdienst" → nicht angezeigt
- Event mit "Gottesdienst" + "Alpha" → angezeigt

**Technische Änderungen:**
- Shortcodes: filter_tags + show_tags Parameter
- Template Data: filter_events_by_tags() mit AND-Logik  
- Templates: Tag-Badges Rendering
- CSS: .cts-tag-badge Styles

**Vorgängerversionen:**
- v0.10.4.9: Tags-Import (Root-Cause Fix)
- v0.10.4.10: Description-Felder getrennt

**Installation:**
1. Plugin-ZIP herunterladen
2. In WordPress hochladen
3. Tags werden automatisch synchronisiert
