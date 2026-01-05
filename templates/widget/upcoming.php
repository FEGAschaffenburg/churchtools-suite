<?php
/**
 * Widget View - Upcoming Events
 *
 * Kompakter Widget für Upcoming Events (Sidebar/Widget-Bereich)
 *
 * @package ChurchTools_Suite
 * @since   0.10.1.0
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : false;
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : true;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-widget cts-widget-upcoming" data-view="widget-upcoming">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-widget-empty">
			<p><?php esc_html_e( 'Keine kommenden Termine', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<ul class="cts-widget-list">
		<?php $enable_modal = $args['enable_modal'] ?? true; ?>
		<?php foreach ( $events as $event ) : ?>
			<li class="cts-widget-item <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>"<?php endif; ?>>
					<div class="cts-widget-item-header">
						<span class="cts-widget-date"><?php echo esc_html( $event['start_day'] ?? '' ); ?></span>
						<span class="cts-widget-month"><?php echo esc_html( $event['start_month_short'] ?? '' ); ?></span>
					</div>
					
					<div class="cts-widget-item-content">
						<div class="cts-widget-title"><?php echo esc_html( $event['title'] ); ?></div>
						
						<?php if ( $show_time && ! empty( $event['start_time'] ) ) : ?>
							<div class="cts-widget-time">🕐 <?php echo esc_html( $event['start_time'] ); ?></div>
						<?php endif; ?>
						
						<?php if ( $show_location && ! empty( $event['location'] ) ) : ?>
							<div class="cts-widget-location">📍 <?php echo esc_html( $event['location'] ); ?></div>
						<?php endif; ?>
						
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
							<div class="cts-widget-description" style="font-size: 0.875rem; color: #6b7280; margin-top: 0.375rem;">
								<?php echo esc_html( wp_trim_words( $event['event_description'], 10 ) ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
							<div class="cts-widget-description" style="font-size: 0.875rem; color: #6b7280; margin-top: 0.375rem;">
								<?php echo esc_html( wp_trim_words( $event['appointment_description'], 10 ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-widget-services" style="font-size: 0.875rem; color: #6b7280; margin-top: 0.375rem;">
								<?php
								$first_service = $event['services'][0];
								echo esc_html( $first_service['service_name'] );
								if ( ! empty( $first_service['person_name'] ) ) {
									echo ': ' . esc_html( $first_service['person_name'] );
								}
								if ( count( $event['services'] ) > 1 ) {
									echo ' <span style="font-weight: 600;">+' . ( count( $event['services'] ) - 1 ) . '</span>';
								}
								?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-widget-calendar" style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.375rem;">
								📅 <?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_tags && ! empty( $event['tags'] ) ) : ?>
							<?php
							$tags = is_string( $event['tags'] ) ? json_decode( $event['tags'], true ) : $event['tags'];
							if ( is_array( $tags ) && ! empty( $tags ) ) :
							?>
							<div class="cts-widget-tags" style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.375rem;">
								<?php foreach ( array_slice( $tags, 0, 2 ) as $tag ) : ?>
									<span class="cts-tag" style="display: inline-block; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.625rem; font-weight: 600; background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>; color: #fff;">
										<?php echo esc_html( $tag['name'] ?? '' ); ?>
									</span>
								<?php endforeach; ?>
								<?php if ( count( $tags ) > 2 ) : ?>
									<span style="font-size: 0.625rem; color: #9ca3af;">+<?php echo count( $tags ) - 2; ?></span>
								<?php endif; ?>
							</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					
				</li>
			<?php endforeach; ?>
		</ul>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-widget-upcoming {
	background: white;
	border-radius: 0.5rem;
	overflow: hidden;
}

.cts-widget-list {
	list-style: none;
	margin: 0;
	padding: 0;
}

.cts-widget-item {
	display: flex;
	gap: 0.75rem;
	padding: 0.75rem;
	border-bottom: 1px solid #e5e7eb;
	cursor: pointer;
	transition: background 0.2s ease;
}

.cts-widget-item:last-child {
	border-bottom: none;
}

.cts-widget-item:hover {
	background: #f9fafb;
}

.cts-widget-item-header {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	width: 50px;
	min-width: 50px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border-radius: 0.375rem;
	text-align: center;
}

.cts-widget-date {
	font-size: 1.25rem;
	font-weight: 700;
	line-height: 1;
}

.cts-widget-month {
	font-size: 0.75rem;
	text-transform: uppercase;
	opacity: 0.9;
}

.cts-widget-item-content {
	flex: 1;
}

.cts-widget-title {
	font-size: 0.875rem;
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 0.25rem;
}

.cts-widget-time {
	font-size: 0.75rem;
	color: #6b7280;
}

.cts-widget-location {
	font-size: 0.75rem;
	color: #059669;
	margin-top: 0.25rem;
}

.cts-widget-empty {
	padding: 1.5rem;
	text-align: center;
	color: #9ca3af;
	font-size: 0.875rem;
}
</style>
