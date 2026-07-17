# View Feature Support Matrix

## Übersicht

Die ChurchTools Suite implementiert ein **Feature Support System**, das sicherstellt, dass Anzeigeoptionen konsistent in allen Interfaces (Gutenberg & Elementor) angezeigt werden, aber automatisch deaktiviert werden, wenn eine View das Feature nicht unterstützt.

**Beispiel:** Die "Minimal"-View zeigt keine Bilder an. Der "Bilder anzeigen" Toggle wird im Editor angezeigt, ist aber ausgegraut/deaktiviert.

---

## Feature-Matrix Datei

**Datei:** `includes/view-feature-matrix.php`

Definiert für jede View, welche Features unterstützt werden:

```php
'minimal' => [
    'show_event_description' => true,      // ✅ Aber nur im Popup
    'show_appointment_description' => true, // ✅ Aber nur im Popup
    'show_location' => false,              // ❌ Keine inline Location
    'show_services' => false,              // ❌ Keine Services
    'show_time' => true,                   // ✅
    'show_tags' => false,                  // ❌ Keine Tags
    'show_images' => false,                // ❌ Keine Bilder
    'show_calendar_name' => false,         // ❌ Nur im Popup
    'show_month_separator' => true,        // ✅
],
```

---

## Unterstützte Views & Features

### 📝 **List Views**

| Feature | Classic | Classic-with-Images | Minimal | Modern (Row) |
|---------|---------|---------------------|---------|--------------|
| Event-Beschreibung | ✅ | ✅ | ✅ Popup | ✅ |
| Termin-Beschreibung | ✅ | ✅ | ✅ Popup | ✅ |
| Ort | ✅ | ✅ | ❌ | ✅ |
| Services | ✅ | ✅ | ❌ | ✅ |
| Uhrzeit | ✅ | ✅ | ✅ | ✅ |
| Tags | ✅ | ✅ | ❌ | ✅ |
| Bilder | ❌ | ✅ | ❌ | ❌ |
| Kalendername | ✅ | ✅ | ❌ Popup | ✅ |
| Monatstrenner | ✅ | ✅ | ✅ | ✅ |

### 🎯 **Grid Views**

| Feature | Simple | Modern Grid |
|---------|--------|-------------|
| Event-Beschreibung | ✅ | ✅ |
| Termin-Beschreibung | ✅ | ✅ |
| Ort | ✅ | ✅ |
| Services | ✅ | ✅ |
| Uhrzeit | ✅ | ✅ |
| Tags | ❌ | ✅ |
| Bilder | ✅ | ✅ |
| Kalendername | ✅ | ✅ |
| Monatstrenner | ❌ | ❌ |

### 📅 **Calendar Views**

| Feature | Monthly Simple |
|---------|----------------|
| Event-Beschreibung | ❌ |
| Termin-Beschreibung | ❌ |
| Ort | ❌ |
| Services | ❌ |
| Uhrzeit | ✅ |
| Tags | ❌ |
| Bilder | ❌ |
| Kalendername | ❌ |
| Monatstrenner | ❌ |

---

## Integration in Gutenberg

**Datei:** `assets/js/churchtools-suite-blocks.js`

### Feature-Matrix wird an JavaScript übergeben

```javascript
// In PHP (class-churchtools-suite-blocks.php)
wp_localize_script( 'churchtools-suite-blocks', 'churchtoolsSuiteBlocks', [
    'viewFeatures' => churchtools_suite_get_view_features(),
    // ...
] );
```

### Toggles werden dynamisch deaktiviert

```javascript
// Get feature matrix
const viewFeatures = window.churchtoolsSuiteBlocks?.viewFeatures || {};
const currentViewFeatures = viewFeatures[attributes.view] || {};

// Helper function
const isFeatureSupported = function(featureName) {
    return currentViewFeatures[featureName] !== false;
};

// Toggle Control mit disabled Attribut
el(ToggleControl, {
    label: __('Bilder', 'churchtools-suite'),
    checked: attributes.show_images,
    disabled: !isFeatureSupported('show_images'), // ← Deaktiviert wenn nicht unterstützt
    help: getDisabledHelpText('show_images'),
    onChange: function(value) {
        setAttributes({ show_images: value });
    }
})
```

### Verhalten

- ✅ Alle Toggles werden **immer angezeigt** (konsistentes Interface)
- ⚠️ Nicht unterstützte Toggles sind **ausgegraut/disabled**
- 💡 Help-Text erscheint: "Diese Option wird von der aktuellen View nicht unterstützt"
- 🔄 Toggles werden **dynamisch aktualisiert** wenn View geändert wird

---

## Integration in Elementor

**Datei:** `includes/elementor/class-churchtools-suite-elementor-events-widget.php`

### Ansatz: Description-Texte statt Disabled

Elementor unterstützt kein natives `disabled` Attribut für Controls. Lösung:

1. **Alle Toggles werden OHNE Conditions angezeigt** (konsistentes Interface)
2. **Description-Texte** erklären View-Einschränkungen

```php
$this->add_control(
    'show_images',
    [
        'label' => __( 'Bilder', 'churchtools-suite' ),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => __( 'Ja', 'churchtools-suite' ),
        'label_off' => __( 'Nein', 'churchtools-suite' ),
        'default' => 'yes',
        'description' => __( 'Nur verfügbar in: Classic-with-Images, Grid-Views. Nicht in Classic, Minimal, Modern (Row-Layout)', 'churchtools-suite' ),
    ]
);
```

