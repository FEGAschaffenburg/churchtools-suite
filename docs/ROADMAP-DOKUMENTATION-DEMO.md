# Roadmap Dokumentation und Demo

Stand: 2026-08-26

Legende: `[x]` erledigt, `[~]` umgesetzt, aber mit offenem Erweiterungs- oder Mehrbenutzertest, `[ ]` offen.

## 1. Tester- und Demo-Isolation

- [x] Jeder Tester erhält eigene ChurchTools-Einstellungen
- [x] Jeder Tester erhält eigene Kalender, Events und Dienste
- [~] Fremde Events im Frontend verhindern; Repository-Filter und Frontend-Datenpfad sind umgesetzt, Mehrbenutzertest steht noch aus
- [x] Demo-Daten strikt von Produktivdaten trennen
- [ ] Mehrere parallele Tester testen
- [x] Demo-Plugin und Demo-Theme optional halten
- [x] Aktivierung und Deaktivierung des Demo-Plugins testen

## 2. Dokumentation

- [ ] Dokumentationsstruktur vereinheitlichen: Start, Installation, Konfiguration, Fehlerbehebung
- [ ] Hauptplugin und Addons klar voneinander abgrenzen
- [ ] Eigene Dokumentationsseite für jedes verfügbare Addon pflegen
- [ ] Voraussetzungen und kompatible Versionen zentral dokumentieren
- [ ] Installationsanleitungen für Hauptplugin und Addons aktualisieren
- [ ] Einrichtungsassistent mit einer passenden Dokumentationsseite erklären
- [ ] Kurze Praxisbeispiele für Shortcodes, Gutenberg und Elementor ergänzen
- [ ] Posts-Sync-Konfiguration mit Gruppen, Zieltypen und Status dokumentieren
- [ ] Presentations erst nach vollständigem Test als verfügbar dokumentieren
- [ ] Demo-Plugin und Demo-Theme deutlich als optional kennzeichnen
- [ ] Troubleshooting für Verbindung, Synchronisation, Medien und Berechtigungen ergänzen
- [ ] Changelog bei jedem Release aktualisieren
- [ ] Versionsnummern und Download-Links vor jedem Release prüfen
- [ ] Dokumentationsseiten lokal und live auf funktionierende Links prüfen

## 3. Demo-Website

- [ ] Header-Navigation lokal und live synchron halten
- [ ] Addon-Übersicht mit aktuellen Versionen pflegen
- [ ] Eigene Unterseite pro Addon pflegen
- [ ] Presentations als „Kommt bald“ markieren, solange der Test nicht abgeschlossen ist
- [ ] Demo-Registrierung nur bei aktivem Demo-Plugin anzeigen
- [ ] Demo-CSS und JavaScript nur aus dem Demo-Plugin laden
- [ ] Mehrere Demo-Tester mit getrennten Einstellungen testen
- [ ] Mehrere Demo-Tester mit getrennten Events testen
- [ ] Nur eigene Demo-Events im Frontend anzeigen

## 4. Qualitätssicherung Demo und Dokumentation

- [x] Menü- und Unterseitenstruktur lokal geprüft
- [x] Demo-Plugin-Aktivierung und Deaktivierung geprüft
- [x] Demo-Theme getrennt geprüft
- [ ] Test mit mehreren parallelen Demo-Testern
- [ ] Test der Demo-Registrierung mit CSS und JavaScript
- [ ] Test der Demo-Event-Isolation im Frontend
- [ ] Alle Dokumentationslinks lokal und live prüfen
- [ ] Demo- und Dokumentationsseiten nach jedem Release prüfen

## Priorität

1. Mehrere parallele Demo-Tester
2. Vollständige Event-Isolation im Frontend
3. Dokumentationsstruktur vereinheitlichen
4. Addon-Dokumentation aktuell halten
5. Demo-Seiten und Header-Navigation synchron halten
6. Presentations nach Abschluss der Tests freischalten
