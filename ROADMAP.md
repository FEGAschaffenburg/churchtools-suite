# ChurchTools Suite - Roadmap

> **Aktueller Stand:** v1.0.0.0 (12. Januar 2026) - 🎉 Production Ready  
> **Nächstes Milestone:** v1.1.0 - Performance & Batch Processing

---

## 🎯 Vision

ChurchTools Suite ist eine umfassende WordPress-Integration für ChurchTools, die es Gemeinden ermöglicht, ihre Termine, Kalender und Services nahtlos auf ihrer Website zu präsentieren.

---

 

## 🚀 In Arbeit

Keine aktuellen Entwicklungen. Basis-Funktionalität ist stabil.

---

## 📋 Geplant für Zukunft

### v1.1.0: Performance & Batch Processing
**Ziel:** Große Event-Mengen effizient verarbeiten

**Features:**
- [ ] Batch Event Processing (Chunk-Size konfigurierbar)
- [ ] Progress Tracking mit AJAX Polling
- [ ] Background Processing mit WP-Cron
- [ ] Abort Button für laufende Syncs
- [ ] Batch Database Inserts
- [ ] API Response Caching (Transients)
- [ ] Query Optimization

**Priorität:** Mittel  
**Geschätzter Aufwand:** 4-5 Tage

### v1.2.0: Multi-Language Support
**Ziel:** Mehrsprachige Event-Daten

**Features:**
- [ ] Übersetzungs-Dateien (.pot, .po, .mo)
- [ ] WPML/Polylang Kompatibilität
- [ ] ChurchTools Multi-Language Events synchronisieren

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 3-4 Tage

### v1.3.0: Extended Frontend Widgets
**Ziel:** Zusätzliche Template-Varianten

**Features:**
- [ ] Weitere Slider-Varianten (Auto-play, Pagination)
- [ ] Countdown-Templates (Event-Countdown)
- [ ] Cover-Templates (Hero-Section mit Event)
- [ ] Carousel-Templates (Responsive)

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 4-5 Tage

### v1.4.0: Template Manager & Structure Refactoring
**Ziel:** Professionelles Template-System mit zentraler Verwaltung

**See:** [TEMPLATE-STRUCTURE-PROPOSAL.md](docs/TEMPLATE-STRUCTURE-PROPOSAL.md)

**Phase 1: Struktur-Refactoring (v1.4.0)**
- [ ] Neue hierarchische Ordnerstruktur:
  - `event/` - Event-Templates (list, grid, single, modal, calendar)
  - `calendar/` - Kalender-Komponenten (card, widget, badge)
  - `tag/` - Tag-Komponenten (badge, card, cloud)
  - `service/` - Service-Komponenten (list, card, person)
  - `partial/` - Wiederverwendbare Teile (date-badge, time-range, location-card)
  - `system/` - System-Templates (nicht editierbar)
  - `custom/` - User-Templates (editierbar/uploadbar)
- [ ] Migration bestehender Templates
- [ ] Migration-Script für v1.0 → v1.4.0
- [ ] Kompatibilitäts-Layer für alte Pfade
- [ ] Template-Header mit Metadaten (Name, Type, Version, Author)

**Phase 2: Template-Manager Backend (v1.4.1)**
- [ ] Neue DB-Tabelle: `wp_cts_templates`
- [ ] Template-Registration-API
- [ ] Template-Scanner (automatisches Erkennen)
- [ ] Template-Validator
- [ ] Template-Renderer mit Caching

**Phase 3: Template-Manager UI (v1.4.2)**
- [ ] Admin-Seite: `ChurchTools Suite > Templates`
- [ ] Template-Bibliothek (Liste mit Gruppierung)
- [ ] Aktivieren/Deaktivieren-Toggle pro Template
- [ ] Template-Einstellungen-Seite (pro Template)
- [ ] Filter nach Typ & Status
- [ ] Suche nach Name/Tags

**Phase 4: Template-Upload & Validation (v1.4.3)**
- [ ] ZIP-Upload-Funktion
- [ ] Template-Validator für Uploads
- [ ] PHP-Code-Scanner (Sicherheit)
- [ ] Custom-Templates-Verwaltung
- [ ] Template-JSON-Format (template.json)

**Phase 5: Template-Previews (v1.4.4)**
- [ ] Screenshot-Generator
- [ ] Live-Preview mit Test-Daten
- [ ] Responsive-Vorschau (Desktop/Tablet/Mobile)
- [ ] Template-Galerie

