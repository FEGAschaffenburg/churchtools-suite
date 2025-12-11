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

- **Version:** 0.2.1.0
- **PHP:** 8.0+
- **WordPress:** 6.0+

## Changelog

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
