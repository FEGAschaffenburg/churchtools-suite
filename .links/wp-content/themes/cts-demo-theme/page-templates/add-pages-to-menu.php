<?php
/**
 * Add List View Pages to Menu
 * 
 * Ensures all list view pages are visible in the documentation menu
 * 
 * Usage: php add-pages-to-menu.php
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

echo "Adding List View Pages to Menu...\n\n";

// Get or create menu
$menu_name = 'Documentation Menu';
$menu = wp_get_nav_menu_object( $menu_name );

if ( ! $menu ) {
	// Try to find any menu
	$menus = wp_get_nav_menus();
	if ( ! empty( $menus ) ) {
		$menu = $menus[0];
		echo "Using existing menu: {$menu->name} (ID: {$menu->term_id})\n\n";
	} else {
		echo "No menu found. Creating new menu: $menu_name\n";
		$menu_id = wp_create_nav_menu( $menu_name );
		$menu = wp_get_nav_menu_object( $menu_id );
	}
}

$menu_id = $menu->term_id;

// Pages to add (in order)
$pages = [
	'list-ansichten/list-modern' => 'List Modern',
	'list-ansichten/list-classic' => 'List Classic',
	'list-ansichten/list-minimal' => 'List Minimal',
	'list-ansichten/list-compact' => 'List Compact',
	'list-ansichten/list-table' => 'List Table',
];

$added_count = 0;
$skipped_count = 0;

foreach ( $pages as $slug => $title ) {
	// Get page by path
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	
	if ( ! $page ) {
		echo "⚠️  Page '$slug' not found - skipping\n";
		$skipped_count++;
		continue;
	}
	
	// Check if already in menu
	$menu_items = wp_get_nav_menu_items( $menu_id );
	$already_exists = false;
	
	foreach ( $menu_items as $item ) {
		if ( $item->object_id === $page->ID ) {
			$already_exists = true;
			break;
		}
	}
	
	if ( $already_exists ) {
		echo "✓  $title - Already in menu\n";
		$skipped_count++;
		continue;
	}
	
	// Add to menu
	$menu_item_data = [
		'menu-item-object-id' => $page->ID,
		'menu-item-object' => 'page',
		'menu-item-type' => 'post_type',
		'menu-item-status' => 'publish',
		'menu-item-title' => $title,
	];
	
	$result = wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );
	
	if ( is_wp_error( $result ) ) {
		echo "❌ Failed to add $title: " . $result->get_error_message() . "\n";
		continue;
	}
	
	echo "✅ Added $title to menu\n";
	$added_count++;
}

echo "\n========================================\n";
echo "Summary:\n";
echo "  Menu: {$menu->name} (ID: $menu_id)\n";
echo "  Added: $added_count pages\n";
echo "  Skipped: $skipped_count pages\n";
echo "========================================\n";
