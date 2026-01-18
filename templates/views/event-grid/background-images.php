<?php
/**
 * Grid View with Background Images
 *
 * Moderne Card-Ansicht mit Bild als Hintergrund und Overlay-Text
 * Pinterest-ähnliches Layout mit 2-4 Spalten
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.35
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load image helper
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-helper.php';

// Parse boolean parameters
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;

// Grid columns
$columns = absint( $args['columns'] ?? 3 );
$columns = max( 1, min( 4, $columns ) ); // 1-4 columns

// Style mode and custom colors
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';
if ( $style_mode === 'plugin' ) {
	$custom_styles = '--cts-primary-color: #2563eb;';
} elseif ( $style_mode === 'custom' ) {
	$primary = $args['custom_primary_color'] ?? '#2563eb';
	$custom_styles = sprintf(
		'--cts-primary-color: %s;',
		esc_attr( $primary )
	);
}

// Use calendar colors
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-grid cts-grid-with-background-images" 
		data-view="grid-with-background-images"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-grid-empty" style="grid-column: 1 / -1;">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
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
			
			// Get image URL with fallback
			// Build calendar array with calendar_image_id for fallback
			$event_arr = is_array( $event ) ? $event : (array) $event;
			$calendar_for_image = ! empty( $event_arr['calendar_image_id'] ) ? [
				'calendar_image_id' => $event_arr['calendar_image_id'],
			] : null;
			$image_url = ChurchTools_Suite_Image_Helper::get_image_url( $event_arr, $calendar_for_image );
			$bg_style = sprintf( 'background-image: url("%s");', esc_url( $image_url ) );
			
			// Overlay color from calendar
			$overlay_opacity = '0.6';
			$overlay_color = 'rgba(0, 0, 0, ' . $overlay_opacity . ')';
			if ( $use_calendar_colors && ! empty( $event['calendar_color'] ) ) {
				$overlay_color = ChurchTools_Suite_Shortcodes::hex_to_rgba( $event['calendar_color'], $overlay_opacity );
			}
			?>

<div class="cts-grid-card cts-grid-card-background-image <?php echo esc_attr( $click_class ); ?>" 
	<?php echo $click_attrs; ?>
	style="<?php echo $bg_style; ?>">
	
	<!-- Background Overlay -->
	<div class="cts-grid-card-overlay" style="background-color: <?php echo esc_attr( $overlay_color ); ?>"></div>
	
	<!-- Content (über Overlay) -->
	<div class="cts-grid-card-content">
		
		<!-- Date Badge -->
		<div class="cts-grid-card-date">
		<div class="cts-grid-card-day"><?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'd' ) ); ?></div>
		<div class="cts-grid-card-month"><?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'M' ) ); ?></div>
		<!-- Title -->
		<h3 class="cts-grid-card-title"><?php echo esc_html( $event['title'] ); ?></h3>
		
		<!-- Meta -->
		<div class="cts-grid-card-meta">
			<?php if ( $show_time ) : ?>
				<span class="cts-grid-card-time">
				🕐 <?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'H:i' ) ); ?>
			
			<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
				<span class="cts-grid-card-location">
					📍 <?php echo esc_html( $event['location_name'] ); ?>
				</span>
			<?php endif; ?>
		</div>
		
		<!-- Tags -->
		<?php if ( $show_tags && ! empty( $event_arr['tags'] ) ) : ?>
			<div class="cts-grid-card-tags">
				<?php 
				$tags = [];
				if ( is_string( $event_arr['tags'] ) ) {
					$tags = json_decode( $event_arr['tags'], true ) ?? [];
				} elseif ( is_array( $event_arr['tags'] ) ) {
					$tags = $event_arr['tags'];
				}
				foreach ( array_slice( $tags, 0, 2 ) as $tag ) : 
				?>
					<span class="cts-grid-card-tag" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>">
						<?php echo esc_html( $tag['name'] ?? '' ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		
	</div>
	
</div>

		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
/* Grid with Background Images */
.cts-grid-with-background-images {
	display: grid;
	gap: 16px;
	margin-bottom: 24px;
}

