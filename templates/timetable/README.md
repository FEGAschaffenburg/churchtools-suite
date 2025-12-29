# Timetable Templates

Zeitplan-Ansicht für Events (z.B. Konferenz-Schedule).

## Verfügbare Varianten

### Classic Timetable
- **Datei:** `classic.php`
- **Stil:** Tabellen-Layout mit Zeitachse
- **Features:** Stunden-Grid, Multi-Track
- **Inspiration:** Konferenz-Websites (WordCamp, etc.)

### Timeline Timetable
- **Datei:** `timeline.php`
- **Stil:** Vertikale Timeline mit Zeitmarken
- **Features:** Chronologische Darstellung, Verbindungslinien
- **Inspiration:** Modern Events Calendar "Timeline View"

### Compact Timetable
- **Datei:** `compact.php`
- **Stil:** Liste mit Zeitangaben (kein Grid)
- **Features:** Mobile-First, platzsparend
- **Inspiration:** Google Calendar Agenda View

### Multi-Day Timetable
- **Datei:** `multi-day.php`
- **Stil:** Tabs pro Tag, Grid pro Tag
- **Features:** Tagswechsel, Overlapping Events
- **Inspiration:** Festival-Schedules

### Kanban Timetable
- **Datei:** `kanban.php`
- **Stil:** Spalten pro Ort/Track
- **Features:** Drag & Drop (View-Only), Color-Coding
- **Inspiration:** Trello-Style Event Planning

## Shortcode Verwendung

```
[cts_timetable template="classic" date="2025-12-25" show_tracks="true"]
```

## Parameter

- `template` - classic|timeline|compact|multi-day|kanban (default: classic)
- `date` - Spezifisches Datum (default: heute)
- `date_range` - Mehrere Tage (default: 1)
- `show_tracks` - Mehrere Spalten/Räume (default: false)
- `time_format` - 24h|12h (default: 24h)
- `hour_start` - Startzeit Grid (default: 8)
- `hour_end` - Endzeit Grid (default: 20)
- `show_breaks` - Pausen anzeigen (default: false)
