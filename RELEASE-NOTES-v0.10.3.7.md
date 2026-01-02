# Release Notes v0.10.3.7

**Release-Datum:** 2. Januar 2026  
**Type:** Patch (Bug Fix)

## 🐛 KRITISCHER FIX - Modal Template Rendering

### Problem (aus Console-Log erkannt)
```
[ChurchTools Suite] Modal template response: {success: true, data: {…}}
[ChurchTools Suite] Invalid modal template response
```

AJAX-Call funktionierte, aber `response.data.html` war leer!

### Ursache
```php
// FALSCH (v0.10.3.6):
ChurchTools_Suite_Template_Loader::render_template( 'modal/event-detail.php', [], false );
$html = ob_get_clean();
```

**Problem:** `echo=false` bedeutet die Funktion gibt Output zurück statt zu echoн. Aber `ob_start()` fängt nur ECHOED Output! Das Template wurde zurückgegeben, aber nie ausgegeben → `ob_get_clean()` bekam leeren String.

### Lösung
```php
// RICHTIG (v0.10.3.7):
ChurchTools_Suite_Template_Loader::render_template( 'modal/event-detail.php', [], true );
$html = ob_get_clean();
```

**Fix:** `echo=true` → Template wird ausgegeben → `ob_start()` fängt Output → `ob_get_clean()` bekommt HTML ✅

### Zusätzliche Debug-Info
Wenn Template-Laden fehlschlägt:
```json
{
  "success": false,
  "data": {
    "message": "Modal-Template konnte nicht geladen werden",
    "html": "<!-- error comment -->",
    "debug": {
      "template_path": "/path/to/template",
      "exists": true/false
    }
  }
}
```

---

## ✅ Jetzt sollte funktionieren!

**Erwartete Console-Ausgabe:**
```
[ChurchTools Suite] Public JS loaded
[ChurchTools Suite] initClickableEvents() called
[ChurchTools Suite] Found clickable events: 8
[ChurchTools Suite] Init complete
--- KLICK ---
[ChurchTools Suite] Event clicked, ID: 400
[ChurchTools Suite] showEventModal() called with ID: 400
[ChurchTools Suite] Modal overlay found: false
[ChurchTools Suite] Loading modal template via AJAX...
[ChurchTools Suite] AJAX URL: https://...admin-ajax.php
[ChurchTools Suite] Modal template response: {success: true, data: {html: "..."}}
[ChurchTools Suite] response.data.html exists: true
[ChurchTools Suite] response.data.html length: 14234
[ChurchTools Suite] Appending modal HTML to body...
[ChurchTools Suite] Modal template appended to body
```

Dann lädt das Modal Event-Details und zeigt sie an!

---

## 📦 Update-Anleitung

1. Dashboard → ChurchTools Suite
2. "Jetzt installieren"
3. **Browser-Cache leeren** (STRG+F5)
4. Frontend testen → Event klicken → **Modal sollte sich öffnen!** 🎉

---

**GitHub Tag:** v0.10.3.7  
**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.7
