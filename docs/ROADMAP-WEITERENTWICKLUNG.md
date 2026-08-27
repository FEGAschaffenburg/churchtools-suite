# Roadmap Weiterentwicklung

Stand: 2026-08-27

Legende: `[x]` erledigt, `[~]` umgesetzt, aber mit offenem Erweiterungs- oder Mehrbenutzertest, `[ ]` offen.

## Ziel

Die ChurchTools Integration Suite soll ChurchTools-Daten zuverlässig übernehmen, pro Benutzer sauber trennen und durch optionale Addons erweiterbar bleiben.

## Feedback-Prioritäten

Die bisher vorliegenden Rückmeldungen priorisieren folgende Erweiterungen:

- WP Bakery Addon
- Divi Addon
- Eigenes CSS und bessere Anpassbarkeit der Ansichten
- Posts Sync vollständig und einfach in Produktivumgebungen konfigurieren
- Design- und View-Optionen verständlicher dokumentieren
- Presentations-Addon fertigstellen
- Danach REST-API, ICS-Export sowie Formular- und Mitglieder-Integrationen prüfen

Der Kalenderfilter unterstützt ab v1.3.0.8 die Schreibweise `calendar_ids="1,2,3"` zusätzlich zu `calendar` und `calendars`.

## 1. ChurchTools-Datenübernahme

- [x] Kalender und Termine vollständig synchronisieren
- [~] Dienste mit Dienstname und zugewiesener Person übernehmen; Zuordnung und Darstellung weiter prüfen
- [x] Gruppen und Gruppenposts synchronisieren
- [x] Veranstaltungsorte und Adressen übernehmen
- [~] Bilder und Anhänge synchronisieren; Bildlöschung ist umgesetzt, Anhänge fehlen noch
- [x] Änderungen und Löschungen erkennen
- [x] Veraltete Plugin-Medien automatisch bereinigen
- [ ] Synchronisationsstatus je Datensatz speichern
- [ ] Letzte erfolgreiche Synchronisation im Backend anzeigen
- [~] Fehlerhafte Datensätze protokollieren und erneut synchronisieren; Logging vorhanden, gezielter Retry fehlt

## 2. Backend

- [ ] Assistent für die Ersteinrichtung mit Fortschrittsanzeige und Schrittvalidierung
- [ ] Backend-Navigation in die Bereiche Dashboard, Daten, Synchronisation, Darstellung, Einstellungen und System strukturieren
- [ ] Einheitliches Tab- und Kartenlayout für Hauptplugin und Addons einführen
- [ ] Einheitlichen Ablauf aus Status, Konfiguration, Aktion, Ergebnis und Hilfe verwenden
- [ ] Zentralen Verbindungs- und Synchronisationsstatus im Dashboard anzeigen
- [ ] Backend-Fehlermeldungen mit Ursache und nächstem Schritt vereinheitlichen
- [ ] Synchronisationsassistent mit klaren Einzelschritten
- [~] Verbindungstest mit verständlichen Fehlermeldungen; Verbindungstest vorhanden, Fehlermeldungen weiter vereinheitlichen
- [x] Kalender, Gruppen und Dienste getrennt auswählen
- [ ] Importvorschau vor dem Speichern
- [x] Manueller Sync pro Datenbereich
- [ ] Import einzelner Datensätze zurücksetzen
- [~] Sync-Log filtern und exportieren; Status und Verlauf vorhanden, Filter/Export fehlen

## 3. Presentations Addon

Status: In Entwicklung / Kommt bald

- [ ] Präsentationsseite mit echtem ChurchTools-Termin testen
- [ ] Folien für nächste und besondere Termine prüfen
- [ ] Dienstnamen und Personen korrekt anzeigen
- [ ] Vollbildmodus für Bildschirme ergänzen
- [ ] Automatische Aktualisierung der Präsentation
- [ ] Page-Builder-Abhängigkeit optional machen
- [ ] Dokumentation und Download nach Abschluss der Tests freischalten

## 4. Weitere Addons

### WPBakery Addon

- [ ] Eigenständiges WPBakery-Element mit den Funktionen des Elementor-Addons
- [ ] Listen-, Grid-, Kalender-, Countdown- und Carousel-Ansichten
- [ ] Kalender-IDs, Tags, Zeitraum, Event-Limit und Darstellungsoptionen
- [ ] WPBakery-Kompatibilität, Dokumentation und eigenes Release testen

### Divi Addon

- [ ] Eigenständiges Divi-Modul mit den Funktionen des Elementor-Addons
- [ ] Listen-, Grid-, Kalender-, Countdown- und Carousel-Ansichten
- [ ] Kalender-IDs, Tags, Zeitraum, Event-Limit und Darstellungsoptionen
- [ ] Divi-Kompatibilität, Dokumentation und eigenes Release testen

### REST-API

- [ ] ChurchTools-Daten als JSON bereitstellen
- [ ] API-Schlüssel und Berechtigungen
- [ ] Zugriff pro Kalender und Datenbereich begrenzen

### Calendar Export

- [ ] iCal-/ICS-Feeds
- [ ] Google-, Outlook- und Apple-Kalender unterstützen
- [ ] Feed pro Kalender erzeugen

### Formular-Integration

- [ ] Event-Anmeldung über WordPress-Formular
- [ ] Teilnehmer an ChurchTools übertragen
- [ ] Bestätigungs-E-Mails versenden

### Members und Gruppen

- [ ] Gruppen und Termine im Frontend anzeigen
- [ ] Geschützte Inhalte für Mitglieder
- [ ] Gruppenposts ausgeben

### WooCommerce

- [ ] Veranstaltungen als Produkte abbilden
- [ ] Tickets und Anmeldungen
- [ ] Teilnehmerlisten

### Notifications

- [ ] Benachrichtigung bei neuen Terminen
- [ ] Benachrichtigung bei Änderungen
- [ ] Erinnerungen für Dienste und Veranstaltungen

## 5. Qualitätssicherung Hauptplugin und Addons

- [x] PHP-Syntaxprüfung für alle Pakete
- [ ] Automatisierte Verhaltenstests für Kalenderfilter, Shortcodes und Addons
- [ ] Testmatrix für WordPress-, PHP- und Page-Builder-Kompatibilität
- [ ] Vollständigen UTF-8-/Mojibake-Check für Dokumentationsseiten automatisieren
- [ ] Release-ZIP automatisiert auf Demo-Dateien und Entwicklungsordner prüfen
- [x] Test für Plugin-Aktivierung und Deaktivierung
- [x] Test für Update-Erkennung
- [x] Test für Frontend-Filterung im Einzelpfad
- [x] Test für Menü- und Unterseitenstruktur der produktiven Plugins
- [x] Test mit echten ChurchTools-Daten
- [x] Release-ZIP ohne Demo-Dateien prüfen

## Priorität

1. ChurchTools-Datenübernahme und Löschlogik
2. Assistent für die Ersteinrichtung
3. Backend-Navigation und durchgängiges Design
4. Presentations Addon fertigstellen
5. REST-API und Calendar Export
6. Formular- und Mitglieder-Addon
7. WooCommerce-Integration
