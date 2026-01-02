# Release Notes v0.10.3.4

**Release-Datum:** 20. Januar 2025  
**Type:** Patch (Bug Fix)

## 🐛 Bug Fixes

### Redirect nach Plugin-Update korrigiert

**Problem:**  
Nach dem Update über den "Jetzt installieren"-Button wurde der Benutzer zu `wp-admin/plugins.php` weitergeleitet, obwohl in v0.10.3.2 ein `window.location.reload()` implementiert wurde um zum Dashboard zurückzukehren.

**Ursache:**  
WordPress Core leitet nach Plugin-Updates automatisch zu `plugins.php` weiter. Dies geschieht NACHDEM der Plugin_Upgrader die Dateien aktualisiert hat, unabhängig davon was das JavaScript macht.

**Lösung:**
- Neuer Transient `churchtools_suite_update_redirect` wird VOR dem Update gesetzt
- Hook `admin_init` mit Priorität 1 fängt WordPress-Redirect ab
- Wenn Transient gesetzt ist, erfolgt `wp_safe_redirect()` zum Dashboard
- JavaScript `window.location.reload()` bleibt als Fallback bestehen

**Geänderte Dateien:**
- `admin/class-churchtools-suite-admin.php`: Transient setzen + Redirect-Handler
- `includes/class-churchtools-suite.php`: Hook Registration

---

## 📋 Details

### Code-Änderungen

**admin/class-churchtools-suite-admin.php (Zeile 46):**
```php
// v0.10.3.4: Set redirect destination BEFORE update (prevents plugins.php redirect)
set_transient( 'churchtools_suite_update_redirect', admin_url( 'admin.php?page=churchtools-suite' ), 60 );
```

**admin/class-churchtools-suite-admin.php (Neue Methode):**
```php
/**
 * Prevent WordPress from redirecting to plugins.php after plugin update
 * 
 * WordPress by default redirects to plugins.php after Plugin_Upgrader->install().
 * We intercept this redirect and send users back to our plugin dashboard instead.
 * 
 * @since 0.10.3.4
 */
public function handle_update_redirect() {
    $redirect_target = get_transient( 'churchtools_suite_update_redirect' );
    
    if ( ! empty( $redirect_target ) ) {
        delete_transient( 'churchtools_suite_update_redirect' );
        wp_safe_redirect( $redirect_target );
        exit;
    }
}
```

**includes/class-churchtools-suite.php (Zeile 148):**
```php
// v0.10.3.4: Prevent redirect to plugins.php after update
$this->loader->add_action( 'admin_init', $admin, 'handle_update_redirect', 1 );
```

### Technischer Hintergrund

WordPress Plugin Updates folgen diesem Ablauf:
1. AJAX-Call `cts_run_update`
2. `Plugin_Upgrader->install()` läuft
3. **WordPress Core setzt Redirect-Flag zu `plugins.php`**
4. Plugin-Dateien werden überschrieben
5. AJAX-Response zurück an Browser
6. JavaScript führt `window.location.reload()` aus
7. **WordPress-Redirect überschreibt Reload → `plugins.php`**

Unsere Lösung:
1. Transient `churchtools_suite_update_redirect` VOR Update setzen (60 Sekunden TTL)
2. Nach Update lädt der Browser die Seite neu
3. Hook `admin_init` (Priorität 1) läuft FRÜH
4. Wenn Transient gesetzt → `wp_safe_redirect()` zum Dashboard
5. Transient wird gelöscht
6. WordPress-eigener Redirect wird nie ausgeführt

---

## ✅ Getestet

- [x] Update von v0.10.3.3 → v0.10.3.4
- [x] Redirect zum Dashboard (nicht zu plugins.php)
- [x] Transient wird korrekt gelöscht nach Redirect
- [x] Fallback funktioniert wenn Transient fehlt

---

## 📦 Update-Anleitung

### Automatisch (empfohlen)
1. Dashboard → ChurchTools Suite öffnen
2. "Jetzt installieren" klicken
3. **Neu:** Bleibt jetzt im Dashboard (nicht mehr zu plugins.php)

### Manuell
1. ZIP von GitHub herunterladen
2. Alte Version deaktivieren
3. Neue Version hochladen & aktivieren

---

## 🔗 Änderungshistorie

**v0.10.3.3:** Auto-Update Level Enforcement  
**v0.10.3.2:** Cache-Clearing + Dashboard Reload (teilweise)  
**v0.10.3.1:** Gutenberg & Elementor Fixes  
**v0.10.3.0:** Click-to-Details Configuration  

---

**GitHub Tag:** v0.10.3.4  
**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.4
