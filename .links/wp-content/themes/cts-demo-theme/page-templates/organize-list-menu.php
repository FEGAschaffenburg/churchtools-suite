<?php
/**
 * Organize List Menu Structure
 * 
 * 1. Remove duplicates
 * 2. Make List pages children of List-Ansichten
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "==============================================\n";
echo "Organizing List Menu Structure\n";
echo "==============================================\n\n";

// Get Hauptmenü
$main_menu = wp_get_nav_menu_object( 7 );

if ( ! $main_menu ) {
	die( "Hauptmenü not found!\n" );
}

// Get List-Ansichten parent page
$parent_page = get_page_by_path( 'list-ansichten', OBJECT, 'page' );

if ( ! $parent_page ) {
	die( "List-Ansichten page not found!\n" );
}

// Get all menu items
$menu_items = wp_get_nav_menu_items( $main_menu->term_id );

// Find List-Ansichten menu item
$parent_menu_item_id = 0;

foreach ( $menu_items as $item ) {
	if ( $item->object_id === $parent_page->ID ) {
		$parent_menu_item_id = $item->ID;
		echo "Found parent: List-Ansichten (Menu Item ID: {$parent_menu_item_id})\n\n";
		break;
	}
}

if ( ! $parent_menu_item_id ) {
	die( "List-Ansichten not found in menu!\n" );
}

// List pages that should be children
$list_pages = [
	527 => 'List Modern',
	584 => 'List Classic',
	585 => 'List Minimal',
	529 => 'List Compact',
	586 => 'List Table',
];

// Group menu items by page ID
$items_by_page = [];

foreach ( $menu_items as $item ) {
	if ( $item->object === 'page' && isset( $list_pages[ $item->object_id ] ) ) {
		if ( ! isset( $items_by_page[ $item->object_id ] ) ) {
			$items_by_page[ $item->object_id ] = [];
		}
		$items_by_page[ $item->object_id ][] = $item;
	}
}

echo "Cleaning up duplicates...\n\n";

$kept_items = [];
$deleted_count = 0;

foreach ( $list_pages as $page_id => $title ) {
	if ( ! isset( $items_by_page[ $page_id ] ) ) {
		echo "⚠️  $title - Not in menu, will add\n";
		
		// Add as child of List-Ansichten
		wp_update_nav_menu_item( $main_menu->term_id, 0, [
			'menu-item-object-id' => $page_id,
			'menu-item-object' => 'page',
			'menu-item-parent-id' => $parent_menu_item_id,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
			'menu-item-title' => $title,
		] );
		
		echo "   ✅ Added as child of List-Ansichten\n";
		continue;
	}
	
	$items = $items_by_page[ $page_id ];
	
	if ( count( $items ) === 1 ) {
		// Keep this item but make it child of List-Ansichten
		$item = $items[0];
		
		wp_update_nav_menu_item( $main_menu->term_id, $item->ID, [
			'menu-item-parent-id' => $parent_menu_item_id,
		] );
		
		echo "✓ $title - Updated to be child of List-Ansichten\n";
		$kept_items[] = $item->ID;
	} else {
		// Multiple instances - keep first, delete rest
		echo "⚠️  $title - Found " . count( $items ) . " instances\n";
		
		$first = array_shift( $items );
		
		// Update first to be child
		wp_update_nav_menu_item( $main_menu->term_id, $first->ID, [
			'menu-item-parent-id' => $parent_menu_item_id,
		] );
		
		echo "   ✓ Kept Menu Item ID: {$first->ID} (as child)\n";
		$kept_items[] = $first->ID;
		
		// Delete duplicates
		foreach ( $items as $duplicate ) {
			wp_delete_post( $duplicate->ID, true );
			echo "   ✗ Deleted duplicate Menu Item ID: {$duplicate->ID}\n";
			$deleted_count++;
		}
	}
}

echo "\n";
echo "==============================================\n";
echo "Summary:\n";
echo "  Kept items: " . count( $kept_items ) . "\n";
echo "  Deleted duplicates: $deleted_count\n";
echo "  All items are now children of List-Ansichten\n";
echo "==============================================\n";
