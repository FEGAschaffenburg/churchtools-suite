# Masonry Templates

Pinterest-Style Masonry Grid.

## Verfügbare Varianten

### Classic Masonry
- **Datei:** `classic.php`
- **Stil:** Unregelmäßiges Grid mit variablen Höhen
- **Features:** Auto-Layout, Responsive Columns
- **Inspiration:** Pinterest / Dribbble

### Card Masonry
- **Datei:** `card.php`
- **Stil:** Event-Cards mit Bildern (variable Größen)
- **Features:** Image-Heavy, Hover-Effekte
- **Inspiration:** Unsplash Gallery

### Compact Masonry
- **Datei:** `compact.php`
- **Stil:** Dicht gepackte kleine Cards
- **Features:** Mehr Events gleichzeitig sichtbar
- **Inspiration:** Google Images Layout

## Shortcode Verwendung

```
[cts_masonry template="classic" columns="3" gap="20px"]
```

## Parameter

- `template` - classic|card|compact (default: classic)
- `limit` - Anzahl Events (default: 20)
- `columns` - Anzahl Spalten (default: 3)
- `gap` - Abstand zwischen Cards (default: 20px)
- `show_images` - Event-Bilder anzeigen (default: true)
- `image_ratio` - Seitenverhältnis (default: auto)
