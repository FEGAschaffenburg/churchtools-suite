# ChurchTools Suite v1.2.2.0 — Stabilität & Addon-Updates

**Datum:** 17. Juli 2026

## Zusammenfassung

Major Minor Release mit Verbesserungen der Stabilität, Bug-Fixes bei der Appointment-Synchronisation und Version-Konsolidierung der Addon-Plugins. Alle Addons wurden auf konsistente Patch-Versionen erhöht.

## Änderungen (Main Plugin)

### 🐛 Bugfixes
- **Sync-Cleanup:** Konsistente Behandlung gelöschter ChurchTools-Termine über alle Phasen hinweg (v1.2.1.3)
- **Appointment Detection:** Verbesserte Erkennung gelöschter Termine bei inkrementellem Sync (v1.2.1.4)
- **Nested IDs:** Event-Appointment-IDs aus `event.appointment.base.id` und `event.appointment.id` werden korrekt berücksichtigt
- **Date Normalization:** Ungültige oder fehlende Zeitstempel werden nicht mehr auf aktuelle Zeit standardisiert

### ✨ Features
- Versionskonsolidierung über alle Addons hinweg
- Verbesserte Fehlertoleranz bei variablen API-Datumsfeldern

## Addon-Updates

| Addon | Alte Version | Neue Version | Änderungen |
|-------|-------------|--------------|-----------|
| **churchtools-suite-elementor** | 0.6.28 | 0.6.29 | Patch-Stabilität (aus v1.2.1.3 Updates) |
| **churchtools-suite-posts-sync** | 0.1.7 | 0.1.8 | Patch-Stabilität (aus v1.2.1.3 Updates) |
| **churchtools-suite-presentations** | 0.1.0 | 0.1.0 | Keine Änderungen |
| **churchtools-suite-demo** | 1.1.4.1 | 1.1.4.1 | Keine Änderungen |

## ZIP-Artefakte

- `churchtools-suite-1.2.2.0.zip`
- `churchtools-suite-elementor-0.6.29.zip`
- `churchtools-suite-posts-sync-0.1.8.zip`
- `churchtools-suite-presentations-0.1.0.zip`
- `churchtools-suite-demo-1.1.4.1.zip`

## Kompatibilität

- **WordPress:** ≥ 6.0
- **PHP:** ≥ 8.0
- **ChurchTools:** API v3+

## Hinweise

- Die Version 1.2.1.4 (GitHub-Release) zeigte Versionsmismatches in der Header-Deklaration. Diese Version konsolidiert alle Versionsangaben konsistent auf 1.2.2.0.
- Die lokale Entwicklungsversion 1.2.1.3 war die stabilste Version vor dieser Konsolidierung und ist nun als Backup gesichert.
