# Cover Templates

Fullscreen Event-Cover mit Hintergrundbild und Overlay.

## Verfügbare Varianten

### Classic Cover
- **Datei:** `classic.php`
- **Stil:** Fullscreen Background mit Centered Content
- **Features:** Parallax-Effekt, Dark Overlay
- **Inspiration:** Eventbrite Event Pages

### Modern Cover
- **Datei:** `modern.php`
- **Stil:** Split-Screen (Bild links, Content rechts)
- **Features:** Gradient Overlay, Asymmetrisch
- **Inspiration:** Modern Events Calendar "Single Event Modern"

### Minimal Cover
- **Datei:** `minimal.php`
- **Stil:** Clean Header mit subtiler Overlay
- **Features:** Light Theme, Fokus auf Typography
- **Inspiration:** Apple Events Style

### Video Cover
- **Datei:** `video.php`
- **Stil:** Video-Background mit Controls
- **Features:** Autoplay Video, Mute/Unmute
- **Inspiration:** TED Events

### Slideshow Cover
- **Datei:** `slideshow.php`
- **Stil:** Wechselnde Hintergrundbilder
- **Features:** Fade-Transition, Multiple Images
- **Inspiration:** Festival-Websites

## Shortcode Verwendung

```
[cts_cover event_id="123" template="classic" height="600px"]
```

## Parameter

- `template` - classic|modern|minimal|video|slideshow (default: classic)
- `event_id` - Spezifisches Event (required)
- `height` - Cover-Höhe (default: 500px)
- `overlay_opacity` - 0-1 (default: 0.5)
- `show_calendar` - Kalender-Badge anzeigen (default: true)
- `show_cta` - Call-to-Action Button (default: true)
- `parallax` - Parallax-Effekt (default: false)
