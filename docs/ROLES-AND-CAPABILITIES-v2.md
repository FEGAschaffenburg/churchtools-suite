# ChurchTools Suite - Rollen & Capabilities System (v1.0.4.0+)

**Version:** 1.0.4.0+  
**Status:** Erweiterte Rollen-Struktur  
**Zielgruppe:** Admin, Plugin-Manager, Entwickler

---

## 🎯 Überblick

Das Rollen-System ermöglicht **granulare Zugriffskontrolle** für ChurchTools Suite:

- **Administrator:** Vollzugriff auf alles (WordPress + ChurchTools)
- **cts_manager:** ChurchTools-Konfiguration + Event-Verwaltung (KEINE WordPress-Admin-Rechte)

**Kern-Vorteile:**
- ✅ Trennung von WordPress-Admin und ChurchTools-Verwaltung
- ✅ Sichere Delegation an externe Manager
- ✅ Demo-Benutzer ohne WordPress-Zugriff
- ✅ **[DEMO-ONLY]** Templates sind eigenständige Post Type

---

## 👥 Rollen-Übersicht

### Administrator
Vollständiger Zugriff auf alles (WordPress + ChurchTools)

**Capabilities:**
```
manage_options                    (WordPress-Admin)
├── manage_churchtools_suite      (ChurchTools konfigurieren)
├── configure_churchtools_suite   (API-Einstellungen)
├── sync_churchtools_events       (Events synchronisieren)
├── manage_churchtools_calendars  (Kalender verwalten)
├── manage_churchtools_services   (Services verwalten)
└── view_churchtools_debug        (Debug-Daten anzeigen)
```

---

### cts_manager (neue Standard-Rolle für ChurchTools)
**Ideal für:** Gemeinde-Administrator, ChurchTools-Manager  
**Zugriff:** Nur ChurchTools Suite (KEINE WordPress-Admin-Seite)

**Capabilities:**
```
manage_churchtools_suite          (Hauptberechtigung)
├── configure_churchtools_suite   (API-Verbindung ändern)
├── sync_churchtools_events       (Events + Services synchronisieren)
├── manage_churchtools_calendars  (Kalender-Auswahl ändern)
├── manage_churchtools_services   (Services-Auswahl ändern)
└── view_churchtools_debug        (Debug-Informationen anzeigen)
```

**Hat NICHT:**
- ❌ `manage_options` (kein WordPress-Admin-Zugriff)
- ❌ `edit_posts` (keine normalen WordPress-Seiten bearbeiten)
- ❌ `manage_users` (keine User-Verwaltung)

---

## 📝 Custom Post Type für Templates [DEMO-ONLY]

> **ℹ️ WICHTIG:** Das Template CPT existiert NUR im **ChurchTools Suite Demo Plugin**!

Im **Hauptplugin** sind Templates reguläre WordPress-Seiten/Beiträge.

Im **Demo-Plugin** sind Templates ein eigenständiger **Custom Post Type** (`cts_template`):

### Warum Custom Post Type im Demo?

✅ **Getrennte Permissions:**
- Templates-Rechte sind unabhängig von `edit_posts` (WordPress-Seiten)
- Demo-User kann Templates bearbeiten OHNE Seiten bearbeiten zu dürfen

✅ **Organisiert:**
- Eigenes Menu unter "ChurchTools Suite → Templates & Views"
- Nicht durcheinander mit WordPress-Seiten/Beiträgen

✅ **Sicherheit:**
- Verhindert, dass Demo-User versehentlich Seiten öffentlich macht
- Templates sind automatisch `public=false`

---

## 🔧 Implementierung

### 1. Rollen-Struktur (Hauptplugin)

In `class-churchtools-suite.php`:

```php
add_action( 'init', [ 'ChurchTools_Suite_Roles', 'register_role' ] );
add_action( 'init', [ 'ChurchTools_Suite_Roles', 'register_capabilities' ] );
```

