<?php
/**
 * Template: Single Event - Modern
 * 
 * Moderne Einzeltermin-Ansicht mit Card-Design und Farbakzenten
 * Verwendung: [cts_event id="123" template="modern"]
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

<article class="cts-single-event cts-single-modern">
	
	<!-- Hero Header with Color -->
	<div class="cts-single-hero" style="background: linear-gradient(135deg, <?php echo esc_attr( $calendar_color ); ?>dd 0%, <?php echo esc_attr( $calendar_color ); ?>66 100%);">
		<?php if ( ! empty( $calendar ) ) : ?>
			<div class="cts-single-calendar-tag">
				<?php echo esc_html( $calendar->name ); ?>
			</div>
		<?php endif; ?>
		<h1 class="cts-single-title"><?php echo esc_html( $event->title ); ?></h1>
	</div>
	
	<!-- Content Cards -->
	<div class="cts-single-cards">
		
		<!-- Date & Time Card -->
		<div class="cts-single-card cts-card-datetime">
			<div class="cts-card-icon" style="background-color: <?php echo esc_attr( $calendar_color ); ?>22;">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $calendar_color ); ?>" stroke-width="2">
					<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
					<line x1="16" y1="2" x2="16" y2="6"/>
					<line x1="8" y1="2" x2="8" y2="6"/>
					<line x1="3" y1="10" x2="21" y2="10"/>
				</svg>
			</div>
			<div class="cts-card-content">
				<h3><?php esc_html_e( 'Wann', 'churchtools-suite' ); ?></h3>
				<?php
				$start = new DateTime( $event->start_datetime );
				$end = $event->end_datetime ? new DateTime( $event->end_datetime ) : null;
				?>
				<div class="cts-card-date">
					<?php echo esc_html( $start->format( 'l, d. F Y' ) ); ?>
				</div>
				<?php if ( ! $event->is_all_day ) : ?>
					<div class="cts-card-time">
						<?php 
						echo esc_html( $start->format( get_option( 'time_format' ) ) );
						if ( $end ) {
							echo ' - ' . esc_html( $end->format( get_option( 'time_format' ) ) );
						}
						?>
					</div>
				<?php else : ?>
					<div class="cts-card-all-day"><?php esc_html_e( 'Ganztägig', 'churchtools-suite' ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Location Card -->
		<?php if ( ! empty( $event->location_name ) ) : ?>
		<div class="cts-single-card cts-card-location">
			<div class="cts-card-icon" style="background-color: <?php echo esc_attr( $calendar_color ); ?>22;">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $calendar_color ); ?>" stroke-width="2">
					<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
					<circle cx="12" cy="10" r="3"/>
				</svg>
			</div>
			<div class="cts-card-content">
				<h3><?php esc_html_e( 'Wo', 'churchtools-suite' ); ?></h3>
				<div class="cts-card-location-name">
					<?php echo esc_html( $event->location_name ); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
	</div>
	
	<!-- Description Section -->
	<?php if ( ! empty( $event->description ) ) : ?>
	<div class="cts-single-section cts-section-description">
		<h2><?php esc_html_e( 'Details', 'churchtools-suite' ); ?></h2>
		<div class="cts-section-content">
			<?php echo wp_kses_post( wpautop( $event->description ) ); ?>
		</div>
	</div>
	<?php endif; ?>
	
	<!-- Services Section -->
	<?php if ( ! empty( $services ) ) : ?>
	<div class="cts-single-section cts-section-services">
		<h2><?php esc_html_e( 'Team', 'churchtools-suite' ); ?></h2>
		<div class="cts-services-grid">
			<?php foreach ( $services as $service ) : ?>
				<div class="cts-service-card">
					<div class="cts-service-icon" style="background-color: <?php echo esc_attr( $calendar_color ); ?>22;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $calendar_color ); ?>" stroke-width="2">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
							<circle cx="12" cy="7" r="4"/>
						</svg>
					</div>
					<div class="cts-service-info">
						<div class="cts-service-name"><?php echo esc_html( $service->service_name ); ?></div>
						<?php if ( ! empty( $service->person_name ) ) : ?>
							<div class="cts-service-person"><?php echo esc_html( $service->person_name ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
	
	<!-- Actions -->
	<div class="cts-single-actions">
		<button class="cts-btn-primary" onclick="window.history.back();">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<line x1="19" y1="12" x2="5" y2="12"/>
				<polyline points="12 19 5 12 12 5"/>
			</svg>
			<?php esc_html_e( 'Zurück zur Übersicht', 'churchtools-suite' ); ?>
		</button>
	</div>
	
</article>
