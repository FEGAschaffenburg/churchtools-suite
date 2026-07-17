# Konzept: Durchgängige Backend-Logik (Hauptplugin + Addons)

**Datum:** 27.02.2026  
**Gültig für:** `churchtools-suite` Monorepo (Main + Addons)  
**Ziel:** Einheitlicher, nachvollziehbarer und wartbarer Admin-Flow für Konfiguration, Auswahl und Synchronisation.

---

## 1) Ist-Analyse (kurz)

Aktuell ist die Logik fachlich korrekt, aber aus Nutzerperspektive verteilt:

- **Konfiguration** liegt in `Einstellungen` (z. B. Zeitraum, Auto-Sync, Berichte-Filter).
- **Quellen synchronisieren** liegt teils in separaten Tabs (`Kalender`, `Dienste`) und teils in Addon-Seiten.
- **Auswahl speichern** (Kalender/Services/Gruppen) erfolgt in unterschiedlichen Screens mit unterschiedlichen Mustern.
- **Manueller Sync** liegt teilweise im `Synchronisation`-Tab, teilweise in Addon-Übersichten, teilweise als Debug-Aktion.
- Addons erweitern bereits per Hook (gut), aber UX ist nicht durchgängig in einem einheitlichen Ablauf.

**Folge:** Nutzer müssen „wissen“, wo sie was tun müssen (Quelle syncen vs. auswählen vs. Lauf starten).

---

## 2) Leitprinzipien (Soll)

1. **Eine Stelle für Aktionen, eine Stelle für Einstellungen**
   - Aktionen (Sync jetzt, Quelle laden, Auswahl übernehmen) nur in „Synchronisation“.
   - Persistente Konfiguration nur in „Einstellungen“.

2. **Gleicher Ablauf für jedes Modul**
   - Für Termine, Dienste, Berichte (und zukünftige Addons) identischer 4‑Schritt-Flow.

3. **Main orchestriert, Addons liefern Module**
   - Hauptplugin steuert Seitenstruktur, Reihenfolge, Cron-Orchestrierung, Statusanzeige.
   - Addons registrieren sich als „Sync-Module“ mit klaren Contracts.

4. **Abhängigkeiten explizit sichtbar**
   - Beispiel: „Kalender zuerst synchronisieren, dann auswählen, dann Termine synchronisieren“.

5. **Einheitliche Status-/Fehlerkommunikation**
   - Gleiches Notice-Format, gleiche KPI-Felder, gleiches Logging-Schema pro Modul.

---

## 3) Ziel-Informationsarchitektur

## 3.1 Hauptmenü

- `ChurchTools`
  - `Übersichten` (nur lesen: KPIs, letzte Läufe, Health)
  - `Synchronisation` (alle operativen Aktionen)
  - `Einstellungen` (alle persistenten Optionen)
  - `Addons` (aktivieren/Versionen/Kompatibilität)
  - `Debug` (Logs, Trigger, Diagnostics)
  - `Dokumentation` (extern)

## 3.2 Rollen der Seiten

### A) `Synchronisation` (Operations Hub)
Für **jedes Modul** derselbe Aufbau:

1. **Quelle synchronisieren** (z. B. Kalender, Service-Gruppen, Post-Gruppen)
2. **Auswahl treffen/speichern**
3. **Daten synchronisieren** (Termine/Services/Berichte)
4. **Ergebnis + letzter Lauf + Fehler**

Zusätzlich oben global:
- „**Alle aktivierten Module jetzt synchronisieren**“
- optional je Modul Ein/Aus für diesen Lauf

### B) `Einstellungen` (Configuration Hub)
Nur langlebige Parameter:
- API/Verbindung
- Zeitfenster/Intervall
- Modul-Settings (z. B. Berichte-Target-Type, Sichtbarkeit, Includes)

**Keine** „Jetzt synchronisieren“-Buttons auf dieser Seite.

### C) `Übersichten` (Read-only Hub)
- Statuskarten pro Modul:
  - Aktiviert?
  - Letzte Quelle-Synchronisation
  - Letzte Daten-Synchronisation
  - Anzahl Datensätze
  - Fehlerstatus
- Schnelllinks zu `Synchronisation` (nicht zu verstreuten Einzelseiten)

---

## 4) Standard-Flow pro Modul (verbindlich)

Jedes Modul folgt derselben State-Machine:

1. `source_synced` (Quelle geladen)
2. `selection_saved` (Filter/Auswahl gespeichert)
3. `data_synced` (fachliche Daten aktualisiert)

**UI-Regeln:**
- Schritt 2 ist erst aktiv, wenn Schritt 1 erfolgreich war.
- Schritt 3 ist erst aktiv, wenn Schritt 2 gültig ist.
- Jede Aktion zeigt einheitlich: Erfolg, Warnung, Fehler, Zeitstempel.

