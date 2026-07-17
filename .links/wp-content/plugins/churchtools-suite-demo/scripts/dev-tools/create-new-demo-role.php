<?php
/**
 * Create new demo role with fresh name based on all editor capabilities
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Creating New Demo Role ===" . PHP_EOL . PHP_EOL;

// Step 1: Get editor role as template
$editor = get_role('editor');

if (!$editor) {
	echo "❌ Editor role not found!" . PHP_EOL;
	exit(1);
}

echo "Editor role has " . count($editor->capabilities) . " capabilities" . PHP_EOL . PHP_EOL;

// Step 2: Define new role name
$old_role_name = 'cts_demo_user';
$new_role_name = 'demo_tester';  // NEW NAME
$new_role_display = 'Demo Tester';

echo "Old role: $old_role_name" . PHP_EOL;
echo "New role: $new_role_name" . PHP_EOL . PHP_EOL;

// Step 3: Get all users with old role
$users_to_migrate = get_users(['role' => $old_role_name]);
echo "Found " . count($users_to_migrate) . " users with old role" . PHP_EOL . PHP_EOL;

// Step 4: Delete old role
$old_role = get_role($old_role_name);
if ($old_role) {
	echo "Deleting old role: $old_role_name..." . PHP_EOL;
	remove_role($old_role_name);
	echo "✓ Old role deleted" . PHP_EOL . PHP_EOL;
}

// Step 5: Create new role with ALL editor capabilities
echo "Creating new role: $new_role_name..." . PHP_EOL;
$result = add_role(
	$new_role_name,
	$new_role_display,
	$editor->capabilities  // Copy ALL editor capabilities
);

if (!$result) {
	echo "❌ Role creation failed!" . PHP_EOL;
	exit(1);
}

echo "✓ Role created successfully!" . PHP_EOL . PHP_EOL;

// Verify role
$new_role = get_role($new_role_name);
echo "New role has " . count($new_role->capabilities) . " capabilities:" . PHP_EOL;

$important = ['read', 'edit_posts', 'edit_pages', 'publish_posts', 'delete_posts', 'upload_files', 'moderate_comments'];
foreach ($important as $cap) {
	$has = isset($new_role->capabilities[$cap]) && $new_role->capabilities[$cap];
	echo "  " . ($has ? "✓" : "❌") . " $cap" . PHP_EOL;
}

// Step 6: Migrate users to new role
if (count($users_to_migrate) > 0) {
	echo PHP_EOL . "Migrating users to new role..." . PHP_EOL;
	foreach ($users_to_migrate as $user) {
		echo "  User: {$user->user_login} (ID: {$user->ID})" . PHP_EOL;
		
		// Remove old role (if still exists)
		$user->remove_role($old_role_name);
		
		// Add new role
		$user->add_role($new_role_name);
		
		// Clear cache
		wp_cache_delete($user->ID, 'users');
		wp_cache_delete($user->ID, 'user_meta');
		clean_user_cache($user->ID);
		
		// Verify
		$user = new WP_User($user->ID);
		echo "    New roles: " . implode(', ', $user->roles) . PHP_EOL;
		echo "    ✓ Migrated!" . PHP_EOL;
	}
}

echo PHP_EOL . "Done!" . PHP_EOL;
echo PHP_EOL . "⚠️  IMPORTANT: Update code to use '$new_role_name' instead of '$old_role_name'" . PHP_EOL;
echo "Files to update:" . PHP_EOL;
echo "  - includes/services/class-demo-registration-service.php (line 278)" . PHP_EOL;
echo "  - includes/class-demo-template-cpt.php (ensure_demo_user_role method)" . PHP_EOL;
