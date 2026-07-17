=== ChurchTools Suite - Elementor Integration ===
Contributors: fegaschaffenburg
Tags: churchtools, elementor, events, calendar, church
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 0.6.15
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Elementor Page Builder Widget für ChurchTools Suite Events - Listen, Raster und Kalender-Ansichten mit 28+ Anpassungsoptionen.

== Description ==

**ChurchTools Suite - Elementor Integration** ist ein Sub-Plugin für [ChurchTools Suite](https://github.com/FEGAschaffenburg/churchtools-suite), das einen leistungsstarken Elementor Widget bereitstellt.

= Features =

* **13+ Vordefinierte Templates** - Liste (Classic, Modern, Minimal), Raster (Simple, Modern), Kalender (Monthly, Weekly)
* **28+ Kontrollparameter** - Content, Filters, Display, Grid, Style, Advanced Sections
* **Shortcode-Wrapper Architektur** - Re-Use der bewährten ChurchTools Suite Shortcodes
* **Responsive Design** - Optimiert für Desktop, Tablet und Mobile
* **Live-Preview** - Änderungen sofort im Elementor Editor sichtbar

= Anwendungsbeispiele =

* **Event-Listen** - Kommende Gottesdienste, Kleingruppen, Veranstaltungen
* **Event-Raster** - Übersichtliche Karten-Ansicht mit Bildern
* **Monats-Kalender** - Interaktive Kalender-Ansicht

= Voraussetzungen =

* WordPress >= 6.0
* PHP >= 8.0
* [ChurchTools Suite](https://github.com/FEGAschaffenburg/churchtools-suite) >= v1.0.9.0
* [Elementor](https://elementor.com/) >= v3.0.0

= Installation =

1. ChurchTools Suite >= v1.0.9.0 installieren und aktivieren
2. Elementor >= v3.0.0 installieren und aktivieren
3. Dieses Sub-Plugin installieren und aktivieren
4. Elementor Editor öffnen → Widget-Panel → "ChurchTools Suite" Kategorie → "ChurchTools Events" Widget

= Warum ein separates Plugin? =

Ab ChurchTools Suite v1.0.9.0 wird die Elementor-Integration modularisiert:

* **Optional** - Nur für Elementor-Nutzer relevant
* **Wartbar** - Separate Releases möglich
* **Zukunftssicher** - Weitere Sub-Plugins folgen (WooCommerce, Gravity Forms, etc.)

Ab ChurchTools Suite **v2.0.0** (Q4 2026) ist dieses Sub-Plugin **zwingend erforderlich** für Elementor-Nutzung.

== Installation ==

= Automatische Installation =

1. WordPress Admin → Plugins → Installieren
2. "ChurchTools Suite Elementor" suchen
3. Installieren und Aktivieren

= Manuelle Installation =

1. ZIP-Datei von [GitHub Releases](https://github.com/FEGAschaffenburg/churchtools-suite/releases) herunterladen
2. WordPress Admin → Plugins → Installieren → Plugin hochladen
3. ZIP-Datei auswählen und installieren
4. Aktivieren

= Nach der Installation =

1. Seite in Elementor bearbeiten
2. Widget-Panel öffnen (linke Sidebar)
3. "ChurchTools Suite" Kategorie finden
4. "ChurchTools Events" Widget per Drag & Drop auf die Seite ziehen
5. Widget-Einstellungen im linken Panel anpassen

== Frequently Asked Questions ==

= Benötige ich ChurchTools Suite UND dieses Plugin? =

Ja. Dieses Plugin ist eine **Erweiterung** für ChurchTools Suite und funktioniert nicht eigenständig.

= Benötige ich Elementor Free oder Pro? =

**Elementor Free** ist ausreichend. Pro-Features werden nicht benötigt.

= Funktioniert das Plugin mit Gutenberg? =

Nein. Für Gutenberg nutzen Sie die integrierten Blöcke in ChurchTools Suite (keine separate Installation nötig).

= Funktioniert meine alte Widget-Konfiguration noch? =

Ja! Bestehende Elementor-Widgets funktionieren ohne Änderung. Das Sub-Plugin ist zu 100% kompatibel.

= Was passiert wenn ich das Sub-Plugin nicht installiere? =

Bis ChurchTools Suite **v1.9.x**: Elementor-Integration funktioniert weiterhin über Hauptplugin (deprecated).
Ab ChurchTools Suite **v2.0.0**: Widget ist nur noch über Sub-Plugin verfügbar.

= Kann ich ChurchTools Suite >= v2.0.0 ohne Sub-Plugin nutzen? =

Ja, aber ohne Elementor-Widget. Gutenberg Blöcke und Shortcodes funktionieren normal.

== Screenshots ==

1. Elementor Widget Panel - "ChurchTools Events" Widget in der Kategorie "ChurchTools Suite"
2. Widget Einstellungen - Content Section mit View-Type und Template-Auswahl
3. Widget Einstellungen - Display Section mit Toggle-Optionen
4. Live-Preview - Event-Liste im Elementor Editor

== Changelog ==

= 0.6.14 - 2026-04-12 =
* Fix: Services werden in Listenansicht wieder zuverlässig angezeigt
* Robustere Auswertung von Elementor-Switcher-Werten (`yes/true/1/on`)

= 0.6.13 - 2026-04-12 =
* Updater-Hotfix: erzwungene Cache-Aktualisierung auf Plugins-/Update-Seiten
* Verhindert, dass veraltete Update-Transients ein verfügbares Addon-Update ausblenden

= 0.6.12 - 2026-04-12 =
* Elementor-4-Kompatibilität gehärtet: robustere Aktualisierung der `event_id`-Optionen im Editor
* Verzicht auf fragile direkte Select2-Reinitialisierung
* Readme-Metadaten aktualisiert (Stable Tag, Tested up to)

= 0.5.0 - 2026-02-13 =
* Initial Beta Release
* Extracted from ChurchTools Suite v1.0.8.0
* Renamed classes: ChurchTools_Suite_Elementor_* → CTS_Elementor_*
* Dependency checks for ChurchTools Suite >= v1.0.9.0
* Dependency checks for Elementor >= v3.0.0
* Admin notices for missing dependencies
* Hooks into churchtools_suite_loaded action
* Full compatibility with ChurchTools Suite shortcodes
* 28+ widget controls (Content, Filters, Display, Grid, Style, Advanced)
* 13+ view templates (List, Grid, Calendar)

== Upgrade Notice ==

= 0.5.0 =
Initial beta release. Erfordert ChurchTools Suite >= v1.0.9.0 und Elementor >= v3.0.0.

== Support ==

* **GitHub Issues:** https://github.com/FEGAschaffenburg/churchtools-suite/issues
* **Dokumentation:** https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor
* **Main Plugin:** https://github.com/FEGAschaffenburg/churchtools-suite

== Development ==

* **Repository:** https://github.com/FEGAschaffenburg/churchtools-suite
* **License:** GPL-3.0-or-later
* **Contributing:** Pull Requests welcome!
