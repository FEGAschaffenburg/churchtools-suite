# Slider Templates

Event-Slider mit Bildergalerie und Navigation.

## Verfügbare Varianten

### Classic Slider
- **Datei:** `classic.php`
- **Stil:** Fullwidth-Slider mit großen Event-Cards
- **Features:** Pfeile, Dots, Auto-Play
- **Inspiration:** The Events Calendar "Event Slider"

### Modern Slider
- **Datei:** `modern.php`
- **Stil:** 3D-Effekt mit Perspective
- **Features:** Swipe, Smooth Transitions
- **Inspiration:** Modern Events Calendar "Modern View"

### Minimal Slider
- **Datei:** `minimal.php`
- **Stil:** Clean Design, Fokus auf Content
- **Features:** Fade-Effekt, minimale UI
- **Inspiration:** Eventbrite Style

### Card Slider
- **Datei:** `card.php`
- **Stil:** Mehrere Cards gleichzeitig sichtbar (2-4)
- **Features:** Responsive Grid, Scroll
- **Inspiration:** Meetup.com Event Grid

### Hero Slider
- **Datei:** `hero.php`
- **Stil:** Fullscreen Hero mit Overlay
- **Features:** Background-Images, CTA-Buttons
- **Inspiration:** Eventim / Ticketmaster

## Shortcode Verwendung

```
[cts_slider template="classic" limit="5" autoplay="true" speed="3000"]
```

## Parameter

- `template` - classic|modern|minimal|card|hero (default: classic)
- `limit` - Anzahl Events (default: 10)
- `autoplay` - true|false (default: true)
- `speed` - Millisekunden zwischen Slides (default: 5000)
- `arrows` - true|false (default: true)
- `dots` - true|false (default: true)
- `show_calendar` - Kalender-Badge anzeigen (default: true)
- `show_time` - Uhrzeit anzeigen (default: true)
- `show_location` - Ort anzeigen (default: true)
