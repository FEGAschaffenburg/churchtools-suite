# Template Manager - Zusammenfassung & Antwort

**Datum:** 8. Januar 2026  
**Frage:** "sollte man die templates in der ordnerstrucktur trennen. ggf kommen noch template für Themen hinzu - pro calender, pro tag, pro service .... - sollte es sowas wie ein teplatemager geben ? wenn ja bitte auf roadmap"

---

## ✅ Antwort: Ja, definitiv!

### 📁 Aktuelle Struktur (v0.9.9.43)

```
templates/
├── calendar/      # Kalender-Ansichten (monthly, weekly)
├── grid/          # Event-Listen als Grid/Cards
├── list/          # Event-Listen als Liste
├── modal/         # Event-Details als Modal
└── single/        # Event-Details als Vollseite
```

**Problem:**
- Alles Event-zentriert
- Keine Kalender-Komponenten (z.B. Kalender-Card für Sidebar)
- Keine Tag-Komponenten (z.B. Tag-Cloud, Tag-Badge)
- Keine Service-Komponenten (z.B. Service-Liste mit Personen)
- Keine Verwaltung (kein Manager)

---

## 🎯 Vorgeschlagene Lösung: Template Manager (v1.4.0)

### Neue Ordnerstruktur

```
templates/
├── event/                    # Event-bezogene Templates
│   ├── list/                 # Listen-Ansichten
│   ├── grid/                 # Grid-Ansichten
│   ├── single/               # Vollseiten
│   ├── modal/                # Modal-Overlays
│   └── calendar/             # Kalender-Ansichten
│
├── calendar/                 # Kalender-Komponenten ✨ NEU
│   ├── card.php              # Kalender als Card
│   ├── widget.php            # Sidebar-Widget
│   ├── badge.php             # Kleiner Badge
│   └── list-item.php         # Listeneintrag
│
├── tag/                      # Tag-Komponenten ✨ NEU
│   ├── badge.php             # Tag-Badge (inline)
│   ├── card.php              # Tag-Card (erweitert)
│   └── cloud.php             # Tag-Cloud
│
├── service/                  # Service-Komponenten ✨ NEU
│   ├── list.php              # Service-Liste
│   ├── card.php              # Service-Card
│   ├── badge.php             # Service-Badge
│   └── person.php            # Person mit Service
│
├── partial/                  # Wiederverwendbare Teile ✨ NEU
│   ├── date-badge.php        # Datum-Anzeige
│   ├── time-range.php        # Zeit-Anzeige
│   ├── location-card.php     # Standort-Karte
│   ├── image-hero.php        # Hero-Bild Section
│   └── meta-card.php         # Meta-Informations-Card
│
├── system/                   # System-Templates (nicht editierbar) ✨ NEU
│   └── [Mirror der Basis-Templates]
│
└── custom/                   # User-Templates (editierbar/uploadbar) ✨ NEU
    └── [User-hochgeladene Templates]
```

---

## 🔧 Template Manager Features

### Admin-Bereich: `ChurchTools Suite > Templates`

1. **Template-Bibliothek**
   - Liste aller Templates
   - Gruppierung nach Typ (Event, Calendar, Tag, Service)
   - Status-Anzeige: Aktiv / Inaktiv / System / Custom

2. **Template-Verwaltung**
   - Ein/Aus-Schalter pro Template
   - Template aktivieren/deaktivieren
   - Template-Einstellungen (pro Template)

3. **Template-Upload**
   - ZIP-Upload für Custom Templates
   - Validierung & Sicherheits-Check
   - Template-Galerie

4. **Template-Previews**
   - Screenshot jedes Templates
   - Live-Preview mit Test-Daten
   - Responsive-Vorschau (Desktop/Tablet/Mobile)

5. **Template-Einstellungen**
   - Pro Template konfigurierbar
   - Farben, Schriften, Layout
   - Speicherung in WordPress-Optionen

---

## 🎨 Neue Shortcodes (v1.5.0)

### Kalender-Komponenten

```php
// Kalender als Card in Sidebar
[cts_calendar_card id="main" template="widget"]

// Kalender als Badge
[cts_calendar_badge id="main"]

// Kalender-Liste (alle Kalender)
[cts_calendar_list template="list-item"]
```

### Tag-Komponenten

```php
// Tag-Cloud (alle Tags)
[cts_tag_cloud template="cloud" count="20"]

// Einzelner Tag als Badge
[cts_tag_badge id="123"]

// Tag-Card mit Events
[cts_tag_card id="123" template="card"]
```

### Service-Komponenten

```php
// Service-Liste eines Events
[cts_service_list event_id="123" template="person"]

// Alle Services eines Kalenders
[cts_service_list calendar_id="main" template="list"]

// Service als Card
[cts_service_card id="456" template="card"]
```

---

## 📋 Implementation-Phasen

### Phase 1: Struktur-Refactoring (v1.4.0)
- [x] Neue Ordnerstruktur definiert
- [ ] Migration-Script schreiben
- [ ] Bestehende Templates migrieren
- [ ] Kompatibilitäts-Layer für alte Pfade

### Phase 2: Template-Manager Backend (v1.4.1)
- [ ] DB-Tabelle `wp_cts_templates`
- [ ] Template-Registration-API
- [ ] Template-Scanner
- [ ] Template-Validator

### Phase 3: Template-Manager UI (v1.4.2)
- [ ] Admin-Seite erstellen
- [ ] Template-Liste mit Gruppierung
- [ ] Aktivieren/Deaktivieren-Toggle
- [ ] Template-Einstellungen-Seite

### Phase 4: Template-Upload (v1.4.3)
- [ ] ZIP-Upload-Funktion
- [ ] Sicherheits-Validierung
- [ ] Custom-Templates-Verwaltung