/* Dynamic columns */
.cts-grid-with-background-images[data-columns="1"] { grid-template-columns: 1fr; }
.cts-grid-with-background-images[data-columns="2"] { grid-template-columns: repeat(2, 1fr); }
.cts-grid-with-background-images[data-columns="3"] { grid-template-columns: repeat(3, 1fr); }
.cts-grid-with-background-images[data-columns="4"] { grid-template-columns: repeat(4, 1fr); }

/* Grid Card */
.cts-grid-card-background-image {
	position: relative;
	height: 250px;
	border-radius: 8px;
	overflow: hidden;
	background-size: cover;
	background-position: center;
	cursor: pointer;
	transition: transform 0.3s, box-shadow 0.3s;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cts-grid-card-background-image:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 16px rgba(0, 0, 0, 0.2);
}

/* Overlay */
.cts-grid-card-overlay {
	position: absolute;
	inset: 0;
	z-index: 1;
	transition: background-color 0.3s;
}

.cts-grid-card-background-image:hover .cts-grid-card-overlay {
	background-color: rgba(0, 0, 0, 0.4);
}

/* Content */
.cts-grid-card-content {
	position: absolute;
	inset: 0;
	z-index: 2;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	padding: 16px;
	color: white;
}

/* Date Badge */
.cts-grid-card-date {
	position: absolute;
	top: 12px;
	right: 12px;
	background-color: rgba(255, 255, 255, 0.95);
	color: #1e293b;
	padding: 8px 12px;
	border-radius: 6px;
	text-align: center;
	font-weight: 700;
	font-size: 13px;
	line-height: 1.2;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.cts-grid-card-day {
	font-size: 16px;
}

.cts-grid-card-month {
	font-size: 10px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	opacity: 0.8;
}

/* Title */
.cts-grid-card-title {
	margin: 0;
	font-size: 18px;
	font-weight: 700;
	line-height: 1.3;
	text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Meta */
.cts-grid-card-meta {
	display: flex;
	flex-direction: column;
	gap: 4px;
	font-size: 12px;
	margin-top: auto;
	text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.cts-grid-card-time,
.cts-grid-card-location {
	display: flex;
	align-items: center;
	gap: 4px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

/* Tags */
.cts-grid-card-tags {
	display: flex;
	gap: 4px;
	flex-wrap: wrap;
	margin-top: 8px;
}

.cts-grid-card-tag {
	display: inline-block;
	padding: 3px 6px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: 600;
	color: white;
	text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Empty State */
.cts-grid-empty {
	grid-column: 1 / -1;
	padding: 60px 20px;
	text-align: center;
	color: #64748b;
	background-color: #f8fafc;
	border-radius: 8px;
}

.cts-empty-icon {
	display: block;
	font-size: 48px;
	margin-bottom: 12px;
	opacity: 0.5;
}

.cts-grid-empty h3 {
	margin: 0 0 8px 0;
	font-size: 18px;
	color: #1e293b;
}

.cts-grid-empty p {
	margin: 0;
	font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-grid-with-background-images[data-columns="4"] { grid-template-columns: repeat(3, 1fr); }
	.cts-grid-with-background-images[data-columns="3"] { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
	.cts-grid-with-background-images[data-columns="4"] { grid-template-columns: repeat(2, 1fr); }
	.cts-grid-with-background-images[data-columns="3"] { grid-template-columns: repeat(2, 1fr); }
	.cts-grid-with-background-images[data-columns="2"] { grid-template-columns: 1fr; }
	
	.cts-grid-card-background-image {
		height: 200px;
	}
	
	.cts-grid-card-title {
		font-size: 16px;
	}
	
	.cts-grid-card-meta {
		font-size: 11px;
	}
}

@media (max-width: 480px) {
	.cts-grid-with-background-images {
		grid-template-columns: 1fr;
	}
	
	.cts-grid-card-background-image {
		height: 180px;
	}
	
	.cts-grid-card-date {
		top: 8px;
		right: 8px;
		padding: 6px 10px;
		font-size: 11px;
	}
	
	.cts-grid-card-title {
		font-size: 14px;
	}
}
</style>
