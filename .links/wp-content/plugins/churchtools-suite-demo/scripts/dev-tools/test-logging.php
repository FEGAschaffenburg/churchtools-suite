<?php
/**
 * Test if logging system works
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Testing Logging System ===" . PHP_EOL . PHP_EOL;

// Get the log file path
$log_file = WP_CONTENT_DIR . '/cts-demo-debug.log';
echo "Log file path: $log_file" . PHP_EOL;

// Check if file exists
if (file_exists($log_file)) {
    echo "✓ File exists (Size: " . filesize($log_file) . " bytes)" . PHP_EOL;
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($log_file)) . PHP_EOL;
} else {
    echo "❌ File does not exist yet" . PHP_EOL;
}

// Check if directory is writable
$dir = dirname($log_file);
if (is_writable($dir)) {
    echo "✓ Directory is writable: $dir" . PHP_EOL;
} else {
    echo "❌ Directory is NOT writable: $dir" . PHP_EOL;
}

// Try to write a test message
echo PHP_EOL . "Attempting to write test message..." . PHP_EOL;
$test_message = "[" . date('Y-m-d H:i:s') . "] TEST: Manual logging test" . PHP_EOL;
$result = file_put_contents($log_file, $test_message, FILE_APPEND);

if ($result !== false) {
    echo "✓ Write successful ($result bytes)" . PHP_EOL;
    echo PHP_EOL . "Log content:" . PHP_EOL;
    echo file_get_contents($log_file);
} else {
    echo "❌ Write failed!" . PHP_EOL;
}

// Now test the actual debug_log method if available
echo PHP_EOL . "Testing debug_log() method..." . PHP_EOL;
if (class_exists('ChurchTools_Suite_Demo_Template_CPT')) {
    $reflection = new ReflectionClass('ChurchTools_Suite_Demo_Template_CPT');
    if ($reflection->hasMethod('debug_log')) {
        $method = $reflection->getMethod('debug_log');
        $method->setAccessible(true);
        $instance = new ChurchTools_Suite_Demo_Template_CPT();
        $method->invoke($instance, "TEST: debug_log() method called");
        echo "✓ debug_log() method executed" . PHP_EOL;
        
        echo PHP_EOL . "Log content after debug_log():" . PHP_EOL;
        echo file_get_contents($log_file);
    } else {
        echo "❌ debug_log() method not found" . PHP_EOL;
    }
} else {
    echo "❌ Class not found" . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
