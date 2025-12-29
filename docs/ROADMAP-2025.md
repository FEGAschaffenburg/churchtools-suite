# ChurchTools Suite - Roadmap 2025

**Aktueller Stand:** v0.6.5.19 (18. Dezember 2025)  
**Status:** Production-Ready für Basis-Features  
**Nächstes Major Release:** v0.7.0.0 - Sync-Optimierungen

---

## ✅ Abgeschlossen: v0.1.0 - v0.6.5.19

### Phase 1: Backend Foundation (v0.3.x)
- ✅ Cookie-basierte ChurchTools API-Authentifizierung
- ✅ Repository Pattern (Base, Calendars, Events, Services, Service Groups)
- ✅ 2-Phasen Event Sync (Events API + Appointments API)
- ✅ Migration System (DB v1.0 - v1.6)
- ✅ Admin UI (Dashboard, Settings, Calendars, Events, Services, Sync, Debug)
- ✅ AJAX Handlers für manuellen Sync
- ✅ WP-Cron Integration mit Detection & Fallback
- ✅ Sync-Historie & Error-Tracking
- ✅ Service Groups & Services Selection (2-Step Workflow)
- ✅ Event Services Import mit Person-Zuordnung

### Phase 2: Frontend Framework (v0.4.x - v0.5.x)
- ✅ Template Loader System (Theme Override Support)
- ✅ Shortcode Handler (13 Shortcode-Typen definiert)
- ✅ Template Data Provider Service
- ✅ Frontend CSS/JS mit Conditional Loading
- ✅ Shortcode Manager (Admin-Subpage)
- ✅ List Templates: Classic, Medium
- ✅ Grid Template: Simple
- ✅ Calendar Template: Monthly-Modern
- ✅ Modal Template: Default

### Phase 3: Editor Integration (v0.5.8.x - v0.5.9.x)
- ✅ Gutenberg Block (Unified Block mit View-Selektor)
- ✅ Elementor Widget (6 Collapsible Sections)
- ✅ Preset-System (Standard + Custom Presets)
- ✅ REST API Endpoints (/calendars, /presets)
- ✅ Live Preview im Editor

### Phase 4: UI-Verbesserungen (v0.6.x)
- ✅ Sprint 1-4 Parameter implementiert (9 neue Parameter)
  - show_description, show_location, show_services
  - show_calendar_name, show_time
  - order, date_from, date_to, columns
- ✅ Collapsible Panels (Gutenberg, Elementor, Shortcode Manager)
- ✅ Kalender-Auswahl mit Checkboxen (statt Textfeld)
- ✅ Select-Options (Array + Objekt Support)
- ✅ Initial Load Fix für Preset-Parameter
- ✅ Shortcode Presets Repository (Migration 1.6)

---

## 🚀 Phase 5: Sync-Optimierungen (v0.7.0.0) - Q1 2026

**Ziel:** Schnellere, intelligentere Synchronisation mit ChurchTools

### v0.7.0.1 - Vereinfachte Einstellungen ⭐ QUICK WIN
**Problem:** Admin-UI ist überladen, zu viele Klicks für einfache Aufgaben

**Lösung:**
- [ ] Setup-Wizard für Erst-Konfiguration
  - 3-Schritt-Prozess: ChurchTools verbinden → Kalender wählen → Services wählen
  - Auto-Test nach jedem Schritt
  - Quick-Start-Guide mit Video-Tutorial
- [ ] Smart-Defaults
  - Auto-Sync standardmäßig aktiviert (täglich)
  - Alle Kalender automatisch ausgewählt
  - Häufig genutzte Services vorausgewählt
- [ ] One-Click-Actions im Dashboard
  - "Jetzt synchronisieren" Button prominent
  - Status-Kacheln mit direkten Links
  - Schnellzugriff auf letzte Sync-Logs
- [ ] Vereinfachte Service-Auswahl
  - Service-Gruppen ausblendbar (für Einsteiger)
  - "Alle auswählen" / "Alle abwählen" Buttons
  - Suche/Filter in Service-Liste

