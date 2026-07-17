<?php
/**
 * Check current user role and capabilities
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Checking Demo User Status ===" . PHP_EOL . PHP_EOL;

// Get user
$user = get_user_by('id', 13);

if (!$user) {
	echo "❌ User ID 13 not found!" . PHP_EOL;
	exit(1);
}

echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
echo "Roles: " . implode(', ', $user->roles) . PHP_EOL . PHP_EOL;

// Check demo_tester role
$role = get_role('demo_tester');

if (!$role) {
	echo "❌ demo_tester role does not exist!" . PHP_EOL;
	exit(1);
}

echo "demo_tester role capabilities (" . count($role->capabilities) . " total):" . PHP_EOL;
$important_caps = ['read', 'edit_posts', 'edit_pages', 'edit_cts_demo_pages', 'publish_posts', 'delete_posts'];
foreach ($important_caps as $cap) {
	$has_it = isset($role->capabilities[$cap]) && $role->capabilities[$cap];
	echo "  " . ($has_it ? "✓" : "❌") . " $cap" . PHP_EOL;
}

echo PHP_EOL . "User's actual capabilities:" . PHP_EOL;
foreach ($important_caps as $cap) {
	$has_it = $user->has_cap($cap);
	echo "  " . ($has_it ? "✓" : "❌") . " $cap" . PHP_EOL;
}

// Check CPT
echo PHP_EOL . "CPT cts_demo_page:" . PHP_EOL;
$cpt = get_post_type_object('cts_demo_page');
if ($cpt) {
	echo "  ✓ Registered" . PHP_EOL;
	echo "  create_posts capability: " . $cpt->cap->create_posts . PHP_EOL;
	echo "  User can create: " . (current_user_can($cpt->cap->create_posts) ? "✓ YES" : "❌ NO") . PHP_EOL;
} else {
	echo "  ❌ Not registered!" . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
