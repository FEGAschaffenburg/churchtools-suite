# Release Notes - v0.9.9.85

**Veröffentlicht:** 9. Januar 2026  
**Status:** Template Improvements + Clean URLs  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🎯 Was ist neu

### 1. **Minimal Templates hinzugefügt** (Testing)

Zwei neue ultra-einfache Templates zum Testen:

**Modal:**
- `templates/views/event-modal/minimal.php`
- Nur die Basics: Titel, Datum, Zeit, Ort, Beschreibung
- Keine Schnörkel, kein Styling-Overhead

**Single Page:**
- `templates/views/event-single/minimal.php`
- Einfaches Layout mit Zurück-Button
- Perfekt zum Debuggen

**Dashboard:**
- Beide Templates erscheinen automatisch in den Dropdowns (Settings → Templates)
- Dynamische Template-Discovery via Filesystem-Scan

---

### 2. **Template-Logik vereinfacht**

**Vorher (v0.9.9.84):**
```php
// Block konnte template überschreiben
$template = $_POST['template_override'] ?? get_option('setting');
```

**Jetzt (v0.9.9.85):**
```php
// Immer Dashboard Setting (einfacher!)
$template = get_option('churchtools_suite_single_template');
$template = get_option('churchtools_suite_modal_template');
```

**Grund:**
- Template "modern" existiert nicht mehr
- Block braucht kein template Parameter (nur click_action)
- Zentralisierte Kontrolle über Dashboard

---

### 3. **Saubere Event-Page URLs**

**Vorher (v0.9.9.84):**
```
❌ /events/?event_id=24&show_event_description=0&show_appointment_description=0&show_location=0&show_services=0&show_time=0&show_tags=0&show_calendar_name=0
```

**Jetzt (v0.9.9.85):**
```
✅ /events/?event_id=24&template=professional
```

**Geändert:**
- `assets/js/churchtools-suite-public.js`: Display-Parameter entfernt
- Nur noch `event_id` und `template` im URL
- `/events/` Seite kontrolliert die Anzeige selbst

---

## 🧹 Code-Änderungen

### admin/class-churchtools-suite-admin.php

**Page Navigation:**
```php
// v0.9.9.85: Immer Dashboard Setting
$single_template = get_option( 'churchtools_suite_single_template', 'professional' );
$event_page_url = home_url( '/events/?event_id=' . urlencode( $event_id ) . '&template=' . urlencode( $single_template ) );
```

**Modal:**
```php
// v0.9.9.85: Immer Dashboard Setting (kein Block override)
$modal_template = $global_modal_setting;
```

### assets/js/churchtools-suite-public.js

**Event-Page Link:**
```javascript
// v0.9.9.85: Clean URL - nur event_id
const params = {
    event_id: eventId
};
```

**Modal AJAX:**
```javascript
// v0.9.9.85: template_override entfernt
data: {
    action: 'cts_get_modal_template',
    nonce: churchtoolsSuitePublic.nonce,
    event_id: eventId,
    current_view: currentView,
    click_action: clickAction
    // ❌ ENTFERNT: template_override
}
```

---

## 📁 Neue Dateien

```
templates/views/
├── event-modal/
│   ├── professional.php  (besteht)
│   └── minimal.php       ✨ NEU
├── event-single/
│   ├── professional.php  (besteht)
│   └── minimal.php       ✨ NEU
```

---

## 🧪 Testing

### 1. **Minimal Templates testen**

**Dashboard:**
1. ChurchTools Suite → Einstellungen → Templates
2. Wähle "Minimal" für Modal Template
3. Wähle "Minimal" für Single Template
4. Speichern

**Frontend:**
1. Event klicken (click_action="modal") → Einfaches Modal
2. Event klicken (click_action="page") → Einfache Seite

### 2. **Saubere URLs prüfen**

**Page Navigation:**
```
✅ /events/?event_id=24&template=minimal
❌ Keine show_* Parameter mehr
```

### 3. **Template Discovery**

Dashboard Dropdown zeigt automatisch:
- professional
- minimal

(Keine Hardcoding mehr!)

---

## 🎯 Block Parameter (Reminder)

```
[cts_event_list click_action="modal"]    → Nutzt Modal Template aus Settings
[cts_event_list click_action="page"]     → Nutzt Single Template aus Settings
```

**Kein template Parameter nötig!** Wird zentral im Dashboard gesteuert.

---

## ⚙️ Migration von v0.9.9.84

**Keine Breaking Changes!** 

Funktioniert sofort:
- ✅ Bestehende Blocks (click_action wird weiter unterstützt)
- ✅ Dashboard Settings (professional bleibt Default)
- ✅ URLs (sauberer, aber rückwärtskompatibel)

**Optional:**
- Dashboard → Templates → Teste "minimal" Templates

---

## 📝 Zusammenfassung

| Feature | v0.9.9.84 | v0.9.9.85 |
|---------|----------|----------|
| **Template Source** | Block override OR Settings | Nur Settings |
| **Event-Page URLs** | Mit show_* Parametern | Nur event_id + template |
| **Available Templates** | Nur professional | professional + minimal |
| **Template Discovery** | Hardcoded | Dynamisch (Filesystem) |
| **Block Parameter** | click_action + template | Nur click_action |

---

**Was funktioniert jetzt besser:**
- ✅ Einfachere Template-Verwaltung (zentral im Dashboard)
- ✅ Saubere URLs (kein Parameter-Chaos)
- ✅ Testing-freundlich (minimal Templates)
- ✅ Dynamische Template-Discovery (keine Hardcodes)

---

**Nächste Schritte:**
1. Testing auf Live-Server
2. Feedback zu minimal Templates
3. Weitere Templates bei Bedarf

🚀 **Ready for deployment!**
