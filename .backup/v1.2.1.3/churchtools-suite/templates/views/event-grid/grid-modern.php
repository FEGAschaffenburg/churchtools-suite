<?php
/**
 * Grid View - Modern
 *
 * Modernes Karten-Grid mit optionalem Hero-Bild und zentriertem Monatstrenner.
 *
 * @package ChurchTools_Suite
 * @since   1.2.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-helper.php';

$columns = isset( $args['columns'] ) ? intval( $args['columns'] ) : 3;
$columns = max( 1, min( 6, $columns ) );

$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_images = isset( $args['show_images'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;
$image_fit = isset( $args['image_fit'] ) ? ChurchTools_Suite_Shortcodes::sanitize_image_fit( $args['image_fit'] ) : 'cover';
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );
$event_action = $args['event_action'] ?? 'modal';

$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'plugin' ) {
	$custom_styles = '--cts-primary-color: #2563eb; --cts-text-color: #1e293b; --cts-bg-color: #ffffff; --cts-border-radius: 12px; --cts-font-size: 15px; --cts-padding: 20px; --cts-spacing: 20px;';
} elseif ( $style_mode === 'custom' ) {
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $args['custom_primary_color'] ?? '#2563eb' ),
		esc_attr( $args['custom_text_color'] ?? '#1e293b' ),
		esc_attr( $args['custom_background_color'] ?? '#ffffff' ),
		absint( $args['custom_border_radius'] ?? 12 ),
		absint( $args['custom_font_size'] ?? 15 ),
		absint( $args['custom_padding'] ?? 20 ),
		absint( $args['custom_spacing'] ?? 20 )
	);
}

if ( ! function_exists( 'cts_truncate_text' ) ) {
	function cts_truncate_text( $text, $length = 150 ) {
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

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>" data-image-fit="<?php echo esc_attr( $image_fit ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-grid-modern"
		data-view="grid-modern"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		data-show-images="<?php echo esc_attr( $show_images ? '1' : '0' ); ?>"
		data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>">

		<?php if ( empty( $events ) ) : ?>
			<div class="cts-list__empty-state" role="status" aria-live="polite">
				<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
				<p><?php esc_html_e( 'Keine Termine gefunden.', 'churchtools-suite' ); ?></p>
			</div>
		<?php else : ?>
			<?php
			$current_month = '';
			foreach ( $events as $event ) :
				$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
				$show_separator = $show_month_separator && $event_month !== $current_month;
				$current_month = $event_month;
				$calendar_color = $event['calendar_color'] ?? '#2563eb';
				$day = get_date_from_gmt( $event['start_datetime'], 'd' );
				$month_short = get_date_from_gmt( $event['start_datetime'], 'M' );

				$click_class = '';
				$click_attrs = '';
				if ( $event_action === 'modal' ) {
					$click_class = ' cts-event-clickable';
					$click_attrs = sprintf(
						'data-event-id="%s" data-event-title="%s" data-event-start="%s" data-event-location="%s" data-event-description="%s"',
						esc_attr( $event['id'] ),
						esc_attr( $event['title'] ),
						esc_attr( $event['start_datetime'] ),
						esc_attr( $event['location_name'] ?? '' ),
						esc_attr( wp_trim_words( $event['event_description'] ?? '', 50 ) )
					);
				} elseif ( $event_action === 'page' ) {
					$click_class = ' cts-event-page-link';
					$event_url = add_query_arg(
						[
							'event_id' => $event['id'],
							'template' => $single_event_template,
						],
						$single_event_base
					);
					$click_attrs = sprintf(
						'data-event-id="%s" data-event-url="%s"',
						esc_attr( $event['id'] ),
						esc_url( $event_url )
					);
				}

				$card_style = '';
				if ( $use_calendar_colors ) {
					$card_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"', esc_attr( $calendar_color ), esc_attr( $calendar_color ) );
				}

				$event_arr = is_array( $event ) ? $event : (array) $event;
				$calendar_for_image = ! empty( $event_arr['calendar_image_id'] ) ? [ 'calendar_image_id' => $event_arr['calendar_image_id'] ] : null;
				$image_url = ChurchTools_Suite_Image_Helper::get_image_url( $event_arr, $calendar_for_image );
				?>

				<?php if ( $show_separator ) : ?>
					<h2 class="cts-grid-modern__month-separator"><?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'F Y' ) ); ?></h2>
				<?php endif; ?>

				<article class="cts-grid-card-modern<?php echo esc_attr( $click_class ); ?>"<?php echo $card_style . ' ' . $click_attrs; ?>>
					<?php if ( $show_images && ! empty( $image_url ) ) : ?>
						<div class="cts-card-image-hero">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['title'] ); ?>" class="cts-card-image" />
						</div>
					<?php endif; ?>

					<div class="cts-card-content-wrapper">
						<div class="cts-card-header">
							<div class="cts-card-date">
								<div class="cts-date-day"><?php echo esc_html( $day ); ?></div>
								<div class="cts-date-month"><?php echo esc_html( strtoupper( $month_short ) ); ?></div>
							</div>
							<?php if ( $show_time && ! empty( $event['time_display'] ) ) : ?>
								<div class="cts-card-time">
									<span class="dashicons dashicons-clock"></span>
									<span><?php echo esc_html( $event['time_display'] ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<div class="cts-card-body">
							<h3 class="cts-card-title"><?php echo esc_html( $event['title'] ); ?></h3>

							<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
								<div class="cts-location">
									<span class="dashicons dashicons-location"></span>
									<span><?php echo esc_html( $event['location_name'] ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
								<div class="cts-event-description"><?php echo esc_html( cts_truncate_text( $event['event_description'], 180 ) ); ?></div>
							<?php endif; ?>

							<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
								<div class="cts-appointment-description"><?php echo esc_html( cts_truncate_text( $event['appointment_description'], 180 ) ); ?></div>
							<?php endif; ?>

							<?php if ( $show_tags && ! empty( $event['tags_array'] ) ) : ?>
								<div class="cts-tags">
									<?php foreach ( $event['tags_array'] as $tag ) : ?>
										<span class="cts-tag" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;"><?php echo esc_html( $tag['name'] ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
								<div class="cts-services">
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

						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-card-footer">
								<div class="cts-calendar-name-grid"><?php echo esc_html( $event['calendar_name'] ); ?></div>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
