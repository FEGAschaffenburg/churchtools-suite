# User Management & Demo Registration (v1.0.3)

> **Neu in v1.0.3.0:** Minimal User Management Interface, Automatische Demo-User-Erstellung, Post-Registration Credentials Display

## 📋 Übersicht

Die ChurchTools Suite v1.0.3 bringt ein intuitives User-Management-System und verbesserte Demo-Registrierung:

### ✨ Neue Features

1. **CTS Managers Dashboard** (Settings → Benutzer)
   - Übersicht aller Plugin-Manager
   - Benutzer direkt editierbar
   - Letzte Anmeldung sichtbar

2. **Demo User Auto-Creation**
   - Beim Aktivieren des Demo Plugins wird automatisch ein `demo-manager` User erstellt
   - Bekommt `cts_manager` Rolle
   - Admin sieht Credentials für 24h

3. **Post-Registration Credentials**
   - Nach erfolgreicher Registrierung werden Zugangsdaten angezeigt
   - Email & Passwort mit Copy-Buttons
   - Nächste Schritte zur Verifizierung

---

## 🎯 CTS Managers Dashboard

### Zugriff
**Admin Panel:** Settings → Benutzer (neuer Subtab)

### Features
- ✅ Read-Only Liste aller `cts_manager` User
- ✅ Benutzerdetails: Name, Email, Letzte Anmeldung
- ✅ "Bearbeiten" Link zu WordPress User-Editor
- ✅ "Alle Benutzer verwalten" Button
- ✅ Anleitung zum Hinzufügen neuer Manager

### Screenshots
```
[Manager-Liste Übersicht]
┌─────────────────────────────────────────────────────────┐
│ ChurchTools Suite Manager                               │
├─────────────────────────────────────────────────────────┤
│ Benutzername  │ Email              │ Angemeldet │ Aktion│
├─────────────────────────────────────────────────────────┤
│ demo-manager  │ demo@example.com  │ vor 1h     │ ✎     │
│ admin         │ admin@example.com │ vor 2h     │ ✎     │
└─────────────────────────────────────────────────────────┘

Anleitung zum Hinzufügen:
1. Gehe zu Benutzer → Alle Benutzer
2. Wähle einen Benutzer
3. Unter "Rolle" wähle "ChurchTools Suite Manager"
4. Speichere die Änderungen
```

### Code-Beispiel

```php
// Die Liste wird automatisch geladen:
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';
$cts_managers = ChurchTools_Suite_Roles::get_cts_managers();

// Jeder Manager hat alle 6 Capabilities:
// - manage_churchtools_suite
// - configure_churchtools_suite
// - sync_churchtools_events
// - manage_churchtools_calendars
// - manage_churchtools_services
// - view_churchtools_debug
```

---

## 🤖 Demo User Auto-Creation

### Funktionsweise

Wenn das **ChurchTools Suite Demo Plugin** aktiviert wird:

1. **Check:** Ist `demo-manager` User bereits vorhanden?
2. **Nein:** Neuer User wird erstellt
   - Username: `demo-manager`
   - Password: Auto-generiert (16 Zeichen, stark)
   - Email: `demo@example.com`
   - Rolle: `cts_manager`
3. **Admin-Notiz:** Credentials werden für 24h angezeigt

### Admin-Notiz Template

```
✅ ChurchTools Suite Demo aktiviert!

Ein Demo-Manager-Benutzer wurde automatisch erstellt:

Benutzername: demo-manager
Passwort: xY9z#mK$2pL@qR8vW
E-Mail: demo@example.com

Diese Anmeldedaten werden hier in 24 Stunden automatisch gelöscht.
[Benutzer bearbeiten →]
```

### Code

```php
// In class-churchtools-suite-demo.php
private function create_demo_user(): void {
    $demo_user = get_user_by( 'login', 'demo-manager' );
    if ( $demo_user ) return; // Existiert bereits
    
    $password = wp_generate_password( 16, true );
    $user_id = wp_create_user( 'demo-manager', $password, 'demo@example.com' );
    
    $user = new WP_User( $user_id );
    $user->add_role( 'cts_manager' );
    
    // Credentials speichern für 24h Admin-Notiz
    set_transient( 'cts_demo_user_created', 
        [ 'username' => 'demo-manager', 'password' => $password, ... ],
        24 * HOUR_IN_SECONDS
    );
}
```

