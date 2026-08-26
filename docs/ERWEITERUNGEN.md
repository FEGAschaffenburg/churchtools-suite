# ChurchTools Integration Suite Erweiterungen

Stand: 2026-08-26

## Grundprinzip

Die Erweiterungen sind eigenständige WordPress-Plugins. Das Hauptplugin **ChurchTools Integration Suite** muss immer zuerst installiert und aktiviert werden.

Das Demo-Plugin und das Demo-Theme sind optional und werden nur für die Dokumentations- und Demo-Website benötigt.

## Elementor Integration

**Version:** 1.0.0.3  
**Status:** Verfügbar  
**Repository:** https://github.com/FEGAschaffenburg/churchtools-suite-elementor

### Zweck

Das Addon stellt ein ChurchTools-Events-Widget für Elementor bereit.

### Funktionen

- Listen-, Raster- und Kalenderansichten
- 28+ Kontrollparameter
- Filter- und Anzeigeoptionen
- Live-Vorschau im Elementor-Editor
- Responsive Einstellungen
- Kompatibel mit Elementor Free

### Voraussetzungen

- ChurchTools Integration Suite ab Version 1.3.0.6
- Elementor ab Version 3.0.0
- WordPress ab Version 6.0
- PHP ab Version 8.2

### Installation

1. Hauptplugin installieren und aktivieren.
2. Elementor installieren und aktivieren.
3. Elementor-Addon als ZIP aus dem GitHub-Repository installieren.
4. Plugin aktivieren.
5. Im Elementor-Editor das Widget **ChurchTools Events** einfügen.

### Geplante Verbesserungen

- [ ] Dienst- und Personenfilter
- [ ] Widget für einzelne Events
- [ ] Weitere Kalenderlayouts
- [ ] Erweiterte globale Widget-Voreinstellungen
- [ ] Verbesserte Elementor-4-Kompatibilität

## Posts Sync

**Version:** 0.2.0.3  
**Status:** Verfügbar  
**Repository:** https://github.com/FEGAschaffenburg/churchtools-suite-posts-sync

### Zweck

Das Addon synchronisiert ChurchTools-Posts in WordPress-Beiträge, Seiten oder den Berichtstyp.

### Funktionen

- Auswahl von ChurchTools-Gruppen
- Zieltyp Beiträge, Seiten oder Berichte
- Status Entwurf, veröffentlicht oder privat
- Änderungserkennung
- Manueller und automatischer Sync
- Gutenberg-Block **ChurchTools Berichte**
- Shortcode `[cts_posts]`
- Nutzung auf gehosteten Produktivinstallationen

### Voraussetzungen

- ChurchTools Integration Suite ab Version 1.3.0.6
- WordPress ab Version 5.0
- PHP ab Version 8.2

### Installation und Konfiguration

1. Hauptplugin installieren und aktivieren.
2. Posts-Sync-Addon als ZIP aus dem GitHub-Repository installieren.
3. Addon aktivieren.
4. Unter **ChurchTools Integration → Einstellungen → Berichte** die Synchronisation öffnen.
5. Gruppen, Zieltyp und Veröffentlichungsstatus auswählen.
6. Synchronisation manuell starten oder automatisch ausführen lassen.

### Geplante Verbesserungen

- [ ] Bilder und Medien aus Posts übernehmen
- [ ] Kategorien und Schlagwörter synchronisieren
- [ ] Vorschau vor dem Import
- [ ] Sync-Log je Gruppe
- [ ] Einzelne Posts erneut synchronisieren
- [ ] Importierte Inhalte gezielt zurücksetzen

## Presentations

**Version:** 0.1.3  
**Status:** Kommt bald  
**Repository:** https://github.com/FEGAschaffenburg/churchtools-suite-presentations

### Zweck

Das Addon erstellt Präsentationsseiten für ChurchTools-Termine und vorhandene Suite-Ansichten.

### Geplante Funktionen

- Präsentationsseiten für Bildschirme
- Auswahl eines Leit-Termins
- Folien für nächste und besondere Termine
- Auswahl der verwendeten Views
- Konfigurierbare Slide-Dauer
- Vollbildmodus
- Automatische Aktualisierung
- Anzeige von Dienstnamen und Personen

### Voraussetzungen

- ChurchTools Integration Suite ab Version 1.3.0.6
- WordPress ab Version 6.0
- PHP ab Version 8.0

### Geplanter Ablauf

1. Hauptplugin aktivieren.
2. Presentations-Addon installieren.
3. Leit-Termin auswählen.
4. Folien und Ansichten konfigurieren.
5. Präsentationsseite erstellen.
6. Seite auf dem Zielbildschirm öffnen.

### Vor der Freischaltung

- [ ] Echte Termine testen
- [ ] Dienstnamen und Personen prüfen
- [ ] Vollbildmodus testen
- [ ] Automatische Aktualisierung testen
- [ ] Mobile und große Bildschirme testen
- [ ] Dokumentation finalisieren
- [ ] Download-Link freischalten

## Demo-Plugin

**Version:** 1.1.4.1  
**Status:** Optional  
**Repository:** separat

Das Demo-Plugin ist nicht Bestandteil des Hauptplugins. Es stellt Demo-Benutzer, isolierte Demo-Daten und die Demo-Registrierung bereit.

### Regeln

- Jeder Tester erhält eigene Einstellungen.
- Jeder Tester sieht nur eigene Demo-Kalender und Demo-Events.
- Demo-Events werden nicht in die Produktivtabelle geschrieben.
- Im Frontend werden nur Events des jeweiligen Testers angezeigt.
- Das Demo-Plugin wird nicht in das Hauptplugin-ZIP aufgenommen.

## Update-Regeln

- Jede Erweiterung besitzt eigene Versionsnummern.
- Jede Erweiterung wird über ein eigenes GitHub-Repository veröffentlicht.
- Das Hauptplugin muss vor den Erweiterungen aktualisiert werden.
- Updates erscheinen nur für installierte Erweiterungen.
- Demo-Plugin und Demo-Theme sind von Produktivinstallationen getrennt.

## Weitere mögliche Erweiterungen

### Calendar Export

- [ ] iCal-/ICS-Feeds
- [ ] Kalenderfeed pro ChurchTools-Kalender
- [ ] Google-, Outlook- und Apple-Kalender-Unterstützung

### REST API

- [ ] Events als JSON bereitstellen
- [ ] Kalender und Dienste ausgeben
- [ ] API-Schlüssel und Berechtigungen

### Formular-Integration

- [ ] Event-Anmeldung über WordPress-Formulare
- [ ] Teilnehmer an ChurchTools übertragen
- [ ] Bestätigungs-E-Mails

### Notifications

- [ ] Benachrichtigung bei neuen Terminen
- [ ] Benachrichtigung bei Änderungen
- [ ] Erinnerungen für Dienste

### WooCommerce

- [ ] Events als Produkte
- [ ] Tickets und Anmeldungen
- [ ] Teilnehmerlisten
