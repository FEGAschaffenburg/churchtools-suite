# ChurchTools Posts Sync – Konzept & Implementierungsplan

**Datum**: Februar 2026  
**Status**: Als Addon in Monorepo strukturiert  
**Version**: Main v1.1.5.0 (geplant), Addon v0.1.0

---

## 1. Feature-Übersicht

### Zielstellung
Automatische Synchronisation von **ChurchTools-Posts** (z.B. news, announcements) in WordPress-Posts/Seiten ermöglichen, um Inhalte aus ChurchTools direkt auf der Website zu veröffentlichen.

### Nutzen
- **Zentrale Content-Verwaltung**: Redakteure pflegen Inhalte nur in ChurchTools
- **Bidirektionale Integration**: ChurchTools = Contentquelle, WordPress = Veröffentlichungsplattform
- **Flexible Zieltypen**: Inhalte als Posts oder Seiten
- **Flexible Veröffentlichung**: Draft, Entwurf oder privat
- **Optional**: Deaktivierbar pro Installation (Zero Breaking Changes)

### Non-Ziele
- Bilder/Medien synchronisieren (Phase 1)
- Kategorien/Tags synchronisieren (Phase 1)
- Rückwärts-Sync (WordPress → ChurchTools)
- Löschen von nicht mehr vorhandenen Posts

---

## 2. Architektur & Design

### 2.1 Komponenten

#### A. Service: `ChurchTools_Suite_Posts_Sync_Service`
**Datei**: `addons/churchtools-suite-posts-sync/includes/class-cts-posts-sync-service.php`

**Zweck**: 
- Fetcht Posts von der ChurchTools API
- Normalisiert die Daten (Titel, Content, Excerpt, Slug, Datum)
- Erstellt oder aktualisiert WordPress-Posts basierend auf Änderungserkennung

**Wichtige Methoden**:
```php
public function sync_posts(): void
  → Haupteinstiegspunkt
  
private function fetch_posts_from_api(): array
  → Holt Posts von /api/posts?from=...&to=...
  
private function normalize_ct_post( array $post ): array
  → Bereinigt HTML, generiert Slug, konvertiert Datum
  
private function find_existing_post_id( string $ct_post_id ): ?int
  → Sucht vorhandenen Post via Meta `_cts_ct_post_id`
  
private function create_or_update_post( array $normalized_post ): void
  → Erstellt neuen Post oder aktualisiert existierenden
```

**Change Detection**:
- MD5-Hash der normalisierten Daten wird als Meta `_cts_ct_post_hash` gespeichert
- Nur bei Hash-Unterschied wird aktualisiert (verhindert unnötige Änderungen)

#### B. Settings-Optionen
**Datei**: `admin/views/settings/subtab-sync.php`

**Drei neue Optionen**:
```
1. churchtools_suite_ct_posts_sync_enabled  (Boolean)
   → Aktiviert/Deaktiviert die Funktion (default: 0 = aus)

2. churchtools_suite_ct_posts_target_type   (Enum: 'post' | 'page')
   → Zieltyp bestimmen (Standard: 'post')

3. churchtools_suite_ct_posts_target_status (Enum: 'draft' | 'publish' | 'private')
   → Veröffentlichungsstatus (Standard: 'draft')
```

#### C. Integration in Sync-Flows (Hook-basiert)

**1. Manueller Sync (AJAX)**:
- `ajax_sync_events()` in `admin/class-churchtools-suite-admin.php`
- Main Plugin triggert Hook `do_action( 'cts_do_sync_posts', $client )`
- Addon übernimmt Sync-Logik, falls aktiv

**2. Manual Trigger (AJAX)**:
- `ajax_trigger_manual_sync()` für Emergency/Debug-Syncs
- Main Plugin triggert Hook `do_action( 'cts_do_sync_posts', $ct_client, $result )`
- Addon erweitert `$result` um `ct_posts_*` Werte

**3. Automatischer Cron-Sync**:
- `auto_sync()` in `includes/class-churchtools-suite-cron.php`
- Main Plugin triggert Hook `do_action( 'cts_do_sync_posts', $ct_client, $result )`
- Addon wird nur ausgeführt, wenn installiert + aktiviert

