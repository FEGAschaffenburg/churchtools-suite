<?php
/**
 * Dev helper: inspect local events vs. expected cleanup keys (read-only).
 *
 * Usage (wp-env):
 *   npx wp-env run cli wp eval-file wp-content/plugins/churchtools-suite/scripts/dev-tools/check-sync-cleanup.php
 *
 * Or from plugin dir with WP loaded:
 *   php scripts/dev-tools/check-sync-cleanup.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Allow running via `wp eval-file` only.
	fwrite( STDERR, "Run via WP-CLI (wp eval-file), not standalone PHP.\n" );
	exit( 1 );
}

require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';

$days_past   = (int) get_option( 'churchtools_suite_sync_days_past', 7 );
$days_future = (int) get_option( 'churchtools_suite_sync_days_future', 90 );
$from        = gmdate( 'Y-m-d', time() - $days_past * DAY_IN_SECONDS );
$to          = gmdate( 'Y-m-d', time() + $days_future * DAY_IN_SECONDS );

$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
$events_repo    = new ChurchTools_Suite_Events_Repository();
$calendar_ids   = $calendars_repo->get_selected_calendar_ids();

if ( empty( $calendar_ids ) ) {
	WP_CLI::warning( 'No calendars selected. Select calendars in ChurchTools Suite admin first.' );
	exit( 0 );
}

WP_CLI::log( sprintf( 'Sync range: %s → %s', $from, $to ) );

foreach ( $calendar_ids as $calendar_id ) {
	$rows = $events_repo->get_appointment_rows_in_range(
		$from . ' 00:00:00',
		$to . ' 23:59:59',
		(string) $calendar_id
	);

	$missing_appointment_id = 0;
	$keys                   = [];

	foreach ( $rows as $row ) {
		$appointment_id = (string) ( $row['appointment_id'] ?? '' );
		$start_datetime = (string) ( $row['start_datetime'] ?? '' );
		if ( $appointment_id === '' || $start_datetime === '' ) {
			++$missing_appointment_id;
			continue;
		}
		$keys[] = $appointment_id . '|' . $start_datetime;
	}

	WP_CLI::log( '' );
	WP_CLI::log( sprintf( 'Calendar %s: %d rows in range, %d without appointment_id/start', $calendar_id, count( $rows ), $missing_appointment_id ) );
	WP_CLI::log( sprintf( '  Sample keys (max 5): %s', implode( ', ', array_slice( $keys, 0, 5 ) ) ) );
	WP_CLI::log( '  Run a full event sync, then check logs for "Deleted N events/appointments".' );
}
