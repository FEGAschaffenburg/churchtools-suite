<?php
/**
 * Update cts_demo_user role - add ALL editor capabilities
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Updating cts_demo_user Role ===" . PHP_EOL . PHP_EOL;

// Get editor role
$editor = get_role('editor');

if (!$editor) {
	echo "❌ Editor role not found!" . PHP_EOL;
	exit(1);
}

echo "Editor has " . count($editor->capabilities) . " capabilities" . PHP_EOL;

// Get cts_demo_user role
$role = get_role('cts_demo_user');

if (!$role) {
	echo "❌ cts_demo_user role not found!" . PHP_EOL;
	exit(1);
}

echo "cts_demo_user has " . count($role->capabilities) . " capabilities (before update)" . PHP_EOL . PHP_EOL;

// Add ALL editor capabilities
echo "Adding all editor capabilities..." . PHP_EOL;
$added = 0;
foreach ($editor->capabilities as $cap => $value) {
	if ($value) {
		$role->add_cap($cap);
		$added++;
	}
}

echo "✓ Added/updated $added capabilities" . PHP_EOL . PHP_EOL;

// Reload role
$role = get_role('cts_demo_user');
echo "cts_demo_user now has " . count($role->capabilities) . " capabilities" . PHP_EOL . PHP_EOL;

// Show important capabilities
$important = ['read', 'edit_posts', 'edit_pages', 'publish_posts', 'delete_posts', 'upload_files'];
echo "Important capabilities:" . PHP_EOL;
foreach ($important as $cap) {
	$has = isset($role->capabilities[$cap]) && $role->capabilities[$cap];
	echo "  " . ($has ? "✓" : "❌") . " $cap" . PHP_EOL;
}

// Update existing demo users (force refresh)
echo PHP_EOL . "Refreshing demo users..." . PHP_EOL;
$users = get_users(['role' => 'cts_demo_user']);
foreach ($users as $user) {
	wp_cache_delete($user->ID, 'users');
	wp_cache_delete($user->ID, 'user_meta');
	clean_user_cache($user->ID);
	echo "  ✓ Refreshed user: {$user->user_login}" . PHP_EOL;
}

echo PHP_EOL . "Done! Please test again." . PHP_EOL;
