<?php
/**
 * Test general admin access
 */

require_once __DIR__ . '/../../../../../wp-load.php';

echo "=== Test Admin Access ===\n\n";

$demo_user = get_user_by( 'login', 'demo_kn1402' );

if ( ! $demo_user ) {
	echo "❌ User not found\n";
	exit( 1 );
}

wp_set_current_user( $demo_user->ID );

echo "User: {$demo_user->user_login} (ID: {$demo_user->ID})\n";
echo "Roles: " . implode( ', ', $demo_user->roles ) . "\n\n";

echo "Basic WordPress Capabilities:\n";
$basic_caps = [
	'read',
	'edit_posts',
	'publish_posts',
	'delete_posts',
	'upload_files',
	'edit_pages',
	'edit_others_posts',
	'manage_options',
	'manage_categories',
];

foreach ( $basic_caps as $cap ) {
	echo "  " . ( current_user_can( $cap ) ? '✓' : '✗' ) . " {$cap}\n";
}

echo "\nCritical Check:\n";
echo "  read: " . ( current_user_can( 'read' ) ? '✓ YES' : '✗ NO' ) . "\n";

if ( ! current_user_can( 'read' ) ) {
	echo "\n❌ PROBLEM FOUND!\n";
	echo "User does NOT have 'read' capability!\n";
	echo "This is the MINIMUM requirement for wp-admin access.\n";
	echo "WordPress will redirect to login or homepage.\n\n";
} else {
	echo "\n✓ User CAN access wp-admin (has 'read' capability)\n\n";
}

echo "Admin Bar:\n";
echo "  is_user_logged_in(): " . ( is_user_logged_in() ? 'YES' : 'NO' ) . "\n";
echo "  is_admin(): " . ( is_admin() ? 'NO (CLI)' : 'NO' ) . "\n\n";

// Check what WordPress sees
echo "WordPress User Object:\n";
echo "  Exists: " . ( $demo_user->exists() ? 'YES' : 'NO' ) . "\n";
echo "  ID: {$demo_user->ID}\n";
echo "  All capabilities count: " . count( $demo_user->allcaps ) . "\n";

echo "\n=== Check Role Definition ===\n\n";

$role = get_role( 'cts_demo_user' );

if ( ! $role ) {
	echo "❌ Role 'cts_demo_user' does NOT exist!\n";
	echo "This is a CRITICAL error!\n";
	exit( 1 );
}

echo "✓ Role exists\n\n";
echo "Role capabilities:\n";
foreach ( $role->capabilities as $cap => $val ) {
	if ( $val ) {
		echo "  ✓ {$cap}\n";
	}
}

if ( ! isset( $role->capabilities['read'] ) || ! $role->capabilities['read'] ) {
	echo "\n❌ CRITICAL: Role does NOT have 'read' capability!\n";
	echo "This must be added to the role!\n\n";
	
	echo "Fix:\n";
	echo "  \$role->add_cap( 'read' );\n";
}
