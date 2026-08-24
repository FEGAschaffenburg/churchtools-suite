# Release Notes v1.2.0.5

## Übersicht
Dieses Release fokussiert sich auf die Kompatibilität des Elementor-Addons mit neueren Elementor-Versionen (inkl. 4.x) und bringt ein robustes Editor-Verhalten bei dynamischen Kontrolloptionen.

## Enthaltene Änderungen
- Elementor-Addon auf `0.6.12` angehoben
- Editor-JS gehärtet:
  - robustere Aktualisierung der `event_id`-Optionen
  - keine direkte fragile Select2-Reinitialisierung mehr
  - stabileres Fallback bei geänderten Editor-Internals
- Addon-Readme aktualisiert (`Stable tag`, `Tested up to`)

## Wirkung
- Geringeres Risiko für Ausfälle im Elementor-Editor nach Updates
- Keine Datenbankmigration erforderlich

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.5.zip`
- `churchtools-suite-elementor-0.6.12.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