### Phase 5: Template-Previews (v1.4.4)
- [ ] Screenshot-Generator
- [ ] Live-Preview
- [ ] Responsive-Vorschau

### Phase 6: Komponenten-Templates (v1.5.0)
- [ ] Calendar-Komponenten
- [ ] Tag-Komponenten
- [ ] Service-Komponenten
- [ ] Partial-Templates

**Gesamt-Aufwand:** 15-20 Tage (6 Phasen)

---

## 🚀 Vorteile

### Für Administratoren:
- ✅ Zentrale Template-Verwaltung
- ✅ Ein/Aus-Schalter pro Template
- ✅ Preview vor Aktivierung
- ✅ Template-Einstellungen ohne Code
- ✅ Custom Templates hochladen

### Für Entwickler:
- ✅ Klare Template-Hierarchie
- ✅ Wiederverwendbare Komponenten (DRY-Prinzip)
- ✅ Template-API für programmatische Nutzung
- ✅ Versionierung und Abhängigkeiten
- ✅ Einfaches Erstellen neuer Templates

### Für User:
- ✅ Mehr Template-Optionen
- ✅ Konsistente Darstellung
- ✅ Bessere Anpassbarkeit
- ✅ Schnellere Ladezeiten

---

## 📊 Use Cases

### 1. Kalender-Widget in Sidebar

**User:** Gemeinde möchte Kalender-Übersicht in Sidebar

**Lösung:**
```php
[cts_calendar_card id="main" template="widget"]
```

**Template:** `templates/calendar/widget.php`

---

### 2. Tag-Cloud auf Startseite

**User:** Alle Event-Tags als Cloud anzeigen

**Lösung:**
```php
[cts_tag_cloud template="cloud" count="30"]
```

**Template:** `templates/tag/cloud.php`

---

### 3. Service-Liste mit Personen

**User:** Zeige alle Dienste eines Gottesdienstes mit verantwortlichen Personen

**Lösung:**
```php
[cts_service_list event_id="123" template="person"]
```

**Template:** `templates/service/person.php`

---

### 4. Custom Event-Grid hochladen

**User:** Designer hat Custom Grid-Template erstellt

**Schritte:**
1. ZIP erstellen mit `template.php`, `style.css`, `preview.jpg`, `template.json`
2. Hochladen über `Templates > Hochladen`
3. Aktivieren in Template-Manager
4. Verwenden: `[cts_events template="my-custom-grid"]`

---

## 🗂️ Dokumentation

### Neue Dateien erstellt:

1. **docs/TEMPLATE-STRUCTURE-PROPOSAL.md** (450+ Zeilen)
   - Vollständige Struktur-Beschreibung
   - Template-Metadaten-Format
   - Datenbank-Schema
   - API-Referenz
   - Migration-Pfad
   - Sicherheits-Konzept
   - Performance-Optimierung
   - Future Enhancements

2. **ROADMAP.md** (aktualisiert)
   - v1.4.0: Template Manager (6 Phasen)
   - v1.4.5: Advanced Style Customizer
   - v1.5.0: Komponenten-Templates
   - v2.0+: Vision Features (Marketplace, Visual Editor, AI)

---

## 🔮 Vision Features (v2.0+)

### Template Marketplace (v2.0)
- Templates kaufen/verkaufen
- Rating & Reviews
- Automatic Updates

### Visual Template Editor (v2.1)
- Drag & Drop Editor
- Live-Preview beim Editieren
- Component-Library

### Template Versioning (v2.2)
- Git-ähnliche Versionskontrolle
- Rollback-Funktion
- Change History

### AI-Powered Features (v2.3)
- AI-Template-Generator
- Smart Layout-Suggestions
- Auto-Optimization

---

## ✅ Roadmap-Status

**✅ Hinzugefügt zur Roadmap:**
- v1.4.0: Template Manager & Structure Refactoring
- 6 Implementierungs-Phasen definiert
- Geschätzter Aufwand: 15-20 Tage
- Priorität: Mittel
- Target: Post-v1.0 (Stable Release)

**📄 Dokumentation:**
- Template Structure Proposal erstellt
- Use Cases beschrieben
- Migration-Pfad definiert
- API-Referenz dokumentiert

---

## 🎯 Nächste Schritte

1. **Community Feedback** einholen:
   - Fehlen wichtige Template-Typen?
   - Ist die Struktur verständlich?
   - Welche Features sind am wichtigsten?

2. **Prototyp** erstellen (Optional):
   - Template-Manager UI-Mock
   - Beispiel-Templates für neue Typen
   - Demo der Komponenten-Shortcodes

3. **Migration testen**:
   - Script für v0.9.x → v1.4.0
   - Kompatibilitäts-Tests
   - Performance-Messungen

4. **Prioritäten festlegen**:
   - Welche Phase zuerst?
   - Quick-Wins identifizieren
   - MVP definieren

---

## 📞 Zusammenfassung

**Frage beantwortet:**
- ✅ **Ordnerstruktur trennen?** → Ja! Neue hierarchische Struktur vorgeschlagen
- ✅ **Templates für Kalender/Tags/Services?** → Ja! Als Komponenten-Templates in v1.5.0
- ✅ **Template-Manager?** → Ja! Als v1.4.0-v1.4.4 auf Roadmap gesetzt

**Dokumente erstellt:**
- `docs/TEMPLATE-STRUCTURE-PROPOSAL.md` (Vollständiges Konzept)
- `ROADMAP.md` (Aktualisiert mit v1.4.0-v1.5.0)

**Status:** 📋 Proposal fertig, bereit für Feedback & Implementation

**Target Version:** v1.4.0 (Post-v1.0 Stable Release)
