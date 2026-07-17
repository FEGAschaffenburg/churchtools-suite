<?php
/**
 * List View - Minimal
 *
 * Ultra-kompakte einzeilige Liste für Seitenleisten
 * Nur Datum, Uhrzeit, Titel und kurze Beschreibung
 *
 * @package ChurchTools_Suite
 * @since   0.9.6.27
 * @version 2.0.0 (Modernized - BEM + einzeilig)
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Helper function for text truncation
if ( ! function_exists( 'cts_truncate_text_minimal' ) ) {
	function cts_truncate_text_minimal( $text, $length = 80 ) {
		if ( empty( $text ) || mb_strlen( $text ) <= $length ) {
			return $text;
		}
		
		$truncated = mb_substr( $text, 0, $length );
		$last_space = mb_strrpos( $truncated, ' ' );
		
		if ( $last_space !== false ) {
			$truncated = mb_substr( $truncated, 0, $last_space );
		}
		
		return rtrim( $truncated, '.,;:!?' ) . '…';
	}
}

// Parse boolean parameters
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// Not supported in minimal view
$show_calendar_name = false;
$show_location = false;
$show_services = false;
$show_tags = false;
$show_images = false;
$use_calendar_colors = false;

// Style mode and custom colors (simplified for minimal)
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';

if ( $style_mode === 'plugin' ) {
	$text = '#1e293b';
	$text_secondary = '#6b7280';
	$border = '#e5e7eb';
	$font_size = 14;
	$spacing = 8;
	
	$custom_styles = sprintf(
		'--minimal-text: %s; --minimal-text-secondary: %s; --minimal-border: %s; --minimal-font-size: %dpx; --minimal-spacing: %dpx;',
		esc_attr( $text ),
		esc_attr( $text_secondary ),
		esc_attr( $border ),
		absint( $font_size ),
		absint( $spacing )
	);
} elseif ( $style_mode === 'custom' ) {
	$text = $args['custom_text_color'] ?? '#1e293b';
	$text_secondary = $args['custom_text_secondary_color'] ?? '#6b7280';
	$border = $args['custom_border_color'] ?? '#e5e7eb';
	$font_size = $args['custom_font_size'] ?? 14;
	$spacing = $args['custom_spacing'] ?? 8;
	
	$custom_styles = sprintf(
		'--minimal-text: %s; --minimal-text-secondary: %s; --minimal-border: %s; --minimal-font-size: %dpx; --minimal-spacing: %dpx;',
		esc_attr( $text ),
		esc_attr( $text_secondary ),
		esc_attr( $border ),
		absint( $font_size ),
		absint( $spacing )
	);
}

// Display empty state if no events
if ( empty( $events ) ) :
?>
	<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
		<div class="cts-list cts-list--minimal">
			<p class="cts-list__empty-state">
				<?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?>
			</p>
		</div>
	</div>
	<?php
	return;
endif;

// Track current month for separator
$current_month = null;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list--minimal"
		data-view="list-minimal"
		data-style-mode="<?php echo esc_attr( $style_mode ); ?>"
		data-show-event-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-month-separator="<?php echo esc_attr( $show_month_separator ? '1' : '0' ); ?>"
		data-show-calendar-name="0"
		data-show-location="0"
		data-show-services="0"
		data-show-tags="0"
		data-show-images="0"
		data-use-calendar-colors="0">
	
	<?php foreach ( $events as $event ) : ?>
		
		<?php
		// Month separator
		$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
		if ( $show_month_separator && ( $current_month === null || $current_month !== $event_month ) ) :
			$current_month = $event_month;
			$month_timestamp = strtotime( get_date_from_gmt( $event['start_datetime'] ) );
			$month_label = date_i18n( 'F Y', $month_timestamp );
		?>
			<h2 class="cts-list__month-separator">
				<time datetime="<?php echo esc_attr( $event_month ); ?>">
					<?php echo esc_html( $month_label ); ?>
				</time>
			</h2>
		<?php endif; ?>
		
		<?php
		// Extract event data
		$event_id          = ! empty( $event['id'] ) ? absint( $event['id'] ) : 0;
		$appointment_id    = ! empty( $event['appointment_id'] ) ? absint( $event['appointment_id'] ) : null;
		$event_title       = ! empty( $event['title'] ) ? esc_html( $event['title'] ) : esc_html__( 'Untitled Event', 'churchtools-suite' );
		$event_description = ! empty( $event['description'] ) ? wp_strip_all_tags( $event['description'] ) : '';
		$event_description_short = ! empty( $event_description ) ? cts_truncate_text_minimal( $event_description, 80 ) : '';
		
		// Location data
		$event_location = '';
		if ( ! empty( $event['address_name'] ) ) {
			$event_location = esc_html( $event['address_name'] );
		} elseif ( ! empty( $event['location_name'] ) ) {
			$event_location = esc_html( $event['location_name'] );
		} elseif ( ! empty( $event['address_street'] ) ) {
			$event_location = esc_html( $event['address_street'] );
		}
		
		// Calendar name
		$calendar_name = ! empty( $event['calendar_name'] ) ? esc_html( $event['calendar_name'] ) : '';
		
		// Use WordPress date format from settings (localized)
		$date_timestamp = strtotime( get_date_from_gmt( $event['start_datetime'] ) );
		$date_display = date_i18n( get_option( 'date_format' ), $date_timestamp );
		
		$time_display = '';
		if ( $show_time && ! empty( $event['start_time'] ) ) {
			$time_display = $event['start_time'];
			if ( ! empty( $event['end_time'] ) ) {
				$time_display .= ' – ' . $event['end_time'];
			}
		}
		
		// Determine if appointment
		$is_appointment = ! empty( $appointment_id );
		
		// Check if we have additional info to show in popup
		$has_additional_info = ( $event_description && ( ( $is_appointment && $show_appointment_description ) || ( ! $is_appointment && $show_event_description ) ) ) || $event_location || $calendar_name;
		?>
		
		<article 
			class="cts-list--minimal__item<?php echo $is_appointment ? ' cts-list--minimal__item--appointment' : ''; ?>"
			data-event-id="<?php echo esc_attr( $event_id ); ?>"
			<?php if ( $appointment_id ) : ?>
				data-appointment-id="<?php echo esc_attr( $appointment_id ); ?>"
			<?php endif; ?>
		>
			<!-- Date -->
			<time class="cts-list--minimal__date" datetime="<?php echo esc_attr( get_date_from_gmt( $event['start_datetime'], 'c' ) ); ?>">
				<?php echo esc_html( $date_display ); ?>
			</time>
			
			<!-- Time -->
			<?php if ( $time_display ) : ?>
				<span class="cts-list--minimal__time">
					<?php echo esc_html( $time_display ); ?>
				</span>
			<?php endif; ?>
			
			<!-- Content: Title + Short Description -->
			<div class="cts-list--minimal__content">
				<span class="cts-list--minimal__title">
					<?php echo $event_title; ?>
				</span>
				
				<?php if ( $event_description_short && ( ( $is_appointment && $show_appointment_description ) || ( ! $is_appointment && $show_event_description ) ) ) : ?>
					<span class="cts-list--minimal__description">
						<?php echo esc_html( $event_description_short ); ?>
					</span>
				<?php endif; ?>
			</div>
			
			<!-- Info Icon (shows popup with full details) -->
			<?php if ( $has_additional_info ) : ?>
				<button 
					class="cts-list--minimal__info-icon" 
					type="button"
					aria-label="<?php esc_attr_e( 'Weitere Informationen anzeigen', 'churchtools-suite' ); ?>"
					aria-expanded="false"
				>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="16" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12.01" y2="8"></line>
					</svg>
				</button>
				
				<!-- Popup Content -->
				<div class="cts-list--minimal__popup" role="tooltip" aria-hidden="true">
					<div class="cts-list--minimal__popup-content">
						
						<?php if ( $calendar_name ) : ?>
							<div class="cts-list--minimal__popup-calendar">
								<strong><?php echo $calendar_name; ?></strong>
							</div>
						<?php endif; ?>
						
						<?php if ( $event_description && ( ( $is_appointment && $show_appointment_description ) || ( ! $is_appointment && $show_event_description ) ) ) : ?>
							<div class="cts-list--minimal__popup-description">
								<?php echo esc_html( $event_description ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $event_location ) : ?>
							<div class="cts-list--minimal__popup-location">
								<svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								<?php echo $event_location; ?>
							</div>
						<?php endif; ?>
						
					</div>
				</div>
			<?php endif; ?>
			
		</article>
		
	<?php endforeach; ?>
	
	</div><!-- .cts-list--minimal -->
</div><!-- .churchtools-suite-wrapper -->

<script>
( function() {
	'use strict';
	
	// Toggle popup on info icon click
	document.addEventListener( 'DOMContentLoaded', function() {
		const infoIcons = document.querySelectorAll( '.cts-list--minimal__info-icon' );
		
		infoIcons.forEach( function( icon ) {
			icon.addEventListener( 'click', function( e ) {
				e.stopPropagation();
				
				const isExpanded = this.getAttribute( 'aria-expanded' ) === 'true';
				
				// Close all other popups
				document.querySelectorAll( '.cts-list--minimal__info-icon[aria-expanded="true"]' ).forEach( function( otherIcon ) {
					if ( otherIcon !== icon ) {
						otherIcon.setAttribute( 'aria-expanded', 'false' );
					}
				} );
				
				// Toggle current popup
				this.setAttribute( 'aria-expanded', ! isExpanded );
				
				// Close popup on outside click
				if ( ! isExpanded ) {
					setTimeout( function() {
						const closePopup = function( event ) {
							if ( ! icon.contains( event.target ) && ! icon.nextElementSibling.contains( event.target ) ) {
								icon.setAttribute( 'aria-expanded', 'false' );
								document.removeEventListener( 'click', closePopup );
							}
						};
						document.addEventListener( 'click', closePopup );
					}, 100 );
				}
			} );
			
			// Close on Escape key
			icon.addEventListener( 'keydown', function( e ) {
				if ( e.key === 'Escape' && this.getAttribute( 'aria-expanded' ) === 'true' ) {
					this.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		} );
	} );
} )();
</script>