---

## 5) Technische Zielarchitektur

## 5.1 Modul-Registry (Main Plugin)

Neuer zentraler Hook im Main Plugin:

- `apply_filters( 'cts_register_sync_modules', [] )`

Jedes Modul liefert ein Manifest, z. B.:

- `id` (z. B. `events`, `services`, `posts`)
- `label`
- `capability`
- `dependencies` (z. B. `events` hängt von `calendars`-source+selection ab)
- `render_sync_ui_callback`
- `render_settings_ui_callback`
- `save_settings_callback`
- `run_source_sync_callback`
- `run_data_sync_callback`
- `get_status_callback`

So bleibt Logik im Addon, aber UX-Struktur zentral im Main.

## 5.2 Einheitliche Endpoints

Aktuell viele spezifische AJAX-Actions. Ziel:

- `cts_module_action` mit Parametern:
  - `module`
  - `action` = `source_sync | selection_save | data_sync | settings_save | status`

Alternativ weiter mehrere Endpoints intern, aber eine gemeinsame Dispatcher-Schicht im Main.

## 5.3 Einheitliches Statusmodell

Option/Transient-Struktur pro Modul:

- `churchtools_suite_module_{id}_status`
  - `last_source_sync_at`
  - `last_selection_save_at`
  - `last_data_sync_at`
  - `last_result` (counts/errors/message)
  - `state` (`idle|running|error|ok`)

## 5.4 Einheitliche Locks

Zur Vermeidung paralleler Läufe:
- pro Modul Lock (`cts_lock_module_{id}`)
- globaler Orchestrierungs-Lock (`cts_lock_all_sync`)
- Timeout + sauberer Release bei Fehlern

---

## 6) Cron-Logik (vereinheitlicht)

Aktuell existieren mehrere Trigger-Pfade. Ziel:

- Ein Haupt-Cron „Auto-Sync Orchestrator“ im Main.
- Dieser lädt Modul-Registry und verarbeitet aktivierte Module in definierter Reihenfolge.
- Addons liefern nur `run_data_sync_callback` + `get_status_callback`.

Reihenfolge-Beispiel:
1. Calendars source/selection check
2. Events sync
3. Services sync
4. Posts sync

---

## 7) Migrationsplan (inkrementell, ohne Big Bang)

## Phase 1 – UX konsolidieren (ohne API-Bruch)

- In `Synchronisation` alle Aktions-Buttons pro Modul zentral anbieten.
- In `Einstellungen` nur Speichern-Formulare behalten.
- Bestehende AJAX-Actions weiterverwenden, aber über gemeinsame UI aufrufen.

## Phase 2 – Modul-Registry einführen

- Main lädt Module via `cts_register_sync_modules`.
- Services- und Posts-Addon registrieren Manifest.
- Bestehende Hook-Punkte (`cts_*_settings_render/save`) bleiben als Fallback.

## Phase 3 – Endpoint/Status vereinheitlichen

- Dispatcher `cts_module_action` einführen.
- Einheitliches Statusobjekt + Noticemapping.
- Alte Endpoints markieren als deprecated (1–2 Releases Übergang).

## Phase 4 – Cron-Orchestrator

- Auto-Sync nur noch orchestriert.
- Modulabhängigkeiten strikt prüfen.

---

## 8) Konkrete Soll-Regeln für aktuelle Module

### Termine (Main)
- Quelle: Kalender-Sync
- Auswahl: Kalender-Auswahl
- Daten-Sync: Termine
- Settings: Zeitraum + Auto-Sync

### Dienste (Main)
- Quelle: Service-Gruppen-Sync
- Auswahl: Gruppen + Services
- Daten-Sync: Services importieren
- Settings: nur dauerhafte Parameter

### Berichte (Posts-Sync Addon)
- Quelle: Post-Gruppen-Sync
- Auswahl: Gruppen-IDs/Filter
- Daten-Sync: Berichte importieren
- Settings: Target-Type, Status, Include-Flags, Zeitfilter

**Wichtig:** Berichte bleiben addon-own business logic, aber erscheinen im identischen Sync-Flow.

### Antwort auf die Architekturfrage: Servicegruppen und Postgruppen

**Kurzantwort:** Ja, aus Architektur-/UX-Sicht sind sie **das gleiche Muster**, aber **nicht dieselbe Domäne**.

- **Gleiches Muster (wiederverwendbar):**
   - Quelle laden (`source_sync`)
   - Auswahl speichern (`selection_save`)
   - Status anzeigen (Zeitpunkt, Anzahl, Fehler)
- **Unterschiedliche Domäne (getrennte Implementierung):**
   - anderer Endpoint
   - andere Feldstruktur/Payload
   - andere Zielobjekte in WordPress

