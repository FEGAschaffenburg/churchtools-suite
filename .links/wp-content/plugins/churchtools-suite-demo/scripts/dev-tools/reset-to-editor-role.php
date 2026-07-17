<?php
/**
 * Reset: Delete custom demo role and assign Editor role to demo users
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Reset Demo Users to Editor Role ===" . PHP_EOL . PHP_EOL;

// Step 1: Find all users with cts_demo_user role
$demo_users = get_users([
	'role' => 'cts_demo_user',
	'fields' => ['ID', 'user_login']
]);

echo "Found " . count($demo_users) . " demo users" . PHP_EOL . PHP_EOL;

// Step 2: Change their role to editor
foreach ($demo_users as $user_data) {
	$user = new WP_User($user_data->ID);
	
	echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
	echo "  Current roles: " . implode(', ', $user->roles) . PHP_EOL;
	
	// Remove cts_demo_user role
	$user->remove_role('cts_demo_user');
	
	// Add editor role
	$user->add_role('editor');
	
	// Reload user
	$user = new WP_User($user->ID);
	
	echo "  New roles: " . implode(', ', $user->roles) . PHP_EOL;
	echo "  ✓ Changed to editor!" . PHP_EOL . PHP_EOL;
}

// Step 3: Delete the cts_demo_user role
echo "Deleting cts_demo_user role..." . PHP_EOL;
remove_role('cts_demo_user');

$role = get_role('cts_demo_user');
if (!$role) {
	echo "✓ Role deleted successfully!" . PHP_EOL;
} else {
	echo "❌ Role still exists!" . PHP_EOL;
}

echo PHP_EOL . "Done! All demo users are now editors." . PHP_EOL;
echo PHP_EOL . "Editor capabilities include:" . PHP_EOL;
$editor = get_role('editor');
if ($editor) {
	foreach ($editor->capabilities as $cap => $value) {
		if ($value) {
			echo "  ✓ $cap" . PHP_EOL;
		}
	}
}
