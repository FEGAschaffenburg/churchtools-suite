# Release Notes v1.2.0.2

## Übersicht
Stabilitäts- und Klarheits-Release: Elementor-spezifischer Code wurde aus dem Hauptmodul entfernt, zusätzlich wurden Debug- und Settings-Bereiche konsistenter benannt und strukturiert.

## Enthaltene Änderungen
- Core-Entkopplung von Elementor in `assets/js/churchtools-suite-public.js`
  - Entfernt: Elementor-spezifische Bridge (`elementor/frontend/init`)
  - Entfernt: Elementor-spezifische Editor-Erkennung im Core
- Health-Übersicht im Debug-Bereich (`admin/views/debug/subtab-uebersicht.php`)
- Entfernung des Settings-Subtabs `Benutzer` (`admin/views/tab-settings.php`)
- Präzisierte Begriffe für Gruppen in Services/Posts-UI

## Wirkung
- Hauptplugin bleibt ohne harte Elementor-Kopplung.
- Elementor-Editor wird nicht mehr durch Core-JavaScript beeinträchtigt, wenn das Elementor-Addon nicht installiert ist.
- Admin-Navigation und Terminologie sind konsistenter.
- Keine Datenbankmigration erforderlich.

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.2.zip`
- `churchtools-suite-elementor-0.6.11.zip`
- `churchtools-suite-posts-sync-0.1.3.zip`
