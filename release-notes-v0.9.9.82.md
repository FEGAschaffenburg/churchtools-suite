# Release Notes - v0.9.9.82

**Veröffentlicht:** 9. Januar 2026  
**Status:** IMPROVEMENT - Non-Breaking  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🚀 Keine Hardcoded Templates Mehr!

### Das Problem
v0.9.9.81 hatte Whitelisten hardcoded:

```php
$valid_modal_templates = [ 'professional' ]; // ← Hardcoded!
$valid_single_templates = [ 'professional' ];
```

**Problem:** Wenn neue Templates hinzugefügt werden, musste der PHP-Code aktualisiert werden! 😱

### Die Lösung (v0.9.9.82)

**Dynamische Template-Discovery vom Dateisystem:**

```php
// NEW: Scan filesystem statt hardcoden
$valid_modal_templates = self::get_available_modal_templates();
$valid_single_templates = self::get_available_single_templates();
```

**Wie es funktioniert:**

```php
private static function get_available_modal_templates(): array {
  $template_dir = CHURCHTOOLS_SUITE_PATH . 'templates/views/event-modal/';
  
  // Scan directory für *.php Dateien
  $templates = [];
  $files = scandir( $template_dir );
  
  foreach ( $files as $file ) {
    if ( substr( $file, -4 ) === '.php' && $file[0] !== '.' ) {
      // "professional.php" → "professional"
      $templates[] = substr( $file, 0, -4 );
    }
  }
  
  return $templates; // ['professional']
}
```

### Was sich ändert

| Szenario | v0.9.9.81 ❌ | v0.9.9.82 ✅ |
|----------|----------|----------|
| Neue modal template hinzufügen | PHP-Code updaten | Automatisch erkannt! |
| admin_get_modal_templates() | Hardcoded | Scan live |
| Fallback Template | 'professional' | Erster verfügbarer |

### Vorteil

✅ **Zukunftssicher:** Neue Templates funktionieren automatisch, ohne Code-Änderungen  
✅ **Wartbar:** Keine Whitelisten zu pflegen  
✅ **Sicher:** Fallback nutzt tatsächlich verfügbares Template  

---

## 📦 Deployment

```
ZIP: C:\privat\churchtools-suite-0.9.9.82.zip (0.32 MB)
Status: Ready to Deploy
Changes: admin/class-churchtools-suite-admin.php + version
```

---

## 🔧 Technical Details

### Neue Funktionen (Private Static Methods)

```php
// In ChurchTools_Suite_Admin class:

private static function get_available_modal_templates(): array
  → Scans: templates/views/event-modal/
  → Returns: ['professional']

private static function get_available_single_templates(): array
  → Scans: templates/views/event-single/
  → Returns: ['professional']
```

### Fallback-Verhalten

```php
// Falls Directory nicht existiert oder leer:
if ( empty( $valid_modal_templates ) ) {
  $valid_modal_templates = [ 'professional' ];
}

// Falls eingestelltes Template nicht gefunden:
if ( ! in_array( $setting, $valid_templates ) ) {
  use_first_available_template( $valid_templates[0] );
}
```

---

## 📝 Commits

- **Commit:** e81f7d9
- **Files:** 3 (admin class, version, release notes)
- **Lines:** +178 -9

---

**Zusammenfassung:** v0.9.9.82 macht die Template-Validierung zukunftssicher - neue Templates werden automatisch erkannt, statt dass Whitelisten manuell aktualisiert werden müssen! 🎯

