# ChurchTools Suite

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

## Entwicklung

- **Version:** 0.3.5.1
- **PHP:** 8.0+
- **WordPress:** 6.0+

## Changelog

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
