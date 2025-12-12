-- SQL zum manuellen Updaten der Tabelle (falls bereits erstellt)
-- In phpMyAdmin ausführen oder via WordPress CLI

ALTER TABLE wp_cts_calendars 
  CHANGE COLUMN external_id calendar_id varchar(100) NOT NULL,
  ADD COLUMN name_translated varchar(255) DEFAULT NULL AFTER name,
  ADD COLUMN raw_payload longtext DEFAULT NULL AFTER sort_order;
