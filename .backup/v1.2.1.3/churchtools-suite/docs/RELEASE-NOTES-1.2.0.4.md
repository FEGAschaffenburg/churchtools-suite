# Release Notes v1.2.0.4

## Übersicht
Release mit Fokus auf Posts-Sync-Frontend und Editor-Stabilität: Das Posts-Sync-Addon erhält eine nutzbare Frontend-Ausgabe (Block + Shortcode), robustere Block-Registrierung sowie eine präzisere "Nur neue"-Logik mit Uhrzeit-Auswertung.

## Enthaltene Änderungen
- Posts-Sync-Frontend ergänzt
  - Neuer Gutenberg-Block "ChurchTools Berichte"
  - Neuer Shortcode `[cts_posts]`
  - Einheitliche Render-Logik für Block und Shortcode
- Block-Registrierung im Editor stabilisiert
  - Zusätzliche Fallback-Registrierung/Enqueue für inkonsistente Editor-Kontexte
- "Nur neue"-Filter verfeinert
  - Ablauf wird mit Datum und Uhrzeit ausgewertet (nicht nur tagbasiert)
- Umgebungsfreigaben im Posts-Sync-Addon vereinheitlicht
  - `local`, `development`, `staging` sowie optionales Force-Enable/Filter-Override

## Wirkung
- Beiträge aus ChurchTools können direkt im Frontend ausgegeben werden.
- Die Sichtbarkeit/Registrierung des Blocks ist in mehr Editor-Konfigurationen zuverlässig.
- Zeitkritische Inhalte verschwinden exakter nach Endzeit.
- Keine zusätzliche Datenbankmigration erforderlich.

## Artefakte (Monorepo-Release)
An dieses eine GitHub-Release anhängen:
- `churchtools-suite-1.2.0.4.zip`
- `churchtools-suite-elementor-0.6.11.zip`
- `churchtools-suite-posts-sync-0.1.4.zip`
