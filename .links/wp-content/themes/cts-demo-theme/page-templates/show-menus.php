<?php
/**
 * Show Menu Structure
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

$menus = wp_get_nav_menus();

echo "WordPress Menus:\n\n";

foreach ( $menus as $menu ) {
	echo "Menu: {$menu->name} (ID: {$menu->term_id})\n";
	echo str_repeat( '-', 50 ) . "\n";
	
	$items = wp_get_nav_menu_items( $menu->term_id );
	
	if ( empty( $items ) ) {
		echo "  (empty)\n";
	} else {
		foreach ( $items as $item ) {
			echo "  - {$item->title} (Page ID: {$item->object_id})\n";
		}
	}
	
	echo "\n";
}
