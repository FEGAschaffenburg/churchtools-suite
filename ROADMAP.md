# ChurchTools Suite - Roadmap

## Aktueller Stand: v0.3.1.0
✅ Cookie-basierte Authentifizierung mit Session Keep-Alive

---

## v0.3.2.0 - Repository Base-Klasse
**Ziel:** Grundlage für alle Repositories schaffen

### Features:
- [ ] Repository Base-Klasse (class-churchtools-suite-repository-base.php)
  - Gemeinsame Basis für alle Repositories
  - CRUD-Operationen (create, read, update, delete)
  - DB-Query-Helfer (where, orderBy, limit)
  - Prepared Statements für Sicherheit
  - Error-Handling

---

## v0.3.3.0 - Calendars Repository
**Ziel:** Kalender-Daten verwalten

### Features:
- [ ] Calendars Repository (class-churchtools-suite-calendars-repository.php)
  - Erweitert Repository Base
  - Kalender aus DB lesen/schreiben
  - get_all(), get_by_id(), get_selected()
  - is_selected Flag für Auswahl
  - Kalender-Sync-Status

---

## v0.3.4.0 - Calendar Sync Service
**Ziel:** Kalender von ChurchTools abrufen

### Features:
- [ ] Calendar Sync Service (class-churchtools-suite-calendar-sync-service.php)
  - API-Call zu `/calendars`
  - ChurchTools-Kalender abrufen
  - In `cts_calendars` Tabelle speichern
  - Sync-Timestamp verwalten
  - Fehlerbehandlung und Logging

---

## v0.3.5.0 - Admin UI: Kalender-Tab
**Ziel:** Kalender im Admin anzeigen und auswählen

### Features:
- [ ] Tab "Kalender" im Admin
  - Neuer Tab in admin-page.php
  - View: tab-calendars.php
- [ ] Kalender-Liste anzeigen
  - Alle synchronisierten Kalender
  - Name, Beschreibung, Farbe
  - Checkbox für Auswahl
- [ ] "Kalender synchronisieren" Button
  - AJAX Handler für Kalender-Sync
  - Progress-Anzeige
  - Erfolgs-/Fehlermeldung

---

## v0.3.6.0 - Events Repository
**Ziel:** Event-Daten verwalten

### Features:
- [ ] Events Repository (class-churchtools-suite-events-repository.php)
  - Erweitert Repository Base
  - Events aus DB lesen/schreiben
  - get_all(), get_by_calendar(), get_upcoming()
  - Suche/Filter-Funktionen
  - Bulk-Operations

---

## v0.3.7.0 - Event Sync Service
**Ziel:** Events von ChurchTools abrufen

### Features:
- [ ] Event Sync Service (class-churchtools-suite-sync-service.php)
  - API-Call zu `/calendars/{id}/appointments`
  - Termine für ausgewählte Kalender abrufen
  - In `cts_events` Tabelle speichern
  - Deduplizierung nach ChurchTools-ID
  - Update bestehender Events
  - Alte Events löschen

---

## v0.3.8.0 - Sync-Button funktionsfähig
**Ziel:** Manuelle Synchronisation ermöglichen

### Features:
- [ ] AJAX Handler für manuellen Sync
  - ajax_sync_now in Admin-Klasse
  - Sync-Service aufrufen
  - Status zurückgeben
- [ ] Progress-Bar während Sync
  - Fortschritts-Anzeige
  - Anzahl synchronisierter Events
- [ ] Dashboard-Statistiken aktualisieren
  - Event-Count nach Sync
  - Calendar-Count
  - Letzter Sync-Zeitpunkt

---

## v0.3.9.0 - Admin UI: Events-Tab
**Ziel:** Synchronisierte Events im Admin anzeigen

### Features:
- [ ] Tab "Events" im Admin
  - Neuer Tab in admin-page.php
  - View: tab-events.php
- [ ] Event-Liste anzeigen
  - Tabelle mit allen Events
  - Titel, Datum, Kalender, Status
  - Pagination
- [ ] Filter-Optionen
  - Nach Kalender filtern
  - Nach Datum filtern
  - Suche nach Titel

---

## v0.3.10.0 - Event Services Repository
**Ziel:** Event-Services verwalten

### Features:
- [ ] Event Services Repository
  - Services zu Events zuordnen
  - Service-Details speichern
  - get_by_event(), get_by_service_id()

