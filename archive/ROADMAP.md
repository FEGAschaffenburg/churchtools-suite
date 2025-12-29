# ChurchTools Suite - Roadmap (DEPRECATED)

> ⚠️ **Diese Roadmap ist veraltet!**  
> Bitte siehe **[ROADMAP-2025.md](ROADMAP-2025.md)** für die aktuelle Planung.
> 
> **Aktueller Stand:** v0.6.5.19 (18. Dezember 2025)  
> **Nächstes Release:** v0.7.0.0 - Sync-Optimierungen

---

## Archiviert: Alter Stand v0.5.9.38
✅ Backend komplett funktional
✅ Frontend-Framework komplett (Template Loader, Shortcodes, Data Provider)
✅ Gutenberg-Block vereinfacht (ein Block mit View-Auswahl)
✅ Elementor-Integration komplett
✅ Classic & Medium List Templates funktional
⏳ Weitere View-Templates folgen iterativ

---

## ✅ Abgeschlossen: v0.3.1.0 - v0.3.13.0

### v0.3.1.0 - v0.3.8.4 (Basis-Implementierung)
- ✅ Cookie-basierte ChurchTools API-Authentifizierung
- ✅ Repository Base-Klasse mit CRUD-Operationen
- ✅ Calendars Repository & Sync Service
- ✅ Events Repository & Event Sync Service (2-Phasen)
- ✅ Admin UI komplett (Dashboard, Settings, Calendars, Events, Sync, Debug)
- ✅ Kalender-Auswahl & Event-Synchronisation
- ✅ AJAX Handlers für manuellen Sync
- ✅ Migration System (DB v1.0, v1.1)

### v0.3.9.0 - Custom Cron-Intervalle (deprecated)
- ✅ Custom Cron-Intervalle implementiert (30min-12h)

### v0.3.9.1 - Tages-Intervalle & WP-Cron Detection
- ✅ Sync-Intervalle auf Tage umgestellt (täglich, 2-30 Tage)
- ✅ WP-Cron Disabled Detection mit Warnung
- ✅ System-Cron Anleitung bei deaktiviertem WP-Cron

### v0.3.9.2 - Erweitertes Error-Handling
- ✅ Try-Catch Block um Auto-Sync
- ✅ Fehler-Tracking in wp_options
- ✅ Dashboard & Debug-Tab zeigen Sync-Status mit Details

### v0.3.9.3 - Sync-Historie Tabelle
- ✅ Sync-Historie in eigener Tabelle (Migration 1.2)
- ✅ Sync History Repository mit CRUD-Operationen
- ✅ Debug-Tab zeigt letzte 10 Syncs

### v0.3.9.4 - Manueller Cron-Trigger
- ✅ AJAX-Endpoints für manuellen Sync & Keepalive
- ✅ Debug-Tab mit Trigger-Buttons
- ✅ Sofortiges Feedback mit Statistiken

### v0.3.10.0 - Event Services Repository
- ✅ Event Services Repository mit CRUD
- ✅ Tabelle `wp_cts_event_services` nutzen
- ✅ get_for_event(), delete_for_event(), get_unique_service_names()

### v0.3.11.0 - Services-Tabelle & Auswahl-UI
- ✅ Migration 1.3: wp_cts_services Tabelle
- ✅ Services Repository mit CRUD & Selection
- ✅ Service Sync Service (/api/services)
- ✅ Tab "Services" im Admin mit Sync & Auswahl
- ✅ AJAX Handlers & JavaScript

### v0.3.11.1 - Repository Table Prefix Fix
- ✅ Services Repository: CHURCHTOOLS_SUITE_DB_PREFIX hinzugefügt
- ✅ Event Services Repository: CHURCHTOOLS_SUITE_DB_PREFIX hinzugefügt

### v0.3.11.2 - API Endpoint Fix
- ✅ Service Sync: Endpoint von /api/masterdata/serviceGroups zu /api/services geändert
- ✅ Service Structure: serviceGroupId Feld genutzt

### v0.3.11.3 - Service Groups Selection (2-Step Workflow)
- ✅ Migration 1.4: wp_cts_service_groups Tabelle
- ✅ Service Groups Repository mit CRUD & Selection
- ✅ sync_service_groups() Methode (/api/servicegroups)
- ✅ sync_services() filtert nach ausgewählten Gruppen
- ✅ AJAX Handlers für Service Groups
- ✅ Tab "Services" mit 3-Schritt-Workflow
- ✅ JavaScript für Service Groups Sync & Selection

