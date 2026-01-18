<?php
/**
 * Event Single - Minimal
 * 
 * Sehr einfache Single-Page ohne Schnörkel - nur die Basics
 * 
 * @package ChurchTools_Suite
 * @since   0.9.9.85
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get event ID from query parameter
$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;

if ( ! $event_id ) {
	echo '<p>Event nicht gefunden.</p>';
	return;
}

// Load repositories
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

$events_repo = new ChurchTools_Suite_Events_Repository();
$calendars_repo = new ChurchTools_Suite_Calendars_Repository();

// Get event
$event = $events_repo->get_by_id( $event_id );

if ( ! $event ) {
	echo '<p>Event nicht gefunden.</p>';
	return;
}

// Get calendar
$calendar = null;
if ( ! empty( $event->calendar_id ) ) {
	$calendar = $calendars_repo->get_by_calendar_id( $event->calendar_id );
}

// Format date
$date_format = get_option( 'date_format', 'd.m.Y' );
$time_format = get_option( 'time_format', 'H:i' );

$start_date = $event->start_datetime ? get_date_from_gmt( $event->start_datetime, $date_format ) : '';
$start_time = $event->start_datetime ? get_date_from_gmt( $event->start_datetime, $time_format ) : '';
$end_time = $event->end_datetime ? get_date_from_gmt( $event->end_datetime, $time_format ) : '';

// Descriptions
$event_description = $event->event_description ?? '';
$appointment_description = $event->appointment_description ?? '';
$full_description = '';

if ( ! empty( $event_description ) ) {
	$full_description = $event_description;
}
if ( ! empty( $appointment_description ) ) {
	if ( ! empty( $full_description ) ) {
		$full_description .= "\n\n";
	}
	$full_description .= $appointment_description;
}

$full_description = wpautop( wp_kses_post( $full_description ) );
?>

<div class="cts-single-minimal">
	<div class="cts-single-header">
		<?php if ( $calendar && ! empty( $calendar->name ) ) : ?>
			<span class="cts-calendar-label"><?php echo esc_html( $calendar->name ); ?></span>
		<?php endif; ?>
		<h1><?php echo esc_html( $event->title ); ?></h1>
	</div>

	<div class="cts-single-info">
		<?php if ( $start_date ) : ?>
			<div class="cts-info-row">
				<div class="cts-info-label">Datum</div>
				<div class="cts-info-value"><?php echo esc_html( $start_date ); ?></div>
			</div>
		<?php endif; ?>
		<?php if ( $start_time ) : ?>
			<div class="cts-info-row">
				<div class="cts-info-label">Zeit</div>
				<div class="cts-info-value">
					<?php echo esc_html( $start_time ); ?>
					<?php if ( $end_time && $end_time !== $start_time ) : ?>
						– <?php echo esc_html( $end_time ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $event->address_name ) || ! empty( $event->location_name ) ) : ?>
			<div class="cts-info-row">
				<div class="cts-info-label"><?php esc_html_e( 'Ort', 'churchtools-suite' ); ?></div>
				<div class="cts-info-value">
					<?php if ( ! empty( $event->address_name ) ) : ?>
						<?php echo esc_html( $event->address_name ); ?>
						<?php if ( ! empty( $event->address_street ) ) : ?>
							<br><?php echo esc_html( $event->address_street ); ?>
						<?php endif; ?>
						<?php if ( ! empty( $event->address_zip ) || ! empty( $event->address_city ) ) : ?>
							<br><?php echo esc_html( trim( $event->address_zip . ' ' . $event->address_city ) ); ?>
						<?php endif; ?>
					<?php elseif ( ! empty( $event->location_name ) ) : ?>
						<?php echo esc_html( $event->location_name ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $full_description ) ) : ?>
		<div class="cts-single-description">
			<?php echo $full_description; ?>
		</div>
	<?php endif; ?>
</div>
