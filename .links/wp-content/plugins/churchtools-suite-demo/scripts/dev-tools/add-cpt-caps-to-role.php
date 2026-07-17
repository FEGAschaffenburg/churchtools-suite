<?php
/**
 * Add CPT-specific capabilities to demo_tester role
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Adding CPT Capabilities to demo_tester Role ===" . PHP_EOL . PHP_EOL;

$role = get_role('demo_tester');

if (!$role) {
	echo "❌ demo_tester role not found!" . PHP_EOL;
	exit(1);
}

echo "Current capabilities: " . count($role->capabilities) . PHP_EOL . PHP_EOL;

// Add all cts_demo_page capabilities
$cpt_caps = [
	'edit_cts_demo_pages',
	'edit_others_cts_demo_pages',
	'publish_cts_demo_pages',
	'read_private_cts_demo_pages',
	'delete_cts_demo_pages',
	'delete_private_cts_demo_pages',
	'delete_published_cts_demo_pages',
	'delete_others_cts_demo_pages',
	'edit_private_cts_demo_pages',
	'edit_published_cts_demo_pages',
	'edit_cts_demo_page',
	'read_cts_demo_page',
	'delete_cts_demo_page',
	'manage_cts_demo_pages',
];

echo "Adding CPT capabilities..." . PHP_EOL;
foreach ($cpt_caps as $cap) {
	$role->add_cap($cap);
	echo "  ✓ $cap" . PHP_EOL;
}

// Reload role
$role = get_role('demo_tester');
echo PHP_EOL . "New total: " . count($role->capabilities) . " capabilities" . PHP_EOL . PHP_EOL;

// Check important ones
$check = ['read', 'edit_posts', 'edit_cts_demo_pages', 'publish_cts_demo_pages', 'delete_posts'];
echo "Important capabilities:" . PHP_EOL;
foreach ($check as $cap) {
	$has = isset($role->capabilities[$cap]) && $role->capabilities[$cap];
	echo "  " . ($has ? "✓" : "❌") . " $cap" . PHP_EOL;
}

// Refresh all demo users
echo PHP_EOL . "Refreshing demo users..." . PHP_EOL;
$users = get_users(['role' => 'demo_tester']);
foreach ($users as $user) {
	wp_cache_delete($user->ID, 'users');
	wp_cache_delete($user->ID, 'user_meta');
	clean_user_cache($user->ID);
	echo "  ✓ {$user->user_login}" . PHP_EOL;
}

echo PHP_EOL . "Done! Test creating a demo page now." . PHP_EOL;
