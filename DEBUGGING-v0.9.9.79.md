# v0.9.9.79 - Debugging Guide für "Modal nicht geöffnet"

## 🎯 Schnellstart Debugging auf Live-Server

Nach Deploy von v0.9.9.79:

### Step 1: Plugin aktivieren
```
WordPress Admin → Plugins → ChurchTools Suite → Aktivieren
```

### Step 2: Test-Klick durchführen
```
Frontend:
1. Gehe auf Seite mit [cts_events] Shortcode (List-View)
2. Klicke auf ein Event in der Liste
3. Modal sollte öffnen ODER nichts passiert
```

### Step 3: Logs ansehen
```
WordPress Admin:
1. ChurchTools Suite → Erweitert → Logs
2. Dort nach "template_loader" oder "ajax_modal" suchen
3. Die Logs zeigen EXAKT warum Modal nicht geladen wurde
```

---

## 🔍 Was die Logs dir zeigen

### Szenario A: Template WAS FOUND ✅

```
[DEBUG] Locating template START
  ✓ template_name: "views/event-modal/professional.php"
  ✓ churchtools_suite_path: "/var/www/wp-content/plugins/churchtools-suite/"

[DEBUG] Checking plugin template (DETAILED)
  ✓ path: "/var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php"
  ✓ exists: true
  ✓ is_readable: true
  ✓ filesize: 4872

[DEBUG] Template found in plugin (RETURNING)
  ✓ path: "/var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php"
  ✓ filesize: 4872
```

**Wenn diese Logs erscheinen:** ✅ Template wird GEFUNDEN  
**Aber Modal öffnet noch nicht?** → Problem liegt nicht im Template-Loading, sondern im JavaScript oder AJAX

---

### Szenario B: Template NICHT GEFUNDEN ❌

```
[WARNING] Template NOT FOUND - DETAILED ERROR
  ❌ plugin_exists: false
  ❌ plugin_readable: false
  ✓ churchtools_suite_path: "/var/www/wp-content/plugins/churchtools-suite/"
  ✓ churchtools_suite_path_defined: true
```

**Was bedeutet das?**
- Plugin-Pfad ist KORREKT ✓
- ABER Datei existiert NICHT ❌

**Behebung:**
```bash
# SSH Terminal
ls -la /var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php

# Wenn Datei nicht exists:
# → ZIP nicht korrekt hochgeladen
# → Neuen ZIP hochladen!
```

---

### Szenario C: CHURCHTOOLS_SUITE_PATH ist falsch ❌

```
[WARNING] Template NOT FOUND - DETAILED ERROR
  ❌ churchtools_suite_path: ""
  ❌ churchtools_suite_path_defined: false
```

**Was bedeutet das?**
- Define wurde nicht aufgerufen
- Plugin-Konstante ist NICHT definiert

**Behebung:**
```
1. Plugin deaktivieren
2. Plugin löschen
3. ZIP neu hochladen (korrekt in wp-content/plugins/)
4. Aktivieren
```

---

### Szenario D: Datei existiert, aber is_readable = false ❌

```
[DEBUG] Checking plugin template (DETAILED)
  ✓ exists: true
  ✓ is_readable: false  ← PROBLEM!
  ✓ filesize: 4872
```

**Was bedeutet das?**
- Datei IST hochgeladen ✓
- ABER WordPress kann sie nicht LESEN ❌

**Ursachen & Lösungen:**

```bash
# SSH Terminal - Aktuellen User checken
whoami
# Output: www-data oder nobody

# Datei-Permissions anschauen
ls -l /var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php
# Output: -rw-r--r-- (644 = OK)

# Falls Permissions falsch:
chmod 644 /var/www/wp-content/plugins/churchtools-suite/templates/views/event-modal/professional.php

# Falls Permissions OK aber is_readable noch false:
# → SELinux/AppArmor Problem
# → Contact Hosting Provider
```

---

## 📊 Debugging Workflow

```
1. Plugin deployen (v0.9.9.79)
2. Frontend: Event klicken
3. Admin Logs ansehen
   ├─ Szenario A? → JavaScript/AJAX Problem → nächste Debug-Version
   ├─ Szenario B? → ZIP nicht korrekt hochgeladen
   ├─ Szenario C? → Plugin nicht korrekt im Ordner
   └─ Szenario D? → File-Permissions Problem
4. Beheben entsprechend
5. Test wiederholen
```

---

## 🔧 Erweiterte Debug-Info

### admin/class-churchtools-suite-admin.php auch verbessert

Mit v0.9.9.79 loggt der AJAX-Handler jetzt auch:

```json
{
  "Dashboard settings loaded",
  "churchtools_suite_modal_template": "professional",
  "churchtools_suite_single_template": "professional",
  "from_setting": "modal"
}
```

Das zeigt, dass:
- ✅ Dashboard-Settings werden GELESEN
- ✅ Richtige Template wird AUSGEWÄHLT
- ✅ AJAX-Handler AUFGERUFEN wird

Wenn diese Logs NICHT erscheinen:
- Event-Klick aktiviert JavaScript nicht
- Frontend JavaScript-Fehler

---

## 🚀 Nachdem Root-Cause gefunden ist

### Wenn ZIP-Problem:
```
1. Delete alte Version
2. ZIP neu hochladen
3. Test
```

### Wenn Permissions-Problem:
```
1. SSH chmod 644 auf Dateien
2. Test
```

### Wenn JavaScript-Problem (nächste Version):
```
1. Browser-Konsole (F12) öffnen
2. Dort werden Fehler von churchtools-suite-public.js gezeigt
3. Version 0.9.9.80 wird noch bessere JS-Logs hinzufügen
```

---

## 📞 Support-Tickets mit Logs

Wenn Support nötig ist, bitte diese Infos teilen:

```
1. WordPress-Version
2. PHP-Version
3. Hosting-Provider
4. Logs aus: Admin → ChurchTools Suite → Erweitert → Logs
5. Ausgabe von: ls -l /var/www/wp-content/plugins/churchtools-suite/templates/
```

Mit dieser Info können wir Root-Cause in 5 Minuten finden!

---

## ✅ Fertig!

Nach Implementierung von v0.9.9.79 solltest du exakt sehen, warum Modal nicht geladen wird. Teile die Logs, und wir finden die Root-Cause schnell!

