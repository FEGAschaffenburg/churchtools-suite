<?php
/**
 * Quick check script
 */

// Show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>ChurchTools Suite Demo - Plugin Check</h1>";

// Load WordPress (absolute path to avoid open_basedir restriction)
require_once '/var/www/clients/client436/web2980/web/wp-load.php';

echo "<h2>1. Plugin Status</h2>";
if (is_plugin_active('churchtools-suite-demo-1.0.6.0/churchtools-suite-demo.php')) {
    echo "✅ Plugin is ACTIVE<br>";
} else {
    echo "❌ Plugin is NOT active<br>";
}

echo "<h2>2. Class Check</h2>";
if (class_exists('ChurchTools_Suite_Demo_Registration_Service')) {
    echo "✅ Registration Service class EXISTS<br>";
} else {
    echo "❌ Registration Service class NOT FOUND<br>";
}

if (class_exists('ChurchTools_Suite_Demo_Shortcodes')) {
    echo "✅ Shortcodes class EXISTS<br>";
} else {
    echo "❌ Shortcodes class NOT FOUND<br>";
}

echo "<h2>3. Shortcode Check</h2>";
global $shortcode_tags;
if (isset($shortcode_tags['cts_demo_register'])) {
    echo "✅ Shortcode [cts_demo_register] is REGISTERED<br>";
} else {
    echo "❌ Shortcode [cts_demo_register] NOT registered<br>";
}

echo "<h2>4. AJAX Action Check</h2>";
if (has_action('wp_ajax_nopriv_cts_demo_register')) {
    echo "✅ AJAX action 'cts_demo_register' is REGISTERED<br>";
} else {
    echo "❌ AJAX action 'cts_demo_register' NOT registered<br>";
}

echo "<h2>5. Database Check</h2>";
global $wpdb;
$table = $wpdb->prefix . 'cts_demo_users';
$exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
if ($exists) {
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo "✅ Table exists: $table ($count users)<br>";
} else {
    echo "❌ Table NOT found: $table<br>";
}

echo "<h2>6. Recent Errors</h2>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $lines = file($log_file);
    $recent = array_slice($lines, -20);
    echo "<pre>" . htmlspecialchars(implode('', $recent)) . "</pre>";
} else {
    echo "No debug.log found<br>";
}
