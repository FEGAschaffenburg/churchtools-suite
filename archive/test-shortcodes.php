<?php
/**
 * Debug Script - Test Shortcodes
 * 
 * Dieses Script prüft, ob Events in der Datenbank sind und ob die Shortcodes funktionieren.
 * 
 * VERWENDUNG:
 * 1. Datei ins Plugin-Hauptverzeichnis kopieren
 * 2. Im Browser aufrufen: https://your-site.com/wp-content/plugins/churchtools-suite/test-shortcodes.php
 * 3. Nach dem Test wieder löschen (Sicherheit!)
 */

// WordPress laden
require_once '../../../wp-load.php';

// Sicherheitsprüfung
if ( ! current_user_can( 'manage_options' ) ) {
	die( 'Keine Berechtigung!' );
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>ChurchTools Suite - Shortcode Test</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			max-width: 1200px;
			margin: 40px auto;
			padding: 20px;
			background: #f5f5f5;
		}
		.section {
			background: #fff;
			padding: 20px;
			margin-bottom: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		h1 { color: #333; }
		h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
		pre {
			background: #f9f9f9;
			padding: 15px;
			border-radius: 4px;
			overflow-x: auto;
			border-left: 4px solid #667eea;
		}
		.success { color: #00a32a; font-weight: bold; }
		.error { color: #d63638; font-weight: bold; }
		.warning { color: #dba617; font-weight: bold; }
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
		}
		th, td {
			padding: 10px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}
		th {
			background: #667eea;
			color: #fff;
			font-weight: 600;
		}
		.shortcode-output {
			border: 2px solid #667eea;
			border-radius: 8px;
			padding: 20px;
			margin-top: 20px;
			background: #f9fafb;
		}
	</style>
</head>
<body>

<h1>🔍 ChurchTools Suite - Shortcode Debug</h1>

<!-- 1. Database Check -->
<div class="section">
	<h2>1. Datenbank-Prüfung</h2>
	<?php
	global $wpdb;
	$events_table = $wpdb->prefix . 'cts_events';
	$calendars_table = $wpdb->prefix . 'cts_calendars';
	
	// Check if tables exist
	$events_exists = $wpdb->get_var( "SHOW TABLES LIKE '$events_table'" ) === $events_table;
	$calendars_exists = $wpdb->get_var( "SHOW TABLES LIKE '$calendars_table'" ) === $calendars_table;
	
	echo '<p><strong>Tabellen:</strong></p>';
	echo '<ul>';
	echo '<li>wp_cts_events: ' . ( $events_exists ? '<span class="success">✓ Existiert</span>' : '<span class="error">✗ Fehlt</span>' ) . '</li>';
	echo '<li>wp_cts_calendars: ' . ( $calendars_exists ? '<span class="success">✓ Existiert</span>' : '<span class="error">✗ Fehlt</span>' ) . '</li>';
	echo '</ul>';
	
	if ( $events_exists ) {
		$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM $events_table" );
		$upcoming_count = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(*) FROM $events_table WHERE start_datetime >= %s AND status = 'active'",
			current_time( 'mysql' )
		) );
		
		echo '<p><strong>Events:</strong></p>';
		echo '<ul>';
		echo '<li>Gesamt: ' . $event_count . '</li>';
		echo '<li>Anstehend (ab jetzt): ' . $upcoming_count . '</li>';
		echo '</ul>';
		
		// Show sample events
		$sample_events = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, event_id, calendar_id, title, start_datetime, end_datetime 
			FROM $events_table 
			WHERE start_datetime >= %s AND status = 'active'
			ORDER BY start_datetime ASC
			LIMIT 5",
			current_time( 'mysql' )
		) );
		
		if ( $sample_events ) {
			echo '<p><strong>Nächste 5 Termine:</strong></p>';
			echo '<table>';
			echo '<tr><th>ID</th><th>Title</th><th>Kalender</th><th>Start</th></tr>';
			foreach ( $sample_events as $event ) {
				echo '<tr>';
				echo '<td>' . esc_html( $event->id ) . '</td>';
				echo '<td>' . esc_html( $event->title ) . '</td>';
				echo '<td>' . esc_html( $event->calendar_id ) . '</td>';
				echo '<td>' . esc_html( date_i18n( 'd.m.Y H:i', strtotime( $event->start_datetime ) ) ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p class="warning">⚠ Keine anstehenden Events gefunden!</p>';
		}
	}
	
	if ( $calendars_exists ) {
		$calendars = $wpdb->get_results( "SELECT calendar_id, name, is_selected FROM $calendars_table" );
		$selected_count = $wpdb->get_var( "SELECT COUNT(*) FROM $calendars_table WHERE is_selected = 1" );
		
		echo '<p><strong>Kalender:</strong></p>';
		echo '<ul>';
		echo '<li>Gesamt: ' . count( $calendars ) . '</li>';
		echo '<li>Ausgewählt: ' . $selected_count . '</li>';
		echo '</ul>';
		
		if ( $calendars ) {
			echo '<table>';
			echo '<tr><th>ID</th><th>Name</th><th>Ausgewählt</th></tr>';
			foreach ( $calendars as $cal ) {
				echo '<tr>';
				echo '<td>' . esc_html( $cal->calendar_id ) . '</td>';
				echo '<td>' . esc_html( $cal->name ) . '</td>';
				echo '<td>' . ( $cal->is_selected ? '<span class="success">Ja</span>' : 'Nein' ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
	?>
</div>

<!-- 2. Template Data Provider Test -->
<div class="section">
	<h2>2. Template Data Provider Test</h2>
	<?php
	try {
		$data_provider = new ChurchTools_Suite_Template_Data();
		echo '<p class="success">✓ Template Data Provider erfolgreich initialisiert</p>';
		
		// Test get_events()
		$test_events = $data_provider->get_events( [ 'limit' => 3 ] );
		echo '<p><strong>get_events() Test:</strong></p>';
		echo '<ul>';
		echo '<li>Ergebnis: ' . count( $test_events ) . ' Events</li>';
		echo '</ul>';
		
		if ( ! empty( $test_events ) ) {
			echo '<pre>' . print_r( $test_events[0], true ) . '</pre>';
		}
	} catch ( Exception $e ) {
		echo '<p class="error">✗ Fehler: ' . esc_html( $e->getMessage() ) . '</p>';
	}
	?>
</div>

<!-- 3. Shortcode Test -->
<div class="section">
	<h2>3. Shortcode Test</h2>
	
	<h3>Test 1: [cts_list view="classic" limit="3"]</h3>
	<div class="shortcode-output">
		<?php echo do_shortcode( '[cts_list view="classic" limit="3"]' ); ?>
	</div>
	
	<h3>Test 2: [cts_grid view="simple" limit="3" columns="3"]</h3>
	<div class="shortcode-output">
		<?php echo do_shortcode( '[cts_grid view="simple" limit="3" columns="3"]' ); ?>
	</div>
	
	<h3>Test 3: [cts_calendar view="monthly-modern"]</h3>
	<div class="shortcode-output">
		<?php echo do_shortcode( '[cts_calendar view="monthly-modern"]' ); ?>
	</div>
</div>

<!-- 4. Debug Logs -->
<div class="section">
	<h2>4. Debug Logs (wenn WP_DEBUG aktiviert)</h2>
	<?php
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		echo '<p class="success">✓ WP_DEBUG ist aktiviert - Prüfe debug.log Datei</p>';
		echo '<p>Log-Datei: <code>' . WP_CONTENT_DIR . '/debug.log</code></p>';
		
		// Show last 20 lines of debug.log
		$log_file = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $log_file ) ) {
			$lines = file( $log_file );
			$last_lines = array_slice( $lines, -20 );
			echo '<pre>' . esc_html( implode( '', $last_lines ) ) . '</pre>';
		}
	} else {
		echo '<p class="warning">⚠ WP_DEBUG ist deaktiviert</p>';
		echo '<p>Aktiviere WP_DEBUG in wp-config.php für detaillierte Logs:</p>';
		echo '<pre>define( \'WP_DEBUG\', true );
define( \'WP_DEBUG_LOG\', true );
define( \'WP_DEBUG_DISPLAY\', false );</pre>';
	}
	?>
</div>

<div class="section">
	<h2>✅ Checkliste</h2>
	<ol>
		<li>Sind Events in der Datenbank? <?php echo $event_count > 0 ? '<span class="success">✓ Ja</span>' : '<span class="error">✗ Nein - Sync durchführen!</span>'; ?></li>
		<li>Sind anstehende Events vorhanden? <?php echo $upcoming_count > 0 ? '<span class="success">✓ Ja</span>' : '<span class="error">✗ Nein - Alle Events in Vergangenheit!</span>'; ?></li>
		<li>Sind Kalender ausgewählt? <?php echo $selected_count > 0 ? '<span class="success">✓ Ja</span>' : '<span class="warning">⚠ Nein - Aber nicht erforderlich</span>'; ?></li>
		<li>Funktioniert Template Data Provider? <?php echo isset( $data_provider ) ? '<span class="success">✓ Ja</span>' : '<span class="error">✗ Nein</span>'; ?></li>
	</ol>
</div>

<p style="color: #999; font-size: 12px; text-align: center; margin-top: 40px;">
	⚠️ Wichtig: Diese Debug-Datei nach dem Test löschen!
</p>

</body>
</html>
