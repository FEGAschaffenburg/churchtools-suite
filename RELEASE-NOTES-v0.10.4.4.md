# Release Notes - v0.10.4.4

**Release-Datum:** 5. Januar 2026  
**Typ:** Critical Bugfix  
**Priorität:** Hoch 🚨

---

## 🐛 Behobene Fehler

### JavaScript Error Handling - Undefined Error Crashes

**Problem:**
- Dashboard Sync Button: "Cannot read properties of undefined (reading 'message')"
- Alle AJAX-Fehlerhandler crashten wenn `error` undefined war
- Betraf 12 verschiedene AJAX-Funktionen

**Root Cause:**
```javascript
// VORHER (crasht wenn error undefined):
.catch(error => {
    result.innerHTML = 'Fehler: ' + error.message;
})

// NACHHER (null-safe):
.catch(error => {
    const errorMsg = error?.message || 'Unbekannter Fehler';
    result.innerHTML = 'Fehler: ' + errorMsg;
})
```

**Betroffene Funktionen:**
1. `initSyncButton()` - Dashboard Sync
2. `initDataHeaderSync()` - Data Header Sync
3. `initTestConnection()` - Connection Test
4. `initCalendarSync()` - Calendar Sync
5. `initCalendarSelection()` - Calendar Selection
6. `initServiceGroupSync()` - Service Group Sync
7. `initServiceGroupSelection()` - Service Group Selection
8. `initServiceSync()` - Service Sync
9. `initServiceSelection()` - Service Selection
10. `initEventSync()` - Event Sync
11. Database Reset Handler
12. Session Keepalive Handler

**Lösung:**
- Alle `error.message` durch `error?.message || 'Unbekannter Fehler'` ersetzt
- Optional Chaining (`?.`) verhindert Crash bei undefined
- Fallback-Message wenn error.message fehlt

---

## 📝 Geänderte Dateien

- `assets/js/churchtools-suite-admin.js`: 12 AJAX Error Handler gefixt
- `churchtools-suite.php`: Version bump zu 0.10.4.4

---

## 🎯 Auswirkungen

**Vorher:**
```
[User klickt Sync Button]
❌ JavaScript Crash: "Cannot read properties of undefined"
❌ Keine Fehlermeldung für User
❌ Button bleibt "loading" stecken
```

**Nachher:**
```
[User klickt Sync Button]
✅ Error wird sauber gefangen
✅ User sieht: "Fehler: [Details]" oder "Fehler: Unbekannter Fehler"
✅ Button kehrt zu Normalzustand zurück
```

---

## ⚠️ Breaking Changes

Keine.

---

## 🔄 Migration

Keine Datenbank-Änderungen.

---

## 🧪 Testing

1. Plugin auf v0.10.4.4 aktualisieren
2. Dashboard → Sync Button klicken
3. Bei Fehler: Sollte saubere Fehlermeldung zeigen
4. Button sollte nicht mehr "hängen"

---

## 📚 Technische Details

**JavaScript Optional Chaining:**
```javascript
// Alte Syntax (crasht):
error.message

// Neue Syntax (safe):
error?.message || 'Fallback'
```

**Browser Support:**
- ✅ Chrome 80+ (März 2020)
- ✅ Firefox 74+ (März 2020)
- ✅ Safari 13.1+ (März 2020)
- ✅ Edge 80+ (Februar 2020)

Alle modernen Browser werden unterstützt (WordPress 6.0+ erfordert ohnehin moderne Browser).

---

## 🔗 Links

- **GitHub Commit:** https://github.com/FEGAschaffenburg/churchtools-suite
- **Vorherige Version:** v0.10.4.3
- **Issue:** Sync Button crashed mit "Cannot read properties of undefined"

---

**Hinweis:** Dieser Fix behebt einen kritischen Frontend-Fehler der alle AJAX-Operationen betraf.
