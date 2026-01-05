<?php
/**
 * Search View - Classic
 *
 * Event-Suchformular mit Echtzeit-Filterung
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

$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
?>

<div class="churchtools-suite-wrapper">
<div class="cts-search cts-search-classic" data-view="search-classic">
	
	<!-- Search Form -->
	<div class="cts-search-form">
		<input 
			type="text" 
			class="cts-search-input" 
			placeholder="<?php esc_attr_e( 'Termine durchsuchen...', 'churchtools-suite' ); ?>"
			autocomplete="off"
		/>
		<span class="cts-search-icon">🔍</span>
	</div>
	
	<!-- Results -->
	<div class="cts-search-results">
		
		<?php if ( empty( $events ) ) : ?>
			
			<div class="cts-search-empty">
				<p><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></p>
			</div>
			
		<?php else : ?>
			
			<div class="cts-search-items">
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
			<?php foreach ( $events as $event ) : ?>
				<div class="cts-search-item <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>"<?php endif; ?> data-event-title="<?php echo esc_attr( $event['title'] ); ?>">
						<div class="cts-search-item-title">
							<?php echo esc_html( $event['title'] ); ?>
						</div>
						
						<div class="cts-search-item-meta">
							<span class="cts-search-date"><?php echo esc_html( $event['start_date'] ?? '' ); ?></span>
							<span class="cts-search-time"><?php echo esc_html( $event['start_time'] ?? '' ); ?></span>
						</div>
						
						<?php if ( $show_location && ! empty( $event['location'] ) ) : ?>
							<div class="cts-search-location">
								📍 <?php echo esc_html( $event['location'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
							<div class="cts-search-description">
								<?php echo wp_kses_post( wp_trim_words( $event['event_description'], 15 ) ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
							<div class="cts-search-description">
								<?php echo wp_kses_post( wp_trim_words( $event['appointment_description'], 15 ) ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-search-services" style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
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
									echo ' <span style="font-weight: 600;">+' . ( count( $event['services'] ) - 2 ) . '</span>';
								}
								?>
							</div>
						<?php endif; ?>
						
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
							<div class="cts-search-calendar" style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">
								📅 <?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( ! empty( $event['tags'] ) ) : ?>
							<?php
							$tags = is_string( $event['tags'] ) ? json_decode( $event['tags'], true ) : $event['tags'];
							if ( is_array( $tags ) && ! empty( $tags ) ) :
							?>
							<div class="cts-search-tags" style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
								<?php foreach ( $tags as $tag ) : ?>
									<span class="cts-tag" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>; color: #fff;">
										<?php echo esc_html( $tag['name'] ?? '' ); ?>
									</span>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
						<?php endif; ?>
						
					</div>
				<?php endforeach; ?>
			</div>
			
		<?php endif; ?>
		
	</div>
	
</div>
</div>

<script>
document.addEventListener( 'DOMContentLoaded', function() {
	const searchInput = document.querySelector( '.cts-search-input' );
	const searchItems = document.querySelectorAll( '.cts-search-item' );
	
	if ( ! searchInput ) return;
	
	searchInput.addEventListener( 'input', function( e ) {
		const query = e.target.value.toLowerCase();
		
		searchItems.forEach( item => {
			const title = item.getAttribute( 'data-event-title' ).toLowerCase();
			const shouldShow = title.includes( query ) || query === '';
			
			item.style.display = shouldShow ? 'block' : 'none';
		});
	});
});
</script>

<style>
.cts-search-classic {
	max-width: 100%;
}

.cts-search-form {
	position: relative;
	margin-bottom: 2rem;
}

.cts-search-input {
	width: 100%;
	padding: 0.75rem 1rem;
	padding-right: 2.5rem;
	border: 2px solid #e5e7eb;
	border-radius: 0.5rem;
	font-size: 1rem;
	transition: border-color 0.2s ease;
}

.cts-search-input:focus {
	outline: none;
	border-color: #3b82f6;
	box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.cts-search-icon {
	position: absolute;
	right: 1rem;
	top: 50%;
	transform: translateY( -50% );
	pointer-events: none;
	color: #9ca3af;
}

.cts-search-items {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}

.cts-search-item {
	padding: 1rem;
	border: 1px solid #e5e7eb;
	border-radius: 0.5rem;
	background: white;
	cursor: pointer;
	transition: all 0.2s ease;
}

.cts-search-item:hover {
	border-color: #3b82f6;
	box-shadow: 0 4px 6px rgba( 59, 130, 246, 0.1 );
}

.cts-search-item-title {
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 0.5rem;
}

.cts-search-item-meta {
	display: flex;
	gap: 1rem;
	font-size: 0.875rem;
	color: #6b7280;
	margin-bottom: 0.5rem;
}

.cts-search-location {
	font-size: 0.875rem;
	color: #059669;
	margin-bottom: 0.5rem;
}

.cts-search-description {
	font-size: 0.875rem;
	color: #6b7280;
	margin-top: 0.5rem;
}

.cts-search-empty {
	text-align: center;
	padding: 2rem;
	color: #6b7280;
}
</style>
