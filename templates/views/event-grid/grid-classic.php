<?php
/**
 * Grid View - Classic
 * 
 * Klassisches Card-Layout mit großem Hero-Image:
 * - Hero-Bild (full-width)
 * - Datum-Badge (overlay links oben)
 * - Titel
 * - Location
 * - Share + Detail Button
 * 
 * @package ChurchTools_Suite
 * @since   1.1.0.5
 * @version 1.1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get columns setting (default: 3)
$columns = isset( $args['columns'] ) ? intval( $args['columns'] ) : 3;
$columns = max( 1, min( 6, $columns ) ); // Validate 1-6

// Display Options
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_images = isset( $args['show_images'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;
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
	'cts-grid-classic',
];
if ( ! empty( $args['class'] ) ) {
	$wrapper_classes[] = esc_attr( $args['class'] );
}

// Helper: Get image URL
if ( ! function_exists( 'cts_get_event_image_url' ) ) {
	function cts_get_event_image_url( $event ) {
		// Priority: event_image > appointment_image > calendar_image
		if ( ! empty( $event['event_image_url'] ) ) {
			return $event['event_image_url'];
		}
		if ( ! empty( $event['appointment_image_url'] ) ) {
			return $event['appointment_image_url'];
		}
		if ( ! empty( $event['calendar_image_url'] ) ) {
			return $event['calendar_image_url'];
		}
		// Fallback placeholder
		return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="800" height="400"%3E%3Crect fill="%23e5e7eb" width="800" height="400"/%3E%3Ctext fill="%236b7280" font-family="sans-serif" font-size="24" x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle"%3EKein Bild%3C/text%3E%3C/svg%3E';
	}
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-columns="<?php echo esc_attr( $columns ); ?>"
	data-show-images="<?php echo $show_images ? '1' : '0'; ?>"
	data-image-fit="<?php echo esc_attr( $image_fit ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		<p class="cts-no-events"><?php esc_html_e( 'Keine Events gefunden.', 'churchtools-suite' ); ?></p>
	<?php else : ?>
		
		<div class="cts-grid-classic-rows">
			<?php 
			$events_count = is_array( $events ) ? count( $events ) : 0;
			$columns_effective = max( 1, min( $columns, $events_count ) );
			$chunks = array_chunk( $events, max( 1, $columns ) );
			foreach ( $chunks as $chunk ) : 
				$row_cols = min( $columns_effective, count( $chunk ) );
			?>
			<div class="cts-grid-classic-row" style="--row-columns: <?php echo esc_attr( $row_cols ); ?>;">
			<?php foreach ( $chunk as $event ) : 
				// Start timestamp
				$start_ts = current_time( 'timestamp' );
				$time_format = get_option( 'time_format' );
				$has_ampm = ( strpos( $time_format, 'a' ) !== false || strpos( $time_format, 'A' ) !== false );
				$time_suffix = $has_ampm ? '' : ' Uhr';
				if ( ! empty( $event['start_datetime'] ) ) {
					try {
						$dt = new DateTime( $event['start_datetime'], new DateTimeZone( 'UTC' ) );
						$dt->setTimezone( $wp_timezone );
						$start_ts = $dt->getTimestamp();
					} catch ( Exception $e ) {
						$start_ts = current_time( 'timestamp' );
					}
				}
				$start_time_display = wp_date( $time_format, $start_ts, $wp_timezone ) . $time_suffix;
				
				// End timestamp
				$end_time_display = '';
				if ( ! empty( $event['end_datetime'] ) ) {
					try {
						$dt_end = new DateTime( $event['end_datetime'], new DateTimeZone( 'UTC' ) );
						$dt_end->setTimezone( $wp_timezone );
						$end_ts = $dt_end->getTimestamp();
						$end_time_display = wp_date( $time_format, $end_ts, $wp_timezone ) . $time_suffix;
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
							'ctse_context' => 'elementor',
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
				
				// Card style
				$card_style = '';
				if ( $use_calendar_colors ) {
					$card_style = sprintf( ' style="--calendar-color: %s;"', esc_attr( $calendar_color ) );
				}
				
				// Image URL
				$image_url = cts_get_event_image_url( $event );
				?>
				
				<div class="cts-grid-classic-card <?php echo esc_attr( $event_class ); ?>" 
				     <?php echo $event_attrs; ?>
				     <?php echo $card_style; ?>>  
					
					<!-- Hero Image -->
					<?php if ( $show_images ) : ?>
						<div class="cts-classic-hero">
							<img src="<?php echo esc_url( $image_url ); ?>" 
							     alt="<?php echo esc_attr( $event['title'] ); ?>"
							     class="cts-classic-hero-img">
							
							<!-- Datum Badge (Overlay) -->
							<div class="cts-classic-date-badge">
								<div class="cts-classic-date-day">
									<?php echo esc_html( wp_date( 'd', $start_ts, $wp_timezone ) ); ?>
								</div>
								<div class="cts-classic-date-month">
									<?php echo esc_html( strtoupper( wp_date( 'M', $start_ts, $wp_timezone ) ) ); ?>
								</div>
							</div>
							
							<!-- Calendar Badge (top-right) -->
							<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
								<div class="cts-classic-calendar-badge">
									<?php echo esc_html( $event['calendar_name'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
					<!-- Card Content -->
					<div class="cts-classic-content">
						<h3 class="cts-classic-title"><?php echo esc_html( $event['title'] ); ?></h3>
						
						<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
							<div class="cts-classic-location">
								<span class="dashicons dashicons-location"></span>
								<?php echo esc_html( $event['location_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_time ) : ?>
							<div class="cts-classic-time">
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
					</div>
					
					<!-- Card Footer (Actions) -->
					<div class="cts-classic-footer">
						<!-- Share Button -->
						<button class="cts-classic-share" onclick="event.stopPropagation();" title="<?php esc_attr_e( 'Teilen', 'churchtools-suite' ); ?>">
							<span class="dashicons dashicons-share"></span>
						</button>
						
						<!-- Detail Button -->
						<?php if ( $event_action === 'modal' ) : ?>
							<button class="cts-classic-detail">
								<?php esc_html_e( 'Details ansehen', 'churchtools-suite' ); ?>
							</button>
						<?php elseif ( $event_action === 'page' ) : ?>
							<a href="<?php echo esc_url( $click_url ); ?>" class="cts-classic-detail">
								<?php esc_html_e( 'Details ansehen', 'churchtools-suite' ); ?>
							</a>
						<?php else : ?>
							<span class="cts-classic-detail cts-disabled">
								<?php esc_html_e( 'Details ansehen', 'churchtools-suite' ); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
		
	<?php endif; ?>
</div>
