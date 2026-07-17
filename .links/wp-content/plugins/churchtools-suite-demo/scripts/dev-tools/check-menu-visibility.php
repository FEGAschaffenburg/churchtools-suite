<?php
/**
 * Check what admin menu items demo user can see
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

// Switch to demo user context
wp_set_current_user(13);
$user = wp_get_current_user();

echo "=== Admin Menu Visibility for Demo User ===" . PHP_EOL . PHP_EOL;
echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
echo "Role: " . implode(', ', $user->roles) . PHP_EOL . PHP_EOL;

// Check specific capabilities
$caps_to_check = [
	'upload_files' => 'Media Library',
	'manage_churchtools_suite' => 'ChurchTools Main Menu',
	'edit_posts' => 'Posts',
	'edit_pages' => 'Pages',
	'edit_cts_demo_pages' => 'Demo Pages',
];

echo "Capability checks:" . PHP_EOL;
foreach ($caps_to_check as $cap => $description) {
	$has = current_user_can($cap);
	echo "  " . ($has ? "✓ CAN" : "✗ CANNOT") . " $description (cap: $cap)" . PHP_EOL;
}

echo PHP_EOL . "Menu visibility:" . PHP_EOL;
echo "  - Dashboard: Always visible" . PHP_EOL;
echo "  - Demo Pages: " . (current_user_can('edit_cts_demo_pages') ? "✓ VISIBLE" : "✗ HIDDEN") . PHP_EOL;
echo "  - Media: " . (current_user_can('upload_files') ? "✓ VISIBLE" : "✗ HIDDEN") . PHP_EOL;
echo "  - ChurchTools: " . (current_user_can('manage_churchtools_suite') ? "✓ VISIBLE" : "✗ HIDDEN") . PHP_EOL;
echo "  - Posts: " . (current_user_can('edit_posts') ? "✓ VISIBLE" : "✗ HIDDEN") . PHP_EOL;
echo "  - Pages: " . (current_user_can('edit_pages') ? "✓ VISIBLE" : "✗ HIDDEN") . PHP_EOL;

echo PHP_EOL . "Analysis:" . PHP_EOL;
if (current_user_can('upload_files')) {
	echo "  ⚠️  Media Library is VISIBLE because user has 'upload_files' capability" . PHP_EOL;
	echo "     This is needed for uploading images in demo pages." . PHP_EOL;
	echo "     We can hide the menu entry while keeping the capability." . PHP_EOL;
}

if (current_user_can('manage_churchtools_suite')) {
	echo "  ❌ ChurchTools menu is VISIBLE - should not be!" . PHP_EOL;
} else {
	echo "  ✓ ChurchTools menu is HIDDEN - correct!" . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
