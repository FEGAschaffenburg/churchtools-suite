<?php
/**
 * Grid View - Modern
 *
 * Modernes Grid mit großen Cards und Bildern
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

$columns = isset( $args['columns'] ) ? absint( $args['columns'] ) : 3;
// Normalize boolean args (support strings from Gutenberg)
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-grid cts-grid-modern" data-view="grid-modern" style="--grid-columns: <?php echo esc_attr( $columns ); ?>;">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-grid-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
			<div class="cts-grid-card-modern <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>"<?php endif; ?>>
				
				<!-- Header with Gradient -->
				<div class="cts-card-header" style="background: linear-gradient(135deg, <?php echo esc_attr( $event['calendar_color'] ?? '#667eea' ); ?> 0%, #764ba2 100%);">
					<div class="cts-header-content">
						<div class="cts-date-badge">
							<span class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></span>
							<span class="cts-date-month"><?php echo esc_html( $event['start_month_short'] ); ?></span>
						</div>
						<?php if ( $show_time ) : ?>
						<div class="cts-time-badge">
							<span class="dashicons dashicons-clock"></span>
							<?php echo esc_html( $event['start_time'] ); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
				
				<!-- Body -->
				<div class="cts-card-body">
					
					<h3 class="cts-card-title"><?php echo esc_html( $event['title'] ); ?></h3>
					
					<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
						<p class="cts-card-description">
							<?php echo esc_html( wp_trim_words( $event['description'], 25 ) ); ?>
						</p>
					<?php endif; ?>
                    
					<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
						<div class="cts-card-location">
							<span class="dashicons dashicons-location"></span>
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
					
				</div>
				
				<!-- Footer -->
				<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
					<div class="cts-card-footer-modern">
						<div class="cts-services-grid">
							<?php foreach ( $event['services'] as $service ) : ?>
								<div class="cts-service-badge-modern">
									<span class="cts-service-icon">👤</span>
									<div class="cts-service-info">
										<span class="cts-service-name"><?php echo esc_html( $service['service_name'] ); ?></span>
										<?php if ( ! empty( $service['person_name'] ) ) : ?>
											<span class="cts-person-name"><?php echo esc_html( $service['person_name'] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-grid-modern {
	display: grid;
	grid-template-columns: repeat(var(--grid-columns, 3), 1fr);
	gap: 28px;
}

/* Empty State */
.cts-grid-empty {
	grid-column: 1 / -1;
	padding: 60px 20px;
	text-align: center;
	color: #6b7280;
}

.cts-empty-icon {
	font-size: 64px;
	display: block;
	margin-bottom: 16px;
}

.cts-grid-empty h3 {
	margin: 0 0 8px;
	font-size: 18px;
	font-weight: 600;
	color: #374151;
}

.cts-grid-empty p {
	margin: 0;
	font-size: 14px;
}

/* Card */
.cts-grid-card-modern {
	background: #fff;
	border-radius: 16px;
	overflow: hidden;
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
	transition: transform 0.3s, box-shadow 0.3s;
	cursor: pointer;
}

.cts-grid-card-modern:hover {
	transform: translateY(-8px);
	box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}

/* Header */
.cts-card-header {
	padding: 24px;
	min-height: 140px;
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
}

.cts-header-content {
	width: 100%;
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
}

.cts-date-badge {
	display: flex;
	flex-direction: column;
	align-items: center;
	background: rgba(255,255,255,0.25);
	backdrop-filter: blur(10px);
	padding: 12px 16px;
	border-radius: 12px;
	color: #fff;
}

.cts-date-day {
	font-size: 32px;
	font-weight: 700;
	line-height: 1;
	margin-bottom: 2px;
}

.cts-date-month {
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.cts-time-badge {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 8px 14px;
	background: rgba(255,255,255,0.25);
	backdrop-filter: blur(10px);
	border-radius: 20px;
	color: #fff;
	font-size: 13px;
	font-weight: 500;
}

.cts-time-badge .dashicons {
	width: 16px;
	height: 16px;
	font-size: 16px;
}

/* Body */
.cts-card-body {
	padding: 24px;
}

.cts-card-title {
	margin: 0 0 12px;
	font-size: 20px;
	font-weight: 700;
	color: #1f2937;
	line-height: 1.3;
}

.cts-card-description {
	margin: 0 0 16px;
	font-size: 14px;
	line-height: 1.6;
	color: #6b7280;
}

.cts-card-location {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 14px;
	font-weight: 500;
	color: #374151;
}

.cts-card-location .dashicons {
	width: 18px;
	height: 18px;
	font-size: 18px;
	color: #667eea;
}

/* Footer */
.cts-card-footer-modern {
	padding: 20px 24px;
	background: #f9fafb;
	border-top: 1px solid #e5e7eb;
}

.cts-services-grid {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.cts-service-badge-modern {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 10px;
	background: #fff;
	border-radius: 8px;
	border: 1px solid #e5e7eb;
}

.cts-service-icon {
	font-size: 20px;
	line-height: 1;
}

.cts-service-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.cts-service-name {
	font-size: 13px;
	font-weight: 600;
	color: #1f2937;
}

.cts-person-name {
	font-size: 12px;
	color: #6b7280;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-grid-modern {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 640px) {
	.cts-grid-modern {
		grid-template-columns: 1fr;
	}
}
</style>
