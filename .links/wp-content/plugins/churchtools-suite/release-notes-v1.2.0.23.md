# Release Notes v1.2.0.23

## Zusammenfassung
Dieses Release stellt den Bildimport auf echte Originaldateien um, wenn ChurchTools diese bereitstellt.

## Enthaltene Änderungen

### Originalbild-Priorisierung
- Der Sync priorisiert jetzt echte Bild-URLs in dieser Reihenfolge:
  - `originalUrl`
  - `downloadUrl`
  - `originalImageUrl`
  - `fileUrl`
  - `url`
- Erst danach werden Thumbnail-Varianten als Fallback verwendet:
  - `imageUrl`
  - `thumbnailUrl`
  - `thumbUrl`

### Wirkung
- Bei Re-Sync wird jetzt bevorzugt die echte Originaldatei importiert (sofern ChurchTools sie liefert).
- Dadurch sind Medien in WordPress nicht mehr auf kleine Vorschaubilder begrenzt, wenn eine Originalquelle vorhanden ist.

## Versionen
- Core Plugin: 1.2.0.23
- Elementor Addon: 0.6.22
- Posts Sync Addon: 0.1.4
- Presentations Addon: 0.1.0
