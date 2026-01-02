# Release Notes v0.10.3.3

> **Version:** 0.10.3.3  
> **Release-Typ:** Patch (Bugfix)  
> **Datum:** 2. Januar 2026  
> **GitHub:** [v0.10.3.3](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.3)

---

## 🔧 Bugfix: Auto-Update Stufe wird respektiert

### Problem

**Symptom:**
- "Jetzt installieren" Button wurde angezeigt, unabhängig von der "Auto-Update Stufe" Einstellung
- Beispiel: Bei Einstellung "Nur Major-Updates" wurde trotzdem der Install-Button für Build/Patch/Minor Updates angezeigt
- Benutzer konnte Updates installieren, die laut Konfiguration nicht installiert werden sollten

**Ursache:**
- Dashboard prüfte nur `auto_update_enabled`, aber NICHT `auto_update_level`
- Button-Logik ignorierte die konfigurierte Update-Stufe

---

### Lösung

**Intelligente Button-Anzeige:**
- Dashboard liest jetzt `auto_update_level` Einstellung
- Button wird nur angezeigt wenn Update-Typ ≤ konfigurierte Stufe

**Update-Stufen:**
1. **major** - Nur Major-Updates (1.0.0 → 2.0.0)
2. **major_minor** - Major + Minor (1.0.0 → 1.1.0)
3. **major_minor_patch** - Major + Minor + Patch (1.0.0 → 1.0.1)
4. **all** - Alle Updates inkl. Build (1.0.0.0 → 1.0.0.1)

**Beispiel-Szenarien:**

| Einstellung | Verfügbares Update | Button sichtbar? |
|-------------|-------------------|------------------|
| Nur Major-Updates | 0.10.3.2 → 0.10.3.3 (Build) | ❌ Nein |
| Nur Major-Updates | 0.10.0.0 → 1.0.0.0 (Major) | ✅ Ja |
| Major + Minor | 0.10.3.2 → 0.11.0.0 (Minor) | ✅ Ja |
| Major + Minor | 0.10.3.2 → 0.10.4.0 (Patch) | ❌ Nein |
| Alle Updates | 0.10.3.2 → 0.10.3.3 (Build) | ✅ Ja |

---

## 📝 Änderungen

### Geänderte Dateien

**admin/views/tab-dashboard.php:**

```php
// NEU: Auto-Update Level laden (Zeile 64)
$auto_update_level = get_option( 'churchtools_suite_auto_update_level', 'major_minor_patch' );

// NEU: Prüfen ob Update erlaubt ist (Zeilen 106-117)
$update_allowed = false;
switch ( $auto_update_level ) {
    case 'major':
        $update_allowed = ( $update_type === 'major' );
        break;
    case 'major_minor':
        $update_allowed = in_array( $update_type, [ 'major', 'minor' ], true );
        break;
    case 'major_minor_patch':
        $update_allowed = in_array( $update_type, [ 'major', 'minor', 'patch' ], true );
        break;
    case 'all':
        $update_allowed = true;
        break;
}

// NEU: Hinweis wenn Update nicht erlaubt (Zeilen 145-155)
<?php elseif ( $auto_update_enabled && ! $update_allowed ) : ?>
    <p style="...">
        ℹ️ Dieses Update wird nicht automatisch installiert (Stufe: major).
        <a href="...">Einstellungen ändern</a>
    </p>
<?php endif; ?>

// NEU: Button nur bei erlaubtem Update (Zeile 159)
<?php if ( $auto_update_enabled && $update_allowed ) : ?>
    <button id="cts_install_update_btn">Jetzt installieren</button>
<?php else : ?>
    <a href="...">
        <?php if ( ! $auto_update_enabled ) : ?>
            Auto-Update aktivieren
        <?php else : ?>
            Update-Stufe anpassen
        <?php endif; ?>
    </a>
<?php endif; ?>
```

**churchtools-suite.php:**
- Version 0.10.3.2 → 0.10.3.3

---

## 🚀 Installation

### Automatisches Update
Falls Auto-Update aktiviert **UND** Stufe passend:
1. Update wird automatisch installiert ✅

### Manuelles Update
1. Plugin über WordPress Admin updaten
2. Dashboard wird automatisch neu geladen
3. Fertig! ✅

---

## ✅ Testing

### Test-Szenarien

#### Szenario 1: "Nur Major-Updates"
- Einstellung: `major`
- Verfügbar: v0.10.3.3 (Build)
- **Erwartung:** ❌ Button NICHT sichtbar
- **Tatsächlich:** ✅ Funktioniert

