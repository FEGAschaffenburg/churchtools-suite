# ChurchTools Suite - Changelog

## v1.0.3.3 - Critical Hotfix (12. Januar 2026)

### 🐛 Bugfix
- **CRITICAL:** Fixed undefined $events variable causing fatal error in template data service
- Restored missing DB query and event formatting logic accidentally removed in v1.0.3.2

---

## v1.0.3.2 - Demo-Mode Cleanup (12. Januar 2026)

### 🔧 Änderungen
- Entfernt verbleibende CTS_DEMO_MODE- und Demo-Handling-Pfade im Hauptplugin (AJAX Event Details, Dashboard, API-Settings, Template Data)
- Demo-Events werden ausschließlich über das separate Demo-Plugin bereitgestellt (Persistenz in DB), kein Fallback im Hauptplugin
- README aktualisiert (Demo-Modus-Konstante entfernt)

---

## v1.0.3.1 - Modal Event Loading Bugfix (12. Januar 2026)

### 🐛 Bugfixes
- ✅ **Modal Event Loading** - Demo Events zeigen nun korrekt Inhalte im Modal
  - AJAX-Handler `cts_get_event_details` unterstützt jetzt Demo-Events
  - Event-Modal wird auch bei Demo-Daten korrekt angezeigt
  - Fehler "Error Loading Event" bei Demo-Events behoben

### 🔧 Technical
- Enhanced `ajax_get_event_details()` in `class-churchtools-suite-admin.php`
  - Fallback zu Demo Data Provider wenn Event nicht in DB gefunden
  - Unterstützt beide DB Objects und Demo-Event Arrays
  - Timezone-aware Datumformatierung für Demo-Events

---

## v1.0.3.0 - User Management & Demo Registration (12. Januar 2026)

### ✨ Features
- ✅ **CTS Managers Dashboard** - Read-only Liste aller Plugin-Manager unter Settings
  - Manager-Übersicht mit Email, letzter Anmeldung
  - Quick-Link zu User-Editor
  - Anleitung zum Hinzufügen neuer Manager

- ✅ **Demo User Auto-Create** - Automatische Erstellung beim Demo-Plugin Activation
  - `demo-manager` User wird erstellt (falls nicht vorhanden)
  - Bekommt `cts_manager` Rolle automatisch
  - Admin sieht Credentials für 24h in Admin-Notiz
  - Strong Password wird generiert

- ✅ **Post-Registration Credentials** - Zugangsdaten nach erfolgreicher Registrierung
  - Email & Passwort werden angezeigt
  - Copy-Buttons für einfache Verwendung
  - Toggle für Passwort-Sichtbarkeit
  - Schritt-für-Schritt Anleitung
  - Direct Link zur Demo

### 📚 Documentation
- 🆕 [USER-MANAGEMENT-GUIDE.md](../docs/USER-MANAGEMENT-GUIDE.md) - Vollständiger Guide für neue Features
- Updated [ROLES-AND-CAPABILITIES.md](../docs/ROLES-AND-CAPABILITIES.md)

### 🎯 Improvements
- Benutzerfreundlichere Verwaltung von Plugin-Zugriffen
- Demo-Erlebnis deutlich verbessert
- Besserer Onboarding-Flow für neue Benutzer

### Files
- `admin/views/settings/subtab-benutzer.php` (neu)
- `includes/class-demo-registration-response.php` (neu)
- `admin/class-demo-admin.php` (erweitert: Admin-Notiz)
- `churchtools-suite-demo/churchtools-suite-demo.php` (erweitert: Auto-Create)
- `admin/views/tab-settings.php` (erweitert: Benutzer-Subtab)
- `includes/class-demo-shortcodes.php` (erweitert: Success-Shortcode)

---

## v1.0.2.0 - Roles & Capabilities System (12. Januar 2026)

### Features
- ✅ **Option B: Granular Permissions** - WordPress-native Rollen & Capabilities
  - Neue Custom-Rolle: `cts_manager` für dedizierte Plugin-Manager
  - 6 granulare Capabilities:
    - `manage_churchtools_suite` - Allgemeiner Admin-Zugang
    - `configure_churchtools_suite` - Settings & Verbindung konfigurieren
    - `sync_churchtools_events` - Events synchronisieren & triggern
    - `manage_churchtools_calendars` - Kalender verwalten & selektieren
    - `manage_churchtools_services` - Services verwalten & selektieren
    - `view_churchtools_debug` - Debug & Logs ansehen
  - Automische Rolle-Registrierung bei Plugin-Aktivierung
  - Alle 23+ AJAX-Handler aktualisiert auf granulare Permissions
  - Menu-Items verwenden neue Capabilities statt `manage_options`

