<?php
/**
 * Template: Single Event - Minimal
 * 
 * Minimalistische Einzeltermin-Ansicht mit Fokus auf Lesbarkeit
 * Verwendung: [cts_event id="123" template="minimal"]
 *
 * @package ChurchTools_Suite
 * @since   0.7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Available variables: $event, $calendar, $services
?>

<article class="cts-single-event cts-single-minimal">
	
	<!-- Simple Header -->
	<header class="cts-single-header">
		<h1><?php echo esc_html( $event->title ); ?></h1>
		<?php if ( ! empty( $calendar ) ) : ?>
			<span class="cts-calendar-label"><?php echo esc_html( $calendar->name ); ?></span>
		<?php endif; ?>
	</header>
	
	<!-- Event Info -->
	<div class="cts-single-info">
		
		<?php
		$start = new DateTime( $event->start_datetime );
		$end = $event->end_datetime ? new DateTime( $event->end_datetime ) : null;
		?>
		
		<!-- Date & Time -->
		<div class="cts-info-row">
			<span class="cts-info-label"><?php esc_html_e( 'Datum:', 'churchtools-suite' ); ?></span>
			<span class="cts-info-value">
				<?php echo esc_html( $start->format( get_option( 'date_format' ) ) ); ?>
			</span>
		</div>
		
		<?php if ( ! $event->is_all_day ) : ?>
		<div class="cts-info-row">
			<span class="cts-info-label"><?php esc_html_e( 'Uhrzeit:', 'churchtools-suite' ); ?></span>
			<span class="cts-info-value">
				<?php 
				echo esc_html( $start->format( get_option( 'time_format' ) ) );
				if ( $end ) {
					echo ' - ' . esc_html( $end->format( get_option( 'time_format' ) ) );
				}
				?>
			</span>
		</div>
		<?php endif; ?>
		
		<!-- Location -->
		<?php if ( ! empty( $event->location_name ) ) : ?>
		<div class="cts-info-row">
			<span class="cts-info-label"><?php esc_html_e( 'Ort:', 'churchtools-suite' ); ?></span>
			<span class="cts-info-value"><?php echo esc_html( $event->location_name ); ?></span>
		</div>
		<?php endif; ?>
		
	</div>
	
	<!-- Description -->
	<?php if ( ! empty( $event->description ) ) : ?>
	<div class="cts-single-description">
		<?php echo wp_kses_post( wpautop( $event->description ) ); ?>
	</div>
	<?php endif; ?>
	
	<!-- Services -->
	<?php if ( ! empty( $services ) ) : ?>
	<div class="cts-single-services">
		<h3><?php esc_html_e( 'Dienste', 'churchtools-suite' ); ?></h3>
		<ul class="cts-services-list">
			<?php foreach ( $services as $service ) : ?>
				<li>
					<strong><?php echo esc_html( $service->service_name ); ?></strong>
					<?php if ( ! empty( $service->person_name ) ) : ?>
						– <?php echo esc_html( $service->person_name ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
	
</article>
