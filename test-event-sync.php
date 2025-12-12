<?php
/**
 * Test Event Sync Service
 * 
 * Füge diesen Code in eine Debug-Datei ein oder führe ihn in einer Testumgebung aus.
 * ACHTUNG: Nicht in Produktion verwenden!
 * 
 * @package ChurchTools_Suite
 * @since   0.3.7.0
 */

// Nur für Testing!
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Nur für Debug-Umgebungen!');
}

// Load dependencies
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-event-sync-service.php';

// Initialize services
$client = new ChurchTools_Suite_CT_Client();
$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
$events_repo = new ChurchTools_Suite_Events_Repository();
$sync_service = new ChurchTools_Suite_Event_Sync_Service($client, $events_repo, $calendars_repo);

// Test 1: Get selected calendars
echo "<h2>Test 1: Ausgewählte Kalender</h2>\n";
$selected = $calendars_repo->get_selected();
echo "<p>Anzahl ausgewählter Kalender: " . count($selected) . "</p>\n";
foreach ($selected as $calendar) {
    echo "<p>- Kalender {$calendar->calendar_id}: {$calendar->name}</p>\n";
}

// Test 2: Sync events
echo "<h2>Test 2: Events synchronisieren</h2>\n";
echo "<p>Starte Synchronisation...</p>\n";

$result = $sync_service->sync_events([
    'from' => date('Y-m-d', strtotime('-7 days')),
    'to' => date('Y-m-d', strtotime('+90 days')),
]);

if (is_wp_error($result)) {
    echo "<p style='color: red;'>FEHLER: {$result->get_error_message()}</p>\n";
} else {
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    echo "<h3>Statistiken</h3>\n";
    echo "<ul>\n";
    echo "<li>Kalender verarbeitet: {$result['calendars_processed']}</li>\n";
    echo "<li>Events gefunden: {$result['events_found']}</li>\n";
    echo "<li>Appointments gefunden: {$result['appointments_found']}</li>\n";
    echo "<li>Events neu: {$result['events_inserted']}</li>\n";
    echo "<li>Events aktualisiert: {$result['events_updated']}</li>\n";
    echo "<li>Events übersprungen: {$result['events_skipped']}</li>\n";
    echo "<li>Fehler: {$result['errors']}</li>\n";
    echo "</ul>\n";
}

// Test 3: Show synced events
echo "<h2>Test 3: Synchronisierte Events</h2>\n";
$events = $events_repo->get_upcoming(20);
echo "<p>Anzahl kommender Events: " . count($events) . "</p>\n";
echo "<table border='1' cellpadding='5'>\n";
echo "<tr><th>Event ID</th><th>Appointment ID</th><th>Titel</th><th>Start</th><th>Ende</th><th>Kalender</th></tr>\n";
foreach ($events as $event) {
    echo "<tr>";
    echo "<td>{$event->event_id}</td>";
    echo "<td>{$event->appointment_id}</td>";
    echo "<td>{$event->title}</td>";
    echo "<td>{$event->start_datetime}</td>";
    echo "<td>{$event->end_datetime}</td>";
    echo "<td>{$event->calendar_id}</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Test 4: Check last sync time
echo "<h2>Test 4: Letzte Synchronisation</h2>\n";
$last_sync = $sync_service->get_last_sync_time();
if ($last_sync) {
    echo "<p>Letzte Event-Sync: " . date('d.m.Y H:i:s', strtotime($last_sync)) . "</p>\n";
} else {
    echo "<p>Noch keine Event-Synchronisation durchgeführt</p>\n";
}

$calendars_last_sync = get_option('churchtools_suite_calendars_last_sync', null);
if ($calendars_last_sync) {
    echo "<p>Letzte Kalender-Sync: " . date('d.m.Y H:i:s', strtotime($calendars_last_sync)) . "</p>\n";
} else {
    echo "<p>Noch keine Kalender-Synchronisation durchgeführt</p>\n";
}
