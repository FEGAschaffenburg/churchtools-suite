# Carousel Templates

Horizontales Carousel mit Event-Cards.

## Verfügbare Varianten

### Classic Carousel
- **Datei:** `classic.php`
- **Stil:** Standard Carousel mit Pfeilen
- **Features:** 3-4 Cards gleichzeitig, Scroll
- **Inspiration:** Netflix-Style Carousel

### Thumbnail Carousel
- **Datei:** `thumbnail.php`
- **Stil:** Kleine Thumbnails mit aktiver Großansicht
- **Features:** Preview-Leiste, Click-to-Enlarge
- **Inspiration:** Amazon Product Carousel

### Infinite Carousel
- **Datei:** `infinite.php`
- **Stil:** Loop ohne Ende
- **Features:** Auto-Scroll, Nahtloser Übergang
- **Inspiration:** Logo-Slider / Partner-Carousel

### Centered Carousel
- **Datei:** `centered.php`
- **Stil:** Fokussierte Card in der Mitte
- **Features:** Side Cards teilweise sichtbar, Smooth Scale
- **Inspiration:** Apple App Store Carousel

### Vertical Carousel
- **Datei:** `vertical.php`
- **Stil:** Vertikales Scrollen
- **Features:** Wheel-Scroll, Touch-Friendly
- **Inspiration:** Mobile App Patterns

## Shortcode Verwendung

```
[cts_carousel template="classic" items_per_view="3" autoplay="false"]
```

## Parameter

- `template` - classic|thumbnail|infinite|centered|vertical (default: classic)
- `limit` - Anzahl Events (default: 10)
- `items_per_view` - Gleichzeitig sichtbare Cards (default: 3)
- `autoplay` - true|false (default: false)
- `speed` - Scroll-Geschwindigkeit (default: 500ms)
- `spacing` - Gap zwischen Cards (default: 20px)
- `loop` - Endlos-Loop (default: true)