**Erwartete UX:**
- Setup in <5 Minuten statt 15-20 Minuten
- Weniger Rückfragen im Support

---

### v0.7.0.2 - Erweiterte Event-Daten aus JSON ⭐ PRIORITÄT
**Problem:** Viele Felder aus ChurchTools API-Response werden ignoriert

**Lösung:**
- [ ] Neue Event-Felder in DB speichern
  - `note` (Interne Notizen)
  - `information` (HTML-Beschreibung)
  - `category` (Event-Kategorie)
  - `image_url` (Event-Bild URL)
  - `link` (Externer Link)
  - `cost` (Kosten/Eintritt)
  - `signup_required` (Anmeldung erforderlich)
  - `max_participants` (Max. Teilnehmer)
  - `contact_person` (Ansprechpartner)
- [ ] Migration 1.8: Neue Spalten hinzufügen
- [ ] Events Repository erweitern
  - Getter/Setter für neue Felder
  - Filterung nach Kategorien
- [ ] Template Data Provider erweitern
  - Neue Felder in Events-Array
  - Helper für Image-URLs
- [ ] Templates anpassen
  - Event-Bilder in List/Grid Views
  - Kategorien als Tags
  - Anmelde-Button bei signup_required

**Erwartete Features:**
- Reichere Event-Darstellung
- Kategorien-Filter möglich
- Event-Bilder im Frontend

---

### v0.7.0.3 - Admin-UI für Events & Services ⭐ PRIORITÄT
**Problem:** Importierte Daten nur als Tabelle, schwer durchsuchbar

**Lösung:**
- [ ] Events-Tab: Card-Layout statt Tabelle
  - Große Event-Cards mit Bild (wenn vorhanden)
  - Kalender-Farbe als Akzent
  - Datum prominent
  - Services als Chips/Pills
  - Quick-Actions: Bearbeiten, Löschen, Vorschau
- [ ] Filter & Suche
  - Nach Kalender filtern
  - Nach Datum-Range filtern
  - Nach Services filtern
  - Volltextsuche in Titel/Beschreibung
- [ ] Pagination & Lazy Loading
  - 20 Events pro Seite
  - Infinite Scroll als Option
- [ ] Services-Tab: Kompakte Liste
  - Service-Name + Anzahl Events
  - Person-Zuordnungen expandable
  - Farbcodierung nach Service-Gruppe
- [ ] Bulk-Actions
  - Mehrere Events löschen
  - Events neu synchronisieren
  - Services neu zuordnen

**Erwartete UX:**
- Übersichtlichere Darstellung
- Schnellere Navigation
- Bessere Filterung

---

### v0.7.0.4 - Weitere Sync-Datenquellen ⭐ FEATURE REQUEST
**Problem:** Nur Events werden synchronisiert, andere ChurchTools-Daten fehlen

**Lösung:**
- [ ] Gruppen (Groups) synchronisieren
  - `/api/groups` Endpoint
  - Groups Repository + Migration 1.9
  - Gruppen-Auswahl im Admin
  - Shortcode: [cts_groups]
- [ ] Personen (Persons) synchronisieren
  - `/api/persons` Endpoint (nur öffentliche Profile)
  - Persons Repository + Migration 2.0
  - Datenschutz-Einstellungen
  - Shortcode: [cts_team]
- [ ] Ressourcen (Resources) synchronisieren
  - `/api/resources` (Räume, Equipment)
  - Resources Repository + Migration 2.1
  - Ressourcen-Kalender
  - Shortcode: [cts_resources]
- [ ] Songs (Worship-Songs) synchronisieren
  - `/api/songs` Endpoint
  - Songs Repository + Migration 2.2
  - Setlist-Anzeige
  - Shortcode: [cts_setlist]
