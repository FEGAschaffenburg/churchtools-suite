# ChurchTools Suite - Roadmap

> **Aktueller Stand:** v0.9.0 (Januar 2026)  
> **Nächstes Milestone:** v1.0.0 - Production Ready

---

## 🎯 Vision

ChurchTools Suite ist eine umfassende WordPress-Integration für ChurchTools, die es Gemeinden ermöglicht, ihre Termine, Kalender und Services nahtlos auf ihrer Website zu präsentieren.

---

## ✅ Abgeschlossen

### v0.9.0: Clean Slate - Fokus auf Stabilität (AKTUELL)
**Ziel:** Zurück zu den Grundlagen - Bewährtes behalten, Komplexität reduzieren

**Änderungen:**
- ✅ Simplified Database Schema (zurück zu DB Version 1.0)
- ✅ Nur essentielle Tabellen: calendars, events, event_services, schedule, sync_history, services, service_groups, shortcode_presets
- ✅ Composite Unique Key für Events (appointment_id + start_datetime)
- ✅ Migration System bereinigt (nur noch v1.0)
- ✅ Demo-Features in separates Plugin ausgelagert (churchtools-suite-demo)
- ✅ Cookie-basierte ChurchTools API-Authentifizierung
- ✅ Repository-Pattern für Datenzugriff
- ✅ 2-Phasen Event-Sync (Events + Appointments)
- ✅ Admin UI (Dashboard, Settings, Sync, Debug)
- ✅ Service Groups & Services Synchronisation
- ✅ Event Services Import
- ✅ List/Classic Template als Basis-View
- ✅ Gutenberg Block & Elementor Integration (minimiert)
- ✅ Plugin-eigenes Logging System

**Was entfernt wurde:**
- ❌ Komplexe Template-Varianten (werden schrittweise neu aufgebaut)
- ❌ Demo Users Feature (jetzt in Demo-Plugin)
- ❌ Interne Registrierungs-Features
- ❌ Überladene Admin-Tabs (Calendars, Events)

**Deployment:**
- Production Plugin: Git + GitHub Releases
- Demo Plugin: SSH-only (KEIN Git)
- Demo Pages: SSH-only (KEIN Git)

---

## 🚀 In Arbeit

Keine aktuellen Arbeiten. Bereit für nächstes Milestone.

---

## 📋 Geplant

### v0.9.1: Template System Rebuild Phase 1
**Ziel:** Bewährte Templates wieder aktivieren

**Features:**
- [ ] Grid View (card, masonry)
- [ ] Timeline View
- [ ] Agenda View
- [ ] Template Data Provider erweitern
- [ ] Frontend Tests für alle Views

**Priorität:** Hoch  
**Geschätzter Aufwand:** 3-4 Tage

### v0.9.2: Template System Rebuild Phase 2
**Ziel:** Erweiterte Templates

**Features:**
- [ ] Calendar View (monthly, weekly)
- [ ] Slider View
- [ ] Timetable View
- [ ] Modal Details verbessern
- [ ] Responsive Optimierung

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v0.9.3: Admin UI Rebuild
**Ziel:** Daten-Tabs wieder aktivieren

**Features:**
- [ ] Calendars Tab (Liste, Sync, Auswahl)
- [ ] Events Tab (Liste, Filter, Details)
- [ ] Services Tab (verbessert)
- [ ] Quick-Edit Funktionen
- [ ] Bulk Operations

**Priorität:** Mittel  
**Geschätzter Aufwand:** 4-5 Tage

### v0.9.4: Extended Event Data
**Ziel:** Mehr Event-Informationen nutzen

**Features:**
- [ ] Neue Spalten: `note`, `information`, `category`, `image_url`, `link`, `cost`
- [ ] Migration für Schema-Änderungen
- [ ] Template Data Provider erweitern
- [ ] Frontend Displays aktualisieren

**Priorität:** Mittel  
**Geschätzter Aufwand:** 2-3 Tage

### v0.9.5: Shortcode Presets
**Ziel:** Wiederverwendbare Konfigurationen

**Features:**
- [ ] Presets speichern/laden/löschen
- [ ] System Presets (Default-Konfigurationen)
- [ ] User Presets (Custom-Konfigurationen)
- [ ] Admin UI für Preset-Verwaltung
- [ ] Import/Export Funktionalität

