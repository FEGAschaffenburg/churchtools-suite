<?php
/**
 * Template Attributes Debug
 * 
 * Füge diesen Code TEMPORÄR in templates/list/classic.php ein (Zeile 28, nach $show_tags)
 */

// DEBUG: Zeige alle übergebenen Argumente
if ( WP_DEBUG || ( isset( $_GET['cts_debug'] ) && current_user_can( 'manage_options' ) ) ) {
	echo '<div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; font-family: monospace; font-size: 12px;">';
	echo '<h3 style="margin-top:0;">🔍 ChurchTools Suite - Template Debug</h3>';
	
	echo '<h4>Übergebene Argumente ($args):</h4>';
	echo '<pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">';
	print_r( $args );
	echo '</pre>';
	
	echo '<h4>Geparste Toggle-Werte:</h4>';
	echo '<table style="width:100%; border-collapse: collapse;">';
	echo '<tr><th style="text-align:left; border-bottom: 1px solid #ddd;">Parameter</th><th style="text-align:left; border-bottom: 1px solid #ddd;">Wert</th><th style="text-align:left; border-bottom: 1px solid #ddd;">Typ</th></tr>';
	
	$toggles = [
		'show_event_description' => $show_event_description,
		'show_appointment_description' => $show_appointment_description,
		'show_services' => $show_services,
		'show_location' => $show_location,
		'show_calendar_name' => $show_calendar_name,
		'show_time' => $show_time,
		'show_tags' => $show_tags,
	];
	
	foreach ( $toggles as $name => $value ) {
		$display_value = $value ? '✅ TRUE' : '❌ FALSE';
		$type = gettype( $value );
		$color = $value ? '#d4edda' : '#f8d7da';
		echo '<tr style="background: ' . $color . ';">';
		echo '<td style="padding: 5px;">' . $name . '</td>';
		echo '<td style="padding: 5px;"><strong>' . $display_value . '</strong></td>';
		echo '<td style="padding: 5px;">' . $type . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	echo '<h4>Erstes Event (Beispiel):</h4>';
	if ( ! empty( $events ) ) {
		$first_event = $events[0];
		echo '<pre style="background: #f5f5f5; padding: 10px; overflow-x: auto; max-height: 300px;">';
		echo 'Titel: ' . ( $first_event['title'] ?? 'FEHLT' ) . "\n";
		echo 'Event-Beschreibung: ' . ( ! empty( $first_event['event_description'] ) ? substr( $first_event['event_description'], 0, 100 ) . '...' : 'LEER' ) . "\n";
		echo 'Termin-Beschreibung: ' . ( ! empty( $first_event['appointment_description'] ) ? substr( $first_event['appointment_description'], 0, 100 ) . '...' : 'LEER' ) . "\n";
		echo 'Services: ' . ( ! empty( $first_event['services'] ) ? count( $first_event['services'] ) . ' gefunden' : 'LEER' ) . "\n";
		echo 'Location: ' . ( $first_event['location_name'] ?? 'FEHLT' ) . "\n";
		echo 'Tags: ' . ( ! empty( $first_event['tags_array'] ) ? count( $first_event['tags_array'] ) . ' gefunden' : 'LEER' ) . "\n";
		echo '</pre>';
	} else {
		echo '<p style="color: red;">KEINE EVENTS GEFUNDEN!</p>';
	}
	
	echo '<p><small>💡 Tipp: Deaktiviere WP_DEBUG in wp-config.php oder entferne ?cts_debug=1 aus der URL um diese Box zu verstecken</small></p>';
	echo '</div>';
}
?>
