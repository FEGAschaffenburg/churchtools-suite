# ChurchTools Suite - Roadmap

> **Aktueller Stand:** v0.10.0.0 (Januar 2026)  
> **Nächstes Milestone:** v1.0.0 - Production Ready

---

## 🎯 Vision

ChurchTools Suite ist eine umfassende WordPress-Integration für ChurchTools, die es Gemeinden ermöglicht, ihre Termine, Kalender und Services nahtlos auf ihrer Website zu präsentieren.

---

## ✅ Abgeschlossen

### v0.1.0 - v0.9.5.2: Core Features & Templates
- ✅ Cookie-basierte ChurchTools API-Authentifizierung
- ✅ Repository-Pattern für Datenzugriff
- ✅ 2-Phasen Event-Sync (Events + Appointments)
- ✅ Admin UI (Dashboard, Settings, Calendars, Events, Sync, Debug)
- ✅ Migration System (DB-Versionierung bis 2.2)
- ✅ Service Groups & Services Synchronisation
- ✅ Event Services Import
- ✅ Template System mit 13 Shortcode-Handlern
- ✅ Gutenberg Block & Elementor Integration
- ✅ Incremental Sync mit Deleted Events Detection
- ✅ Plugin-eigenes Logging System
- ✅ Clickable Events mit Modal Details
- ✅ Appointment Change Tracking
- ✅ Composite Unique Key (appointment_id + start_datetime)
- ✅ Separate Descriptions (Event vs. Appointment)
- ✅ Address Details & Tags Support
- ✅ Demo Mode mit realistischen Events

### v0.10.0.0: Plugin Architecture Cleanup (AKTUELL)
**Ziel:** Trennung von Production & Demo Features

**Änderungen:**
- ✅ Demo-Features in separates Plugin ausgelagert (churchtools-suite-demo)
- ✅ Filter-Hook `churchtools_suite_get_events` für Erweiterbarkeit
- ✅ Demo Data Provider bleibt, aber wird nur via Filter aktiviert
- ✅ Migration 2.3 (demo_users) entfernt
- ✅ Demo-Repository/Service-Klassen entfernt
- ✅ Production Plugin bereinigt für echte Gemeinden

**Deployment:**
- Production Plugin: Git + GitHub Releases
- Demo Plugin: SSH-only (KEIN Git)
- Demo Pages: SSH-only (KEIN Git)

---

## 🚀 In Arbeit

Keine aktuellen Arbeiten. Nächstes Milestone: v1.0.0

---

## 📋 Geplant

### v0.8.2.0: Extended Event Data
**Ziel:** Mehr Event-Informationen nutzen

**Features:**
- [ ] Neue Spalten: `note`, `information`, `category`, `image_url`, `link`, `cost`
- [ ] Migration für Schema-Änderungen
- [ ] Template Data Provider erweitern
- [ ] Frontend Displays aktualisieren
- [ ] Gutenberg Block erweitern (neue Felder)

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v0.8.3.0: Service Group Hierarchy
**Ziel:** Service-Verwaltung verbessern

**Features:**
- [ ] Service Group Namen in UI anzeigen
- [ ] Hierarchische Service-Organisation
- [ ] Filter nach Service Group
- [ ] Service Group Icons/Colors

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 2-3 Tage

### v0.9.0.0: Performance & Batch Processing
**Ziel:** Große Event-Mengen effizient verarbeiten

**Features:**
- [ ] Batch Event Processing (Chunk-Size konfigurierbar)
- [ ] Progress Tracking mit AJAX Polling
- [ ] Background Processing mit WP-Cron
- [ ] Abort Button für laufende Syncs
- [ ] Batch Database Inserts (Performance)
- [ ] API Response Caching (Transients)
- [ ] Query Optimization (Indexed Queries)

**Priorität:** Niedrig (aktuell keine großen Datenmengen erwartet)  
**Geschätzter Aufwand:** 4-5 Tage

### v0.9.1.0: Shortcode Presets
**Ziel:** Wiederverwendbare Konfigurationen

**Features:**
- [ ] Shortcode Presets Repository
- [ ] Migration für wp_cts_shortcode_presets Tabelle
- [ ] Presets speichern/laden/löschen
- [ ] System Presets (Default-Konfigurationen)
- [ ] User Presets (Custom-Konfigurationen)
- [ ] Admin UI für Preset-Verwaltung
- [ ] Import/Export Funktionalität

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v0.8.3.0: Service Group Hierarchy
**Ziel:** Service-Verwaltung verbessern

