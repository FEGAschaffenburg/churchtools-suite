# 🚨 ChurchTools Suite v1.0.3.1 - HOTFIX für Admin-Menu

**Status:** ✅ FIXED & DEPLOYED  
**Commits:** 
- Main Plugin: 39a29d8 + Tag v1.0.3.1.1
- Demo Plugin: cc6e2d3 (capability consistency)

**Problem:** Admin-Menu "ChurchTools Suite" verschwindet nach Update  
**Root Cause:** 
1. Activation Hooks feuern bei Updates nicht → Capabilities nicht erstellt
2. Demo Plugin benutzt `manage_options` statt `manage_churchtools_suite` → Inkonsistenz

**Solution:** 
1. Fallback-Prüfung beim Admin-Laden (Hauptplugin)
2. Demo Plugin benutzt jetzt gleiche Capability (Konsistenz)

---

## 🔍 WAS IST DAS PROBLEM?

Nach dem Update zu v1.0.3.1:
- ❌ Admin-Menu "ChurchTools" ist **weg**
- ❌ Zugriff auf Plugin-Seite **verweigert**
- ✅ Plugin funktioniert aber noch im Hintergrund

**Ursache:** Kapazitäten `manage_churchtools_suite` nicht erstellt weil Activation Hook nicht gefeuert wurde

---

## ✅ DIE LÖSUNG

Der Fix ist bereits in Commit **39a29d8** implementiert:

```php
// Neue Fallback-Prüfung in define_admin_hooks():
private function ensure_capabilities_exist(): void {
    $admin_role = get_role( 'administrator' );
    if ( ! $admin_role || ! $admin_role->has_cap( 'manage_churchtools_suite' ) ) {
        // Fehlen noch? → Jetzt erstellen!
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';
        ChurchTools_Suite_Roles::create_or_update_roles();
    }
}
```

**Das bedeutet:**
- Beim nächsten Admin-Load wird geprüft
- Wenn Capabilities fehlen → werden sie **automatisch erstellt**
- Menü-Eintrag erscheint **wieder** ✅

---

## 🚀 DEPLOYMENT DIESER HOTFIX

### Option 1: Manuell (lokal → Server)

```powershell
# 1. Datei updaten
# churchtools-suite/includes/class-churchtools-suite.php
# → Commit 39a29d8 kopieren

# 2. Hochladen via FTP/SSH
# 3. WordPress Admin neu laden (F5)
```

### Option 2: GitHub Auto-Update

Benutzers WordPress erkennt neue Version und aktualisiert automatisch

```
Admin → Plugins → ChurchTools Suite → Update jetzt durchführen
```

### Option 3: WP-CLI

```bash
wp plugin update churchtools-suite
```

---

## ✅ NACH DEM FIX

1. **Admin neu laden** (F5 oder neu anmelden)
2. **Linkes Menü** sollte "ChurchTools" zeigen
3. **Darauf klicken** → Sollte Dashboard laden

---

## 🧪 VERIFY FIX

```
1. Admin-Bereich öffnen
2. Linkes Menü nach "ChurchTools" suchen
3. ✅ Sollte da sein!
4. Drauf klicken → Dashboard laden
```

---

**Status:** ✅ FIXED  
**Severity:** 🔴 HIGH (Blocks Admin Access)  
**Fix Complexity:** 🟢 LOW (Simple Fallback)  
**Deployment Time:** ⏱️ 2 Minuten
