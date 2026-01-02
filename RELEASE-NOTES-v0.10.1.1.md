# Release Notes: ChurchTools Suite v0.10.1.1

**Release Date:** 2. Januar 2026  
**Type:** Bugfix Release  
**Status:** Production Ready

---

## 🐛 Bugfix

### Kritischer Syntax-Fehler behoben

**Problem:**  
Parse-Error in `templates/list/classic.php` Zeile 119:
```
Parse error: syntax error, unexpected token "endif"
```

**Ursache:**  
Doppelte Code-Zeilen (116-119) durch versehentliche Duplikation beim letzten Update.

**Lösung:**  
- 4 duplizierte Zeilen entfernt
- Template-Syntax korrigiert
- Keine funktionalen Änderungen

---

## 🔧 Geänderte Dateien

**`templates/list/classic.php`**
- Zeilen 116-119 entfernt (Duplikate)
- Korrekte endif-Struktur wiederhergestellt

**`churchtools-suite.php`**
- Version: 0.10.1.0 → 0.10.1.1

---

## 🚀 Upgrade-Anleitung

### Automatisches Update
Plugin wird automatisch aktualisiert (wenn Auto-Update aktiv).

### Manuelles Update
1. Backup erstellen
2. Neues ZIP hochladen
3. Plugin aktivieren

### Git Pull
```bash
cd /var/www/.../wp-content/plugins/churchtools-suite
git pull origin main
```

---

## 📊 Änderungen

- **Commits:** 2 (Bugfix + Version-Bump)
- **Dateien geändert:** 2
- **Zeilen entfernt:** 4 (dupliziert)
- **Funktionale Änderungen:** 0

---

## ✅ Kompatibilität

- WordPress 6.0+
- PHP 8.0+
- Alle Features von v0.10.1.0 bleiben erhalten

---

## 🔗 Links

- **GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.1.1
- **Vorherige Version:** v0.10.1.0

---

**Update-Empfehlung:** ⚠️ **Dringend empfohlen** - Behebt kritischen Parse-Error
