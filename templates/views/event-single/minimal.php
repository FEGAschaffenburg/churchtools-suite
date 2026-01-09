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
	echo '<p>' . esc_html__( 'Event nicht gefunden.', 'churchtools-suite' ) . '</p>';
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
	echo '<p>' . esc_html__( 'Event nicht gefunden.', 'churchtools-suite' ) . '</p>';
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
	
	<!-- Back Link -->
	<div class="cts-back">
		<a href="javascript:history.back()">← <?php esc_html_e( 'Zurück', 'churchtools-suite' ); ?></a>
	</div>
	
	<!-- Calendar Badge -->
	<?php if ( $calendar && ! empty( $calendar->name ) ) : ?>
		<div class="cts-calendar-badge"><?php echo esc_html( $calendar->name ); ?></div>
	<?php endif; ?>
	
	<!-- Title -->
	<h1 class="cts-title"><?php echo esc_html( $event->title ); ?></h1>
	
	<!-- Date & Time -->
	<div class="cts-datetime">
		<?php if ( $start_date ) : ?>
			<div class="cts-date">📅 <?php echo esc_html( $start_date ); ?></div>
		<?php endif; ?>
		<?php if ( $start_time ) : ?>
			<div class="cts-time">
				🕐 <?php echo esc_html( $start_time ); ?>
				<?php if ( $end_time && $end_time !== $start_time ) : ?>
					- <?php echo esc_html( $end_time ); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- Location -->
	<?php if ( ! empty( $event->address_name ) ) : ?>
		<div class="cts-location">
			<strong>📍 <?php esc_html_e( 'Ort:', 'churchtools-suite' ); ?></strong>
			<div>
				<?php echo esc_html( $event->address_name ); ?>
				<?php if ( ! empty( $event->address_street ) ) : ?>
					<br><?php echo esc_html( $event->address_street ); ?>
				<?php endif; ?>
				<?php if ( ! empty( $event->address_zip ) || ! empty( $event->address_city ) ) : ?>
					<br><?php echo esc_html( trim( $event->address_zip . ' ' . $event->address_city ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	<?php elseif ( ! empty( $event->location_name ) ) : ?>
		<div class="cts-location">
			<strong>📍 <?php esc_html_e( 'Ort:', 'churchtools-suite' ); ?></strong>
			<div><?php echo esc_html( $event->location_name ); ?></div>
		</div>
	<?php endif; ?>
	
	<!-- Description -->
	<?php if ( ! empty( $full_description ) ) : ?>
		<div class="cts-description">
			<?php echo $full_description; ?>
		</div>
	<?php endif; ?>
	
</div>

<style>
.cts-single-minimal {
	max-width: 700px;
	margin: 2rem auto;
	padding: 2rem;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cts-single-minimal .cts-back {
	margin-bottom: 1rem;
}

.cts-single-minimal .cts-back a {
	color: #64748b;
	text-decoration: none;
	font-size: 0.9rem;
}

.cts-single-minimal .cts-back a:hover {
	color: #2563eb;
}

.cts-single-minimal .cts-calendar-badge {
	display: inline-block;
	padding: 4px 12px;
	background: #e2e8f0;
	border-radius: 4px;
	font-size: 0.85rem;
	margin-bottom: 0.5rem;
}

.cts-single-minimal .cts-title {
	font-size: 2rem;
	margin: 0.5rem 0 1.5rem 0;
	color: #1e293b;
	line-height: 1.3;
}

.cts-single-minimal .cts-datetime {
	display: flex;
	gap: 1.5rem;
	margin-bottom: 1.5rem;
	padding: 1rem;
	background: #f8fafc;
	border-radius: 6px;
}

.cts-single-minimal .cts-date,
.cts-single-minimal .cts-time {
	font-size: 1rem;
	color: #475569;
}

.cts-single-minimal .cts-location {
	margin-bottom: 2rem;
	padding: 1rem;
	background: #f8fafc;
	border-radius: 6px;
}

.cts-single-minimal .cts-location strong {
	display: block;
	margin-bottom: 0.5rem;
	color: #64748b;
	font-size: 0.9rem;
}

.cts-single-minimal .cts-description {
	line-height: 1.8;
	color: #334155;
}

.cts-single-minimal .cts-description p:first-child {
	margin-top: 0;
}

.cts-single-minimal .cts-description p:last-child {
	margin-bottom: 0;
}
</style>
