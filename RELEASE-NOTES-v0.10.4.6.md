# Release Notes - v0.10.4.6

**Release-Datum:** 5. Januar 2026  
**Typ:** CRITICAL HOTFIX #2 🚨🚨🚨  
**Priorität:** SOFORT

---

## 🐛 Behobener Fatal Error #2

### Zweiter Syntax Error in Event Sync Service

**Problem:**
```
Fatal Error: syntax error, unexpected token "=>" 
File: class-churchtools-suite-event-sync-service.php:882
```

**Root Cause:**
v0.10.4.3 Debug-Logging-Edit hatte ZWEI Fehler:
1. ✅ Zeile 566 - `$stats` Array (gefixt in v0.10.4.5)
2. ❌ Zeile 882 - Duplizierter/beschädigter Debug-Code

```php
// KAPUTT (v0.10.4.3-5):
ChurchTools_Suite_Logger::debug(...);  // ✅ OK

    'has_tags' => isset($appointment['tags']),  // ❌ Orphaned array!
    'appointment_keys' => array_keys($appointment),
    'base_keys' => isset($appointment['base']) ? array_keys($appointment['base']) : [],
]
);

// GEFIXT (v0.10.4.6):
ChurchTools_Suite_Logger::debug(...);  // ✅ OK

// Extract all available fields...  // ✅ Cleaned up
```

**Symptom:**
- ❌ v0.10.4.5 immer noch kaputt (zweiter Syntax Error)
- ❌ Alle Syncs crashten weiterhin

---

## ✅ Lösung

Entfernung des duplizierten/beschädigten Array-Codes nach dem Debug-Logging.

---

## 📝 Geänderte Dateien

- `includes/services/class-churchtools-suite-event-sync-service.php`: Duplikat-Code entfernt
- `churchtools-suite.php`: Version bump zu 0.10.4.6

---

## ⚠️ WICHTIG

**DO NOT USE:**
- ❌ v0.10.4.3 (Syntax Error #1 + #2)
- ❌ v0.10.4.4 (Syntax Error #1 + #2)
- ❌ v0.10.4.5 (Syntax Error #2 blieb)

**USE THIS:**
- ✅ v0.10.4.6 (BEIDE Syntax Errors gefixt)
- ✅ v0.10.4.2 (Letzte stabile Version vor Debug-Logging)

---

## 📚 Timeline

- **v0.10.4.2:** ✅ Stabil
- **v0.10.4.3:** ❌ BROKEN (2 Syntax Errors eingeführt)
- **v0.10.4.4:** ❌ BROKEN (JS Fix, Syntax Errors blieben)
- **v0.10.4.5:** ❌ BROKEN (Syntax Error #1 gefixt, #2 blieb)
- **v0.10.4.6:** ✅ GEFIXT (BEIDE Syntax Errors behoben)

---

## 🔗 Links

- **GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.4.6
- **Vorherige Version:** v0.10.4.2 (letzte funktionierende Version)

---

**JETZT sollte es wirklich funktionieren! Entschuldigung für die mehrfachen kaputten Releases.** 😓