- [ ] Predigten (Sermons) synchronisieren
  - `/api/songs` (ChurchTools Songs ≈ Predigten)
  - Sermons Repository + Migration 2.3
  - Audio/Video-Player-Integration
  - Shortcode: [cts_sermons]

**Erwartete Features:**
- Vollständige ChurchTools-Integration
- Mehr Use-Cases abgedeckt
- Zentrale Datenhaltung in WordPress

---

### v0.7.1.0 - Incremental Sync ⭐ PRIORITÄT 1
**Problem:** Jeder Sync lädt alle Events neu (auch unveränderte)

**Lösung:**
- [ ] Last-Modified Tracking in DB
  - Neue Spalte: `last_modified` in `wp_cts_events`
  - Neue Spalte: `last_sync_timestamp` in `wp_options`
- [ ] Delta-Sync Logic
  - ChurchTools API: `modified_after` Parameter nutzen
  - Nur geänderte/neue Events laden
  - Gelöschte Events erkennen (Soft-Delete Check)
- [ ] Sync-Statistiken erweitern
  - Neue Felder: `events_unchanged`, `events_deleted`
- [ ] Migration 1.7: last_modified Spalten hinzufügen

**Erwartete Performance:**
- 80-95% weniger API-Calls bei regelmäßigem Sync
- 5-10x schnellerer Sync bei großen Kalender-Daten

---

### v0.7.2.0 - Batch Processing
**Problem:** Große Event-Mengen (>500) führen zu Timeouts

**Lösung:**
- [ ] Chunked Processing
  - Events in Batches von 50 Events verarbeiten
  - Progress-Tracking zwischen Batches
  - Pause zwischen Batches (Rate-Limit-Schutz)
- [ ] Background Processing
  - WP-Cron Job in 5-Minuten-Intervalle aufteilen
  - Transients für Batch-State
- [ ] Progress-UI im Admin
  - Live-Progress-Bar während Sync
  - Abbruch-Button für lange Syncs
- [ ] Timeout-Protection
  - set_time_limit() für große Syncs
  - Memory-Limit-Checks

**Erwartete Performance:**
- Keine Timeouts mehr bei >1000 Events
- Sync läuft im Hintergrund weiter

---

### v0.7.3.0 - Smart Caching
**Problem:** Kalender-Daten werden zu oft neu geladen

**Lösung:**
- [ ] Transients-Cache für API-Responses
  - Kalender-Liste: 1 Stunde Cache
  - Service-Gruppen: 1 Tag Cache
  - Services: 1 Tag Cache
- [ ] Object Cache Integration
  - Redis/Memcached Support (wenn verfügbar)
  - Fallback zu Transients
- [ ] Cache-Invalidierung
  - Manual Flush-Button im Admin
  - Auto-Flush bei Sync
- [ ] Query-Caching für Events
  - Häufige Queries cachen (z.B. "nächste 10 Events")
  - Cache-TTL: 5 Minuten

**Erwartete Performance:**
- 50-70% weniger DB-Queries
- Schnelleres Page-Rendering

---

### v0.7.4.0 - Health Monitoring
**Problem:** Sync-Fehler werden erst spät erkannt

**Lösung:**
- [ ] API Health Check
  - Ping ChurchTools API alle 15 Minuten
  - Status-Indikator im Dashboard
- [ ] Error-Rate Monitoring
  - Fehler-Counter pro Stunde/Tag
  - Warnungen bei hohen Error-Rates
- [ ] Alert-System
  - E-Mail bei kritischen Fehlern
  - Admin-Notice bei Sync-Problemen
- [ ] Diagnostics-Tool
  - One-Click-Test für alle API-Endpoints
  - Netzwerk-Latency-Check
  - SSL-Zertifikat-Prüfung

**Erwartete Performance:**
- Proaktive Fehlererkennung
- Schnellere Problemlösung

---

### v0.7.5.0 - Retry Logic & Resilience
**Problem:** Temporäre Netzwerk-Fehler brechen Sync ab

