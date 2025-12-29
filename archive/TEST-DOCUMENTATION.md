# ChurchTools Suite v0.5.9.21 - Demo & Test Dokumentation

## 📦 Paket-Inhalt

**ZIP-Datei**: `C:\privat\churchtools-suite-0.5.9.21.zip`  
**Größe**: 0.15 MB  
**Einträge**: 67 Dateien

---

## 🆕 Neu in Version 0.5.9.21

### ✅ Checkbox-Kalenderauswahl in allen Gutenberg-Blöcken
- **Calendar Block** (-neu): Checkboxen statt Text-Input
- **List Block** (-neu): Checkboxen statt Text-Input
- **Grid Block** (-neu): Checkboxen statt Text-Input

### ✅ Demo-Dokumentation erstellt
1. **SHORTCODE-DEMO.md** - Markdown-Version für Entwickler
2. **shortcode-demo.html** - HTML-Version für WordPress-Seiten
3. **SHORTCODE-REFERENCE.md** - Quick Reference aller Shortcodes

---

## 📚 Demo-Dateien im Detail

### 1. SHORTCODE-DEMO.md
**Zweck**: Entwickler-Dokumentation  
**Format**: Markdown  
**Inhalt**:
- Alle 13 Shortcode-Typen
- 70+ View-Varianten
- Test-Checklisten
- Notizen-Bereiche für Feedback

**Verwendung**: Referenz beim Entwickeln

---

### 2. shortcode-demo.html
**Zweck**: WordPress Demo-Seite  
**Format**: HTML mit Inline-Styling  
**Inhalt**:
- Styled Sections für jeden Shortcode-Typ
- Checkboxen für Test-Checklisten
- Notizen-Bereiche mit Formular-Feldern

**Verwendung**:
1. WordPress → Neue Seite erstellen
2. Im Editor auf "Code-Editor" (Text) umschalten
3. Gesamten HTML-Inhalt einfügen
4. "Vorschau" klicken → Alle Shortcodes werden gerendert
5. Durchscrollen und testen

---

### 3. SHORTCODE-REFERENCE.md
**Zweck**: Quick Reference  
**Format**: Kompakte Liste  
**Inhalt**:
- Alle Shortcodes mit Standard-Parametern
- Gemeinsame Parameter erklärt
- Troubleshooting-Guide
- Statistik (13 Typen, 70+ Views)

**Verwendung**: Schnelles Nachschlagen beim Einbau

---

## 🎯 Test-Workflow

### Schritt 1: Plugin installieren
```bash
# In WordPress:
1. Plugins → Installieren → ZIP hochladen
2. churchtools-suite-0.5.9.21.zip auswählen
3. Installieren & Aktivieren
```

### Schritt 2: Demo-Seite erstellen
```bash
# WordPress Admin:
1. Seiten → Neu hinzufügen
2. Titel: "ChurchTools Suite - Shortcode Demo"
3. Code-Editor öffnen (3 Punkte → Code-Editor)
4. Inhalt von shortcode-demo.html einfügen
5. "Vorschau" klicken
```

### Schritt 3: Systematisch testen
1. **Calendar Views** (8 Varianten) durchgehen
2. **List Views** (10 Varianten) prüfen
3. **Grid Views** (14 Varianten) testen
4. **Slider/Carousel** (9 Varianten) mit Autoplay
5. **Countdown/Widget** (6 Varianten) Funktionalität
6. **Cover/Timetable** (8 Varianten) Layout
7. **Search/Map** (5 Varianten) Interaktion

### Schritt 4: Checklisten abarbeiten
- [ ] **Funktionalität**: Alle rendern ohne Fehler
- [ ] **Layout**: Responsive, keine Breaks
- [ ] **Performance**: Ladezeiten OK
- [ ] **Interaktion**: Buttons/Slider funktionieren
- [ ] **Edge Cases**: Empty States, lange Titel

### Schritt 5: Probleme dokumentieren
Im Notizen-Bereich der Demo-Seite:
- Problem beschreiben
- Betroffene View notieren
- Priorität vergeben (Hoch/Mittel/Niedrig)
- Screenshot machen

---

## 🔍 Was testen?

### Funktionalität
- ✅ Alle Shortcodes rendern
- ✅ Events werden angezeigt
- ✅ Kalenderfilter wirken
- ✅ Services erscheinen (bei show_services="true")
- ✅ Datumsformatierung stimmt
- ✅ Links funktionieren

### Layout & Design
- ✅ Responsive auf Mobile/Tablet/Desktop
- ✅ Keine Layout-Breaks
- ✅ Farben konsistent
- ✅ Schriften lesbar
- ✅ Abstände harmonisch
- ✅ Icons sichtbar

### Performance
- ✅ Ladezeiten unter 2 Sekunden
- ✅ Keine JavaScript-Fehler in Console
- ✅ Keine PHP-Errors in Debug-Log
- ✅ CSS wird geladen
- ✅ Keine Render-Blocking

### Interaktion
- ✅ Slider autoplay läuft
- ✅ Carousel Navigation (Pfeile)
- ✅ Countdown zählt live runter
- ✅ Search funktioniert
- ✅ Map-Marker klickbar
- ✅ Toggle öffnet/schließt

### Edge Cases
- ✅ Keine Events → Empty State angezeigt
- ✅ Sehr lange Titel → Textumbruch
- ✅ Keine Services → Kein Layout-Break
- ✅ Ungültige Calendar-ID → Fehlertext
- ✅ Viele Events → Pagination

