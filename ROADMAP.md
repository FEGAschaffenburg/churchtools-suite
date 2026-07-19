# ChurchTools Suite – Roadmap

Letzte Aktualisierung: 19. Juli 2026 | Aktueller Stand: v1.2.3.0

---

## Legende

| Symbol | Bedeutung |
|--------|-----------|
| ✅ | Abgeschlossen |
| 🔄 | In Arbeit |
| 🟡 | Geplant (nächste Version) |
| 🔵 | Geplant (mittelfristig) |
| ⚪ | Idee / unter Beobachtung |

---

## v1.2.x – Bugfixes & Stabilität ✅ ABGESCHLOSSEN

| Version | Feature/Fix | Status |
|---------|-------------|--------|
| v1.2.3.0 | Shortcode Views erweitert (11 List-Views, 8 Calendar-Views) | ✅ |
| v1.2.3.0 | Sync Update-Logik: alle Felder werden korrekt übertragen (title, end_datetime, last_modified, …) | ✅ |
| v1.2.3.0 | Incremental Sync (modified_after) funktioniert zuverlässig | ✅ |
| v1.2.2.0 | GitHub Auto-Update-Checker implementiert | ✅ |
| v1.2.1.x | Posts Sync Addon für Produktivumgebungen freigegeben | ✅ |
| v1.2.0.x | Image-Deduplication bei wiederkehrenden Events | ✅ |
| v1.2.0.x | Composite Key (appointment_id\|start_datetime) für fehlerfreie Löschung | ✅ |

---

## v1.3.0 – CSS-Anpassung & Dokumentation 🟡 NÄCHSTE VERSION

> **Umfrage-Impuls:** Meistgenannter Wunsch: eigenes CSS, CSS-Anpassung der Ansichten (2× erwähnt)

### Features

#### 🎨 Custom CSS pro Shortcode/Preset (HOCH)
- Neues Feld `custom_css` in Shortcode-Presets
- CSS wird scoped auf das jeweilige Shortcode-Element (`#cts-instance-{id}`)
- Admin-UI: CodeMirror-Editor für CSS im Preset-Formular
- Keine Plugin-Core-Datei muss bearbeitet werden

#### 📖 Dokumentation verbessern (MITTEL)
- WP Bakery Addon in Demo und Docs besser sichtbar machen
- Quick-Start-Guide für neue Nutzer
- FAQ-Seite auf plugin.feg-aschaffenburg.de erweitern
- Demo-Seite: alle Views mit Live-Vorschau

#### 🧩 WP Bakery Addon sichtbarer machen (NIEDRIG)
- Bereits vorhanden aber nicht bekannt
- Onboarding-Hinweis im Admin nach Aktivierung

---

## v1.4.0 – Posts Sync & Beiträge 🔵 MITTELFRISTIG

> **Umfrage-Impuls:** "Berichte Sync Addon nicht nur in lokaler Umgebung konfigurierbar" + "Vereinfachung der Beiträgefunktion"

### Features

#### 📰 Posts Sync Addon – Vereinfachung (HOCH)
- Konfiguration vollständig über WordPress Admin (ohne lokale Datei-Änderungen)
- Mapping-Interface: ChurchTools-Felder → WordPress Post-Felder
- Fehlertoleranz verbessern (failed posts werden geloggt, nicht still ignoriert)

#### 🔁 Posts Sync – Erweiterte Optionen (MITTEL)
- Beitrags-Kategorien aus ChurchTools-Tags automatisch setzen
- Bilder aus ChurchTools direkt als Featured Image importieren
- Veröffentlichungsstatus steuern (draft / publish / schedule)

---

## v1.5.0 – Design & Themes 🔵 MITTELFRISTIG

> **Umfrage-Impuls:** "Design war nicht passend" (Nutzer wechselte deshalb von WordPress weg)

### Features

#### 🎨 Vorgefertigte Design-Themes (HOCH)
- 3–5 fertige Theme-Presets (Modern, Klassisch, Minimal, Dark, Church)
- 1-Klick-Aktivierung im Admin
- Eigene Farben und Schriften pro Theme einstellbar

#### 📐 Responsive Verbesserungen (MITTEL)
- Mobile-First Überarbeitung der List/Grid-Views
- Carousel-Verhalten auf kleinen Bildschirmen verbessern

---

## v2.0.0 – Architektur & Erweiterbarkeit ⚪ LANGFRISTIG

### Features

#### 🔌 Plugin-API für externe Entwickler (MITTEL)
- Öffentliche PHP-Hooks und Filter dokumentieren
- Eigene Addons können eigene Views/Presets registrieren

#### 🌐 Mehrsprachigkeit (NIEDRIG)
- ChurchTools-Termingruppen sprachabhängig anzeigen
- Frontend-Texte per WordPress-Sprachsystem übersetzbar

#### 📊 Analytics & Statistiken (NIEDRIG)
- Welche Termine werden am häufigsten aufgerufen?
- Sync-Statistiken mit Verlaufsdiagramm im Admin

---

## Backlog / Ideen ⚪

- Filterung nach Tags/Kategorien im Frontend direkt (ohne Reload)
- iCal-Export-Link für einzelne Kalender
- Push-Benachrichtigung bei neuen Terminen (Browser-Notification)
- Karten-Ansicht (OpenStreetMap) für Termine mit Adresse
- Elementor Addon: weitere Widgets (Countdown, Carousel)
- Serientermine besser visualisieren (Wiederholungs-Badge)

---

## Umfrage-Zusammenfassung (Juli 2026)

4 Teilnehmer, davon 2 Webmaster / 2 Pastoren

| Frage | Ergebnis |
|-------|----------|
| Einrichtung | Sehr einfach bis neutral |
| Bedienung | Sehr gut bis befriedigend |
| Dokumentation | Unzufrieden bis sehr zufrieden → **Verbesserungsbedarf** |
| Sync-Zuverlässigkeit | Meist bis sehr zuverlässig |
| Gesamtzufriedenheit | Ø ~6/10 |
| Weiterempfehlung | 3× Ja / 1× Unentschlossen |

**Top-Wünsche aus der Umfrage:**
1. Eigenes CSS / CSS-Anpassung → v1.3.0
2. WP Bakery Addon bekannter machen → v1.3.0
3. Posts Sync in Produktivumgebung → v1.4.0
4. Design-Optionen verbessern → v1.5.0