**Phase 6: Komponenten-Templates (v1.5.0)**
- [ ] Calendar-Komponenten (card, widget, badge, list-item)
- [ ] Tag-Komponenten (badge, card, cloud)
- [ ] Service-Komponenten (list, card, badge, person)
- [ ] Partial-Templates (date-badge, time-range, location-card)
- [ ] Shortcodes für Komponenten

**Priorität:** Mittel  
**Geschätzter Aufwand:** 15-20 Tage (alle Phasen)  
**Hinweis:** Revolutioniert das Template-System - ermöglicht modulare Komponenten

### v1.5.0: Advanced Integration
**Ziel:** Externe System-Integration

**Features:**
- [ ] REST API Endpoints (öffentlich dokumentiert)
- [ ] Webhook Support (Event-Trigger)
- [ ] iCal Export (Standards-konform)
- [ ] Google Calendar Integration (bidirektional)

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 5-7 Tage

### v1.6.0: Extended Admin Tools
**Ziel:** Erweiterte Admin-Funktionen

**Features:**
- [ ] Bulk Operations (Massenbearbeitung)
- [ ] Advanced Filtering & Search
- [ ] Export/Import (CSV, JSON)
- [ ] Statistics & Analytics Dashboard
- [ ] Event-Duplikat-Detection

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 5-6 Tage

---

## 🔮 Vision Features (v2.0+)

### v2.0: Template Marketplace
- [ ] Templates kaufen/verkaufen
- [ ] Rating & Reviews System
- [ ] Automatic Updates für gekaufte Templates
- [ ] Template-Bundles

### v2.1: Visual Template Editor
- [ ] Drag & Drop Editor
- [ ] Live-Preview beim Editieren
- [ ] Component-Library
- [ ] CSS-Visual-Editor (No-Code)

### v2.2: Template Versioning & Git Integration
- [ ] Git-ähnliche Versionskontrolle
- [ ] Rollback-Funktion
- [ ] Change History
- [ ] Template-Diffs anzeigen

### v2.3: AI-Powered Features
- [ ] AI-Template-Generator (aus Beschreibung)
- [ ] Smart Layout-Suggestions
- [ ] Auto-Optimization für Performance
- [ ] Content-aware Styling

### v2.4: Advanced Customization
- [ ] Element-spezifische Farben
- [ ] Schriftarten-Auswahl pro Element
- [ ] Style-Presets (speichern/laden)
- [ ] CSS-Export für Custom-Themes
- [ ] Hover-States & Animations

---

## 🐛 Bekannte Probleme

### Kritisch
- ✅ Keine (v1.0.0.0 stabil)

### Mittel
- Keine

### Niedrig
- Keine

---

## 📝 Notizen

### Technische Schulden
- [ ] Unit Tests (PHPUnit) hinzufügen
- [ ] Code Coverage erhöhen (Ziel: >80%)
- [ ] Inline-Dokumentation vervollständigen
- [ ] Performance-Profiling durchführen

### Verbesserungsideen
- [ ] Dashboard Widgets für Quick Stats
- [ ] Quick-Edit in Event-Liste
- [ ] Template Preview im Admin
- [ ] Visual Shortcode Builder
- [ ] WP-CLI Commands erweitern

---

## 🎓 Ressourcen

**Dokumentation:**
- [ChurchTools API Docs](https://api.church.tools/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Gutenberg Block Editor](https://developer.wordpress.org/block-editor/)

**Tools:**
- [WP-CLI](https://wp-cli.org/)
- [Query Monitor](https://querymonitor.com/)
- [Debug Bar](https://wordpress.org/plugins/debug-bar/)

---

## 📊 Version-Historie

| Version | Datum | Status | Focus |
|---------|-------|--------|-------|
| v1.0.0.0 | 12. Jan 2026 | ✅ Released | Production Ready |
| v0.9.0+ | 2025 | ✅ Archived | Clean Slate |
| v0.8.x | 2024 | ✅ Archived | Template Rebuild |

---

**Letzte Aktualisierung:** 12. Januar 2026 (v1.0.0.0 - Production Ready)  
**Repository:** [GitHub - FEGAschaffenburg/churchtools-suite](https://github.com/FEGAschaffenburg/churchtools-suite)
