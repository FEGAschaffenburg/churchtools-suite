# ChurchTools Suite - Roadmap

Letzte Aktualisierung: 28. August 2026 | Aktueller Stand: v1.3.0.11

Diese Datei ist die einzige Zukunftsplanung des Hauptplugins. Der abgeschlossene Stand steht im README und in der Git-Historie.

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

## v1.3.x – Stabilisierung des Hauptplugins 🟡 NÄCHSTE VERSION

> **Umfrage-Impuls:** Meistgenannter Wunsch: eigenes CSS, CSS-Anpassung der Ansichten (2× erwähnt)

### Features

#### 🎨 Custom CSS pro Shortcode/Preset (HOCH) ✅ UMGESETZT
- `custom_css` wird in Presets und Block-Attributen gespeichert.
- CSS wird pro Ausgabe über eine stabile Instanz-ID begrenzt.
- CSS kann über den Shortcode-Manager und die Block-/Shortcode-API verwendet werden.

#### 🔧 Custom CSS - optionale Nacharbeiten (NIEDRIG)
- [ ] CSS-Editor durch CodeMirror mit Syntaxprüfung ersetzen.
- [ ] Vorschau für Custom CSS in Liste, Grid und Kalender ergänzen.
- [ ] CSS-Validierung und verständliche Fehlermeldungen verbessern.
- [ ] Sicherheitsprüfung für problematische CSS-Regeln dokumentieren.

#### 📖 Dokumentation verbessern (MITTEL)
1. README und ROADMAP je Repository aktuell halten.
2. Schnellstart für Verbindung, Kalenderauswahl und ersten Shortcode schreiben.
3. `calendar_ids="1,2,3"` mit gültigem und ungültigem Beispiel dokumentieren.
4. FAQ zu Cache, WP-Cron, Berechtigungen und Addon-Abhängigkeiten ergänzen.
5. Jede öffentliche View mit Beispiel, erwarteter Ausgabe und benötigten Assets prüfen.
6. Ergebnis: Neue Nutzer können die erste Ausgabe ohne Rückfrage erstellen.

#### 🧩 WPBakery- und Divi-Addons (NACH v1.3.x)
Die Builder-Addons starten erst, wenn die Kern-API, Filterung und Release-Prozesse stabil sind.

#### 🧩 WP Bakery Addon (HOCH)
1. Eigenständiges Repository und Plugin-Grundgerüst anlegen.
2. Abhängigkeit zu ChurchTools Suite und WPBakery prüfen.
3. WPBakery-Element `ChurchTools Events` registrieren.
4. Gemeinsame Shortcode-API für View, `calendar_ids`, Tags, Zeitraum und Limit verwenden.
5. Controls für Liste, Grid, Kalender, Countdown und Carousel ergänzen.
6. Event-Aktionen und Anzeigeoptionen aus dem Elementor-Addon abbilden.
7. Editor-, Frontend- und fehlende-Abhängigkeit-Zustände testen.
8. README, ROADMAP, ZIP-Build und eigenes GitHub-Release erstellen.
9. Ergebnis: WPBakery bietet dieselben Funktionen wie Elementor.

#### 🧱 Divi Addon (HOCH)
1. Eigenständiges Repository und Plugin-Grundgerüst anlegen.
2. Abhängigkeit zu ChurchTools Suite und Divi prüfen.
3. Divi-Modul `ChurchTools Events` registrieren.
4. Gemeinsame Shortcode-API für View, `calendar_ids`, Tags, Zeitraum und Limit verwenden.
5. Controls für Liste, Grid, Kalender, Countdown und Carousel ergänzen.
6. Event-Aktionen und Anzeigeoptionen aus dem Elementor-Addon abbilden.
7. Visual Builder, Frontend und fehlende-Abhängigkeit-Zustände testen.
8. README, ROADMAP, ZIP-Build und eigenes GitHub-Release erstellen.
9. Ergebnis: Divi bietet dieselben Funktionen wie Elementor.

---

## v1.4.0 – Hauptplugin: Sync-Status und Betriebsstabilität 🔵 MITTELFRISTIG

> **Umfrage-Impuls:** "Berichte Sync Addon nicht nur in lokaler Umgebung konfigurierbar" + "Vereinfachung der Beiträgefunktion"