**Ergebnis:**
- Rolle `cts_manager` wird erstellt
- Capabilities werden zugewiesen
- KEINE Template-CPT

---

### 2. Templates im Demo-Plugin

Im **ChurchTools Suite Demo Plugin** wird zusätzlich ein CPT registriert:

```php
// churchtools-suite-demo.php
add_action( 'init', [ 'ChurchTools_Suite_Demo_Template_CPT', 'register' ] );
```

**Ergebnis:**
- CPT `cts_template` wird NUR im Demo registriert
- Demo-User kann Templates erstellen/bearbeiten
- Hauptplugin bleibt unverändert
- Eigenes Menu: "ChurchTools Suite → Templates & Views"

---

### 3. Capabilities im Demo

```php
// class-demo-template-cpt.php
class ChurchTools_Suite_Demo_Template_CPT {
    
    const TEMPLATE_CAPABILITIES = [
        'manage_cts_templates',
        'edit_cts_template',
        'view_cts_templates',
    ];
    
    public static function add_capabilities(): void {
        $admin = get_role( 'administrator' );
        $cts_manager = get_role( 'cts_manager' );
        
        // Beide Rollen bekommen Template-Rechte
        if ( $admin ) {
            foreach ( self::TEMPLATE_CAPABILITIES as $cap ) {
                $admin->add_cap( $cap );
            }
        }
        
        if ( $cts_manager ) {
            foreach ( self::TEMPLATE_CAPABILITIES as $cap ) {
                $cts_manager->add_cap( $cap );
            }
        }
    }
}
```

---

## 👤 Benutzer-Management

### Benutzer-Rollen in Dashboard

**Admin → Benutzer → Rolle hinzufügen:**

```
Standard-WordPress Rollen:
□ Administrator
□ Editor
□ Autor
□ Beitragskontributor
□ Abonnent

ChurchTools Suite Rollen:
☑ cts_manager       (ChurchTools-Verwaltung + Templates)
☑ cts_editor        (Nur Templates)
```

---

### Beispiele

#### Szenario 1: Gemeinde-Administrator
```
Rolle: cts_manager

Kann:
✅ ChurchTools-API konfigurieren
✅ Events synchronisieren
✅ Kalender auswählen
✅ Services konfigurieren
✅ Templates bearbeiten
✅ Debug-Infos anzeigen

Kann NICHT:
❌ WordPress-Einstellungen ändern
❌ Plugins installieren
❌ Benutzer-Verwaltung
```

#### Szenario 2: Template-Designer (externe Agentur)
```
Rolle: cts_editor

Kann:
✅ Templates erstellen/bearbeiten

Kann NICHT:
❌ ChurchTools-API konfigurieren
❌ Events synchronisieren
❌ Kalender/Services ändern
❌ WordPress-Zugang
```

#### Szenario 3: Demo-Benutzer
```
Rolle: cts_manager

Kann:
✅ Alles von ChurchTools Suite

Kann NICHT:
❌ Irgendwas in WordPress
❌ Nur ChurchTools Suite sichtbar

Idealfalls auch:
❌ 7-Tage Zugang (Option: Auto-Delete)
```

---

## 🔐 Sicherheit

### Capabilities-Hierarchie

```
WordPress Administrator
    ├── manage_options (ALLE WordPress-Rechte)
    │   └── Erbt ChurchTools Capabilities
    │
ChurchTools Manager (cts_manager)
    ├── manage_churchtools_suite
    │   ├── configure_churchtools_suite
    │   ├── sync_churchtools_events
    │   ├── manage_churchtools_calendars
    │   ├── manage_churchtools_services
    │   └── view_churchtools_debug
    │
    └── manage_cts_templates (Custom Post Type Caps)
        ├── edit_cts_template
        └── view_cts_templates
        
ChurchTools Editor (cts_editor)
    └── manage_cts_templates (Nur Templates!)
        ├── edit_cts_template
        └── view_cts_templates
```

