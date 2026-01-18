<?php
/**
 * List View - Minimal
 *
 * Ultra-kompakte Liste: Nur Datum, Uhrzeit und Titel
 * Schmale, niedrige Boxen ohne Zusatzinformationen
 *
 * @package ChurchTools_Suite
 * @since   0.9.6.27
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// v0.9.6.27: Minimal view - only month separator is configurable
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// v0.9.9.10: Parse use_calendar_colors and show_calendar_name
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false; // v0.9.9.12: Default false (minimal soll kompakt bleiben)

// v0.9.6.27: Style mode and custom colors
$style_mode = $args['style_mode'] ?? 'theme';

// WP timezone for consistent date/time (AM/PM or Uhr handled via time_format)
$wp_timezone = wp_timezone();
$custom_styles = '';

if ( $style_mode === 'plugin' ) {
	$primary = '#2563eb';
	$text = '#1e293b';
	$bg = '#ffffff';
	$border_radius = 6;
	$font_size = 14;
	$padding = 12;
	$spacing = 8;
	
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
	$border_radius = $args['custom_border_radius'] ?? 6;
	$font_size = $args['custom_font_size'] ?? 14;
	$padding = $args['custom_padding'] ?? 12;
	$spacing = $args['custom_spacing'] ?? 8;
	
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

// Track current month for separator
$current_month = null;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list-minimal" 
		data-view="list-minimal">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3>Keine Termine gefunden</h3>
			<p>Es gibt aktuell keine Termine in diesem Zeitraum.</p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php 
			// WP-timezone aware start/end timestamps
			$start_ts = current_time( 'timestamp' );
			$end_ts = null;
			if ( ! empty( $event['start_datetime'] ) ) {
				try {
					$dt = new DateTime( $event['start_datetime'], new DateTimeZone( 'UTC' ) );
					$dt->setTimezone( $wp_timezone );
					$start_ts = $dt->getTimestamp();
				} catch ( Exception $e ) {
					$start_ts = current_time( 'timestamp' );
				}
			}
			if ( ! empty( $event['end_datetime'] ) ) {
				try {
					$end_dt = new DateTime( $event['end_datetime'], new DateTimeZone( 'UTC' ) );
					$end_dt->setTimezone( $wp_timezone );
					$end_ts = $end_dt->getTimestamp();
				} catch ( Exception $e ) {
					$end_ts = null;
				}
			}
			$start_date_display = wp_date( 'j. M', $start_ts, $wp_timezone );
			$start_time_display = wp_date( get_option( 'time_format' ), $start_ts, $wp_timezone );
			$end_time_display = $end_ts ? wp_date( get_option( 'time_format' ), $end_ts, $wp_timezone ) : '';

			// Month separator logic
			$event_month = wp_date( 'Y-m', $start_ts, $wp_timezone );
			if ( $show_month_separator && ( $current_month === null || $current_month !== $event_month ) ) : 
				$current_month = $event_month;
			?>
				<div class="cts-month-separator">
					<span class="cts-month-name"><?php echo esc_html( wp_date( 'F Y', $start_ts, $wp_timezone ) ); ?></span>
				</div>
			<?php endif; ?>
			
			<?php 
			// Event action logic
			$event_action = isset( $args['event_action'] ) ? $args['event_action'] : 'modal';

			// Single event target for page clicks
			$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
			$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );
			
			$click_class = '';
			$click_attrs = '';
			
			if ( $event_action === 'modal' ) {
				$click_class = 'cts-event-clickable';
				$click_attrs = sprintf(
					'data-event-id="%s" role="button" tabindex="0" aria-label="%s"',
					esc_attr( $event['id'] ),
					esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) )
				);
			} elseif ( $event_action === 'page' ) {
				$click_class = 'cts-event-page-link';
				$page_url = add_query_arg(
					[
						'event_id' => $event['id'],
						'template' => $single_event_template,
						'ctse_context' => 'elementor',
					],
					$single_event_base
				);
				$click_attrs = sprintf(
					'data-event-id="%s" data-event-url="%s" role="link" tabindex="0" aria-label="%s"',
					esc_attr( $event['id'] ),
					esc_url( $page_url ),
					esc_attr( sprintf( __( 'Zu %s navigieren', 'churchtools-suite' ), $event['title'] ) )
				);
			}
			?>
			
			<?php 
			// v0.9.9.10: Inline-Styles für Kalenderfarbe
			$event_style = '';
			$calendar_color = $event['calendar_color'] ?? '#2563eb';
			if ( $use_calendar_colors ) {
				// Hintergrund in Kalenderfarbe (mit Transparenz)
				$event_style = sprintf(
					'background: linear-gradient(135deg, %1$s15 0%%, %1$s08 100%%); border-left: 3px solid %1$s;',
					esc_attr( $calendar_color )
				);
			}
			?>
			<div class="cts-event-minimal <?php echo esc_attr( $click_class ); ?>" <?php echo $click_attrs; ?><?php echo $event_style ? ' style="' . $event_style . '"' : ''; ?>>
				
				<!-- Kalendername (v0.9.9.20: Farbe nur bei use_calendar_colors=true) -->
				<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
					$calendar_name_style = '';
					if ( $use_calendar_colors ) {
						$calendar_name_style = sprintf( ' style="color: %s; font-weight: 600;"', esc_attr( $calendar_color ) );
					}
				?>
					<div class="cts-calendar-name-minimal"<?php echo $calendar_name_style; ?>>
						<?php echo esc_html( $event['calendar_name'] ); ?>
					</div>
				<?php endif; ?>
				
				<!-- Datum + Uhrzeit (Text-Format, v0.9.6.37) -->
				<div class="cts-datetime-minimal">
					<span class="cts-date-text"><?php echo esc_html( $start_date_display ); ?></span>
					<span class="cts-time-separator"> • </span>
					<span class="cts-time-text">
						<?php echo esc_html( $start_time_display ); ?>
						<?php if ( ! empty( $end_time_display ) ) : ?>
							- <?php echo esc_html( $end_time_display ); ?>
						<?php endif; ?>
					</span>
				</div>
				
				<!-- Titel (single line, ellipsis on overflow) -->
				<div class="cts-title-minimal">
					<?php echo esc_html( $event['title'] ); ?>
				</div>
				
				<!-- Location Info Icon (v0.9.6.32) -->
				<?php
				$info_parts = array_filter( [
					$event['address_name'] ?? '',
					$event['address_street'] ?? '',
					$event['address_zip'] ?? '',
					$event['address_city'] ?? ''
				] );
				if ( ! empty( $info_parts ) ) {
					$info_text = implode( ', ', $info_parts );
					?>
					<span class="cts-location-info-icon" data-tooltip="<?php echo esc_attr( $info_text ); ?>">
						<span class="dashicons dashicons-location"></span>
					</span>
					<?php
				}
				?>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
	</div><!-- /.cts-list-minimal -->
</div><!-- /.churchtools-suite-wrapper -->
