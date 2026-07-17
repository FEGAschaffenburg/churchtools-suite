<?php
/**
 * Remove Exact Duplicates from Menu
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

echo "Removing duplicate menu items...\n\n";

// Delete exact duplicates
$duplicates = [
	603, // List Modern (duplicate of 554)
	606, // List Compact (duplicate of 556)
];

foreach ( $duplicates as $menu_item_id ) {
	$result = wp_delete_post( $menu_item_id, true );
	if ( $result ) {
		echo "✓ Deleted Menu Item ID: $menu_item_id\n";
	} else {
		echo "⚠️  Could not delete Menu Item ID: $menu_item_id\n";
	}
}

echo "\n✅ Done! Final structure should have:\n";
echo "  - List Modern (554)\n";
echo "  - List Compact (556)\n";
echo "  - List Classic (604)\n";
echo "  - List Minimal (605)\n";
echo "  - List Table (607)\n";
