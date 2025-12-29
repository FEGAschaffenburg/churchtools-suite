<?php
/**
 * List View - Classic with Services
 *
 * Kompakte einzeilige Liste mit Services - alles in einer schmalen Zeile
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
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-classic" data-view="list-classic-services">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			
			<div class="cts-event-classic" data-event-id="<?php echo esc_attr( $event['id'] ); ?>">
				
				<!-- Datum (Text) -->
				<div class="cts-date">
					<?php echo esc_html( $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] ); ?>
				</div>
				
				<!-- Uhrzeit (Von-Bis) -->
				<?php if ( $show_time ) : ?>
				<div class="cts-time">
					<?php echo esc_html( $event['start_time'] ); ?>
					<?php if ( ! empty( $event['end_time'] ) ) : ?>
						- <?php echo esc_html( $event['end_time'] ); ?>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				
				<!-- Titel & Description -->
				<div class="cts-title-block">
					<div class="cts-title">
						<?php echo esc_html( $event['title'] ); ?>
					</div>
					<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
						<div class="cts-description">
							<?php echo esc_html( wp_trim_words( $event['description'], 15 ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				
				<!-- Services mit Personen (ALWAYS shown) -->
				<div class="cts-services">
					<?php if ( ! empty( $event['services'] ) ) : ?>
						<?php 
						$service_items = array();
						foreach ( array_slice( $event['services'], 0, 2 ) as $s ) {
							if ( ! empty( $s['person_name'] ) ) {
								$service_items[] = $s['service_name'] . ': ' . $s['person_name'];
							} else {
								$service_items[] = $s['service_name'];
							}
						}
						echo esc_html( implode( ' | ', $service_items ) );
						if ( count( $event['services'] ) > 2 ) {
							echo ' <span class="cts-more">+' . ( count( $event['services'] ) - 2 ) . '</span>';
						}
						?>
					<?php endif; ?>
				</div>
				
				<!-- Ort -->
				<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
				<div class="cts-location">
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
				
			</div>
			
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>
