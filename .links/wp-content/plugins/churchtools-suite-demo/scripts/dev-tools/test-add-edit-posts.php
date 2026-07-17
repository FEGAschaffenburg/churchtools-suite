<?php
/**
 * Test: Grant edit_posts capability temporarily to see if that's the issue
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Testing edit_posts Capability ===" . PHP_EOL . PHP_EOL;

$role = get_role('cts_demo_user');

if (!$role) {
	echo "❌ Role not found!" . PHP_EOL;
	exit(1);
}

echo "Adding edit_posts capability..." . PHP_EOL;
$role->add_cap('edit_posts');

echo "✓ Done!" . PHP_EOL . PHP_EOL;

echo "Now test by logging in as demo user and trying to create a page." . PHP_EOL;
echo "Does it still redirect?" . PHP_EOL;
