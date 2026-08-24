# Test-Checkliste Release 1.2.3.7

Stand: 2026-08-21

## 1. Automatische Pruefungen

- [x] PHP-Syntax aller 122 PHP-Dateien mit Local PHP 8.5.1 geprueft
- [x] Keine PHP-Syntaxfehler
- [x] `git diff --check` erfolgreich
- [x] Hauptplugin-Version ist `1.2.3.7`
- [x] Posts-Sync-Version ist `0.1.9`
- [x] Elementor-Version ist `0.6.29`
- [x] Gesamt-ZIP enthaelt Hauptplugin und beide Add-ons
- [x] ZIP enthaelt keine `.git`- oder `.vscode`-Metadaten

## 2. Local-WordPress-Basis

- [x] Local-Site `http://feg.local` startet ohne 502-Fehler
- [x ] WordPress-Admin ist erreichbar
- [ x] Hauptplugin laesst sich aktivieren
- [ x] Posts-Sync-Addon laesst sich aktivieren
- [ x] Elementor-Addon laesst sich aktivieren, falls Elementor installiert ist
- [x ] Keine PHP-Warnings oder Fatal Errors im WordPress- und PHP-Log

## 3. Issue #10 Medien abgelaufener Events

- [x ] Option „Alte Termine löschen“ ist standardmäßig deaktiviert
- [x ] Bei deaktivierter Option bleiben alte Termine und Bilder erhalten
- [ x] Löschalter konfigurieren, z. B. 30 Tage
- [x ] Eingabe „Löschalter“ wird beim Aktivieren ohne Seiten-Reload freigeschaltet
- [ ] Eingabe „Löschalter“ wird beim Deaktivieren ohne Seiten-Reload gesperrt
- [ ] Termine innerhalb des Löschalters bleiben erhalten
- [ ] Option aktivieren und Sync-Zeitraum festlegen
- [ ] Event mit importiertem ChurchTools-Bild anlegen
- [ ] Event-Ende in die Vergangenheit setzen
- [ ] Cron- oder manuellen Sync ausfuehren
- [ ] Importiertes Bild wird aus der Mediathek geloescht
- [ ] Abgelaufener Event-Datensatz wird gelöscht
- [ ] Manuell hochgeladenes, nicht vom Plugin importiertes Bild bleibt erhalten
- [ ] Von mehreren Events verwendetes importiertes Bild bleibt erhalten, bis keine Referenz mehr besteht
- [ ] Sync-Statistik enthaelt `expired_events_deleted` und `media_deleted`

## 4. Issues #3 und #9 Kalender und lange Titel

- [ c] Monatskalender mit kurzem Titel pruefen
- [x ] Monatskalender mit sehr langem Titel pruefen
- [ ] Modal mit sehr langem Titel pruefen
- [ ] Titel ueberlagert weder Inhalt noch Sidebar
- [ ] Kalender-Spalten bleiben gleich breit
- [ ] Tooltip-Titel brechen korrekt um
- [ ] Desktop-Ansicht pruefen
- [ ] Mobile Ansicht bei 768 px pruefen
- [ ] Mobile Ansicht bei 480 px pruefen

## 5. Issue #6 Tenant-URL

- [ ] Tenant als `gemeinde` speichern
- [ ] Tenant als `GEMEINDE` speichern
- [ ] Vollstaendige URL `https://GEMEINDE.church.tools/` einfuegen
- [ ] Gespeicherte URL lautet `https://gemeinde.church.tools`
- [ ] Verbindungstest mit der normalisierten URL erfolgreich

## 6. Issues #4 und #8 Posts-Sync und Gruppenposts

- [ ] Berichte-Sync auf Local aktivieren
- [ ] Berichte-Sync auf einer Hosting-Installation aktivieren
- [ ] Post-Gruppen synchronisieren
- [ ] Gruppe aus der Gruppenliste auswaehlen
- [ ] Echten Gruppenpost synchronisieren
- [ ] Zieltyp Beitrag pruefen
- [ ] Zieltyp Seite pruefen
- [ ] Zieltyp ChurchTools-Bericht pruefen
- [ ] Gutenberg-Block `[cts_posts]`-Ausgabe pruefen
- [ ] Shortcode-Ausgabe pruefen
- [ ] Aenderungserkennung durch erneuten Sync pruefen

## 7. Release-Abnahme

- [ ] Gesamt-ZIP in einer frischen WordPress-Installation installieren
- [ ] Hauptplugin und Add-ons aktivieren
- [ ] Alle sechs offenen Issues mit Testergebnis kommentieren
- [ ] Nach erfolgreicher Abnahme die entsprechenden Issues schliessen
- [ ] Git-Commit erstellen
- [ ] GitHub-Tag `v1.2.3.7` erstellen
- [ ] GitHub-Release mit Gesamt-ZIP und Einzel-ZIPs veroeffentlichen

## Aktueller Status

Die Local-Site `http://feg.local` ist erreichbar. Fuer die weiteren WordPress- und Sync-Tests ist noch eine Anmeldung im lokalen Admin-Bereich erforderlich.
