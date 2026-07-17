# ChurchTools Suite

Monorepo fuer das WordPress-Hauptplugin ChurchTools Suite und die dazugehoerigen Addons.

## Repository-Status

Stand: 2026-04-14

### Enthaltene Plugins

| Paket | Typ | Version | Einstiegspunkt | Status |
| --- | --- | --- | --- | --- |
| ChurchTools Suite | Hauptplugin | 1.2.0.7 | `churchtools-suite.php` | aktiv entwickelt |
| ChurchTools Suite - Elementor Integration | Addon | 0.6.14 | `addons/churchtools-suite-elementor/churchtools-suite-elementor.php` | aktiv entwickelt |
| ChurchTools Suite - Posts Sync Addon | Addon | 0.1.4 | `addons/churchtools-suite-posts-sync/churchtools-suite-posts-sync.php` | aktiv entwickelt |

### Abhaengigkeiten

- Hauptplugin: WordPress >= 6.0, PHP >= 8.0
- Elementor-Addon: ChurchTools Suite, Elementor, WordPress >= 6.0, PHP >= 8.0
- Posts-Sync-Addon: ChurchTools Suite >= 1.2.0.0, WordPress >= 5.0, PHP >= 8.0

## Letzter Plugin-Check

Der technische Basis-Check wurde am 2026-04-14 lokal mit PHP 8.3.13 ausgefuehrt.

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
- Elementor-Widget: `addons/churchtools-suite-elementor/includes/`
- Aktive List-Templates: `templates/views/event-list/`

## Entwicklung lokal und auf Servern

- Lokaler PHP-Pfad in VS Code: `C:\laragon\bin\php\php-8.3.13-Win32-vs16-x64\php.exe`
- Deployment-Skripte nutzen lokale SSH-Host-Aliase aus der Benutzerkonfiguration
- Standardziel der Deploy-Skripte fuer Testsysteme ist `plugin-test`

## Empfohlene naechste Schritte

1. Frontend-Filter in WordPress manuell mit echten Event-Daten pruefen
2. Sync-Cleanup mit einem Vollsync gegen ein Testsystem verifizieren
3. Danach Readme/Changelog pro Release fortschreiben und Release-ZIPs bauen