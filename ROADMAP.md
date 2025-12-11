# ChurchTools Suite - Roadmap

## Aktueller Stand: v0.3.1.0
✅ Cookie-basierte Authentifizierung mit Session Keep-Alive

---

## v0.4.0.0 - Kalender-Synchronisation
**Ziel:** Kalender aus ChurchTools abrufen und lokal speichern

### Features:
- [ ] Repository Base-Klasse (class-churchtools-suite-repository-base.php)
  - Gemeinsame Basis für alle Repositories
  - CRUD-Operationen
  - DB-Query-Helfer

- [ ] Calendars Repository (class-churchtools-suite-calendars-repository.php)
  - Kalender aus DB lesen/schreiben
  - is_selected Flag für Auswahl
  - Kalender-Sync-Status

- [ ] Calendar Sync Service (class-churchtools-suite-calendar-sync-service.php)
  - API-Call zu `/calendars`
  - ChurchTools-Kalender abrufen
  - In `cts_calendars` Tabelle speichern
  - Sync-Timestamp verwalten

- [ ] Admin UI für Kalender-Auswahl
  - Tab "Kalender" im Admin
  - Liste aller verfügbaren Kalender
  - Checkboxen für Auswahl
  - "Kalender synchronisieren" Button

---

## v0.4.1.0 - Event-Synchronisation (Basis)
**Ziel:** Events/Termine aus ausgewählten Kalendern abrufen

### Features:
- [ ] Events Repository (class-churchtools-suite-events-repository.php)
  - Events aus DB lesen/schreiben
  - Suche/Filter-Funktionen
  - Bulk-Operations

- [ ] Event Sync Service (class-churchtools-suite-sync-service.php)
  - API-Call zu `/calendars/{id}/appointments`
  - Termine für ausgewählte Kalender abrufen
  - In `cts_events` Tabelle speichern
  - Deduplizierung nach ChurchTools-ID

- [ ] Sync-Button funktionsfähig machen
  - AJAX Handler für manuellen Sync
  - Progress-Bar während Sync
  - Erfolgs-/Fehlermeldungen
  - Aktualisierung der Dashboard-Statistiken

---

## v0.4.2.0 - Event-Services & Schedule
**Ziel:** Event-Services und Schedule-Daten

### Features:
- [ ] Event Services Repository
  - Services zu Events zuordnen
  - Service-Details speichern

- [ ] Schedule Repository
  - Schedule-Einträge (Mitarbeiter-Dienste)
  - Personen-Zuordnungen

- [ ] Erweiterte Event-Daten
  - Beschreibung, Notizen
  - Bilder/Anhänge
  - Custom Fields

---

## v0.5.0.0 - Frontend: Shortcodes & Templates
**Ziel:** Events im Frontend anzeigen

### Features:
- [ ] Template Loader (class-churchtools-suite-template-loader.php)
  - Template-System für Themes
  - Override-Mechanismus
  - Default-Templates

- [ ] Shortcode Handler (class-churchtools-suite-shortcodes.php)
  - [cts_events] - Event-Liste
  - [cts_calendar] - Kalender-Ansicht
  - Attribute für Filter (kalender, datum, anzahl, etc.)

- [ ] Event-Templates erstellen
  - templates/events/list.php - Listen-Ansicht
  - templates/events/grid.php - Grid-Ansicht
  - templates/events/single.php - Einzelansicht
  - templates/events/calendar.php - Kalender-Ansicht

- [ ] CSS/JS für Frontend
  - public/css/churchtools-suite-public.css
  - public/js/churchtools-suite-public.js
  - Responsive Design

---

## v0.5.1.0 - Shortcode-Presets
**Ziel:** Vordefinierte Shortcode-Konfigurationen

### Features:
- [ ] Shortcode Presets Repository
  - Presets speichern/laden
  - Name, Beschreibung, Konfiguration

- [ ] Preset-Manager UI
  - Presets erstellen/bearbeiten/löschen
  - Shortcode-Generator
  - Copy-to-Clipboard

- [ ] Preset-Attribute
  - Layout (list/grid/calendar)
  - Anzahl Events
  - Zeitraum-Filter
  - Kalender-Filter

---

## v0.6.0.0 - Automatischer Sync
**Ziel:** Automatische Synchronisation im Hintergrund

### Features:
- [ ] Auto-Sync Einstellungen
  - Sync aktivieren/deaktivieren
  - Intervall konfigurieren (Minuten/Stunden)
  - Mindest-Intervall: 30 Minuten

- [ ] Sync Cron-Job
  - Automatische Synchronisation
  - Error-Handling
  - Retry-Logik

- [ ] Sync-Status auf Dashboard
  - Letzter Sync-Zeitpunkt
  - Nächster geplanter Sync
  - Sync-Historie

---

## v0.6.1.0 - Rate Limiting & Security
**Ziel:** API-Schutz und Sicherheit

### Features:
- [ ] Rate Limiter (class-churchtools-suite-rate-limiter.php)
  - Request-Limits pro Zeiteinheit
  - Schutz vor API-Überlastung
  - Automatische Throttling

- [ ] Input Validator (class-churchtools-suite-input-validator.php)
  - Validierung von Formulareingaben
  - Sanitization-Helfer
  - XSS-Schutz

- [ ] Crypto Helper (class-churchtools-suite-crypto.php)
  - Passwort-Verschlüsselung in DB
  - Secure Storage für Credentials

---

## v0.7.0.0 - Logger & Debugging
**Ziel:** Fehlersuche und Monitoring

### Features:
- [ ] Logger (class-churchtools-suite-logger.php)
  - Log-Levels (error, warning, info, debug)
  - Log-Rotation
  - Log-Viewer im Admin

- [ ] Debug-Modus
  - Detaillierte API-Logs
  - SQL-Query-Logs
  - Performance-Monitoring

- [ ] Admin Debug-Tab
  - System-Informationen
  - API-Test-Tool
  - Datenbank-Browser
  - Log-Viewer

---

## v0.7.1.0 - Updater & Migrationen
**Ziel:** Plugin-Updates und DB-Migrationen

### Features:
- [ ] Updater (class-churchtools-suite-updater.php)
  - Versions-Prüfung
  - Update-Benachrichtigungen
  - Automatische DB-Migrationen

- [ ] Migrations-System
  - Versionierte Migrationen
  - Rollback-Funktionalität
  - Migration-Status-Tracking

---

## v0.8.0.0 - Internationalisierung
**Ziel:** Mehrsprachigkeit

### Features:
- [ ] i18n Setup (class-churchtools-suite-i18n.php)
  - Text-Domain laden
  - POT-Datei generieren
  - Deutsche Übersetzung

- [ ] Frontend-Texte übersetzen
  - Alle Template-Strings
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

**Stand:** Version 0.3.1.0 (11. Dezember 2025)
**Nächster Schritt:** v0.4.0.0 - Kalender-Synchronisation
