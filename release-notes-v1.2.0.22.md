# Release Notes v1.2.0.22

## Zusammenfassung
Dieses Release behebt den Bildimport beim Re-Sync und verbessert die Übernahme des ursprünglichen Dateinamens.

## Enthaltene Änderungen

### Re-Sync Bildverhalten
- Bilder werden beim Re-Sync jetzt immer neu importiert (Event + Appointment).
- Vorherige CTS-importierte Bilder werden vor dem Re-Import ersetzt, damit keine veralteten Bilder bestehen bleiben.

### Dateiname beim Import
- Dateiname wird robuster aus ChurchTools übernommen:
  - aus explizitem Bildnamen,
  - aus URL-Query-Parametern (`filename`, `fileName`, `name`, `file`, `download`),
  - aus URL-Pfad als Fallback.
- Ziel-Datei wird mit korrekter Bild-Endung gespeichert (kein fehlerhaftes `.tmp`-Suffix).

## Versionen
- Core Plugin: 1.2.0.22
- Elementor Addon: 0.6.22
- Posts Sync Addon: 0.1.4
- Presentations Addon: 0.1.0
