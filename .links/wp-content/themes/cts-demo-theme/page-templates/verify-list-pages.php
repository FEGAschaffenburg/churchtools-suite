<?php
/**
 * Verify List View Pages Setup
 * 
 * Checks that all pages have:
 * 1. Escaped shortcodes in code blocks
 * 2. Unescaped shortcodes in live execution
 * 3. Are in the menu
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "==============================================\n";
echo "List View Pages Verification Report\n";
echo "==============================================\n\n";

$pages = [
	'list-ansichten/list-modern' => 'List Modern',
	'list-ansichten/list-classic' => 'List Classic',
	'list-ansichten/list-minimal' => 'List Minimal',
	'list-ansichten/list-compact' => 'List Compact',
	'list-ansichten/list-table' => 'List Table',
];

foreach ( $pages as $slug => $title ) {
	echo "Page: $title\n";
	echo str_repeat( '-', 50 ) . "\n";
	
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	
	if ( ! $page ) {
		echo "❌ Page not found!\n\n";
		continue;
	}
	
	echo "✅ ID: {$page->ID}\n";
	echo "✅ URL: " . get_permalink( $page->ID ) . "\n";
	echo "✅ Status: {$page->post_status}\n";
	
	$content = $page->post_content;
	
	// Check for escaped shortcodes in code blocks
	preg_match_all( '/<code>.*?\[cts_list.*?\].*?<\/code>/s', $content, $unescaped_matches );
	preg_match_all( '/<code>.*?&#91;cts_list.*?&#93;.*?<\/code>/s', $content, $escaped_matches );
	
	$unescaped_in_code = count( $unescaped_matches[0] );
	$escaped_in_code = count( $escaped_matches[0] );
	
	if ( $escaped_in_code > 0 ) {
		echo "✅ Escaped shortcodes in code blocks: $escaped_in_code\n";
	} else {
		echo "⚠️  No escaped shortcodes found in code blocks\n";
	}
	
	// Check for unescaped shortcode in live execution
	preg_match( '/<!-- wp:shortcode -->\s*\[cts_list.*?\]\s*<!-- \/wp:shortcode -->/', $content, $live_matches );
	
	if ( ! empty( $live_matches ) ) {
		echo "✅ Live shortcode execution found (unescaped)\n";
	} else {
		echo "❌ Live shortcode execution NOT found or escaped!\n";
	}
	
	// Check if in menu
	$menus = wp_get_nav_menus();
	$in_menu = false;
	
	foreach ( $menus as $menu ) {
		$menu_items = wp_get_nav_menu_items( $menu->term_id );
		foreach ( $menu_items as $item ) {
			if ( $item->object_id === $page->ID ) {
				$in_menu = true;
				echo "✅ In menu: {$menu->name}\n";
				break 2;
			}
		}
	}
	
	if ( ! $in_menu ) {
		echo "⚠️  NOT in any menu\n";
	}
	
	echo "\n";
}

echo "==============================================\n";
echo "Verification Complete\n";
echo "==============================================\n";
