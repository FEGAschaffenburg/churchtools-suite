<?php
/**
 * List View - Modern
 *
 * Modernes Card-Layout - kompakt und mehrzeilig
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
// Normalize boolean args (support strings from Gutenberg)
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-modern" data-view="list-modern">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			
			<div class="cts-event-modern" data-event-id="<?php echo esc_attr( $event['id'] ); ?>">
				
				<!-- Header kompakt -->
				<div class="cts-event-header" style="background: <?php echo ! empty( $event['calendar_color'] ) ? 'linear-gradient(135deg, ' . esc_attr( $event['calendar_color'] ) . ' 0%, ' . esc_attr( $event['calendar_color'] ) . 'dd 100%)' : 'var(--wp--preset--color--primary, var(--wp-admin-theme-color, #2271b1))'; ?>;">
					<div class="cts-event-date">
						<span class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></span>
						<span class="cts-date-month"><?php echo esc_html( $event['start_month'] ); ?></span>
					</div>
					<?php if ( $show_time ) : ?>
					<div class="cts-event-time">
						<?php echo esc_html( $event['start_time'] ); ?>
						<?php if ( ! empty( $event['end_time'] ) ) : ?>
							- <?php echo esc_html( $event['end_time'] ); ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="cts-event-body">
					
					<!-- Titel -->
					<h3 class="cts-event-title"><?php echo esc_html( $event['title'] ); ?></h3>
					
					<!-- Beschreibung -->
					<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
						<div class="cts-event-description">
							<?php echo esc_html( wp_trim_words( $event['description'], 20 ) ); ?>
						</div>
					<?php endif; ?>
					
					<!-- Meta Info -->
					<div class="cts-event-meta">
						
						<!-- Ort -->
						<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
							<span class="cts-meta-location">
								📍 <?php
								if ( ! empty( $event['address_name'] ) ) {
									echo esc_html( $event['address_name'] );
								} elseif ( ! empty( $event['location_name'] ) ) {
									echo esc_html( $event['location_name'] );
								} else {
									$parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
									echo esc_html( implode( ', ', $parts ) );
								}
								?>
							</span>
						<?php endif; ?>
						
						<!-- Services -->
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<span class="cts-meta-services">
								<?php 
								$service_names = array_map( function( $s ) { 
									return $s['service_name']; 
								}, array_slice( $event['services'], 0, 2 ) );
								echo esc_html( implode( ', ', $service_names ) );
								if ( count( $event['services'] ) > 2 ) {
									echo ' +' . ( count( $event['services'] ) - 2 );
								}
								?>
							</span>
						<?php endif; ?>
						
					</div>
					
				</div>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

<?php foreach ( array_slice( $event['services'], 0, 3 ) as $service ) : ?>
									<span class="cts-service-badge">
										<?php echo esc_html( $service['service_name'] ); ?>
									</span>
								<?php endforeach; ?>
								<?php if ( count( $event['services'] ) > 3 ) : ?>
									<span class="cts-service-more">+<?php echo count( $event['services'] ) - 3; ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						
					</div>
					
				</div>
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>

