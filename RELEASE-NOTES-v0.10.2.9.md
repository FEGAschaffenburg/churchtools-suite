# Release Notes - v0.10.2.9

**Release-Datum:** 2. Januar 2026  
**Typ:** Bugfix Release

## 🐛 Behobene Fehler

### Dashboard Cron-Anzeige
- **Problem:** Cron-Jobs zeigten technische Namen statt benutzerfreundlicher Beschreibungen
- **Ursache:** `ChurchTools_Suite_Cron_Display` Klasse wurde nicht geladen
- **Lösung:** Klasse wird jetzt in `load_dependencies()` geladen
- **Auswirkung:** Dashboard zeigt jetzt schöne Namen wie "Event-Synchronisation" statt "churchtools_suite_auto_sync"

### Kalender-Navigation im Admin
- **Problem:** Monatswechsel-Buttons funktionierten nicht (Netzwerkfehler)
- **Ursache:** Public JavaScript wurde im Admin nicht geladen, `churchtoolsSuitePublic` Variable fehlte
- **Lösung:** Public JS + Lokalisierung jetzt auch im Admin-Bereich
- **Auswirkung:** AJAX-Navigation funktioniert jetzt überall (Frontend + Admin)

## 📝 Technische Details

### Geänderte Dateien
- `includes/class-churchtools-suite.php` - Cron_Display Klasse laden
- `admin/class-churchtools-suite-admin.php` - Public JS im Admin laden
- `admin/views/tab-dashboard.php` - Intervall-Namen vervollständigt

### Code-Änderungen
```php
// includes/class-churchtools-suite.php
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-cron-display.php';

// admin/class-churchtools-suite-admin.php
wp_enqueue_script('churchtools-suite-public', ...);
wp_localize_script('churchtools-suite-public', 'churchtoolsSuitePublic', [...]);
```

## 🔧 Für Entwickler

**Dependency-Änderung:**
- Admin-Script hat jetzt Abhängigkeit zu Public-Script
- Public-Script wird sowohl im Frontend als auch im Admin geladen
- Beide Scripts haben eigene Lokalisierung (churchtoolsSuite vs churchtoolsSuitePublic)

**Testing:**
- Dashboard: Cron-Namen prüfen
- Admin/Frontend: Kalender-Navigation testen

## ⬆️ Update-Hinweise

**Von v0.10.2.8:**
- Keine Breaking Changes
- Automatisches Update empfohlen
- Keine Datenbank-Migrationen

## 📊 Betroffene Komponenten

- ✅ Dashboard Cron-Anzeige
- ✅ Kalender Monthly Modern (Navigation)
- ✅ Admin JavaScript Loading
- ✅ AJAX-Endpoints

---

**Installation:** ZIP-Upload via WordPress Admin oder Auto-Update  
**Support:** [GitHub Issues](https://github.com/FEGAschaffenburg/churchtools-suite/issues)
