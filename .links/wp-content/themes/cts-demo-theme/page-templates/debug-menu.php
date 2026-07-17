<?php
/**
 * Debug Menu Structure
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

$main_menu = wp_get_nav_menu_object( 7 );
$menu_items = wp_get_nav_menu_items( $main_menu->term_id );

echo "Hauptmenü Items (detailed):\n\n";

foreach ( $menu_items as $item ) {
	echo "Menu Item ID: {$item->ID}\n";
	echo "  Title: {$item->title}\n";
	echo "  Type: {$item->type} / Object: {$item->object}\n";
	echo "  Object ID: {$item->object_id}\n";
	echo "  Parent ID: {$item->menu_item_parent}\n";
	
	if ( $item->object === 'page' ) {
		$page = get_post( $item->object_id );
		if ( $page ) {
			echo "  Page Slug: {$page->post_name}\n";
		}
	}
	
	echo "\n";
}

// Find List-Ansichten
echo "\nSearching for List-Ansichten:\n";
$parent_page = get_page_by_path( 'list-ansichten', OBJECT, 'page' );

if ( $parent_page ) {
	echo "Page found: ID {$parent_page->ID}, Status: {$parent_page->post_status}\n";
	
	foreach ( $menu_items as $item ) {
		if ( $item->object_id == $parent_page->ID ) {
			echo "✓ Found in menu: Menu Item ID {$item->ID}\n";
		}
	}
}
