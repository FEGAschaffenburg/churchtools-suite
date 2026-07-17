<?php
/**
 * Fix existing demo users - Update their role capabilities
 */

define('WP_USE_THEMES', false);
require_once ('../../../../../wp-load.php');

echo "=== Fixing Demo User Role ===" . PHP_EOL . PHP_EOL;

// Get the role
$role = get_role('cts_demo_user');

if (!$role) {
	echo "❌ Role cts_demo_user does not exist!" . PHP_EOL;
	echo "Creating role now..." . PHP_EOL;
	
	add_role(
		'cts_demo_user',
		'CTS Demo User',
		[
			'read'                              => true,
			'edit_cts_demo_pages'               => true,
			'edit_cts_demo_page'                => true,
			'read_cts_demo_page'                => true,
			'delete_cts_demo_page'              => true,
			'delete_cts_demo_pages'             => true,
			'publish_cts_demo_pages'            => true,
			'edit_published_cts_demo_pages'     => true,
			'delete_published_cts_demo_pages'   => true,
			'create_cts_demo_pages'             => true,
		]
	);
	
	echo "✓ Role created!" . PHP_EOL;
	$role = get_role('cts_demo_user');
}

// Show role capabilities
echo "Role capabilities:" . PHP_EOL;
foreach ($role->capabilities as $cap => $value) {
	echo "  " . ($value ? "✓" : "❌") . " $cap" . PHP_EOL;
}

// Find all users with cts_demo_user role
$users = get_users([
	'role' => 'cts_demo_user',
	'fields' => ['ID', 'user_login']
]);

echo PHP_EOL . "Found " . count($users) . " demo users" . PHP_EOL . PHP_EOL;

foreach ($users as $user_data) {
	$user = new WP_User($user_data->ID);
	
	echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
	
	// Check current capabilities
	$has_caps = [
		'read' => $user->has_cap('read'),
		'edit_cts_demo_pages' => $user->has_cap('edit_cts_demo_pages'),
		'publish_cts_demo_pages' => $user->has_cap('publish_cts_demo_pages'),
	];
	
	echo "  Current caps:" . PHP_EOL;
	foreach ($has_caps as $cap => $value) {
		echo "    " . ($value ? "✓" : "❌") . " $cap" . PHP_EOL;
	}
	
	// Refresh user meta (forces WordPress to reload capabilities from role)
	wp_cache_delete($user->ID, 'users');
	wp_cache_delete($user->ID, 'user_meta');
	clean_user_cache($user->ID);
	
	// Force role update
	$user->remove_role('cts_demo_user');
	$user->add_role('cts_demo_user');
	
	// Reload user
	$user = new WP_User($user->ID);
	
	// Check new capabilities
	$new_caps = [
		'read' => $user->has_cap('read'),
		'edit_cts_demo_pages' => $user->has_cap('edit_cts_demo_pages'),
		'publish_cts_demo_pages' => $user->has_cap('publish_cts_demo_pages'),
	];
	
	echo "  After refresh:" . PHP_EOL;
	foreach ($new_caps as $cap => $value) {
		echo "    " . ($value ? "✓" : "❌") . " $cap" . PHP_EOL;
	}
	
	echo "  ✓ User updated!" . PHP_EOL . PHP_EOL;
}

echo "Done." . PHP_EOL;
