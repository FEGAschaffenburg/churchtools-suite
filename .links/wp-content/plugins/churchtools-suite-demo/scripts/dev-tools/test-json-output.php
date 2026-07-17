<?php
/**
 * Test WordPress JSON Output for BOM
 */

// Load WordPress
$wp_load_path = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( 'Error: wp-load.php not found' );
}
require_once $wp_load_path;

// Capture all output
ob_start();

// Call WordPress JSON function
wp_send_json_error( [
	'message' => 'Test message',
] );

// Get buffered output
$output = ob_get_clean();

// Show hex dump
echo "<h2>WordPress JSON Output - First 50 bytes (hex):</h2>\n<pre>";
for ( $i = 0; $i < min( 50, strlen( $output ) ); $i++ ) {
	printf( "%02X ", ord( $output[$i] ) );
	if ( ( $i + 1 ) % 16 === 0 ) {
		echo "\n";
	}
}
echo "</pre>\n\n";

// Check for BOM
if ( strlen( $output ) >= 3 && ord( $output[0] ) === 0xEF && ord( $output[1] ) === 0xBB && ord( $output[2] ) === 0xBF ) {
	echo "<h2 style='color: red;'>⚠️ UTF-8 BOM FOUND!</h2>\n";
	echo "<p>The BOM (Byte Order Mark) is coming from WordPress or a plugin file loaded before JSON output.</p>\n";
} else {
	echo "<h2 style='color: green;'>✅ No BOM detected</h2>\n";
}

// Show raw JSON
echo "<h2>Raw JSON output:</h2>\n<pre>";
echo htmlspecialchars( $output );
echo "</pre>\n";

// Show first char details
if ( isset( $output[0] ) ) {
	echo "<h2>First character analysis:</h2>\n<pre>";
	echo "Character: " . htmlspecialchars( $output[0] ) . "\n";
	echo "Hex: " . sprintf( "%02X", ord( $output[0] ) ) . "\n";
	echo "Expected: 7B (opening brace '{')\n";
	echo "</pre>";
}