Empfehlung: ein gemeinsames technisches Basiskonzept `Group Source` (UI + State + Callbacks), aber pro Domäne eigene Adapter/Mapper.

---

## 8.1) Inkrementelles Refactoring ohne Gesamtumbau

Damit das Team parallel weiterliefern kann, gelten folgende Leitplanken:

1. **Kein Breaking Change an bestehenden AJAX-Actions in Phase 1/2**
2. **Pro Schritt maximal 1–2 Screens anfassen**
3. **Jeder Schritt ist releasefähig und rückbaubar**
4. **Neue Logik zuerst als Adapter/Fassade vor bestehender Logik**

---

## 8.2) Umsetzungs-Backlog (arbeitspaketfähig)

### Paket A — UI-Entzerrung (klein, sofort)

**Ziel:** Nutzer findet alles konsistent, ohne technische Logik zu ändern.

- In `Synchronisation` pro Modul die 3 Aktionen sichtbar machen (Quelle, Auswahl, Daten-Sync).
- In `Einstellungen` alle „Jetzt synchronisieren“-Buttons entfernen/verlinken.
- In `Übersichten` nur Read-only KPIs + Link auf `Synchronisation`.

**Akzeptanz:** Kein operativer Sync-Button mehr in Einstellungsformularen.

### Paket B — Gemeinsame Group-Source-Komponente

**Ziel:** Servicegruppen und Postgruppen nutzen denselben UI-/State-Baustein.

- Neues abstraktes Muster `GroupSourceModule` definieren (keine harte Vererbung nötig).
- Einheitliche Callbacks:
   - `run_group_source_sync`
   - `save_group_selection`
   - `get_group_source_status`
- Servicegruppen-Flow und Postgruppen-Flow daran anbinden.

**Akzeptanz:** Beide Gruppenmodule haben identisches Bedienmuster und identische Statusanzeige.

### Paket C — Modul-Status vereinheitlichen

**Ziel:** Ein gemeinsamer Statusvertrag für Main und Addons.

- Option pro Modul: `churchtools_suite_module_{id}_status`
- Pflichtfelder: `state`, `last_source_sync_at`, `last_data_sync_at`, `last_result`
- Einheitliche Noticemapper für success/warning/error.

**Akzeptanz:** Übersicht und Debug lesen nur noch dieses Format.

### Paket D — Registry im Main (ohne Endpoint-Umbau)

**Ziel:** Addons registrieren sich formal, Logik bleibt lokal.

- Filter `cts_register_sync_modules` produktiv nutzen.
- Main rendert die Modulsektionen dynamisch.
- Bestehende Hooks (`cts_*_settings_*`) bleiben als Kompatibilität.

**Akzeptanz:** Neues Modul kann ohne Core-Template-Änderung eingebunden werden.

### Paket E — Dispatcher + Deprecation

**Ziel:** Technische Vereinheitlichung, ohne sofort alte Endpoints zu löschen.

- `cts_module_action` einführen.
- Alte Endpoints intern weiter auf bestehende Handler routen.
- Deprecation-Hinweis in Logs + Changelog.

**Akzeptanz:** Neues UI nutzt Dispatcher, altes UI funktioniert weiter.

---

## 8.3) Reihenfolge für die nächsten 3 Releases

### Release R1 (risikoarm)
- Paket A komplett
- Paket B nur für UI-Struktur (ohne tiefe Service-Refactors)

### Release R2
- Paket B technisch abschließen
- Paket C einführen

### Release R3
- Paket D einführen
- Paket E vorbereiten (Dispatcher parallel)

---

## 8.4) DoD je Schritt (Definition of Done)

Ein Arbeitspaket gilt nur als fertig, wenn:

1. Sync-Flow im UI für betroffene Module identisch wirkt
2. Keine bestehende Action/URL für Alt-Nutzer bricht
3. Lint/Smoke-Test im lokalen WP fehlerfrei
4. Changelog-Eintrag für Migrationshinweis vorhanden

---

## 8.5) Risiken und Gegenmaßnahmen

- **Risiko:** Doppelter Zustand (alt + neu) erzeugt Inkonsistenz  
   **Gegenmaßnahme:** Neue Statusoption als Single Source of Truth, alte Werte nur lesen/fallback.

- **Risiko:** Addons verlieren Autonomie  
   **Gegenmaßnahme:** Registry + Callback-Contract, keine Businesslogik in Core verschieben.

- **Risiko:** Zu großer Umbau auf einmal  
   **Gegenmaßnahme:** Release-Rhythmus R1/R2/R3 strikt einhalten.

---

## 9) Akzeptanzkriterien

Das Konzept gilt als umgesetzt, wenn:

1. Ein Nutzer ohne Vorwissen pro Modul denselben Ablauf sieht.
2. Keine Sync-Aktionen mehr in Einstellungsformularen verstreut sind.
3. Alle Module in einer gemeinsamen Synchronisationsseite bedienbar sind.
4. Status/KPIs/Fehler konsistent dargestellt werden.
5. Auto-Sync-Reihenfolge nachvollziehbar und zentral orchestriert ist.