### Section-Level Notice

```php
$this->start_controls_section(
    'display_section',
    [
        'label' => __( 'Anzeigeoptionen', 'churchtools-suite' ),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        'description' => __( '💡 Hinweis: Nicht alle Optionen werden von jeder View unterstützt. Minimal-View unterstützt z.B. keine Bilder, Services oder inline Location-Anzeige.', 'churchtools-suite' ),
    ]
);
```

---

## Vorteile des Systems

### ✅ **Konsistentes Interface**
- Alle User sehen die gleichen Optionen, unabhängig von der gewählten View
- Keine verwirrenden "versteckten" Toggles

### 📚 **Lerneffekt**
- User sehen alle verfügbaren Features
- Deaktivierte Features signalisieren: "Diese View unterstützt das nicht"
- Help-Texte erklären warum

### 🔧 **Wartbarkeit**
- Zentrale Feature-Matrix in einer Datei
- Neue Views: Einfach Matrix-Eintrag hinzufügen
- Keine duplizierten Conditions in UI-Code

### 🎯 **Backwards Compatibility**
- Templates erhalten weiterhin alle Parameter
- Ignorieren nicht unterstützte Features einfach
- Kein Breaking Change für bestehende Shortcodes

---

## Neue View hinzufügen

### 1. Feature-Matrix erweitern

```php
// includes/view-feature-matrix.php
'meine-neue-view' => [
    'show_event_description' => true,
    'show_appointment_description' => true,
    'show_location' => true,
    'show_services' => false,  // ❌ Diese View zeigt keine Services
    'show_time' => true,
    'show_tags' => true,
    'show_images' => true,
    'show_calendar_name' => true,
    'show_month_separator' => true,
],
```

### 2. View-Template erstellen

```php
// templates/views/event-list/meine-neue-view.php
<?php
// Parse Parameter wie gewohnt
$show_event_description = isset( $args['show_event_description'] ) 
    ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) 
    : true;

// Services werden ignoriert, da nicht in Feature-Matrix
// ...
```

### 3. Fertig! 🎉

- Gutenberg: Toggles werden automatisch deaktiviert
- Elementor: Description-Texte sollten angepasst werden (optional)
- Template: Ignoriert einfach nicht unterstützte Parameter

---

## Helper-Funktionen

```php
// Prüfen ob Feature unterstützt wird
if ( churchtools_suite_view_supports( 'minimal', 'show_images' ) ) {
    // true/false
}

// Alle unterstützten Features einer View
$features = churchtools_suite_get_view_supported_features( 'classic' );
// Returns: ['show_event_description', 'show_location', ...]

// Alle NICHT unterstützten Features
$disabled = churchtools_suite_get_view_disabled_features( 'minimal' );
// Returns: ['show_services', 'show_images', ...]

// Komplette Matrix
$all_features = churchtools_suite_get_view_features();
```

---

## Testing

### Gutenberg Block
1. Neuen Block einfügen: **ChurchTools Events**
2. View wechseln: Classic → Minimal → Modern
3. ✅ Anzeige-Optionen Panel sollte immer sichtbar bleiben
4. ✅ Bei Minimal sollten "Bilder", "Services", "Tags" deaktiviert sein
5. ✅ Help-Text: "Diese Option wird von der aktuellen View nicht unterstützt"

### Elementor Widget
1. Neues Widget einfügen: **ChurchTools Events**
2. View wechseln zwischen verschiedenen Templates
3. ✅ Alle Anzeigeoptionen immer sichtbar
4. ✅ Description-Texte erklären Einschränkungen
5. ✅ Section-Level Notice am Anfang: "Nicht alle Optionen..."

---

## Change Log

**v1.0.6.0 (15. Feb 2026)**
- ✨ Feature-Matrix System implementiert
- 🎨 Gutenberg: Dynamisches disabled Attribut für Toggles
- 📝 Elementor: Description-Texte für Feature-Einschränkungen
- 📚 Alle Views dokumentiert in `VIEW-FEATURE-SUPPORT.md`

---

## Technische Details

### Warum kein Elementor `disabled`?

Elementor unterstützt folgende Control-States:
- ✅ `condition` - Control nur anzeigen wenn Bedingung erfüllt (versteckt/zeigt)
- ❌ **Kein** natives `disabled` Attribut

Alternativen:
1. **Conditions verwenden** → Versteckt Toggles (schlecht für Konsistenz)
2. **Description-Texte** → Klar und verständlich (gewählte Lösung)
3. **Custom CSS/JS** → Zu komplex, wartungsaufwändig

### Performance

- Feature-Matrix: Einmal beim Block-Load geladen
- Gutenberg: Cached in `window.churchtoolsSuiteBlocks`
- Elementor: PHP-File nur bei Widget-Render geladen
- **Kein** Performance-Impact auf Frontend (nur Editor)

---

## Best Practices

### ✅ DO
- Feature-Matrix aktuell halten wenn neue Views hinzugefügt werden
- Description-Texte kurz und verständlich
- Neue Features zur Matrix hinzufügen (Default: false für unknown Views)

### ❌ DON'T
- Features in Templates hardcoded prüfen (`if ($view === 'minimal')`)
- Conditions in Gutenberg/Elementor für Feature-Visibility (use disabled)
- Feature-Matrix in mehreren Dateien duplizieren
