<?php
/**
 * Grid View - Simple
 *
 * Einfaches Grid mit Cards in 3-Spalten Layout
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
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-grid cts-grid-simple" data-view="grid-simple" style="--grid-columns: <?php echo esc_attr( $columns ); ?>;">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-grid-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			
			<div class="cts-grid-card cts-event-clickable" data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>">
				
				<!-- Date Badge -->
				<div class="cts-card-date" style="background-color: <?php echo esc_attr( $event['calendar_color'] ?? '#667eea' ); ?>">
					<div class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></div>
					<div class="cts-date-month"><?php echo esc_html( $event['start_month_short'] ); ?></div>
				<div class="cts-date-year"><?php echo esc_html( $event['start_year'] ); ?></div>
					<h3 class="cts-card-title"><?php echo esc_html( $event['title'] ); ?></h3>
					
					<!-- Time -->
					<?php if ( $show_time ) : ?>
					<div class="cts-card-meta">
						<span class="dashicons dashicons-clock"></span>
						<?php echo esc_html( $event['start_time'] ); ?>
						<?php if ( ! empty( $event['end_time'] ) ) : ?>
							- <?php echo esc_html( $event['end_time'] ); ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					
					<!-- Location -->
					<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
						<div class="cts-card-meta">
							<span class="dashicons dashicons-location"></span>
							<?php
							if ( ! empty( $event['address_name'] ) ) {
								echo esc_html( $event['address_name'] );
							} elseif ( ! empty( $event['location_name'] ) ) {
								echo esc_html( $event['location_name'] );
							} else {
								$parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
								echo esc_html( implode( ', ', $parts ) );
							}
							?>
						</div>
					<?php endif; ?>
					
					<!-- Description -->
					<?php if ( ! empty( $args['show_description'] ) && ! empty( $event['description'] ) ) : ?>
						<p class="cts-card-description">
							<?php echo esc_html( wp_trim_words( $event['description'], 20 ) ); ?>
						</p>
					<?php endif; ?>
					
				</div>
				
				<!-- Services Footer -->
				<?php if ( ! empty( $event['services'] ) ) : ?>
					<div class="cts-card-footer">
						<?php foreach ( array_slice( $event['services'], 0, 2 ) as $service ) : ?>
							<span class="cts-service-tag">
								<?php echo esc_html( $service['service_name'] ); ?>
							</span>
						<?php endforeach; ?>
						<?php if ( count( $event['services'] ) > 2 ) : ?>
							<span class="cts-service-more">+<?php echo count( $event['services'] ) - 2; ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-grid-simple {
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
.cts-grid-card {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	overflow: hidden;
	display: flex;
	flex-direction: column;
	transition: transform 0.2s, box-shadow 0.2s;
	cursor: pointer;
}

.cts-grid-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

/* Date Badge */
.cts-card-date {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 16px;
	background-color: #667eea;
	color: #fff;
}

.cts-date-day {
	font-size: 32px;
	font-weight: 700;
	line-height: 1;
	margin-bottom: 4px;
}

.cts-date-month {
	font-size: 14px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	opacity: 0.9;
}

.cts-date-year {
	font-size: 12px;
	font-weight: 500;
	margin-top: 4px;
	opacity: 0.8;
}

/* Content */
.cts-card-content {
	padding: 20px;
	flex: 1;
}

.cts-card-title {
	margin: 0 0 16px;
	font-size: 18px;
	font-weight: 700;
	color: #1f2937;
	line-height: 1.3;
}

.cts-card-meta {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 10px;
	font-size: 14px;
	color: #6b7280;
}

.cts-card-meta .dashicons {
	width: 16px;
	height: 16px;
	font-size: 16px;
}

.cts-card-description {
	margin: 16px 0 0;
	font-size: 14px;
	line-height: 1.6;
	color: #6b7280;
}

/* Footer */
.cts-card-footer {
	padding: 16px;
	border-top: 1px solid #f3f4f6;
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}

.cts-service-tag {
	padding: 4px 10px;
	background: #f3f4f6;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	color: #374151;
}

.cts-service-more {
	padding: 4px 10px;
	background: #667eea;
	color: #fff;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 700;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-grid-simple {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 640px) {
	.cts-grid-simple {
		grid-template-columns: 1fr;
	}
}
</style>
