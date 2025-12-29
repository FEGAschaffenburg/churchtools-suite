# Countdown Templates

Event-Countdown mit Zeitanzeige bis zum nächsten Event.

## Verfügbare Varianten

### Classic Countdown
- **Datei:** `classic.php`
- **Stil:** Große Zahlen mit Labels (Tage/Stunden/Minuten/Sekunden)
- **Features:** Live-Update, Flip-Animation
- **Inspiration:** Eventbrite Countdown

### Circular Countdown
- **Datei:** `circular.php`
- **Stil:** Kreisförmige Progress-Bars
- **Features:** SVG-Animation, Smooth Progress
- **Inspiration:** Modern Events Calendar "Countdown View"

### Minimal Countdown
- **Datei:** `minimal.php`
- **Stil:** Inline Countdown-Text
- **Features:** Kompakt, dezent
- **Inspiration:** Meetup "Event starts in..."

### Card Countdown
- **Datei:** `card.php`
- **Stil:** Event-Card mit integriertem Countdown
- **Features:** Event-Details + Countdown kombiniert
- **Inspiration:** Ticket-Plattformen

### Banner Countdown
- **Datei:** `banner.php`
- **Stil:** Fullwidth Banner mit Hintergrundbild
- **Features:** Hero-Image, CTA-Button, Countdown-Overlay
- **Inspiration:** Festival-Websites

## Shortcode Verwendung

```
[cts_countdown template="classic" event_id="123" show_seconds="true"]
```

## Parameter

- `template` - classic|circular|minimal|card|banner (default: classic)
- `event_id` - Spezifisches Event (default: nächstes Event)
- `show_days` - true|false (default: true)
- `show_hours` - true|false (default: true)
- `show_minutes` - true|false (default: true)
- `show_seconds` - true|false (default: true)
- `auto_hide` - Nach Event-Start ausblenden (default: false)
- `show_event_info` - Event-Details anzeigen (default: true)
