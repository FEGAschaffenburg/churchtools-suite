<?php
/**
 * Grid View - Simple
 * 
 * Card-basiertes Grid-Layout mit konfigurierbarer Spaltenanzahl
 * Analog zu Liste Classic mit allen Display-Options
 * 
 * @package ChurchTools_Suite
 * @since   0.9.9.0
 * @version 0.9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get columns setting (default: 3)
$columns = isset( $args['columns'] ) ? intval( $args['columns'] ) : 3;
$columns = max( 1, min( 6, $columns ) ); // Validate 1-6

// Display Options (analog zu Liste Classic) - v0.9.9.3: Boolean parsing hinzugefügt
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$event_action = $args['event_action'] ?? 'modal';

// Single event target for page clicks
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Use WordPress timezone for all date/time outputs
$wp_timezone = wp_timezone();

// Style-Mode unterstützen
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'plugin' ) {
	// Default plugin colors
	$custom_styles = '--cts-primary-color: #2563eb; --cts-text-color: #1e293b; --cts-bg-color: #ffffff; --cts-border-radius: 6px; --cts-font-size: 14px; --cts-padding: 12px; --cts-spacing: 16px;';
} elseif ( $style_mode === 'custom' ) {
	$primary = $args['custom_primary_color'] ?? '#2563eb';
	$text = $args['custom_text_color'] ?? '#1e293b';
	$bg = $args['custom_background_color'] ?? '#ffffff';
	$border_radius = $args['custom_border_radius'] ?? 6;
	$font_size = $args['custom_font_size'] ?? 14;
	$padding = $args['custom_padding'] ?? 12;
	$spacing = $args['custom_spacing'] ?? 16;
	
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
	'cts-grid-simple',
	'cts-style-' . esc_attr( $style_mode ),
];
if ( ! empty( $args['class'] ) ) {
	$wrapper_classes[] = esc_attr( $args['class'] );
}
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     style="<?php echo esc_attr( $custom_styles ); ?>" 
     data-columns="<?php echo esc_attr( $columns ); ?>"
     data-show-event-description="<?php echo $show_event_description ? '1' : '0'; ?>"
     data-show-appointment-description="<?php echo $show_appointment_description ? '1' : '0'; ?>"
     data-show-location="<?php echo $show_location ? '1' : '0'; ?>"
     data-show-services="<?php echo $show_services ? '1' : '0'; ?>"
     data-show-time="<?php echo $show_time ? '1' : '0'; ?>"
     data-show-tags="<?php echo $show_tags ? '1' : '0'; ?>"
     data-show-calendar-name="<?php echo $show_calendar_name ? '1' : '0'; ?>">
	
	<?php if ( empty( $events ) ) : ?>
		<p class="cts-no-events"><?php esc_html_e( 'Keine Events gefunden.', 'churchtools-suite' ); ?></p>
	<?php else : ?>
		
		<div class="cts-grid-rows">
			<?php 
			$events_count = is_array( $events ) ? count( $events ) : 0;
			$columns_effective = max( 1, min( $columns, $events_count ) );
			$chunks = array_chunk( $events, max( 1, $columns ) );
			foreach ( $chunks as $chunk ) : 
				$row_cols = min( $columns_effective, count( $chunk ) );
			?>
			<div class="cts-grid-row" style="--row-columns: <?php echo esc_attr( $row_cols ); ?>;">
			<?php foreach ( $chunk as $event ) : 
				// WP-timezone aware start timestamp
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
				$start_date_display = wp_date( get_option( 'date_format' ), $start_ts, $wp_timezone );
				$start_time_display = wp_date( get_option( 'time_format' ), $start_ts, $wp_timezone );
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
				
				// Tooltip mit wichtigsten Daten
				$tooltip_parts = [];
				$tooltip_parts[] = $start_date_display;
				if ( $show_time ) {
					$tooltip_parts[] = $start_time_display;
				}
				if ( ! empty( $event['location_name'] ) ) {
					$tooltip_parts[] = $event['location_name'];
				}
				$tooltip = implode( ' | ', $tooltip_parts );
				
				// Calendar color for accent
				$calendar_color = $event['calendar_color'] ?? '#2563eb';
				
				// v0.9.9.20: Inline-Styles nur bei use_calendar_colors=true
				$event_inline_style = '';
				if ( $use_calendar_colors ) {
					$event_inline_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"',
						esc_attr( $calendar_color ),
						esc_attr( $calendar_color )
					);
				}
				?>
				
				<div class="cts-grid-card <?php echo esc_attr( $event_class ); ?>" 
				     <?php echo $event_attrs; ?>
				     title="<?php echo esc_attr( $tooltip ); ?>"
				     <?php echo $event_inline_style; ?>>  
					
					<!-- Card Header mit Datum (v0.9.9.10: Hintergrund in Kalenderfarbe wenn aktiv) -->
					<?php 
					$header_style = '';
					if ( $use_calendar_colors ) {
						$header_style = sprintf( 'background: linear-gradient(135deg, %1$s 0%%, %1$sdd 100%%); color: #ffffff;', esc_attr( $calendar_color ) );
					}
					?>
					<div class="cts-card-header"<?php echo $header_style ? ' style="' . $header_style . '"' : ''; ?>>
						<div class="cts-card-date">
							<div class="cts-date-day">
								<?php echo esc_html( wp_date( 'd', $start_ts, $wp_timezone ) ); ?>
							</div>
							<div class="cts-date-month">
								<?php echo esc_html( wp_date( 'M', $start_ts, $wp_timezone ) ); ?>
							</div>
						</div>
						<?php if ( $show_time ) : ?>
							<div class="cts-card-time">
								<span class="dashicons dashicons-clock"></span>
								<?php echo esc_html( $start_time_display ); ?>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Card Body -->
					<div class="cts-card-body">
					<!-- Kalendername (v0.9.9.20: Farbe nur bei use_calendar_colors=true) -->
					<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
						$calendar_name_style = '';
						if ( $use_calendar_colors ) {
							$calendar_name_style = sprintf( ' style="color: %s; font-weight: 600; font-size: 0.85em; margin-bottom: 6px;"', esc_attr( $calendar_color ) );
						}
					?>
						<div class="cts-calendar-name-grid"<?php echo $calendar_name_style; ?>>
								<?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php 
						$title_style = '';
						if ( $use_calendar_colors ) {
							$title_style = sprintf( 'color: %s;', esc_attr( $calendar_color ) );
						}
						?>
						<h3 class="cts-card-title"<?php echo $title_style ? ' style="' . $title_style . '"' : ''; ?>><?php echo esc_html( $event['title'] ); ?></h3>
						
						<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
							<div class="cts-location">
								<span class="dashicons dashicons-location"></span>
								<?php echo esc_html( $event['location_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
							<div class="cts-event-description">
								<?php echo wp_kses_post( wpautop( wp_trim_words( $event['event_description'], 20 ) ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
							<div class="cts-appointment-description">
								<?php echo wp_kses_post( wpautop( wp_trim_words( $event['appointment_description'], 20 ) ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_tags && ! empty( $event['tags'] ) ) : 
							$tags = json_decode( $event['tags'], true );
							if ( is_array( $tags ) && ! empty( $tags ) ) : ?>
								<div class="cts-tags">
									<?php foreach ( $tags as $tag ) : 
										$tag_color = $tag['color'] ?? '#6b7280';
										?>
										<span class="cts-tag" style="background-color: <?php echo esc_attr( $tag_color ); ?>;">
											<?php echo esc_html( $tag['name'] ?? '' ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-services">
								<strong><?php esc_html_e( 'Dienste:', 'churchtools-suite' ); ?></strong>
								<ul class="cts-services-list">
									<?php foreach ( $event['services'] as $service ) : ?>
										<li>
											<span class="cts-service-name"><?php echo esc_html( $service['service_name'] ?? '' ); ?>:</span>
											<span class="cts-person-name"><?php echo esc_html( $service['person_name'] ?? __( 'Offen', 'churchtools-suite' ) ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Card Footer (Calendar Badge) -->
					<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
						<div class="cts-card-footer">
							<span class="cts-calendar-badge" style="background-color: <?php echo esc_attr( $calendar_color ); ?>;">
								<?php echo esc_html( $event['calendar_name'] ); ?>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
		
	<?php endif; ?>
</div>
