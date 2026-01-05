<?php
/**
 * List View - Fluent
 *
 * Microsoft Fluent Design inspiriertes Layout mit Acrylic-Effekten
 *
 * @package ChurchTools_Suite
 * @since   0.5.12.0
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
// v0.10.4.0: Alle Toggles unterstützen
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : false;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : false;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-fluent" data-view="list-fluent">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
		<div class="cts-event-fluent <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>"<?php endif; ?>>
				<div class="cts-event-content">
					
					<!-- Left Side: Date & Time -->
					<div class="cts-event-sidebar">
						<div class="cts-date-stack">
							<span class="cts-date-weekday"><?php echo esc_html( date_i18n( 'l', strtotime( $event['start_datetime'] ) ) ); ?></span>
							<span class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></span>
							<span class="cts-date-month"><?php echo esc_html( $event['start_month'] ); ?> <?php echo esc_html( $event['start_year'] ); ?></span>
						</div>
						<?php if ( $show_time ) : ?>
						<div class="cts-time-stack">
							<span class="cts-time-label"><?php esc_html_e( 'Start', 'churchtools-suite' ); ?></span>
							<span class="cts-time-value"><?php echo esc_html( $event['start_time'] ); ?></span>
							<?php if ( ! empty( $event['end_time'] ) ) : ?>
								<span class="cts-time-label"><?php esc_html_e( 'Ende', 'churchtools-suite' ); ?></span>
								<span class="cts-time-value"><?php echo esc_html( $event['end_time'] ); ?></span>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
					
					<!-- Right Side: Main Content -->
					<div class="cts-event-main">
						
						<!-- Calendar Badge -->
						<?php if ( ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-calendar-badge" style="background-color: <?php echo esc_attr( $event['calendar_color'] ?? '#0078d4' ); ?>">
								<?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<!-- Title -->
						<h3 class="cts-event-title"><?php echo esc_html( $event['title'] ); ?></h3>
						
						<!-- Description -->
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
						<div class="cts-event-description">
							<?php echo esc_html( wp_trim_words( $event['event_description'], 40 ) ); ?>
						</div>
						<?php endif; ?>
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
						<div class="cts-event-description">
							<?php echo esc_html( wp_trim_words( $event['appointment_description'], 40 ) ); ?>
						</div>
						<?php endif; ?>

						<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
							<div class="cts-event-location">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
									<path d="M8 0C5.243 0 3 2.243 3 5c0 3.854 5 11 5 11s5-7.146 5-11c0-2.757-2.243-5-5-5zm0 7.5c-1.378 0-2.5-1.122-2.5-2.5S6.622 2.5 8 2.5 10.5 3.622 10.5 5 9.378 7.5 8 7.5z"/>
								</svg>
								<?php
								if ( ! empty( $event['address_name'] ) ) {
									echo esc_html( $event['address_name'] );
								} elseif ( ! empty( $event['location_name'] ) ) {
									echo esc_html( $event['location_name'] );
								} else {
									echo esc_html( $event['address_street'] ?? '' );
								}

								$info_parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
								if ( ! empty( $info_parts ) ) {
									$info_text = implode( ', ', $info_parts );
									?> <span class="cts-info-popup" title="<?php echo esc_attr( $info_text ); ?>"> ⓘ</span><?php
								}
								?>
							</div>
						<?php endif; ?>
						<!-- Title -->
						<h3 class="cts-event-title"><?php echo esc_html( $event['title'] ); ?></h3>

						<!-- Services -->
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-event-services">
								<?php foreach ( $event['services'] as $service ) : ?>
									<div class="cts-service-item">
										<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
											<path d="M8 8c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
										</svg>
										<span class="cts-service-name"><?php echo esc_html( $service['service_name'] ); ?></span>
										<?php if ( ! empty( $service['person_name'] ) ) : ?>
											<span class="cts-service-person"><?php echo esc_html( $service['person_name'] ); ?></span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						
						<!-- Calendar Name -->
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-event-calendar" style="margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280;">
								📅 <?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<!-- Tags -->
						<?php if ( ! empty( $event['tags'] ) ) : ?>
							<?php
							$tags = is_string( $event['tags'] ) ? json_decode( $event['tags'], true ) : $event['tags'];
							if ( is_array( $tags ) && ! empty( $tags ) ) :
							?>
							<div class="cts-event-tags" style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
								<?php foreach ( $tags as $tag ) : ?>
									<span class="cts-tag" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>; color: #fff;">
										<?php echo esc_html( $tag['name'] ?? '' ); ?>
									</span>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
						<?php endif; ?>
						
				<div class="cts-reveal-border"></div>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-list-fluent {
	max-width: 1000px;
	margin: 0 auto;
}

/* Empty State */
.cts-list-empty {
	padding: 60px 20px;
	text-align: center;
	color: #6b7280;
}

