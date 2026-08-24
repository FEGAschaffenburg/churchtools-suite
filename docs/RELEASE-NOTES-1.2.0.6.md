# Release Notes v1.2.0.6

## Übersicht
Hotfix-Release für den Elementor-Addon-Updater. Ziel ist, dass verfügbare Addon-Updates zuverlässig im WordPress-Backend angezeigt werden.

## Enthaltene Änderungen
- Elementor-Addon auf `0.6.13` angehoben
- Updater verbessert:
  - Cache-Refresh auf `Plugins`- und `Update`-Seite
  - Löschen stale Update-Transients vor erneuter Prüfung
  - direkter neuer Plugin-Update-Check über WordPress-Mechanik

## Wirkung
- Verfügbare Updates für `churchtools-suite-elementor` werden zuverlässiger erkannt.
- Keine Datenbankmigration erforderlich.

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.6.zip`
- `churchtools-suite-elementor-0.6.13.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
