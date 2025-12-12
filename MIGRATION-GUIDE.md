# Datenbank-Migration für ChurchTools Suite

## Problem
Nach dem Update auf Version 0.3.7.x gibt es Datenbankfehler:
```
Unknown column 'event_id' in 'WHERE'
Unknown column 'appointment_id' in 'WHERE'
```

## Lösung

### Option 1: Automatische Migration (Empfohlen)
1. **Deaktivieren** Sie das Plugin in WordPress (Plugins → Installierte Plugins → ChurchTools Suite → Deaktivieren)
2. **Aktivieren** Sie das Plugin wieder
3. Die Datenbankschema-Migration läuft automatisch

### Option 2: Manuelle SQL-Migration
Falls Option 1 nicht funktioniert, führen Sie diese SQL-Befehle aus:

```sql
-- In phpMyAdmin oder einem anderen DB-Tool ausführen

-- WICHTIG: Passen Sie wp_ an Ihr Tabellen-Präfix an!

-- Spalte umbenennen und neue Spalten hinzufügen
ALTER TABLE `wp_cts_events` 
  CHANGE COLUMN `external_id` `event_id` varchar(100) NOT NULL,
  ADD COLUMN `appointment_id` varchar(100) DEFAULT NULL AFTER `calendar_id`,
  ADD COLUMN `raw_payload` longtext DEFAULT NULL AFTER `status`;

-- Indexe anpassen
ALTER TABLE `wp_cts_events`
  DROP INDEX IF EXISTS `idx_external_id`,
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD KEY `appointment_id` (`appointment_id`);
```

### Was wird geändert?
- `external_id` → `event_id` (umbenennen)
- Neue Spalte: `appointment_id` (für Appointments ohne Events)
- Neue Spalte: `raw_payload` (für vollständige API-Daten)

### Testen
Nach der Migration:
1. Gehen Sie zu **ChurchTools → Sync**
2. Klicken Sie auf **"Termine jetzt synchronisieren"**
3. Es sollten keine Datenbankfehler mehr auftreten

## Hinweis
Beim nächsten Plugin-Update (ab v0.3.7.3) wird diese Migration automatisch beim Aktivieren durchgeführt!
