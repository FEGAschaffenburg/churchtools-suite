<?php
/**
 * Calendar View - Monthly Simple
 * 
 * Klassischer Monatskalender mit Event-Markern und Tooltip
 * 
 * @package ChurchTools_Suite
 * @since   0.9.8.0
 * @version 0.9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current month/year from URL or use current date
$current_month = isset( $_GET['cts_month'] ) ? intval( $_GET['cts_month'] ) : date( 'n' );
$current_year = isset( $_GET['cts_year'] ) ? intval( $_GET['cts_year'] ) : date( 'Y' );

// Validate month/year
$current_month = max( 1, min( 12, $current_month ) );
$current_year = max( 2020, min( 2030, $current_year ) );

// Calculate previous/next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ( $prev_month < 1 ) {
	$prev_month = 12;
	$prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ( $next_month > 12 ) {
	$next_month = 1;
	$next_year++;
}

// Get first and last day of month
$first_day = sprintf( '%04d-%02d-01', $current_year, $current_month );
$last_day = date( 'Y-m-t', strtotime( $first_day ) );

// Get day of week for first day (1=Monday, 7=Sunday)
$first_weekday = date( 'N', strtotime( $first_day ) );

// Calculate calendar grid start (Monday of first week)
$grid_start = date( 'Y-m-d', strtotime( $first_day . ' -' . ( $first_weekday - 1 ) . ' days' ) );

// Get events and group by day
$events_by_day = [];
if ( ! empty( $events ) ) {
	foreach ( $events as $event ) {
		// Convert UTC to WordPress timezone
		$day = get_date_from_gmt( $event['start_datetime'], 'Y-m-d' );
		
		// Only include events in displayed month (allow some overlap for grid)
		$event_month = date( 'n', strtotime( $day ) );
		$event_year = date( 'Y', strtotime( $day ) );
		
		if ( ! isset( $events_by_day[ $day ] ) ) {
			$events_by_day[ $day ] = [];
		}
		
		$events_by_day[ $day ][] = $event;
	}
}

// Event-Action Parameter
$event_action = $args['event_action'] ?? 'modal';
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Style-Mode unterstützen
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'plugin' ) {
	// Default plugin colors
	$custom_styles = '--cts-primary-color: #2563eb; --cts-text-color: #1e293b; --cts-bg-color: #ffffff; --cts-border-radius: 6px; --cts-font-size: 14px; --cts-padding: 12px; --cts-spacing: 8px;';
} elseif ( $style_mode === 'custom' ) {
	$primary = $args['custom_primary_color'] ?? '#2563eb';
	$text = $args['custom_text_color'] ?? '#1e293b';
	$bg = $args['custom_background_color'] ?? '#ffffff';
	$border_radius = $args['custom_border_radius'] ?? 6;
	$font_size = $args['custom_font_size'] ?? 14;
	$padding = $args['custom_padding'] ?? 12;
	$spacing = $args['custom_spacing'] ?? 8;
	
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $primary ),
		esc_attr( $text ),
		esc_attr( $bg ),
		intval( $border_radius ),
		intval( $font_size ),
		intval( $padding ),
		intval( $spacing )
	);
}

// Get current page URL for navigation
$current_url = add_query_arg( [], remove_query_arg( [ 'cts_month', 'cts_year' ] ) );

// Month names (localized)
$month_name = date_i18n( 'F Y', strtotime( $first_day ) );

// Weekday names (localized, short)
$weekdays = [
	__( 'Mo', 'churchtools-suite' ),
	__( 'Di', 'churchtools-suite' ),
	__( 'Mi', 'churchtools-suite' ),
	__( 'Do', 'churchtools-suite' ),
	__( 'Fr', 'churchtools-suite' ),
	__( 'Sa', 'churchtools-suite' ),
	__( 'So', 'churchtools-suite' ),
];

?>

<div class="cts-calendar-monthly-simple cts-style-<?php echo esc_attr( $style_mode ); ?>" 
     style="<?php echo esc_attr( $custom_styles ); ?>" 
     data-event-action="<?php echo esc_attr( $event_action ); ?>">
	
	<!-- Calendar Header with Navigation -->
	<div class="cts-calendar-header">
		<a href="<?php echo esc_url( add_query_arg( [ 'cts_month' => $prev_month, 'cts_year' => $prev_year ], $current_url ) ); ?>" 
		   class="cts-calendar-nav cts-calendar-prev">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
		</a>
		
		<h2 class="cts-calendar-title"><?php echo esc_html( $month_name ); ?></h2>
		
		<a href="<?php echo esc_url( add_query_arg( [ 'cts_month' => $next_month, 'cts_year' => $next_year ], $current_url ) ); ?>" 
		   class="cts-calendar-nav cts-calendar-next">
			<span class="dashicons dashicons-arrow-right-alt2"></span>
		</a>
	</div>
	
	<!-- Weekday Header -->
	<div class="cts-calendar-weekdays">
		<?php foreach ( $weekdays as $weekday ) : ?>
			<div class="cts-calendar-weekday"><?php echo esc_html( $weekday ); ?></div>
		<?php endforeach; ?>
	</div>
	
	<!-- Calendar Grid (dynamic weeks: 5–6 × 7 days) -->
	<div class="cts-calendar-grid">
		<?php
		$current_date = $grid_start;
		$today = date( 'Y-m-d' );
		
		// Determine number of weeks to render for this month
		$days_in_month = (int) date( 't', strtotime( $first_day ) );
		$weeks = (int) ceil( ( $first_weekday - 1 + $days_in_month ) / 7 );
		// Ensure a minimum of 5 weeks and a maximum of 6 weeks
		$weeks = max( 5, min( 6, $weeks ) );
		$total_cells = $weeks * 7;
		
		for ( $i = 0; $i < $total_cells; $i++ ) :
			$day_number = date( 'j', strtotime( $current_date ) );
			$is_current_month = date( 'n', strtotime( $current_date ) ) == $current_month;
			$is_today = $current_date === $today;
			$has_events = isset( $events_by_day[ $current_date ] );
			
			// Cell classes
			$cell_classes = [ 'cts-calendar-day-cell' ];
			if ( ! $is_current_month ) {
				$cell_classes[] = 'cts-other-month';
			}
			if ( $is_today ) {
				$cell_classes[] = 'cts-today';
			}
			if ( $has_events ) {
				$cell_classes[] = 'cts-has-events';
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', $cell_classes ) ); ?>" 
			     data-date="<?php echo esc_attr( $current_date ); ?>">
				
				<!-- Day Number -->
				<div class="cts-day-number"><?php echo esc_html( $day_number ); ?></div>
				
				<!-- Event Markers -->
				<?php if ( $has_events ) : ?>
					<div class="cts-event-markers">
						<?php
						$day_events = array_slice( $events_by_day[ $current_date ], 0, 3 ); // Max 3 visible
						$remaining = count( $events_by_day[ $current_date ] ) - 3;
						
						foreach ( $day_events as $event ) :
							$calendar_color = $event['calendar_color'] ?? '#2563eb';
				$event_time = get_date_from_gmt( $event['start_datetime'], get_option( 'time_format' ) );
							$event_attrs = '';
							$event_classes = 'cts-event-marker';
							
							// v0.9.9.10: Event-Marker nutzt bereits --calendar-color für Hintergrund
							
							if ( $event_action === 'modal' ) {
								$event_classes .= ' cts-event-clickable';
								$event_attrs = sprintf(
									'data-event-id="%s" data-event-title="%s" data-event-start="%s" data-event-location="%s" data-event-description="%s"',
									esc_attr( $event['id'] ),
									esc_attr( $event['title'] ),
									esc_attr( $event['start_datetime'] ),
									esc_attr( $event['location_name'] ?? '' ),
									esc_attr( wp_trim_words( $event['event_description'] ?? '', 50 ) )
								);
							} elseif ( $event_action === 'page' ) {
								$event_classes .= ' cts-event-page-link';
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
							// else: event_action === 'none' → no click functionality
							
							// v0.9.9.17: Only set calendar color if use_calendar_colors is enabled
							$event_style = '';
							if ( $use_calendar_colors ) {
								$event_style = sprintf(
									'style="--calendar-color: %s; --cts-primary-color: %s;"',
									esc_attr( $calendar_color ),
									esc_attr( $calendar_color )
								);
							}
							?>
							<div class="<?php echo esc_attr( $event_classes ); ?>" 
							     <?php echo $event_style; ?>
							     <?php echo $event_attrs; ?>>
								<span class="cts-event-time"><?php echo esc_html( $event_time ); ?></span>
								<span class="cts-event-title-short"><?php echo esc_html( wp_trim_words( $event['title'], 3, '...' ) ); ?></span>
								
								<!-- Tooltip -->
								<div class="cts-event-tooltip">
									<div class="cts-tooltip-title"><?php echo esc_html( $event['title'] ); ?></div>
									<div class="cts-tooltip-time">
										<span class="dashicons dashicons-clock"></span>
										<?php echo esc_html( $event_time ); ?>
									</div>
									<?php if ( ! empty( $event['location_name'] ) ) : ?>
										<div class="cts-tooltip-location">
											<span class="dashicons dashicons-location"></span>
											<?php echo esc_html( $event['location_name'] ); ?>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $event['event_description'] ) ) : ?>
										<div class="cts-tooltip-description">
											<?php echo esc_html( wp_trim_words( $event['event_description'], 20 ) ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
						
						<?php if ( $remaining > 0 ) : ?>
							<div class="cts-more-events">
								+<?php echo esc_html( $remaining ); ?> <?php esc_html_e( 'mehr', 'churchtools-suite' ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
			// Move to next day
			$current_date = date( 'Y-m-d', strtotime( $current_date . ' +1 day' ) );
		endfor;
		?>
	</div>
</div>
