# Release Notes v0.10.1.3

**Datum:** 2. Januar 2026  
**Typ:** Bugfix Release  
**Priorität:** Hoch (kritische Template-Fehler + UX-Verbesserung)

---

## 🐛 Bugfixes

### List/Modern Template komplett repariert
**Problem:** Template hatte mehrere schwerwiegende Syntax-Fehler:
1. Doppelter Code-Block (Zeilen 77-87)
2. Orphan-Services-Block am Dateiende (außerhalb foreach-Schleife)

**Lösung:**
- ✅ Doppelten Location-Code entfernt
- ✅ Verwaisten Services-Block am Ende entfernt
- ✅ Template vollständig funktionsfähig

**Betroffene Datei:**
- `templates/list/modern.php`

---

### Platzhalter-Views aus Page Buildern entfernt
**Problem:** Gutenberg & Elementor zeigten 8 nicht-implementierte View-Typen an:
- slider, masonry, agenda, timetable, carousel, countdown, cover, map
- Diese Templates enthalten nur `🚧 In Entwicklung` Platzhalter
- **Resultat:** User wählten Views aus, die nicht funktionieren

**Lösung:**
- ✅ Nur **5 funktionierende View-Typen** werden angezeigt:
  1. **List** (6 Varianten: classic, classic-services, modern, medium, fluent, compact)
  2. **Calendar** (1 Variante: monthly-modern)
  3. **Grid** (3 Varianten: simple, colorful, modern)
  4. **Search** (1 Variante: classic)
  5. **Widget** (1 Variante: upcoming)
- ✅ TODO-Kommentare in Code für spätere Implementierung

**Betroffene Dateien:**
- `assets/js/churchtools-suite-blocks.js` (Gutenberg)
- `includes/class-churchtools-suite-elementor-widget.php` (Elementor)

**Code-Reduktion:**
- Gutenberg: 13 → 5 View-Typen
- Elementor: 13 → 5 View-Typen (inkl. Controls + Switch-Cases)
- **-331 Zeilen** toten Code entfernt

---

## 📦 Technische Details

### Git Commits
- `90cc499` - List/Classic fehlende Schließungs-Tags
- `6815364` - List/Modern doppelter Code + orphan-Block
- `6070d8e` - Platzhalter-Views aus Page Buildern entfernt

### Dateiänderungen
| Datei | Änderungen | Impact |
|-------|------------|--------|
| `templates/list/classic.php` | +7 Zeilen (Schließungs-Tags) | ✅ Template funktioniert |
| `templates/list/modern.php` | -36 Zeilen (doppelter Code) | ✅ Template funktioniert |
| `assets/js/churchtools-suite-blocks.js` | -100 Zeilen (Platzhalter) | ✅ Nur funktionierende Views |
| `includes/class-churchtools-suite-elementor-widget.php` | -231 Zeilen | ✅ Nur funktionierende Views |

---

## 🎯 Verbesserungen

### User Experience
**Vorher:**
- User sieht 13 View-Typen in Gutenberg/Elementor
- 8 davon zeigen nur "🚧 In Entwicklung" Platzhalter
- Verwirrung & Frustration bei Nutzern

**Nachher:**
- User sieht nur 5 funktionierende View-Typen
- Alle Views rendern echten Content
- Klare Auswahl, kein Rätselraten

---

## ⚠️ Upgrade-Hinweise

**Von v0.10.1.0, v0.10.1.1 oder v0.10.1.2:**
- Kein manueller Eingriff nötig
- **Wichtig:** Bestehende Shortcodes mit Platzhalter-Views (z.B. `[cts_slider]`) werden jetzt ignoriert
- Empfohlen: Nach Update alle Seiten mit Gutenberg/Elementor Blocks prüfen

**Kritikalität:**
- **HOCH** wenn List/Modern Template verwendet wird
- **MITTEL** für bessere Page Builder UX
- **NIEDRIG** wenn nur List/Classic verwendet wird

---

## 🧪 Testing

**Erfolgreich getestet:**
- ✅ List/Classic Template rendert korrekt
- ✅ List/Modern Template rendert korrekt
- ✅ Gutenberg Block zeigt nur 5 View-Typen
- ✅ Elementor Widget zeigt nur 5 View-Typen
- ✅ Keine JavaScript/PHP Fehler

**Test-Umgebung:**
- WordPress 6.4+
- PHP 8.0+
- Gutenberg Editor
- Elementor Page Builder
- Produktiv-Installation (web2945)

---

## 🔗 Links

**GitHub Release:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v0.10.1.3  
**Download:** [churchtools-suite-0.10.1.3.zip](https://github.com/FEGAschaffenburg/churchtools-suite/releases/download/v0.10.1.3/churchtools-suite-0.10.1.3.zip)

---

## 📚 Für Entwickler

**Platzhalter-Templates (noch nicht implementiert):**
```
templates/slider/classic.php       - 🚧 TODO
templates/masonry/classic.php      - 🚧 TODO
templates/agenda/classic.php       - 🚧 TODO
templates/timetable/classic.php    - 🚧 TODO
templates/carousel/classic.php     - 🚧 TODO
templates/countdown/classic.php    - 🚧 TODO
templates/cover/classic.php        - 🚧 TODO
templates/map/classic.php          - 🚧 TODO
```

**Sobald implementiert:**
- Templates mit echtem Code füllen
- View-Typen in Gutenberg/Elementor wieder aktivieren
- Shortcode Manager erweitern

---

**Vorherige Version:** [v0.10.1.2](RELEASE-NOTES-v0.10.1.2.md)  
**Nächste Version:** TBD
