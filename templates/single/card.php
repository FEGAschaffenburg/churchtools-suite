<?php
/**
 * Template: Single Event - Card
 * 
 * Card-basierte Einzeltermin-Ansicht mit visuellen Elementen
 * Verwendung: [cts_event id="123" template="card"]
 *
 * @package ChurchTools_Suite
 * @since   0.7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Available variables: $event, $calendar, $services
$calendar_color = ! empty( $calendar->color ) ? $calendar->color : '#0073aa';
?>

<article class="cts-single-event cts-single-card">
	
	<div class="cts-event-card-container">
		
		<!-- Color Accent Bar -->
		<div class="cts-card-accent" style="background-color: <?php echo esc_attr( $calendar_color ); ?>;"></div>
		
		<!-- Card Header -->
		<div class="cts-card-header">
			<div class="cts-card-date-badge">
				<?php
				$start = new DateTime( $event->start_datetime );
				?>
				<div class="cts-badge-day"><?php echo esc_html( $start->format( 'd' ) ); ?></div>
				<div class="cts-badge-month"><?php echo esc_html( $start->format( 'M' ) ); ?></div>
			</div>
			<div class="cts-card-title-group">
				<h1 class="cts-card-title"><?php echo esc_html( $event->title ); ?></h1>
				<?php if ( ! empty( $calendar ) ) : ?>
					<div class="cts-card-calendar"><?php echo esc_html( $calendar->name ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Card Body -->
		<div class="cts-card-body">
			
			<!-- Info Grid -->
			<div class="cts-card-info-grid">
				
				<!-- Time -->
				<div class="cts-card-info-item">
					<div class="cts-info-icon" style="color: <?php echo esc_attr( $calendar_color ); ?>;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"/>
							<polyline points="12 6 12 12 16 14"/>
						</svg>
					</div>
					<div class="cts-info-content">
						<div class="cts-info-label"><?php esc_html_e( 'Uhrzeit', 'churchtools-suite' ); ?></div>
						<div class="cts-info-value">
							<?php
							if ( $event->is_all_day ) {
								esc_html_e( 'Ganztägig', 'churchtools-suite' );
							} else {
								echo esc_html( $start->format( get_option( 'time_format' ) ) );
								if ( $event->end_datetime ) {
									$end = new DateTime( $event->end_datetime );
									echo ' - ' . esc_html( $end->format( get_option( 'time_format' ) ) );
								}
							}
							?>
						</div>
					</div>
				</div>
				
				<!-- Location -->
				<?php if ( ! empty( $event->location_name ) ) : ?>
				<div class="cts-card-info-item">
					<div class="cts-info-icon" style="color: <?php echo esc_attr( $calendar_color ); ?>;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
							<circle cx="12" cy="10" r="3"/>
						</svg>
					</div>
					<div class="cts-info-content">
						<div class="cts-info-label"><?php esc_html_e( 'Ort', 'churchtools-suite' ); ?></div>
						<div class="cts-info-value"><?php echo esc_html( $event->location_name ); ?></div>
					</div>
				</div>
				<?php endif; ?>
				
			</div>
			
			<!-- Description -->
			<?php if ( ! empty( $event->description ) ) : ?>
			<div class="cts-card-description">
				<h3><?php esc_html_e( 'Details', 'churchtools-suite' ); ?></h3>
				<?php echo wp_kses_post( wpautop( $event->description ) ); ?>
			</div>
			<?php endif; ?>
			
			<!-- Services -->
			<?php if ( ! empty( $services ) ) : ?>
			<div class="cts-card-services">
				<h3><?php esc_html_e( 'Team', 'churchtools-suite' ); ?></h3>
				<div class="cts-services-tags">
					<?php foreach ( $services as $service ) : ?>
						<div class="cts-service-tag" style="border-color: <?php echo esc_attr( $calendar_color ); ?>;">
							<strong><?php echo esc_html( $service->service_name ); ?></strong>
							<?php if ( ! empty( $service->person_name ) ) : ?>
								<span><?php echo esc_html( $service->person_name ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
			
		</div>
		
		<!-- Card Footer -->
		<div class="cts-card-footer">
			<button class="cts-btn-back" onclick="window.history.back();">
				<?php esc_html_e( 'Zurück', 'churchtools-suite' ); ?>
			</button>
		</div>
		
	</div>
	
</article>
