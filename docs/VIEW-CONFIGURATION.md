# View Configuration Reference

> **Version:** 0.9.9.20  
> **Letzte Aktualisierung:** 8. Januar 2026

Dieses Dokument beschreibt die Standard-Einstellungen und Style-Logik aller View-Templates im ChurchTools Suite Plugin.

---

## 📋 Inhaltsverzeichnis

1. [Übersicht](#übersicht)
2. [Style-System](#style-system)
3. [View-Spezifische Konfigurationen](#view-spezifische-konfigurationen)
4. [Display Options (Toggles)](#display-options-toggles)
5. [Kalenderfarben-Logik](#kalenderfarben-logik)
6. [Event-Actions](#event-actions)

---

## Übersicht

Das Plugin verwendet ein konsistentes Konfigurationssystem über alle Views hinweg. Jede View unterstützt:

- **Display Options** (Toggles für Anzeige-Elemente)
- **Style-Modes** (theme, plugin, custom)
- **Kalenderfarben-Option** (`use_calendar_colors`)
- **Event-Actions** (modal, page, none)

---

## Style-System

### Style-Modes

Das Plugin unterstützt drei Style-Modi:

| Mode | Beschreibung | CSS-Variablen |
|------|-------------|---------------|
| `theme` | Verwendet Theme-Farben (Standard) | Nutzt WordPress Theme CSS |
| `plugin` | Verwendet Plugin-Standardfarben | Vordefinierte Plugin-Farben |
| `custom` | Benutzerdefinierte Farben | Individuell konfigurierbar |

**Custom-Mode Parameter:**
- `custom_primary_color` (Default: `#2563eb`)
- `custom_text_color` (Default: `#1e293b`)
- `custom_background_color` (Default: `#ffffff`)
- `custom_border_radius` (Default: `6`)
- `custom_font_size` (Default: `14`)
- `custom_padding` (Default: `12`)
- `custom_spacing` (Default: `16`)

### CSS-Variablen-Hierarchie

```
--cts-primary-color
--cts-text-color
--cts-bg-color
--cts-border-radius
--cts-font-size
--cts-padding
--cts-spacing
```

**Wichtig:** Diese Variablen werden NUR gesetzt, wenn `style_mode='custom'`

---

## View-Spezifische Konfigurationen

### 📄 Liste Classic (`list/classic`)

**Beschreibung:** Kompakte einzeilige Liste mit Datum-Box

**Standard-Werte:**

| Option | Standard | Typ | Beschreibung |
|--------|----------|-----|--------------|
| `show_event_description` | `true` | Boolean | Event-Beschreibung anzeigen |
| `show_appointment_description` | `true` | Boolean | Termin-Beschreibung anzeigen |
| `show_location` | `true` | Boolean | Ort anzeigen |
| `show_services` | `false` | Boolean | Dienste anzeigen |
| `show_time` | `true` | Boolean | Uhrzeit anzeigen |
| `show_tags` | `true` | Boolean | Tags anzeigen |
| `show_calendar_name` | `true` | Boolean | Kalendername anzeigen |
| `show_month_separator` | `true` | Boolean | Monatstrennlinie anzeigen |
| `use_calendar_colors` | `false` | Boolean | Kalenderfarben verwenden |
| `event_action` | `modal` | String | Event-Klick-Aktion |

**Kalenderfarben-Effekte (wenn `use_calendar_colors=true`):**

| Element | Effekt |
|---------|--------|
| Datum-Box | Hintergrundfarbe = Kalenderfarbe, Textfarbe = Auto (hell/dunkel) |
| Kalendername | Textfarbe = Kalenderfarbe |
| CSS-Variable `--calendar-color` | Gesetzt auf Event-Container |
| CSS-Variable `--cts-primary-color` | Überschrieben mit Kalenderfarbe |

**Technische Details:**
- Luminanz-Berechnung für Datum-Box Textfarbe (W3C-Formel)
- `luminance > 128` → Dunkle Schrift (`#1e293b`)
- `luminance ≤ 128` → Helle Schrift (`#ffffff`)

---

### 📄 Liste Minimal (`list/minimal`)

**Beschreibung:** Ultra-kompakte Liste ohne Datum-Box

**Standard-Werte:**

| Option | Standard | Typ | Beschreibung |
|--------|----------|-----|--------------|
| `show_event_description` | `false` | Boolean | Event-Beschreibung anzeigen |
| `show_appointment_description` | `false` | Boolean | Termin-Beschreibung anzeigen |
| `show_location` | `true` | Boolean | Ort anzeigen (als Icon mit Tooltip) |
| `show_services` | `false` | Boolean | Dienste anzeigen |
| `show_time` | `true` | Boolean | Uhrzeit anzeigen |
| `show_tags` | `true` | Boolean | Tags anzeigen |
| `show_calendar_name` | `false` | Boolean | Kalendername anzeigen |
| `show_month_separator` | `true` | Boolean | Monatstrennlinie anzeigen |
| `use_calendar_colors` | `false` | Boolean | Kalenderfarben verwenden |
| `event_action` | `modal` | String | Event-Klick-Aktion |

**Kalenderfarben-Effekte (wenn `use_calendar_colors=true`):**

| Element | Effekt |
|---------|--------|
| Event-Container | Background-Gradient (15% → 8% Transparenz), Border-Left (3px solid) |
| Kalendername | Textfarbe = Kalenderfarbe, Font-Weight = 600 |

**Technische Details:**
- Gradient: `linear-gradient(135deg, {color}15 0%, {color}08 100%)`
- Border-Left: `3px solid {color}`

---

### 📄 Liste Modern (`list/modern`)

**Beschreibung:** Moderne Card-basierte Liste mit Datum-Badge

**Standard-Werte:**

| Option | Standard | Typ | Beschreibung |
|--------|----------|-----|--------------|
| `show_event_description` | `true` | Boolean | Event-Beschreibung anzeigen |
| `show_appointment_description` | `true` | Boolean | Termin-Beschreibung anzeigen |
| `show_location` | `true` | Boolean | Ort anzeigen |
| `show_services` | `false` | Boolean | Dienste anzeigen |
| `show_time` | `true` | Boolean | Uhrzeit anzeigen |
| `show_tags` | `true` | Boolean | Tags anzeigen |
| `show_calendar_name` | `true` | Boolean | Kalendername anzeigen |
| `show_month_separator` | `true` | Boolean | Monatstrennlinie anzeigen |
| `use_calendar_colors` | `false` | Boolean | Kalenderfarben verwenden |
| `event_action` | `modal` | String | Event-Klick-Aktion |

**Kalenderfarben-Effekte (wenn `use_calendar_colors=true`):**

| Element | Effekt |
|---------|--------|
| Datum-Badge | Background-Gradient (100% → 80% Transparenz), Border-Color, Textfarbe = Weiß |
| Kalendername | Textfarbe = Kalenderfarbe, Font-Weight = 600 |
| CSS-Variable `--calendar-color` | Gesetzt auf Event-Container |
| CSS-Variable `--cts-primary-color` | Überschrieben mit Kalenderfarbe |

**Technische Details:**
- Gradient: `linear-gradient(135deg, {color} 0%, {color}cc 100%)`

---

### 🎴 Grid Simple (`grid/simple`)

**Beschreibung:** Card-Grid mit konfigurierbarer Spaltenanzahl

**Standard-Werte:**

| Option | Standard | Typ | Beschreibung |
|--------|----------|-----|--------------|
| `columns` | `3` | Integer (1-6) | Anzahl Spalten |
| `show_event_description` | `true` | Boolean | Event-Beschreibung anzeigen |
| `show_appointment_description` | `true` | Boolean | Termin-Beschreibung anzeigen |
| `show_location` | `true` | Boolean | Ort anzeigen |
| `show_services` | `false` | Boolean | Dienste anzeigen |
| `show_time` | `true` | Boolean | Uhrzeit anzeigen |
| `show_tags` | `true` | Boolean | Tags anzeigen |
| `show_calendar_name` | `true` | Boolean | Kalendername anzeigen |
| `use_calendar_colors` | `false` | Boolean | Kalenderfarben verwenden |
| `event_action` | `modal` | String | Event-Klick-Aktion |

**Kalenderfarben-Effekte (wenn `use_calendar_colors=true`):**

| Element | Effekt |
|---------|--------|
| Card-Header | Background-Gradient (100% → 87% Transparenz), Textfarbe = Weiß |
| Titel | Textfarbe = Kalenderfarbe |
| Kalendername | Textfarbe = Kalenderfarbe, Font-Weight = 600 |
| CSS-Variable `--calendar-color` | Gesetzt auf Card-Container |
| CSS-Variable `--cts-primary-color` | Überschrieben mit Kalenderfarbe |

**Technische Details:**
- Gradient: `linear-gradient(135deg, {color} 0%, {color}dd 100%)`
- Grid CSS: `grid-template-columns: repeat(var(--grid-columns), 1fr)`

---

### 📅 Calendar Monthly Simple (`calendar/monthly-simple`)

**Beschreibung:** Klassischer Monatskalender mit Event-Markern

**Standard-Werte:**

| Option | Standard | Typ | Beschreibung |
|--------|----------|-----|--------------|
| `use_calendar_colors` | `false` | Boolean | Kalenderfarben verwenden |
| `event_action` | `modal` | String | Event-Klick-Aktion |

**Kalenderfarben-Effekte (wenn `use_calendar_colors=true`):**

| Element | Effekt |
|---------|--------|
| Event-Marker | Background = Kalenderfarbe |
| Tooltip | Background = Kalenderfarbe, Textfarbe = Weiß |
| Tooltip-Arrow | Border-Color = Kalenderfarbe |
| CSS-Variable `--calendar-color` | Gesetzt auf Event-Marker |
| CSS-Variable `--cts-primary-color` | Gesetzt auf Event-Marker |

**Technische Details:**
- Event-Marker: `background: var(--calendar-color, var(--cts-primary-color, #2563eb))`
- Tooltip: `background: var(--calendar-color, var(--cts-primary-color, #1e293b))`
- Fallback-Hierarchie: Kalenderfarbe → Style-Mode → Hardcoded

---

## Display Options (Toggles)

### Boolean-Parsing

Alle Display-Options unterstützen flexible Boolean-Werte:

**True-Werte:**
- `true`, `'true'`, `'1'`, `1`, `'yes'`, `'on'`

**False-Werte:**
- `false`, `'false'`, `'0'`, `0`, `'no'`, `'off'`, `null`, `''`

**Beispiel:**
```php
[churchtools_events view="list-classic" show_location="yes" show_services="1"]
```

### Standard-Toggles (alle Views)

| Toggle | Beschreibung | Classic | Minimal | Modern | Grid |
|--------|--------------|---------|---------|--------|------|
| `show_event_description` | Event-Beschreibung | ✅ | ❌ | ✅ | ✅ |
| `show_appointment_description` | Termin-Beschreibung | ✅ | ❌ | ✅ | ✅ |
| `show_location` | Ort/Location | ✅ | ✅ | ✅ | ✅ |
| `show_services` | Dienste/Services | ❌ | ❌ | ❌ | ❌ |
| `show_time` | Uhrzeit | ✅ | ✅ | ✅ | ✅ |
| `show_tags` | Tags | ✅ | ✅ | ✅ | ✅ |
| `show_calendar_name` | Kalendername | ✅ | ❌ | ✅ | ✅ |
| `show_month_separator` | Monatstrennlinie | ✅ | ✅ | ✅ | - |

**Legende:**
- ✅ = Standardmäßig aktiviert (`true`)
- ❌ = Standardmäßig deaktiviert (`false`)
- `-` = Nicht verfügbar

---

## Kalenderfarben-Logik

### Grundprinzip (v0.9.9.20)

```
IF use_calendar_colors = FALSE (Standard):
  → KEINE Inline-Styles werden gesetzt
  → Style-Mode CSS hat Vorrang
  → Kalenderfarben werden NICHT verwendet
  
IF use_calendar_colors = TRUE:
  → Inline-Styles für --calendar-color werden gesetzt
  → Inline-Styles für --cts-primary-color werden gesetzt
  → Kalenderfarben überschreiben Style-Mode
```

### CSS-Variablen-Fallback-Kette

Alle Views verwenden eine konsistente Fallback-Hierarchie:

```css
/* Event-Marker/Container */
background: var(--calendar-color, var(--cts-primary-color, #2563eb));

/* 1. Versuche --calendar-color (gesetzt wenn use_calendar_colors=true)
   2. Fallback zu --cts-primary-color (Style-Mode Farbe)
   3. Fallback zu #2563eb (Hardcoded Default) */
```

### Inline-Style-Regel (v0.9.9.20)

**Vorher (bis v0.9.9.19):**
```php
// PROBLEM: --calendar-color wurde IMMER gesetzt!
style="--calendar-color: <?php echo $calendar_color; ?>;"
```

**Jetzt (ab v0.9.9.20):**
```php
// RICHTIG: Inline-Styles nur bei use_calendar_colors=true
$event_inline_style = '';
if ( $use_calendar_colors ) {
    $event_inline_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"',
        esc_attr( $calendar_color ),
        esc_attr( $calendar_color )
    );
}
echo $event_inline_style;
```

### Betroffene Elemente pro View

#### Liste Classic
- ✅ Event-Container (`--calendar-color`, `--cts-primary-color`)
- ✅ Datum-Box (`background-color`, `color` mit Luminanz-Check)
- ✅ Kalendername (`color`)

#### Liste Minimal
- ✅ Event-Container (`background` Gradient, `border-left`)
- ✅ Kalendername (`color`)

#### Liste Modern
- ✅ Event-Container (`--calendar-color`, `--cts-primary-color`)
- ✅ Datum-Badge (`background` Gradient, `color`, `border-color`)
- ✅ Kalendername (`color`)

#### Grid Simple
- ✅ Card-Container (`--calendar-color`, `--cts-primary-color`)
- ✅ Card-Header (`background` Gradient, `color`)
- ✅ Titel (`color`)
- ✅ Kalendername (`color`)

#### Calendar Monthly Simple
- ✅ Event-Marker (`--calendar-color`, `--cts-primary-color`)
- ✅ Tooltip (`background`)
- ✅ Tooltip-Arrow (`border-bottom-color`)

---

## Event-Actions

Definiert, was beim Klick auf ein Event passiert.

### Verfügbare Actions

| Action | Beschreibung | Verhalten |
|--------|--------------|-----------|
| `modal` | Modal/Lightbox (Standard) | Öffnet Details-Modal ohne Seitenwechsel |
| `page` | Seiten-Navigation | Navigiert zur Event-Detail-Seite |
| `none` | Keine Aktion | Event ist nicht klickbar |

### Technische Implementierung

#### Modal
```php
$event_class = 'cts-event-clickable';
$event_attrs = sprintf(
    'data-event-id="%s" role="button" tabindex="0"',
    esc_attr( $event['id'] )
);
```

#### Page
```php
$event_class = 'cts-event-page-link';
$event_attrs = sprintf(
    'data-event-id="%s" role="link" tabindex="0"',
    esc_attr( $event['id'] )
);
```

#### None
```php
$event_class = '';
$event_attrs = '';
```

### JavaScript-Handler

**Modal:** `churchtools-suite-public.js`
```javascript
$(document).on('click', '.cts-event-clickable', function(e) {
    if ($(this).hasClass('cts-event-page-link')) {
        return; // Skip if page-link
    }
    // Open modal...
});
```

**Page:** `churchtools-suite-public.js`
```javascript
$(document).on('click', '.cts-event-page-link', function(e) {
    e.preventDefault();
    const eventId = $(this).data('event-id');
    window.location.href = '?event_id=' + eventId;
});
```

---

## Migration Notes

### v0.9.9.14 → v0.9.9.15
- **CSS Fix:** Entfernt `!important` aus `.cts-date-box` Rules
- **Grund:** Inline-Styles wurden überschrieben
- **Betroffene Dateien:** `assets/css/churchtools-suite-public.css`

### v0.9.9.15 → v0.9.9.16
- **Tooltip Fix:** Calendar-Tooltips nutzen Kalenderfarben
- **Betroffene Elemente:** `.cts-event-tooltip`, `.cts-event-tooltip::before`

### v0.9.9.16 → v0.9.9.17
- **Calendar Fix:** `--calendar-color` nur bei `use_calendar_colors=true`
- **Betroffene Dateien:** `templates/calendar/monthly-simple.php`

### v0.9.9.17 → v0.9.9.18
- **Overflow Fix:** `.cts-calendar-view` auf `overflow: visible`
- **Grund:** Tooltips wurden abgeschnitten

### v0.9.9.18 → v0.9.9.19
- **Overflow Fix:** `.cts-calendar-monthly-simple` auf `overflow: visible`
- **Fallback Fix:** Verschachtelte CSS-Variablen für Style-Mode-Unterstützung

### v0.9.9.19 → v0.9.9.20
- **Konsistenz-Fix:** ALLE Views respektieren `use_calendar_colors` korrekt
- **Betroffene Dateien:** 
  - `templates/list/classic.php`
  - `templates/list/minimal.php`
  - `templates/list/modern.php`
  - `templates/grid/simple.php`
- **Änderung:** Inline-Styles nur bei `use_calendar_colors=true`

---

## Best Practices

### 1. Style-Mode Wählen

**Empfehlung:**
- `theme` für WordPress-Theme-Integration (Standard)
- `plugin` für konsistentes Look-and-Feel
- `custom` für individuelle Anpassungen

### 2. Kalenderfarben Nutzen

**Use Case:**
- Multiple Kalender mit unterschiedlichen Farben
- Visuelle Unterscheidung wichtig

**Shortcode-Beispiel:**
```php
[churchtools_events view="list-classic" use_calendar_colors="true"]
```

### 3. Display Options Optimieren

**Performance:**
- Deaktiviere nicht benötigte Elemente
- Beispiel: `show_services="false"` wenn keine Dienste vorhanden

### 4. Event-Actions Auswählen

**Empfehlung:**
- `modal` für moderne UX (kein Seitenwechsel)
- `page` für SEO-optimierte Event-Details
- `none` für reine Anzeige ohne Interaktion

---

## Troubleshooting

### Problem: Kalenderfarben werden nicht angezeigt

**Lösung:**
1. Prüfen: `use_calendar_colors="true"` gesetzt?
2. Prüfen: Browser-Cache leeren
3. Prüfen: CSS-Datei korrekt geladen?

### Problem: Style-Mode wird ignoriert

**Ursache:** `use_calendar_colors=true` überschreibt Style-Mode

**Lösung:** `use_calendar_colors="false"` setzen

### Problem: Tooltips nicht sichtbar

**Ursache:** Container mit `overflow: hidden`

**Lösung:** Plugin auf v0.9.9.18+ aktualisieren

---

## Technische Referenz

### CSS-Klassen-Konventionen

| Präfix | Verwendung | Beispiel |
|--------|-----------|----------|
| `cts-` | Alle Plugin-Klassen | `.cts-event` |
| `cts-list-` | Liste-Views | `.cts-list-classic` |
| `cts-grid-` | Grid-Views | `.cts-grid-card` |
| `cts-calendar-` | Calendar-Views | `.cts-calendar-day` |
| `cts-event-` | Event-Elemente | `.cts-event-clickable` |

### Data-Attribute

| Attribut | Typ | Beschreibung |
|----------|-----|--------------|
| `data-event-id` | String | ChurchTools Event-ID |
| `data-event-title` | String | Event-Titel |
| `data-event-start` | Datetime | Start-Datum/-Zeit |
| `data-event-location` | String | Ort/Location |
| `data-event-description` | String | Beschreibung |
| `data-style-mode` | String | Aktiver Style-Mode |
| `data-show-*` | Boolean (0/1) | Display-Option-Status |

---

**Dokumentations-Ende**  
Bei Fragen oder Feedback bitte ein Issue auf GitHub erstellen: https://github.com/FEGAschaffenburg/churchtools-suite/issues
