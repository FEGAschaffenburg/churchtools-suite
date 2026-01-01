# ChurchTools Suite v0.10.0.0 - Plugin Architecture Cleanup

**Veröffentlicht:** Januar 2026  
**Typ:** Architecture Refactoring  
**Breaking Changes:** ⚠️ Ja (Demo-Features entfernt)

---

## 🎯 Überblick

Diese Version trennt Production- und Demo-Features in separate Plugins:
- **churchtools-suite** (Production) - Für echte Gemeinden
- **churchtools-suite-demo** (Demo Addon) - Nur für Demo-Server

**Ziel:** Saubere Codebasis ohne Demo-spezifischen Code für Production-User.

---

## ✨ Neue Features

### Filter-Hook für Erweiterbarkeit

Neuer Filter `churchtools_suite_get_events` erlaubt externen Plugins, Events zu überschreiben:

```php
add_filter('churchtools_suite_get_events', function($events, $filters) {
    // Modify or replace events
    return $events;
}, 10, 2);
```

**Verwendung:**
- Demo Plugin nutzt diesen Filter für Demo-Daten
- Andere Plugins können eigene Event-Quellen integrieren
- Ermöglicht Caching-Layer oder externe APIs

---

## 🔧 Änderungen

### Entfernt

- ❌ **Migration 2.3** (demo_users Tabelle) - Jetzt im Demo Plugin
- ❌ **Demo Users Repository** - Verschoben zu churchtools-suite-demo
- ❌ **Demo Registration Service** - Verschoben zu churchtools-suite-demo
- ❌ **CTS_DEMO_MODE Konstante** - Nicht mehr benötigt
- ❌ **is_demo_mode() Methode** - Ersetzt durch Filter-Hook
- ❌ **get_demo_events() Methode** - Ersetzt durch Filter-Hook

### Behalten (Kompatibilität)

- ✅ **Demo Data Provider** - Bleibt im Plugin, aber inaktiv
  - Wird nur aktiviert wenn Demo-Plugin Filter registriert
  - Gemeinden ohne Demo-Plugin bemerken keinen Unterschied

### Geändert

- **DB_VERSION:** 2.3 → 2.2 (Migration 2.3 entfernt)
- **Template Data Provider:** Filter-Hook statt Demo-Mode Check
- **Plugin Version:** 0.9.5.2 → 0.10.0.0

---

## 📦 Deployment-Strategie

### Production Plugin (churchtools-suite)
```bash
# Git-managed, GitHub Releases
git push
gh release create v0.10.0.0
```

### Demo Plugin (churchtools-suite-demo)
```bash
# SSH-only, KEIN Git!
.\deploy-complete.ps1 -Component demo
```

### Demo Pages (churchtools-suite-demos)
```bash
# SSH-only, KEIN Git!
.\deploy-ssh.ps1
```

---

## 🔄 Migration Guide

### Für Gemeinden (Production User)

**Keine Aktion erforderlich!** Update funktioniert automatisch.

```bash
# WordPress Admin
Plugins → Update verfügbar → Aktualisieren

# Oder via WP-CLI
wp plugin update churchtools-suite
```

**Was passiert:**
- Migration 2.3 wird übersprungen (DB Version bleibt 2.2)
- Demo-Features werden nicht geladen (waren eh inaktiv)
- Alle Daten bleiben erhalten
- Keine Funktions-Änderungen

### Für Demo-Server

**Installation beider Plugins erforderlich:**

```bash
# 1. Production Plugin aktualisieren
ssh web2975@plugin.feg-aschaffenburg.de
cd /var/www/.../plugins/
# ZIP hochladen und entpacken

# 2. Demo Plugin installieren
# ZIP hochladen und entpacken
wp plugin activate churchtools-suite-demo

# 3. Demo Pages aktualisieren
.\deploy-ssh.ps1 -PageName backend-demo
```

---

## ⚠️ Breaking Changes

### Für Entwickler

**1. CTS_DEMO_MODE Konstante nicht mehr unterstützt:**

```php
// ❌ ALT (funktioniert nicht mehr):
define('CTS_DEMO_MODE', true);

// ✅ NEU (via Filter):
add_filter('churchtools_suite_get_events', function($events, $filters) {
    if (/* demo condition */) {
        return $demo_provider->get_events($filters);
    }
    return $events;
}, 10, 2);
```

**2. Demo-Klassen entfernt:**

```php
// ❌ ALT (existiert nicht mehr):
$demo_users_repo = new ChurchTools_Suite_Demo_Users_Repository();
$registration_service = new ChurchTools_Suite_Demo_Registration_Service();

// ✅ NEU (separates Plugin):
// Siehe churchtools-suite-demo Plugin
```

---

## 🐛 Bugfixes

Keine kritischen Bugfixes in dieser Version.  
Focus lag auf Architektur-Verbesserungen.

---

## 📊 Code-Statistiken

**Entfernt:**
- 3 Klassen (Demo Users Repo, Demo Registration Service, Migration 2.3)
- ~800 Zeilen Code
- 1 Datenbank-Tabelle (demo_users)

**Hinzugefügt:**
- 1 Filter-Hook
- Dokumentation für Erweiterbarkeit

**Resultat:**
- **Kleineres Plugin:** ~15 KB weniger
- **Sauberer Code:** Keine Demo-Logik im Production Code
- **Bessere Wartbarkeit:** Klare Trennung der Zuständigkeiten

---

## 🎓 Ressourcen

**Dokumentation:**
- [DEPLOYMENT-GUIDE.md](../DEPLOYMENT-GUIDE.md) - Deployment-Strategien
- [ROADMAP.md](../ROADMAP.md) - Roadmap aktualisiert

**Demo Plugin:**
- Repository: `C:\privat\churchtools-suite-demo`
- README: SSH Deployment Guide
- Features: Self-Service Registration, Backend-Zugang

**Related:**
- [Migration Guide](../docs/MIGRATION-GUIDE.md)
- [Filter Hooks Reference](../docs/FILTER-HOOKS.md)

---

## 🚀 Nächste Schritte

**v1.0.0 - Production Ready:**
- Security Audit
- Performance Optimization
- Vollständige Dokumentation
- Unit Tests
- WordPress.org Submission

---

## 📝 Hinweise

### Für Plugin-Entwickler

Der neue `churchtools_suite_get_events` Filter bietet folgende Möglichkeiten:

```php
// Beispiel 1: Caching
add_filter('churchtools_suite_get_events', function($events, $filters) {
    $cache_key = 'cts_events_' . md5(serialize($filters));
    $cached = wp_cache_get($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    wp_cache_set($cache_key, $events, '', 3600);
    return $events;
}, 10, 2);

// Beispiel 2: External API
add_filter('churchtools_suite_get_events', function($events, $filters) {
    if (isset($filters['source']) && $filters['source'] === 'external') {
        return fetch_from_external_api($filters);
    }
    return $events;
}, 10, 2);

// Beispiel 3: Demo Mode (wie Demo Plugin)
add_filter('churchtools_suite_get_events', function($events, $filters) {
    if (is_user_logged_in() && current_user_can('cts_demo_user')) {
        $demo_provider = new ChurchTools_Suite_Demo_Data_Provider();
        return $demo_provider->get_events($filters);
    }
    return $events;
}, 10, 2);
```

---

**Fragen?** Siehe [GitHub Issues](https://github.com/FEGAschaffenburg/churchtools-suite/issues)
