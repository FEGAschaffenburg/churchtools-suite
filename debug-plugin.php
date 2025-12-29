<?php
/**
 * Debug Script - Check Plugin Loading
 * 
 * Teste ob das Plugin geladen werden kann
 */

// Simulate WordPress
define( 'ABSPATH', 'C:/test/' );
define( 'CHURCHTOOLS_SUITE_VERSION', '0.7.2.1' );
define( 'CHURCHTOOLS_SUITE_PATH', __DIR__ . '/' );
define( 'CHURCHTOOLS_SUITE_URL', 'http://example.com/' );
define( 'CHURCHTOOLS_SUITE_BASENAME', 'churchtools-suite/churchtools-suite.php' );
define( 'CHURCHTOOLS_SUITE_DB_PREFIX', 'cts_' );

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Testing file loads...\n\n";

// Test Admin Class
echo "Loading Admin Class... ";
try {
    require_once __DIR__ . '/admin/class-churchtools-suite-admin.php';
    echo "✓ OK\n";
    
    // Try to instantiate
    $admin = new ChurchTools_Suite_Admin('0.7.2.1');
    echo "  Admin instance created: ✓ OK\n";
    
    // Check if method exists
    if (method_exists($admin, 'add_plugin_admin_menu')) {
        echo "  Method add_plugin_admin_menu exists: ✓ OK\n";
    } else {
        echo "  Method add_plugin_admin_menu exists: ✗ MISSING!\n";
    }
    
} catch (Throwable $e) {
    echo "✗ ERROR\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

echo "\nDone!\n";
