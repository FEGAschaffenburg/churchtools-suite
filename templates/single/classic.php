<?php
/**
 * Template: Single Event - Classic
 * 
 * Klassische Einzeltermin-Ansicht mit detaillierten Informationen
 * Verwendung: [cts_event id="123" template="classic"]
 *
 * @package ChurchTools_Suite
 * @since   0.7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Available variables: $event, $calendar, $services
?>

<article class="cts-single-event cts-single-classic">
	
	<!-- Event Header -->
	<header class="cts-single-header">
		<?php if ( ! empty( $calendar ) ) : ?>
			<div class="cts-single-calendar-badge" style="background-color: <?php echo esc_attr( $calendar->color ?? '#0073aa' ); ?>;">
				<?php echo esc_html( $calendar->name ); ?>
			</div>
		<?php endif; ?>
		
		<h1 class="cts-single-title"><?php echo esc_html( $event->title ); ?></h1>
	</header>
	
	<!-- Event Meta Information -->
	<div class="cts-single-meta">
		
		<!-- Date & Time -->
		<div class="cts-single-meta-item cts-single-datetime">
			<svg class="cts-single-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
				<line x1="16" y1="2" x2="16" y2="6"/>
				<line x1="8" y1="2" x2="8" y2="6"/>
				<line x1="3" y1="10" x2="21" y2="10"/>
			</svg>
			<div class="cts-single-meta-content">
				<strong><?php esc_html_e( 'Datum & Uhrzeit:', 'churchtools-suite' ); ?></strong>
				<div class="cts-single-date">
					<?php
					$start = new DateTime( $event->start_datetime );
					$end = $event->end_datetime ? new DateTime( $event->end_datetime ) : null;
					
					// Date formatting
					echo esc_html( $start->format( get_option( 'date_format' ) ) );
					
					// Time formatting
					if ( ! $event->is_all_day ) {
						echo ' | ' . esc_html( $start->format( get_option( 'time_format' ) ) );
						if ( $end ) {
							echo ' - ' . esc_html( $end->format( get_option( 'time_format' ) ) );
						}
					} else {
						echo ' <span class="cts-all-day-badge">' . esc_html__( 'Ganztägig', 'churchtools-suite' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>
		
		<!-- Location -->
		<?php if ( ! empty( $event->address_name ) || ! empty( $event->location_name ) || ! empty( $event->address_street ) ) : ?>
		<div class="cts-single-meta-item cts-single-location">
			<svg class="cts-single-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
				<circle cx="12" cy="10" r="3"/>
			</svg>
			<div class="cts-single-meta-content">
				<strong><?php esc_html_e( 'Ort:', 'churchtools-suite' ); ?></strong>
				<div><?php
				if ( ! empty( $event->address_name ) ) {
					echo esc_html( $event->address_name );
				} elseif ( ! empty( $event->location_name ) ) {
					echo esc_html( $event->location_name );
				} else {
					echo esc_html( $event->address_street ?? '' );
				}

				$info_parts = array_filter( [ $event->address_street ?? '', $event->address_zip ?? '', $event->address_city ?? '' ] );
				if ( ! empty( $info_parts ) ) {
					$info_text = implode( ', ', $info_parts );
					?> <span class="cts-info-popup" title="<?php echo esc_attr( $info_text ); ?>"> ⓘ</span><?php
				}
				?></div>
			</div>
		</div>
		<?php endif; ?>
		
	</div>
	
	<!-- Event Description -->
	<?php if ( ! empty( $event->description ) ) : ?>
	<div class="cts-single-description">
		<h2><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></h2>
		<div class="cts-single-description-content">
			<?php echo wp_kses_post( wpautop( $event->description ) ); ?>
		</div>
	</div>
	<?php endif; ?>
	
	<!-- Event Services -->
	<?php if ( ! empty( $services ) ) : ?>
	<div class="cts-single-services">
		<h2><?php esc_html_e( 'Dienste', 'churchtools-suite' ); ?></h2>
		<div class="cts-single-services-list">
			<?php foreach ( $services as $service ) : ?>
				<div class="cts-single-service-item">
					<svg class="cts-single-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4"/>
					</svg>
					<div>
						<strong><?php echo esc_html( $service->service_name ); ?></strong>
						<?php if ( ! empty( $service->person_name ) ) : ?>
							<span class="cts-service-person">: <?php echo esc_html( $service->person_name ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
	
	<!-- Actions -->
	<div class="cts-single-actions">
		<button class="cts-single-action-btn cts-btn-back" onclick="window.history.back();">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<line x1="19" y1="12" x2="5" y2="12"/>
				<polyline points="12 19 5 12 12 5"/>
			</svg>
			<?php esc_html_e( 'Zurück', 'churchtools-suite' ); ?>
		</button>
	</div>
	
</article>