---

## 📊 Vollständige Shortcode-Liste

### 1. Calendar (8 Views)
```
monthly-modern, monthly-clean, monthly-classic
weekly-fluent, weekly-liquid
yearly
daily, daily-liquid
```

### 2. List (10 Views)
```
classic, standard, modern, minimal, toggle
with-map, fluent
large-liquid, medium-liquid, small-liquid
```

### 3. Grid (14 Views)
```
simple, modern, minimal, ocean, classic
colorful, novel, with-map, tile
large-liquid, medium-liquid, small-liquid
```

### 4. Slider (5 Views)
```
type-1, type-2, type-3, type-4, type-5
```

### 5. Countdown (3 Views)
```
type-1, type-2, type-3
```

### 6. Cover (5 Views)
```
classic, modern, clean, fluent, liquid
```

### 7. Timetable (3 Views)
```
modern, clean, timeline
```

### 8. Carousel (4 Views)
```
type-1, type-2, type-3, type-4
```

### 9. Widget (3 Views)
```
upcoming-events, calendar-widget, countdown-widget
```

### 10. Search (2 Views)
```
bar, advanced
```

### 11. Map (3 Views)
```
standard, advanced, liquid
```

### 12. Modal (1 View)
```
default
```

### 13. Single (2 Views)
```
detail, full
```

**Gesamt**: 13 Typen | 63 Varianten

---

## 🎨 Parameter-Optionen

### Standard (alle Views)
```
calendar="1,2,3"    # Kalender-IDs (kommagetrennt)
limit="10"          # Max. Anzahl Events
from="today"        # Start-Datum
to="+30 days"       # End-Datum
```

### List-spezifisch
```
show_services="true"  # Dienste anzeigen
```

### Grid-spezifisch
```
columns="3"           # Anzahl Spalten (2-4)
```

### Slider/Carousel-spezifisch
```
autoplay="true"       # Auto-Abspielen
interval="5000"       # Intervall (ms)
```

### Single-spezifisch
```
event_id="123"        # Event-ID aus ChurchTools
```

---

## 🐛 Bekannte Einschränkungen

### Templates nicht implementiert (zeigen Platzhalter)
- Viele Views haben nur Basis-Template
- "Template not found" ist normal bei nicht implementierten Views
- Nächster Schritt: Templates schrittweise implementieren

### Gutenberg-Blöcke
- ✅ Calendar Block: Funktioniert mit Checkboxen
- ✅ List Block: Funktioniert mit Checkboxen
- ✅ Grid Block: Funktioniert mit Checkboxen
- ⚠️ Weitere Blocks (Slider, Countdown, etc.) noch nicht als Blocks verfügbar

### Services-Import
- Services müssen im Admin → Services-Tab ausgewählt werden
- show_services="true" zeigt nur ausgewählte Services
- Bei Problemen: Services neu synchronisieren

---

## 🚀 Optimierungs-Roadmap

### Sofort (Prio 1)
1. Alle Templates implementieren (momentan Platzhalter)
2. Empty States verbessern
3. Error Messages benutzerfreundlicher
4. Console.logs entfernen (Debug-Code)

### Kurzfristig (Prio 2)
1. Weitere Gutenberg-Blocks (Slider, Countdown, etc.)
2. Pagination für große Event-Listen
3. Filter-UI im Frontend
4. Bilderunterstützung

### Mittelfristig (Prio 3)
1. Cache-Layer für Performance
2. Custom CSS pro View
3. Template-Überschreibung dokumentieren
4. Shortcode-Generator im Admin

### Langfristig (Prio 4)
1. Multi-Language Support
2. Export-Funktionen (iCal, PDF)
3. Statistiken & Analytics
4. A/B-Testing für Views

---

## 📝 Feedback-Template

```markdown
### Problem gefunden

**View**: [z.B. cts_list view="modern"]
**Beschreibung**: [Was ist das Problem?]
**Screenshot**: [Optional]
**Priorität**: Hoch / Mittel / Niedrig
**Reproduzierbar**: Ja / Nein
**Browser**: [Chrome, Firefox, Safari, etc.]
**Gerät**: Desktop / Mobile / Tablet

### Verbesserungsvorschlag

**Idee**: [Was könnte besser sein?]
**Betroffene Views**: [Liste]
**Begründung**: [Warum wichtig?]
**Aufwand**: Hoch / Mittel / Niedrig
**Impact**: Hoch / Mittel / Niedrig
```

---

## 🎉 Nächste Schritte

1. ✅ **Plugin installieren** - churchtools-suite-0.5.9.21.zip
2. ✅ **Demo-Seite erstellen** - shortcode-demo.html einfügen
3. ✅ **Systematisch testen** - Alle 63 Views durchgehen
4. ✅ **Feedback dokumentieren** - Notizen-Bereich nutzen
5. ⏳ **Probleme priorisieren** - Was muss zuerst gefixt werden?
6. ⏳ **Optimierungen planen** - Roadmap aktualisieren
7. ⏳ **Templates implementieren** - Fehlende Views nachbauen
8. ⏳ **Produktionsversion** - v1.0.0 vorbereiten

---

**Stand**: 16. Dezember 2025, 19:15 Uhr  
**Version**: 0.5.9.21  
**Status**: Bereit zum Testen ✅
