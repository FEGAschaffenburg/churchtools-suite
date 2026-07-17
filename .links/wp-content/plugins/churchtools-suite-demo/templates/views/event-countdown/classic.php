<?php
/**
 * Countdown View - Classic
 * 
 * Zeigt das nächste anstehende Event mit Countdown-Timer und Hero-Image
 * Design: Split-Layout mit Event-Details links, Hero-Image rechts
 * 
 * @package ChurchTools_Suite
 * @since   1.1.1.0
 * @version 1.1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display Options
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_images = isset( $args['show_images'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Nur das nächste Event anzeigen
$next_event = ! empty( $events ) ? $events[0] : null;

if ( ! $next_event ) {
	echo '<div class="cts-countdown-empty">';
	echo '<p>' . esc_html__( 'Keine anstehenden Events', 'churchtools-suite' ) . '</p>';
	echo '</div>';
	return;
}

// Event-Action Parameter (v1.1.4.0)
$event_action = $args['event_action'] ?? 'modal';
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// Build event click attributes (v1.1.4.0)
$click_class = '';
$click_attrs = '';

if ( $event_action === 'modal' ) {
	$click_class = ' cts-event-clickable';
	$click_attrs = sprintf(
		' data-event-id="%s" role="button" tabindex="0" aria-label="%s"',
		esc_attr( $next_event['id'] ),
		esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $next_event['title'] ) )
	);
} elseif ( $event_action === 'page' ) {
	$click_class = ' cts-event-page-link';
	$page_url = add_query_arg(
		[
			'event_id' => $next_event['id'],
			'template' => $single_event_template,
			'ctse_context' => 'elementor',
		],
		$single_event_base
	);
	$click_attrs = sprintf(
		' data-event-id="%s" data-event-url="%s" role="link" tabindex="0" aria-label="%s"',
		esc_attr( $next_event['id'] ),
		esc_url( $page_url ),
		esc_attr( sprintf( __( 'Zu %s navigieren', 'churchtools-suite' ), $next_event['title'] ) )
	);
}

// Event-Daten extrahieren
$event_id = $next_event['event_id'] ?? $next_event['appointment_id'] ?? 0;
$event_title = $next_event['title'] ?? '';
$event_description = $next_event['event_description'] ?? '';
$appointment_description = $next_event['appointment_description'] ?? '';
$start_datetime = $next_event['start_datetime'] ?? '';
$end_datetime = $next_event['end_datetime'] ?? '';
$calendar_name = $next_event['calendar_name'] ?? '';
$calendar_color = $next_event['calendar_color'] ?? '#3b82f6';
$location = $next_event['location_name'] ?? '';
$tags = $next_event['tags_array'] ?? [];
$services = $next_event['services'] ?? [];

// Bild-Prioritat: Event -> Kalender (Fallback uber Image Helper)
$hero_image = null;
if ( class_exists( 'ChurchTools_Suite_Image_Helper' ) ) {
	$event_arr = is_array( $next_event ) ? $next_event : (array) $next_event;
	$calendar_for_image = ! empty( $event_arr['calendar_image_id'] ) ? [
		'calendar_image_id' => $event_arr['calendar_image_id'],
	] : null;
	$hero_image = ChurchTools_Suite_Image_Helper::get_image_url( $event_arr, $calendar_for_image );
}

// Fallbacks when helper is unavailable or returns empty
if ( empty( $hero_image ) ) {
	if ( ! empty( $next_event['image_url'] ) ) {
		$hero_image = $next_event['image_url'];
	} elseif ( ! empty( $next_event['calendar_image_url'] ) ) {
		$hero_image = $next_event['calendar_image_url'];
	}
}

// Datum formatieren (DB ist UTC, konvertieren zu lokaler Timezone)
$date_obj = null;
$wp_timezone = wp_timezone();

if ( ! empty( $start_datetime ) ) {
	try {
		$date_obj = new DateTime( $start_datetime, new DateTimeZone( 'UTC' ) );
		$date_obj->setTimezone( $wp_timezone );
	} catch ( Exception $e ) {
		error_log( 'Countdown: DateTime creation failed: ' . $e->getMessage() );
		$date_obj = null;
	}
}

$day = $date_obj ? wp_date( 'd', $date_obj->getTimestamp(), $wp_timezone ) : '';
$month = $date_obj ? wp_date( 'F', $date_obj->getTimestamp(), $wp_timezone ) : '';
$year = $date_obj ? wp_date( 'Y', $date_obj->getTimestamp(), $wp_timezone ) : '';

// Uhrzeit formatieren
$start_ts = $date_obj ? $date_obj->getTimestamp() : 0;
$time_format = get_option( 'time_format' );
$has_ampm = ( strpos( $time_format, 'a' ) !== false || strpos( $time_format, 'A' ) !== false );
$start_time_display = $start_ts ? wp_date( $time_format, $start_ts, $wp_timezone ) : '';
if ( ! $has_ampm && $start_time_display ) {
	$start_time_display .= ' Uhr'; // German format
}

// End time
$end_time_display = '';
if ( ! empty( $end_datetime ) ) {
	try {
		$end_obj = new DateTime( $end_datetime, new DateTimeZone( 'UTC' ) );
		$end_obj->setTimezone( $wp_timezone );
		$end_ts = $end_obj->getTimestamp();
		$end_time_display = wp_date( $time_format, $end_ts, $wp_timezone );
		if ( ! $has_ampm ) {
			$end_time_display .= ' Uhr';
		}
	} catch ( Exception $e ) {
		error_log( 'Countdown: End DateTime creation failed: ' . $e->getMessage() );
	}
}

// Countdown-Target (ISO 8601 für JavaScript mit Timezone-Info)
$countdown_target = $date_obj ? $date_obj->format( 'c' ) : '';

// Event-Link Logic
$event_url = '#';
$event_class = 'cts-countdown-event';
$event_link_attr = '';

if ( $event_action === 'modal' ) {
	$event_class .= ' cts-event-modal-trigger';
	$event_link_attr = ' data-event-id="' . esc_attr( $event_id ) . '" data-template="' . esc_attr( $single_event_template ?? 'professional' ) . '"';
} elseif ( $event_action === 'page' ) {
	$event_url = esc_url( $single_event_base . '?event_id=' . $event_id );
} else {
	$event_link_attr = '';
	$event_class .= ' cts-countdown-event-disabled';
}

// Style Mode Management
$style_mode = $args['style_mode'] ?? 'theme';
$use_custom_colors = $style_mode === 'custom';
$custom_primary_color = $args['custom_primary_color'] ?? '#3b82f6';
$custom_text_color = $args['custom_text_color'] ?? '#ffffff';
$custom_background_color = $args['custom_background_color'] ?? '#2d3748';

// CSS-Variablen für Style-Mode
$style_attr = 'style="--calendar-color: ' . esc_attr( $calendar_color ) . ';';

// Custom-Modus: Setze Custom-Farben als CSS-Variablen
if ( $use_custom_colors ) {
	$style_attr .= ' --countdown-bg: ' . esc_attr( $custom_background_color ) . ';';
	$style_attr .= ' --countdown-text: ' . esc_attr( $custom_text_color ) . ';';
	$style_attr .= ' --countdown-accent: ' . esc_attr( $custom_primary_color ) . ';';
}

$style_attr .= '"';
?>

<div class="cts-countdown-classic<?php echo $use_calendar_colors ? ' cts-countdown-use-calendar-colors' : ''; ?><?php echo $click_class; ?>" 
     data-style-mode="<?php echo esc_attr( $style_mode ); ?>" 
     <?php echo $style_attr; ?> 
     data-countdown-target="<?php echo esc_attr( $countdown_target ); ?>"
     <?php echo $click_attrs; ?>>

	<!-- Main Content -->
	<div class="cts-countdown-content">
		
		<!-- Left Side: Event Details -->
		<div class="cts-countdown-left">
			
			<!-- Event Details -->
			<div class="cts-countdown-details">
				
				<!-- Datum-Badge -->
				<div class="cts-countdown-date">
					<span class="cts-countdown-day"><?php echo esc_html( $day ); ?></span>
					<span class="cts-countdown-month"><?php echo esc_html( $month ); ?></span>
					<span class="cts-countdown-year"><?php echo esc_html( $year ); ?></span>
				</div>

				<!-- Event-Info -->
				<div class="cts-countdown-info">
					<h3 class="cts-countdown-title"><?php echo esc_html( $event_title ); ?></h3>
					
					<?php if ( $show_time && ! empty( $start_time_display ) ) : ?>
						<p class="cts-countdown-time">
							<span class="dashicons dashicons-clock"></span>
							<?php 
							if ( ! empty( $end_time_display ) ) {
								echo esc_html( $start_time_display . ' - ' . $end_time_display );
							} else {
								echo esc_html( $start_time_display );
							}
							?>
						</p>
					<?php endif; ?>
					
					<?php if ( $show_event_description && ! empty( $event_description ) ) : ?>
						<p class="cts-countdown-description"><?php echo esc_html( wp_trim_words( $event_description, 20 ) ); ?></p>
					<?php endif; ?>
					
					<?php if ( $show_appointment_description && ! empty( $appointment_description ) ) : ?>
						<p class="cts-countdown-appointment-description"><?php echo esc_html( wp_trim_words( $appointment_description, 20 ) ); ?></p>
					<?php endif; ?>
					
					<?php if ( $show_location && ! empty( $location ) ) : ?>
						<p class="cts-countdown-location">
							<span class="dashicons dashicons-location"></span>
							<?php echo esc_html( $location ); ?>
						</p>
					<?php endif; ?>
					
					<?php if ( $show_calendar_name && ! empty( $calendar_name ) ) : ?>
						<p class="cts-countdown-calendar">
							<span class="dashicons dashicons-calendar"></span>
							<?php echo esc_html( $calendar_name ); ?>
						</p>
					<?php endif; ?>
					
					<?php if ( $show_tags && ! empty( $tags ) && is_array( $tags ) ) : ?>
						<div class="cts-countdown-tags">
							<?php foreach ( $tags as $tag ) : ?>
							<?php 
							$tag_name = is_array( $tag ) ? ( $tag['name'] ?? '' ) : $tag;
							if ( ! empty( $tag_name ) ) :
							?>
								<span class="cts-countdown-tag"><?php echo esc_html( $tag_name ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				
				<?php if ( $show_services && ! empty( $services ) && is_array( $services ) ) : ?>
					<div class="cts-countdown-services">
						<?php foreach ( $services as $service ) : ?>
							<?php 
							$service_name = is_array( $service ) ? ( $service['service_name'] ?? $service['name'] ?? '' ) : $service;
							if ( ! empty( $service_name ) ) :
							?>
								<span class="cts-countdown-service"><?php echo esc_html( $service_name ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
					
		</div><!-- /.cts-countdown-info -->
		
	</div><!-- /.cts-countdown-details -->
	
</div><!-- /.cts-countdown-left -->

<!-- Right Side: Hero Image with Countdown Overlay -->
<div class="cts-countdown-right <?php echo ( ! $show_images || ! $hero_image ) ? 'cts-countdown-no-image' : ''; ?>">
	<div class="cts-countdown-image-wrapper">
		<?php if ( $show_images && $hero_image ) : ?>
			<img src="<?php echo esc_url( $hero_image ); ?>" 
			     alt="<?php echo esc_attr( $event_title ); ?>"
			     class="cts-countdown-image">
		<?php else : ?>
			<!-- Fallback: Calendar Color Background -->
			<div class="cts-countdown-color-fallback"></div>
		<?php endif; ?>
		
		<!-- Countdown Timer Overlay -->
		<div class="cts-countdown-timer-overlay">
			<div class="cts-countdown-timer">
				<div class="cts-countdown-unit">
					<span class="cts-countdown-value" data-unit="days">0</span>
					<span class="cts-countdown-unit-label"><?php esc_html_e( 'TAGE', 'churchtools-suite' ); ?></span>
				</div>
				<div class="cts-countdown-unit">
					<span class="cts-countdown-value" data-unit="hours">0</span>
					<span class="cts-countdown-unit-label"><?php esc_html_e( 'STD', 'churchtools-suite' ); ?></span>
				</div>
				<div class="cts-countdown-unit">
					<span class="cts-countdown-value" data-unit="minutes">0</span>
					<span class="cts-countdown-unit-label"><?php esc_html_e( 'MIN', 'churchtools-suite' ); ?></span>
				</div>
				<div class="cts-countdown-unit">
					<span class="cts-countdown-value" data-unit="seconds">0</span>
					<span class="cts-countdown-unit-label"><?php esc_html_e( 'SEK', 'churchtools-suite' ); ?></span>
				</div>
			</div>
		</div>
		
		<?php if ( $show_calendar_name && ! empty( $calendar_name ) ) : ?>
			<span class="cts-countdown-calendar-badge">
				<?php echo esc_html( $calendar_name ); ?>
			</span>
		<?php endif; ?>
	</div>
</div>

	</div><!-- /.cts-countdown-content -->

</div><!-- /.cts-countdown-classic -->
