# ChurchTools Suite - Option B: Rollen & Capabilities System

**Version:** 1.0.2.0+  
**Status:** Implementation Guide  
**Zielgruppe:** Entwickler, Plugin-Manager

---

## 📋 Überblick

Option B implementiert ein **WordPress-natives Rollen-/Capabilities-System** für ChurchTools Suite, ohne große Datenbankumstrukturierungen.

**Ziel:** Ermöglicht es, dass Benutzer das Plugin **konfigurieren und nutzen** können, **ohne** volle WordPress-Administrator-Rechte zu haben.

---

## 🎯 Features

### ✅ Was wird implementiert

- ✅ Neue WordPress-Rolle: `cts_manager`
- ✅ Neue Capabilities (6):
  - `manage_churchtools_suite` (Hauptberechtigung)
  - `configure_churchtools_suite` (API-Einstellungen)
  - `sync_churchtools_events` (Events synchronisieren)
  - `manage_churchtools_calendars` (Kalender verwalten)
  - `manage_churchtools_services` (Services verwalten)
  - `view_churchtools_debug` (Debug-Informationen anzeigen)
- ✅ Menu-Eintrag nur für autorisierte User sichtbar
- ✅ AJAX-Handler mit granularen Capabilities
- ✅ Admin-Seite mit neuem User-Management (später)

### ❌ Nicht in Option B

- ❌ Multi-Instanz (bleiben für Option C)
- ❌ User-spezifische ChurchTools-Credentials
- ❌ Event-Scoping pro User
- ❌ Separate Admin-Seite für jeden User

---

## 📁 Neue Dateien

### 1. `includes/class-churchtools-suite-roles.php`

Zentrale Klasse für Rollen & Capabilities-Management:

```php
class ChurchTools_Suite_Roles {
    const ROLE_CTS_MANAGER = 'cts_manager';
    const CAPABILITIES = [
        'manage_churchtools_suite',
        'configure_churchtools_suite',
        'sync_churchtools_events',
        'manage_churchtools_calendars',
        'manage_churchtools_services',
        'view_churchtools_debug',
    ];
    
    // Hauptmethoden:
    public static function register_role();           // Registriert Rolle
    public static function remove_role();             // Entfernt Rolle (Uninstall)
    public static function user_can_manage_churchtools(); // Permission Check
    public static function get_cts_managers();        // Listet Manager auf
}
```

---

## 🚀 Implementierung

### Phase 1: Plugin-Aktivierung (Automatisch)

Bei Plugin-Aktivierung werden Rollen registriert:

```php
// churchtools-suite.php
register_activation_hook(__FILE__, 'activate_churchtools_suite');

// class-churchtools-suite-activator.php
public static function activate() {
    require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';
    ChurchTools_Suite_Roles::register_role();
    // ... weitere Initialisierung
}
```

**Ergebnis:** Nach Plugin-Aktivierung ist die `cts_manager`-Rolle verfügbar

---

### Phase 2: AJAX-Checks anpassen

**Vorher (manage_options):**
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( [ 'message' => 'Keine Berechtigung.' ] );
}
```

**Nachher (manage_churchtools_suite):**
```php
if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
    wp_send_json_error( [ 'message' => 'Keine Berechtigung.' ] );
}
```

**Betroffene AJAX-Handler:**
- `ajax_test_connection` → `configure_churchtools_suite`
- `ajax_sync_calendars` → `sync_churchtools_events`
- `ajax_sync_events` → `sync_churchtools_events`
- `ajax_save_calendar_selection` → `manage_churchtools_calendars`
- `ajax_sync_services` → `manage_churchtools_services`
- Alle Debug-Handler → `view_churchtools_debug`

---

### Phase 3: Admin-Menü anpassen

**Vorher:**
```php
add_menu_page(..., 'manage_options', ...);
```

**Nachher:**
```php
add_menu_page(..., 'manage_churchtools_suite', ...);
```

---

## 🎬 Demo-Nutzung

### Demo-Benutzer erstellen

```bash
# Über WP-CLI
wp user create \
  demo-manager \
  demo@example.com \
  --user_pass=TestPassword123 \
  --role=cts_manager

