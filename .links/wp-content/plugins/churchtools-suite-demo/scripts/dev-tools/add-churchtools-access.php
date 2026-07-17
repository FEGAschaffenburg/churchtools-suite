<?php
/**
 * Add ChurchTools menu capability to demo_tester role and existing users
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Adding ChurchTools Menu Access ===" . PHP_EOL . PHP_EOL;

// Get demo_tester role
$role = get_role('demo_tester');

if (!$role) {
	echo "❌ demo_tester role not found!" . PHP_EOL;
	exit(1);
}

// Add capability
$had_cap_before = $role->has_cap('manage_churchtools_suite');
$role->add_cap('manage_churchtools_suite');

echo "Role: demo_tester" . PHP_EOL;
echo "  Had capability before: " . ($had_cap_before ? "YES" : "NO") . PHP_EOL;
echo "  ✓ Added 'manage_churchtools_suite' capability" . PHP_EOL . PHP_EOL;

// Count current capabilities
$capabilities = array_keys(array_filter($role->capabilities));
echo "Total capabilities: " . count($capabilities) . PHP_EOL;
echo "  (was 16, now should be 17)" . PHP_EOL . PHP_EOL;

// Refresh all demo users
$args = array(
	'role' => 'demo_tester',
	'fields' => 'all',
);
$demo_users = get_users($args);

echo "Refreshing " . count($demo_users) . " demo user(s):" . PHP_EOL;
foreach ($demo_users as $user) {
	// Clear user cache to pick up new capabilities
	wp_cache_delete($user->ID, 'users');
	wp_cache_delete($user->ID, 'user_meta');
	
	// Force refresh
	$user = new WP_User($user->ID);
	
	$can_access = $user->has_cap('manage_churchtools_suite');
	echo "  - {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
	echo "    Can access ChurchTools menu: " . ($can_access ? "✓ YES" : "✗ NO") . PHP_EOL;
}

echo PHP_EOL . "Verification:" . PHP_EOL;
wp_set_current_user(13);
$test_user = wp_get_current_user();
echo "  Current user: {$test_user->user_login}" . PHP_EOL;
echo "  Can manage ChurchTools: " . (current_user_can('manage_churchtools_suite') ? "✓ YES" : "✗ NO") . PHP_EOL;
echo "  Can edit demo pages: " . (current_user_can('edit_cts_demo_pages') ? "✓ YES" : "✗ NO") . PHP_EOL;

echo PHP_EOL . "✅ Done! ChurchTools menu will now be visible for demo users." . PHP_EOL;
