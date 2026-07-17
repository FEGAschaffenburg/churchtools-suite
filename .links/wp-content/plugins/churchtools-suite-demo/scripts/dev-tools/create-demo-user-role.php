<?php
/**
 * Create cts_demo_user role as copy of editor role
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Creating cts_demo_user Role (Copy of Editor) ===" . PHP_EOL . PHP_EOL;

// Step 1: Get editor role capabilities
$editor = get_role('editor');

if (!$editor) {
	echo "❌ Editor role not found!" . PHP_EOL;
	exit(1);
}

echo "Editor role has " . count($editor->capabilities) . " capabilities" . PHP_EOL . PHP_EOL;

// Step 2: Delete old cts_demo_user role if it exists
$old_role = get_role('cts_demo_user');
if ($old_role) {
	echo "Deleting old cts_demo_user role..." . PHP_EOL;
	remove_role('cts_demo_user');
	echo "✓ Old role deleted" . PHP_EOL . PHP_EOL;
}

// Step 3: Create new cts_demo_user role with editor capabilities
echo "Creating new cts_demo_user role..." . PHP_EOL;
add_role(
	'cts_demo_user',
	'CTS Demo User',
	$editor->capabilities  // Copy all editor capabilities
);

$role = get_role('cts_demo_user');

if ($role) {
	echo "✓ Role created successfully!" . PHP_EOL . PHP_EOL;
	
	echo "cts_demo_user capabilities:" . PHP_EOL;
	foreach ($role->capabilities as $cap => $value) {
		if ($value) {
			echo "  ✓ $cap" . PHP_EOL;
		}
	}
} else {
	echo "❌ Role creation failed!" . PHP_EOL;
	exit(1);
}

// Step 4: Convert demo users from editor to cts_demo_user
echo PHP_EOL . "Converting demo users to cts_demo_user role..." . PHP_EOL . PHP_EOL;

$demo_users = get_users([
	'role' => 'editor',
	'meta_key' => 'cts_demo_mode',
	'meta_value' => '1'
]);

echo "Found " . count($demo_users) . " demo users with editor role" . PHP_EOL . PHP_EOL;

foreach ($demo_users as $user) {
	echo "User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
	echo "  Current roles: " . implode(', ', $user->roles) . PHP_EOL;
	
	// Remove editor role
	$user->remove_role('editor');
	
	// Add cts_demo_user role
	$user->add_role('cts_demo_user');
	
	// Reload user
	$user = new WP_User($user->ID);
	
	echo "  New roles: " . implode(', ', $user->roles) . PHP_EOL;
	echo "  ✓ Converted to cts_demo_user!" . PHP_EOL . PHP_EOL;
}

echo PHP_EOL . "Done! cts_demo_user role created with all editor capabilities." . PHP_EOL;