---

### Best Practices

✅ **DO:**
- Nutze `cts_manager` für vertrauenswürdige Gemeinde-Admin
- Nutze `cts_editor` für externe Designer
- Nutze `cts_manager` für Demo-Benutzer (mit Zeit-Limit)
- Überprüfe regelmäßig, wer welche Rollen hat

❌ **DON'T:**
- Gib niemals `manage_options` an externe Benutzer
- Nutze nicht "Administrator" für Demo
- Verwalte Credentials in Plaintext
- Ändere nicht manuell die Rollen-Capabilities

---

## 📊 Capabilities-Tabelle

| Capability | Administrator | cts_manager | Beschreibung |
|-----------|----|----|
| manage_options | ✅ | ❌ | WordPress-Admin-Zugriff |
| manage_churchtools_suite | ✅ | ✅ | Hauptberechtigung für ChurchTools |
| configure_churchtools_suite | ✅ | ✅ | API-Einstellungen ändern |
| sync_churchtools_events | ✅ | ✅ | Events/Services synchronisieren |
| manage_churchtools_calendars | ✅ | ✅ | Kalender-Auswahl ändern |
| manage_churchtools_services | ✅ | ✅ | Services-Auswahl ändern |
| view_churchtools_debug | ✅ | ✅ | Debug-Informationen anzeigen |
| **manage_cts_templates** | ✅ | ✅ | **[DEMO-ONLY]** Templates erstellen/löschen |
| **edit_cts_template** | ✅ | ✅ | **[DEMO-ONLY]** Templates bearbeiten |
| **view_cts_templates** | ✅ | ✅ | **[DEMO-ONLY]** Templates anzeigen |

---

## 🚀 Integration in Demo-Plugin

### Automatische User-Erstellung

```php
// Bei Backend-Demo-Registrierung (churchtools-suite-demo.php):
$user_id = wp_create_user(
    $email,
    $password,
    $email
);

$user = new WP_User( $user_id );
$user->set_role( 'cts_manager' );

// Template-Capabilities hinzufügen (nur wenn Demo-Plugin aktiv)
if ( class_exists( 'ChurchTools_Suite_Demo_Template_CPT' ) ) {
    $user->add_cap( 'manage_cts_templates' );
    $user->add_cap( 'edit_cts_template' );
    $user->add_cap( 'view_cts_templates' );
}

// Nur ChurchTools sichtbar!
update_user_meta( $user_id, 'show_admin_bar_front', false );
```

### Demo-User Zugriff

**Schnellstart Backend-Demo:**
1. Registrieren → Email bestätigen
2. Login als `demo-manager` mit Demo-Passwort
3. Admin-Bar: Nur "ChurchTools Suite" sichtbar
4. Im Demo: Zusätzlich "Templates & Views" Tab verfügbar

---

## 📋 Nächste Schritte

### Phase 1: Basis-Implementierung (JETZT)
- [x] Rollen-Struktur (cts_manager) im Hauptplugin
- [x] Custom Post Type für Templates NUR im Demo-Plugin
- [x] Capabilities-System

### Phase 2: UI & Management (v1.0.5.0)
- [ ] Admin → Benutzer-Verwaltung (Rollen-Zuweiser)
- [ ] ChurchTools Suite → Benutzer-Tab
- [ ] Audit-Log: Wer hat was geändert?

### Phase 3: Advanced (v1.1.0)
- [ ] User-spezifische Kalender-Filter
- [ ] User-spezifische ChurchTools-Credentials
- [ ] User-Sessions-Management

---

## 🔗 Referenzen

- [WordPress Roles & Capabilities](https://developer.wordpress.org/plugins/users/roles-and-capabilities/)
- [Custom Post Types](https://developer.wordpress.org/plugins/post-types/)
- ROADMAP.md → Template Manager Section

---

**Version:** 1.0.4.0  
**Letztes Update:** 13. Januar 2026

