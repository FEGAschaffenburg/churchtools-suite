# Release Notes - v0.10.3.22

**Release-Datum:** 2. Januar 2026  
**Typ:** CRITICAL FIX - Calendar Month Display

---

## 🔥 CRITICAL FIX: Kalender zeigt jetzt den richtigen Monat!

**v0.10.3.21 hatte AJAX funktionsfähig, ABER:**
- ❌ Kalender-Anzeige blieb immer bei "Januar 2026"
- ❌ Grid zeigte immer aktuellen Monat (nicht den angeklickten)

---

## 🐛 Das Problem

### Symptome:
- ✅ AJAX-Call erfolgreich
- ✅ Events werden geladen
- ❌ Titel zeigt immer "Januar 2026"
- ❌ Kalender-Grid zeigt immer aktuellen Monat

### Root Cause:

**Template (templates/calendar/monthly-modern.php):**

```php
// TITEL (Zeile 38) - ❌ FALSCH
<h2 class="cts-calendar-title"><?php echo esc_html( date_i18n( 'F Y' ) ); ?></h2>

// GRID (Zeile 79-82) - ❌ FALSCH
$first_day = date( 'Y-m-01' );      // Aktueller Monat!
$last_day = date( 'Y-m-t' );        // Aktueller Monat!
$start_weekday = date( 'N', strtotime( $first_day ) );
$days_in_month = date( 't' );       // Aktueller Monat!
```

**Problem:**
- Template nutzt `date()` und `date_i18n()` → **IMMER aktuelles Datum**
- AJAX übergibt Jahr/Monat, aber Template ignoriert es
- Deshalb bleibt Anzeige immer bei "Januar 2026"

---

## ✅ Die Lösung

### Jahr/Monat als Parameter übergeben und nutzen:

**1. AJAX-Handler übergibt Jahr/Monat:**
```php
// admin/class-churchtools-suite-admin.php
$atts = [
    'view' => 'monthly-modern',
    'from' => $from_date,
    'to' => $to_date,
    'limit' => $limit,
    'enable_modal' => $enable_modal,
    'year' => $year,   // NEU: Jahr für Titel
    'month' => $month, // NEU: Monat für Titel
];
```

**2. Template nutzt Parameter für Titel:**
```php
<h2 class="cts-calendar-title">
    <?php 
    // Use year/month from AJAX if available
    if ( isset( $args['year'] ) && isset( $args['month'] ) ) {
        $timestamp = mktime( 0, 0, 0, $args['month'], 1, $args['year'] );
        echo esc_html( date_i18n( 'F Y', $timestamp ) );
    } else {
        // Initial page load - current date
        echo esc_html( date_i18n( 'F Y' ) );
    }
    ?>
</h2>
```

**3. Template nutzt Parameter für Grid:**
```php
// Use year/month from AJAX if available
if ( isset( $args['year'] ) && isset( $args['month'] ) ) {
    $year = $args['year'];
    $month = $args['month'];
} else {
    $year = date( 'Y' );
    $month = date( 'm' );
}

$first_day = sprintf( '%04d-%02d-01', $year, $month );
$last_day = date( 'Y-m-t', strtotime( $first_day ) );
$start_weekday = date( 'N', strtotime( $first_day ) );
$days_in_month = date( 't', strtotime( $first_day ) );

// Grid uses correct month!
for ( $day = 1; $day <= $days_in_month; $day++ ) {
    $date = sprintf( '%04d-%02d-%02d', $year, $month, $day );
    // ...
}
```

---

## 🔧 Technische Details

### Geänderte Dateien

**admin/class-churchtools-suite-admin.php**
- `ajax_load_calendar_month()` - Übergibt year/month in $atts

**templates/calendar/monthly-modern.php**
- Titel nutzt $args['year'] und $args['month']
- Grid berechnet Tage basierend auf AJAX-Parametern
- Fallback auf aktuelles Datum bei Initial Load

### Template-Parameter Logik:

**Initial Page Load (Shortcode):**
- Keine year/month Parameter
- Template nutzt `date()` → aktueller Monat

**AJAX Calendar Navigation:**
- year/month Parameter gesetzt
- Template nutzt Parameter → angeklickter Monat

---

## 📊 Testing

### Erwartetes Verhalten:
- ✅ Kalender lädt initial mit aktuellem Monat
- ✅ Klick auf "›" → nächster Monat
- ✅ **Titel ändert sich**: "Januar 2026" → "Februar 2026"
- ✅ **Grid ändert sich**: 31 Tage → 28 Tage
- ✅ Events werden im richtigen Monat angezeigt
- ✅ Klick auf "‹" → vorheriger Monat funktioniert

### Console Debug:
```
[Calendar] Loading month: 2026 2
[Calendar] AJAX success
[Calendar] Re-initializing event handlers
[Calendar] Month loaded successfully
```

---

## 🔄 Migration von v0.10.3.21

Keine Datenbank-Änderungen.

**Update-Prozess:**
1. Plugin aktualisieren
2. **Browser-Cache leeren** (STRG+F5)
3. Kalender testen - Monatswechsel sollte jetzt sichtbar sein!

---

## 🎯 Bug-Fixing Timeline

- v0.10.3.18: Logging
- v0.10.3.19: get_events_in_range()
- v0.10.3.20: Array Conversion
- v0.10.3.21: Event-Handler Re-Init
- **v0.10.3.22:** **Month Display Fix → Kalender zeigt richtigen Monat!** ✅

---

## 🎉 Kalender KOMPLETT funktional!

**Was funktioniert jetzt:**
- ✅ Monatswechsel vorwärts/rückwärts
- ✅ **Titel zeigt richtigen Monat**
- ✅ **Grid zeigt richtige Tage**
- ✅ Events im richtigen Monat
- ✅ Events klickbar
- ✅ Modal funktioniert

**WICHTIG:** Browser-Cache leeren! 🔄

---

**Vollständiges Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.22
