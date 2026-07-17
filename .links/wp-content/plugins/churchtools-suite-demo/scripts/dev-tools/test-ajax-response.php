<?php
/**
 * Test AJAX Response for BOM
 * 
 * This script simulates the AJAX registration handler
 * and outputs the raw bytes to check for BOM.
 *  
 * Usage: Place in plugin root and access via browser:
 * https://plugin.feg-aschaffenburg.de/wp-content/plugins/churchtools-suite-demo/test-ajax-response.php
 */

// Load WordPress - go up 6 directories: dev-tools -> scripts -> plugin -> plugins -> wp-content -> web -> wp-load.php
$wp_load_path = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	die( 'Error: wp-load.php not found at: ' . htmlspecialchars( $wp_load_path ) );
}
require_once $wp_load_path;

// Make sure Demo plugin is loaded
if ( ! defined( 'CHURCHTOOLS_SUITE_DEMO_VERSION' ) ) {
	die( 'Error: ChurchTools Suite Demo plugin is not active!' );
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch ANY output before JSON
ob_start();

// Trigger WordPress init to ensure all hooks are registered
do_action('init');

// Simulate AJAX request
$_POST['action'] = 'cts_demo_register';
$_POST['nonce'] = wp_create_nonce( 'cts_demo_register' );
$_POST['email'] = 'tobias@feg.de';
$_POST['first_name'] = 'Test';
$_POST['last_name'] = 'User';
$_POST['password'] = 'testpass123';
$_POST['password_confirm'] = 'testpass123';
$_POST['privacy_accepted'] = '1';

// Call the AJAX handler
do_action( 'wp_ajax_nopriv_cts_demo_register' );

// Get buffered output
$output = ob_get_clean();

// Show raw hex dump of first 50 bytes
echo "<h2>First 50 bytes (hexadecimal):</h2>\n<pre>";
for ( $i = 0; $i < min( 50, strlen( $output ) ); $i++ ) {
	printf( "%02X ", ord( $output[$i] ) );
	if ( ( $i + 1 ) % 16 === 0 ) {
		echo "\n";
	}
}
echo "</pre>\n\n";

// Show raw output
echo "<h2>Raw output:</h2>\n<pre>";
echo htmlspecialchars( substr( $output, 0, 500 ) );
echo "</pre>\n\n";

// Check for BOM
if ( strlen( $output ) >= 3 && ord( $output[0] ) === 0xEF && ord( $output[1] ) === 0xBB && ord( $output[2] ) === 0xBF ) {
	echo "<h2 style='color: red;'>⚠️ UTF-8 BOM FOUND!</h2>\n";
} else {
	echo "<h2 style='color: green;'>✅ No BOM detected</h2>\n";
}

// Show first character
echo "<h2>First character:</h2>\n<pre>";
if ( isset( $output[0] ) ) {
	echo "Character: " . htmlspecialchars( $output[0] ) . "\n";
	echo "Hexadecimal: " . sprintf( "%02X", ord( $output[0] ) ) . "\n";
	echo "Decimal: " . ord( $output[0] ) . "\n";
}
echo "</pre>";
