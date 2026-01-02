<?php
/**
 * List View - Classic
 *
 * Kompakte einzeilige Liste - alles in einer schmalen Zeile
 *
 * @package ChurchTools_Suite
 * @since   0.6.3.12
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Sprint 3: Parse boolean parameters (support strings from Gutenberg attributes)
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-classic" data-view="list-classic">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			
<div class="cts-event-classic cts-event-clickable" data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>">
				
				<!-- Datum (Text) -->
				<div class="cts-date">
					<?php echo esc_html( $event['start_day'] . '. ' . $event['start_month'] ); ?>
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
				
				<!-- Kalender-Name -->
				<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
					<div class="cts-calendar-name">
						<?php echo esc_html( $event['calendar_name'] ); ?>
					</div>
				<?php endif; ?>
				
				<!-- Titel & Description -->
				<div class="cts-title-block">
					<span class="cts-title"><?php echo esc_html( $event['title'] ); ?></span>
					<?php if ( ! empty( $args['show_description'] ) && ! empty( $event['description'] ) ) : ?>
						<span class="cts-description"> - <?php echo esc_html( wp_trim_words( $event['description'], 15 ) ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
					<div class="cts-services">
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
					</div>
				<?php endif; ?>
			
			<!-- Ort -->
			<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
				<div class="cts-list-location">
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
		
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>
