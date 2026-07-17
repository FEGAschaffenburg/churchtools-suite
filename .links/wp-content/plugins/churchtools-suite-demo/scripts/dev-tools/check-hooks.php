<?php
/**
 * Check if debug hooks are properly registered
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Checking Debug Hooks ===" . PHP_EOL . PHP_EOL;

// Check admin_init hook
echo "admin_init hooks:" . PHP_EOL;
global $wp_filter;
if (isset($wp_filter['admin_init'])) {
    foreach ($wp_filter['admin_init']->callbacks as $priority => $hooks) {
        foreach ($hooks as $hook) {
            if (is_array($hook['function'])) {
                $class = is_object($hook['function'][0]) ? get_class($hook['function'][0]) : $hook['function'][0];
                $method = $hook['function'][1];
                if (strpos($class, 'Demo') !== false || strpos($method, 'debug') !== false) {
                    echo "  Priority $priority: $class->$method()" . PHP_EOL;
                }
            }
        }
    }
}

// Check wp_redirect filter
echo PHP_EOL . "wp_redirect filter:" . PHP_EOL;
if (isset($wp_filter['wp_redirect'])) {
    foreach ($wp_filter['wp_redirect']->callbacks as $priority => $hooks) {
        foreach ($hooks as $hook) {
            if (is_array($hook['function'])) {
                $class = is_object($hook['function'][0]) ? get_class($hook['function'][0]) : $hook['function'][0];
                $method = $hook['function'][1];
                echo "  Priority $priority: $class->$method()" . PHP_EOL;
            }
        }
    }
} else {
    echo "  ❌ Not registered!" . PHP_EOL;
}

// Check if debug methods exist
echo PHP_EOL . "Checking if debug methods exist:" . PHP_EOL;
if (class_exists('ChurchTools_Suite_Demo_Template_CPT')) {
    $reflection = new ReflectionClass('ChurchTools_Suite_Demo_Template_CPT');
    $methods = ['debug_log', 'debug_page_creation_access', 'debug_redirect'];
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "  ✓ $method() exists" . PHP_EOL;
        } else {
            echo "  ❌ $method() missing!" . PHP_EOL;
        }
    }
} else {
    echo "  ❌ Class ChurchTools_Suite_Demo_Template_CPT not found!" . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
