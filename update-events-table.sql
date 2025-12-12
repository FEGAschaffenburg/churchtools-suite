-- Migration: Update wp_cts_events table schema
-- Run this SQL in phpMyAdmin or via WordPress database tools

-- Schritt 1: Prüfen, ob die Tabelle existiert und Backup empfohlen
-- WICHTIG: Machen Sie vorher ein Backup Ihrer Datenbank!

-- Schritt 2: Spalten umbenennen und hinzufügen
ALTER TABLE `wp_cts_events` 
  CHANGE COLUMN `external_id` `event_id` varchar(255) NOT NULL,
  ADD COLUMN `appointment_id` varchar(255) DEFAULT NULL AFTER `calendar_id`,
  ADD COLUMN `raw_payload` longtext DEFAULT NULL AFTER `status`;

-- Schritt 3: Index anpassen
ALTER TABLE `wp_cts_events`
  DROP INDEX IF EXISTS `idx_external_id`,
  ADD UNIQUE KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_appointment_id` (`appointment_id`);

-- Schritt 4: Erfolgsmeldung
SELECT 'Migration erfolgreich! Die Tabelle wp_cts_events wurde aktualisiert.' AS status;
