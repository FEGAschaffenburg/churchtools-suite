# ChurchTools Suite

**Aktuelle Version:** 0.10.3.27

Professionelle WordPress-Integration für ChurchTools.

## Features

- 🗓️ Event-Synchronisation
- 📅 Kalender-Management
- 🔄 Automatische Updates
- 🎨 Modernes Admin-Interface
- 🔒 Sichere API-Kommunikation

## Installation

1. ZIP herunterladen
2. In WordPress unter Plugins → Installieren → Hochladen
3. Plugin aktivieren
4. ChurchTools-Zugangsdaten eingeben

## Demo-Modus (für Demo-Websites)

**Hinweis:** Dieser Modus ist nur für Demo-Websites gedacht und wird normalen Plugin-Benutzern nicht angezeigt.

### Was ist der Demo-Modus?

Der Demo-Modus ermöglicht es, das Plugin **ohne ChurchTools-API-Zugriff** zu nutzen. Perfekt für:
- Demo-Websites (z.B. https://plugin.feg-aschaffenburg.de)
- Präsentationen und Screenshots
- Entwicklung ohne ChurchTools-Instanz

### Aktivierung

Fügen Sie in Ihrer `wp-config.php` (vor `/* That's all, stop editing! */`) hinzu:

```php
/**
 * ChurchTools Suite - Demo Mode
 * Generiert fake Events ohne ChurchTools API
 */
define('CTS_DEMO_MODE', true);
```

### Was bietet der Demo-Modus?

- ✅ **6 Demo-Kalender** mit realistischen Namen und Farben
- ✅ **90 Tage Event-Daten** (wöchentliche Events + spezielle Termine)
- ✅ **9 Service-Typen** (Prediger, Moderation, Lobpreis, etc.)
- ✅ **Realistische deutsche Beschreibungen** für alle Events
- ✅ **Vollständige Adressen** (Straße, PLZ, Stadt, GPS-Koordinaten)
- ✅ **Farb-codierte Tags** für bessere Visualisierung

### Demo-Daten im Detail

**Kalender:**
1. Gottesdienste (blau)
2. Jugend (grün)
3. Kinder (gelb)
4. Musik (rot)
5. Kleingruppen (lila)
6. Gemeindeveranstaltungen (cyan)

**Wöchentliche Events:**
- Gottesdienst (Sonntags 10:00-11:30)
- Jugendabend (Freitags 19:00-21:00)
- Kindergottesdienst (Sonntags 10:00-11:30)
- Lobpreis-Probe (Donnerstags 20:00-21:30)
- Hauskreis (Mittwochs 19:30-21:30)

**Spezielle Events:**
- Gemeindefest (+30 Tage)
- Alpha-Kurs Startabend (+14 Tage)

### Deaktivierung

Entfernen Sie die Zeile aus `wp-config.php` oder setzen Sie:

```php
define('CTS_DEMO_MODE', false);
```

**Wichtig:** Normalen Plugin-Benutzern wird diese Option nicht angezeigt. Der Demo-Modus ist ausschließlich über die `wp-config.php` steuerbar.

## Entwicklung

- **Version:** 0.9.2.24
- **PHP:** 8.0+
- **WordPress:** 6.0+

## Updater

Das Plugin enthält einen optionalen GitHub-basierten Updater. Details zur Konfiguration (PAT, Option `churchtools_suite_github_token`, `WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN`, Verhalten von "Manuelles Update prüfen" vs. "Update ausführen", Rollback) stehen in der Dokumentation: `docs/UPDATER.md`.

## Gutenberg Integration

ChurchTools Suite unterstützt jetzt den Block-Editor (Gutenberg)!

### Verfügbare Blocks

- **ChurchTools Calendar** - Kalender-Ansichten (monthly-modern, monthly-clean, etc.)
- **ChurchTools List** - Listen-Ansichten (classic, modern, minimal, fluent)
- **ChurchTools Grid** - Raster-Ansichten (simple, modern, colorful)

### Verwendung

1. Block-Editor öffnen
2. Block hinzufügen (+)
3. Nach "ChurchTools" suchen
4. Block auswählen
5. Einstellungen in der Sidebar anpassen:
   - View auswählen
   - Kalender IDs eingeben (kommagetrennt)
   - Limit setzen
   - Spalten konfigurieren (nur Grid)

Alle Blocks nutzen Server-Side Rendering und greifen auf die gleichen Templates wie die Shortcodes zu.

## Shortcodes

Alternativ können alle Funktionen auch über Shortcodes verwendet werden. Der **Shortcode Manager** (ChurchTools → ⚡ Shortcode Manager) zeigt eine übersichtliche Liste aller 13 verfügbaren Shortcodes mit Filterfunktion und Copy-to-Clipboard.

## Changelog

### 0.5.8.0 - Gutenberg Block Integration
- **NEU:** Gutenberg-Support für ChurchTools Shortcodes
- **NEU:** Custom Block Category "ChurchTools Suite" im Block-Editor
- **NEU:** Calendar Block mit 8 View-Varianten
- **NEU:** List Block mit 4 View-Varianten
- **NEU:** Grid Block mit 3 View-Varianten
- **NEU:** InspectorControls für Block-Einstellungen (View, Calendar, Limit, Columns)
- **NEU:** Block-Preview im Editor mit Icons
- **NEU:** Server-Side Rendering nutzt existierende Shortcode-Templates
- Blocks erscheinen in Block-Inserter unter "ChurchTools Suite"
- Keine Duplikation von Template-Logik - Blocks nutzen Shortcode-System

### 0.5.7.2 - Shortcode Manager Filterung
- **NEU:** Shortcode Manager mit 13 Shortcodes auf einen Blick
- **NEU:** Filter nach Text (Tag/Name-Suche) und Kategorie
- **NEU:** Expandable Details für Views und Parameter
- **NEU:** Copy-to-Clipboard für jeden Shortcode
- **NEU:** Live-Counter zeigt gefilterte Shortcodes
- **NEU:** Freundliche "Keine Ergebnisse" Meldung
- Vanilla JavaScript ohne jQuery-Abhängigkeit

### 0.5.7.1 - Shortcode Manager Subpage
- **NEU:** Shortcode Manager als eigene Admin-Subpage
- **NEU:** Grid-Layout zeigt alle 13 Shortcodes mit Metadaten
- **NEU:** Icons, Namen, Beschreibungen, Beispiel-Code
- **NEU:** Responsive Design für alle Bildschirmgrößen
- Menü-Eintrag: ChurchTools → ⚡ Shortcode Manager

### 0.3.11.2 - Bugfix: Services API-Endpunkt
- **FIX:** Service Sync verwendet jetzt korrekten API-Endpunkt `/api/services` statt `/api/masterdata/serviceGroups`
- **FIX:** Service-Struktur angepasst - verwendet `serviceGroupId` aus API
- Behebt "API-Fehler (HTTP 404): Not found" beim Services-Sync
- Services werden jetzt korrekt von ChurchTools geladen

### 0.3.11.1 - Bugfix: Repository Table Prefix
- **FIX:** Services Repository verwendet jetzt korrektes Tabellenpräfix (CHURCHTOOLS_SUITE_DB_PREFIX)
- **FIX:** Event Services Repository verwendet jetzt korrektes Tabellenpräfix
- Behebt "Table 'wp_services' doesn't exist" Fehler
- Korrekte Tabellennamen: wp_cts_services, wp_cts_event_services

### 0.3.11.0 - Services-Tabelle & Auswahl-UI
- **NEU:** Migration 1.3 erstellt `wp_cts_services` Tabelle automatisch
- **NEU:** Services Repository für ChurchTools Service Master Data
- **NEU:** Service Sync Service - Synchronisiert Services von `/api/masterdata/serviceGroups`
- **NEU:** Admin Tab "Services" mit Service-Auswahl (wie Kalender-Auswahl)
- **NEU:** Service-Auswahl UI mit Checkboxes für jedes Service
- **NEU:** AJAX Handler `cts_sync_services` und `cts_save_service_selection`
- **NEU:** JavaScript Handler für Service-Sync und Auswahl-Speicherung
- **NEU:** Services werden von ChurchTools geladen mit Service-Groups
- **NEU:** `get_selected()`, `update_selection()`, `get_selected_service_ids()` Methoden
- Benutzer können auswählen welche Services bei Event-Sync importiert werden
- Grundlage für Event Services Import in nächster Version

### 0.3.10.0 - Event Services Repository
- **NEU:** Event Services Repository für Verwaltung von Event-Services
- **NEU:** `upsert()` Methode - Insert/Update Services mit event_id + service_name als Natural Key
- **NEU:** `get_for_event($event_id)` - Alle Services für ein Event abrufen
- **NEU:** `delete_for_event($event_id)` - Services löschen bei Event-Delete
- **NEU:** `get_unique_service_names()` - Liste aller importierten Service-Namen
- **NEU:** `get_service_stats()` - Statistiken über Service-Nutzung
- **NEU:** `search_by_person()` - Services nach Person durchsuchen
- Repository nutzt bestehende `wp_cts_event_services` Tabelle (seit Migration 1.0)
- Grundlage für Event Services Import in zukünftigen Versionen
- Vollständige CRUD-Operationen für Services-Daten

### 0.3.9.4 - Manueller Cron-Trigger
- **NEU:** Manueller Trigger-Button für Event-Sync im Debug-Tab
- **NEU:** Manueller Trigger-Button für Session Keepalive im Debug-Tab
- **NEU:** AJAX-Handler `cts_trigger_manual_sync` und `cts_trigger_keepalive`
- **NEU:** Sofortige Ausführung von Cron-Jobs ohne Warten auf Schedule
- **NEU:** Manuell getriggerte Syncs werden als 'manual' in Historie gespeichert
- Sofortiges Feedback mit Erfolgs-/Fehler-Meldung nach Ausführung
- Seiten-Reload nach erfolgreichem Sync zeigt aktualisierte Statistiken
- Ideal für Testing und sofortige Synchronisation

### 0.3.9.3 - Sync-Historie Tabelle
- **NEU:** Sync-Historie wird in eigener Tabelle `wp_cts_sync_history` gespeichert
- **NEU:** Migration 1.2 erstellt sync_history Tabelle automatisch
- **NEU:** Repository `ChurchTools_Suite_Sync_History_Repository` mit CRUD-Operationen
- **NEU:** Debug-Tab zeigt letzte 10 Syncs in übersichtlicher Tabelle
- **NEU:** Historie-Tracking: Sync-Typ (auto/manuell), Status, Statistiken, Fehler, Dauer
- **NEU:** Automatisches Cleanup alter Sync-Einträge (>90 Tage)
- **NEU:** get_recent(), get_by_type(), get_failed(), get_stats_summary() Methoden
- Cron schreibt jeden Sync in Historie (Start + Completion)
- Vollständige Nachvollziehbarkeit aller Sync-Operationen
- Fehlermeldungen werden direkt unter fehlgeschlagenen Syncs angezeigt

### 0.3.9.2 - Erweitertes Error-Handling
- **NEU:** Try-Catch Block um Auto-Sync mit detailliertem Error-Tracking
- **NEU:** Fehler werden in wp_options gespeichert und persistent angezeigt
- **NEU:** Dashboard zeigt Sync-Status (Erfolg/Fehler) mit Details
- **NEU:** Debug-Tab zeigt letzte Sync-Statistiken und Fehler
- **NEU:** Success-Tracking mit vollständigen Stats (neu/aktualisiert/übersprungen)
- **NEU:** Sync-Dauer wird gemessen und angezeigt
- Stack Trace im WP_DEBUG Modus für detailliertes Debugging
- Error-Logs mit Zeitstempel und Fehlermeldung
- Automatische Fehler-Löschung bei erfolgreichem Sync

### 0.3.9.1 - Tages-Intervalle & WP-Cron Detection
- **ÄNDERUNG:** Sync-Intervalle auf Tage umgestellt (statt Stunden)
- **NEU:** Täglich (empfohlen), alle 2 Tage, alle 3 Tage
- **NEU:** Wöchentlich, alle 2 Wochen, monatlich
- **NEU:** WP-Cron Disabled Detection mit Warnung
- **NEU:** System-Cron Anleitung bei deaktiviertem WP-Cron
- Serverfreundlich: Längere Intervalle reduzieren Server-Last
- Passend für Events: Tägliche Updates sind ausreichend
- Settings & Dashboard zeigen WP-Cron Status

### 0.3.9.0 - Custom Cron-Intervalle (deprecated)
- Custom Cron-Intervalle implementiert (ersetzt durch v0.3.9.1)

### 0.3.8.4 - Fix Timezone-Konvertierung
- **Fix:** Termine-Übersicht zeigt jetzt lokale Zeit statt UTC
- Verwendet get_date_from_gmt() für UTC → Lokale Zeit Konvertierung
- Berücksichtigt WordPress-Zeitzone (Settings → General → Timezone)
- Start- und Enddatum werden korrekt in lokale Zeitzone umgerechnet
- Beispiel: UTC 13:00 → Berlin 14:00 (im Winter) / 15:00 (im Sommer)

### 0.3.8.3 - WordPress Datum/Zeit-Formate
- **NEU:** Verwendet WordPress-Einstellungen für Datum/Zeit-Formate
- **Settings → General → Date Format** wird verwendet
- **Settings → General → Time Format** wird verwendet
- Tab Termine: Datum/Zeit aus WordPress-Einstellungen
- Tab Sync: Letzte Sync-Timestamps aus WordPress-Einstellungen
- Tab Settings: Letzte Auto-Sync aus WordPress-Einstellungen
- Tab Dashboard: Letzter Login aus WordPress-Einstellungen
- Filter-Anzeige: Datum aus WordPress-Einstellungen
- Timezone-aware: Verwendet WordPress-Timezone
- Dynamisch: Ändert sich automatisch mit WordPress-Einstellungen

### 0.3.8.2 - KRITISCHER Bugfix: appointmentId Sammlung
- **KRITISCH:** Fix appointmentId Sammlung in Phase 1
- **KRITISCH:** API verwendet event.appointmentId (NICHT event.appointment.id)
- Fix: Phase 2 überspringt jetzt korrekt bereits importierte Appointments
- Fix: Keine Duplikate mehr bei Events mit Appointments
- Debug: Phase 2 Logging - zeigt "already imported in Phase 1" Count
- Debug: Phase 1 Logging - zeigt gesammelte appointment_ids
- Basiert auf echter ChurchTools API-Struktur

### 0.3.8.1 - Bugfix Event Sync Phase 1
- **Fix:** Erweiterte Kalender-Erkennung für Events (Phase 1)
- **Fix:** Prüfe exists_by_event_id VOR upsert (korrekte Statistik)
- **Debug:** Erweiterte Logging für Event-Strukturen
- **Debug:** Zeige Phase 1/2 Statistiken im error_log
- Kalender-Filterung jetzt mit 8 Fallback-Checks
- Unterstützt: appointments[].calendar, base.calendar, calendarId
- WP_DEBUG: Loggt Event-Struktur für Analyse

### 0.3.8.0 - Termine-Übersicht & Auto-Sync
- **NEU:** Tab "Termine" mit kompletter Übersichtstabelle
- **NEU:** Filter nach Datum und Kalender (von/bis)
- **NEU:** Pagination (50 Termine pro Seite)
- **NEU:** Automatische Synchronisation per Cron-Job
- **NEU:** Auto-Sync Einstellungen (stündlich/täglich)
- **NEU:** Auto-Sync Ein/Aus Toggle in Settings
- Anzeige: Event-Typ (Event vs. Appointment)
- Anzeige: Kalender-Badge mit Farbe
- Anzeige: Ort, Datum, Zeit, Beschreibung
- Cron: churchtools_suite_auto_sync Hook
- UI: Moderne Tabelle mit Hover-Effekten
- UI: Filter-Sektion mit aktiven Filtern
- UI: Toggle-Switch für Auto-Sync

### 0.3.7.4 - Versioniertes Migrations-System
- **NEU:** Automatisches Migrations-System mit DB-Versionierung
- Migrationen laufen jetzt bei **jedem Plugin-Update** (nicht nur bei Aktivierung)
- DB-Version wird in wp_options gespeichert (churchtools_suite_db_version)
- Migrations-Klasse: class-churchtools-suite-migrations.php
- Migration 1.0: Initiale Tabellen-Erstellung
- Migration 1.1: Event Sync Schema (external_id → event_id, appointment_id, raw_payload)
- Idempotent: Jede Migration kann mehrfach ausgeführt werden
- Automatisch: Läuft bei jedem Plugin-Init

### 0.3.7.3 - Automatische Datenbank-Migration
- **WICHTIG:** Automatische Schema-Migration beim Aktivieren
- Fix: Datenbank-Fehler "Unknown column 'event_id'"
- Fix: Datenbank-Fehler "Unknown column 'appointment_id'"
- Migriert automatisch: external_id → event_id
- Fügt automatisch hinzu: appointment_id, raw_payload Spalten
- Lösung: Plugin deaktivieren und neu aktivieren
- MIGRATION-GUIDE.md mit manuellen Anweisungen
- update-events-table.sql für manuelle Migration

### 0.3.7.2 - Bugfix & UI-Konsolidierung
- Fix: Kritischer Syntax-Fehler in Event Sync Service (fehlende Array-Klammer)
- UI: Alle Sync-Funktionen im "Sync"-Tab konsolidiert
- Kalender-Tab: Fokus auf Kalenderauswahl
- Sync-Tab: Kalender-Sync, Termin-Sync und Hinweise an einem Ort
- Bessere Validierung: Warnung wenn keine Kalender ausgewählt

### 0.3.7.1 - Konfigurierbare Sync-Zeiträume
- Neue Einstellungen: Vergangene Tage (Standard: 7)
- Neue Einstellungen: Zukünftige Tage (Standard: 90)
- Zeitraum-Vorgaben werden automatisch beim Sync verwendet
- Anzeige des konfigurierten Zeitraums im Kalender-Tab
- Validierung: Min/Max-Werte für Zeiträume

### 0.3.7.0 - Event Sync Service
- ChurchTools_Suite_Event_Sync_Service implementiert
- 2-Phasen-Synchronisation:
  - Phase 1: Events API - Events mit ihren Appointments (1:N)
  - Phase 2: Appointments API - Standalone Appointments ohne Events
- Appointments ohne Event nutzen Appointmentdaten
- Appointments mit Events nutzen Eventdaten (1:X)
- sync_events() mit konfigurierbarem Datumsbereich
- Intelligente Kalender-Validierung (6 Fallbacks für Events, 3 für Appointments)
- Duplikats-Prävention via appointment_id-Tracking
- Admin UI: "Termine synchronisieren" Button im Kalender-Tab
- AJAX Handler: cts_sync_events mit Statistiken

### 0.3.6.0 - Events Repository
- ChurchTools_Suite_Events_Repository implementiert
- upsert_by_event_id() für Insert/Update-Logik
- get_by_event_id() und get_by_appointment_id()
- get_by_calendar_id() für Kalender-Events
- get_upcoming() für kommende Termine
- get_in_range() für Datumsbereich-Abfragen
- delete_older_than() für Aufräumen alter Termine
- Tabellen-Schema: event_id, appointment_id, raw_payload

### 0.3.5.3 - Bugfix
- Fix: Tabellen-Schema korrigiert (calendar_id statt external_id)
- Fix: name_translated und raw_payload Spalten hinzugefügt
- WICHTIG: Plugin deaktivieren und neu aktivieren!

### 0.3.5.2 - Bugfix
- Fix: AJAX Handler mit Try-Catch für bessere Fehlerbehandlung
- Fix: Return-Statements nach wp_send_json_error hinzugefügt
- Bessere Fehlermeldungen bei AJAX-Aufrufen

### 0.3.5.1 - Bugfix
- Fix: AJAX Handler werden jetzt direkt registriert (nicht mehr mit init Hook)
- Behebt "Unexpected token '<'" Fehler bei AJAX-Aufrufen

### 0.3.5.0 - Admin UI Kalender-Tab
- Neuer Tab "Kalender" im Admin-Bereich
- "Kalender synchronisieren" Button mit AJAX
- Kalender-Übersicht mit Tabelle (Name, ID, Sichtbarkeit, Farbe)
- Checkboxen für Kalenderauswahl (is_selected)
- "Alle auswählen" Checkbox
- "Auswahl speichern" mit AJAX
- Letzte Sync-Zeit wird angezeigt
- Seite lädt nach erfolgreicher Sync neu
- AJAX Handler: ajax_sync_calendars, ajax_save_calendar_selection
- JavaScript für alle Interaktionen ohne jQuery

### 0.3.4.0 - Calendar Sync Service
- Calendar Sync Service für Kalender-Synchronisation
- sync_calendars() lädt alle Kalender aus ChurchTools
- import_calendar() speichert einzelne Kalender mit upsert
- fetch_calendar() für einzelne Kalender
- is_sync_needed() prüft ob Sync nötig (>1h alt)
- Neue Kalender: is_selected = is_public (Standard)
- Sync-Zeitstempel wird in Options gespeichert

### 0.3.3.0 - Calendars Repository
- Calendars Repository mit upsert_by_calendar_id
- get_selected() für ausgewählte Kalender
- get_selected_ids() und get_selected_calendar_ids()
- set_selected() und update_selected() für Bulk-Operationen
- is_selected-Flag bleibt bei Updates erhalten (User-Einstellung)

### 0.3.2.0 - Repository Base-Klasse
- Abstrakte Basis-Klasse für alle Repositories
- CRUD-Operationen: get_by_id, insert, update_by_id, delete_by_id
- get_all mit Order und Limit
- get_where für flexible WHERE-Queries
- Automatische Timestamps (created_at, updated_at)
- count, exists, delete_all, truncate Helfer
- Zentrale Fehlerbehandlung mit get_last_error
- Grundlage für Calendars, Events, Services Repositories

### 0.3.1.0 - Cookie-Management & Session Keep-Alive
- Cookie-Ablauf-Prüfung implementiert
- Automatisches Re-Login bei abgelaufenen Cookies
- Stündlicher Cron-Job für Session Keep-Alive
- whoami API-Call hält Session aktiv
- is_authenticated() prüft Cookie-Expires-Datum
- Error-Logging für Cron-Jobs

### 0.3.0.0 - ChurchTools Login & API Client
- ChurchTools API Client Klasse implementiert
- Login mit Username/Passwort und Token-Speicherung
- Verbindung testen Button in Einstellungen
- Dashboard zeigt echten Verbindungsstatus
- Token wird in WordPress-Options gespeichert
- Automatisches Re-Login bei 401 Errors
- User-Info nach erfolgreichem Login anzeigen

### 0.2.3.1 - Tenant-Name Eingabe
- Nur Tenant-Name statt vollständiger URL
- Eingabefeld zeigt: https://[tenant].church.tools
- Automatische URL-Generierung
- Vereinfachte Konfiguration

### 0.2.3.0 - Boxed Layout & Kompaktes Design
- Boxed Layout statt volle Breite
- Kompakteres, klareres Design
- 3-Spalten-Grid für Cards
- Reduzierte Paddings und Margins
- Einfachere Buttons ohne Schatten
- Kleinere Stat-Numbers (36px)
- System-Card in Grid integriert
- Max-Width 700px für Einzelkarten
- Saubere, übersichtliche Optik

### 0.2.2.1 - Design-Verfeinerungen
- Verfeinerte Typografie (größere Überschriften, bessere Schriftgewichte)
- Verbesserte Card-Shadows und Hover-Effekte
- Größere Status-Indikatoren mit Glow-Effekt
- Optimierte Button-Styles mit Schatten
- Besseres Spacing (Grid, Padding, Margins)
- Icon-Hintergrund im Header
- Stat-Numbers in Blau mit 48px Größe
- Subtile Animationen

### 0.2.2.0 - Design wie Original-Plugin
- Dashboard mit Section-Header und Beschreibung
- Cards mit Header, Body, Footer-Struktur
- System-Info-Card auf Dashboard
- Status-Indikatoren (grün/rot/grau)
- Buttons im Original-WordPress-Style
- Komplett überarbeitetes CSS (540 Zeilen)
- Design orientiert am alten repro-ct-suite Plugin

### 0.2.1.1 - Cleanup
- Gelöscht: Shortcode-Manager CSS/JS
- Gelöscht: Gutenberg-Block JS
- Gelöscht: Debug JS (nicht mehr benötigt)
- Gelöscht: Backup-Dateien
- Nur noch essenzielle Dateien: churchtools-suite-admin.css/js, churchtools-suite-modal.css

### 0.2.1.0 - Eigenständiges Design
- Komplett eigenes CSS ohne WordPress-Abhängigkeiten
- Vanilla JavaScript statt jQuery
- Emoji-Icons statt Dashicons
- Eigene Form-Styles und Tabellen
- Responsive Design
- Bessere Accessibility

### 0.2.0.6 - Cache Busting
- Version erhöht für CSS Cache Refresh

### 0.2.0.5 - ZIP Script Improvement
- Script verschiebt ALLE alten ZIPs ins Archiv (nicht nur gleiche Version)
- Bessere Archivierung vor neuer ZIP-Erstellung

### 0.2.0.4 - Enhanced Design
- Gradient Header wie im alten Plugin
- Verbesserte Cards mit Hover-Effekten
- Modernere Tab-Navigation
- Schönere Farben und Abstände

### 0.2.0.3 - Login mit Benutzername/Passwort
- Settings: Benutzername (E-Mail) und Passwort statt API Token
- Kompatibel mit alter Plugin-Logik

### 0.2.0.1 - Code Cleanup
- Alle "repro-" Präfixe entfernt
- CSS-Klassen bereinigt: .cts-* statt .repro-ct-suite-*
- Package-Namen aktualisiert

### 0.2.0.0 - Modern Admin UI
- Clean & Modern Admin Design
- Dashboard mit Status Cards
- Settings-Formular
- Sync-Interface mit Progress
- Debug-Informationen
- Responsive Grid Layout

### 0.1.0.0 - Initial Development
- Projektstart mit sauberer Code-Basis
- PHP 8.0+ mit modernen Features (Union Types, Named Arguments)

### Roadmap → 1.0.0
- 0.1.0: Grundstruktur & Assets
- 0.2.0: Core-Klassen & DB
- 0.3.0: Repositories
- 0.4.0: Sync-Services
- 0.5.0: Admin-UI
- 1.0.0: Production Release
- Saubere Code-Basis
- Neue DB-Struktur (wp_cts_*)
- Modernes Admin-Design
- GitHub-Update-System