**Lösung:**
- [ ] Exponential Backoff
  - Retry nach 1s, 2s, 4s, 8s, 16s
  - Max 5 Retries pro Request
- [ ] Partial Success Handling
  - Bei Fehler: bereits geladene Events speichern
  - Fehlgeschlagene Events markieren für Retry
- [ ] Circuit Breaker Pattern
  - Nach 3 aufeinanderfolgenden Fehlern: Sync pausieren
  - Auto-Reset nach 30 Minuten
- [ ] Graceful Degradation
  - Bei API-Fehler: alte Daten anzeigen mit Hinweis
  - Fallback auf Cache

**Erwartete Performance:**
- 90% weniger Sync-Abbrüche durch temporäre Fehler
- Bessere Stabilität

---

### v0.7.6.0 - Webhook Support (Optional)
**Problem:** Polling ist ineffizient bei seltenen Änderungen

**Lösung:**
- [ ] Webhook-Endpoint in WordPress
  - Route: `/wp-json/churchtools-suite/v1/webhook`
  - HMAC-Signatur-Validierung
- [ ] Event-Triggered Sync
  - Bei ChurchTools-Änderung: Push-Notification
  - Sofortiger Sync nur für geänderte Ressourcen
- [ ] Webhook-Konfiguration im Admin
  - URL-Generierung
  - Secret-Key-Management
- [ ] Fallback zu Polling
  - Bei fehlenden Webhooks: normaler Cron weiter aktiv

**Erwartete Performance:**
- Near-Realtime Updates (<1 Minute Latenz)
- 99% weniger API-Calls

---

## 🎨 Phase 6: Template-Bibliothek (v0.8.0.0) - Q2 2026

**Ziel:** Mehr View-Varianten für verschiedene Use-Cases

### v0.8.1.0 - List Views erweitern
- [ ] Fluent List (Moderne Fluent Design Language)
- [ ] Compact List (Sehr platzsparend, ohne Bilder)
- [ ] Timeline List (Vertikale Timeline mit Datums-Markern)
- [ ] Agenda List (Tages-gruppiert mit Uhrzeit-Anzeige)

### v0.8.2.0 - Calendar Views erweitern
- [ ] Monthly Clean (Minimalistisches Design)
- [ ] Weekly Fluent (Wochen-Ansicht mit Stunden-Grid)
- [ ] Yearly (Jahres-Übersicht mit Event-Punkten)
- [ ] Daily (Tages-Ansicht mit Timeline)

### v0.8.3.0 - Grid Views erweitern
- [ ] Modern Grid (Cards mit Shadows & Hover-Effekten)
- [ ] Colorful Grid (Kalender-Farben als Akzente)
- [ ] Masonry Grid (Pinterest-Style Layout)

### v0.8.4.0 - Special Views
- [ ] Slider (5 Carousel-Varianten)
- [ ] Countdown (3 Timer-Varianten bis zum Event)
- [ ] Cover (5 Hero-Section-Varianten)
- [ ] Timetable (3 Stundenplan-Varianten)

---

## 🔒 Phase 7: Security & Performance (v0.9.0.0) - Q3 2026

### v0.9.1.0 - Rate Limiting
- [ ] Rate Limiter Class
  - Request-Limits: 60 Requests/Minute, 1000/Stunde
  - Transients-basierte Counter
  - Automatisches Throttling
- [ ] IP-basiertes Limiting (für REST API)
- [ ] User-basiertes Limiting (für Admin)
- [ ] Bypass für localhost/development

### v0.9.2.0 - Input Validation & Sanitization
- [ ] Input Validator Class
  - Whitelist-basierte Validierung
  - Type-Checking (int, string, date, etc.)
  - Length-Limits
- [ ] XSS-Protection für alle User-Inputs
- [ ] SQL-Injection-Prevention (Prepared Statements)
- [ ] CSRF-Protection (Nonce-Validierung überall)