---

## 10) Kurzempfehlung für den nächsten Sprint

1. **Phase 1 starten** (höchster UX-Gewinn, geringes Risiko):
   - „Synchronisation“ als zentrale Action-Seite ausbauen.
   - Settings-Seiten auf reine Konfiguration begrenzen.

2. **Posts-Sync als Referenzmodul** in die einheitliche Sync-UI ziehen.

3. Danach **Modul-Registry** im Main einführen (Phase 2).

---

## Entscheidungsnotiz

Die bestehende Hook-Strategie ist bereits eine gute Grundlage. Das fehlende Stück ist primär eine **einheitliche Orchestrierung und UX-Führung** im Main Plugin. Dieses Konzept nutzt die aktuelle Architektur, statt sie zu brechen.

---

## 11) Addon-Trennung (überarbeitet)

Ziel der Trennung: **Main bleibt Plattform**, Addons liefern **optionale Integrationen oder fachliche Domänen mit eigener Lebensdauer**.

### 11.1) Entscheidungsregeln: Gehört ein Feature in Core oder Addon?

Ein Feature gehört in den **Core (Main Plugin)**, wenn mindestens 3 Punkte zutreffen:

1. Es ist für den Standardnutzer ohne Zusatzplugin relevant.
2. Es wird in der zentralen Synchronisationsreihenfolge zwingend benötigt.
3. Es hat keine harte Drittanbieter-Abhängigkeit (außer ChurchTools + WordPress).
4. Es definiert Plattform-Bausteine (Registry, Statusmodell, Locks, Orchestrator).

Ein Feature gehört in ein **Addon**, wenn mindestens 2 Punkte zutreffen:

1. Es hat eine externe Produktabhängigkeit (z. B. Elementor).
2. Es ist optionaler Fachbereich (nicht jeder Kunde nutzt ihn).
3. Es braucht eigenes Release-Tempo oder experimentelle Iterationen.
4. Es erweitert primär UI/Output, nicht den Plattform-Kern.

### 11.2) Entscheidungsmatrix für aktuelle Module

#### A) Main Plugin (`churchtools-suite`) bleibt Core für

- CT-Auth/Client, Session-Handling, Basis-Fehlerbehandlung.
- zentrale Admin-Navigation, Sync-Seite, Übersicht, Einstellungen.
- Modul-Registry, Statusvertrag, Locking, Cron-Orchestrierung.
- Event-/Kalender-Synchronisation (Basis-Datendomäne).
- Service-/Servicegruppen-Synchronisation (als Core-Domäne, sofern produktiv genutzt).

#### B) Addon `churchtools-suite-elementor` bleibt klar Addon

- harte Drittabhängigkeit (`elementor`).
- eigener UI-/Widget-Lebenszyklus.
- keine Verschiebung in Core vorgesehen.

#### C) Addon `churchtools-suite-posts-sync` bleibt Addon, aber mit enger Core-Anbindung

- fachlich optional (nicht jeder braucht Berichte-Import nach WP-Inhalte).
- nutzt denselben Orchestrierungs- und Statusvertrag wie Core-Module.
- Businesslogik bleibt im Addon (Mapper, Target-Type, Include-Optionen).

### 11.3) Was **nicht** in Addons dupliziert werden darf

Folgende Bausteine dürfen nur einmal im Main geführt werden:

- Modulstatus-Schema
- Locking-Mechanik
- globaler Auto-Sync-Orchestrator
- gemeinsame Admin-Notices/Statusdarstellung

Addons liefern nur Adapter/Module, keine zweite Plattform.

### 11.4) Migrationsregel für zukünftige Features

Für jedes neue Feature wird vor Implementierung ein kurzer „Placement Check" gemacht:

1. **Abhängigkeit prüfen** (Drittanbieter ja/nein)
2. **Optionalität prüfen** (Standardfall oder Spezialfall)
3. **Orchestrierungsrelevanz prüfen** (zentral notwendig oder modulär)

Ergebnis:

- „Core" → Implementierung im Main inkl. Registry-Eintrag.
- „Addon" → eigenes Addon-Modul mit Contract zum Main.

### 11.5) Konkrete Empfehlung ab sofort

1. Elementor bleibt strikt separat.
2. Posts-Sync bleibt separat, wird aber zuerst auf den gemeinsamen Modul-Contract gebracht.
3. Keine weiteren „Mini-Frameworks" in Addons aufbauen; Infrastruktur nur im Main.
4. Bei neuen CT-Domänen (z. B. weitere API-Bereiche) zuerst Core-Kriterien prüfen, erst dann Addon-Entscheidung.