.cts-empty-icon {
	font-size: 64px;
	display: block;
	margin-bottom: 16px;
}

.cts-list-empty h3 {
	margin: 0 0 8px;
	font-size: 18px;
	font-weight: 600;
	color: #374151;
}

.cts-list-empty p {
	margin: 0;
	font-size: 14px;
}

/* Event Card */
.cts-event-fluent {
	position: relative;
	background: rgba(255,255,255,0.7);
	backdrop-filter: blur(30px) saturate(150%);
	border: 1px solid rgba(0,0,0,0.08);
	border-radius: 8px;
	margin-bottom: 16px;
	overflow: hidden;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cts-event-fluent:hover {
	background: rgba(255,255,255,0.85);
	box-shadow: 0 8px 32px rgba(0,0,0,0.12);
	transform: translateY(-4px);
}

.cts-event-fluent:last-child {
	margin-bottom: 0;
}

/* Reveal Border (Fluent Design) */
.cts-reveal-border {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	border: 2px solid transparent;
	border-radius: 8px;
	pointer-events: none;
	transition: border-color 0.3s;
}

.cts-event-fluent:hover .cts-reveal-border {
	border-color: rgba(0,120,212,0.4);
}

/* Content */
.cts-event-content {
	display: flex;
	gap: 32px;
	padding: 24px;
}

/* Sidebar */
.cts-event-sidebar {
	flex-shrink: 0;
	width: 140px;
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.cts-date-stack {
	display: flex;
	flex-direction: column;
	gap: 4px;
	padding: 16px;
	background: rgba(0,120,212,0.08);
	border-left: 3px solid #0078d4;
	border-radius: 4px;
}

.cts-date-weekday {
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: #0078d4;
}

.cts-date-day {
	font-size: 32px;
	font-weight: 300;
	line-height: 1;
	color: #1f2937;
}

.cts-date-month {
	font-size: 13px;
	font-weight: 600;
	color: #6b7280;
}

.cts-time-stack {
	display: flex;
	flex-direction: column;
	gap: 6px;
	padding: 12px;
	background: rgba(0,0,0,0.03);
	border-radius: 4px;
}

.cts-time-label {
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.3px;
	color: #8c8f94;
}

.cts-time-value {
	font-size: 16px;
	font-weight: 600;
	color: #1f2937;
	font-variant-numeric: tabular-nums;
}

/* Main Content */
.cts-event-main {
	flex: 1;
	min-width: 0;
}

.cts-calendar-badge {
	display: inline-block;
	padding: 4px 12px;
	margin-bottom: 12px;
	background-color: #0078d4;
	color: #fff;
	border-radius: 4px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.cts-event-title {
	margin: 0 0 12px;
	font-size: 24px;
	font-weight: 600;
	color: #1f2937;
	line-height: 1.3;
}

.cts-event-description {
	margin-bottom: 16px;
	font-size: 14px;
	line-height: 1.6;
	color: #374151;
}

.cts-event-location {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 16px;
	padding: 10px 14px;
	background: rgba(0,0,0,0.03);
	border-radius: 4px;
	font-size: 14px;
	font-weight: 500;
	color: #374151;
}

.cts-event-location svg {
	flex-shrink: 0;
	color: #0078d4;
}

.cts-event-services {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.cts-service-item {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 10px 14px;
	background: rgba(0,120,212,0.05);
	border-left: 2px solid #0078d4;
	border-radius: 4px;
	font-size: 14px;
}

.cts-service-item svg {
	flex-shrink: 0;
	color: #0078d4;
}

.cts-service-name {
	font-weight: 600;
	color: #1f2937;
}

.cts-service-person {
	color: #6b7280;
	margin-left: auto;
}

/* Responsive */
@media (max-width: 768px) {
	.cts-event-content {
		flex-direction: column;
		gap: 20px;
		padding: 20px;
	}
	
	.cts-event-sidebar {
		width: 100%;
		flex-direction: row;
	}
	
	.cts-date-stack,
	.cts-time-stack {
		flex: 1;
	}
	
	.cts-event-title {
		font-size: 20px;
	}
}
</style>
