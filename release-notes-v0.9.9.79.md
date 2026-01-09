# Release Notes - v0.9.9.79

**Veröffentlicht:** 9. Januar 2026  
**Status:** Development  
**Kompatibilität:** PHP 8.0+, WordPress 6.0+

---

## 🔍 Fokus: ULTRA-DETAILLIERTES Template Path Logging

Diese Version fügt extrem detailliertes Logging zur Template-Lokalisierung hinzu, um das "Template not found" Problem auf Live-Servern zu diagnostizieren.

### Was wurde geändert?

#### 📋 `includes/class-churchtools-suite-template-loader.php` - locate_template()

**Neue Debug-Informationen pro Pfad-Check:**

```
Theme Template Check:
  ✓ Genauen Pfad
  ✓ file_exists() = true/false
  ✓ is_readable() = true/false
  ✓ Pfadlänge
  
Parent Theme Check:
  ✓ Genauen Pfad (falls Child-Theme)
  ✓ file_exists() = true/false
  ✓ is_readable() = true/false
  
Plugin Template Check (DETAILLIERT):
  ✓ Genauen Pfad
  ✓ Pfadlänge
  ✓ file_exists() = true/false
  ✓ is_readable() = true/false
  ✓ Dateigröße (in Bytes)
  ✓ CHURCHTOOLS_SUITE_PATH (Wert + Länge)
  ✓ Relativer Pfad-Teil ("templates/...")
  
Fehlerfall (Template nicht gefunden):
  ✓ ALLE Pfade aus allen Locations
  ✓ Alle file_exists() Ergebnisse
  ✓ CHURCHTOOLS_SUITE_PATH Debug-Info
```

**Beispiel Debug-Log:**

```json
{
  "level": "DEBUG",
  "context": "template_loader",
  "message": "Checking plugin template (DETAILED)",
  "data": {
    "path": "/var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php",
    "path_length": 94,
    "exists": false,
    "is_readable": false,
    "filesize": 0,
    "churchtools_suite_path": "/var/www/wp-content/plugins/churchtools-suite/",
    "relative_part": "templates/views/event-modal/professional.php"
  }
}
```

**Beispiel Fehlerlog:**

```json
{
  "level": "WARNING",
  "context": "template_loader",
  "message": "Template NOT FOUND - DETAILED ERROR",
  "data": {
    "template_name": "views/event-modal/professional.php",
    "plugin_template_path": "/var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php",
    "plugin_template_path_length": 94,
    "plugin_exists": false,
    "plugin_readable": false,
    "churchtools_suite_path": "/var/www/wp-content/plugins/churchtools-suite/",
    "churchtools_suite_path_length": 54,
    "churchtools_suite_path_defined": true
  }
}
```

---

## 🎯 Ziel dieser Version

**Diagnostizieren:**
- Warum `file_exists()` false zurückgibt, obwohl Datei existiert
- Ob `CHURCHTOOLS_SUITE_PATH` korrekt definiert ist
- Ob Dateiberechtigungen ein Problem sind (`is_readable`)
- Genaue Pfade für Path-Separator-Probleme (Windows vs Linux)

**Ermöglichkeit:**
- Userfreundliche Support-Anfragen ("Datei zu groß?", "Pfad falsch?")
- Automatische Fehler-Diagnose in Live-Umgebungen
- Vergleich: Dev-Server vs Live-Server Pfade

---

## 🔧 Technische Details

### Logging-Struktur (pro Check)

```php
// Start-Log mit Context
[DEBUG] Locating template START
  → template_name
  → churchtools_suite_path
  → path_length
  → path_defined

// Jeder Location-Check
[DEBUG] Checking [theme/parent/plugin] template
  → path (vollständig)
  → exists (boolean)
  → is_readable (boolean)
  → filesize (wenn exists)

// Wenn gefunden
[DEBUG] Template found in [location] (RETURNING)
  → path
  → filesize
  → is_readable

// Wenn NICHT gefunden
[WARNING] Template NOT FOUND - DETAILED ERROR
  → Alle Pfade
  → Alle exists/readable-Status
  → CHURCHTOOLS_SUITE_PATH Debug-Info
```

---

