# ChurchTools Suite - Release Notes v0.10.4.7

**Datum:** 5. Januar 2026  
**Art:** KRITISCHER BUGFIX - Tags Import Fix

---

## 🔥 KRITISCHER FIX: Tags Import Funktioniert Jetzt!

### Problem
**Tags wurden NICHT importiert** - alle Appointments zeigten `KEINE TAGS in API-Response`.

**Root Cause:** WordPress `add_query_arg()` unterstützt NICHT die ChurchTools API-Syntax für Array-Parameter!

```php
// ❌ FALSCH (bisheriger Code):
add_query_arg(['include' => ['tags', 'event']], $url)
// Resultat: ?include=tags&include=event (überschreibt sich selbst!)

// ✅ RICHTIG (neuer Code):
// Resultat: ?include[]=tags&include[]=event
```

ChurchTools API erwartet: `include[]=tags&include[]=event`  
WordPress erzeugte: `include=event` (nur letzter Wert!)

---

## 📝 Änderungen

### Datei: `includes/class-churchtools-suite-ct-client.php`

**Funktion:** `api_request()` (Zeile 267-288)

**NEU:** Manuelle Query-String-Konstruktion für Array-Parameter

```php
// v0.10.4.7: Build query string manually to support array parameters
$query_parts = [];
foreach ($data as $key => $value) {
    if (is_array($value)) {
        // Array parameters: include[] => ['tags', 'event']
        foreach ($value as $item) {
            $query_parts[] = urlencode($key) . '[]=' . urlencode($item);
        }
    } else {
        // Simple parameters: from => '2024-01-01'
        $query_parts[] = urlencode($key) . '=' . urlencode($value);
    }
}
```

**Vorher:**
```php
if ($method === 'GET' && !empty($data)) {
    $url = add_query_arg($data, $url);
}
```

---

## ✅ Testing

### Beispiel-API-Aufruf

**Input (Code):**
```php
$api_params = [
    'from' => '2024-01-01',
    'to' => '2024-12-31',
    'include' => ['tags', 'event', 'group']
];
```

**Output (URL) - VORHER (❌ FALSCH):**
```
/api/calendars/42/appointments?from=2024-01-01&to=2024-12-31&include=group
```
→ Nur `group` übrig, `tags` und `event` verloren!

**Output (URL) - NACHHER (✅ RICHTIG):**
```
/api/calendars/42/appointments?from=2024-01-01&to=2024-12-31&include[]=tags&include[]=event&include[]=group
```

---

## 🚀 Deployment

```powershell
cd C:\privat\churchtools-suite\scripts
.\create-wp-zip.ps1 -Version "0.10.4.7"

cd C:\privat\churchtools-suite
gh release create v0.10.4.7 --title "v0.10.4.7 - CRITICAL: Tags Import Fix" -F RELEASE-NOTES-v0.10.4.7.md C:\privat\churchtools-suite-0.10.4.7.zip
```

---

## ⚠️ WICHTIG für Benutzer

**Nach dem Update:**

1. **Plugin updaten** auf v0.10.4.7
2. **Manuellen Sync durchführen:** ChurchTools Suite → Synchronisation → "Jetzt synchronisieren"
3. **Logs prüfen:** Erweitert → Logs → "FIRST APPOINTMENT SAMPLE" sollte jetzt `tags` enthalten
4. **Tags sollten sichtbar sein** in Templates (wenn ChurchTools Tags hat!)

---

## 📊 Impact

**Betroffene Features:**
- ✅ Tags Import (funktioniert jetzt!)
- ✅ Alle Templates mit `show_tags="true"`
- ✅ Event Details Modal (Tags-Badges)

**Betroffene API-Aufrufe:**
- `/calendars/{id}/appointments?include[]=tags` (Phase 2 Sync)
- Alle anderen API-Calls mit Array-Parametern

---

## 🔗 Timeline

- **v0.10.4.0-0.10.4.2:** Tags Support in Templates hinzugefügt
- **v0.10.4.3-0.10.4.6:** Syntax-Fehler-Katastrophe (2 Emergency Hotfixes)
- **v0.10.4.7:** Tags Import FIX - `add_query_arg()` Problem behoben

---

**Migration:** Keine Datenbankänderungen  
**Breaking Changes:** Keine  
**Rückwärtskompatibilität:** Voll kompatibel
