<?php
/**
 * Carousel View - Single Hero
 *
 * Full-width single-event slider with background image and overlay content.
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$autoplay = isset( $args['autoplay'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['autoplay'] ) : false;
$autoplay_delay = isset( $args['autoplay_delay'] ) ? intval( $args['autoplay_delay'] ) : 7000;
$autoplay_delay = max( 1000, min( 10000, $autoplay_delay ) );
$loop = isset( $args['loop'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['loop'] ) : true;

$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
$show_images = isset( $args['show_images'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_images'] ) : true;
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_title = isset( $args['show_title'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_title'] ) : true;
$image_fit = isset( $args['image_fit'] ) ? ChurchTools_Suite_Shortcodes::sanitize_image_fit( $args['image_fit'] ) : 'contain';
$hero_title_font_size = isset( $args['hero_title_font_size'] ) ? max( 0, min( 120, intval( $args['hero_title_font_size'] ) ) ) : 0;
$hero_layout_preset = isset( $args['hero_layout_preset'] ) && in_array( $args['hero_layout_preset'], [ 'compact', 'standard', 'hero' ], true ) ? $args['hero_layout_preset'] : 'standard';
$hero_mobile_optimize = isset( $args['hero_mobile_optimize'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['hero_mobile_optimize'] ) : true;
$event_action = $args['event_action'] ?? 'modal';
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );
$wp_timezone = wp_timezone();

$event_class = '';
if ( $event_action === 'modal' ) {
	$event_class = 'cts-event-clickable';
} elseif ( $event_action === 'page' ) {
	$event_class = 'cts-event-page-link';
}

$wrapper_classes = [
	'churchtools-suite-wrapper',
	'cts-carousel-classic',
	'cts-carousel-single-hero',
];

if ( ! empty( $args['class'] ) ) {
	$wrapper_classes[] = esc_attr( $args['class'] );
}

if ( ! function_exists( 'cts_carousel_single_hero_image_url' ) ) {
	function cts_carousel_single_hero_image_url( $event ) {
		if ( ! empty( $event['image_url'] ) ) {
			return (string) $event['image_url'];
		}
		if ( ! empty( $event['event_image_url'] ) ) {
			return (string) $event['event_image_url'];
		}
		if ( ! empty( $event['appointment_image_url'] ) ) {
			return (string) $event['appointment_image_url'];
		}
		if ( ! empty( $event['calendar_image_url'] ) ) {
			return (string) $event['calendar_image_url'];
		}

		return '';
	}
}

$hero_height_desktop = 'clamp(380px, 64vh, 760px)';
$hero_height_mobile = '70vh';
$hero_content_width = '840px';
$hero_title_default = 'clamp(1.9rem, 4.1vw, 3.8rem)';

if ( $hero_layout_preset === 'compact' ) {
	$hero_height_desktop = 'clamp(320px, 56vh, 620px)';
	$hero_height_mobile = '62vh';
	$hero_content_width = '760px';
	$hero_title_default = 'clamp(1.6rem, 3.2vw, 3rem)';
} elseif ( $hero_layout_preset === 'hero' ) {
	$hero_height_desktop = 'clamp(460px, 76vh, 920px)';
	$hero_height_mobile = '78vh';
	$hero_content_width = '960px';
	$hero_title_default = 'clamp(2.1rem, 4.8vw, 4.5rem)';
}
?>

<style>
	.cts-carousel-single-hero {
		position: relative;
	}
	.cts-carousel-single-hero .cts-carousel-track {
		--slides-per-view: 1;
	}
	.cts-carousel-single-hero .cts-carousel-slide {
		position: relative;
		min-height: <?php echo esc_html( $hero_height_desktop ); ?>;
		padding: 0;
		border-radius: 20px;
		overflow: hidden;
		background: #0f172a;
		box-shadow: 0 20px 60px rgba(15, 23, 42, 0.22);
	}
	.cts-carousel-single-hero .cts-carousel-slide:hover {
		transform: none;
	}
	.cts-carousel-single-hero-media,
	.cts-carousel-single-hero-fallback {
		position: absolute;
		inset: 0;
	}
	.cts-carousel-single-hero-media img {
		width: 100%;
		height: 100%;
		object-fit: <?php echo esc_html( $image_fit ); ?>;
		object-position: center center;
		background: #0f172a;
	}
	[data-image-fit="contain"] .cts-carousel-single-hero-img {
		object-fit: contain !important;
	}
	.cts-carousel-single-hero-fallback {
		background: linear-gradient(135deg, var(--calendar-color, #2563eb), #0f172a 72%);
	}
	.cts-carousel-single-hero-overlay {
		position: absolute;
		inset: 0;
		background: linear-gradient(180deg, rgba(15, 23, 42, 0.12) 0%, rgba(15, 23, 42, 0.45) 35%, rgba(15, 23, 42, 0.88) 100%);
	}
	.cts-carousel-single-hero-inner {
		position: relative;
		z-index: 2;
		min-height: inherit;
		display: flex;
		align-items: flex-end;
		padding: clamp(22px, 4vw, 46px);
	}
	.cts-carousel-single-hero-content {
		width: min(100%, <?php echo esc_html( $hero_content_width ); ?>);
		color: #fff;
	}
	.cts-carousel-single-hero-badges {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-bottom: 18px;
	}
	.cts-carousel-single-hero-badge {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 8px 14px;
		border-radius: 999px;
		background: rgba(255, 255, 255, 0.14);
		backdrop-filter: blur(10px);
		font-size: 13px;
		font-weight: 600;
	}
	.cts-carousel-single-hero h2 {
		margin: 0 0 14px;
		font-size: <?php echo $hero_title_font_size > 0 ? esc_html( $hero_title_font_size . 'px' ) : esc_html( $hero_title_default ); ?>;
		line-height: 1.02;
		color: #fff;
		text-wrap: balance;
	}
	.cts-carousel-single-hero-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 18px;
		margin-bottom: 18px;
		font-size: clamp(1rem, 1.8vw, 1.15rem);
	}
	.cts-carousel-single-hero-meta-item {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		color: rgba(255, 255, 255, 0.96);
	}
	.cts-carousel-single-hero-copy {
		max-width: 72ch;
		font-size: clamp(1rem, 1.45vw, 1.18rem);
		line-height: 1.6;
		color: rgba(255, 255, 255, 0.92);
	}
	.cts-carousel-single-hero-copy p:last-child {
		margin-bottom: 0;
	}
	.cts-carousel-single-hero-services,
	.cts-carousel-single-hero-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 16px;
	}
	.cts-carousel-single-hero-chip {
		display: inline-flex;
		align-items: center;
		padding: 7px 11px;
		border-radius: 999px;
		background: rgba(255,255,255,0.12);
		backdrop-filter: blur(8px);
		font-size: 13px;
	}
	.cts-carousel-single-hero .cts-carousel-nav {
		top: 50%;
		transform: translateY(-50%);
	}
	.cts-carousel-single-hero .cts-carousel-pagination {
		position: absolute;
		left: 0;
		right: 0;
		bottom: 16px;
		z-index: 3;
	}
	@media (max-width: 767px) {
		.cts-carousel-single-hero .cts-carousel-slide {
			min-height: <?php echo $hero_mobile_optimize ? esc_html( $hero_height_mobile ) : '78vh'; ?>;
		}
		<?php if ( $hero_mobile_optimize ) : ?>
		.cts-carousel-single-hero .cts-carousel-single-hero-inner {
			padding: 18px 16px 78px;
		}
		.cts-carousel-single-hero h2 {
			display: -webkit-box;
			-webkit-line-clamp: 3;
			-webkit-box-orient: vertical;
			overflow: hidden;
			line-height: 1.08;
		}
		.cts-carousel-single-hero-copy {
			display: -webkit-box;
			-webkit-line-clamp: 4;
			-webkit-box-orient: vertical;
			overflow: hidden;
			line-height: 1.45;
		}
		<?php endif; ?>
		.cts-carousel-single-hero .cts-carousel-nav {
			top: auto;
			bottom: 58px;
			transform: none;
		}
	}
</style>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
	data-slides-per-view="1"
	data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
	data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>"
	data-loop="<?php echo $loop ? '1' : '0'; ?>"
	data-image-fit="<?php echo esc_attr( $image_fit ); ?>"
	data-show-images="<?php echo $show_images ? '1' : '0'; ?>">
	<?php if ( empty( $events ) ) : ?>
		<p class="cts-no-events"><?php esc_html_e( 'Keine Events gefunden.', 'churchtools-suite' ); ?></p>
	<?php else : ?>
		<?php $has_multiple_events = count( $events ) > 1; ?>
		<div class="cts-carousel-container">
			<div class="cts-carousel-track">
				<?php foreach ( $events as $event ) :
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
					$time_format = get_option( 'time_format' );
					$has_ampm = ( strpos( $time_format, 'a' ) !== false || strpos( $time_format, 'A' ) !== false );
					$start_time_display = wp_date( $time_format, $start_ts, $wp_timezone );
					if ( ! $has_ampm ) {
						$start_time_display .= ' Uhr';
					}
					$end_time_display = '';
					if ( ! empty( $event['end_datetime'] ) ) {
						try {
							$dt_end = new DateTime( $event['end_datetime'], new DateTimeZone( 'UTC' ) );
							$dt_end->setTimezone( $wp_timezone );
							$end_ts = $dt_end->getTimestamp();
							$end_time_display = wp_date( $time_format, $end_ts, $wp_timezone );
							if ( ! $has_ampm ) {
								$end_time_display .= ' Uhr';
							}
						} catch ( Exception $e ) {
							$end_time_display = '';
						}
					}
					$calendar_color = $event['calendar_color'] ?? '#2563eb';
					$image_url = $show_images ? cts_carousel_single_hero_image_url( $event ) : '';
					$description_html = '';
					if ( $show_event_description && ! empty( $event['event_description'] ) ) {
						$description_html .= wpautop( wp_trim_words( wp_strip_all_tags( $event['event_description'] ), 42 ) );
					}
					if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) {
						if ( $description_html !== '' ) {
							$description_html .= wpautop( wp_trim_words( wp_strip_all_tags( $event['appointment_description'] ), 24 ) );
						} else {
							$description_html = wpautop( wp_trim_words( wp_strip_all_tags( $event['appointment_description'] ), 42 ) );
						}
					}
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
						$click_url = add_query_arg(
							[
								'event_id' => $event['id'],
								'template' => $single_event_template,
							],
							$single_event_base
						);
						$event_attrs = sprintf(
							'data-event-id="%s" data-event-url="%s"',
							esc_attr( $event['id'] ),
							esc_url( $click_url )
						);
					}
					$tags = ! empty( $event['tags'] ) ? json_decode( $event['tags'], true ) : [];
				?>
				<div class="cts-carousel-slide <?php echo esc_attr( $event_class ); ?>" <?php echo $event_attrs; ?> style="--calendar-color: <?php echo esc_attr( $calendar_color ); ?>;">
					<?php if ( ! empty( $image_url ) ) : ?>
						<div class="cts-carousel-single-hero-media"><img class="cts-carousel-single-hero-img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event['title'] ); ?>"></div>
					<?php else : ?>
						<div class="cts-carousel-single-hero-fallback"></div>
					<?php endif; ?>
					<div class="cts-carousel-single-hero-overlay"></div>
					<div class="cts-carousel-single-hero-inner">
						<div class="cts-carousel-single-hero-content">
							<div class="cts-carousel-single-hero-badges">
								<div class="cts-carousel-single-hero-badge"><?php echo esc_html( wp_date( 'l, j. F Y', $start_ts, $wp_timezone ) ); ?></div>
								<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
									<div class="cts-carousel-single-hero-badge"><?php echo esc_html( $event['calendar_name'] ); ?></div>
								<?php endif; ?>
							</div>
							<?php if ( $show_title ) : ?>
								<h2><?php echo esc_html( $event['title'] ); ?></h2>
							<?php endif; ?>
							<div class="cts-carousel-single-hero-meta">
								<?php if ( $show_time ) : ?>
									<div class="cts-carousel-single-hero-meta-item"><span class="dashicons dashicons-clock"></span><span><?php echo esc_html( $end_time_display ? $start_time_display . ' - ' . $end_time_display : $start_time_display ); ?></span></div>
								<?php endif; ?>
								<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
									<div class="cts-carousel-single-hero-meta-item"><span class="dashicons dashicons-location"></span><span><?php echo esc_html( $event['location_name'] ); ?></span></div>
								<?php endif; ?>
							</div>
							<?php if ( $description_html !== '' ) : ?>
								<div class="cts-carousel-single-hero-copy"><?php echo wp_kses_post( $description_html ); ?></div>
							<?php endif; ?>
							<?php if ( $show_tags && is_array( $tags ) && ! empty( $tags ) ) : ?>
								<div class="cts-carousel-single-hero-tags">
									<?php foreach ( $tags as $tag ) : ?>
										<span class="cts-carousel-single-hero-chip"><?php echo esc_html( $tag['name'] ?? '' ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
								<div class="cts-carousel-single-hero-services">
									<?php foreach ( $event['services'] as $service ) : ?>
										<span class="cts-carousel-single-hero-chip"><?php echo esc_html( $service['service_name'] ?? '' ); ?><?php if ( ! empty( $service['person_name'] ) ) : ?>: <?php echo esc_html( $service['person_name'] ); ?><?php endif; ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $has_multiple_events ) : ?>
		<button class="cts-carousel-nav cts-carousel-nav-prev" aria-label="<?php esc_attr_e( 'Vorheriges Event', 'churchtools-suite' ); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
		<button class="cts-carousel-nav cts-carousel-nav-next" aria-label="<?php esc_attr_e( 'Nächstes Event', 'churchtools-suite' ); ?>"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
		<?php endif; ?>
		<div class="cts-carousel-pagination"></div>
	<?php endif; ?>
</div>