### Improvements
- 🔧 **Permission Check Standardisierung** - Alle AJAX-Handler konsistent
- 📚 **Dokumentation** - Vollständiges `ROLES-AND-CAPABILITIES.md` Guide
- 🚀 **Deployment** - Migration Scripts für Bash & PowerShell

### Breaking Changes
- ⚠️ Plugin-Menu-Zugang benötigt jetzt `manage_churchtools_suite` statt `manage_options`
- Bestehende Admin-User bekommen Role `cts_manager` automatisch bei Update

### Files
- `includes/class-churchtools-suite-roles.php` (neu)
- `admin/class-churchtools-suite-admin.php` (23+ Permission Checks)
- `includes/class-churchtools-suite-activator.php` (Role-Registration)
- `docs/ROLES-AND-CAPABILITIES.md` (neu)
- `scripts/migrate-roles.sh`, `migrate-roles.ps1` (neu)

---

### Features
- ✅ **Option B: Granular Permissions** - WordPress-native Rollen & Capabilities
  - Neue Custom-Rolle: `cts_manager` für dedizierte Plugin-Manager
  - 6 granulare Capabilities:
    - `manage_churchtools_suite` - Allgemeiner Admin-Zugang
    - `configure_churchtools_suite` - Settings & Verbindung konfigurieren
    - `sync_churchtools_events` - Events synchronisieren & triggern
    - `manage_churchtools_calendars` - Kalender verwalten & selektieren
    - `manage_churchtools_services` - Services verwalten & selektieren
    - `view_churchtools_debug` - Debug & Logs ansehen
  - Automische Rolle-Registrierung bei Plugin-Aktivierung
  - Alle 23+ AJAX-Handler aktualisiert auf granulare Permissions
  - Menu-Items verwenden neue Capabilities statt `manage_options`

### Improvements
- 🔧 **Permission Check Standardisierung** - Alle AJAX-Handler konsistent
- 📚 **Dokumentation** - Vollständiges `ROLES-AND-CAPABILITIES.md` Guide
- 🚀 **Deployment** - Migration Scripts für Bash & PowerShell

### Breaking Changes
- ⚠️ Plugin-Menu-Zugang benötigt jetzt `manage_churchtools_suite` statt `manage_options`
- Bestehende Admin-User bekommen Role `cts_manager` automatisch bei Update

### Files
- `includes/class-churchtools-suite-roles.php` (neu)
- `admin/class-churchtools-suite-admin.php` (23+ Permission Checks)
- `includes/class-churchtools-suite-activator.php` (Role-Registration)
- `docs/ROLES-AND-CAPABILITIES.md` (neu)
- `scripts/migrate-roles.sh`, `migrate-roles.ps1` (neu)

---

## v0.5.1.0 - Frontend CSS/JS (12. Dezember 2025)

### Features
- ✅ **Frontend CSS** - Vollständiges Styling für alle View-Typen
  - Calendar View Styles (Monatskalender mit Grid)
  - List View Styles (Date-Badge, Services, Meta-Infos)
  - Grid View Styles (Card-Layout, responsive Columns)
  - Loading States & Spinner-Animation
  - Empty States für leere Ergebnisse
  - Responsive Design (Mobile-first)

- ✅ **Frontend JavaScript** - Interaktive Features
  - Calendar Grid Rendering (Events in Kalender-Tage einfügen)
  - Calendar Navigation (Monatswechsel)
  - Grid Detail Buttons (Modal-Trigger)
  - Modal Views (Event-Details in Overlay)
  - Event Click Handlers (Mehrere Events pro Tag)
  - AJAX Integration (Event-Laden ohne Page Reload)

- ✅ **Conditional Asset Loading** - Performance-Optimierung
  - CSS/JS nur laden wenn Shortcodes auf Seite verwendet werden
  - `has_shortcode()` Check für alle 14 CTS Shortcodes
  - Vermeidung unnötiger HTTP-Requests

