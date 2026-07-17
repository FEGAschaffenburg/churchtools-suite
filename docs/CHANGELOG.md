# ChurchTools Suite - Changelog

## v1.2.1.0 - Release-Update (27. April 2026)

### 📦 Release-Artefakte
- `churchtools-suite-1.2.1.0.zip`
- `churchtools-suite-elementor-0.6.28.zip`
- `churchtools-suite-posts-sync-0.1.6.zip`

## v1.2.0.26 - Posts Sync f�r Produktivumgebungen freigegeben (27. April 2026)

### ?? Neu / Freigabe
- **Posts Sync Addon** ist jetzt f�r alle Umgebungen freigegeben (kein "coming soon" mehr).
- Entfernt: automatische Deaktivierung in Nicht-Lokalen-Umgebungen.
- Entfernt: "coming soon"-Hinweis in Admin-Bereichen.

### ?? Release-Artefakte
- `churchtools-suite-1.2.0.26.zip`
- `churchtools-suite-elementor-0.6.24.zip`
- `churchtools-suite-posts-sync-0.1.6.zip`

## v1.2.0.25 - Image Deduplication für wiederkehrende Events (27. April 2026)

### 🚀 Performance-Optimierung
- Bilder werden bei wiederkehrenden Events nicht mehr mehrfach heruntergeladen.
- Neue `find_existing_image_by_url()` Methode prüft, ob ein Bild mit derselben URL bereits importiert wurde.
- Beim Sync wird das bestehende Bild wiederverwendet statt es erneut zu importieren.
- Signifikant schnellere Syncs für wiederkehrende Events und Termine.

