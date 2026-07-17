<?php
/**
 * Final verification of ChurchTools menu access
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

wp_set_current_user(13);
$user = wp_get_current_user();

echo "=== Final Verification ===" . PHP_EOL . PHP_EOL;
echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
echo "Role: " . implode(', ', $user->roles) . PHP_EOL . PHP_EOL;

// Count total capabilities
$role = get_role('demo_tester');
$total_caps = count(array_keys(array_filter($role->capabilities)));

echo "Total Capabilities: {$total_caps}" . PHP_EOL . PHP_EOL;

// Check key capabilities
$checks = [
	'read' => 'Read access',
	'upload_files' => 'Upload files',
	'manage_churchtools_suite' => 'ChurchTools menu',
	'edit_cts_demo_pages' => 'Edit demo pages',
	'publish_cts_demo_pages' => 'Publish demo pages',
	'edit_posts' => 'Edit WordPress posts (should be NO)',
	'edit_pages' => 'Edit WordPress pages (should be NO)',
];

echo "Capability checks:" . PHP_EOL;
foreach ($checks as $cap => $description) {
	$has = current_user_can($cap);
	$icon = $has ? "✓" : "✗";
	$status = $has ? "YES" : "NO";
	echo "  {$icon} {$description}: {$status}" . PHP_EOL;
}

echo PHP_EOL . "Expected menu items for demo user:" . PHP_EOL;
echo "  ✓ Dashboard" . PHP_EOL;
echo "  ✓ CTS Demo (Demo Pages)" . PHP_EOL;
echo "  ✓ ChurchTools Suite" . PHP_EOL;
echo "  ✗ Media (hidden via code)" . PHP_EOL;
echo "  ✗ Posts (no capability)" . PHP_EOL;
echo "  ✗ Pages (no capability)" . PHP_EOL;

echo PHP_EOL . "✅ Configuration complete!" . PHP_EOL;
echo "   Please test in browser to verify ChurchTools menu is visible." . PHP_EOL;
