# Single Event Templates

Dieses Verzeichnis enthält Templates für die Anzeige einzelner Termine.

## Verfügbare Templates

- **classic.php** - Klassische strukturierte Ansicht
- **modern.php** - Modernes Design mit Hero-Header
- **minimal.php** - Minimalistische Text-fokussierte Ansicht
- **card.php** - Card-basiertes Premium-Design

## Verwendung

```php
[cts_event id="123" template="classic"]
[cts_event id="123" template="modern"]
[cts_event id="123" template="minimal"]
[cts_event id="123" template="card"]
```

## Theme Override

Um ein Template anzupassen, kopiere es in dein Theme:

```
themes/dein-theme/churchtools-suite/single/classic.php
```

## Dokumentation

Vollständige Dokumentation: `/docs/SINGLE-EVENT-TEMPLATES.md`