### 💾 Weitere Verbesserungen
- Optimierte Datenbank-Abfragen für Bildsuche
- Besseres Logging beim Bildwiederverwendung für Debugging
- Reduzierte Bandbreitennutzung und Speicherplatzverbrauch

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.25.zip`
- `churchtools-suite-elementor-0.6.23.zip`
- `churchtools-suite-posts-sync-0.1.5.zip`

## v1.2.0.24 - Carousel Navigation UX Fix (26. April 2026)

### 🛠️ Fixes
- Carousel-Pfeile werden nicht mehr angezeigt, wenn nur ein Termin vorhanden ist.
- Zusätzlicher JS-Fallback blendet Navigation aus, wenn kein Navigationsschritt möglich ist.

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.24.zip`
- `churchtools-suite-elementor-0.6.22.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.23 - Originalbild-Priorisierung (26. April 2026)

### 🛠️ Fixes
- Bildimport priorisiert jetzt echte Original-/Download-URLs aus ChurchTools.
- Thumbnail-URLs (`imageUrl`/`thumbnailUrl`) werden nur noch als Fallback genutzt.
- Re-Sync verwendet damit bevorzugt die echte Originaldatei, wenn verfügbar.

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.23.zip`
- `churchtools-suite-elementor-0.6.22.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.22 - Re-Sync Bild-Neuerstellung (26. April 2026)

### 🛠️ Fixes
- Beim Re-Sync werden Event- und Appointment-Bilder jetzt immer neu importiert.
- Vorhandene CTS-importierte Bilder werden vor Re-Import sicher ersetzt, um veraltete Bilder zu vermeiden.
- Dateiname aus ChurchTools wird beim Import robuster übernommen (inkl. Query-Parameter wie `filename`/`name`).

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.22.zip`
- `churchtools-suite-elementor-0.6.22.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.21 - UX & Modal Bildklick Fix (26. April 2026)

### 🛠️ Fixes
- Modal-Bildklick führt nicht mehr versehentlich zur Startseite bei ungültigem Bild-Link
- Robustere Auflösung von Bild-URLs im Frontend-Modal

### ✨ Verbesserungen
- Neue Hero-Presets für Bild/Höhe (`Kompakt`, `Standard`, `Hero`)
- Optionale Mobile-Optimierung für die Hero-View
- Bessere Standardwerte für Hero-Höhe und Titel-Darstellung ohne Nachjustieren

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.21.zip`
- `churchtools-suite-elementor-0.6.22.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.20 - Hero-View Fixes (26. April 2026)

### 🛠️ Fixes
- Elementor: `Titel anzeigen (Hero)` Schalter wieder verfügbar (Core + Addon Widget)
- Hero-Carousel: `hero_title_font_size` wird korrekt vom Widget bis ins Template durchgereicht
- Hero-Carousel: Bilddarstellung `Contain` robuster umgesetzt, damit das Bild vollständig sichtbar bleibt

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.20.zip`
- `churchtools-suite-elementor-0.6.21.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.19 - Update-Erkennung stabilisiert (26. April 2026)

### 🔧 Verbesserungen
- Neuer eindeutiger Versionssprung, damit WordPress die Aktualisierung wieder sicher als neues Update erkennt
- Elementor Addon-Version synchron mit angehoben, damit Monorepo-Release konsistent bleibt

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.19.zip`
- `churchtools-suite-elementor-0.6.20.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.18 - Elementor Stil-Gruppierung & Bilddarstellung (26. April 2026)

### ✨ Neu
- Elementor: Stil-Tab klar gruppiert mit Abschnitten `Bild & Hero` sowie `Farben & Layout`
- Bilddarstellung (`Cover/Contain`) und Hero-Titelgröße sind im Stil-Tab besser auffindbar

### 🔧 Verbesserungen
- Modal-Bilddarstellung auf `Contain` umgestellt (vollständiges Bild statt Zuschnitt)
- Klick auf Modal-Bild öffnet das Originalbild in einem neuen Tab

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.18.zip`
- `churchtools-suite-elementor-0.6.19.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.17 - Bildmodus für Views (26. April 2026)

### ✨ Neu
- Neue Option `Bilddarstellung` in Gutenberg-Block und Elementor-Widget: `Zuschneiden (Cover)` oder `Ganzes Bild (Contain)`
- Option wird bis in die Templates durchgereicht (`list`, `grid`, `carousel`, `countdown`)

### 🔧 Verbesserungen
- Einheitliche Sanitisierung für Bildmodus in den Shortcodes (`cover`/`contain`)
- CSS-Contain-Modus ergänzt inkl. deaktivierter Hover-Zoom-Effekte bei Ganzbild-Darstellung

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.17.zip`
- `churchtools-suite-elementor-0.6.18.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.16 - Update-Trigger UI-Fix (26. April 2026)

### 🛠️ Fixes
- Manueller Update-Trigger im Debug-Tab nutzt wieder den AJAX-Check statt Redirect auf WordPress Update-Core
- Rückmeldungen der manuellen Update-Prüfung werden im Bereich `Update & Log` angezeigt (nicht mehr im Sync-Bereich)
- Bei Fehlern wird neben der allgemeinen Meldung auch die technische Detailmeldung aus dem Updater angezeigt

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.16.zip`
- `churchtools-suite-elementor-0.6.17.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.15 - Neue Carousel Hero-View (26. April 2026)

### ✨ Neue Features
- Neue Carousel-Ansicht `carousel-einzel-event` mit vollflächigem Hintergrundbild und Overlay-Inhalten
- Hero-Slider zeigt Titel, Datum, Uhrzeit, Ort sowie optional Tags und Dienste

### 🔧 Verbesserungen
- Neue Carousel-View im Core-Template-Loader registriert und normalisiert
- Elementor-Auswahl (Core + Addon Widget) um `carousel-einzel-event` erweitert

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.15.zip`
- `churchtools-suite-elementor-0.6.17.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
- `churchtools-suite-presentations-0.1.0.zip`

## v1.2.0.7 - Frontend/Elementor Hotfixes (12. April 2026)

### 🛠️ Hotfixes
- **Modal-Popup Anzeige stabilisiert**
  - Robuste Bool-Auswertung für `data-*` Anzeigeoptionen im Frontend-Modal
  - Verhindert inkonsistente Anzeige von Details je nach View/Container

- **Elementor Listenansicht: Services-Fix (Addon v0.6.14)**
  - Switcher-Werte werden robust ausgewertet (`yes/true/1/on`)
  - `show_services` wird zuverlässig an Shortcodes übergeben

- **Deploy-Skripte gehärtet**
  - Automatische Rechte-Normalisierung nach SCP-Deploy (`dirs 755`, `files 644`)
  - Verhindert 403-Fehler bei CSS/JS-Assets nach Deployment

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.7.zip`
- `churchtools-suite-elementor-0.6.14.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.6 - Updater-Hotfix für Elementor Addon (12. April 2026)

### 🛠️ Hotfix
- **Elementor-Addon Updater aktualisiert (v0.6.13)**
  - Erzwingt Refresh der Update-Caches auf `Plugins`- und `Update`-Seite
  - Verhindert, dass stale Transients verfügbare Addon-Updates ausblenden

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.6.zip`
- `churchtools-suite-elementor-0.6.13.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.5 - Elementor 4 Kompatibilitäts-Härtung (12. April 2026)

### 🔧 Verbesserungen
- **Elementor-Addon auf v0.6.12 aktualisiert**
  - Robustere Aktualisierung der `event_id`-Optionsliste im Elementor-Editor
  - Keine fragile direkte Select2-Reinitialisierung mehr
  - Stabileres Verhalten in neueren Elementor-Editor-Versionen (u. a. 4.x)

### 📦 Release-Artefakte
- `churchtools-suite-1.2.0.5.zip`
- `churchtools-suite-elementor-0.6.12.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`

## v1.2.0.4 - Posts-Sync Frontend & Block-Stabilisierung (12. April 2026)

### ✨ Neue Features
- **Posts-Frontend-Ausgabe für das Posts-Sync-Addon**
  - Neuer Gutenberg-Block "ChurchTools Berichte"
  - Neuer Shortcode `[cts_posts]` für flexible Einbindung
  - Gemeinsame Render-Logik für Block und Shortcode

### 🔧 Verbesserungen
- **"Nur neue"-Filter verfeinert**
  - Berücksichtigt jetzt neben dem Datum auch die genaue Ende-Uhrzeit
  - Zeitfensterprüfung nutzt Veröffentlichungs-/Ablauf-Metadaten

- **Block-Registrierung robuster gemacht**
  - Zusätzliche Fallback-Registrierung/Enqueue für Editor-Kontexte mit abweichendem Verhalten

- **Umgebungslogik im Posts-Sync-Addon vereinheitlicht**
  - Freigabe für `local`, `development`, `staging`
  - Optionales Force-Enable und Filter-Hook für Overrides

### 🧩 Monorepo / Runtime
- **Addon-Sync in Runtime-Ordner beibehalten**
  - Änderungen an Addons werden weiterhin per `scripts/sync-runtime-addons.ps1` in aktive Runtime-Plugin-Ordner gespiegelt

## v1.2.0.3 - Kritischer Bugfix: UTF-8 BOM & Elementor-Editor-Kompatibilität (1. März 2026)

### 🐛 Kritischer Bugfix
- **UTF-8 BOM aus PHP-Dateien entfernt**
  - `churchtools-suite.php` und `class-churchtools-suite-logger.php` hatten UTF-8 BOM (EF BB BF)
  - BOM wurde bei jeder HTTP-Antwort als Rohtext ausgegeben und brach alle JSON/REST-API-Responses
  - Elementor-Editor zeigte weißes Canvas wegen `SyntaxError: Unexpected token '﻿'` in REST-Antworten
  - Fix: BOM aus beiden Dateien entfernt

### 🛡️ Stabilität
- **isEditor-Erkennung für Elementor-Preview erweitert**
  - `elementor-editor-active`-Body-Klasse und `elementor-preview`-URL-Parameter werden nun erkannt
  - Click-Handler (Modal, Navigation) werden im Elementor-Preview-Iframe nicht mehr initialisiert

- **`enqueue_block_assets`-Guard für Elementor-Admin-Seite**
  - Public-Assets werden auf der Elementor-Editor-Admin-Seite (`post.php?action=elementor`) nicht geladen
  - Reduziert potenzielle Konflikte mit dem Elementor-Editor-Kontext

### ⚠️ Hinweis nach Update
- **OPcache leeren**: Nach dem Update Laragon neu starten (Stop → Start) um den PHP OPcache zu leeren

---

## v1.2.0.2 - Core/Elementor Entkopplung & UI-Klarheit (1. März 2026)

### 🛡️ Stabilität
- **Elementor-Code aus dem Core entfernt**
  - Elementor-spezifische Bridge in `assets/js/churchtools-suite-public.js` entfernt
  - Kein Zugriff des Hauptmoduls mehr auf `elementorFrontend`/Elementor-Hooks
  - Verhindert Nebenwirkungen im Elementor-Editor, wenn das Elementor-Addon nicht installiert ist

### 🧭 Admin-UX
- **Health-Übersicht in Debug integriert**
  - Health-Status der Sync-Module in `Debug > Übersicht` sichtbar

- **Settings bereinigt**
  - Subtab `Benutzer` aus `Einstellungen` entfernt

- **Begriffe präzisiert**
  - Eindeutige Trennung in der UI zwischen `Dienstgruppen` und `Post-Gruppen`

### ✅ Qualitätssicherung
- Syntaxprüfung der betroffenen PHP-Dateien erfolgreich

## v1.2.0.1 - Hotfix Grid Template Parse Error (28. Februar 2026)

### 🐛 Kritischer Bugfix
- **Parse Error in Grid-Template behoben**
  - Fix für fehlerhafte PHP-Control-Structure in `templates/views/event-grid/grid-background-images.php`
  - Ursache war ein ungültiger Blockabschluss (`if/endif`) im Meta-Bereich der Card
  - Kritischer Fehler beim Laden der betroffenen Grid-Ansicht wird damit verhindert

### ✅ Qualitätssicherung
- PHP-Lint erneut auf allen Plugin-Dateien ausgeführt

## v1.2.0.0 - Sync Module Platform (27. Februar 2026)

### ✨ Neue Features
- **Sync-Module Registry im Core**
  - Einführung einer zentralen Modul-Registry über `ChurchTools_Suite_Sync_Modules`
  - Addon-Module werden über `cts_register_sync_modules` registriert
  - Core-Modul `events` (Termine) wird immer als Basis-Modul bereitgestellt

- **Sync Runtime (Status + Locks)**
  - Neue Runtime-Klasse `ChurchTools_Suite_Sync_Runtime`
  - Einheitliches Statusmodell pro Modul (`state`, letzte Läufe, Ergebnis)
  - Locking für modulbezogene Aktionen zur Vermeidung paralleler Läufe

### 🔧 Verbesserungen
- **Posts-Sync Addon in Modul-Contract integriert**
  - Modul-Manifest + Status-Callback für `posts`
  - Runtime-gestützte Status-/Result-Integration

- **Modulstatus sichtbar in beiden Bereichen**
  - Modulstatus-Tabelle im Tab `Synchronisation`
  - Modulstatus-Tabelle auch in `Einstellungen > Synchronisation`
  - Karten werden immer angezeigt (inkl. Hinweis, falls keine Module vorhanden)

- **Statusdarstellung vereinheitlicht**
  - Lesbare Statuslabels: `Bereit`, `Läuft`, `OK`, `Fehler`, `Deaktiviert`

### 🧩 Monorepo / Runtime
- **Addon-Stand vereinheitlicht**
  - Sync der Monorepo-Addon-Quellen in Runtime-Plugin-Ordner über `scripts/sync-runtime-addons.ps1`

### ✅ Qualitätssicherung
- PHP-Lint für alle betroffenen Dateien ohne Syntaxfehler

## v1.1.4.2 - Logger Simplification (19. Februar 2026)

### 🔧 Optimierungen
- **Logger vereinfacht** - Nutzt jetzt WordPress error_log() statt custom File-Logging
  - Keine custom Log-Dateien mehr in wp-content/uploads/
  - Keine Log-Rotation, Compression, CSV-Export mehr nötig
  - Einfachere Wartung und weniger Komplexität
  - Legacy-Methoden als No-Ops für Backward-Compatibility
  - Logs nur noch in WordPress debug.log (wenn WP_DEBUG aktiv)

- **Debug-Code entfernt** - Temporäres Debugging aus Templates entfernt
  - Countdown Template: Event-Daten Debug-Logs entfernt
  - Production-ready Code ohne temporäre Debug-Statements

## v1.1.4.1 - Countdown Click Bugfix (19. Februar 2026)

### 🔧 Bugfixes
- ✅ **Countdown Click-Funktionalität** - Variable-Ordering-Bug behoben
  - Fix: Click-Attribute wurden VOR Event-Zuweisung erstellt
  - `$next_event` wird jetzt korrekt ZUERST definiert, dann für Click-Logik verwendet
  - Countdown ist jetzt vollständig klickbar mit Modal-Support
  - Alle Accessibility-Attribute (`role="button"`, `data-event-id`, `aria-label`) funktionieren

## v1.1.4.0 - Hero Images & Calendar Fallback (19. Februar 2026)

### ✨ Neue Features
- **Hero Images aus raw_payload** - Events können jetzt imageUrl im raw_payload haben
  - Template Data extrahiert automatisch imageUrl aus JSON
  - Demo-Events nutzen hochwertige Unsplash-Bilder
  - Fallback-Chain: raw_payload → image_url → calendar_image

- **Kalender-Bild Fallback** - Events ohne Bild zeigen automatisch Kalender-Bild
  - Intelligente 3-stufige Fallback-Logik
  - Keine leeren Bilder mehr in Listen/Grids/Carousels
  - Immer visuelle Darstellung garantiert

- **Carousel View Image Support** - Carousel zeigt jetzt Hero-Images korrekt
  - Unterstützt image_url aus Template Data
  - Fallback zu Kalender-Farbe wenn kein Bild vorhanden

### 🔧 Bugfixes
- ✅ **Countdown/Carousel Demo-Seiten** - Bilder werden jetzt korrekt angezeigt
  - Fehlende Hero-Images in Countdown-Ansicht behoben
  - Carousel zeigt jetzt alle Event-Bilder
  - Demo-Events haben realistische Beispiel-Bilder

### 📝 Demo Plugin
- Demo-Events generieren jetzt automatisch Hero-Images
- Event-spezifische Bilder (Gottesdienst, Jugendabend, Kindergottesdienst, etc.)
- Kommerzielle Nutzung ohne Attribution (Unsplash License)

## v1.0.5.0 - Elementor Integration Fix & System Info (17. Januar 2026)

### 🔧 Bugfixes
- ✅ **Elementor Integration Load Timing** - Integration wird jetzt über `plugins_loaded` Hook geladen
  - Fix: `is_plugin_active()` Funktion war im Constructor noch nicht verfügbar
  - Priority 20 stellt sicher dass Elementor vor unserer Integration lädt
  - Automatisches Laden von `wp-admin/includes/plugin.php` wenn benötigt

- ✅ **Elementor Widget Registration** - Verbesserte Hook-Registrierung
  - Kategorie "ChurchTools Suite" wird korrekt registriert
  - Widget wird über `elementor/widgets/register` Hook registriert
  - Umfangreiches Debug-Logging für Troubleshooting

### ✨ Neue Features
- **System Info Dashboard** - Elementor-Status wird im Admin-Dashboard angezeigt
  - Zeigt ob Elementor aktiv ist (✓ Aktiv / ✗ Inaktiv)
  - Zeigt installierte Elementor-Version wenn aktiv
  - Integration im System-Bereich neben WordPress/PHP-Version

### 🏗️ Architektur
- **Saubere Integration-Klasse** - Neue `ChurchTools_Suite_Elementor_Integration` Klasse
  - Zentrale Verwaltung aller Elementor-Funktionen
  - Klare Trennung von Plugin-Core und Elementor-Code
  - Conditional Loading: Nur laden wenn Elementor aktiv ist

### 📝 Code Quality
- Umfangreiches Error-Logging für Debugging
- Try-Catch Blöcke für Widget-Registrierung
- Klare Fehlerbehandlung bei fehlenden Dependencies

---

## v1.0.4.0 - Calendar Image Picker & API Error Handling (17. Januar 2026)

### 🔧 Bugfixes
- ✅ **Calendar Image Picker JavaScript Fix** - Try-Catch Block verhindert Fehler
  - Robuste Fehlerbehandlung wenn Mediathek nicht geladen ist
  - Console-Logging für besseres Debugging

- ✅ **API Error Messages** - Strukturierte Fehlerausgabe mit Debug-Hints
  - Zeigt HTTP-Statuscode und Error-Message
  - Gibt hilfreiche Debugging-Hinweise (z.B. CT-API-Token prüfen)
  - Verbesserte User Experience bei API-Problemen

---

## v1.0.3.19 - Elementor Widget Hotfix (14. Januar 2026)

### 🔧 Bugfixes
- ✅ **Class Definition Wrapper** - Elementor Widget-Klasse in `if ( ! class_exists() )` umschlossen
  - Verhindert Fatal Error bei doppelter Klassendefinition
  - Standard WordPress Plugin-Pattern

- ✅ **Registration Guard** - Widget-Registrierung überprüft `did_action( 'elementor/loaded' )`
  - Widget wird nur registriert wenn Elementor aktiv ist
  - Fehlerprävention für inaktive Elementor Installationen

### ✅ Qualitätssicherung
- PHP Syntax Validation bestanden
- Alle Dateien geprüft (churchtools-suite.php, classes, widget)

---

## v1.0.3.18 - Elementor Events Widget (14. Januar 2026)

### ✨ Neue Features
- **Elementor Page Builder Integration** 🎉
  - Neuer "ChurchTools Events" Widget für Elementor
  - Pragmatische Shortcode-Wrapper-Architektur (Reuse existing functionality)
  - 28+ Kontrollparameter mit vollständiger UI
  - Volle Unterstützung aller bestehenden Shortcode-Features

### 🎨 Widget-Funktionen
**Content Section:**
- Ansicht: Liste oder Raster
- Template: 13 vordefinierte Designs
- Anzahl Events: 1-100
- Spalten (Raster): 1-6

**Filter Section:**
- Kalender-Filter (Multi-Select)
- Tags-Filter (Multi-Select)
- Vergangene Events anzeigen

**Display Section:**
- 8 Toggle-Optionen für verschiedene Event-Informationen

**Style Section:**
- Theme Standard oder Benutzerdefiniert
- Custom Colors, Spacing, Border Radius

### 🔧 Technische Details
- Location: `includes/elementor/class-churchtools-suite-elementor-events-widget.php`
- Registration: `elementor/widgets/register` Hook
- Architecture: Shortcode-Wrapper (pragmatisch, wartbar)
- Vollständig auf Deutsch

---

## v1.0.3.6 - Modal Event Loading Bugfix (12. Januar 2026)

### 🐛 Bugfixes
- ✅ **Modal Event Loading** - Demo Events zeigen nun korrekt Inhalte im Modal
  - AJAX-Handler `cts_get_event_details` unterstützt jetzt Demo-Events
  - Event-Modal wird auch bei Demo-Daten korrekt angezeigt
  - Fehler "Error Loading Event" bei Demo-Events behoben

### 🔧 Technical
- Enhanced `ajax_get_event_details()` in `class-churchtools-suite-admin.php`:
  - Fallback zu Demo Data Provider wenn Event nicht in DB gefunden
  - Unterstützt beide DB Objects und Demo-Event Arrays
  - Timezone-aware Datumformatierung für Demo-Events (keine GMT-Konvertierung für Demo-Events)
  - Sichere Feldextraktion mit `isset()` für optionale Properties
  - Image-Felder in AJAX Response hinzugefügt (`image_attachment_id`, `image_url`)

---

## v1.0.3.5 - Translation Notice Suppression (12. Januar 2026)

### 🔧 Änderungen
- JIT Translation Notice Suppression für WordPress 6.7 (identisch mit Demo-Plugin v1.0.5.15)

---

## v1.0.3.4 - Translation Notice Suppression (12. Januar 2026)

### 🔧 Änderungen
- JIT Translation Notice Suppression für WordPress 6.7 (identisch mit Demo-Plugin v1.0.5.15)

---

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

## v1.0.3.1 - DEPRECATED - Modal Bugfix nicht vollständig (12. Januar 2026)

**HINWEIS:** Diese Version hatte den Modal-Fix im CHANGELOG beschrieben, aber nicht implementiert. Siehe v1.0.3.6 für die tatsächliche Implementierung.

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
