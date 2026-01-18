<?php
/**
 * List View - Modern
 * 
 * Card-basiertes Design mit Hover-Effekten und Kalenderfarbe
 * 
 * @package ChurchTools_Suite
 * @since   0.9.7.0
 * @version 0.9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display-Parameter parsen
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Single event target (new page)
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// Style-Mode unterstützen
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';

if ( $style_mode === 'plugin' ) {
	$primary = '#2563eb';
	$text = '#1e293b';
	$bg = '#ffffff';
	$border_radius = 12;
	$font_size = 15;
	$padding = 20;
	$spacing = 20;
	
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $primary ),
		esc_attr( $text ),
		esc_attr( $bg ),
		absint( $border_radius ),
		absint( $font_size ),
		absint( $padding ),
		absint( $spacing )
	);
} elseif ( $style_mode === 'custom' ) {
	$primary = $args['custom_primary_color'] ?? '#2563eb';
	$text = $args['custom_text_color'] ?? '#1e293b';
	$bg = $args['custom_background_color'] ?? '#ffffff';
	$border_radius = $args['custom_border_radius'] ?? 12;
	$font_size = $args['custom_font_size'] ?? 15;
	$padding = $args['custom_padding'] ?? 20;
	$spacing = $args['custom_spacing'] ?? 20;
	
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $primary ),
		esc_attr( $text ),
		esc_attr( $bg ),
		absint( $border_radius ),
		absint( $font_size ),
		absint( $padding ),
		absint( $spacing )
	);
}

// Datum-Formatter
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );

// Helper: Truncate description
if ( ! function_exists( 'truncate_description' ) ) {
	function truncate_description( $text, $length = 150 ) {
		if ( empty( $text ) ) {
			return '';
		}
		$text = wp_strip_all_tags( $text );
		if ( strlen( $text ) <= $length ) {
			return $text;
		}
		return substr( $text, 0, $length ) . '...';
	}
}
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list-modern" 
		 data-view="list-modern"
		 data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		 data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		 data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		 data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		 data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		 data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		 data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>">

		<?php if ( empty( $events ) ) : ?>
			<div class="cts-empty-state">
				<span class="dashicons dashicons-calendar-alt" style="font-size: 48px; opacity: 0.3;"></span>
				<p><?php esc_html_e( 'Keine Termine gefunden.', 'churchtools-suite' ); ?></p>
			</div>
		<?php else : ?>
			<?php 
			$current_month = '';
			foreach ( $events as $event ) : 
			$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
			$show_separator = $show_month_separator && $event_month !== $current_month;
			$current_month = $event_month;
			
			// Kalenderfarbe holen
			$calendar_color = $event['calendar_color'] ?? '#2563eb';
			
			// Datum-Parts
			$day = get_date_from_gmt( $event['start_datetime'], 'd' );
			$month_short = get_date_from_gmt( $event['start_datetime'], 'M' );
				$event_action = $args['event_action'] ?? 'modal';
				$event_class = 'cts-event-modern';
				$event_attrs = '';
				
				if ( $event_action === 'modal' ) {
					$event_class .= ' cts-event-clickable';
					$event_attrs = sprintf( 
						'data-event-id="%s" data-event-title="%s" data-event-start="%s"',
						esc_attr( $event['id'] ),
						esc_attr( $event['title'] ),
						esc_attr( $event['start_datetime'] )
					);
				} elseif ( $event_action === 'page' ) {
					$event_class .= ' cts-event-page-link';
					$event_url = add_query_arg(
						[
							'event_id' => $event['id'],
							'template' => $single_event_template,
						],
						$single_event_base
					);
					$event_attrs = sprintf(
						'data-event-id="%s" data-event-url="%s"',
						esc_attr( $event['id'] ),
						esc_url( $event_url )
					);
				}
			?>
				
				<?php if ( $show_separator ) : ?>
					<div class="cts-month-separator">
					<span><?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'F Y' ) ); ?></span>
					</div>
				<?php endif; ?>
				
				<?php 
				// v0.9.9.20: Inline-Styles nur bei use_calendar_colors=true
				$event_inline_style = '';
				if ( $use_calendar_colors ) {
					$event_inline_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"',
						esc_attr( $calendar_color ),
						esc_attr( $calendar_color )
					);
				}
				?>
				<div class="<?php echo esc_attr( $event_class ); ?>"<?php echo $event_inline_style; ?> <?php echo $event_attrs; ?>>
					<!-- Datum-Badge mit Kalenderfarbe (v0.9.9.10: Gradient wenn aktiv) -->
					<?php 
					$badge_style = '';
					if ( $use_calendar_colors ) {
						$badge_style = sprintf(
							'background: linear-gradient(135deg, %1$s 0%%, %1$scc 100%%); color: #ffffff; border-color: %1$s;',
							esc_attr( $calendar_color )
						);
					}
					?>
					<div class="cts-date-badge-modern"<?php echo $badge_style ? ' style="' . $badge_style . '"' : ''; ?>>
						<span class="cts-date-day"><?php echo esc_html( $day ); ?></span>
						<span class="cts-date-month"><?php echo esc_html( $month_short ); ?></span>
					</div>
					
					<!-- Content-Card -->
					<div class="cts-event-content-modern">
						<div class="cts-event-header-modern">
						<!-- Kalendername (v0.9.9.20: Farbe nur bei use_calendar_colors=true) -->
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
							$calendar_name_style = '';
							if ( $use_calendar_colors ) {
								$calendar_name_style = sprintf( ' style="color: %s; font-weight: 600; font-size: 0.85em; margin-bottom: 4px;"', esc_attr( $calendar_color ) );
							}
						?>
							<div class="cts-calendar-name-modern"<?php echo $calendar_name_style; ?>>
									<?php echo esc_html( $event['calendar_name'] ); ?>
								</div>
							<?php endif; ?>
							
							<h3 class="cts-title"><?php echo esc_html( $event['title'] ); ?></h3>
							
							<?php if ( $show_time && ! empty( $event['time_display'] ) ) : ?>
								<div class="cts-time">
									<span class="dashicons dashicons-clock"></span>
									<time datetime="<?php echo esc_attr( $event['start_datetime'] ); ?>">
										<?php echo esc_html( $event['time_display'] ); ?>
									</time>
								</div>
							<?php endif; ?>
						</div>
						
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
							<div class="cts-description cts-event-description">
								<?php echo esc_html( truncate_description( $event['event_description'], 180 ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
							<div class="cts-description cts-appointment-description">
								<?php echo esc_html( truncate_description( $event['appointment_description'], 180 ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
							<div class="cts-location">
								<span class="dashicons dashicons-location"></span>
								<?php echo esc_html( $event['location_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-services-modern">
								<?php foreach ( $event['services'] as $service ) : ?>
									<div class="cts-service-pill">
										<span class="cts-service-name"><?php echo esc_html( $service['service_name'] ); ?></span>
										<?php if ( ! empty( $service['person_name'] ) ) : ?>
											<span class="cts-service-person"><?php echo esc_html( $service['person_name'] ); ?></span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_tags && ! empty( $event['tags_array'] ) ) : ?>
							<div class="cts-tags-modern">
								<?php foreach ( $event['tags_array'] as $tag ) : ?>
									<span class="cts-tag-badge" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
										<?php echo esc_html( $tag['name'] ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-calendar-badge" style="color: <?php echo esc_attr( $calendar_color ); ?>;">
								<?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