**Features:**
- [ ] Service Group Namen in UI anzeigen
- [ ] Hierarchische Service-Organisation
- [ ] Filter nach Service Group
- [ ] Service Group Icons/Colors

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 2-3 Tage

### v0.9.0.0: Performance & Batch Processing
**Ziel:** Große Event-Mengen effizient verarbeiten

**Features:**
- [ ] Batch Event Processing (Chunk-Size konfigurierbar)
- [ ] Progress Tracking mit AJAX Polling
- [ ] Background Processing mit WP-Cron
- [ ] Abort Button für laufende Syncs
- [ ] Batch Database Inserts (Performance)
- [ ] API Response Caching (Transients)
- [ ] Query Optimization (Indexed Queries)

**Priorität:** Niedrig (aktuell keine großen Datenmengen erwartet)  
**Geschätzter Aufwand:** 4-5 Tage

### v0.9.1.0: Shortcode Presets
**Ziel:** Wiederverwendbare Konfigurationen

**Features:**
- [ ] Shortcode Presets Repository
- [ ] Migration für wp_cts_shortcode_presets Tabelle
- [ ] Presets speichern/laden/löschen
- [ ] System Presets (Default-Konfigurationen)
- [ ] User Presets (Custom-Konfigurationen)
- [ ] Admin UI für Preset-Verwaltung
- [ ] Import/Export Funktionalität

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v0.9.2.0: Advanced Filtering & Search
**Ziel:** Bessere Event-Suche

**Features:**
- [ ] Calendar-Filter in Shortcodes
- [ ] Datum-Range Filter
- [ ] Service-Filter
- [ ] Text-Search
- [ ] AJAX Live-Search
- [ ] URL Parameter Support

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v1.0.0.0: Production Ready
**Ziel:** Stable Release für Production

**Features:**
- [ ] Security Audit
- [ ] Performance Audit
- [ ] Dokumentation komplett
- [ ] Unit Tests
- [ ] Integration Tests
- [ ] WordPress.org Submission Vorbereitung

**Priorität:** Hoch  
**Geschätzter Aufwand:** 5-7 Tage

---

## 🔮 Zukünftig

### v1.1.0: Multi-Language Support
- [ ] Übersetzungs-Dateien (.pot, .po, .mo)
- [ ] WPML/Polylang Kompatibilität
- [ ] ChurchTools Multi-Language Events

### v1.2.0: Extended Frontend Widgets
- [ ] Weitere Slider-Varianten
- [ ] Countdown-Templates
- [ ] Cover-Templates
- [ ] Timetable-Templates
- [ ] Carousel-Templates

### v1.3.0: Advanced Integration
- [ ] REST API Endpoints (öffentlich)
- [ ] Webhook Support
- [ ] iCal Export
- [ ] Google Calendar Integration

### v1.4.0: Extended Admin Tools
- [ ] Bulk Operations
- [ ] Advanced Filtering
- [ ] Export/Import
- [ ] Statistics & Analytics

---

## 🐛 Bekannte Probleme

### Kritisch
- Keine kritischen Bugs bekannt

### Mittel
- Keine

### Niedrig
- Keine

---

## 📝 Notizen

### Technische Schulden
- [ ] Tests hinzufügen (PHPUnit)
- [ ] Code Coverage erhöhen
- [ ] Inline-Dokumentation vervollständigen
- [ ] Performance Profiling durchführen

### Verbesserungsideen
- [ ] Dashboard Widgets
- [ ] Quick-Edit in Event-Liste
- [ ] Drag & Drop Kalender-Sortierung
- [ ] Visual Shortcode Builder
- [ ] Template Preview im Admin

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

**Letzte Aktualisierung:** 22. Dezember 2025 (v0.8.1.0)

### Mittel
- Keine

### Niedrig
- Keine

---

## 📝 Notizen

### Technische Schulden
- [ ] Tests hinzufügen (PHPUnit)
- [ ] Code Coverage erhöhen
- [ ] Inline-Dokumentation vervollständigen
- [ ] Performance Profiling durchführen

### Verbesserungsideen
- [ ] Dashboard Widgets
- [ ] Quick-Edit in Event-Liste
- [ ] Drag & Drop Kalender-Sortierung
- [ ] Visual Shortcode Builder
- [ ] Template Preview im Admin

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

**Letzte Aktualisierung:** 22. Dezember 2025 (v0.8.0.0)
