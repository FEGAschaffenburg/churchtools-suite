<?php
/**
 * List View - Classic with Images
 *
 * Horizontal Layout: Bild links (50px) + Info rechts - alles in einer Zeile
 * Wie Classic aber mit Bild-Thumbnail
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.34
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Image Helper for fallback logic
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-helper.php';

// Parse boolean parameters
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;
$show_images = isset( $args['show_images'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;

// Style mode and custom colors
$style_mode = $args['style_mode'] ?? 'theme';
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

// Use calendar colors
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Track current month for separator
$current_month = null;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list-classic-with-images" 
		data-view="list-classic-with-images"
		data-show-event-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>"
		data-show-images="<?php echo esc_attr( $show_images ? '1' : '0' ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php 
			// Month separator logic
		$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
			if ( $show_month_separator && ( $current_month === null || $current_month !== $event_month ) ) : 
				$current_month = $event_month;
			?>
				<div class="cts-month-separator">
				<span class="cts-month-name"><?php echo esc_html( date_i18n( 'F Y', strtotime( get_date_from_gmt( $event['start_datetime'] ) ) ) ); ?></span>
				</div>
			<?php endif; ?>
			<?php 
			// Event action logic (modal, page, none)
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
<div class="cts-event-classic <?php echo esc_attr( $click_class ); ?>" <?php echo $click_attrs; ?><?php
	if ( $use_calendar_colors && ! empty( $event['calendar_color'] ) ) {
		echo ' style="--calendar-color: ' . esc_attr( $event['calendar_color'] ) . '; --cts-primary-color: ' . esc_attr( $event['calendar_color'] ) . ';"';
	}
?>>
	
	<!-- Datum Box -->
	<?php 
	$date_box_style = '';
	$calendar_color = $event['calendar_color'] ?? '#2563eb';
	if ( $use_calendar_colors ) {
		$hex = ltrim( $calendar_color, '#' );
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b );
		$text_color = ( $luminance > 128 ) ? '#1e293b' : '#ffffff';
		$date_box_style = sprintf( 'background-color: %s; color: %s;', esc_attr( $calendar_color ), esc_attr( $text_color ) );
	}
	?>
	<div class="cts-date-box"<?php echo $date_box_style ? ' style="' . $date_box_style . '"' : ''; ?>>
		<div class="cts-date-month"><?php echo esc_html( $event['start_month'] ); ?></div>
		<div class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></div>
		<div class="cts-date-weekday"><?php echo esc_html( strtoupper( $event['start_weekday'] ) ); ?></div>
	</div>
	
	<!-- Image Thumbnail (nach Datum, 50px) -->
	<?php if ( $show_images ) : ?>
		<div class="cts-event-image-thumb">
			<?php 
			// Build calendar array with calendar_image_id for fallback (v0.9.9.62)
			$event_arr = is_array( $event ) ? $event : (array) $event;
			$calendar_for_image = ! empty( $event_arr['calendar_image_id'] ) ? [
				'calendar_image_id' => $event_arr['calendar_image_id'],
			] : null;

			echo ChurchTools_Suite_Image_Helper::get_image(
				$event_arr,
				$calendar_for_image,
				false,
				array(
					'class' => 'cts-event-thumb-img',
					'alt' => esc_attr( $event_arr['title'] ?? 'Event' ),
					'loading' => 'lazy',
					'width' => 50,
					'height' => 50,
				)
			);
			?>
		</div>
	<?php endif; ?>
	
	<!-- Uhrzeit (Von-Bis) -->
	<?php if ( $show_time ) : ?>
		<div class="cts-time">
			<?php echo esc_html( $event['start_time'] ); ?>
			<?php if ( ! empty( $event['end_time'] ) ) : ?>
				- <?php echo esc_html( $event['end_time'] ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
	<!-- Kalender-Name -->
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
	
	<!-- Titel & Description (2-zeilig vertikal) -->
	<div class="cts-title-block">
		<div class="cts-title"><?php echo esc_html( $event['title'] ); ?></div>
		<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
			<div class="cts-event-description"><?php echo esc_html( wp_trim_words( $event['event_description'], 20 ) ); ?></div>
		<?php elseif ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
			<div class="cts-appointment-description"><?php echo esc_html( wp_trim_words( $event['appointment_description'], 20 ) ); ?></div>
		<?php endif; ?>
	</div>
	
	<!-- Services (wrappable 2-3 lines) -->
	<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
		<div class="cts-services">
			<?php 
			$service_items = array();
			

			$services_arr = is_array( $event['services'] ) ? $event['services'] : array();
			foreach ( array_slice( $services_arr, 0, 2 ) as $s ) {
				if ( ! empty( $s['person_name'] ) ) {
					$service_items[] = $s['service_name'] . ': ' . $s['person_name'];
				} else {
					$service_items[] = $s['service_name'];
				}
			}
			
			echo esc_html( implode( ' | ', $service_items ) );
			
			if ( is_array( $event['services'] ) && count( $event['services'] ) > 2 ) {
				echo ' <span class="cts-more">+' . ( count( $event['services'] ) - 2 ) . '</span>';
			}
			?>
		</div>
	<?php endif; ?>
	
	<!-- Location -->
	<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
		<div class="cts-list-location">
			<?php echo esc_html( $event['location_name'] ); ?>
		</div>
	<?php endif; ?>
	
	<!-- Tags -->
	<?php if ( $show_tags && ! empty( $event['tags'] ) ) : ?>
		<div class="cts-list-tags">
			<?php 
			// Only show first 2 tags inline
			$tags_arr = is_array( $event['tags'] ) ? $event['tags'] : array();
			foreach ( array_slice( $tags_arr, 0, 2 ) as $tag ) :
			?>
				<span class="cts-tag-badge" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
					<?php echo esc_html( $tag['name'] ?? '' ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	
</div>

		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
/* List - Classic with Images (wie Classic, aber mit Bild nach Datum) */
.cts-list-classic {
	display: flex;
	flex-direction: column;
	gap: 0;
}

