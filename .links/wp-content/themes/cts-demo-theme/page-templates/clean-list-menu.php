<?php
/**
 * Clean and Fix List Menu
 * 
 * 1. Delete duplicates at bottom (Menu Items 598-602)
 * 2. Add missing List pages as children of List-Ansichten (Menu Item 550)
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "==============================================\n";
echo "Cleaning List Menu Structure\n";
echo "==============================================\n\n";

$parent_menu_item_id = 550; // List-Ansichten menu item

// Delete duplicates at bottom (598-602)
$duplicates = [598, 599, 600, 601, 602];

echo "Step 1: Deleting duplicate menu items...\n";

foreach ( $duplicates as $menu_item_id ) {
	$result = wp_delete_post( $menu_item_id, true );
	if ( $result ) {
		echo "  ✓ Deleted Menu Item ID: $menu_item_id\n";
	} else {
		echo "  ⚠️  Could not delete Menu Item ID: $menu_item_id\n";
	}
}

echo "\nStep 2: Checking required List pages...\n\n";

// Required pages under List-Ansichten
$required_pages = [
	527 => 'List Modern',
	584 => 'List Classic',
	585 => 'List Minimal',
	529 => 'List Compact',
	586 => 'List Table',
];

// Get current menu items
$main_menu = wp_get_nav_menu_object( 7 );
$menu_items = wp_get_nav_menu_items( $main_menu->term_id );

// Find existing children of List-Ansichten
$existing_children = [];

foreach ( $menu_items as $item ) {
	if ( $item->menu_item_parent == $parent_menu_item_id && $item->object === 'page' ) {
		$existing_children[] = $item->object_id;
	}
}

echo "Current children of List-Ansichten:\n";
foreach ( $menu_items as $item ) {
	if ( $item->menu_item_parent == $parent_menu_item_id ) {
		echo "  - {$item->title} (Page ID: {$item->object_id})\n";
	}
}
echo "\n";

// Add missing pages
$added_count = 0;

foreach ( $required_pages as $page_id => $title ) {
	if ( in_array( $page_id, $existing_children, true ) ) {
		echo "✓ $title - Already child of List-Ansichten\n";
		continue;
	}
	
	// Add as child
	$result = wp_update_nav_menu_item( $main_menu->term_id, 0, [
		'menu-item-object-id' => $page_id,
		'menu-item-object' => 'page',
		'menu-item-parent-id' => $parent_menu_item_id,
		'menu-item-type' => 'post_type',
		'menu-item-status' => 'publish',
		'menu-item-title' => $title,
	] );
	
	if ( is_wp_error( $result ) ) {
		echo "❌ $title - Failed: " . $result->get_error_message() . "\n";
	} else {
		echo "✅ $title - Added as child of List-Ansichten\n";
		$added_count++;
	}
}

echo "\n==============================================\n";
echo "Summary:\n";
echo "  Deleted duplicates: " . count( $duplicates ) . "\n";
echo "  Added new items: $added_count\n";
echo "  Total List pages under List-Ansichten: " . count( $required_pages ) . "\n";
echo "==============================================\n";
