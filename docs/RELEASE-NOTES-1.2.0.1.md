# Release Notes v1.2.0.1

## Übersicht
Hotfix-Release zur Behebung eines kritischen Parse-Errors in der Grid-Template-Ansicht.

## Enthaltene Änderungen
- Fix in `templates/views/event-grid/grid-background-images.php`
- Konsistente Korrektur auch in der Backup-Template-Kopie unter `templates-backup-20260222-211402/views/event-grid/background-images.php`

## Wirkung
- Verhindert den kritischen Fehler beim Laden der betroffenen Grid-Ansicht.
- Keine Datenbankmigration erforderlich.

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.1.zip`
- `churchtools-suite-elementor-0.6.11.zip`
- `churchtools-suite-posts-sync-0.1.3.zip`
