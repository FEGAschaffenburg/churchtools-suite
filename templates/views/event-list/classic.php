<?php
/**
 * List View - Classic
 *
 * Kompakte einzeilige Liste - alles in einer schmalen Zeile
 *
 * @package ChurchTools_Suite
 * @since   0.6.3.12
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Sprint 3: Parse boolean parameters (support strings from Gutenberg attributes)
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// v0.9.6.0: Style mode and custom colors
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';

// Plugin style mode uses default plugin colors
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

// v0.9.2.1: Track current month for separator
$current_month = null;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list-classic" 
		data-view="list-classic"
		data-show-event-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php 
			// v0.9.4.3: Month separator logic (fixed)
		$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
			if ( $show_month_separator && ( $current_month === null || $current_month !== $event_month ) ) : 
				$current_month = $event_month; // Update BEFORE separator output
			?>
				<div class="cts-month-separator">
				<span class="cts-month-name"><?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'F Y' ) ); ?></span>
				</div>
			<?php endif; ?>
			<?php 
			// v0.9.6.14: Event action logic (modal, page, none)
			$event_action = isset( $args['event_action'] ) ? $args['event_action'] : 'modal';

			// Single event target for page clicks
			$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
			$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );
			

		
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
<div class="cts-event-classic <?php echo esc_attr( $click_class ); ?>" <?php echo $click_attrs; ?><?php
	// v0.9.9.2: Use calendar color if option enabled
	if ( $use_calendar_colors && ! empty( $event['calendar_color'] ) ) {
		echo ' style="--calendar-color: ' . esc_attr( $event['calendar_color'] ) . '; --cts-primary-color: ' . esc_attr( $event['calendar_color'] ) . ';"';
	}
?>>
	
	<!-- Datum Box (v0.9.9.10: Hintergrund in Kalenderfarbe wenn aktiv) -->
	<?php 
	$date_box_style = '';
	$calendar_color = $event['calendar_color'] ?? '#2563eb';
	if ( $use_calendar_colors ) {
		// v0.9.9.14: Intelligente Textfarbe basierend auf Hintergrund-Helligkeit
		// Berechne Luminanz der Farbe (0-255)
		$hex = ltrim( $calendar_color, '#' );
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		
		// Relative Luminanz nach W3C-Formel
		$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b );
		
		// Wähle Textfarbe: Dunkel für helle Hintergründe (> 128), Hell für dunkle
		$text_color = ( $luminance > 128 ) ? '#1e293b' : '#ffffff';
		
		$date_box_style = sprintf( 'background-color: %s; color: %s;', esc_attr( $calendar_color ), esc_attr( $text_color ) );
	}
	?>
	<div class="cts-date-box"<?php echo $date_box_style ? ' style="' . $date_box_style . '"' : ''; ?>>
		<div class="cts-date-month"><?php echo esc_html( $event['start_month'] ); ?></div>
		<div class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></div>
		<div class="cts-date-weekday"><?php echo esc_html( strtoupper( $event['start_weekday'] ) ); ?></div>
	</div>
	
	<!-- Uhrzeit (Von-Bis) -->
	<?php if ( $show_time ) : ?>
		<div class="cts-time">
			<?php echo esc_html( $event['start_time'] ); ?>
			<?php if ( ! empty( $event['end_time'] ) ) : ?>
				- <?php echo esc_html( $event['end_time'] ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
	<!-- Kalender-Name (v0.9.9.20: Farbe nur bei use_calendar_colors=true) -->
	<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
		$calendar_name_style = '';
		if ( $use_calendar_colors ) {
			$calendar_name_style = sprintf( ' style="color: %s; font-weight: 600;"', esc_attr( $calendar_color ) );
		}
	?>
		<div class="cts-calendar-name"<?php echo $calendar_name_style; ?>>
			<?php echo esc_html( $event['calendar_name'] ); ?>
		</div>
	<?php endif; ?>
	
	<!-- Titel & Description -->
	<div class="cts-title-block">
		<span class="cts-title"><?php echo esc_html( $event['title'] ); ?></span>
		<?php if ( ! empty( $event['event_description'] ) ) : ?>
			<span class="cts-event-description"> - <?php echo esc_html( wp_trim_words( $event['event_description'], 15 ) ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $event['appointment_description'] ) ) : ?>
			<span class="cts-appointment-description"> - <?php echo esc_html( wp_trim_words( $event['appointment_description'], 15 ) ); ?></span>
		<?php endif; ?>
	</div>
	
	<?php if ( ! empty( $event['services'] ) ) : ?>
		<div class="cts-services">
			<?php 
			$service_items = array();
			
			foreach ( array_slice( $event['services'], 0, 2 ) as $s ) {
				if ( ! empty( $s['person_name'] ) ) {
					$service_items[] = $s['service_name'] . ': ' . $s['person_name'];
				} else {
					$service_items[] = $s['service_name'];
				}
			}
			
			echo esc_html( implode( ' | ', $service_items ) );
			
			if ( count( $event['services'] ) > 2 ) {
				echo ' <span class="cts-more">+' . ( count( $event['services'] ) - 2 ) . '</span>';
			}
			?>
		</div>
	<?php endif; ?>

	<!-- Ort -->
	<?php if ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) : ?>
		<div class="cts-list-location">
			<span class="dashicons dashicons-location"></span>
			<?php
			if ( ! empty( $event['address_name'] ) ) {
				echo esc_html( $event['address_name'] );
			} elseif ( ! empty( $event['location_name'] ) ) {
				echo esc_html( $event['location_name'] );
			} else {
				echo esc_html( $event['address_street'] ?? '' );
			}

			// v0.9.6.32: Info-Icon mit vollständiger Adresse
			$info_parts = array_filter( [
				$event['address_name'] ?? '',
				$event['address_street'] ?? '',
				$event['address_zip'] ?? '',
				$event['address_city'] ?? ''
			] );
			if ( count( $info_parts ) > 1 ) { // Nur Icon zeigen wenn mehr als nur der Name vorhanden ist
				$info_text = implode( ', ', $info_parts );
				?>
				<span class="cts-location-info-icon" data-tooltip="<?php echo esc_attr( $info_text ); ?>">
					<span class="dashicons dashicons-info-outline"></span>
				</span>
				<?php
			}
			?>
		</div>
	<?php endif; ?>

	<!-- Tags (v0.10.4.11) -->
	<?php if ( ! empty( $event['tags_array'] ) ) : ?>
		<div class="cts-list-tags">
			<?php foreach ( $event['tags_array'] as $tag ) : ?>
				<span class="cts-tag-badge" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
					<?php echo esc_html( $tag['name'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
		
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

