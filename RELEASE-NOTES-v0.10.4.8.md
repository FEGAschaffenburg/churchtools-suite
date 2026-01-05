# ChurchTools Suite - Release Notes v0.10.4.8

**Datum:** 5. Januar 2026  
**Art:** BUGFIX - UI Improvement

---

## 🐛 Bugfix: "Logs neu laden" Button funktioniert jetzt

### Problem
Der Button "🔄 Logs neu laden" in **Erweitert → Logs** hatte **keine Funktion**.

**Ursache:** Fehlender JavaScript Event-Handler.

---

## 📝 Änderungen

### Datei: `admin/views/debug/subtab-logs.php`

**NEU:** Click-Handler für Reload-Button hinzugefügt (Zeile 61-66)

```javascript
// Logs neu laden Button (v0.10.4.8)
$('#cts-reload-logs').on('click', function(e){
    e.preventDefault();
    location.reload();
});
```

**Verhalten:**
- Button löst jetzt `location.reload()` aus
- Seite wird neu geladen → aktuelle Logs werden angezeigt

---

## ✅ Testing

**Schritte:**
1. WordPress Admin → ChurchTools Suite → Erweitert → Logs
2. Klick auf "🔄 Logs neu laden"
3. **Erwartet:** Seite lädt neu, Logs werden aktualisiert
4. **Vorher:** Button tat nichts

---

## 🚀 Deployment

```powershell
cd C:\privat\churchtools-suite\scripts
.\create-wp-zip.ps1 -Version "0.10.4.8"

cd C:\privat\churchtools-suite
gh release create v0.10.4.8 --title "v0.10.4.8 - Bugfix: Logs Reload Button" -F RELEASE-NOTES-v0.10.4.8.md C:\privat\churchtools-suite-0.10.4.8.zip
```

---

## 📊 Impact

**Betroffene Features:**
- ✅ Erweitert → Logs Tab (Reload-Button funktioniert)

**Betroffene Dateien:**
- `admin/views/debug/subtab-logs.php`

---

**Migration:** Keine  
**Breaking Changes:** Keine  
**Rückwärtskompatibilität:** Voll kompatibel
