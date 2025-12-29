# Agenda Templates

Agenda-Ansicht ähnlich Google Calendar.

## Verfügbare Varianten

### Classic Agenda
- **Datei:** `classic.php`
- **Stil:** Kompakte Liste mit Datumsgruppen
- **Features:** Tagesweise gruppiert, Expandable
- **Inspiration:** Google Calendar Agenda View

### Compact Agenda
- **Datei:** `compact.php`
- **Stil:** Ultra-kompakt, nur essenzielle Infos
- **Features:** Mobile-optimiert, Inline-Details
- **Inspiration:** iPhone Calendar List

### Timeline Agenda
- **Datei:** `timeline.php`
- **Stil:** Vertikale Timeline mit Datumsmarken
- **Features:** Chronologische Verbindungslinien
- **Inspiration:** Modern Events Calendar

### Weekly Agenda
- **Datei:** `weekly.php`
- **Stil:** Wochenübersicht mit Tagesgrenzen
- **Features:** 7-Tage-Raster, Zeitslots
- **Inspiration:** Outlook Calendar Week View

## Shortcode Verwendung

```
[cts_agenda template="classic" days="7" group_by="date"]
```

## Parameter

- `template` - classic|compact|timeline|weekly (default: classic)
- `days` - Anzahl Tage (default: 7)
- `group_by` - date|calendar|none (default: date)
- `show_past` - Vergangene Events anzeigen (default: false)
- `show_time` - Uhrzeit anzeigen (default: true)
- `expandable` - Events expandierbar (default: true)
