<?php
/**
 * Find what's causing the redirect
 */

require_once __DIR__ . '/../../../../../wp-load.php';

echo "=== Finding Redirect Cause ===\n\n";

// Set demo user
$demo_user = get_user_by( 'login', 'demo_kn1402' );
wp_set_current_user( $demo_user->ID );

echo "Checking for hooks that might redirect...\n\n";

// Check admin_init hooks
global $wp_filter;

$hooks_to_check = [
	'admin_init',
	'current_screen',
	'load-post-new.php',
	'admin_menu',
	'wp_redirect',
];

foreach ( $hooks_to_check as $hook_name ) {
	if ( isset( $wp_filter[$hook_name] ) ) {
		echo "Hook: {$hook_name}\n";
		echo str_repeat( '-', 60 ) . "\n";
		
		foreach ( $wp_filter[$hook_name]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function_name = 'Unknown';
				
				if ( is_string( $callback['function'] ) ) {
					$function_name = $callback['function'];
				} elseif ( is_array( $callback['function'] ) ) {
					if ( is_object( $callback['function'][0] ) ) {
						$function_name = get_class( $callback['function'][0] ) . '->' . $callback['function'][1];
					} else {
						$function_name = $callback['function'][0] . '::' . $callback['function'][1];
					}
				} elseif ( is_object( $callback['function'] ) && ( $callback['function'] instanceof Closure ) ) {
					$function_name = 'Closure';
				}
				
				// Check if it's from ChurchTools or Demo plugin
				$is_cts = ( strpos( $function_name, 'ChurchTools' ) !== false || 
				           strpos( $function_name, 'churchtools' ) !== false ||
				           strpos( $function_name, 'Demo' ) !== false );
				
				$marker = $is_cts ? ' ⚠️  ' : '    ';
				
				echo "{$marker}Priority {$priority}: {$function_name}\n";
			}
		}
		echo "\n";
	}
}

echo "\n=== Theme Check ===\n\n";

$theme = wp_get_theme();
echo "Active Theme: {$theme->get( 'Name' )} (v{$theme->get( 'Version' )})\n";
echo "Theme Directory: " . get_template_directory() . "\n\n";

// Check if theme has functions.php
$functions_file = get_template_directory() . '/functions.php';
if ( file_exists( $functions_file ) ) {
	echo "Checking functions.php for admin redirects...\n";
	$content = file_get_contents( $functions_file );
	
	$patterns = [
		'wp_redirect',
		'admin_init',
		'current_user_can',
		'cts_demo',
	];
	
	foreach ( $patterns as $pattern ) {
		if ( stripos( $content, $pattern ) !== false ) {
			echo "  ✓ Found: {$pattern}\n";
		}
	}
}

echo "\n=== Active Plugins ===\n\n";

$active_plugins = get_option( 'active_plugins' );
foreach ( $active_plugins as $plugin ) {
	echo "  - {$plugin}\n";
}

echo "\n=== Suggestion ===\n\n";
echo "Look for admin_init hooks from ChurchTools Suite or Demo plugin\n";
echo "that might be checking user capabilities and redirecting.\n\n";
echo "Common culprits:\n";
echo "  1. Admin menu access restrictions\n";
echo "  2. Custom capability  checks\n";
echo "  3. Role-based redirects\n";
echo "  4. Theme restrictions\n";
