# Release Notes - v0.9.9.84

**Veröffentlicht:** 9. Januar 2026  
**Status:** MAJOR REFACTOR - Block-centric Configuration  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🎯 Click-Action ist jetzt NUR im Block!

### Das Problem
Admin Settings hatten Click-Action als globale Einstellung → Gilt für ALLE Events auf der Seite.

**Problem:** Manchmal möchte man auf einer Seite Modal-Klicks, auf einer anderen Seite Page-Navigationn.

### Die Lösung (v0.9.9.84)

**Move Click-Action zu Block-Parameter:**

```
Admin Settings:
❌ ENTFERNT: churchtools_suite_click_action Option
✅ Bleiben: Modal Template, Single Template Defaults

Block Parameter:
✅ NEU: click_action="modal"  oder  click_action="page"
✅ NEU: template="professional" (Override)
```

---

## 🔄 Wie es funktioniert

### Block Konfiguration

```
[cts_event_list click_action="modal" template="professional"]
   ↓
   Block sendet diese Info an JavaScript
   ↓
JavaScript showEventModal(eventId, container, view, {click_action: 'modal'})
   ↓
   AJAX Handler nutzt click_action Parameter
   ↓
   Entweder: Modal öffnen ODER: Zur Page navigieren
```

### Szenario 1: Modal

```
[cts_event_list 
  click_action="modal" 
  template="professional"
]
```

↓ User klickt Event

↓ Modal öffnet sich mit "professional" Template

### Szenario 2: Page Navigation

```
[cts_event_list click_action="page"]
```

↓ User klickt Event

↓ Navigiert zu: `/events/?event_id=123&template=professional`

---

## 🧹 Was wurde entfernt

### Admin Settings
- ❌ `churchtools_suite_click_action` Option
- ❌ "Klick Verhalten" Dropdown in Settings
- ✅ Nur noch Info über Block-Parameter

### Database
- Keine Datenbankänderungen

---

## ✅ Was bleibt

### Admin Settings (Templates Tab)
```
✅ churchtools_suite_modal_template (Default: 'professional')
✅ churchtools_suite_single_template (Default: 'professional')
```

### Neue Block-Parameter (v0.9.9.84)
```
click_action="modal"     // Modal-Overlay öffnen
click_action="page"      // Zur Single-Page navigieren
template="professional"  // Template Override (modal)
```

### JavaScript
```javascript
showEventModal(eventId, $container, currentView, {
  click_action: 'modal',
  template: 'professional'
});
```

---

## 📋 Code-Beispiele

### Vorher (v0.9.9.83) ❌
```php
// Admin Settings
churchtools_suite_click_action = 'modal'  // Global!

// Block konnte nicht overridden werden
[cts_event_list]  // Always modal
```

### Nachher (v0.9.9.84) ✅
```php
// Admin Settings
// (click_action removed - kein globales Setting mehr!)

// Block entscheidet
[cts_event_list click_action="modal"]      // Modal
[cts_event_list click_action="page"]       // Page Navigation
[cts_event_list click_action="modal" template="elegant"]  // Custom Template
```

---

## 🔌 AJAX Handler (Vereinfacht)

```php
// v0.9.9.84: click_action vom POST (Block), nicht vom Dashboard
$click_action = $_POST['click_action'] ?? 'modal';

// Wenn Page: Redirect URL zurückgeben
if ($click_action === 'page') {
  wp_send_json_success([
    'action' => 'page',
    'url' => '/events/?event_id=123&template=professional'
  ]);
  return;
}

// Wenn Modal: HTML zurückgeben
wp_send_json_success([
  'action' => 'modal',
  'html' => $modal_html
]);
```

---

## 📱 JavaScript (Vereinfacht)

```javascript
function showEventModal(eventId, $container, currentView, options) {
  options = options || {};
  
  // v0.9.9.84: Get click_action from Block options
  var clickAction = options.click_action || 'modal';
  
  $.ajax({
    data: {
      click_action: clickAction,  // Sende zu AJAX Handler
      template_override: options.template
    },
    success: function(response) {
      if (response.data.action === 'page') {
        // Navigiere
        window.location.href = response.data.url;
      } else {
        // Zeige Modal
        showModal(response.data.html);
      }
    }
  });
}
```

---

## 🧪 Nach dem Deploy

### 1. Admin aufräumen (optional)
```sql
-- Alte Option entfernen (falls vorhanden)
DELETE FROM wp_options 
WHERE option_name = 'churchtools_suite_click_action';
```

### 2. Block anpassen
```
VORHER:
[cts_event_list]

NACHHER (Modal):
[cts_event_list click_action="modal"]

NACHHER (Page):
[cts_event_list click_action="page"]
```

### 3. Testen
```
✓ Frontend: Click Event → Modal öffnet sich
✓ Frontend: click_action="page" → Navigiert zu /events/?event_id=123
✓ Admin Logs: Zeigt click_action in AJAX responses
```

---

## 🎯 Vorteile

| Aspekt | v0.9.9.83 ❌ | v0.9.9.84 ✅ |
|--------|----------|----------|
| Globale Einstellung | Jede Seite gleich | Pro Block konfigurierbar |
| Per-Block Anpassung | Nicht möglich | Einfach im Shortcode |
| Admin Settings | Kompliziert | Vereinfacht (nur Templates) |
| Flexibilität | Niedrig | Hoch |

---

## 📝 Commits

- **Commit:** 1e3e22c
- **Files:** 5 (admin, settings, JS, version, release notes)
- **Lines:** +380 -76

---

**Zusammenfassung:** v0.9.9.84 macht das System block-centric - jeder Block kann entscheiden, ob Modal oder Page-Navigation, statt dass das global in Admin festgelegt ist! 🚀

