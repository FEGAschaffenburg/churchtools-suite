<?php
/**
 * Test Real AJAX Endpoint for BOM
 */

// Make AJAX request from server itself
$url = 'https://plugin.feg-aschaffenburg.de/wp-admin/admin-ajax.php';

// Create a valid nonce (we need WordPress for this)
$wp_load_path = dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-load.php';
require_once $wp_load_path;

// Get valid nonce
$nonce = wp_create_nonce( 'cts_demo_register' );

// Make POST request
$response = wp_remote_post( $url, [
	'body' => [
		'action' => 'cts_demo_register',
		'nonce' => $nonce,
		'email' => 'test@example.com',
		'first_name' => 'Test',
		'last_name' => 'User',
		'password' => 'test123456',
		'password_confirm' => 'test123456',
		'privacy_accepted' => '1',
	],
	'timeout' => 30,
] );

if ( is_wp_error( $response ) ) {
	die( 'Error: ' . $response->get_error_message() );
}

$body = wp_remote_retrieve_body( $response );

// Show hex dump
echo "<h2>AJAX Response - First 50 bytes (hex):</h2>\n<pre>";
for ( $i = 0; $i < min( 50, strlen( $body ) ); $i++ ) {
	printf( "%02X ", ord( $body[$i] ) );
	if ( ( $i + 1 ) % 16 === 0 ) {
		echo "\n";
	}
}
echo "</pre>\n\n";

// Check for BOM
if ( strlen( $body ) >= 3 && ord( $body[0] ) === 0xEF && ord( $body[1] ) === 0xBB && ord( $body[2] ) === 0xBF ) {
	echo "<h2 style='color: red; font-size: 32px;'>⚠️ UTF-8 BOM FOUND!</h2>\n";
	echo "<p style='font-size: 18px;'>The BOM (EF BB BF) is present at the start of the AJAX response.</p>\n";
} else {
	echo "<h2 style='color: green;'>✅ No BOM detected</h2>\n";
	echo "<p>First byte: " . sprintf( "%02X", ord( $body[0] ) ) . " (expected: 7B for '{')</p>\n";
}

// Show raw response
echo "<h2>Full Response:</h2>\n<pre>";
echo htmlspecialchars( substr( $body, 0, 1000 ) );
echo "</pre>\n";

// Decode JSON
echo "<h2>JSON Decode Test:</h2>\n<pre>";
$json = json_decode( $body, true );
if ( json_last_error() === JSON_ERROR_NONE ) {
	echo "✅ JSON is valid!\n";
	print_r( $json );
} else {
	echo "❌ JSON is INVALID!\n";
	echo "Error: " . json_last_error_msg() . "\n";
}
echo "</pre>";
