<?php
/**
 * Remove standard WordPress capabilities from demo_tester role
 * Keep only: read, upload_files, and cts_demo_page capabilities
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Removing WordPress Standard Capabilities ===" . PHP_EOL . PHP_EOL;

$role = get_role('demo_tester');

if (!$role) {
	echo "❌ demo_tester role not found!" . PHP_EOL;
	exit(1);
}

echo "Current capabilities: " . count($role->capabilities) . PHP_EOL . PHP_EOL;

// Capabilities to REMOVE (standard WordPress editing rights)
$caps_to_remove = [
	// Posts
	'edit_posts',
	'edit_others_posts',
	'edit_published_posts',
	'publish_posts',
	'delete_posts',
	'delete_others_posts',
	'delete_published_posts',
	'edit_private_posts',
	'read_private_posts',
	'delete_private_posts',
	
	// Pages
	'edit_pages',
	'edit_others_pages',
	'edit_published_pages',
	'publish_pages',
	'delete_pages',
	'delete_others_pages',
	'delete_published_pages',
	'edit_private_pages',
	'read_private_pages',
	'delete_private_pages',
	
	// Other
	'manage_categories',
	'manage_links',
	'moderate_comments',
	'unfiltered_html',
	
	// Levels (deprecated but still present)
	'level_7',
	'level_6',
	'level_5',
	'level_4',
	'level_3',
	'level_2',
	'level_1',
	'level_0',
];

echo "Removing standard WordPress capabilities..." . PHP_EOL;
$removed = 0;
foreach ($caps_to_remove as $cap) {
	if (isset($role->capabilities[$cap])) {
		$role->remove_cap($cap);
		echo "  ✓ Removed: $cap" . PHP_EOL;
		$removed++;
	}
}

echo PHP_EOL . "✓ Removed $removed capabilities" . PHP_EOL . PHP_EOL;

// Reload role
$role = get_role('demo_tester');
echo "Remaining capabilities: " . count($role->capabilities) . PHP_EOL . PHP_EOL;

// Show what's left
echo "Capabilities that remain:" . PHP_EOL;
foreach ($role->capabilities as $cap => $value) {
	if ($value) {
		echo "  ✓ $cap" . PHP_EOL;
	}
}

// Refresh demo users
echo PHP_EOL . "Refreshing demo users..." . PHP_EOL;
$users = get_users(['role' => 'demo_tester']);
foreach ($users as $user) {
	wp_cache_delete($user->ID, 'users');
	wp_cache_delete($user->ID, 'user_meta');
	clean_user_cache($user->ID);
	echo "  ✓ {$user->user_login}" . PHP_EOL;
}

echo PHP_EOL . "Done!" . PHP_EOL;
echo PHP_EOL . "Demo users can now:" . PHP_EOL;
echo "  ✓ Create/edit/delete their own Demo Pages" . PHP_EOL;
echo "  ✓ Upload files (for images in Demo Pages)" . PHP_EOL;
echo "  ✓ Read content" . PHP_EOL;
echo PHP_EOL . "Demo users CANNOT:" . PHP_EOL;
echo "  ✗ Create/edit WordPress Posts" . PHP_EOL;
echo "  ✗ Create/edit WordPress Pages" . PHP_EOL;
echo "  ✗ Moderate comments" . PHP_EOL;
echo "  ✗ Manage categories/links" . PHP_EOL;