### Files
- `public/css/churchtools-suite-public.css` (neu)
- `public/js/churchtools-suite-public.js` (neu)
- `includes/class-churchtools-suite.php` (erweitert: enqueue_public_assets)

---

## v0.5.0.0 - Shortcode Handler (12. Dezember 2025)

### Features
- ✅ **Shortcode Handler** - 14 verschiedene Shortcodes für alle View-Typen
  - `[cts_calendar]` - Calendar Views (monthly, weekly, yearly, daily)
  - `[cts_list]` - List Views (classic, modern, minimal, with-map, fluent)
  - `[cts_grid]` - Grid Views (simple, modern, colorful, with-map)
  - `[cts_modal]` - Modal Single Event
  - `[cts_slider]` - Slider Views (type-1 bis type-5)
  - `[cts_countdown]` - Countdown Views (type-1 bis type-3)
  - `[cts_cover]` - Cover Views (classic, modern, clean)
  - `[cts_timetable]` - Timetable Views (modern, clean, timeline)
  - `[cts_carousel]` - Carousel Views (type-1 bis type-4)
  - `[cts_single]` - Single Event Views
  - `[cts_map]` - Map Views (standard, advanced, liquid)
  - `[cts_search]` - Search Views (bar, advanced)
  - `[cts_widget]` - Widget Views (upcoming, calendar-widget)
  - `[cts_events]` - Legacy-Kompatibilität

- ✅ **Template Data Provider** - Daten-Service für Templates
  - `get_events()` - Events mit Filtern abrufen
  - `get_event_by_id()` - Einzelnes Event laden
  - `get_events_by_date()` - Events gruppiert nach Datum
  - `get_events_by_calendar()` - Events gruppiert nach Kalender
  - Event-Formatierung mit WordPress date/time formats
  - Services-Integration (Dienste zu Events)
  - Calendar-Info-Integration (Name, Farbe)
  - Computed Fields (is_all_day, is_past, is_today, is_multiday, duration)

### Files
- `includes/class-churchtools-suite-shortcodes.php` (neu)
- `includes/services/class-churchtools-suite-template-data.php` (neu)
- `SHORTCODE-GUIDE.md` (neu) - Vollständige Shortcode-Dokumentation

---

## v0.4.0.0 - Template Loader (12. Dezember 2025)

### Features
- ✅ **Template Loader System** - WordPress-konformes Template-System
  - `locate_template()` - Template-Datei finden (Theme > Plugin Priority)
  - `render_template()` - Template rendern mit Variable Extraction
  - `get_available_views()` - Verfügbare View-Varianten scannen
  - `get_template_info()` - Template-Metadaten (Pfad, Größe, Änderungsdatum)
  - Theme Override Support (Theme überschreibt Plugin-Templates)
  - WordPress Filter Hooks (`churchtools_suite_template_path`, `churchtools_suite_template_output`)
  - Debug-Logging bei aktiviertem `WP_DEBUG`

- ✅ **Basis-Templates** - Proof-of-Concept für 3 View-Typen
  - `templates/calendar/monthly-modern.php` - Monatskalender mit Navigation
  - `templates/list/classic.php` - Listen-View mit Date-Badge und Services
  - `templates/grid/simple.php` - Card-Grid mit konfigurierbaren Columns
  - Alle Templates: Translation-ready, Accessibility-Features, Semantic HTML

- ✅ **Template-Dokumentation**
  - `templates/README.md` - Vollständige Template-Entwickler-Dokumentation
  - Theme Override Anleitung
  - Template-Variablen Referenz
  - Hooks & Filter Dokumentation
  - Best Practices

### Files
- `includes/class-churchtools-suite-template-loader.php` (neu)
- `templates/calendar/monthly-modern.php` (neu)
- `templates/list/classic.php` (neu)
- `templates/grid/simple.php` (neu)
- `templates/README.md` (neu)

---

## v0.3.14.6 - Person Name Fix (11. Dezember 2025)

### Bugfix
- ✅ **Person Names Import** - Personen-Namen werden jetzt korrekt gespeichert
  - Problem: `isset()` gab `true` zurück auch wenn `person = null`
  - Lösung: Geändert zu `!empty()` für korrekte Null-Prüfung
  - Fallback zu `requesterPerson.domainAttributes` wenn `person` null ist
  - ChurchTools API-Struktur korrekt implementiert

