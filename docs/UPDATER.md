# Updater (GitHub Releases)

Diese Datei beschreibt das Update-Verhalten des Plugins und wie man den GitHub-basierten Updater konfiguriert.

## Übersicht

- Das Plugin prüft GitHub Releases der Repository-Release-Tags und injiziert verfügbare Updates in WordPress' Update-Mechanismus.
- Prüfen (`Manuelles Update prüfen`) zeigt nur Metadaten (Version, Release-Notizen, Asset-Name). Die tatsächliche Installation läuft nur nach Bestätigung (`Update ausführen`).
- Installation verwendet die WordPress `Plugin_Upgrader` API, erstellt vor der Installation ein ZIP-Backup des aktuellen Plugins und versucht Rollback bei Fehlern.

## Token / Auth

Für private Repositories oder erhöhte Rate-Limits kann ein GitHub Personal Access Token (PAT) verwendet werden.

- Option (empfohlen für Admins): Plugin-Option `churchtools_suite_github_token` (über secure UI oder WP-CLI setzen).
- Alternative (CI / sichere Speicherung): PHP-Konstante `WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN` in `wp-config.php` setzen.

Beispiel (wp-config.php):

```php
define('WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN', 'ghp_xxx...');
```

Warnung: Speichere PATs sicher; gib sie niemals in öffentliche Repositories weiter.

## Manuelles Prüfen vs. Automatische Updates

- Manuelles Prüfen (`cts_manual_update`) liefert JSON mit `is_update`, `latest_version`, `package_url` und `body` (Release Notes).
- `cts_run_update` führt anschließend die Installation durch.
- Automatische Prüfintervalle werden über die Option `churchtools_suite_update_interval` gesteuert (hourly/daily/weekly).

## Rollback

Vor der Installation wird ein ZIP-Backup des aktuellen `churchtools-suite`-Ordners angelegt. Bei Fehlern während der Installation versucht der Upgrader, das vorherige ZIP wiederherzustellen.

## Release-Paket

Das Release muss ein WordPress-kompatibles ZIP enthalten, das die Plugin-Datei `churchtools-suite.php` im Root des ZIPs hat. Verwende das mitgelieferte Packaging-Skript `scripts/create-wp-zip.ps1`.

## Troubleshooting

- Wenn das Update nicht in der Plugin-Übersicht angezeigt wird, transient `churchtools_suite_github_release` löschen bzw. Cache leeren.
- Prüfe `error_log` und das Plugin-Log (sofern aktiviert) bei Installationsfehlern.

## Weitere Hinweise für Entwickler

- API-Requests zu GitHub verwenden optional den Authorization-Header `Bearer <token>`.
- `pre_set_site_transient_update_plugins` wird verwendet, um WP die neue Version mitzuteilen.
