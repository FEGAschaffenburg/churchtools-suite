# Release Notes v1.2.0.0

## Übersicht
Dieses Release führt die neue Sync-Modul-Plattform ein und vereinheitlicht die Sichtbarkeit und Statusanzeige von Core- und Addon-Synchronisationen im Backend.

## Highlights
- Neue Core-Modul-Registry für Synchronisationsmodule
- Neue Sync-Runtime mit Statusmodell und Locking
- Posts-Sync Addon in den Modul-Contract integriert
- Modulstatus in beiden relevanten Bereichen sichtbar:
  - Synchronisation
  - Einstellungen > Synchronisation
- Einheitliche, lesbare Statuslabels: `Bereit`, `Läuft`, `OK`, `Fehler`, `Deaktiviert`

## Technische Änderungen
- Core:
  - `includes/class-churchtools-suite-sync-modules.php`
  - `includes/class-churchtools-suite-sync-runtime.php`
- Admin UI:
  - `admin/views/tab-sync.php`
  - `admin/views/settings/subtab-sync.php`
- Posts-Sync Addon Contract-Anbindung:
  - `addons/churchtools-suite-posts-sync/churchtools-suite-posts-sync.php`

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.0.zip`
- `churchtools-suite-elementor-0.6.11.zip`
- `churchtools-suite-posts-sync-0.1.3.zip`

## Hinweise
- Runtime-Addon-Ordner wurden vor Build mit Monorepo-Quellen synchronisiert.
- Keine Datenbankstruktur-Änderung erforderlich für diese Funktionserweiterung.
