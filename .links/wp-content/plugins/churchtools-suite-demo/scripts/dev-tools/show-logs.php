<?php
/**
 * Show Debug Logs
 *
 * Usage: php show-logs.php
 */

$log_file = __DIR__ . '/../../../../../cts-demo-debug.log';

echo "=== CTS Demo Debug Logs ===\n\n";
echo "Log file: {$log_file}\n\n";

if ( ! file_exists( $log_file ) ) {
	echo "❌ Log file does not exist yet.\n";
	echo "It will be created when a demo user tries to create a page.\n\n";
	echo "Try this:\n";
	echo "  1. As demo user, go to: Demo Pages → Neu hinzufügen\n";
	echo "  2. Run this script again: php show-logs.php\n";
	exit( 0 );
}

$size = filesize( $log_file );
echo "File size: " . round( $size / 1024, 2 ) . " KB\n";
echo "Last modified: " . date( 'Y-m-d H:i:s', filemtime( $log_file ) ) . "\n\n";

if ( $size === 0 ) {
	echo "ℹ️  Log file is empty.\n";
	echo "Debug logging is enabled, but no events have been logged yet.\n\n";
	exit( 0 );
}

// Show last 50 lines
echo "Last 50 log entries:\n";
echo str_repeat( '=', 80 ) . "\n\n";

$lines = file( $log_file );
$last_lines = array_slice( $lines, -50 );

foreach ( $last_lines as $line ) {
	echo $line;
}

echo "\n" . str_repeat( '=', 80 ) . "\n\n";
echo "To watch in real-time:\n";
echo "  PowerShell: Get-Content '{$log_file}' -Wait -Tail 20\n";
echo "  Or run: php watch-debug.php\n";
