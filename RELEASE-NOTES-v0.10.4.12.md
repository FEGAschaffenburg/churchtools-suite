# v0.10.4.12 - Elementor Integration (Tag-Parameter)

**Release Date:** 5. Januar 2026  
**Type:** Bugfix  
**Branch:** production → deployment

---

## 🎯 Summary

Tag-Parameter in Elementor Widget nachgezogen. Nun sind `show_tags` und `filter_tags` auch in Elementor verfügbar.

**Impact:** Feature-Parität zwischen Gutenberg und Elementor

---

## 🐛 Bug Fix

**Problem:** Tag-Features (v0.10.4.11) fehlten im Elementor Widget

**Lösung:**
1. `show_tags` Toggle in "Was anzeigen?" Sektion
2. `filter_tags` Textfeld in "Filter & Sortierung" Sektion
3. Beide Parameter werden an Shortcode-Handler weitergegeben

---

## 🔧 Technical Implementation

### Files Changed

**1. Elementor Widget (`includes/class-churchtools-suite-elementor-widget.php`):**

**Display-Sektion (nach "Uhrzeit anzeigen"):**
```php
// v0.10.4.12: Tags anzeigen
$this->add_control(
    'show_tags',
    [
        'label'        => __( 'Tags anzeigen', 'churchtools-suite' ),
        'type'         => \Elementor\Controls_Manager::SWITCHER,
        'label_on'     => __( 'Ja', 'churchtools-suite' ),
        'label_off'    => __( 'Nein', 'churchtools-suite' ),
        'return_value' => 'yes',
        'default'      => '',
        'description'  => __( 'ChurchTools-Tags als farbige Badges anzeigen', 'churchtools-suite' ),
    ]
);
```

**Filter-Sektion (nach "Datum bis"):**
```php
// v0.10.4.12: Tag-Filter (AND-Logik)
$this->add_control(
    'filter_tags',
    [
        'label'       => __( 'Nach Tags filtern', 'churchtools-suite' ),
        'type'        => \Elementor\Controls_Manager::TEXT,
        'placeholder' => __( 'z.B. Gottesdienst,Alpha', 'churchtools-suite' ),
        'description' => __( 'Komma-separierte Tag-Namen (AND-Logik: Event muss ALLE Tags haben)', 'churchtools-suite' ),
    ]
);
```

**Render-Methode:**
```php
// v0.10.4.12: Tag-Parameter übergeben
$atts['show_tags'] = ( isset( $settings['show_tags'] ) && $settings['show_tags'] === 'yes' ) ? 'true' : 'false';

if ( $preset_source === 'standard' ) {
    if ( ! empty( $settings['filter_tags'] ) ) {
        $atts['filter_tags'] = sanitize_text_field( $settings['filter_tags'] );
    }
}
```

---

## 📋 Usage in Elementor

### 1. Tag-Anzeige aktivieren

**Elementor Widget Settings:**
- "Was anzeigen?" → "Tags anzeigen" → Aktivieren

### 2. Tag-Filter setzen

**Elementor Widget Settings:**
- "Filter & Sortierung" → "Nach Tags filtern" → `Gottesdienst,Alpha`

**Ergebnis:** Nur Events mit **BEIDEN** Tags werden angezeigt (AND-Logik)

---

## ✅ Feature-Parität

**Gutenberg vs. Elementor:**
- ✅ `show_tags` (beide)
- ✅ `filter_tags` (beide)
- ✅ AND-Logik (beide)
- ✅ Tag-Badges Rendering (beide)

---

## 🧪 Testing Checklist

**Developer Testing:**
- [x] show_tags Toggle im Elementor Widget
- [x] filter_tags Textfeld im Elementor Widget
- [x] Parameter werden an Shortcode weitergegeben
- [x] Tag-Badges werden angezeigt

**User Testing Required:**
- [ ] Elementor Widget mit show_tags=true
- [ ] Elementor Widget mit filter_tags="Gottesdienst"
- [ ] Elementor Widget mit filter_tags="Gottesdienst,Alpha" (AND)

---

## 📚 Related Features

**Depends On:**
- v0.10.4.11: Tag-Filtering & Display Implementation

**Fixes:** Elementor hatte Tag-Features nicht, obwohl Gutenberg sie hatte

---

## 🚀 Deployment

**Files Changed:**
- `includes/class-churchtools-suite-elementor-widget.php` (29 Zeilen)
- `churchtools-suite.php` (Version bump)

**Database Changes:** None

**Migration Required:** No

**Backwards Compatible:** Yes

**Breaking Changes:** None

---

**Previous Version:** v0.10.4.11  
**Next Version:** TBD
