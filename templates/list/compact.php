<?php
/**
 * List View - Compact
 *
 * Kompakte Listen-Ansicht für Sidebar/Widgets
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

// v0.10.4.0: Alle Toggles unterstützen
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : false;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : false;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-compact" data-view="list-compact">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-empty-state">
			<p><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<ul class="cts-list-items">
			<?php foreach ( $events as $event ) : ?>
				<?php $enable_modal = $args['enable_modal'] ?? true; ?>
				<li class="cts-list-item <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>"<?php endif; ?>>
					
					<div class="cts-list-item-date">
						<?php echo esc_html( $event['start_day'] ?? '' ); ?>.<?php echo esc_html( $event['start_month'] ?? '' ); ?>.
					</div>
					
					<div class="cts-list-item-title">
						<?php echo esc_html( $event['title'] ); ?>
					</div>
					
					<?php if ( $show_time && ! empty( $event['start_time'] ) ) : ?>
						<div class="cts-list-item-time">
							<?php echo esc_html( $event['start_time'] ); ?>
						</div>
					<?php endif; ?>
					
					<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
						<div class="cts-list-item-description">
							<?php echo esc_html( wp_trim_words( $event['description'], 15 ) ); ?>
						</div>
					<?php endif; ?>
					
					<?php if ( $show_location && ! empty( $event['location'] ) ) : ?>
						<div class="cts-list-item-location">
							📍 <?php echo esc_html( $event['location'] ); ?>
						</div>
					<?php endif; ?>
					
					<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
						<div class="cts-list-item-services">
							<?php foreach ( $event['services'] as $service ) : ?>
								<span class="cts-service-badge"><?php echo esc_html( $service['service_name'] ); ?>: <?php echo esc_html( $service['person_name'] ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					
					<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
						<div class="cts-list-item-calendar">
							📅 <?php echo esc_html( $event['calendar_name'] ); ?>
						</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $event['tags'] ) ) : ?>
						<?php
						$tags = is_string( $event['tags'] ) ? json_decode( $event['tags'], true ) : $event['tags'];
						if ( is_array( $tags ) && ! empty( $tags ) ) :
						?>
						<div class="cts-event-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<span class="cts-tag" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
									<?php echo esc_html( $tag['name'] ?? '' ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
					
				</li>
			<?php endforeach; ?>
		</ul>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-list-compact {
	max-width: 100%;
}

.cts-list-items {
	list-style: none;
	margin: 0;
	padding: 0;
}

.cts-list-item {
	display: grid;
	grid-template-columns: 70px 1fr 80px;
	gap: 1rem;
	align-items: center;
	padding: 0.75rem;
	border-bottom: 1px solid #e5e7eb;
	cursor: pointer;
	transition: background 0.2s ease;
}

.cts-list-item:last-child {
	border-bottom: none;
}

.cts-list-item:hover {
	background: #f9fafb;
}

.cts-list-item-date {
	font-size: 0.875rem;
	color: #6b7280;
	font-weight: 500;
}

.cts-list-item-title {
	font-size: 0.875rem;
	color: #1f2937;
	font-weight: 500;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.cts-list-item-time {
	font-size: 0.875rem;
	color: #3b82f6;
	text-align: right;
	font-weight: 500;
}
</style>
