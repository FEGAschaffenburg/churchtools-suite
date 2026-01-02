# Release Notes v0.10.1.2

**Datum:** 2. Januar 2026  
**Typ:** Bugfix Release  
**Priorität:** Hoch (kritische Template-Fehler)

---

## 🐛 Bugfixes

### Template-Syntax Fehler behoben
**Problem:** List/Classic Template hatte schwerwiegende Syntax-Fehler:
1. **v0.10.1.1 Fix:** Doppelte `endif`-Zeilen verursachten Parse Error
2. **v0.10.1.2 Fix:** Fehlende Schließungs-Tags am Dateiende (`endforeach`, `endif`, `</div>`)

**Impact:** Template war nicht verwendbar, verursachte Fatal Errors

**Lösung:**
- ✅ Alle doppelten Zeilen entfernt
- ✅ Alle fehlenden Schließungs-Tags hinzugefügt
- ✅ Template vollständig funktionsfähig

**Betroffene Datei:**
- `templates/list/classic.php`

---

### Update-Completion Hook implementiert
**Problem:** Nach manuellem Update aus dem WordPress Dashboard:
- Keine "Fertig"-Meldung angezeigt
- Kein automatischer Seiten-Refresh
- User musste manuell aktualisieren

**Lösung:**
- `upgrader_process_complete` Hook hinzugefügt
- Cache-Clearing nach erfolgreichen Updates
- WordPress-Refresh automatisch ausgelöst
- Erfolgreiche Updates werden geloggt

**Betroffene Datei:**
- `includes/class-churchtools-suite-update-checker.php`

**Neue Methode:**
```php
public static function after_update( $upgrader, $hook_extra ): void
```

---

## 📦 Technische Details

### Git Commits
- `5022e27` - Update-Completion-Hook hinzugefügt
- `90cc499` - Fehlende Schließungs-Tags in list/classic.php

### Dateiänderungen
| Datei | Änderungen | Zeilen |
|-------|------------|--------|
| `class-churchtools-suite-update-checker.php` | Hook + Methode hinzugefügt | +57 |
| `templates/list/classic.php` | Schließungs-Tags ergänzt | +7 |

---

## ⚠️ Upgrade-Hinweise

**Von v0.10.1.0 oder v0.10.1.1:**
- Kein manueller Eingriff nötig
- Automatisches Update empfohlen
- Nach Update: Liste-Classic Template neu testen

**Kritikalität:**
- **HOCH** wenn List/Classic Template verwendet wird
- **MITTEL** für bessere Update-UX

---

## 🧪 Testing

**Erfolgreich getestet:**
- ✅ List/Classic Template rendert ohne Fehler
- ✅ Update-Completion zeigt "Fertig"-Meldung
- ✅ Automatischer Refresh nach Update
- ✅ Keine Parse Errors mehr

**Test-Umgebung:**
- WordPress 6.4+
- PHP 8.0+
- Produktiv-Installation (web2945)

---

## 🔗 Links

**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.1.2  
**Download:** [churchtools-suite-0.10.1.2.zip](https://github.com/FEGAschaffenburg/churchtools-suite/releases/download/v0.10.1.2/churchtools-suite-0.10.1.2.zip)

---

**Vorherige Version:** [v0.10.1.1](RELEASE-NOTES-v0.10.1.1.md)  
**Nächste Version:** TBD