---

## 📝 Post-Registration Credentials Display

### Ablauf

1. **Benutzer registriert sich** auf der Homepage
2. **Formular wird gesendet** via AJAX
3. **Demo-User wird erstellt** in der Datenbank
4. **Zugangsdaten werden angezeigt** (neu!)
   ```
   ✅ Registrierung erfolgreich!
   
   E-Mail: user@example.com     [Kopieren]
   Passwort: ••••••••••••••      [Anzeigen] [Kopieren]
   
   Nächste Schritte:
   1. Überprüfe deine E-Mail
   2. Bestätige deine E-Mail
   3. Melde dich an
   
   [Zur ChurchTools Suite Demo →]
   ```

### UI Features

- ✅ Copy-Buttons für Email & Passwort
- ✅ Toggle zum Passwort anzeigen/verbergen
- ✅ Schritt-für-Schritt Anleitung
- ✅ Direct Link zur Demo
- ✅ Hilfe-Kontakt-Info

### Code

```php
// Shortcode in registration-form.php
[cts_demo_register_success 
    email="user@example.com" 
    password="xY9z#mK$2pL@qR8vW" 
    demo_url="https://plugin.feg-aschaffenburg.de/wp-admin"
]

// Renderer in class-demo-registration-response.php
ChurchTools_Suite_Demo_Registration_Response::render_success(
    [ 'email' => 'user@example.com' ],
    'xY9z#mK$2pL@qR8vW',
    admin_url()
);
```

---

## 🔧 Installation & Setup

### 1. ChurchTools Suite v1.0.3 installieren
```bash
# Download v1.0.3.0 ZIP von GitHub
# Oder via WP-CLI
wp plugin install churchtools-suite --activate
```

### 2. Demo Plugin (Optional)
```bash
# Aktivieren
wp plugin activate churchtools-suite-demo

# Demo-Manager wird automatisch erstellt
# Admin sieht Notiz mit Credentials
```

### 3. Manager-Rolle zuweisen
```bash
# WordPress Admin → Benutzer → Alle Benutzer
# User auswählen → Rolle: "ChurchTools Suite Manager"
# Speichern
```

---

## 📋 User Rollen & Capabilities

### Custom Role: `cts_manager`

**Automatisch zugewiesen bei:**
- Plugin-Aktivierung (für Admin)
- Demo-User-Erstellung
- Manuelle Zuweisung in WordPress Users

**Gewährte Capabilities:**

| Capability | Beschreibung |
|-----------|-------------|
| `manage_churchtools_suite` | Allgemeiner Plugin-Admin |
| `configure_churchtools_suite` | Settings & Verbindung konfigurieren |
| `sync_churchtools_events` | Events synchronisieren & triggern |
| `manage_churchtools_calendars` | Kalender auswählen & verwalten |
| `manage_churchtools_services` | Services auswählen & verwalten |
| `view_churchtools_debug` | Debug & Logs anzeigen |

---

## 🔐 Sicherheit

### Datenschutz
- ✅ Passwörter werden **gehashed** gespeichert
- ✅ Credentials werden nach 24h automatisch aus Admin-Notiz entfernt
- ✅ Demo-User werden nach 30 Tagen automatisch gelöscht
- ✅ Email-Verifizierung erforderlich

### Best Practices
- 📌 Admin sollte Demo-Manager Passwort nach Aktivierung ändern
- 📌 Nur vertrauenswürdige Benutzer sollten `cts_manager` Rolle bekommen
- 📌 Regelmäßig User-Zugriffsrechte überprüfen

---

## 📚 Weitere Ressourcen

- [ROLES-AND-CAPABILITIES.md](../ROLES-AND-CAPABILITIES.md) - Technische Dokumentation
- [CHANGELOG.md](../CHANGELOG.md) - Alle Änderungen in v1.0.3
- [admin/views/settings/subtab-benutzer.php](../admin/views/settings/subtab-benutzer.php) - UI Code
- [includes/class-churchtools-suite-demo.php](../includes/class-churchtools-suite-demo.php) - Demo Plugin

---

**Version:** 1.0.3.0  
**Veröffentlicht:** 12. Januar 2026  
**Unterstützung:** PHP 8.0+ | WordPress 6.0+
