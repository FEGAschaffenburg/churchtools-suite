<?php
/**
 * Create List View Pages
 * 
 * Creates the three list view documentation pages:
 * - List Classic
 * - List Minimal
 * - List Table
 * 
 * Usage: php create-list-view-pages.php
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

// Load WordPress
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

// Helper function to create page
function create_list_view_page( $slug, $title, $template_file ) {
	// Check if page exists
	$existing_page = get_page_by_path( $slug, OBJECT, 'page' );
	
	if ( $existing_page ) {
		echo "Page '$slug' already exists (ID: {$existing_page->ID})\n";
		return $existing_page->ID;
	}
	
	// Load content from template
	if ( ! file_exists( $template_file ) ) {
		echo "Error: Template file not found: $template_file\n";
		return false;
	}
	
	$content = file_get_contents( $template_file );
	
	if ( $content === false ) {
		echo "Error: Could not read template file\n";
		return false;
	}
	
	// Find parent page (List-Ansichten)
	$parent = get_page_by_path( 'list-ansichten', OBJECT, 'page' );
	$parent_id = $parent ? $parent->ID : 0;
	
	// Create page
	$page_data = [
		'post_title' => $title,
		'post_name' => $slug,
		'post_content' => $content,
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_parent' => $parent_id,
		'post_author' => 1,
		'menu_order' => 0,
	];
	
	$page_id = wp_insert_post( $page_data, true );
	
	if ( is_wp_error( $page_id ) ) {
		echo "Error creating page '$slug': " . $page_id->get_error_message() . "\n";
		return false;
	}
	
	echo "✅ Created page '$slug' (ID: $page_id)\n";
	echo "   Title: $title\n";
	echo "   Parent: " . ( $parent ? "List-Ansichten (ID: $parent_id)" : "None" ) . "\n";
	echo "   URL: " . get_permalink( $page_id ) . "\n";
	echo "   Content length: " . strlen( $content ) . " bytes\n\n";
	
	return $page_id;
}

// Create pages
echo "Creating List View Pages...\n\n";

$pages = [
	[
		'slug' => 'list-classic',
		'title' => 'List Classic',
		'template' => __DIR__ . '/list-classic-gutenberg.html',
	],
	[
		'slug' => 'list-minimal',
		'title' => 'List Minimal',
		'template' => __DIR__ . '/list-minimal-gutenberg.html',
	],
	[
		'slug' => 'list-table',
		'title' => 'List Table',
		'template' => __DIR__ . '/list-table-gutenberg.html',
	],
];

$created_count = 0;
$skipped_count = 0;

foreach ( $pages as $page ) {
	$result = create_list_view_page( $page['slug'], $page['title'], $page['template'] );
	
	if ( $result === false ) {
		continue;
	}
	
	if ( is_numeric( $result ) && get_post_status( $result ) === 'publish' ) {
		$created_count++;
	} else {
		$skipped_count++;
	}
}

echo "\n========================================\n";
echo "Summary:\n";
echo "  Created: $created_count pages\n";
echo "  Skipped: $skipped_count pages\n";
echo "========================================\n";
