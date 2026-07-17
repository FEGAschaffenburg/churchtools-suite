<?php
// Quick check if method exists after fresh load
require_once 'C:/Users/nauma/OneDrive/laragon/www/feg-clone/wp-load.php';

$plugin = ChurchTools_Suite_Demo::instance();
$method_exists = method_exists($plugin, 'show_demo_mode_banner');

echo "Method show_demo_mode_banner: " . ($method_exists ? "✅ EXISTS" : "❌ NOT FOUND") . "\n";

if ($method_exists) {
	$ref = new ReflectionMethod($plugin, 'show_demo_mode_banner');
	echo "Defined in: " . basename($ref->getFileName()) . "\n";
	echo "Line: " . $ref->getStartLine() . "\n";
}
