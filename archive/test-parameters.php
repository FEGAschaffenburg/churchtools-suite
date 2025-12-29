<?php
/**
 * Test Parameter Debug Script
 * 
 * Füge diesen Code oben in classic.php ein (nach dem ABSPATH-Check):
 */

// DEBUG: Parameter anzeigen
if ( isset( $args ) ) {
	echo '<div style="background: #ffeb3b; padding: 10px; margin: 10px 0; border: 2px solid #f57c00;">';
	echo '<strong>DEBUG - Template Parameters:</strong><br>';
	echo 'show_description: ';
	var_dump( $args['show_description'] ?? 'NOT SET' );
	echo '<br>';
	echo 'show_location: ';
	var_dump( $args['show_location'] ?? 'NOT SET' );
	echo '<br>';
	echo 'Type von show_description: ' . gettype( $args['show_description'] ?? null );
	echo '<br>';
	echo 'Is false? ' . ( ( $args['show_description'] ?? null ) === false ? 'JA' : 'NEIN' );
	echo '<br>';
	echo 'Is "false"? ' . ( ( $args['show_description'] ?? null ) === 'false' ? 'JA' : 'NEIN' );
	echo '<br>';
	echo 'Is empty? ' . ( empty( $args['show_description'] ?? null ) ? 'JA' : 'NEIN' );
	echo '</div>';
}
