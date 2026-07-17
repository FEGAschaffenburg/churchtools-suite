<?php
/**
 * Grid View - Minimal
 * 
 * Minimalistisches Card-Layout mit nur den wichtigsten Infos:
 * - Datum
 * - Titel
 * - Ort ODER Beschreibung (eines prioritär)
 * - Info-Icon für weitere Details (Tooltip)
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

// Minimal View: Nur Basis-Anzeige, Rest in Info-Tooltip
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;

$event_action = $args['event_action'] ?? 'modal';

// Single event target for page clicks
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// Use WordPress timezone for all date/time outputs
$wp_timezone = wp_timezone();

// Calendar colors
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : true; // Minimal: default colors=true

// Build event classes based on event_action
$event_class = '';
if ( $event_action === 'modal' ) {
	$event_class = 'cts-event-clickable';
} elseif ( $event_action === 'page' ) {
	$event_class = 'cts-event-page-link';
}

// Wrapper classes
$wrapper_classes = [
	'churchtools-suite-wrapper',
	'cts-grid-minimal',
];
if ( ! empty( $args['class'] ) ) {
	$wrapper_classes[] = esc_attr( $args['class'] );
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-columns="<?php echo esc_attr( $columns ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		<p class="cts-no-events"><?php esc_html_e( 'Keine Events gefunden.', 'churchtools-suite' ); ?></p>
	<?php else : ?>
		
		<div class="cts-grid-minimal-rows">
			<?php 
			$events_count = is_array( $events ) ? count( $events ) : 0;
			$columns_effective = max( 1, min( $columns, $events_count ) );
			$chunks = array_chunk( $events, max( 1, $columns ) );
			foreach ( $chunks as $chunk ) : 
				$row_cols = min( $columns_effective, count( $chunk ) );
			?>
			<div class="cts-grid-minimal-row" style="--row-columns: <?php echo esc_attr( $row_cols ); ?>;">
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
				$start_date_display = wp_date( get_option( 'date_format' ), $start_ts, $wp_timezone );
				$start_time_display = wp_date( $time_format, $start_ts, $wp_timezone ) . $time_suffix;
				
				// End timestamp
				$end_ts = null;
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
					$page_url = add_query_arg(
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
						esc_url( $page_url )
					);
				}
				
				// Calendar color
				$calendar_color = $event['calendar_color'] ?? '#2563eb';
				
				// Inline-Styles für Kalenderfarbe
				$card_style = '';
				if ( $use_calendar_colors ) {
					$card_style = sprintf( ' style="--calendar-color: %s;"', esc_attr( $calendar_color ) );
				}
				
				// Info-Tooltip Content: Alles was NICHT direkt angezeigt wird
				$info_tooltip = [];
				
				// Zeit
				if ( $show_time ) {
					$zeit_str = $start_time_display;
					if ( ! empty( $end_time_display ) ) {
						$zeit_str .= ' - ' . $end_time_display;
					}
					$info_tooltip[] = '⏰ ' . $zeit_str;
				}
				
				// Location (wenn aktiviert)
				if ( $show_location && ! empty( $event['location_name'] ) ) {
					$info_tooltip[] = '📍 ' . $event['location_name'];
				}
				
				// Beschreibung (wenn aktiviert und nicht als Haupt-Info)
				if ( $show_event_description && ! $show_location && ! empty( $event['event_description'] ) ) {
					$info_tooltip[] = wp_trim_words( strip_tags( $event['event_description'] ), 15 );
				}
				
				// Kalendername
				if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) {
					$info_tooltip[] = '📅 ' . $event['calendar_name'];
				}
				
				// Tags
				if ( ! empty( $event['tags'] ) ) {
					$tags = json_decode( $event['tags'], true );
					if ( is_array( $tags ) && ! empty( $tags ) ) {
						$tag_names = array_map( fn($t) => $t['name'] ?? '', $tags );
						$info_tooltip[] = '🏷️ ' . implode( ', ', $tag_names );
					}
				}
				
				// Services
				if ( ! empty( $event['services'] ) && is_array( $event['services'] ) ) {
					$service_names = array_map( fn($s) => $s['service_name'] ?? '', $event['services'] );
					$info_tooltip[] = '👥 ' . implode( ', ', $service_names );
				}
				
				$info_tooltip_html = implode( "\n", $info_tooltip );
				
				// Hauptinfo: Location hat Priorität, dann Description
				$main_info = '';
				if ( $show_location && ! empty( $event['location_name'] ) ) {
					$main_info = $event['location_name'];
					$main_info_icon = '📍';
				} elseif ( $show_event_description && ! empty( $event['event_description'] ) ) {
					$main_info = wp_trim_words( strip_tags( $event['event_description'] ), 12 );
					$main_info_icon = '📝';
				}
				?>
				
				<div class="cts-grid-minimal-card <?php echo esc_attr( $event_class ); ?>" 
				     <?php echo $event_attrs; ?>
				     <?php echo $card_style; ?>>  
					
					<!-- Datum Badge (links oben) -->
					<div class="cts-minimal-date">
						<div class="cts-minimal-date-day">
							<?php echo esc_html( wp_date( 'd', $start_ts, $wp_timezone ) ); ?>
						</div>
						<div class="cts-minimal-date-month">
							<?php echo esc_html( wp_date( 'M', $start_ts, $wp_timezone ) ); ?>
						</div>
					</div>
					
					<!-- Info-Icon (rechts oben) -->
					<?php if ( ! empty( $info_tooltip ) ) : ?>
						<div class="cts-minimal-info-icon" title="<?php echo esc_attr( $info_tooltip_html ); ?>">
							<span class="dashicons dashicons-info"></span>
							<div class="cts-minimal-info-popup">
								<?php echo wp_kses_post( nl2br( $info_tooltip_html ) ); ?>
							</div>
						</div>
					<?php endif; ?>
					
					<!-- Card Content -->
					<div class="cts-minimal-content">
						<h3 class="cts-minimal-title"><?php echo esc_html( $event['title'] ); ?></h3>
						
						<?php if ( ! empty( $main_info ) ) : ?>
							<div class="cts-minimal-main-info">
								<span class="cts-minimal-icon"><?php echo $main_info_icon; ?></span>
								<?php echo esc_html( $main_info ); ?>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Calendar Badge (unten) -->
					<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
						<div class="cts-minimal-badge">
							<?php echo esc_html( $event['calendar_name'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
		
	<?php endif; ?>
</div>