## 📊 Debugging-Guide

### Szenario 1: Plugin-Datei nicht gefunden (file_exists = false)

**Mögliche Ursachen:**
- CHURCHTOOLS_SUITE_PATH ist falsch
- Plugin-Datei wurde nicht hochgeladen
- Pfad-Separator-Problem (\ vs /)
- Symlinks auflösen sich nicht korrekt

**Debug-Schritte:**
1. Log zeigt: `plugin_exists: false`
2. SSH-Terminal: `ls -la [plugin_template_path]`
3. Vergleiche Pfad aus Log mit tatsächlicher Datei-Lokation

### Szenario 2: Datei existiert, aber is_readable = false

**Mögliche Ursachen:**
- Falsche File-Permissions (nicht 644)
- WordPress läuft unter anderem User (www-data vs nobody)
- SELinux/AppArmor blockt Zugriff

**Debug-Schritte:**
1. Log zeigt: `exists: true, is_readable: false`
2. SSH: `stat [plugin_template_path]` für Permissions
3. SSH: `whoami` für aktuellen User
4. SSH: `ls -l [plugin_template_path]`

### Szenario 3: CHURCHTOOLS_SUITE_PATH ist leer/undefined

**Mögliche Ursachen:**
- Plugin nicht korrekt geladen (define() fehlgeschlagen)
- define() wird nach locate_template() aufgerufen

**Debug-Schritte:**
1. Log zeigt: `churchtools_suite_path_defined: false` ODER `churchtools_suite_path: ""`
2. Check: Ist Plugin in wp-content/plugins/ ?
3. Check: plugin_dir_path() funktioniert korrekt

---

## 📈 Performance

- **Zusätzliche Aufrufe:** 3-4 file_exists() / is_readable() pro Request
- **Performance-Impact:** Negligible (~1ms)
- **Log-Size:** ~200-300 Bytes pro Template-Check

---

## 🚀 Nächste Schritte nach Debugging

1. **Live-Site Logs sammeln** (nach Deploy auf Live)
2. **User klickt Modal** in List-View
3. **Admin Dashboard → ChurchTools Suite → Erweitert → Logs** aufrufen
4. **Neue Einträge analysieren** mit den oben beschriebenen Szenarien
5. **Root-Cause beheben:**
   - Falsche CHURCHTOOLS_SUITE_PATH? → Fix in .htaccess/wp-config
   - Permissions-Problem? → SSH chmod 644
   - Pfad-Problem? → Path-Normalize in Code

---

## 📦 Deployment

```powershell
# ZIP bereits erstellt:
C:\privat\churchtools-suite-0.9.9.79.zip

# Auf Live-Server deployen:
# 1. Alte Version deaktivieren
# 2. Version 0.9.9.79 hochladen
# 3. Aktivieren
# 4. Admin-Dashboard → ChurchTools Suite → Erweitert → Logs
# 5. User klickt Modal im Frontend
# 6. Logs ansehen für detaillierte Fehler-Info
```

---

## ✅ Checkliste für Live-Test

- [ ] ZIP auf Server hochgeladen
- [ ] Plugin aktiviert
- [ ] Frontend: List-View mit Modal-Aktion geöffnet
- [ ] Klick auf Event in Liste
- [ ] Admin-Dashboard → Erweitert → Logs
- [ ] Neue Log-Einträge mit [template_loader] context
- [ ] Pfade und file_exists-Status überprüft
- [ ] Root-Cause identifiziert

---

## 🔄 Version-Info

- **Build:** 0.9.9.79
- **Git-Hash:** 6eaf677
- **Commit:** logging: v0.9.9.79 - DETAILED template path debugging
- **Files Modified:** 2 (churchtools-suite.php + template-loader.php)
- **Lines Added:** 53
- **Lines Removed:** 14

---

## 📝 Notizen

Diese Version ist **nicht öffentlich** und dient nur zur Diagnose. Sie sollte nach Identifikation der Root-Cause wieder entfernt werden.

Das Logging wird durch alle normalen Operationen hindurch ausgegeben, daher:
- Logs können schnell groß werden
- Vor Production-Einsatz: Normal zu v1.0.0 upgraden (weniger Debug-Output)