### 2.2 Datenfluss

```
ChurchTools API
     ↓
[Fetch] → GET /api/posts?from=X&to=Y (2 Wochen Fenster)
     ↓
[Normalize] → Title, Content (HTML), Excerpt, Slug, Date
     ↓
[Hash] → MD5 des normalisierten Post
     ↓
[Lookup] → Vorhandener Post via `_cts_ct_post_id` Meta?
     ↓
[Update/Create]
     ├─ Neue Posts: wp_insert_post() + Metas
     ├─ Existierende: wp_update_post() nur wenn Hash unterschiedlich
     └─ Metas: `_cts_ct_post_id`, `_cts_ct_post_hash`
     ↓
WordPress Post/Seite
```

### 2.3 Konfigurierbare Parameter

| Parameter | Typ | Standard | Bereich |
|-----------|-----|---------|---------|
| Enabled | Boolean | `false` | on/off |
| Target Type | String | `post` | `post`, `page` |
| Target Status | String | `draft` | `draft`, `publish`, `private` |
| Sync Window | Days | 14 | (Hard-coded, siehe `self::SYNC_DAYS`) |
| API Endpoint | String | `/api/posts` | (Hard-coded) |

---

## 3. Implementierungsstatus

### 3.1 Was ist fertig ✅

1. **Posts-Sync Addon entwickelt**
   - ✅ API-Integration (Fetch)
   - ✅ Datennormalisierung
   - ✅ Change Detection (Hash)
   - ✅ Create/Update-Logik
   - ✅ Meta-Tracking (CT Post ID, Hash)
   - ✅ Error Handling & Logging

2. **Settings UI integriert**
   - ✅ Drei neue Input-Controls
   - ✅ Form-Processing mit `sanitize_text_field()`
   - ✅ Speicherung via `update_option()`

3. **Hook-Integration in Main Plugin**
- ✅ `ajax_sync_events()` triggert `cts_do_sync_posts`
- ✅ `ajax_trigger_manual_sync()` triggert `cts_do_sync_posts`
- ✅ `auto_sync()` in Cron triggert `cts_do_sync_posts`

4. **Logging & Debugging**
   - ✅ Sync-Statistiken tracking (count_created, count_updated, errors)
   - ✅ Detaillierte Error-Messages
   - ✅ Integration mit bestehendem Debug-Tab

### 3.2 Was fehlt noch ⚠️

1. **Syntax-Validierung**
   - Alle Dateien müssen auf PHP-Fehler geprüft werden

2. **End-to-End-Test**
   - Lokale Installation: Sync triggern & Ergebnis prüfen
   - Verschiedene Zieltypen (post/page) testen
   - Verschiedene Status (draft/publish/private) testen
   - Hash-Change-Detection verifizieren
   - Aktualisierungslogik validieren (sollte alte Posts nur bei Hash-Änderung updaten)

3. **Migration für Meta-Felder** (optional)
   - Falls ältere Versionen ohne diese Metas existieren, ggf. Migrations-Task

4. **Dokumentation**
   - User-Dokumentation (wie wird die Funktion in Admin konfiguriert?)
   - Troubleshooting Guide
   - CHANGELOG-Eintrag

5. **Version & Release (Monorepo)**
- Versionen setzen: Main + Addons
- Drei ZIP-Artefakte erstellen (`main`, `elementor`, `posts-sync`)
- Ein GitHub Release im Monorepo erstellen
- Alle drei ZIPs als Assets hochladen

---

## 4. Testing-Plan

### Phase 1: Code-Validierung
```
☐ PHP Syntax Check → php -l alle neuen/geänderten Dateien
☐ WP Fehler-Log → wp-content/debug.log auf Warnings/Errors
☐ AJAX Handler Response → Browser DevTools Network
```

### Phase 2: Funktionale Tests

#### Test 1: Feature Enabled/Disabled
```
1. Settings: Posts Sync DISABLED
2. Trigger Sync (manual)
3. Erwartung: Keine Posts erstellt
4. Repeat mit ENABLED
```

