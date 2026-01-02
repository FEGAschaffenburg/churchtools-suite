# Release Notes v0.10.2.8

**Release-Datum:** 2. Januar 2026  
**Release-Typ:** Bugfix  
**Migrationsbedarf:** Nein  
**Breaking Changes:** Nein

---

## 🎯 Zusammenfassung

Behebt nicht-funktionierenden Monatswechsel in der Calendar Monthly Modern View.

---

## 🐛 Bugfixes

### Calendar Monthly Modern: Monatswechsel funktioniert jetzt
**Problem:** In der Frontend Calendar-View "Monthly Modern" waren die Buttons für Monatswechsel (‹ / ›) nicht funktional.

**Root Cause:**
1. JavaScript-Navigation war nur als TODO-Kommentar vorhanden
2. AJAX-Handler für Monatswechsel fehlte komplett
3. Template hatte keine data-attributes für AJAX-Requests

**Lösung:**
- ✅ Vollständige Monatswechsel-Navigation implementiert
- ✅ AJAX-Handler `cts_load_calendar_month` hinzugefügt (Frontend + Backend)
- ✅ Loading-State mit Spinner während AJAX-Request
- ✅ Unterstützung für eingeloggte UND nicht-eingeloggte Nutzer

---

## ✨ Technische Details

### Frontend JavaScript (churchtools-suite-public.js)
**Neue Funktionen:**
1. `setupCalendarNavigation()` - Event-Listener für Prev/Next Buttons
2. `parseMonthYear()` - Parst Monatstitel (z.B. "Januar 2026")
3. `loadCalendarMonth()` - AJAX-Request für neuen Monat

**Workflow:**
```javascript
Nutzer klickt "›" → Parse aktuellen Monat → Berechne +1 Monat → AJAX Request
→ Server lädt Events für neuen Monat → Rendert Template → Ersetzt HTML
→ Re-initialisiert Navigation für neue Buttons
```

### Backend AJAX-Handler (class-churchtools-suite-admin.php)
**Neue Methode:** `ajax_load_calendar_month()`

**Features:**
- Nonce-Verification (Security)
- Datumsvalidierung (2000-2100, Monat 1-12)
- Dynamischer Datumsbereich (1. bis letzter Tag des Monats)
- Event-Loading via Repository
- Template-Rendering via Template Loader

**Registrierung:**
```php
add_action( 'wp_ajax_cts_load_calendar_month', [...] );           // Eingeloggte
add_action( 'wp_ajax_nopriv_cts_load_calendar_month', [...] );    // Gäste
```

### Template-Änderungen (monthly-modern.php)
**Data-Attributes für AJAX:**
```html
<div class="cts-calendar-monthly" 
     data-calendar-ids="..."   <!-- Für Filter-Persistenz -->
     data-limit="100">          <!-- Für Limit-Persistenz -->
```

**Loading-State CSS:**
```css
.cts-calendar-monthly.cts-loading {
  opacity: 0.6;
  pointer-events: none;  /* Verhindert Doppel-Klicks */
}
.cts-calendar-monthly.cts-loading::after {
  /* Rotierender Spinner */
}
```

---

## 📊 Auswirkungen

### Nutzer-Erfahrung
- ✅ Monatswechsel funktioniert nahtlos
- ✅ Visuelles Feedback (Loading-Spinner)
- ✅ Keine Seiten-Reload nötig
- ✅ Kalender-Filter bleiben erhalten

### Performance
- ✅ Nur benötigte Events werden geladen (Datumsbereich-Filter)
- ✅ Kein Full-Page-Reload
- ✅ Smooth User Experience

### Kompatibilität
- ✅ Funktioniert für eingeloggte Nutzer
- ✅ Funktioniert für Gäste (wp_ajax_nopriv)
- ✅ Keine Breaking Changes

---

## 🔄 Upgrade-Hinweise

### Sofort-Update möglich
- ✅ Keine Datenbank-Migration nötig
- ✅ Keine Template-Anpassungen nötig (automatisch)
- ✅ Keine Shortcode-Änderungen nötig
- ✅ Backward compatible

### Empfohlen für
- Alle Nutzer mit Calendar Monthly Modern View
- Alle Frontend-Calendar-Displays

---

## 🧪 Testing

**Manuelle Tests durchgeführt:**
1. ✅ Monatswechsel vorwärts (›)
2. ✅ Monatswechsel rückwärts (‹)
3. ✅ Loading-State während AJAX
4. ✅ Fehlerbehandlung bei Netzwerk-Problemen
5. ✅ Persistenz von calendar_ids
6. ✅ Funktionalität für eingeloggte Nutzer
7. ✅ Funktionalität für nicht-eingeloggte Besucher

**Browser-Kompatibilität:**
- Chrome/Edge ✅
- Firefox ✅
- Safari ✅ (via date_i18n für Monatsnamen)

---

## 📝 Code-Qualität

**Security:**
- Nonce-Verification für alle AJAX-Requests
- Input-Sanitization (absint, sanitize_text_field)
- Output-Escaping (esc_attr, esc_html)
- SQL Prepared Statements (via Repository)

**Best Practices:**
- Separation of Concerns (Frontend JS ↔ Backend PHP)
- Repository Pattern für Datenbank-Zugriff
- Template Loader für Rendering
- Error Handling mit try/catch

---

**Nächstes Milestone:** v1.0.0 - Production Ready
