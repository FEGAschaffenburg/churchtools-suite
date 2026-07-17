<?php
/**
 * Update List Table Page Content
 * 
 * Loads content from list-table-gutenberg.html and updates the List Table page
 * 
 * Usage: php update-list-table-content.php
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

// Load WordPress
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

// Load Gutenberg template
$template_file = __DIR__ . '/list-table-gutenberg.html';

if ( ! file_exists( $template_file ) ) {
	die( "Error: Template file not found: $template_file\n" );
}

$content = file_get_contents( $template_file );

if ( $content === false ) {
	die( "Error: Could not read template file\n" );
}

// Verify template structure (basic check)
if ( strpos( $content, '<!-- wp:' ) === false ) {
	die( "Error: Template does not contain Gutenberg blocks\n" );
}

// Update WordPress page directly via database
global $wpdb;

$result = $wpdb->update(
	$wpdb->posts,
	[
		'post_content' => $content,
		'post_title' => 'List Table', // Ensure title persists
		'post_modified' => current_time( 'mysql' ),
		'post_modified_gmt' => current_time( 'mysql', 1 )
	],
	[
		'post_name' => 'list-table',
		'post_type' => 'page',
		'post_status' => 'publish'
	],
	[ '%s', '%s', '%s', '%s' ],
	[ '%s', '%s', '%s' ]
);

if ( $result === false ) {
	die( "Error: Database update failed\n" );
}

if ( $result === 0 ) {
	// Check if page exists
	$page = get_page_by_path( 'list-table', OBJECT, 'page' );
	if ( ! $page ) {
		die( "Error: Page 'list-table' not found. Please create it first.\n" );
	}
	echo "Warning: Page exists but no changes were made (content may be identical)\n";
	echo "Page ID: {$page->ID}\n";
} else {
	// Get page ID
	$page = get_page_by_path( 'list-table', OBJECT, 'page' );
	echo "✅ Successfully updated List Table page (ID: {$page->ID})\n";
	echo "Content length: " . strlen( $content ) . " bytes\n";
	echo "URL: " . get_permalink( $page->ID ) . "\n";
}
