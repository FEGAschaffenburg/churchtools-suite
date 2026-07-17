<?php
/**
 * Verify demo_tester role has correct restricted capabilities
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Verifying Demo Role Restrictions ===" . PHP_EOL . PHP_EOL;

$role = get_role('demo_tester');

if (!$role) {
	echo "❌ demo_tester role not found!" . PHP_EOL;
	exit(1);
}

echo "Total capabilities: " . count($role->capabilities) . PHP_EOL . PHP_EOL;

// Check what they SHOULD have
$should_have = ['read', 'upload_files', 'edit_cts_demo_pages', 'publish_cts_demo_pages', 'delete_cts_demo_pages'];
echo "✓ Capabilities they SHOULD have:" . PHP_EOL;
foreach ($should_have as $cap) {
	$has = isset($role->capabilities[$cap]) && $role->capabilities[$cap];
	$status = $has ? "✓ YES" : "❌ NO";
	echo "  $status - $cap" . PHP_EOL;
}

// Check what they should NOT have
$should_not_have = ['edit_posts', 'edit_pages', 'publish_posts', 'manage_categories', 'moderate_comments'];
echo PHP_EOL . "✓ Capabilities they should NOT have:" . PHP_EOL;
foreach ($should_not_have as $cap) {
	$has = isset($role->capabilities[$cap]) && $role->capabilities[$cap];
	$status = !$has ? "✓ CORRECT (not present)" : "❌ WRONG (still present!)";
	echo "  $status - $cap" . PHP_EOL;
}

// Test with actual user
echo PHP_EOL . "Testing with user demo_kn1402..." . PHP_EOL;
$user = get_user_by('id', 13);

if ($user) {
	echo "User: {$user->user_login}" . PHP_EOL;
	echo "Roles: " . implode(', ', $user->roles) . PHP_EOL . PHP_EOL;
	
	// Test specific capabilities
	$tests = [
		'read' => 'Can read',
		'upload_files' => 'Can upload files',
		'edit_cts_demo_pages' => 'Can edit demo pages',
		'edit_posts' => 'Can edit WordPress posts',
		'edit_pages' => 'Can edit WordPress pages',
		'moderate_comments' => 'Can moderate comments',
	];
	
	echo "Capability tests:" . PHP_EOL;
	foreach ($tests as $cap => $description) {
		$can = $user->has_cap($cap);
		$expected = in_array($cap, ['read', 'upload_files', 'edit_cts_demo_pages']);
		$correct = ($can === $expected);
		
		$status = $correct ? "✓" : "❌";
		$result = $can ? "YES" : "NO";
		echo "  $status $description: $result" . ($correct ? '' : ' (WRONG!)') . PHP_EOL;
	}
}

echo PHP_EOL . "Summary:" . PHP_EOL;
if (count($role->capabilities) === 16) {
	echo "  ✓ Correct number of capabilities (16)" . PHP_EOL;
} else {
	echo "  ❌ Wrong number of capabilities (" . count($role->capabilities) . " instead of 16)" . PHP_EOL;
}

echo PHP_EOL . "Done!" . PHP_EOL;