#### Szenario 2: "Major + Minor"
- Einstellung: `major_minor`
- Verfügbar: v0.11.0.0 (Minor)
- **Erwartung:** ✅ Button sichtbar
- **Tatsächlich:** ✅ Funktioniert

#### Szenario 3: "Alle Updates"
- Einstellung: `all`
- Verfügbar: v0.10.3.3 (Build)
- **Erwartung:** ✅ Button sichtbar
- **Tatsächlich:** ✅ Funktioniert

### Getestet auf
- WordPress 6.4+
- PHP 8.0, 8.1, 8.2

### Test-Checkliste
- [x] Button wird nur bei erlaubter Stufe angezeigt
- [x] Hinweis bei nicht-erlaubtem Update
- [x] Link zu Settings funktioniert
- [x] Button-Text passt sich an (aktivieren vs. anpassen)
- [x] Auto-Update respektiert Stufe

---

## 🔍 Technische Details

### Update-Stufen Matching

```php
// Update-Typ Hierarchie (niedrig → hoch):
// build < patch < minor < major

// Matching-Logik:
switch ( $auto_update_level ) {
    case 'major':           // Nur Major
        $allowed = ( $type === 'major' );
        break;
    
    case 'major_minor':     // Major + Minor
        $allowed = in_array( $type, [ 'major', 'minor' ] );
        break;
    
    case 'major_minor_patch': // Major + Minor + Patch
        $allowed = in_array( $type, [ 'major', 'minor', 'patch' ] );
        break;
    
    case 'all':             // Alle (inkl. Build)
        $allowed = true;
        break;
}
```

### UI-Logik Flowchart

```
Update verfügbar?
    └─ Ja
        └─ Auto-Update aktiviert?
            ├─ Nein → Button: "Auto-Update aktivieren"
            └─ Ja
                └─ Update-Typ erlaubt?
                    ├─ Nein → Button: "Update-Stufe anpassen" + Hinweis
                    └─ Ja → Button: "Jetzt installieren"
```

---

## 📊 Vergleich

### Vorher (v0.10.3.2)

```php
// Nur auto_update_enabled geprüft
if ( $auto_update_enabled ) {
    // Button immer anzeigen ❌
}
```

**Problem:** Build-Updates konnten installiert werden, obwohl Stufe = "Nur Major"

### Nachher (v0.10.3.3)

```php
// auto_update_enabled UND auto_update_level geprüft
if ( $auto_update_enabled && $update_allowed ) {
    // Button nur bei passender Stufe ✅
}
```

**Lösung:** Button erscheint nur wenn Update-Typ ≤ konfigurierte Stufe

---

## ⚠️ Upgrade Notes

### Von v0.10.3.2 zu v0.10.3.3:
- **Breaking Changes:** Keine
- **Neue Features:** Respektiert Auto-Update Stufe
- **Bugfixes:** Button-Anzeige korrigiert
- **Datenbank:** Keine Änderungen
- **Kompatibilität:** 100% kompatibel mit v0.10.3.2

### Empfohlene Aktion nach Update:
1. Dashboard öffnen
2. Prüfen ob Update-Benachrichtigung korrekt angezeigt wird
3. Einstellung "Auto-Update Stufe" nach Bedarf anpassen

---

## 🔗 Links

- [GitHub Release](https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.3.3)
- [GitHub Commit](https://github.com/FEGAschaffenburg/churchtools-suite/commit/HEAD)
- [Plugin Homepage](https://plugin.feg-aschaffenburg.de)
- [Dokumentation](https://plugin.feg-aschaffenburg.de/docs/)

---

## 📊 Statistik

- **Dateien geändert:** 2
- **Neue Dateien:** 0
- **Gelöschte Dateien:** 0
- **Zeilen Code:** +25, -8
- **Commits:** 1

---

## 👥 Credits

**Entwickler:** FEG Aschaffenburg  
**Bug-Report:** Production Testing  
**Testing:** Live Environment

---

## 🔄 Nächste Schritte

### Version 0.10.4.0 (geplant)
- Weitere Template-Optimierungen
- Performance-Verbesserungen
- Neue Shortcode-Parameter

### Version 1.0.0 (Roadmap)
- Stable Release
- Production Ready
- WordPress.org Submission

---

**🎉 Vielen Dank für die Nutzung von ChurchTools Suite!**
