<?php
/**
 * Check and Fix List Menu Items
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "==============================================\n";
echo "List Menu Items Check & Fix\n";
echo "==============================================\n\n";

// Find List-Ansichten parent page
$parent = get_page_by_path( 'list-ansichten', OBJECT, 'page' );

if ( ! $parent ) {
	die( "Parent page 'List-Ansichten' not found!\n" );
}

echo "Parent: List-Ansichten (ID: {$parent->ID})\n\n";

// Get all menus
$menus = wp_get_nav_menus();
$main_menu = null;

echo "Available menus:\n";
foreach ( $menus as $menu ) {
	echo "  - {$menu->name} (ID: {$menu->term_id}, Slug: {$menu->slug})\n";
	if ( stripos( $menu->name, 'haupt' ) !== false || stripos( $menu->slug, 'haupt' ) !== false ) {
		$main_menu = $menu;
	}
}
echo "\n";

if ( ! $main_menu ) {
	// Try by ID (7 is Hauptmenü based on previous output)
	$main_menu = wp_get_nav_menu_object( 7 );
}

if ( ! $main_menu ) {
	die( "No menu found!\n" );
}

echo "Using Menu: {$main_menu->name} (ID: {$main_menu->term_id})\n\n";

// Pages that should be in menu
$required_pages = [
	'list-ansichten/list-modern' => 'List Modern',
	'list-ansichten/list-classic' => 'List Classic',
	'list-ansichten/list-minimal' => 'List Minimal',
	'list-ansichten/list-compact' => 'List Compact',
	'list-ansichten/list-table' => 'List Table',
];

// Get current menu items
$menu_items = wp_get_nav_menu_items( $main_menu->term_id );
$existing_page_ids = [];

foreach ( $menu_items as $item ) {
	if ( $item->object === 'page' ) {
		$existing_page_ids[] = $item->object_id;
	}
}

echo "Current menu items (pages only):\n";
foreach ( $menu_items as $item ) {
	if ( $item->object === 'page' ) {
		$page = get_post( $item->object_id );
		if ( $page && strpos( $page->post_name, 'list-' ) !== false ) {
			echo "  ✓ {$item->title} (ID: {$item->object_id}, Slug: {$page->post_name})\n";
		}
	}
}

echo "\n";
echo "Checking required pages...\n\n";

$added_count = 0;
$found_count = 0;

foreach ( $required_pages as $slug => $title ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	
	if ( ! $page ) {
		echo "❌ $title - Page not found (slug: $slug)\n";
		continue;
	}
	
	if ( in_array( $page->ID, $existing_page_ids, true ) ) {
		echo "✓ $title - Already in menu (ID: {$page->ID})\n";
		$found_count++;
		continue;
	}
	
	// Add to menu
	$result = wp_update_nav_menu_item( $main_menu->term_id, 0, [
		'menu-item-object-id' => $page->ID,
		'menu-item-object' => 'page',
		'menu-item-parent-id' => 0,
		'menu-item-type' => 'post_type',
		'menu-item-status' => 'publish',
		'menu-item-title' => $title,
	] );
	
	if ( is_wp_error( $result ) ) {
		echo "❌ $title - Failed to add: " . $result->get_error_message() . "\n";
	} else {
		echo "✅ $title - Added to menu (ID: {$page->ID})\n";
		$added_count++;
	}
}

echo "\n";
echo "==============================================\n";
echo "Summary:\n";
echo "  Already in menu: $found_count\n";
echo "  Newly added: $added_count\n";
echo "==============================================\n";
