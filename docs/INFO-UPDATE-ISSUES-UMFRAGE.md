# Update zu Issues und Umfrage

Stand: 2026-08-26

## Vielen Dank für das Feedback

Vielen Dank für die Rückmeldungen, Fehlermeldungen und Verbesserungsvorschläge zur ChurchTools Integration Suite. Die Hinweise aus der Umfrage und den GitHub-Issues wurden geprüft, umgesetzt und getestet.

## Was wurde aktualisiert?

### Hauptplugin

- Aktualisierung auf Version `1.3.0.7`
- Stabilisierung der Kalender- und Eventdarstellung
- Verbesserung der Darstellung langer Eventtitel
- Dienstnamen werden korrekt angezeigt
- Nicht zugewiesene Personen werden nicht mehr als `TBD` dargestellt
- Tenant-URLs werden beim Speichern normalisiert
- Menüs und Untermenüs wurden bereinigt
- Überflüssige Feedback-Meldung im Backend wurde entfernt
- Demo-Funktionen wurden aus dem Hauptplugin getrennt
- Hauptplugin-ZIP enthält keine Demo-Dateien mehr

### Addons

Die Addons werden als eigenständige Plugins installiert und aktualisiert:

- Elementor Integration `1.0.0.3`
- Posts Sync `0.2.0.3`
- Presentations `0.1.3`

Für jedes Addon gibt es eine eigene Dokumentationsseite mit Voraussetzungen, Installation und Konfiguration.

### Demo-Funktionen

- Die Demo-Seite stellt die Funktionen der ChurchTools Integration Suite anschaulich bereit.
- Jeder Demo-Tester erhält eigene Einstellungen und eigene Demo-Daten.
- Demo-Events werden benutzerbezogen isoliert.
- Die Demo-Registrierung wird nur bei aktivem Demo-Plugin angezeigt.

### Backend und Dokumentation

- Addon-Übersicht aktualisiert
- Eigene Unterseiten für Elementor, Posts Sync und Presentations angelegt
- Header-Navigation lokal an die Dokumentationsseite angepasst
- Presentations wird bis zum Abschluss der Tests als „Kommt bald“ angezeigt
- Roadmaps für Produktentwicklung sowie Dokumentation und Demo getrennt angelegt
- Ein Assistent für die Ersteinrichtung ist als nächste wichtige Erweiterung geplant

## Bearbeitete Issues

Die folgenden GitHub-Issues wurden bearbeitet und nach den durchgeführten Tests geschlossen:

- #3 Kalenderdarstellung bei langen Titeln
- #4 Gruppenposts und Posts-Synchronisation
- #6 ChurchTools Tenant URL
- #8 Berichte-Synchronisation auf Hosting
- #9 Lange Titel hinter der Sidebar
- #10 Medien abgelaufener Events
- #7 Probleme mit Shortcuts war bereits zuvor geschlossen

In den Addon-Repositories gibt es derzeit keine offenen Issues.

## Was wird weiterentwickelt?

Die nächsten Schwerpunkte sind:

- Assistent für die Ersteinrichtung
- Verbesserte Backend-Navigation und einheitliches Design
- Vollständige Tester-Isolation mit mehreren parallelen Testern
- Erweiterte Datenübernahme aus ChurchTools
- Fertigstellung und Freigabe des Presentations-Addons
- Importvorschau und gezieltes Zurücksetzen von Importen
- Erweiterte Sync-Logs und erneute Synchronisation fehlerhafter Datensätze
- Weitere Addons wie ICS-Export, REST-API und Formular-Integration

## Neue Issues melden

Weitere Fehler, Verbesserungsvorschläge und Wünsche sind ausdrücklich willkommen.

Bitte prüfen Sie zunächst, ob bereits ein passendes Issue existiert. Falls nicht, erstellen Sie ein neues Issue im passenden GitHub-Repository:

- Hauptplugin: https://github.com/FEGAschaffenburg/churchtools-suite/issues
- Elementor: https://github.com/FEGAschaffenburg/churchtools-suite-elementor/issues
- Posts Sync: https://github.com/FEGAschaffenburg/churchtools-suite-posts-sync/issues
- Presentations: https://github.com/FEGAschaffenburg/churchtools-suite-presentations/issues

Bitte beschreiben Sie möglichst genau:

- WordPress-, PHP- und Plugin-Version
- verwendetes Theme und aktive Addons
- genaue Schritte zur Reproduktion
- erwartetes und tatsächliches Verhalten
- Fehlermeldungen oder Screenshots
- relevante Logs, ohne Zugangsdaten oder persönliche Daten

## Aktueller Support

Für Fragen zur Installation, Konfiguration oder zur aktuellen Version schreiben Sie bitte an:

**plugin@feg-aschaffenburg.de**

Bitte geben Sie bei Supportanfragen die verwendeten Plugin-Versionen, WordPress-/PHP-Version sowie eine kurze Fehlerbeschreibung an. Zugangsdaten, Passwörter und API-Schlüssel dürfen nicht per E-Mail versendet werden.

Vielen Dank für Ihre Unterstützung und die hilfreichen Rückmeldungen zur weiteren Verbesserung der ChurchTools Integration Suite.
