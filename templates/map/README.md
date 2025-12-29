# Map Templates

Karten-Ansicht mit Event-Locations.

## Verfügbare Varianten

### Classic Map
- **Datei:** `classic.php`
- **Stil:** Google Maps mit Markern
- **Features:** Clustering, Info-Windows
- **Inspiration:** Google Maps Event Finder

### Interactive Map
- **Datei:** `interactive.php`
- **Stil:** Leaflet / OpenStreetMap
- **Features:** Custom Marker, Popup-Cards
- **Inspiration:** Modern Events Calendar Map View

### List + Map
- **Datei:** `list-map.php`
- **Stil:** Split-Screen (Liste links, Karte rechts)
- **Features:** Sync Hover, Click-to-Focus
- **Inspiration:** Airbnb / Booking.com

### Fullscreen Map
- **Datei:** `fullscreen.php`
- **Stil:** Fullpage Map mit Sidebar
- **Features:** Filter-Sidebar, Drawer-Events
- **Inspiration:** Foursquare / Yelp

## Shortcode Verwendung

```
[cts_map template="classic" center="49.9737,9.1510" zoom="12"]
```

## Parameter

- `template` - classic|interactive|list-map|fullscreen (default: classic)
- `center` - Karten-Zentrum (lat,lng) (default: auto)
- `zoom` - Zoom-Level 1-20 (default: 12)
- `height` - Map-Höhe (default: 500px)
- `cluster` - Marker gruppieren (default: true)
- `show_list` - Event-Liste anzeigen (default: false)
- `geolocation` - User-Standort nutzen (default: false)

## Hinweise

- **Geocoding:** Event-Locations müssen Koordinaten haben (wird beim Sync aus `location_name` generiert)
- **API-Key:** Google Maps erfordert API-Key in Einstellungen
- **Alternative:** OpenStreetMap (Leaflet) als kostenlose Option