#### Test 2: Target Type
```
1. Settings: Target Type = 'page'
2. Trigger Sync
3. Erwartung: Neue Items in wp_posts mit post_type='page'
4. Repeat mit 'post'
```

#### Test 3: Target Status
```
1. Settings: Target Status = 'draft'
2. Trigger Sync
3. Erwartung: Alle neuen Posts mit post_status='draft'
4. Repeat mit 'publish', 'private'
```

#### Test 4: Change Detection
```
1. Trigger Sync (erstellt Posts A, B, C)
2. In ChurchTools: Post A nicht ändern, Post B ändern, Post C löschen
3. Trigger Sync erneut
4. Erwartung:
   - Post A: last_modified unverändert
   - Post B: last_modified aktualisiert
   - Post C: weiterhin in WordPress vorhanden (nicht gelöscht)
   - Keine neuen Duplikate
```

#### Test 5: Meta-Tracking
```
1. Trigger Sync (erstellt Posts)
2. Prüfe WP-Admin → Post Meta:
   - _cts_ct_post_id (sollte ChurchTools Post ID sein)
   - _cts_ct_post_hash (sollte MD5 sein)
3. Erwartung: Beide Metas vorhanden und konsistent
```

#### Test 6: Auto-Cron
```
1. Konfiguriere WP-Cron auf kurzes Intervall (z.B. hourly = 1h)
2. Warte auf Cron-Trigger (oder manuell: wp-cli cron test)
3. Erwartung: Posts automatisch synchronisiert
```

### Phase 3: Integrationstests

#### Test 7: Parallelität mit Event-Sync
```
1. Event-Sync aktiv, Post-Sync aktiv
2. Trigger kombinierter Sync
3. Erwartung:
   - Beide Syncs laufen ohne Konflikte
   - Stats zeigen beide Zähler (Events: X, Posts: Y)
```

#### Test 8: Fehlerbehandlung
```
1. ChurchTools API: Offline simulieren (Host blockieren oder Timeout)
2. Trigger Sync
3. Erwartung:
   - Error wird geloggt
   - Sync bricht nicht ab
   - Event-Sync läuft weiter
```

---

## 5. Release-Plan

### Schritte

1. **Syntax-Validierung** (siehe Phase 1: Code-Validierung)
   - Alle neuen Dateien durchprüfen
   - Fehler korrigieren falls vorhanden

2. **Funktionale Tests durchführen** (siehe Phase 2-3)
   - Lokale Installaation nutzen
   - Alle Test-Szenarien durchlaufen
   - Bugs dokumentieren und fixen

3. **Dokumentation aktualisieren**
   - `CHANGELOG.md`: Eintrag für v1.1.5.0 hinzufügen
   - `MIGRATION-GUIDE.md`: Falls nötig (wahrscheinlich nicht)
   - `README.md`: Feature kurz erwähnen

4. **Version erhöhen**
   - `churchtools-suite.php` Header: `1.1.4.16` → `1.1.5.0`
   - `churchtools-suite.php` Constant: `CHURCHTOOLS_SUITE_VERSION` → `1.1.5.0`

