<?php
/**
 * List View - Medium
 *
 * Eine Zeile mit Datumbox links und Inhalten rechts
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
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-medium" data-view="list-medium">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			
<div class="cts-event-medium cts-event-clickable" data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>">
				
				<!-- Datumbox -->
				<div class="cts-date-box">
					<div class="cts-date-day"><?php echo esc_html( $event['start_day'] ); ?></div>
					<div class="cts-date-month"><?php echo esc_html( $event['start_month'] ); ?></div>
					<div class="cts-date-year"><?php echo esc_html( $event['start_year'] ); ?></div>
				</div>
				
				<!-- Content - 2-zeilig -->
				<div class="cts-event-content">
					
					<!-- Zeile 1: Titel & Description -->
					<div class="cts-content-line1">
						<h3 class="cts-event-title"><?php echo esc_html( $event['title'] ); ?></h3>
				<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
					<span class="cts-description"> - <?php echo esc_html( wp_trim_words( $event['description'], 15 ) ); ?></span>
		<?php endif; ?>
			</div>
			
			<!-- Zeile 2: Uhrzeit & Services -->
			<div class="cts-content-line2">
				<?php if ( $show_time ) : ?>
				<span class="cts-meta-time">
					<?php echo esc_html( $event['start_time'] ); ?>
					<?php if ( ! empty( $event['end_time'] ) ) : ?>
						- <?php echo esc_html( $event['end_time'] ); ?>
					<?php endif; ?>
				</span>
				<?php endif; ?>
				
			<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
				<span class="cts-meta-services">
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
				</span>
			<?php endif; ?>
			</div>
			
		</div>
		
		<!-- Ort - eigener Block rechts -->
	<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
		<div class="cts-location-block">
			<?php echo esc_html( $event['location_name'] ); ?>
		</div>
	<?php endif; ?>
		
	</div>
	
<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>