### v0.9.3.0 - Credential Security
- [ ] Crypto Helper Class
  - Passwort-Verschlüsselung mit WordPress Salts
  - Secure Storage für API-Credentials
- [ ] Secrets-Rotation
  - Session-Cookie-Refresh
  - API-Key-Rotation (wenn ChurchTools unterstützt)
- [ ] Audit-Log
  - Login-Versuche loggen
  - API-Zugriffe tracken

### v0.9.4.0 - Performance-Optimierungen
- [ ] Query-Optimierung
  - Indizes für häufige Queries
  - N+1-Problem eliminieren
  - Eager Loading für Relations
- [ ] Lazy Loading
  - Bilder erst bei Sichtbarkeit laden
  - Infinite Scroll für große Event-Listen
- [ ] Asset-Minification
  - CSS/JS minifizieren im Build-Prozess
  - SVG-Sprites für Icons
- [ ] CDN-Support
  - Konfiguration für externe Assets
  - Gravatar-Cache

---

## 📚 Phase 8: Developer Experience (v0.10.0.0) - Q4 2026

### v0.10.1.0 - Logging-System
- [ ] Logger Class
  - Log-Levels: debug, info, warning, error, critical
  - Log-Files: wp-content/uploads/churchtools-suite/logs/
  - Log-Rotation: 10 MB max, 30 Tage Retention
- [ ] Structured Logging (JSON-Format)
- [ ] Log-Viewer im Admin
- [ ] Log-Export (CSV/JSON)

### v0.10.2.0 - Developer-Tools
- [ ] Debug-Modus
  - Detaillierte Error-Messages
  - SQL-Query-Logging
  - Performance-Profiling
- [ ] API-Explorer im Admin
  - Test-Console für ChurchTools API
  - Request/Response-Inspector
  - Mock-Data-Generator
- [ ] Template-Override-Detector
  - Zeigt Theme-Overrides an
  - Version-Kompatibilität prüfen

### v0.10.3.0 - Testing & CI/CD
- [ ] Unit-Tests (PHPUnit)
  - Repository-Tests
  - Service-Tests
  - Helper-Tests
- [ ] Integration-Tests
  - API-Client-Tests (mit Mocks)
  - Sync-Flow-Tests
- [ ] E2E-Tests (Playwright)
  - Admin-UI-Tests
  - Frontend-Shortcode-Tests
- [ ] GitHub Actions CI
  - Auto-Tests bei Push
  - Code-Coverage-Reports

---

## 🌍 Phase 9: Internationalisierung (v0.11.0.0) - Q1 2027

### v0.11.1.0 - i18n Setup
- [ ] i18n Class
  - Text-Domain korrekt laden
  - Plugin-Textdomain registrieren
- [ ] POT-Datei generieren
  - wp-cli i18n make-pot
  - Alle Strings erfassen
- [ ] Deutsche Übersetzung (de_DE)
  - .po/.mo Files
  - Admin-Texte
  - Frontend-Texte
  - JavaScript-Strings

### v0.11.2.0 - Multi-Language Support
- [ ] WPML-Kompatibilität
- [ ] Polylang-Kompatibilität
- [ ] Events in mehreren Sprachen
  - Title/Description-Übersetzungen
  - Sprachauswahl im Shortcode

---

## � Phase 11: Auto-Update System (v0.12.0.0) - Q1 2027

**Ziel:** Plugin-Updates direkt aus GitHub beziehen

### v0.12.1.0 - GitHub Updater Integration
**Problem:** WordPress.org Plugin-Approval dauert lange, Updates verzögert

**Lösung:**
- [ ] GitHub Updater Class
  - GitHub API Integration (releases)
  - Version-Check gegen GitHub Tags
  - Download von Release-Assets (.zip)
- [ ] Update-Notification im Admin
  - Banner bei verfügbarem Update
  - Changelog direkt aus GitHub Release-Notes
  - One-Click-Update-Button