### Features

#### 📰 Posts Sync Addon – Vereinfachung (HOCH)
1. Alle produktiven Einstellungen im WordPress-Admin erfassen.
2. Mapping von ChurchTools-Feldern zu Titel, Inhalt, Autor, Status und Datum definieren.
3. Vorschau mit einem echten Bericht vor dem Speichern anzeigen.
4. Testlauf ohne Schreiben anbieten.
5. Fehler pro Bericht mit ID, Ursache und Zeit protokollieren.
6. Ergebnis: Posts Sync benötigt keine lokale Dateiänderung mehr.

#### 🔁 Posts Sync – Erweiterte Optionen (MITTEL)
1. ChurchTools-Tags normalisieren und Kategorien zuordnen.
2. Fehlende Kategorien optional automatisch anlegen.
3. Bilder laden, Dateityp prüfen und als Featured Image speichern.
4. Wiederholte Bildimporte über Hash oder Quell-ID vermeiden.
5. Status `draft`, `publish` und `future` konfigurierbar machen.
6. Jeden Ablauf mit Testbericht und Rücksetzoption prüfen.

---

## v1.5.0 – Design & Themes 🔵 MITTELFRISTIG

> **Umfrage-Impuls:** "Design war nicht passend" (Nutzer wechselte deshalb von WordPress weg)

### Features

#### 🎨 Vorgefertigte Design-Themes (HOCH)
1. Designvariablen für Farben, Typografie, Abstände und Rahmen definieren.
2. Presets Modern, Klassisch, Minimal, Dark und Church erstellen.
3. Preset-Auswahl mit Vorschau im Admin ergänzen.
4. Eigene Farben und Schriften validiert speichern.
5. Ausgabe in Liste, Grid, Kalender und Einzelansicht prüfen.

#### 📐 Responsive Verbesserungen (MITTEL)
1. View-Breakpoints und Mindestbreiten dokumentieren.
2. Lange Titel, Bilder, Buttons und Dienste auf 320px testen.
3. Grid-Spalten auf Tablet und Mobilgerät prüfen.
4. Carousel-Touch, Überlauf und Tastaturbedienung testen.
5. Ergebnis: Keine Überlappung oder horizontale Scrollpflicht in den Standard-Views.

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

## Arbeitsablauf pro Aufgabe

1. Issue mit Ziel, betroffenen Repositories und Abnahmekriterium anlegen.
2. Änderung zuerst lokal implementieren.
3. PHP-Syntax, Abhängigkeiten und betroffene Frontend-Ausgabe prüfen.
4. Relevante Tests mit echten ChurchTools-Daten ausführen.
5. README und ROADMAP aktualisieren.
6. Plugin-ZIP mit dem zentralen Build-Skript erstellen und Inhalt prüfen.
7. Commit und Push durchführen.
8. GitHub-Release nur mit geprüftem ZIP veröffentlichen.
9. Live-Deployment durchführen und HTTP-, Log- und Funktionscheck ausführen.
10. Issue mit Ergebnis, Version und bekannten Einschränkungen schließen.

## Reihenfolge der Entwicklung

1. Hauptplugin stabilisieren und lokale/live Release-Stände sauber trennen.
2. Sync-Status, Fehlerbehandlung und Retry-Funktionen im Hauptplugin verbessern.
3. Shortcode- und Kalenderfilter als stabile öffentliche API festlegen.
4. Erst danach WPBakery-Addon entwickeln.
5. Danach Divi-Addon entwickeln.

## Nächste drei umsetzbare Aufgaben

- [ ] Synchronisationsstatus je Kalender, Termin, Dienst und Bericht erfassen
- [ ] Fehlerhafte Datensätze einzeln erneut synchronisieren können
- [ ] Gemeinsame ausführbare Tests für `calendar_ids`, automatische Synchronisation und Addon-Abhängigkeiten ablegen

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
1. Eigenes CSS / CSS-Anpassung → umgesetzt in v1.3.0.x
2. WP Bakery Addon konzipieren und umsetzen → v1.3.x
3. Posts Sync in Produktivumgebung → v1.4.0
4. Design-Optionen verbessern → v1.5.0
