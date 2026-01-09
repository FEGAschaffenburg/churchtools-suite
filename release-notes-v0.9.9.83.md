# Release Notes - v0.9.9.83

**Veröffentlicht:** 9. Januar 2026  
**Status:** MAJOR REFACTOR - Simplified & Cleaner  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🎯 VEREINFACHTE TEMPLATE-LOGIK

### Das Problem
Zu viele Template-Entscheidungen basierend auf `current_view`:

```php
// v0.9.9.82: Kompliziert ❌
$view_to_modal_map = [
  'list' => $global_modal_setting,
  'grid' => $global_modal_setting,
  'calendar' => $global_modal_setting,
  'single' => $global_single_setting,  // Unterschied!
];

// Code entschied basierend auf view
// → Doppelte Konfiguration!
```

### Die Lösung (v0.9.9.83)

**Eine einfache Regel:**

```
1️⃣  Admin wählt in Dashboard-Settings: "Modal Template"
    ↓
2️⃣  DIESES Template wird bei JEDEM Event-Klick verwendet
    (egal ob list, grid, calendar, single)
    ↓
3️⃣  Optional: Block kann `template_override` Parameter nutzen
    → Overrided die globale Einstellung für DIESEN Block
```

---

## 📊 Logik-Flussdiagram

```
┌─────────────────────────────────────────────────────────────┐
│ User klickt auf Event in beliebiger View (list/grid/etc)   │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ AJAX: cts_get_modal_template                               │
│ - current_view (nicht mehr relevant!)                       │
│ - template_override (neu!)                                  │
└─────────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────┴──────────────────┐
        ↓                                     ↓
   Template Override      NO Override
   vorhanden?             (Standard)
        │                     │
        ✅ Ja                 ✅ Nein
        │                     │
        ↓                     ↓
  Validiere         Lade Dashboard:
  Block-Template    churchtools_suite_modal_template
        │                     │
        ├─ Valid? ──✅──┐      │
        │           │ └───────┘
        └─ Invalid? ──Fallback zu 'professional'
                  │
                  ↓
    ┌─────────────────────────────┐
    │ Nutze Template              │
    │ (z.B. professional.php)     │
    │ für ALLE VIEWS!             │
    └─────────────────────────────┘
```

---

## 🔧 Code-Beispiele

### Vorher (v0.9.9.82) ❌
```php
// Komplizierte View-basierte Unterscheidung
$view_to_modal_map = [
  'list' => $global_modal_setting,
  'grid' => $global_modal_setting,
  'calendar' => $global_modal_setting,
  'single' => $global_single_setting,  // Separates Setting!
];

if ($current_view === 'single') {
  $modal_template = $view_to_modal_map['single'];
} else {
  $modal_template = $view_to_modal_map['list'];
}
```

### Nachher (v0.9.9.83) ✅
```php
// EINFACH: Ein globales Template für alle
$modal_template = get_option('churchtools_suite_modal_template', 'professional');

// Optional: Block kann overridden
if (isset($_POST['template_override'])) {
  $modal_template = $_POST['template_override'];  // Falls valid
}

// Fertig! Nutze dieses Template überall
```

---

## 🎁 Neue Features

### 1. Block-Parameter: `template_override`

```
[cts_event_list template_override="professional"]
// → Nutzt "professional" statt Dashboard-Setting
```

JavaScript/AJAX:

```javascript
showEventModal(eventId, container, {
  template_override: 'professional'  // Optional
});
```

### 2. Fallback-Logik

```php
// Wenn template_override nicht valid:
// → Fallback zu Dashboard-Setting
// → Falls auch invalid → Fallback zu 'professional'
```

### 3. Logging

```json
{
  "level": "DEBUG",
  "message": "Template selected",
  "data": {
    "selected_template": "professional",
    "source": "dashboard_global",  // Oder "block_override"
    "current_view": "list"
  }
}
```

---

## 📋 Was Entfernt Wurde

❌ `churchtools_suite_single_template` Option (nicht mehr nötig)  
❌ `get_available_single_templates()` Funktion  
❌ `$view_to_modal_map` Array  
❌ View-basierte Template-Entscheidung  

---

## ✅ Was Bleibt

✅ `churchtools_suite_modal_template` Option (globale Einstellung)  
✅ `get_available_modal_templates()` (dynamische Discovery)  
✅ Dynamic Fallback zu 'professional'  
✅ Block-Override-Mechanismus  

---

## 📦 Deployment

```
ZIP: C:\privat\churchtools-suite-0.9.9.83.zip (0.32 MB)
Status: Ready to Deploy
Changes: admin class + version (simplified logic)
```

---

## 🔨 Nach dem Deploy

### 1. Alte Einstellung aufräumen (Optional)
```sql
-- Alte Option entfernen
DELETE FROM wp_options 
WHERE option_name = 'churchtools_suite_single_template';
```

### 2. Testen
```
- Frontend: Klick Event in LIST-View → Modal öffnet sich
- Frontend: Klick Event in GRID-View → Selbes Modal-Template
- Frontend: Block mit template_override="professional" → Fallback-Template
```

### 3. Admin verifiziert
```
- Settings → ChurchTools Suite → Modal Template wählen
- Einstellung nutzt NUR ein Dropdown (nicht zwei!)
```

---

## 📝 Commits

- **Commit:** cdb9696
- **Files:** 2 (admin class, version)
- **Lines:** +156 -77

---

**Zusammenfassung:** v0.9.9.83 vereinfacht die ganze Template-Logik auf eine einfache Regel: **Ein globales Modal-Template für ALLE Views, optional per Block überridden**. Viel sauberer! 🧹✨