- [ ] Update-Settings im Admin
  - Auto-Update aktivieren/deaktivieren
  - Update-Channel wählen (stable/beta)
  - GitHub-Token für private Repos (optional)
- [ ] Rollback-Funktion
  - Vorherige Version automatisch archivieren
  - Rollback-Button bei Problemen
  - Backup vor Update erstellen

**GitHub Release Workflow:**
1. Tag pushen: `git tag v0.7.1.0 && git push --tags`
2. GitHub Action erstellt Release + ZIP
3. Plugin prüft alle 24h auf neue Version
4. Admin erhält Update-Notification

**Erwartete Features:**
- Schnellere Updates (Stunden statt Tage)
- Beta-Testing-Channel für Early Adopters
- Direkte Kontrolle über Releases

### v0.12.2.0 - GitHub Actions CI/CD
**Problem:** Manuelle ZIP-Erstellung fehleranfällig

**Lösung:**
- [ ] GitHub Actions Workflow
  - Auto-Build bei Tag-Push
  - Automated Tests (PHPUnit, PHPStan)
  - Auto-Release mit ZIP-Artifact
- [ ] Version-Bump Automatisierung
  - Script für Versions-Update
  - Changelog-Generierung aus Commits
- [ ] Pre-Release für Beta-Tester
  - Beta-Channel-Support
  - Separate Beta-Builds

---

## �🚢 Phase 10: Production-Release (v1.0.0.0) - Q2 2027

### v1.0.0 - Stable Release
- [ ] Feature-Freeze
- [ ] Security-Audit
- [ ] Performance-Audit
- [ ] Accessibility-Audit (WCAG 2.1 AA)
- [ ] Browser-Testing (Chrome, Firefox, Safari, Edge)
- [ ] WordPress-Multisite-Testing
- [ ] PHP 7.4 - 8.3 Kompatibilität
- [ ] WordPress 5.9+ Kompatibilität

### Dokumentation
- [ ] Benutzer-Handbuch (PDF + Online)
- [ ] Video-Tutorials (YouTube)
- [ ] Entwickler-Dokumentation (GitHub Wiki)
- [ ] API-Dokumentation (PHPDoc + JSDoc)
- [ ] FAQ & Troubleshooting
- [ ] Migration-Guide von altem Plugin

### Marketing & Distribution
- [ ] WordPress.org Plugin-Submission
- [ ] Plugin-Banner & Screenshots
- [ ] Demo-Website
- [ ] Support-Forum Setup
- [ ] Changelog & Release-Notes

---

## 🔮 Future Features (v1.1.0+) - 2027+

### Extended Filtering
- [ ] Kategorie-Filter für Events
- [ ] Orts-Filter (Google Maps Integration)
- [ ] Schlagwort-Filter
- [ ] Volltextsuche
- [ ] Faceted Search (kombinierte Filter)

### Calendar-Export
- [ ] iCal-Export (.ics Download)
- [ ] Google Calendar Integration
- [ ] Outlook Calendar Integration
- [ ] RSS-Feed für Events

### Notifications
- [ ] E-Mail-Benachrichtigungen bei neuen Events
- [ ] Push-Notifications (Web Push API)
- [ ] SMS-Benachrichtigungen (Twilio Integration)
- [ ] Reminder vor Event-Start

### Advanced Shortcodes
- [ ] [cts_countdown] - Live-Countdown bis Event
- [ ] [cts_next_event] - Dynamisch nächster Termin
- [ ] [cts_event_count] - Event-Zähler (für Statistiken)
- [ ] [cts_person_schedule] - Persönliche Dienste-Übersicht
- [ ] [cts_availability] - Verfügbarkeits-Kalender

### WordPress Widgets
- [ ] Legacy Widget (für Classic Themes)
- [ ] Block-Based Widget (für FSE Themes)
- [ ] Sidebar-optimierte Mini-Kalender
- [ ] Upcoming-Events-Widget

