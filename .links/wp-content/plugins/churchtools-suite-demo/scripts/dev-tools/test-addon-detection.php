<?php
/**
 * Test Addon Detection and Update Check
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

// Load addon page functions from main plugin
$addon_page_path = WP_PLUGIN_DIR . '/churchtools-suite/admin/views/addons-page.php';

if (!file_exists($addon_page_path)) {
	echo "❌ Addons page not found at: {$addon_page_path}" . PHP_EOL;
	exit(1);
}

// Capture the output of addons-page.php to get functions only
ob_start();
require_once $addon_page_path;
ob_end_clean();

echo "=== Testing Addon Detection & Update Check ===" . PHP_EOL . PHP_EOL;

// Get all addons
$addons = cts_get_addon_plugins();

echo "Found Addons: " . count($addons) . PHP_EOL . PHP_EOL;

foreach ($addons as $plugin_file => $addon) {
	echo "Plugin: {$addon['Name']}" . PHP_EOL;
	echo "  Slug: {$addon['plugin_slug']}" . PHP_EOL;
	echo "  Version: {$addon['Version']}" . PHP_EOL;
	echo "  Active: " . ($addon['is_active'] ? "YES" : "NO") . PHP_EOL;
	echo "  GitHub Repo: {$addon['github_repo']}" . PHP_EOL;
	
	if (!empty($addon['github_repo'])) {
		echo "  Checking for updates..." . PHP_EOL;
		$update = cts_check_addon_update($addon['github_repo'], $addon['Version']);
		
		if ($update && !is_wp_error($update)) {
			echo "  ✅ UPDATE AVAILABLE!" . PHP_EOL;
			echo "     Current: {$update['current_version']}" . PHP_EOL;
			echo "     Latest: {$update['latest_version']}" . PHP_EOL;
			echo "     Download: {$update['zip_url']}" . PHP_EOL;
		} elseif (is_wp_error($update)) {
			echo "  ❌ Error: " . $update->get_error_message() . PHP_EOL;
		} else {
			echo "  ✅ Up to date" . PHP_EOL;
		}
	}
	
	echo PHP_EOL;
}

// Test auto-updater class
echo "=== Testing Auto-Updater Class ===" . PHP_EOL . PHP_EOL;

if (class_exists('ChurchTools_Suite_Demo_Auto_Updater')) {
	echo "✅ Auto-Updater class loaded" . PHP_EOL;
	
	$release = ChurchTools_Suite_Demo_Auto_Updater::get_latest_release_info();
	
	if (is_wp_error($release)) {
		echo "❌ Error fetching release: " . $release->get_error_message() . PHP_EOL;
	} else {
		echo "  Latest Release: {$release['version']}" . PHP_EOL;
		echo "  Tag: {$release['tag_name']}" . PHP_EOL;
		echo "  ZIP URL: {$release['zip_url']}" . PHP_EOL;
		echo "  Release URL: {$release['html_url']}" . PHP_EOL;
		
		$current = CHURCHTOOLS_SUITE_DEMO_VERSION;
		echo PHP_EOL . "  Current Version: {$current}" . PHP_EOL;
		
		if (version_compare($release['version'], $current, '>')) {
			echo "  ✅ Update available: {$current} → {$release['version']}" . PHP_EOL;
		} else {
			echo "  ✅ Plugin is up to date" . PHP_EOL;
		}
	}
} else {
	echo "❌ Auto-Updater class NOT loaded" . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
