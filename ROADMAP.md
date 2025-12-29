# ChurchTools Suite - Roadmap

> **Aktueller Stand:** v0.9.0.0 (22. Dezember 2025)  
> **Nächstes Milestone:** v0.9.1.0 - Shortcode Presets

---

## 🎯 Vision

ChurchTools Suite ist eine umfassende WordPress-Integration für ChurchTools, die es Gemeinden ermöglicht, ihre Termine, Kalender und Services nahtlos auf ihrer Website zu präsentieren.

---

## ✅ Abgeschlossen

### v0.1.0 - v0.3.13.0: Foundation & Core Features
- ✅ Cookie-basierte ChurchTools API-Authentifizierung
- ✅ Repository-Pattern für Datenzugriff
- ✅ Kalender & Events Synchronisation (2-Phasen: Events + Appointments)
- ✅ Admin UI (Dashboard, Settings, Calendars, Events, Sync, Debug)
- ✅ Migration System (DB-Versionierung)
- ✅ Service Groups & Services Synchronisation
- ✅ Event Services Import (Personen-Zuordnung)
- ✅ Auto-Sync mit WP-Cron
- ✅ Sync-Historie Tracking

### v0.4.0 - v0.5.9.38: Template System & Frontend
- ✅ Template Loader mit Theme-Override Support
- ✅ 13 Shortcode-Handler (Calendar, List, Grid, Modal, Slider, etc.)
- ✅ Template Data Provider Service
- ✅ Frontend CSS/JS mit Responsive Design
- ✅ Shortcode Manager (Admin UI für Shortcode-Übersicht)
- ✅ Gutenberg Block Integration (Vereinfachter Block mit View-Auswahl)
- ✅ Elementor Integration

### v0.6.0 - v0.6.5.19: Advanced Templates & UI
- ✅ List Templates optimiert (Classic, Medium, Fluent)
- ✅ Grid Templates erweitert
- ✅ Calendar Templates (Monthly, Weekly)
- ✅ Demo-Seiten für alle View-Typen

### v0.7.0.0 - v0.7.2.0: Sync Optimizations & Single Event
- ✅ Incremental Sync (Modified-After Parameter)
- ✅ Deleted Events Detection (nur bei Full Sync)
- ✅ Plugin-eigenes Logging System (JSON, Rotation)
- ✅ Single Event Templates & Data Providers
- ✅ Enhanced Error Handling (500 Errors, JSON Validation, Shutdown Handler)

### v0.7.2.1 - v0.7.3.3: Bugfixes & Refinements
- ✅ Logger Parameter Fixes (3-Parameter Format)
- ✅ Backward-Compatibility Constants (INFO, DEBUG, etc.)
- ✅ Enhanced Debug Logging (Event Sync, Service Import)
- ✅ Service Import Validation (personId auf eventService-Level)
- ✅ Date Range Filtering (Events & Appointments)
- ✅ Calculated Date Range Display in Settings

### v0.7.4.0: Admin Navigation Restructure
- ✅ Sub-Tab Navigation (Einstellungen, Daten)
- ✅ Advanced Mode Toggle (Debug-Tab optional)
- ✅ Reorganized Settings (API, Sync, Calendars, Services, Advanced)
- ✅ Data Section (Events, Imported Services)

### v0.8.0.0: Clickable Events & Modal Details
- ✅ List Templates clickable (classic, medium, fluent)
- ✅ Grid Templates clickable (simple, modern, colorful)
- ✅ JavaScript Event-Handler für .cts-event-clickable
- ✅ CSS Hover-Effekte und Keyboard-Support
- ✅ Modal-Integration mit existierenden Single Event Templates
- ✅ Accessibility (role="button", tabindex, aria-label)

### v0.8.1.0 - v0.8.1.3: Appointment Change Tracking & Combined Descriptions
- ✅ Migration 1.9: `appointment_modified` Spalte in events-Tabelle
- ✅ Kombinierte Descriptions (Event.note + Appointment.note)
- ✅ Phase 1: Event-Sync extrahiert appointment_modified Timestamp
- ✅ Phase 2: Appointments-Sync prüft auf Änderungen
- ✅ Update-Logik für Appointment-Only Changes
- ✅ Kombinierte Description mit Separator "--- Termindetails ---"
- ✅ Template-Bugfixes (list/medium.php Syntax-Fehler)

### v0.9.0.0: Appointment als Primary Key (AKTUELL - CRITICAL FIX)
**Ziel:** Fix fundamental data model bug - recurring events overwriting each other

**Problem:**
- ChurchTools Datenmodell: appointment_id = Serie-ID für wiederkehrende Termine
- Gleiche appointment_id kann mehrfach vorkommen mit unterschiedlichen Daten
- Beispiel: "Gottesdienst" (appointment_id 5084)
  - 2025-10-31 17:00 (Instanz 1)
  - 2025-11-14 17:00 (Instanz 2)
  → **Gleiche appointment_id, unterschiedliche start_datetime!**
- Alte Implementierung: appointment_id als UNIQUE Key → Instanzen überschreiben sich

**Lösung:**
- ✅ Migration 2.0: COMPOSITE UNIQUE KEY (appointment_id, start_datetime)
- ✅ event_id wird nullable (für standalone appointments)
- ✅ event_id behält INDEX für Filterung nach Serie
- ✅ Repository: Alle Methoden verwenden COMPOSITE KEY
  - `upsert_by_appointment_id()` - Prüft appointment_id + start_datetime
  - `exists_by_appointment_id()` - Kann mit/ohne start_datetime prüfen
  - `get_by_appointment_id()` - Gibt spezifische Instanz zurück
- ✅ Sync Service: Übergibt start_datetime bei allen Prüfungen
- ✅ Korrekte Statistiken (inserted/updated)

**API Verhalten:**
ChurchTools /calendars/{id}/appointments API liefert für wiederkehrende Termine:
```json
// Instanz 1:
{"appointment":{"base":{"id":5084}}, "calculated":{"startDate":"2025-10-31T17:00:00Z"}}
// Instanz 2:
{"appointment":{"base":{"id":5084}}, "calculated":{"startDate":"2025-11-14T17:00:00Z"}}
```
→ Gleiche ID, unterschiedliche Daten = Serie, nicht einzelner Termin!

---

## 🚀 In Arbeit

Keine aktuellen Arbeiten. Nächstes Milestone: v0.9.1.0

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
