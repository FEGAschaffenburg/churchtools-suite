# Release Notes v0.10.3.10

**Datum:** 2. Januar 2026  
**Typ:** Bugfix (Critical)

---

## 🐛 Kritischer Bugfix

### Widget-Shortcode funktionierte nicht
**Symptom:** `[cts_widget view="upcoming"]` zeigte keine Events an.

**Root Cause:**
- Template-Ordner heißt `widget/` (Singular)
- Shortcode suchte nach `widgets/{view}` (Plural)
- Template konnte nicht gefunden werden → Leere Ausgabe

**Änderungen:**
```php
// VORHER:
return self::render_template( "widgets/{$atts['view']}", $events, $atts );

// NACHHER:
return self::render_template( "widget/{$atts['view']}", $events, $atts );
```

**Zusätzlich:**
- Default-View korrigiert: `upcoming-events` → `upcoming`
- Passt jetzt zur tatsächlichen Template-Datei: `templates/widget/upcoming.php`

---

## ✅ Bestätigung: Search & Widget Templates

**Search Template (`templates/search/classic.php`):**
- ✅ Vollständig implementiert
- ✅ Live-Suche mit JavaScript
- ✅ Filterung nach Titel
- ✅ `enable_modal` Parameter unterstützt
- ✅ Shortcode: `[cts_search view="classic"]`

**Widget Template (`templates/widget/upcoming.php`):**
- ✅ Vollständig implementiert
- ✅ Kompakte Darstellung für Sidebar
- ✅ Datum + Zeit + Location optional
- ✅ `enable_modal` Parameter unterstützt
- ✅ Shortcode: `[cts_widget view="upcoming"]`

**Beide Templates sind:**
- ✅ In Gutenberg Block verfügbar
- ✅ In Elementor Widget verfügbar
- ✅ Korrekt getestet und funktionsfähig

---

## 🔧 Geänderte Dateien

**Backend:**
- `includes/class-churchtools-suite-shortcodes.php` (widget_shortcode)

---

## 🚀 Deployment

```bash
git add .
git commit -m "v0.10.3.10 - CRITICAL: Widget Shortcode Template-Pfad Fix"
git tag v0.10.3.10
git push origin main
git push origin v0.10.3.10
```

---

**Vorherige Version:** v0.10.3.9 (enable_modal Parameter + Calendar Navigation Fix)  
**Nächste geplante Version:** v0.10.4.0 (Console-Logging Cleanup)
