<?php
/**
 * Debug script to test Services loading
 * 
 * Upload this file to WordPress root and access via:
 * https://your-site.com/wp-content/plugins/churchtools-suite/test-services-debug.php
 */

// Load WordPress
require_once '../../../wp-load.php';

// Load repositories
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Services Debug</title>
	<style>
		body { font-family: monospace; padding: 20px; background: #f5f5f5; }
		.section { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		h2 { margin-top: 0; color: #667eea; }
		pre { background: #f9f9f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
		.success { color: #00a32a; font-weight: bold; }
		.error { color: #d63638; font-weight: bold; }
		.info { color: #2271b1; font-weight: bold; }
	</style>
</head>
<body>
	<h1>🔍 ChurchTools Suite - Services Debug</h1>
	
	<div class="section">
		<h2>1️⃣ Database Check</h2>
		<?php
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'event_services';
		
		// Count services
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		echo "<p class='info'>Services in database: {$count}</p>";
		
		// Show latest services
		$services = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 10", ARRAY_A );
		echo "<h3>Latest 10 Services:</h3>";
		echo "<pre>" . print_r( $services, true ) . "</pre>";
		?>
	</div>
	
	<div class="section">
		<h2>2️⃣ Events with Services</h2>
		<?php
		$events_repo = new ChurchTools_Suite_Events_Repository();
		$event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
		
		// Get first 5 events
		$events = $wpdb->get_results( 
			"SELECT * FROM {$wpdb->prefix}" . CHURCHTOOLS_SUITE_DB_PREFIX . "events 
			ORDER BY start_datetime ASC 
			LIMIT 5",
			ARRAY_A 
		);
		
		echo "<p class='info'>Checking first 5 events...</p>";
		
		foreach ( $events as $event ) {
			echo "<h3>Event: " . esc_html( $event['title'] ) . " (ID: {$event['id']})</h3>";
			
			// Get services for this event
			$services = $event_services_repo->get_for_event( $event['id'] );
			
			if ( empty( $services ) ) {
				echo "<p class='error'>No services found</p>";
			} else {
				echo "<p class='success'>Found " . count( $services ) . " services:</p>";
				echo "<pre>" . print_r( $services, true ) . "</pre>";
			}
		}
		?>
	</div>
	
	<div class="section">
		<h2>3️⃣ Template Data Provider Test</h2>
		<?php
		$data_provider = new ChurchTools_Suite_Template_Data();
		
		// Get events
		$formatted_events = $data_provider->get_events( [
			'limit' => 5,
		] );
		
		echo "<p class='info'>Loaded " . count( $formatted_events ) . " events via Template Data Provider</p>";
		
		foreach ( $formatted_events as $event ) {
			echo "<h3>Event: " . esc_html( $event['title'] ) . "</h3>";
			echo "<p>Services count: {$event['services_count']}</p>";
			
			if ( empty( $event['services'] ) ) {
				echo "<p class='error'>No services in formatted event</p>";
			} else {
				echo "<p class='success'>Services found:</p>";
				echo "<pre>" . print_r( $event['services'], true ) . "</pre>";
			}
		}
		?>
	</div>
	
	<div class="section">
		<h2>4️⃣ Shortcode Test</h2>
		<?php
		echo "<h3>Output of [cts_list view=\"classic\" limit=\"5\" show_services=\"true\"]:</h3>";
		echo "<div style='background: #fff; padding: 20px; border: 2px solid #667eea; border-radius: 8px;'>";
		echo do_shortcode( '[cts_list view="classic" limit="5" show_services="true"]' );
		echo "</div>";
		?>
	</div>
	
</body>
</html>
