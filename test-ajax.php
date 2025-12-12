<?php
/**
 * AJAX Test Script
 * Temporär - NUR ZUM DEBUGGEN
 * 
 * Aufruf in Browser: http://deine-site.local/wp-content/plugins/churchtools-suite/test-ajax.php
 */

// WordPress laden
require_once '../../../wp-load.php';

// Check ob eingeloggt
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Nicht eingeloggt oder keine Berechtigung');
}

echo "<h1>AJAX Test</h1>";

// Test 1: Prüfe ob AJAX Actions registriert sind
echo "<h2>1. Registrierte AJAX Actions</h2>";
global $wp_filter;
echo "<pre>";
echo "wp_ajax_cts_test_connection: ";
var_dump(isset($wp_filter['wp_ajax_cts_test_connection']));
echo "\nwp_ajax_cts_sync_calendars: ";
var_dump(isset($wp_filter['wp_ajax_cts_sync_calendars']));
echo "\nwp_ajax_cts_save_calendar_selection: ";
var_dump(isset($wp_filter['wp_ajax_cts_save_calendar_selection']));
echo "</pre>";

// Test 2: Teste Kalender-Sync direkt
echo "<h2>2. Teste Kalender-Sync direkt</h2>";
try {
    require_once __DIR__ . '/includes/class-churchtools-suite-ct-client.php';
    require_once __DIR__ . '/includes/repositories/class-churchtools-suite-repository-base.php';
    require_once __DIR__ . '/includes/repositories/class-churchtools-suite-calendars-repository.php';
    require_once __DIR__ . '/includes/services/class-churchtools-suite-calendar-sync-service.php';
    
    $client = new ChurchTools_Suite_CT_Client();
    $calendars_repo = new ChurchTools_Suite_Calendars_Repository();
    $sync_service = new ChurchTools_Suite_Calendar_Sync_Service($client, $calendars_repo);
    
    echo "<pre>";
    echo "Classes loaded successfully\n";
    echo "Repository Table: " . $calendars_repo->get_table_name() . "\n";
    
    // Test API Call
    echo "\nTesting API call...\n";
    $result = $sync_service->sync_calendars();
    
    if (is_wp_error($result)) {
        echo "ERROR: " . $result->get_error_message() . "\n";
        echo "Error Data: ";
        var_dump($result->get_error_data());
    } else {
        echo "SUCCESS!\n";
        print_r($result);
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}

// Test 3: Prüfe Tabelle
echo "<h2>3. Prüfe Datenbank-Tabelle</h2>";
global $wpdb;
$table_name = $wpdb->prefix . 'cts_calendars';
echo "<pre>";
echo "Table name: $table_name\n";
$exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
echo "Table exists: " . ($exists ? 'YES' : 'NO') . "\n";

if ($exists) {
    echo "\nTable structure:\n";
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    foreach ($columns as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
    
    echo "\nRow count: " . $wpdb->get_var("SELECT COUNT(*) FROM $table_name") . "\n";
}
echo "</pre>";
