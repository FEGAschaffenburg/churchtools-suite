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
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
<div class="cts-event-medium <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>"<?php endif; ?>>
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
			
			<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
				<span class="cts-meta-calendar" style="display: block; margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
					📅 <?php echo esc_html( $event['calendar_name'] ); ?>
				</span>
			<?php endif; ?>
			
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
			</div>
			
		</div>
		
		<!-- Ort - eigener Block rechts -->
	<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
		<div class="cts-location-block">
			<?php
			// Display preferred label (address_name or location_name or street)
			if ( ! empty( $event['address_name'] ) ) {
				echo esc_html( $event['address_name'] );
			} elseif ( ! empty( $event['location_name'] ) ) {
				echo esc_html( $event['location_name'] );
			} else {
				echo esc_html( $event['address_street'] );
			}
			// Prepare info popup content (street, zip, city)
			$info_parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
			if ( ! empty( $info_parts ) ) {
				$info_text = implode( ', ', $info_parts );
				?>
				<span class="cts-info-popup" title="<?php echo esc_attr( $info_text ); ?>"> ⓘ</span>
				<?php
			}
			?>
		</div>
	<?php endif; ?>
		
	</div>
	
<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>