**Priorität:** Mittel  
**Geschätzter Aufwand:** 3-4 Tage

### v0.9.6: Advanced Filtering & Search
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

**Ziel:** Stable Release für Production

**Features:**
- [ ] Security Audit
- [ ] Performance Audit
- [ ] Code Quality Review
- [ ] Dokumentation vervollständigen
- [ ] Unit Tests (PHPUnit)
- [ ] Integration Tests
- [ ] WordPress.org Submission Vorbereitung
- [ ] Performance Profiling

**Priorität:** Hoch  
**Geschätzter Aufwand:** 5-7 Tage

---

## 🔮 Zukünftig (Post-1.0)

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

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 4-5 Tage

### v1.2.0: Multi-Language Support
- [ ] Übersetzungs-Dateien (.pot, .po, .mo)
- [ ] WPML/Polylang Kompatibilität
- [ ] ChurchTools Multi-Language Events

### v1.5.0: Komponenten-Templates (siehe v1.4.0 Phase 6)
**Bereits in v1.4.0 integriert** - Komponenten-basierte Templates

### v1.6.0: Advanced Integration
- [ ] REST API Endpoints (öffentlich)
- [ ] Webhook Support
- [ ] iCal Export
- [ ] Google Calendar Integration

### v1.7.0: Extended Admin Tools
- [ ] Bulk Operations (erweitert)
- [ ] Advanced Filtering
- [ ] Export/Import
- [ ] Statistics & Analytics

---

## 🔮 Vision Features (v2.0+)

### Template Marketplace (v2.0)
- [ ] Templates kaufen/verkaufen
- [ ] Rating & Reviews
- [ ] Automatic Updates für gekaufte Templates
- [ ] Template-Bundles

### Visual Template Editor (v2.1)
- [ ] Drag & Drop Editor
- [ ] Live-Preview beim Editieren
- [ ] Component-Library
- [ ] CSS-Visual-Editor

### Template Versioning (v2.2)
- [ ] Git-ähnliche Versionskontrolle
- [ ] Rollback-Funktion
- [ ] Change History
- [ ] Template-Diffs anzeigen

### AI-Powered Features (v2.3)
- [ ] AI-Template-Generator (Template aus Beschreibung generieren)
- [ ] Smart Layout-Suggestions
- [ ] Auto-Optimization für Performance
- [ ] Content-aware Styling

### v1.3.0: Extended Frontend Widgets
- [ ] Weitere Slider-Varianten
- [ ] Countdown-Templates
- [ ] Cover-Templates
- [ ] Carousel-Templates

### v1.4.0: Template Manager & Structure Refactoring
**Ziel:** Professionelles Template-System mit zentraler Verwaltung

**See:** [TEMPLATE-STRUCTURE-PROPOSAL.md](docs/TEMPLATE-STRUCTURE-PROPOSAL.md)

**Phase 1: Struktur-Refactoring (v1.4.0)**
- [ ] Neue hierarchische Ordnerstruktur
  - `event/` - Event-Templates (list, grid, single, modal, calendar)
  - `calendar/` - Kalender-Komponenten (card, widget, badge)
  - `tag/` - Tag-Komponenten (badge, card, cloud)
  - `service/` - Service-Komponenten (list, card, person)
  - `partial/` - Wiederverwendbare Teile (date-badge, time-range, location-card)
  - `system/` - System-Templates (nicht editierbar)
  - `custom/` - User-Templates (editierbar/uploadbar)
- [ ] Migration bestehender Templates
- [ ] Migration-Script für v0.9.x → v1.4.0
- [ ] Kompatibilitäts-Layer für alte Pfade
- [ ] Template-Header mit Metadaten (Name, Type, Version, Author)

**Phase 2: Template-Manager Backend (v1.4.1)**
- [ ] Neue DB-Tabelle: `wp_cts_templates`
- [ ] Template-Registration-API
- [ ] Template-Scanner (automatisches Erkennen)
- [ ] Template-Validator
- [ ] Template-Renderer mit Caching
- [ ] Template-Abhängigkeiten (Requires: image-helper, calendar-helper)

**Phase 3: Template-Manager UI (v1.4.2)**
- [ ] Admin-Seite: `ChurchTools Suite > Templates`
- [ ] Template-Bibliothek (Liste mit Gruppierung)
- [ ] Aktivieren/Deaktivieren-Toggle pro Template
- [ ] Template-Einstellungen-Seite (pro Template)
- [ ] Filter: System / Custom / Typ
- [ ] Suche nach Name/Tags

