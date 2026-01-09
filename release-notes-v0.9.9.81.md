# Release Notes - v0.9.9.81

**Veröffentlicht:** 9. Januar 2026  
**Status:** CRITICAL BUG FIX  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🔴 REAL-WORLD BUG GEFUNDEN UND BEHOBEN!

### Das Problem
Live-Logs zeigten: `exists: false` für die Modal-Template-Datei

**Root-Cause:** Der Admin hat in den Dashboard-Einstellungen alte/nicht-existierende Template-Namen eingestellt:
- ❌ `churchtools_suite_modal_template: "event-detail"` - **Datei existiert NICHT!**
- ❌ `churchtools_suite_single_template: "modern"` - **Falsch für Event-Modal!**

Die verfügbaren Templates sind:
- ✅ Modal: `professional.php` - EXISTIERT
- ✅ Single: `professional.php` - EXISTIERT

### Die Lösung (v0.9.9.81)

**Whitelist validieren + Fallback auf `professional`:**

```php
// v0.9.9.80: CRITICAL FIX - Validate template names exist
$valid_modal_templates = [ 'professional' ];
$valid_single_templates = [ 'professional' ];

// Check if dashboard settings reference non-existent templates
if ( ! in_array( $global_modal_setting, $valid_modal_templates, true ) ) {
    // Log warning + use fallback
    $global_modal_setting = 'professional';
}

if ( ! in_array( $global_single_setting, $valid_single_templates, true ) ) {
    // Log warning + use fallback
    $global_single_setting = 'professional';
}
```

### Was sich ändert

Admin sieht jetzt im Log:
```json
{
  "level": "WARNING",
  "context": "ajax_modal",
  "message": "Dashboard modal template does not exist - using fallback",
  "data": {
    "requested_template": "event-detail",
    "fallback_template": "professional",
    "valid_templates": ["professional"]
  }
}
```

### Impact

| Szenario | Vorher ❌ | Nachher ✅ |
|----------|----------|----------|
| Admin hat alt. `event-detail` eingestellt | Modal nicht offen | ✅ Fallback zu `professional` |
| Admin hat falsch. `modern` eingestellt | Modal nicht offen | ✅ Fallback zu `professional` |
| Admin hat korrekt. `professional` | ✅ Funktioniert | ✅ Funktioniert |

---

## 📦 Deployment

```
ZIP: C:\privat\churchtools-suite-0.9.9.81.zip (0.32 MB)
Status: Ready to Deploy
Test: Frontend Event klicken → Modal sollte öffnen ✅
```

---

## 🧪 Nach Deploy

1. Plugin aktivieren (v0.9.9.81)
2. Frontend: Event in List-View klicken
3. ✅ Modal öffnet sich mit `professional` Template

**Falls noch nicht:**
- Admin Dashboard → Einstellungen → Modal Template auf `professional` prüfen

---

## 📝 Commits

- **Commit:** 7ad376b
- **Files:** 2 (churchtools-suite.php, admin class)
- **Lines:** +25 -2

---

**Zusammenfassung:** v0.9.9.81 fixt die ECHTE Root-Cause - alte/falsche Template-Namen im Admin werden jetzt automatisch zu `professional` fallback, statt dass Modal nicht öffnet! 🎉

