# ChurchTools Integration Suite

Repository fuer das WordPress-Hauptplugin ChurchTools Integration Suite. Die Addons werden in eigenen Repositories entwickelt und veroeffentlicht.

## Repository-Status

Stand: 2026-08-28

Diese README ist die zentrale Dokumentation des aktuellen technischen Stands. Historische Details werden in der Git-Historie bewahrt.

### Enthaltene Plugins

| Paket | Typ | Version | Einstiegspunkt | Status |
| --- | --- | --- | --- | --- |
| ChurchTools Integration Suite | Hauptplugin | 1.3.0.10 | `churchtools-suite.php` | aktiv entwickelt |
| Elementor Integration | Addon | 1.0.0.3 | [eigenes Repository](https://github.com/FEGAschaffenburg/churchtools-suite-elementor) | eigenständiges Plugin |
| Posts Sync Addon | Addon | 0.2.0.3 | [eigenes Repository](https://github.com/FEGAschaffenburg/churchtools-suite-posts-sync) | eigenständiges Plugin |
| Presentations Addon | Addon | 0.1.3 | [eigenes Repository](https://github.com/FEGAschaffenburg/churchtools-suite-presentations) | eigenständiges Plugin |
| ChurchTools Suite Demo | optionales Plugin | 1.1.4.1 | separat | nur Dokumentation/Demo |

### Abhaengigkeiten

- Hauptplugin: WordPress >= 6.0, PHP >= 8.2
- Elementor-Addon: ChurchTools Integration Suite >= 1.3.0.6, Elementor, WordPress >= 6.0, PHP >= 8.2
- Posts-Sync-Addon: ChurchTools Integration Suite >= 1.3.0.6, WordPress >= 5.0, PHP >= 8.2
- Presentations-Addon: ChurchTools Integration Suite, WordPress >= 6.0, PHP >= 8.0

Das Demo-Plugin und das Demo-Theme sind nicht Bestandteil des Hauptplugins und werden für Produktivinstallationen nicht benötigt.

## Letzter Plugin-Check

Der technische Basis-Check wurde lokal mit PHP 8.2.29 ausgefuehrt.

| Bereich | Gepruefte PHP-Dateien | Ergebnis |
| --- | ---: | --- |
| Hauptplugin | 119 | OK |
| Elementor-Addon | 5 | OK |
| Posts-Sync-Addon | 5 | OK |

Gepruefte Punkte:

- PHP-CLI unter Laragon laeuft lokal fehlerfrei
- PHP-Syntaxpruefung ueber Hauptplugin und beide Addons erfolgreich
- VS-Code-Workspace ist auf den lokalen PHP-Interpreter konfiguriert
- Lokale SSH-Hosts fuer Deployment und Server-Tests sind vorhanden

## Aktueller Arbeitsstand

Im Arbeitsbaum liegen aktuell uncommittete Aenderungen. Der Schwerpunkt liegt momentan auf zwei Bereichen:

1. Frontend-Filter fuer Event-Listen in Hauptplugin und Elementor-Integration
2. Sync-Bereinigung fuer geloeschte Events/Appointments im Hauptplugin

Zusaetzlich wurden alte Backup-Templates unter `templates-backup-20260222-211402/` aus dem Arbeitsbaum entfernt.

### Betroffene Bereiche im aktuellen Worktree

- Frontend-Assets: `assets/css/`, `assets/js/`
- Block- und Shortcode-Logik: `includes/class-churchtools-suite-blocks.php`, `includes/class-churchtools-suite-shortcodes.php`
- Event-Sync und Repository: `includes/repositories/`, `includes/services/`
- Elementor-Widget: separates Repository `churchtools-suite-elementor`
- Aktive List-Templates: `templates/views/event-list/`

## Entwicklung lokal und auf Servern

- Lokaler PHP-Pfad in Local: `C:\Users\KasseFeg\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe`
- Deployment-Skripte nutzen lokale SSH-Host-Aliase aus der Benutzerkonfiguration
- Standardziel der Deploy-Skripte fuer Testsysteme ist `plugin-test`

## Empfohlene naechste Schritte

1. Frontend-Filter in WordPress manuell mit echten Event-Daten pruefen
2. Sync-Cleanup mit einem Vollsync gegen ein Testsystem verifizieren
3. Danach Readme/Changelog pro Release fortschreiben und Release-ZIPs bauen

## Aktuelle Funktionen

- Kalender-, Termin-, Dienst-, Gruppen- und Gruppenpost-Synchronisation
- Shortcodes und Gutenberg-Views für Liste, Grid, Kalender, Countdown und Carousel
- Kalenderfilter über `calendar`, `calendars` und `calendar_ids`
- Custom CSS pro Shortcode, Preset und Block-Instanz
- CSS wird je Ausgabe über eine stabile Instanz-ID begrenzt
- Custom CSS kann im Shortcode-Manager und über die Block-/Shortcode-API verwendet werden
- Standalone-Addons für Elementor, Posts Sync und Presentations
- Automatische Synchronisation über WordPress-Cron
- Der Auto-Updater installiert ausschließlich geprüfte WordPress-Release-ZIPs.