**Phase 4: Template-Upload (v1.4.3)**
- [ ] ZIP-Upload-Funktion
- [ ] Template-Validator für Uploads
- [ ] PHP-Code-Scanner (Sicherheit)
- [ ] Custom-Templates-Verwaltung
- [ ] Template-JSON-Format (template.json)
- [ ] Assets-Management (CSS, JS, Images)

**Phase 5: Template-Previews (v1.4.4)**
- [ ] Screenshot-Generator
- [ ] Live-Preview mit Test-Daten
- [ ] Responsive-Vorschau (Desktop/Tablet/Mobile)
- [ ] Template-Galerie mit Vorschaubildern

**Phase 6: Komponenten-Templates (v1.5.0)**
- [ ] Calendar-Komponenten (card, widget, badge, list-item)
- [ ] Tag-Komponenten (badge, card, cloud)
- [ ] Service-Komponenten (list, card, badge, person)
- [ ] Partial-Templates (date-badge, time-range, location-card, image-hero, meta-card)
- [ ] Shortcodes für Komponenten:
  - `[cts_calendar_card id="main" template="widget"]`
  - `[cts_tag_cloud template="cloud" count="20"]`
  - `[cts_service_list event_id="123" template="person"]`

**Vorteile:**
- ✅ Zentrale Template-Verwaltung
- ✅ Wiederverwendbare Komponenten (DRY-Prinzip)
- ✅ Custom Templates hochladen/verwalten
- ✅ Template-Previews vor Aktivierung
- ✅ Klare Template-Hierarchie
- ✅ Template-spezifische Einstellungen
- ✅ Sicherheits-Validierung für Custom Templates

**Priorität:** Mittel  
**Geschätzter Aufwand:** 15-20 Tage (6 Phasen)  
**Hinweis:** Revolutioniert das Template-System - ermöglicht modulare Komponenten

---

### v1.4.7: Component Manager (Admin UI für Components)
**Ziel:** Separater Manager für wiederverwendbare Komponenten

**Features:**
- [ ] Admin-Seite: `ChurchTools Suite > Components`
- [ ] Component-Bibliothek (nach Typ gruppiert: Calendar, Tag, Service, Partials)
- [ ] Component-Vorschau (Screenshots + Code-Beispiel)
- [ ] Component-Aktivierung (Ein/Aus-Schalter)
- [ ] Usage-Tracking (Welche Views verwenden welche Components?)
- [ ] Component-Einstellungen (pro Component konfigurierbar)
- [ ] Component-Upload (ZIP-Upload für Custom Components)
- [ ] Component-Dokumentation (Verwendung, Parameter, Beispiele)

**Use Cases:**
```php
// Component in View verwenden
get_template_part( 'components/partials/date-badge', null, ['event' => $event] );

// Component via Shortcode
[cts_component type="date-badge" event_id="123"]
```

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 5-7 Tage  
**Hinweis:** Aktuell bleiben Components in Settings > Templates, Manager kommt später

---

### v1.4.5: Advanced Style Customizer (verschoben von v1.4.0)
**Ziel:** Granulare Style-Kontrolle für jedes UI-Element

**Features:**
- [ ] Element-spezifische Farben (Datum-Box, Titel, Location, Tags, etc.)
- [ ] Schriftarten-Auswahl pro Element
- [ ] Schriftgrößen-Steuerung
- [ ] Abstände & Ränder (Padding, Margin)
- [ ] Border-Styles (Radius, Width, Color)
- [ ] Shadow-Effekte
- [ ] Hover-States
- [ ] Style-Presets (speichern/laden)
- [ ] CSS-Export für Custom-Themes

**Priorität:** Niedrig  
**Geschätzter Aufwand:** 6-8 Tage  
**Hinweis:** Nur für Advanced User - normale User nutzen Theme/Plugin/Custom-Basis-Modus

### v1.5.0: Advanced Integration
- [ ] REST API Endpoints (öffentlich)
- [ ] Webhook Support
- [ ] iCal Export
- [ ] Google Calendar Integration

### v1.6.0: Extended Admin Tools
- [ ] Bulk Operations (erweitert)
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

**Letzte Aktualisierung:** 8. Januar 2026 (v0.9.9.43 - Template Configuration)

