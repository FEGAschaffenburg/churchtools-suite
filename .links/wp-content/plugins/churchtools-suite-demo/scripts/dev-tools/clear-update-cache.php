<?php
/**
 * Clear WordPress Update Cache
 * 
 * Run this once via browser to force WordPress to check for updates immediately
 * URL: http://your-site.com/wp-content/plugins/churchtools-suite-demo/scripts/dev-tools/clear-update-cache.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
    die( '❌ You need administrator permissions to run this script.' );
}

echo '<h1>WordPress Update Cache Clearer</h1>';
echo '<hr>';

// Delete update transients
$transients_deleted = 0;
$transient_keys = [
    'update_plugins',
    'update_themes',
    '_transient_update_plugins',
    '_site_transient_update_plugins',
    '_transient_update_themes',
    '_site_transient_update_themes',
];

foreach ( $transient_keys as $key ) {
    if ( delete_transient( $key ) || delete_site_transient( $key ) ) {
        echo "✅ Deleted: $key<br>";
        $transients_deleted++;
    }
}

// Force update check
wp_update_plugins();
wp_update_themes();

echo "<hr>";
echo "<p><strong>✅ Cache cleared!</strong> ($transients_deleted transients deleted)</p>";
echo "<p>Go to: <a href='" . admin_url( 'plugins.php' ) . "'>Plugins page</a> and check for updates.</p>";
echo "<p>Or: <a href='" . admin_url( 'update-core.php' ) . "'>Updates page</a></p>";

echo "<hr>";
echo "<h2>Current Plugin Versions</h2>";
echo "<ul>";

$plugins = [
    'churchtools-suite/churchtools-suite.php' => 'ChurchTools Suite',
    'churchtools-suite-demo/churchtools-suite-demo.php' => 'ChurchTools Suite Demo',
    'churchtools-suite-elementor/churchtools-suite-elementor.php' => 'Elementor Integration',
];

foreach ( $plugins as $plugin_file => $plugin_name ) {
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
    if ( file_exists( $plugin_path ) ) {
        $plugin_data = get_plugin_data( $plugin_path );
        echo "<li><strong>$plugin_name:</strong> v{$plugin_data['Version']}</li>";
    } else {
        echo "<li><strong>$plugin_name:</strong> ❌ Not installed</li>";
    }
}

echo "</ul>";

echo "<hr>";
echo "<p><small>You can delete this file after use: " . __FILE__ . "</small></p>";
