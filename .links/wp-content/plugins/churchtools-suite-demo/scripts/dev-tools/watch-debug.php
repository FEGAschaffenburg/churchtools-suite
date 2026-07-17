<?php
/**
 * Watch Debug Logs in Real-Time
 * 
 * Usage: php watch-debug.php
 * Then in another terminal, try to create a demo page
 */

$log_file = __DIR__ . '/../../../../../cts-demo-debug.log';

echo "=== Watching CTS Demo Debug Log ===\n";
echo "Log file: {$log_file}\n\n";

if ( ! file_exists( $log_file ) ) {
	echo "Creating log file...\n";
	touch( $log_file );
}

echo "Waiting for log entries...\n";
echo "Try to create a demo page now!\n";
echo "Press Ctrl+C to stop.\n\n";
echo str_repeat( '=', 80 ) . "\n\n";

// Clear old content
file_put_contents( $log_file, '' );

$last_size = 0;

while ( true ) {
	clearstatcache();
	$current_size = filesize( $log_file );
	
	if ( $current_size > $last_size ) {
		$handle = fopen( $log_file, 'r' );
		fseek( $handle, $last_size );
		
		while ( ! feof( $handle ) ) {
			$line = fgets( $handle );
			if ( $line ) {
				echo $line;
			}
		}
		
		fclose( $handle );
		$last_size = $current_size;
	}
	
	usleep( 100000 ); // 100ms
}
