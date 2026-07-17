<?php
/**
 * Fix List View Pages Duplicates
 * 
 * Removes old list-classic and list-compact pages,
 * renames new pages to correct slugs
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "Fixing List View Pages...\n\n";

// 1. Delete old list-classic page (ID 528)
echo "1. Deleting old list-classic page (ID: 528)...\n";
$result = wp_delete_post( 528, true ); // true = force delete (skip trash)
if ( $result ) {
	echo "   ✅ Deleted\n\n";
} else {
	echo "   ❌ Failed or already deleted\n\n";
}

// 2. Rename list-classic-2 to list-classic (ID 584)
echo "2. Renaming list-classic-2 to list-classic (ID: 584)...\n";
$result = wp_update_post([
	'ID' => 584,
	'post_name' => 'list-classic',
]);
if ( ! is_wp_error( $result ) ) {
	echo "   ✅ Renamed to list-classic\n";
	$page = get_post( 584 );
	echo "   URL: " . get_permalink( 584 ) . "\n\n";
} else {
	echo "   ❌ Failed: " . $result->get_error_message() . "\n\n";
}

// 3. Check list-compact page (ID 529) - keep or update?
echo "3. Checking list-compact page (ID: 529)...\n";
$compact_page = get_post( 529 );
if ( $compact_page ) {
	echo "   Found: {$compact_page->post_title}\n";
	echo "   Status: {$compact_page->post_status}\n";
	echo "   Content length: " . strlen( $compact_page->post_content ) . " bytes\n";
	
	// Check if it's a placeholder or actual content
	if ( strlen( $compact_page->post_content ) < 100 ) {
		echo "   ℹ️  This seems to be a placeholder (short content)\n";
		echo "   Recommend: Rename list-minimal to list-compact OR update this page\n\n";
	} else {
		echo "   ℹ️  This page has content, keeping it\n\n";
	}
}

echo "========================================\n";
echo "Final page structure:\n";
echo "  - list-modern (ID: 527)\n";
echo "  - list-classic (ID: 584) - renamed from list-classic-2\n";
echo "  - list-minimal (ID: 585)\n";
echo "  - list-table (ID: 586)\n";
echo "  - list-compact (ID: 529) - existing, check if needs update\n";
echo "========================================\n";