### Integrations
- [ ] WooCommerce (Event-Tickets verkaufen)
- [ ] BuddyPress (Community-Events)
- [ ] bbPress (Forum-Integration)
- [ ] Mailchimp (Newsletter-Integration)
- [ ] Zapier (Automation)

---

## 📊 Priorisierung & Timeline

### High Priority (Must-Have für v1.0)
1. **v0.7.0.1 - Vereinfachte Einstellungen** ⭐⭐⭐⭐⭐ (Quick Win!)
2. **v0.7.0.2 - Erweiterte Event-Daten aus JSON** ⭐⭐⭐⭐⭐
3. **v0.7.0.3 - Admin-UI für Events & Services** ⭐⭐⭐⭐⭐
4. **v0.7.1.0 - Incremental Sync** ⭐⭐⭐⭐⭐
5. **v0.7.2.0 - Batch Processing** ⭐⭐⭐⭐⭐
6. **v0.9.1.0 - Rate Limiting** ⭐⭐⭐⭐
7. **v0.9.2.0 - Input Validation** ⭐⭐⭐⭐
8. **v0.10.1.0 - Logging-System** ⭐⭐⭐⭐
9. **v0.12.1.0 - GitHub Updater** ⭐⭐⭐⭐

### Medium Priority (Nice-to-Have für v1.0)
10. **v0.7.0.4 - Weitere Sync-Datenquellen** ⭐⭐⭐
11. **v0.7.3.0 - Smart Caching** ⭐⭐⭐
12. **v0.8.x - Template-Bibliothek** ⭐⭐⭐
13. **v0.10.2.0 - Developer-Tools** ⭐⭐⭐
14. **v0.11.1.0 - i18n Setup** ⭐⭐⭐

### Low Priority (Post v1.0)
15. **v0.7.6.0 - Webhook Support** ⭐⭐
16. **Extended Filtering** ⭐⭐
17. **Integrations** ⭐

---

## 🎯 Roadmap-Ziele

### Kurzfristig (1-3 Monate)
- ✅ Kalender-Checkboxen (abgeschlossen v0.6.5.19)
- ✅ Service-Import im Cron-Job (abgeschlossen v0.6.5.21)
- 🎯 Vereinfachte Einstellungen (v0.7.0.1) - **NEXT!**
- 🎯 Erweiterte Event-Daten (v0.7.0.2)
- 🎯 Admin-UI Verbesserungen (v0.7.0.3)
- 🎯 Incremental Sync (v0.7.1.0)

### Mittelfristig (3-6 Monate)
- Batch Processing (v0.7.2.0)
- Smart Caching (v0.7.3.0)
- Health Monitoring (v0.7.4.0)
- Rate Limiting (v0.9.1.0)
- GitHub Updater (v0.12.1.0)
- Template-Bibliothek (v0.8.x)

### Langfristig (6-12 Monate)
- Weitere Sync-Datenquellen (v0.7.0.4)
- Security-Audit (v0.9.x)
- Logging-System (v0.10.1.0)
- Testing & CI/CD (v0.10.3.0 + v0.12.2.0)
- i18n (v0.11.0)
- Stable Release v1.0.0

---

## 📝 Notizen

**Migration vom alten Plugin:**
- Daten-Migration-Script erstellen
- Mapping alter → neuer Tabellen
- Shortcode-Kompatibilität (Alias-Support)
- Schrittweise Migration ermöglichen

**Performance-Ziele:**
- Sync-Zeit: <5 Sekunden für 100 Events
- Page-Load: <500ms für Event-Liste
- API-Response: <200ms für REST-Endpoints
- Memory-Usage: <128MB während Sync

**Code-Qualität-Ziele:**
- PHP-CodeSniffer: WordPress Coding Standards
- PHPStan Level 6+
- Test-Coverage: >70%
- Dokumentation: >90% DocBlocks

---

**Stand:** 18. Dezember 2025  
**Version:** 0.6.5.19  
**Nächster Meilenstein:** v0.7.0.0 - Sync-Optimierungen