.cts-event-classic {
	display: grid;
	grid-template-columns: auto auto auto 1fr auto auto auto;
	gap: 12px;
	padding: 12px 16px;
	border-bottom: 1px solid #e5e7eb;
	align-items: center;
	transition: background-color 0.2s;
	font-size: 14px;
}

.cts-event-classic:hover {
	background-color: #f8fafc;
}

.cts-event-classic.cts-event-clickable {
	cursor: pointer;
}

/* Datum Box */
.cts-date-box {
	display: flex;
	flex-direction: column;
	align-items: center;
	background-color: #2563eb;
	color: white;
	padding: 8px 12px;
	border-radius: 6px;
	min-width: 60px;
	font-weight: 600;
	text-align: center;
}

.cts-date-month {
	font-size: 10px;
	text-transform: uppercase;
	opacity: 0.9;
	letter-spacing: 0.5px;
}

.cts-date-day {
	font-size: 20px;
	line-height: 1;
	margin: 2px 0;
}

.cts-date-weekday {
	font-size: 9px;
	opacity: 0.8;
	letter-spacing: 0.5px;
}

/* Image Thumbnail (nach Datum) */
.cts-event-image-thumb {
	flex-shrink: 0;
	width: 50px;
	height: 50px;
	border-radius: 6px;
	overflow: hidden;
	background-color: #f1f5f9;
	display: flex;
	align-items: center;
	justify-content: center;
}

.cts-event-thumb-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

/* Uhrzeit */
.cts-time {
	font-weight: 600;
	color: #1e293b;
	white-space: nowrap;
	font-size: 14px;
}

/* Kalender-Name */
.cts-calendar-name {
	font-size: 12px;
	color: #64748b;
	font-weight: 500;
	white-space: nowrap;
}

/* Titel & Description Block */
.cts-title-block {
	flex: 1;
	min-width: 0;
	color: #1e293b;
}

.cts-title {
	font-weight: 600;
	font-size: 14px;
}

.cts-event-description,
.cts-appointment-description {
	color: #64748b;
	font-size: 13px;
}

/* Services */
.cts-services {
	color: #64748b;
	font-size: 13px;
	white-space: nowrap;
}

.cts-more-indicator {
	color: #94a3b8;
	font-size: 12px;
}

/* Location */
.cts-location {
	color: #64748b;
	font-size: 13px;
	white-space: nowrap;
	display: flex;
	align-items: center;
	gap: 4px;
}

.cts-location-icon {
	font-size: 12px;
}

/* Tags */
.cts-tags {
	display: flex;
	gap: 4px;
	flex-wrap: wrap;
	align-items: center;
}

.cts-tag {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 11px;
	font-weight: 500;
	white-space: nowrap;
}

.cts-tag-more {
	font-size: 11px;
	color: #94a3b8;
	font-weight: 600;
}

/* Month Separator */
.cts-month-separator {
	padding: 16px 16px 8px;
	border-bottom: 2px solid #e5e7eb;
	margin-bottom: 8px;
}

.cts-month-name {
	font-size: 14px;
	font-weight: 700;
	color: #1e293b;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

/* Empty State */
.cts-list-empty {
	text-align: center;
	padding: 48px 24px;
	color: #64748b;
}

.cts-empty-icon {
	font-size: 48px;
	display: block;
	margin-bottom: 16px;
	opacity: 0.5;
}

.cts-list-empty h3 {
	margin: 0 0 8px;
	color: #1e293b;
	font-size: 18px;
}

.cts-list-empty p {
	margin: 0;
	font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-event-classic {
		grid-template-columns: auto auto auto 1fr;
		gap: 10px;
		padding: 10px 12px;
	}
	
	.cts-services,
	.cts-location,
	.cts-tags {
		display: none;
	}
}

@media (max-width: 768px) {
	.cts-event-classic {
		grid-template-columns: auto auto 1fr;
		gap: 8px;
		padding: 10px;
		font-size: 13px;
	}
	
	.cts-date-box {
		min-width: 50px;
		padding: 6px 8px;
	}
	
	.cts-date-day {
		font-size: 16px;
	}
	
	.cts-event-image-thumb {
		width: 40px;
		height: 40px;
	}
	
	.cts-time,
	.cts-calendar-name {
		display: none;
	}
}

@media (max-width: 480px) {
	.cts-event-classic {
		grid-template-columns: auto 1fr;
	}
	
	.cts-event-image-thumb {
		display: none;
	}
}
</style>
