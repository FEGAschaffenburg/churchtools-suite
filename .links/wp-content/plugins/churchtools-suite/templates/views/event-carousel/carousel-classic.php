<?php
/**
 * Carousel View - Classic
 * 
 * Horizontales Karussell mit Hero-Images (basiert auf Grid Classic):
 * - Hero-Bild ODER Kalenderfarbe als Hintergrund
 * - Datum-Badge (overlay links oben)
 * - Titel + Location + Zeit
 * - Swipe-Navigation
 * - Prev/Next Buttons
 * - Pagination Dots
 * 
 * @package ChurchTools_Suite
 * @since   1.1.3.0
 * @version 1.1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Carousel-spezifische Optionen
$slides_per_view = isset( $args['slides_per_view'] ) ? intval( $args['slides_per_view'] ) : 3;
$slides_per_view = max( 1, min( 6, $slides_per_view ) ); // Validate 1-6
$autoplay = isset( $args['autoplay'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['autoplay'] ) : false;
$autoplay_delay = isset( $args['autoplay_delay'] ) ? intval( $args['autoplay_delay'] ) : 5000;
$autoplay_delay = max( 1000, min( 10000, $autoplay_delay ) ); // Validate 1-10 seconds
$loop = isset( $args['loop'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['loop'] ) : true;

// Display Options (wie Grid)
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_images = isset( $args['show_images'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_services = isset( $args['show_services'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$image_fit = isset( $args['image_fit'] ) ? ChurchTools_Suite_Shortcodes::sanitize_image_fit( $args['image_fit'] ) : 'cover';

$event_action = $args['event_action'] ?? 'modal';

// Single event target for page clicks
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// Use WordPress timezone
$wp_timezone = wp_timezone();

// Calendar colors
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : true;

// Build event classes
$event_class = '';
if ( $event_action === 'modal' ) {
	$event_class = 'cts-event-clickable';
} elseif ( $event_action === 'page' ) {
	$event_class = 'cts-event-page-link';
}

// Wrapper classes
$wrapper_classes = [
	'churchtools-suite-wrapper',
	'cts-carousel-classic',
];
if ( ! empty( $args['class'] ) ) {
	$wrapper_classes[] = esc_attr( $args['class'] );
}

// Helper: Get image URL (like Grid) - v1.1.3.8: Added image_url support
if ( ! function_exists( 'cts_carousel_get_image_url' ) ) {
	function cts_carousel_get_image_url( $event ) {
		// Priority: image_url (v1.1.3.8) > event_image > appointment_image > calendar_image
		if ( ! empty( $event['image_url'] ) ) {
			return $event['image_url'];
		}
		if ( ! empty( $event['event_image_url'] ) ) {
			return $event['event_image_url'];
		}
		if ( ! empty( $event['appointment_image_url'] ) ) {
			return $event['appointment_image_url'];
		}
		if ( ! empty( $event['calendar_image_url'] ) ) {
			return $event['calendar_image_url'];
		}
		return ''; // Kein Fallback SVG - nutze Calendar Color als Hintergrund
	}
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>"
     data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
     data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>"
     data-loop="<?php echo $loop ? '1' : '0'; ?>"
	data-show-images="<?php echo $show_images ? '1' : '0'; ?>"
	data-image-fit="<?php echo esc_attr( $image_fit ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		<p class="cts-no-events"><?php esc_html_e( 'Keine Events gefunden.', 'churchtools-suite' ); ?></p>
	<?php else : ?>
		<?php $has_multiple_events = count( $events ) > 1; ?>
		
		<!-- Carousel Container -->
		<div class="cts-carousel-container">
			<div class="cts-carousel-track">
				<?php foreach ( $events as $event ) : 
					// Start timestamp
					$start_ts = current_time( 'timestamp' );
					if ( ! empty( $event['start_datetime'] ) ) {
						try {
							$dt = new DateTime( $event['start_datetime'], new DateTimeZone( 'UTC' ) );
							$dt->setTimezone( $wp_timezone );
							$start_ts = $dt->getTimestamp();
						} catch ( Exception $e ) {
							$start_ts = current_time( 'timestamp' );
						}
					}
					
					// Formatiere Zeit mit "Uhr" oder AM/PM
					$time_format = get_option( 'time_format' );
					$has_ampm = ( strpos( $time_format, 'a' ) !== false || strpos( $time_format, 'A' ) !== false );
					
					$start_time_display = wp_date( $time_format, $start_ts, $wp_timezone );
					if ( ! $has_ampm ) {
						$start_time_display .= ' Uhr'; // Deutsches Format: "20:00 Uhr"
					}
					
					// End timestamp
					$end_time_display = '';
					if ( ! empty( $event['end_datetime'] ) ) {
						try {
							$dt_end = new DateTime( $event['end_datetime'], new DateTimeZone( 'UTC' ) );
							$dt_end->setTimezone( $wp_timezone );
							$end_ts = $dt_end->getTimestamp();
							$end_time_display = wp_date( $time_format, $end_ts, $wp_timezone );
							if ( ! $has_ampm ) {
								$end_time_display .= ' Uhr'; // Deutsches Format: "22:00 Uhr"
							}
						} catch ( Exception $e ) {
							// Skip
						}
					}
					
					// Event-Action Data Attributes
					$event_attrs = '';
					$click_url = '';
					if ( $event_action === 'modal' ) {
						$event_attrs = sprintf(
							'data-event-id="%s" data-event-title="%s" data-event-start="%s" data-event-location="%s" data-event-description="%s"',
							esc_attr( $event['id'] ),
							esc_attr( $event['title'] ),
							esc_attr( $event['start_datetime'] ),
							esc_attr( $event['location_name'] ?? '' ),
							esc_attr( wp_trim_words( $event['event_description'] ?? '', 50 ) )
						);
					} elseif ( $event_action === 'page' ) {
						$click_url = add_query_arg(
							[
								'event_id' => $event['id'],
								'template' => $single_event_template,
							],
							$single_event_base
						);
						$event_attrs = sprintf(
							'data-event-id="%s" data-event-url="%s"',
							esc_attr( $event['id'] ),
							esc_url( $click_url )
						);
					}
					
					// Calendar color
					$calendar_color = $event['calendar_color'] ?? '#2563eb';
					
					// Image URL
					$image_url = cts_carousel_get_image_url( $event );
					
					// Slide style
					$slide_style = '';
					if ( $use_calendar_colors ) {
						$slide_style .= sprintf( '--calendar-color: %s;', esc_attr( $calendar_color ) );
					}
					// WICHTIG: Wenn show_images=false ODER kein Bild → Kalenderfarbe als Hintergrund
					if ( ! $show_images || empty( $image_url ) ) {
						$slide_style .= sprintf( ' background: %s;', esc_attr( $calendar_color ) );
					}
					?>
					
					<div class="cts-carousel-slide <?php echo esc_attr( $event_class ); ?>" 
					     <?php echo $event_attrs; ?>
					     <?php if ( ! empty( $slide_style ) ) : ?>
					     style="<?php echo esc_attr( $slide_style ); ?>"
					     <?php endif; ?>>  
						
						<!-- Hero Image ODER Farbhintergrund -->
						<?php if ( $show_images && ! empty( $image_url ) ) : ?>
							<div class="cts-carousel-hero">
								<img src="<?php echo esc_url( $image_url ); ?>" 
								     alt="<?php echo esc_attr( $event['title'] ); ?>"
								     class="cts-carousel-hero-img">
								
								<!-- Datum Badge (Overlay) -->
							<div class="cts-carousel-date-badge" 
							     <?php if ( $use_calendar_colors ) : ?>
							     style="background: <?php echo esc_attr( $calendar_color ); ?>"
							     <?php endif; ?>>
								<div class="cts-carousel-date-day">
									<?php echo esc_html( wp_date( 'd', $start_ts, $wp_timezone ) ); ?>
								</div>
								<div class="cts-carousel-date-month">
									<?php echo esc_html( strtoupper( wp_date( 'M', $start_ts, $wp_timezone ) ) ); ?>
									</div>
								</div>
								
								<!-- Calendar Badge (top-right) -->
								<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
									<div class="cts-carousel-calendar-badge">
										<?php echo esc_html( $event['calendar_name'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<!-- Kein Bild: Zeige Datum + Kalender-Badge auf Farbhintergrund -->
							<div class="cts-carousel-color-bg">
							<div class="cts-carousel-date-badge" 
							     <?php if ( $use_calendar_colors ) : ?>
							     style="background: <?php echo esc_attr( $calendar_color ); ?>"
							     <?php endif; ?>>
								<div class="cts-carousel-date-day">
									<?php echo esc_html( wp_date( 'd', $start_ts, $wp_timezone ) ); ?>
									</div>
									<div class="cts-carousel-date-month">
										<?php echo esc_html( strtoupper( wp_date( 'M', $start_ts, $wp_timezone ) ) ); ?>
									</div>
								</div>
								
								<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
									<div class="cts-carousel-calendar-badge">
										<?php echo esc_html( $event['calendar_name'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						
						<!-- Slide Content -->
						<div class="cts-carousel-content">
							<h3 class="cts-carousel-title"><?php echo esc_html( $event['title'] ); ?></h3>
							
							<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
								<div class="cts-carousel-location">
									<span class="dashicons dashicons-location"></span>
									<?php echo esc_html( $event['location_name'] ); ?>
								</div>
							<?php endif; ?>
							
							<?php if ( $show_time ) : ?>
								<div class="cts-carousel-time">
									<span class="dashicons dashicons-clock"></span>
									<?php 
									if ( ! empty( $end_time_display ) ) {
										echo esc_html( $start_time_display . ' - ' . $end_time_display );
									} else {
										echo esc_html( $start_time_display );
									}
									?>
								</div>
							<?php endif; ?>
							
							<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
								<div class="cts-carousel-event-description">
									<?php echo wp_kses_post( wpautop( wp_trim_words( $event['event_description'], 20 ) ) ); ?>
								</div>
							<?php endif; ?>
							
							<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
								<div class="cts-carousel-appointment-description">
									<?php echo wp_kses_post( wpautop( wp_trim_words( $event['appointment_description'], 20 ) ) ); ?>
								</div>
							<?php endif; ?>
							
							<?php if ( $show_tags && ! empty( $event['tags'] ) ) : 
								$tags = json_decode( $event['tags'], true );
								if ( is_array( $tags ) && ! empty( $tags ) ) : ?>
									<div class="cts-carousel-tags">
										<?php foreach ( $tags as $tag ) : 
											$tag_color = $tag['color'] ?? '#6b7280';
											?>
											<span class="cts-carousel-tag" style="background-color: <?php echo esc_attr( $tag_color ); ?>;">
												<?php echo esc_html( $tag['name'] ?? '' ); ?>
											</span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							
							<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
								<div class="cts-carousel-services">
									<strong><?php esc_html_e( 'Dienste:', 'churchtools-suite' ); ?></strong>
									<ul class="cts-carousel-services-list">
										<?php foreach ( $event['services'] as $service ) : ?>
											<li>
												<span class="cts-carousel-service-name"><?php echo esc_html( $service['service_name'] ?? '' ); ?>:</span>
												<span class="cts-carousel-person-name"><?php echo esc_html( $service['person_name'] ?? __( 'Offen', 'churchtools-suite' ) ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		
		<?php if ( $has_multiple_events ) : ?>
		<!-- Navigation Buttons -->
		<button class="cts-carousel-nav cts-carousel-nav-prev" aria-label="<?php esc_attr_e( 'Vorheriges Event', 'churchtools-suite' ); ?>">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
		</button>
		<button class="cts-carousel-nav cts-carousel-nav-next" aria-label="<?php esc_attr_e( 'Nächstes Event', 'churchtools-suite' ); ?>">
			<span class="dashicons dashicons-arrow-right-alt2"></span>
		</button>
		<?php endif; ?>
		
		<!-- Pagination Dots -->
		<div class="cts-carousel-pagination"></div>
		
	<?php endif; ?>
</div>