---

## v0.3.11.0 - Schedule Repository
**Ziel:** Mitarbeiter-Dienste verwalten

### Features:
- [ ] Schedule Repository
  - Schedule-Einträge (Mitarbeiter-Dienste)
  - Personen-Zuordnungen
  - get_by_event(), get_by_person()

---

## v0.3.12.0 - Template Loader
**Ziel:** Template-System vorbereiten

### Features:
- [ ] Template Loader (class-churchtools-suite-template-loader.php)
  - Template-System für Themes
  - Override-Mechanismus (Theme > Plugin)
  - Default-Templates
  - locate_template(), render_template()

---

## v0.3.13.0 - Shortcode Handler (Basis)
**Ziel:** [cts_events] Shortcode implementieren

### Features:
- [ ] Shortcode Handler (class-churchtools-suite-shortcodes.php)
  - [cts_events] - Event-Liste
  - Attribute: calendar, limit, order
  - Events Repository nutzen
  - Template Loader nutzen
- [ ] Basis-Template
  - templates/events/list.php - Einfache Listen-Ansicht
  - HTML-Struktur mit CSS-Klassen

---

## v0.3.14.0 - Frontend CSS/JS
**Ziel:** Styling für Frontend

### Features:
- [ ] Frontend CSS
  - public/css/churchtools-suite-public.css
  - Event-Liste Styling
  - Responsive Design
- [ ] Frontend JS (falls benötigt)
  - public/js/churchtools-suite-public.js
  - Interaktive Features

---

## v0.3.15.0 - Weitere Event-Templates
**Ziel:** Verschiedene Ansichten

### Features:
- [ ] Grid-Template
  - templates/events/grid.php - Kachel-Ansicht
- [ ] Compact-Template
  - templates/events/compact.php - Kompakte Liste
- [ ] Single-Template
  - templates/events/single.php - Einzelansicht

---

## v0.3.16.0 - Shortcode-Presets Repository
**Ziel:** Vordefinierte Konfigurationen

### Features:
- [ ] Shortcode Presets Repository
  - Presets speichern/laden
  - Name, Beschreibung, Konfiguration (JSON)
  - get_all(), get_by_id(), save(), delete()

---

## v0.3.17.0 - Preset-Manager UI
**Ziel:** Admin-Interface für Presets

### Features:
- [ ] Tab "Shortcodes" im Admin
  - View: tab-shortcodes.php
  - Liste aller Presets
- [ ] Preset-Editor
  - Formular für Preset-Erstellung
  - Attribute konfigurieren
  - Vorschau
- [ ] Shortcode-Generator
  - Generiert fertigen Shortcode
  - Copy-to-Clipboard Button

---

## v0.3.18.0 - Automatischer Sync (Einstellungen)
**Ziel:** Auto-Sync konfigurieren

### Features:
- [ ] Auto-Sync Settings UI
  - Checkbox aktivieren/deaktivieren
  - Intervall-Auswahl (30min, 1h, 2h, 6h, 12h, 24h)
  - Speichern in Options-Tabelle
- [ ] Validierung
  - Mindest-Intervall: 30 Minuten
  - Fehlermeldungen

---

## v0.3.19.0 - Sync Cron-Job
**Ziel:** Automatische Synchronisation

### Features:
- [ ] Sync Cron-Job implementieren
  - Neuer Cron-Hook: churchtools_suite_auto_sync
  - Ruft Calendar & Event Sync Services auf
  - Error-Handling und Logging
- [ ] Cron-Scheduling
  - Schedule bei Aktivierung/Settings-Änderung
  - Dynamisches Intervall
  - Clear bei Deaktivierung

---

## v0.3.20.0 - Sync-Historie
**Ziel:** Sync-Verlauf nachvollziehen

### Features:
- [ ] Sync-Historie im Dashboard
  - Letzter Sync-Zeitpunkt
  - Nächster geplanter Sync
  - Anzahl synchronisierter Items
- [ ] Sync-Log
  - Erfolg/Fehler-Status
  - Dauer des Syncs
  - Anzahl neuer/aktualisierter/gelöschter Events

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

**Stand:** Version 0.3.1.0 (11. Dezember 2025)
**Nächster Schritt:** v0.3.2.0 - Repository Base-Klasse