### Files
- `includes/services/class-churchtools-suite-event-sync-service.php` (Line 678, 693)

---

## v0.3.13.0 - Services UI in Events-Tab (11. Dezember 2025)

### Features
- ✅ **Events-Tab erweitert** - Services-Spalte in Event-Tabelle
  - Service-Name mit Person-Name anzeigen
  - CSS-Styling für Services-Anzeige
  - Mehrere Services pro Event

### Files
- `admin/views/tab-events.php`
- `admin/css/churchtools-suite-admin.css`

---

## v0.3.12.0 - Event Services Sync (10. Dezember 2025)

### Features
- ✅ **Event Services Import** - Services werden bei Event-Sync importiert
  - `process_event_services()` Methode
  - eventServices aus Events API extrahiert
  - Filter nach ausgewählten Services (aus Services-Tab)
  - Speicherung in event_services Tabelle
  - Auto-Delete alter Services bei Event-Update
  - Person-Name aus eventServices/requesterPerson extrahiert
  - Debug-Logging für Service-Import

### Files
- `includes/services/class-churchtools-suite-event-sync-service.php`

---

## v0.3.11.4 - API Endpoint Verification (10. Dezember 2025)

### Bugfix
- ✅ Doppeltes "api" in Endpoints entfernt
- Korrekte Endpoints: `/api/servicegroups`, `/api/services`
- `api_request()` fügt bereits `/api/` Prefix hinzu

---

## v0.3.11.3 - Service Groups Selection (10. Dezember 2025)

### Features
- ✅ **Migration 1.4** - wp_cts_service_groups Tabelle
- ✅ **Service Groups Repository** - CRUD & Selection
- ✅ **2-Step Workflow** - Erst Gruppen, dann Services synchronisieren
- ✅ **AJAX Handlers** - Service Groups Sync & Selection
- ✅ **Admin UI** - Tab "Services" mit 3-Schritt-Workflow

### Files
- `includes/class-churchtools-suite-migrations.php` (Migration 1.4)
- `includes/repositories/class-churchtools-suite-service-groups-repository.php` (neu)
- `admin/views/tab-services.php` (erweitert)

---

## v0.3.11.0 - Services Selection (9. Dezember 2025)

### Features
- ✅ **Migration 1.3** - wp_cts_services Tabelle
- ✅ **Services Repository** - CRUD & Selection
- ✅ **Service Sync Service** - /api/services Sync
- ✅ **Admin UI** - Tab "Services" mit Sync & Auswahl
- ✅ **AJAX Handlers & JavaScript**

### Files
- `includes/class-churchtools-suite-migrations.php` (Migration 1.3)
- `includes/repositories/class-churchtools-suite-services-repository.php` (neu)
- `includes/services/class-churchtools-suite-service-sync-service.php` (neu)
- `admin/views/tab-services.php` (neu)

---

## v0.3.10.0 - Event Services Repository (9. Dezember 2025)

### Features
- ✅ **Event Services Repository** - CRUD für event_services Tabelle
  - `get_for_event()` - Services für Event abrufen
  - `delete_for_event()` - Alle Services eines Events löschen
  - `get_unique_service_names()` - Alle verwendeten Service-Namen

### Files
- `includes/repositories/class-churchtools-suite-event-services-repository.php` (neu)

---

## v0.3.9.4 - Manueller Cron-Trigger (8. Dezember 2025)

### Features
- ✅ **AJAX-Endpoints** - Manueller Sync & Keepalive Trigger
- ✅ **Debug-Tab** - Buttons für manuelle Ausführung
- ✅ **Sofortiges Feedback** - Sync-Statistiken anzeigen

### Files
- `admin/class-churchtools-suite-admin.php` (AJAX Handler)
- `admin/views/tab-debug.php` (Trigger-Buttons)

---

## v0.3.9.3 - Sync-Historie Tabelle (8. Dezember 2025)

### Features
- ✅ **Migration 1.2** - wp_cts_sync_history Tabelle
- ✅ **Sync History Repository** - CRUD für Sync-Logs
- ✅ **Debug-Tab** - Letzte 10 Syncs anzeigen

### Files
- `includes/class-churchtools-suite-migrations.php` (Migration 1.2)
- `includes/repositories/class-churchtools-suite-sync-history-repository.php` (neu)

---

**Vollständiger Changelog:** Siehe [ROADMAP.md](ROADMAP.md)