# Oder per Code
$user_id = wp_create_user(
    'demo-manager',
    'TestPassword123',
    'demo@example.com'
);
$user = new WP_User( $user_id );
$user->set_role( 'cts_manager' );
```

### Demo-Benutzer testet Plugin

1. Login als `demo-manager`
2. Admin-Menü zeigt nur "ChurchTools Suite" (kein WordPress-Admin-Zugang)
3. Kann Settings konfigurieren, Events synchronisieren
4. Kann KEINE WordPress-Einstellungen ändern (kein `manage_options`)

---

## 🔧 Technische Details

### Capabilities-Hierarchie

```
Administrator (manage_options)
    ├── manage_churchtools_suite       (Alle ChurchTools-Rechte)
    │   ├── configure_churchtools_suite
    │   ├── sync_churchtools_events
    │   ├── manage_churchtools_calendars
    │   ├── manage_churchtools_services
    │   └── view_churchtools_debug

cts_manager (custom role)
    └── manage_churchtools_suite       (Nur ChurchTools-Rechte!)
        ├── configure_churchtools_suite
        ├── sync_churchtools_events
        ├── manage_churchtools_calendars
        ├── manage_churchtools_services
        └── view_churchtools_debug
```

### Backwards Compatibility

- Administrator hat auch neue Capabilities (für alte Plugins)
- `manage_options` Check wird durch `manage_churchtools_suite` ersetzt
- Administrator kann alles, was vorher möglich war

---

## 📊 Migration von Option A → B

Benutzer mit `manage_options`:
- ✅ Können weiterhin Plugin nutzen
- ✅ Haben zusätzlich die neuen Capabilities
- ℹ️ Keine Änderung erforderlich

Neue `cts_manager`-Benutzer:
- ✅ Haben Zugriff auf ChurchTools Suite
- ❌ Haben KEIN Zugriff auf WordPress-Admin

---

## 🔒 Sicherheit

### Was ändert sich?

1. **Granulare Kontrolle:** Statt `manage_options` (alles), jetzt spezifische Capabilities
2. **Separation of Concerns:** ChurchTools-Manager != WordPress-Admin
3. **Audit-Trail:** Welcher User hat was gemacht (später mit Logger)

### Best Practices

- ✅ Nutze `cts_manager` für Demo-Benutzer
- ✅ Nutze `cts_manager` für externe Konfigurateurs
- ✅ Entferne `manage_options` von normalen Benutzern
- ✅ Regelmäßig User-Liste auditen

---

## 🚦 Nächste Schritte

### Phase 2 (Mittelfristig)

- [ ] ALLE AJAX-Handler auf neue Capabilities umstellen
- [ ] Settings-Seite für User-Management (Liste, Rollen-Zuweisung)
- [ ] Admin-Seite mit "Aktuelle User"-Widget
- [ ] User-spezifische Welcome-Seite

### Phase 3 (Langfristig → Option C)

- [ ] User-Meta statt wp_options
- [ ] Multi-Instanz Support
- [ ] User-spezifische ChurchTools-Credentials
- [ ] Event-Scoping per User

---

## 📝 Beispiel-Code

### Benutzer-Permissions prüfen

```php
// In der Admin-Klasse
if ( ChurchTools_Suite_Roles::user_can_manage_churchtools() ) {
    echo "User kann ChurchTools Suite verwalten";
}

if ( current_user_can( 'configure_churchtools_suite' ) ) {
    echo "User kann API-Einstellungen ändern";
}

if ( current_user_can( 'sync_churchtools_events' ) ) {
    echo "User kann Events synchronisieren";
}
```

### Alle CTS Manager auflisten

```php
$managers = ChurchTools_Suite_Roles::get_cts_managers();

foreach ( $managers as $user ) {
    echo $user->display_name . " (" . $user->user_email . ")\n";
}
```

---

## ✅ Checkliste für Implementierung

- [x] Rollen-Klasse erstellen
- [x] In Activator integrieren
- [x] Menu-Item auf neue Capability aktualisieren
- [ ] ALLE AJAX-Handler anpassen
- [ ] Settings-UI für Nutzer-Management
- [ ] Tests schreiben
- [ ] Dokumentation fertig
- [ ] Release-Notes aktualisieren

---

**Version:** 1.0.2.0  
**Letztes Update:** 12. Januar 2026
