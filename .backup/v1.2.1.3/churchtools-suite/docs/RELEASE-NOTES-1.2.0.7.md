# Release Notes v1.2.0.7

## Übersicht
Hotfix-Release mit Fokus auf stabile Frontend-Anzeige, Elementor-Listenansicht und robusten Deploy-Prozess.

## Enthaltene Änderungen
- Frontend-Modal verbessert:
  - robuste Auswertung von `data-*` Bool-Werten
  - konsistentere Anzeige von Beschreibung/Ort/Services/Kalendername
- Elementor-Addon auf `0.6.14`:
  - Fix für fehlende Services in Listenansichten
  - robuste Switcher-Auswertung (`yes/true/1/on`)
- Deploy-Skripte verbessert:
  - automatische Rechte-Normalisierung nach Upload (`dirs 755`, `files 644`)
  - verhindert Asset-403 bei CSS/JS

## Wirkung
- Popups und Listen verhalten sich konsistenter.
- Elementor-Listen zeigen Services wieder zuverlässig an.
- Deployments sind weniger fehleranfällig.
- Keine Datenbankmigration erforderlich.

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.7.zip`
- `churchtools-suite-elementor-0.6.14.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
