<?php
/**
 * Check for duplicate appointments in database
 * 
 * Run this via: wp eval-file check-duplicates.php
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

global $wpdb;
$prefix = $wpdb->prefix . 'cts_';
$events_table = $prefix . 'events';

echo "=== Appointment Duplicates Check ===\n\n";

// Check 1: Group by appointment_id - should all be COUNT = 1
echo "1. Checking appointment_id duplicates:\n";
$duplicates = $wpdb->get_results("
	SELECT appointment_id, COUNT(*) as count, 
	       GROUP_CONCAT(id) as row_ids,
	       GROUP_CONCAT(start_datetime) as dates
	FROM {$events_table}
	WHERE appointment_id IS NOT NULL
	GROUP BY appointment_id
	HAVING COUNT(*) > 1
");

if (empty($duplicates)) {
	echo "   ✅ No appointment_id duplicates found\n\n";
} else {
	echo "   ❌ Found " . count($duplicates) . " appointment_id duplicates:\n";
	foreach ($duplicates as $dup) {
		echo "   - appointment_id: {$dup->appointment_id}\n";
		echo "     Count: {$dup->count}\n";
		echo "     Row IDs: {$dup->row_ids}\n";
		echo "     Dates: {$dup->dates}\n\n";
	}
}

// Check 2: Group by event_id - SHOULD have duplicates (recurring events)
echo "2. Checking event_id recurring events:\n";
$recurring = $wpdb->get_results("
	SELECT event_id, COUNT(*) as count,
	       GROUP_CONCAT(appointment_id) as appointment_ids,
	       GROUP_CONCAT(start_datetime) as dates,
	       MIN(title) as title
	FROM {$events_table}
	WHERE event_id IS NOT NULL
	GROUP BY event_id
	HAVING COUNT(*) > 1
	ORDER BY count DESC
	LIMIT 10
");

if (empty($recurring)) {
	echo "   ⚠️  No recurring events found (event_id appears only once each)\n";
	echo "   This indicates the bug is still present!\n\n";
} else {
	echo "   ✅ Found " . count($recurring) . " recurring events:\n";
	foreach ($recurring as $rec) {
		echo "   - event_id: {$rec->event_id}\n";
		echo "     Title: {$rec->title}\n";
		echo "     Instances: {$rec->count}\n";
		echo "     Appointment IDs: {$rec->appointment_ids}\n";
		echo "     Dates: {$rec->dates}\n\n";
	}
}

// Check 3: Show sample events with both event_id AND appointment_id
echo "3. Sample events (first 10 rows):\n";
$samples = $wpdb->get_results("
	SELECT id, event_id, appointment_id, title, 
	       DATE(start_datetime) as date
	FROM {$events_table}
	ORDER BY start_datetime ASC
	LIMIT 10
");

foreach ($samples as $sample) {
	echo "   ID: {$sample->id}\n";
	echo "   Event ID: {$sample->event_id}\n";
	echo "   Appointment ID: {$sample->appointment_id}\n";
	echo "   Title: {$sample->title}\n";
	echo "   Date: {$sample->date}\n";
	echo "   ---\n";
}

// Check 4: Database indexes
echo "\n4. Current indexes on events table:\n";
$indexes = $wpdb->get_results("SHOW INDEX FROM {$events_table}");
foreach ($indexes as $idx) {
	$unique = $idx->Non_unique == 0 ? 'UNIQUE' : 'INDEX';
	echo "   {$unique}: {$idx->Key_name} on column {$idx->Column_name}\n";
}

echo "\n=== End of Check ===\n";
