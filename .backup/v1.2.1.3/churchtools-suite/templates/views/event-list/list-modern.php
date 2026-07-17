<?php
/**
 * List View - Modern (Row Layout)
 *
 * Mehrzeilige Liste mit großem Date-Badge und CSS Grid
 *
 * @package ChurchTools_Suite
 * @since   1.0.6.0
 * @version 3.0.0
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 * 
 * FEATURES:
 * - BEM naming convention (.cts-list--modern-rows__item)
 * - CSS Grid layout with prominent date badge
 * - CSS Custom Properties for theming
 * - Container-based responsive design (3 breakpoints)
 * - Style modes: theme/plugin/custom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse boolean parameters (support strings from Gutenberg attributes)
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;

// v1.1.0.2: Image support
$show_images = isset( $args['show_images'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;
$image_style = $args['image_style'] ?? 'thumbnail'; // 'thumbnail' or 'hero'

// Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Style mode and custom colors
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

// Track current month for separator
$current_month = null;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list--modern-rows" 
		data-view="list-modern-rows"
		data-show-event-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>"
		data-show-images="<?php echo esc_attr( $show_images ? '1' : '0' ); ?>"
		data-image-style="<?php echo esc_attr( $image_style ); ?>"
		data-show-month-separator="<?php echo esc_attr( $show_month_separator ? '1' : '0' ); ?>">
	
	<!-- Modern List - In Entwicklung -->
	<div class="cts-list--modern-rows__development-notice" style="padding: 2rem; text-align: center; background: #f0f9ff; border: 2px dashed #0284c7; border-radius: 8px; margin: 2rem 0;">
		<svg style="width: 48px; height: 48px; margin: 0 auto 1rem; fill: #0284c7;" viewBox="0 0 24 24">
			<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
		</svg>
		<h3 style="margin: 0 0 0.5rem; font-size: 1.25rem; color: #0c4a6e;">Moderne Listenansicht</h3>
		<p style="margin: 0; color: #075985; font-size: 1rem;">Diese Ansicht wird noch entwickelt.</p>
		<p style="margin: 0.5rem 0 0; color: #0369a1; font-size: 0.875rem;">Bitte verwenden Sie die Classic-Ansicht: <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">view="classic"</code></p>
	</div>
	
	<?php if ( false ) : // Deaktiviert für Entwicklung ?>
		<?php foreach ( $events as $event ) : ?>
			<?php 
			// Month separator logic (fixed)
			$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
			if ( $show_month_separator && ( $current_month === null || $current_month !== $event_month ) ) : 
				$current_month = $event_month; // Update BEFORE separator output
			?>
				<div class="cts-list--modern-rows__month-separator">
					<time class="cts-list--modern-rows__month-name" datetime="<?php echo esc_attr( $event_month ); ?>">
					<?php echo esc_html( date_i18n( 'F Y', strtotime( get_date_from_gmt( $event['start_datetime'] ) ) ) ); ?>
					</time>
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
				$click_class = ' cts-list--modern-rows__item--clickable';
				$click_attrs = sprintf(
					' data-event-id="%s" role="button" tabindex="0" aria-label="%s"',
					esc_attr( $event['id'] ),
					esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) )
				);
			} elseif ( $event_action === 'page' ) {
				$click_class = ' cts-list--modern-rows__item--page-link';
				$page_url = add_query_arg(
					[
						'event_id' => $event['id'],
						'template' => $single_event_template,
						'ctse_context' => 'elementor',
					],
					$single_event_base
				);
				$click_attrs = sprintf(
					' data-event-id="%s" data-event-url="%s" role="link" tabindex="0" aria-label="%s"',
					esc_attr( $event['id'] ),
					esc_url( $page_url ),
					esc_attr( sprintf( __( 'Zu %s navigieren', 'churchtools-suite' ), $event['title'] ) )
				);
			}
			
			// Calendar color styling
			$item_style = '';
			$calendar_color = $event['calendar_color'] ?? '#2563eb';
			if ( $use_calendar_colors && ! empty( $event['calendar_color'] ) ) {
				$item_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"', esc_attr( $calendar_color ), esc_attr( $calendar_color ) );
			}
			?>
			
			<article class="cts-list--modern-rows__item<?php echo esc_attr( $click_class ); ?>"<?php echo $click_attrs . $item_style; ?>>
				
				<!-- Date Box (with calendar color if active) -->
				<?php 
				$date_box_style = '';
				if ( $use_calendar_colors ) {
					// Calculate luminance for intelligent text color
					$hex = ltrim( $calendar_color, '#' );
					$r = hexdec( substr( $hex, 0, 2 ) );
					$g = hexdec( substr( $hex, 2, 2 ) );
					$b = hexdec( substr( $hex, 4, 2 ) );
					
					// Relative luminance (W3C formula)
					$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b );
					
					// Choose text color: Dark for light backgrounds (> 128), Light for dark
					$text_color = ( $luminance > 128 ) ? '#1e293b' : '#ffffff';
					
					$date_box_style = sprintf( ' style="background-color: %s; color: %s;"', esc_attr( $calendar_color ), esc_attr( $text_color ) );
				}
				?>
				<div class="cts-list--modern-rows__date"<?php echo $date_box_style; ?>>
					<?php if ( ! $show_month_separator ) : ?>
						<span class="cts-list--modern-rows__date-month"><?php echo esc_html( $event['start_month'] ); ?></span>
					<?php endif; ?>
					<span class="cts-list--modern-rows__date-day"><?php echo esc_html( $event['start_day'] ); ?></span>
					<span class="cts-list--modern-rows__date-weekday"><?php echo esc_html( strtoupper( $event['start_weekday'] ) ); ?></span>
				</div>
			
				<!-- Image (Thumbnail or Hero) - v1.1.0.2 -->
				<?php if ( $show_images ) : ?>
					<?php 
					// Build calendar array with calendar_image_id for fallback (v1.1.0.3)
					$event_arr = (array) $event;
					$calendar_for_image = ! empty( $event_arr['calendar_image_id'] ) ? [
						'calendar_image_id' => $event_arr['calendar_image_id'],
					] : null;
					
					if ( $image_style === 'hero' ) :
						// Hero style: Large cover image with title overlay
					?>
						<div class="cts-list--modern-rows__hero">
							<?php 
					echo ChurchTools_Suite_Image_Helper::get_image(
						$event_arr,
						$calendar_for_image,
						false,
						array(
							'class' => 'cts-list--modern-rows__hero-img',
							'alt' => esc_attr( $event_arr['title'] ?? 'Event' ),
							'loading' => 'lazy',
							'width' => 400,
							'height' => 180,
						)
					);
					?>
					<?php else : ?>
						<!-- Thumbnail style: Round 60x60px inline -->
						<div class="cts-list--modern-rows__thumb">
							<?php 
							echo ChurchTools_Suite_Image_Helper::get_image(
								$event_arr,
								$calendar_for_image,
								false,
								array(
									'class' => 'cts-list--modern-rows__thumb-img',
									'alt' => esc_attr( $event_arr['title'] ?? 'Event' ),
									'loading' => 'lazy',
									'width' => 60,
									'height' => 60,
								)
							);
							?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				
			<!-- Time (From-To) - 2-line display -->
			<?php if ( $show_time ) : ?>
				<time class="cts-list--modern-rows__time" datetime="<?php echo esc_attr( $event['start_datetime'] ); ?>">
					<span class="cts-list--modern-rows__time-start"><?php echo esc_html( $event['start_time'] ); ?></span>
					<?php if ( ! empty( $event['end_time'] ) ) : ?>
						<span class="cts-list--modern-rows__time-end"><?php echo esc_html( $event['end_time'] ); ?></span>
					<?php endif; ?>
				</time>
			<?php endif; ?>
			<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
				$calendar_name_style = '';
				if ( $use_calendar_colors ) {
					$calendar_name_style = sprintf( ' style="color: %s; font-weight: 600;"', esc_attr( $calendar_color ) );
				}
			?>
				<span class="cts-list--modern-rows__calendar"<?php echo $calendar_name_style; ?>>
					<?php echo esc_html( $event['calendar_name'] ); ?>
				</span>
			<?php endif; ?>
				
				<!-- Title & Descriptions -->
				<div class="cts-list--modern-rows__content">
					<h3 class="cts-list--modern-rows__title"><?php echo esc_html( $event['title'] ); ?></h3>
					<?php if ( ! empty( $event['event_description'] ) ) : ?>
						<p class="cts-list--modern-rows__description cts-list--modern-rows__description--event">
							<?php echo esc_html( wp_trim_words( $event['event_description'], 15 ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $event['appointment_description'] ) ) : ?>
						<p class="cts-list--modern-rows__description cts-list--modern-rows__description--appointment">
							<?php echo esc_html( wp_trim_words( $event['appointment_description'], 15 ) ); ?>
						</p>
					<?php endif; ?>
				</div>
				
				<!-- Services -->
				<?php if ( ! empty( $event['services'] ) ) : ?>
					<div class="cts-list--modern-rows__services">
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
							echo ' <span class="cts-list--modern-rows__services-more">+' . ( count( $event['services'] ) - 2 ) . '</span>';
						}
						?>
					</div>
				<?php endif; ?>

				<!-- Location -->
				<?php if ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) : ?>
					<div class="cts-list--modern-rows__location">
						<span class="cts-list--modern-rows__location-icon dashicons dashicons-location"></span>
						<span class="cts-list--modern-rows__location-text">
							<?php
							if ( ! empty( $event['address_name'] ) ) {
								echo esc_html( $event['address_name'] );
							} elseif ( ! empty( $event['location_name'] ) ) {
								echo esc_html( $event['location_name'] );
							} else {
								echo esc_html( $event['address_street'] ?? '' );
							}
							?>
						</span>
						
						<?php
						// Info icon with full address
						$info_parts = array_filter( [
							$event['address_name'] ?? '',
							$event['address_street'] ?? '',
							$event['address_zip'] ?? '',
							$event['address_city'] ?? ''
						] );
						if ( count( $info_parts ) > 1 ) : // Show icon only if more than just the name is available
							$info_text = implode( ', ', $info_parts );
						?>
							<button class="cts-list--modern-rows__location-info" 
								type="button" 
								aria-label="<?php esc_attr_e( 'Vollständige Adresse anzeigen', 'churchtools-suite' ); ?>"
								data-tooltip="<?php echo esc_attr( $info_text ); ?>">
								<span class="dashicons dashicons-info-outline"></span>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<!-- Tags -->
				<?php if ( ! empty( $event['tags_array'] ) ) : ?>
					<div class="cts-list--modern-rows__tags">
						<?php foreach ( $event['tags_array'] as $tag ) : ?>
							<span class="cts-list--modern-rows__tag" 
								style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
								<?php echo esc_html( $tag['name'] ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				
			</article>
		
		<?php endforeach; ?>
		
	<?php endif; // Ende: Entwicklungsmodus ?>
	
	</div>
</div>
