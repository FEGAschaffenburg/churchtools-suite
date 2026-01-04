<?php
/**
 * Grid View - Colorful
 *
 * Buntes Grid mit farbigen Karten basierend auf Calendar-Farbe
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
<div class="cts-grid cts-grid-colorful" data-view="grid-colorful" style="--grid-columns: <?php echo esc_attr( $columns ); ?>;">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-grid-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
			<div class="cts-grid-card-colorful <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>"<?php endif; ?> style="--card-color: <?php echo esc_attr( $event['calendar_color'] ?? '#667eea' ); ?>;">
				
				<!-- Color Strip -->
				<div class="cts-color-strip"></div>
				
				<!-- Content -->
				<div class="cts-card-content-colorful">
					
					<!-- Header -->
					<div class="cts-card-header-colorful">
						<div class="cts-date-circle">
							<span class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></span>
						</div>
						<div class="cts-meta-stack">
							<?php if ( $show_time ) : ?>
							<div class="cts-time-info">
								⏰ <?php echo esc_html( $event['start_time'] ); ?>
								<?php if ( ! empty( $event['end_time'] ) ) : ?>
									- <?php echo esc_html( $event['end_time'] ); ?>
								<?php endif; ?>
							</div>
							<?php endif; ?>
							<div class="cts-month-info">
								<?php echo esc_html( $event['start_month'] ); ?> <?php echo esc_html( $event['start_year'] ); ?>
							</div>
						</div>
					</div>
					
					<!-- Title -->
					<h3 class="cts-card-title-colorful"><?php echo esc_html( $event['title'] ); ?></h3>
					
					<!-- Description -->
					<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
						<p class="cts-card-description-colorful">
							<?php echo esc_html( wp_trim_words( $event['description'], 20 ) ); ?>
						</p>
					<?php endif; ?>
					
					<!-- Details -->
					<div class="cts-card-details">
						
						<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
							<div class="cts-detail-item">
								<span class="cts-detail-icon">📍</span>
								<span class="cts-detail-text"><?php
								if ( ! empty( $event['address_name'] ) ) {
									echo esc_html( $event['address_name'] );
								} elseif ( ! empty( $event['location_name'] ) ) {
									echo esc_html( $event['location_name'] );
								} else {
									$parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
									echo esc_html( implode( ', ', $parts ) );
								}
								?></span>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) : ?>
							<div class="cts-detail-item">
								<span class="cts-detail-icon">📅</span>
								<span class="cts-detail-text"><?php echo esc_html( $event['calendar_name'] ); ?></span>
							</div>
						<?php endif; ?>
						
					</div>
					
					<!-- Services -->
					<?php if ( $show_services && ! empty( $event['services'] ) : ?>
						<div class="cts-services-colorful">
							<?php foreach ( array_slice( $event['services'], 0, 3 ) as $service ) : ?>
								<span class="cts-service-pill">
									👤 <?php echo esc_html( $service['service_name'] ); ?>
								</span>
							<?php endforeach; ?>
							<?php if ( count( $event['services'] ) > 3 ) : ?>
								<span class="cts-service-pill cts-pill-more">+<?php echo count( $event['services'] ) - 3; ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
				</div>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-grid-colorful {
	display: grid;
	grid-template-columns: repeat(var(--grid-columns, 3), 1fr);
	gap: 24px;
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
.cts-grid-card-colorful {
	position: relative;
	background: #fff;
	border-radius: 12px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	transition: transform 0.2s, box-shadow 0.2s;
	cursor: pointer;
}

.cts-grid-card-colorful:hover {
	transform: translateY(-4px);
	box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

/* Color Strip */
.cts-color-strip {
	height: 8px;
	background: var(--card-color);
}

/* Content */
.cts-card-content-colorful {
	padding: 20px;
}

/* Header */
.cts-card-header-colorful {
	display: flex;
	align-items: center;
	gap: 16px;
	margin-bottom: 16px;
}

.cts-date-circle {
	flex-shrink: 0;
	width: 60px;
	height: 60px;
	border-radius: 50%;
	background: var(--card-color);
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
}

.cts-date-day {
	font-size: 24px;
	font-weight: 700;
	line-height: 1;
}

.cts-meta-stack {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.cts-time-info {
	font-size: 14px;
	font-weight: 600;
	color: #1f2937;
}

.cts-month-info {
	font-size: 13px;
	color: #6b7280;
}

/* Title */
.cts-card-title-colorful {
	margin: 0 0 12px;
	font-size: 18px;
	font-weight: 700;
	color: #1f2937;
	line-height: 1.3;
}

/* Description */
.cts-card-description-colorful {
	margin: 0 0 16px;
	font-size: 14px;
	line-height: 1.6;
	color: #6b7280;
}

/* Details */
.cts-card-details {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin-bottom: 16px;
}

.cts-detail-item {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	color: #374151;
}

.cts-detail-icon {
	font-size: 16px;
	line-height: 1;
}

/* Services */
.cts-services-colorful {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	padding-top: 16px;
	border-top: 1px solid #f3f4f6;
}

.cts-service-pill {
	padding: 6px 12px;
	background: rgba(0,0,0,0.05);
	border-radius: 16px;
	font-size: 12px;
	font-weight: 600;
	color: #374151;
}

.cts-pill-more {
	background: var(--card-color);
	color: #fff;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-grid-colorful {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 640px) {
	.cts-grid-colorful {
		grid-template-columns: 1fr;
	}
}
</style>