### v0.3.11.4 - API Endpoint Verification
- ✅ Korrekte Endpoints verifiziert: /api/servicegroups und /api/services
- ✅ Keine /api/masterdata/* Prefix nötig

### v0.3.11.5 - Debug-Logging
- ✅ Debug-Logging in CT Client (API Requests mit URL)
- ✅ Debug-Logging in Service Sync (Gruppen & Services)
- ✅ AJAX-Fehler zeigen URL an

### v0.3.11.6 - Doppeltes "api" Fix
- ✅ Endpoint-Korrektur: `servicegroups` statt `/api/servicegroups`
- ✅ api_request() fügt bereits `/api/` Prefix hinzu

### v0.3.12.0 - Event Services Sync
- ✅ Event Sync Service erweitert
- ✅ process_event_services() Methode
- ✅ eventServices aus Events API extrahiert
- ✅ Filter nach ausgewählten Services
- ✅ Speicherung in event_services Tabelle
- ✅ Auto-Delete alter Services bei Event-Update
- ✅ Person-Name aus eventServices extrahiert
- ✅ Debug-Logging für Service-Import

### v0.3.13.0 - Services UI in Events-Tab
- ✅ Events-Tab erweitert
- ✅ Services-Spalte in Event-Tabelle
- ✅ Service-Name mit Person-Name anzeigen
- ✅ CSS-Styling für Services-Anzeige
- ✅ get_for_event() Methode im Event Services Repository

**Dateien:**
- `admin/views/tab-events.php`
- `admin/css/churchtools-suite-admin.css`

---

## ✅ v0.4.0.0 - Template Loader (COMPLETED)
**Ziel:** Template-System vorbereiten

### Features:
- ✅ Template Loader (class-churchtools-suite-template-loader.php)
  - Template-System für Themes
  - Override-Mechanismus (Theme > Plugin)
  - Default-Templates
  - locate_template(), render_template(), get_available_views()
- ✅ Basis-Templates erstellt
  - templates/calendar/monthly-modern.php
  - templates/list/classic.php
  - templates/grid/simple.php
- ✅ Template README mit vollständiger Dokumentation

---

## ✅ v0.5.0.0 - Shortcode Handler (COMPLETED)
**Ziel:** Alle Shortcodes für View-Typen implementieren

### Features:
- ✅ Shortcode Handler (class-churchtools-suite-shortcodes.php)
  - [cts_calendar] - Calendar Views (monthly, weekly, yearly, daily)
  - [cts_list] - List Views (classic, modern, minimal, with-map, fluent)
  - [cts_grid] - Grid Views (simple, modern, colorful, with-map)
  - [cts_modal] - Modal Single Event
  - [cts_slider] - Slider Views (type-1 bis type-5)
  - [cts_countdown] - Countdown Views (type-1 bis type-3)
  - [cts_cover] - Cover Views (classic, modern, clean)
  - [cts_timetable] - Timetable Views (modern, clean, timeline)
  - [cts_carousel] - Carousel Views (type-1 bis type-4)
  - [cts_single] - Single Event Views
  - [cts_map] - Map Views (standard, advanced, liquid)
  - [cts_search] - Search Views (bar, advanced)
  - [cts_widget] - Widget Views (upcoming, calendar-widget)
  - [cts_events] - Legacy-Kompatibilität
- ✅ Template Data Provider Service
  - Fetch & Format Events
  - Date/Time Formatting
  - Services Integration
  - Calendar Info Integration
  - Helper Methods: get_events_by_date(), get_events_by_calendar()

---

## ✅ v0.5.1.0 - Frontend CSS/JS (COMPLETED)
**Ziel:** Styling für Frontend

### Features:
- ✅ Frontend CSS
  - public/css/churchtools-suite-public.css
  - Calendar View Styles
  - List View Styles
  - Grid View Styles
  - Responsive Design
  - Loading States
  - Empty States
- ✅ Frontend JS
  - public/js/churchtools-suite-public.js
  - Calendar Navigation
  - Grid Detail Buttons
  - Modal Views
  - Event Click Handlers
  - AJAX Integration
- ✅ Conditional Asset Loading
  - Nur laden wenn Shortcodes verwendet werden
  - has_shortcode() Check für alle CTS Shortcodes

---

## ✅ v0.5.2.0 - v0.5.7.2 - Frontend UI & Shortcode Manager (COMPLETED)
**Ziel:** Admin-Interface für Shortcodes

### Features:
- ✅ Shortcode Manager als Admin-Subpage
  - admin/views/shortcode-manager.php
  - Grid-Layout mit allen 13 Shortcodes
  - Filter: Text-Suche + Kategorie
  - Details: Views & Parameter (expandable)
  - Copy-to-Clipboard Funktion
  - Ohne jQuery - Vanilla JavaScript
- ✅ Basis-Templates implementiert
  - templates/calendar/monthly-modern.php
  - templates/list/classic.php
  - templates/grid/simple.php
  - templates/modal/default.php

---

## ✅ v0.5.8.0 - Gutenberg Block Integration (COMPLETED)
**Ziel:** Shortcodes im Block-Editor verfügbar machen

### Features:
- ✅ Gutenberg Blocks Class
  - includes/class-churchtools-suite-blocks.php
  - Custom Block Category "ChurchTools Suite"
  - Block Registration mit `register_block_type()`
- ✅ Block Editor JavaScript
  - admin/js/churchtools-suite-blocks.js
  - InspectorControls für Einstellungen
  - Preview im Editor
  - View-Selektor, Calendar IDs, Limit/Columns
- ✅ Three Core Blocks
  - churchtools-suite/calendar (8 views)
  - churchtools-suite/list (4 views)
  - churchtools-suite/grid (3 views)
- ✅ Server-Side Rendering
  - Render Callbacks nutzen existierende Shortcodes
  - Keine Duplikation von Template-Logik

---

## ✅ v0.5.8.0 - v0.5.9.38 - Blocks & Templates (COMPLETED)
**Ziel:** Block-Editor vereinfachen und Templates optimieren

### Features:
- ✅ Gutenberg Block vereinfacht (v0.5.9.36)
  - Ein einziger Block: `churchtools-suite/events`
  - Zweistufige Auswahl: Ansichtstyp → Variante
  - Smart Controls (Spalten nur bei Grid, Services nur bei List)
  - wp-api-fetch Integration für Kalender-Abruf
- ✅ Elementor Integration (v0.5.9.38)
  - Widget: `ChurchTools Events`
  - Kategorie: "ChurchTools Suite"
  - Gleiche Auswahl-Logik wie Gutenberg Block
- ✅ List Templates optimiert (v0.5.9.25-35)
  - Classic List: Kompaktes horizontales Layout
  - Medium List: Datumbox + Beschreibung flex
  - Services optional & horizontal
- ✅ Demo-Seiten vereinfacht (v0.5.9.25)
  - Modulare Demo-Files pro View-Typ
  - Live-Rendering mit do_shortcode()

---

## v0.5.9.0 - Erweiterte Gutenberg-Blocks (DEPRECATED)
**Ziel:** Weitere Shortcode-Typen als Blocks

### Features:
- [ ] Slider Block
  - churchtools-suite/slider (5 views)
  - Autoplay & Interval Settings
- [ ] Countdown Block
  - churchtools-suite/countdown (3 views)
  - Target Event Selection
- [ ] Cover Block
  - churchtools-suite/cover (5 views)
  - Background Options
- [ ] Widget Block
  - churchtools-suite/widget (3 views)
  - Sidebar-optimiert

---

## v0.5.10.0 - Shortcode-Presets Repository
**Ziel:** Vordefinierte Konfigurationen

### Features:
- [ ] Shortcode Presets Repository
  - Presets speichern/laden
  - Name, Beschreibung, Konfiguration (JSON)
  - get_all(), get_by_id(), save(), delete()

---

## v0.6.0.0 - Weitere Event-Templates (AKTUELL)
**Ziel:** Mehr View-Varianten implementieren

### Priorität 1: List Views
- [ ] Fluent List optimieren
  - templates/list/fluent.php
  - Moderne Fluent Design Language
  - Responsive & animiert
  
### Priorität 2: Calendar Views
- [ ] Calendar Templates erstellen
  - templates/calendar/monthly-modern.php
  - templates/calendar/monthly-clean.php
  - templates/calendar/weekly-fluent.php
  - templates/calendar/yearly.php
  - templates/calendar/daily.php
  
### Priorität 3: Grid Views
- [ ] Grid Templates erweitern
  - templates/grid/modern.php
  - templates/grid/colorful.php
  - templates/grid/simple.php (bereits vorhanden)

### Priorität 4: Weitere View-Typen
- [ ] Slider Views (5 Varianten)
- [ ] Countdown Views (3 Varianten)
- [ ] Cover Views (5 Varianten)
- [ ] Timetable Views (3 Varianten)
- [ ] Carousel Views (4 Varianten)

---

## v0.4.0.0 - Rate Limiting
**Ziel:** API-Schutz

### Features:
- [ ] Rate Limiter (class-churchtools-suite-rate-limiter.php)
  - Request-Limits pro Zeiteinheit
  - Schutz vor API-Überlastung
  - Automatische Throttling
  - Transients für Counter

---

## v0.4.1.0 - Input Validator
**Ziel:** Sicherheit erhöhen

### Features:
- [ ] Input Validator (class-churchtools-suite-input-validator.php)
  - Validierung von Formulareingaben
  - Sanitization-Helfer
  - XSS-Schutz
  - Nonce-Validierung-Helfer

---

## v0.4.2.0 - Crypto Helper
**Ziel:** Credentials sicher speichern

### Features:
- [ ] Crypto Helper (class-churchtools-suite-crypto.php)
  - Passwort-Verschlüsselung in DB
  - Secure Storage für Credentials
  - WordPress Salts nutzen

---

## v0.4.3.0 - Logger
**Ziel:** Fehlersuche ermöglichen

### Features:
- [ ] Logger (class-churchtools-suite-logger.php)
  - Log-Levels (error, warning, info, debug)
  - Log-Dateien in wp-content/uploads/churchtools-suite/logs/
  - Log-Rotation (max 10 MB, max 30 Tage)
  - get_logs(), clear_logs()

---

## v0.4.4.0 - Debug-Tab
**Ziel:** Admin-Tools für Debugging

### Features:
- [ ] Admin Debug-Tab
  - View: tab-debug.php
  - System-Informationen (PHP, WordPress, Plugin-Version)
  - API-Test-Tool (Connection Test mit Details)
  - Log-Viewer
- [ ] Datenbank-Browser
  - Tabellen-Übersicht
  - Anzahl Einträge
  - Letzte Aktualisierung

---

## v0.4.5.0 - Updater
**Ziel:** Plugin-Updates verwalten

### Features:
- [ ] Updater (class-churchtools-suite-updater.php)
  - Versions-Prüfung gegen GitHub
  - Update-Benachrichtigungen
  - Automatische DB-Migrationen bei Update

---

## v0.4.6.0 - Migrations-System
**Ziel:** DB-Änderungen versionieren

### Features:
- [ ] Migrations-System
  - Versionierte Migrationen
  - migration-v{version}.php Dateien
  - Automatische Ausführung bei Update
  - Migration-Status in Options

---

## v0.5.0.0 - Internationalisierung
**Ziel:** Mehrsprachigkeit

### Features:
- [ ] i18n Setup (class-churchtools-suite-i18n.php)
  - Text-Domain laden
  - POT-Datei generieren
  - Deutsche Übersetzung (de_DE.po/mo)
- [ ] Alle Texte übersetzen
  - Admin-Texte
  - Frontend-Texte
  - JavaScript-Strings
  - Error-Messages

---

## v1.0.0.0 - Production Ready
**Ziel:** Produktionsreife Version

### Features:
- [ ] Performance-Optimierungen
  - DB-Query-Caching
  - Transients für API-Calls
  - Lazy-Loading

- [ ] Testing
  - Unit-Tests für Repositories
  - Integration-Tests für Services
  - E2E-Tests für Frontend

- [ ] Dokumentation
  - Benutzer-Handbuch
  - Entwickler-Dokumentation
  - API-Dokumentation
  - Video-Tutorials

- [ ] Polish
  - Icon-Set vervollständigen
  - Animationen
  - Accessibility (WCAG 2.1)
  - SEO-Optimierung

---

## Optionale Features (v1.1.0+)

### Calendar-View
- Monats-/Wochen-Ansicht
- FullCalendar.js Integration
- iCal-Export

### Extended Filtering
- Kategorie-Filter
- Orts-Filter
- Schlagwort-Filter
- Volltextsuche

### Notifications
- E-Mail-Benachrichtigungen bei neuen Events
- RSS-Feed
- Push-Notifications

### Widgets
- WordPress Widget für Sidebar
- Gutenberg-Blocks
- Elementor-Integration

### Advanced Shortcodes
- [cts_countdown] - Countdown bis Event
- [cts_next_event] - Nächster Termin
- [cts_event_count] - Event-Zähler
- [cts_person_schedule] - Persönliche Dienste

---

## Migration-Strategie vom alten Plugin

### Daten übernehmen:
1. Mapping alter → neuer Tabellennamen
2. Daten-Migration-Script
3. Option zur parallelen Nutzung
4. Deaktivierungs-Hinweis für altes Plugin

### Kompatibilität:
- Alte Shortcodes als Alias unterstützen
- Template-Pfade kompatibel halten
- Options-Migration

---

## v1.0.0.0 - Production Ready
**Ziel:** Produktionsreife Version

### Features:
- [ ] Performance-Optimierungen
  - DB-Query-Caching
  - Transients für API-Calls
  - Lazy-Loading
- [ ] Testing
  - Unit-Tests für Repositories
  - Integration-Tests für Services
- [ ] Dokumentation
  - Benutzer-Handbuch
  - Entwickler-Dokumentation
  - Video-Tutorials
- [ ] Polish
  - Accessibility (WCAG 2.1)
  - SEO-Optimierung

---

**Stand:** Version 0.5.9.38 (16. Dezember 2025)
**Nächster Schritt:** v0.6.0.0 - Weitere Event-Templates (List, Calendar, Grid)
