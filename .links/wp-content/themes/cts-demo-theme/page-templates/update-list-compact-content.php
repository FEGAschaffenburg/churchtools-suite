<?php
/**
 * Update List Compact Page with Minimal Content
 * 
 * Since list-compact and list-minimal are semantically the same,
 * update list-compact with the minimal template content
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

// Load Gutenberg template from list-minimal
$template_file = __DIR__ . '/list-minimal-gutenberg.html';

if ( ! file_exists( $template_file ) ) {
	die( "Error: Template file not found: $template_file\n" );
}

$content = file_get_contents( $template_file );

if ( $content === false ) {
	die( "Error: Could not read template file\n" );
}

// Replace "List Minimal" with "List Compact" in content for consistency
$content = str_replace( 'List Minimal', 'List Compact', $content );
$content = str_replace( 'list-minimal', 'list-compact', $content );

// Update page
global $wpdb;

$result = $wpdb->update(
	$wpdb->posts,
	[
		'post_content' => $content,
		'post_title' => 'List Compact',
		'post_modified' => current_time( 'mysql' ),
		'post_modified_gmt' => current_time( 'mysql', 1 )
	],
	[
		'ID' => 529
	],
	[ '%s', '%s', '%s', '%s' ],
	[ '%d' ]
);

if ( $result === false ) {
	die( "Error: Database update failed\n" );
}

echo "✅ Successfully updated List Compact page (ID: 529)\n";
echo "Content length: " . strlen( $content ) . " bytes\n";
echo "URL: " . get_permalink( 529 ) . "\n";
echo "\nNote: list-compact and list-minimal now have identical content (both use 'minimal' view)\n";
