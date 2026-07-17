<?php
/**
 * Check List View Pages
 * 
 * Lists all pages with 'list-' prefix to find duplicates
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

$pages = get_posts([
	'post_type' => 'page',
	'post_status' => 'any',
	'numberposts' => 100,
]);

echo "List View Pages:\n\n";

if ( ! $pages ) {
	echo "No pages found\n";
	exit;
}

foreach ( $pages as $p ) {
	if ( strpos( $p->post_name, 'list-' ) === 0 ) {
		echo sprintf(
			"ID: %d | Status: %s | Slug: %s | Title: %s\n",
			$p->ID,
			$p->post_status,
			$p->post_name,
			$p->post_title
		);
	}
}