5. **ZIP-Artefakte erstellen**
   ```powershell
   cd scripts
   .\create-wp-zip.ps1 -Version "1.1.5.0" -Plugin main
   .\create-wp-zip.ps1 -Version "0.6.10" -Plugin elementor
   .\create-wp-zip.ps1 -Version "0.1.0" -Plugin posts-sync
   ```
   - ZIPs liegen in `C:\privat\`

6. **Monorepo GitHub Release erstellen**
   ```bash
   git tag v1.1.5.0
   git push
   git push --tags
   ```
   - Ein Release im Repo `FEGAschaffenburg/churchtools-suite`
   - Drei ZIP-Artefakte hochladen
   - Release Notes mit Versionsmatrix Main + Addons

7. **Auto-Update testen**
   - Einige Zeit warten (API Cache)
   - Live-Installation: Check für Updates sollte v1.1.5.0 anbieten

---

## 6. Konfigurationsbeispiele

### Szenario A: Content-Team nutzt ChurchTools für News-Verwaltung
```
Posts Sync: ENABLED ✓
Target Type: post
Target Status: draft
→ Alle ChurchTools-Posts werden als Draft-Posts in WP importiert
→ Redakteur prüft, ergänzt ggf. Bilder, dann publish
```

### Szenario B: Automatische Veröffentlichung von Ankündigungen
```
Posts Sync: ENABLED ✓
Target Type: post
Target Status: publish
→ ChurchTools-Posts werden direkt veröffentlicht auf Website
→ Kein manueller Schritt nötig
```

### Szenario C: Statische Seiten aus ChurchTools
```
Posts Sync: ENABLED ✓
Target Type: page
Target Status: publish
→ ChurchTools-Posts werden als Seiten erstellt
→ Gut für "FAQ", "Über uns", etc.
```

---

## 7. Bekannte Limitierungen & Future Work

### Phase 1 (aktuell):
- ❌ Keine Bilder/Medien
- ❌ Keine Kategorien/Tags
- ❌ Keine Custom Fields
- ❌ Keine Rückwärts-Sync (WP → CT)
- ❌ Posts nicht löschen, wenn in CT nicht mehr vorhanden

### Mögliche Phase 2:
- ⭐ Featured Image aus CT-Bild
- ⭐ ChurchTools-Kategorien → WP Kategorien
- ⭐ Verknüpfung mit Event-Syncs (z.B. Post zu Event)
- ⭐ Custom Fields Template für erweiterte Felder
- ⭐ Conditional Sync (nur bestimmte CT-Kategorien)

---

## 8. Technische Notizen

### Warum Optionality (Feature deaktivierbar)?
- **Backward Compatibility**: Bestehende Installationen nicht beeinträchtigt
- **Opt-In Prinzip**: Nur wer die Funktion braucht, nutzt sie
- **Testing**: Einfacher auszuschalten, falls Bugs auftauchen

### Warum Hash-Based Change Detection?
- **Effizienz**: Verhindert unnötige `post_modified` Updates
- **Datenschonung**: WP-Admin zeigt nur tatsächliche Änderungen
- **Standard-Muster**: Gleich wie Event-Sync (consistency)

### Warum 14-Tage-Fenster?
- **Balanceakt**: Nicht zu alt (Performance), nicht zu neu (Stabilität)
- **Konsistenz**: Passt zu Event-Sync Fenster
- **Configurable**: Ggf. in Later Versions anpassbar

### Security Considerations
- ✅ Nonce-Check in AJAX Handlers
- ✅ Admin-Capability Check (`manage_options`)
- ✅ `sanitize_text_field()` bei Optionen
- ✅ `wp_kses_post()` für Content (falls später implementiert)
- ✅ API Auth via bestehenden CT_Client (sicher)

---

## 9. Success Criteria

Release gilt als erfolgreich, wenn:

- ✅ Alle Syntax-Fehler behoben
- ✅ Alle Test-Szenarien (Phase 2-3) erfolgreich
- ✅ Settings speichern/abrufen korrekt
- ✅ Manueller Sync triggert erfolgreich
- ✅ Auto-Cron läuft ohne Fehler
- ✅ Change Detection verhindert unnötige Updates
- ✅ Logging zeigt korrekten Status
- ✅ Dokumentation aktualisiert
- ✅ ZIP-Asset erstellt & GitHub Release vorhanden
- ✅ Auto-Updater erkennt neue Version

---

## 10. Kontakt & Fragen

Falls während des Testing Fragen auftauchen:
1. Zunächst in `wp-content/debug.log` prüfen (WP_DEBUG=true)
2. Dann Admin → Debug-Tab → "ChurchTools Posts → WordPress" Statistiken
3. AJAX-Response in Browser DevTools prüfen

---

**Nächste Schritte**: 
1. Diesen Plan reviewen
2. Syntax validieren
3. Phase 2-3 Tests durchführen
4. Releaseplan umsetzen
