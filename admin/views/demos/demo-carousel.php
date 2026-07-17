<?php
/**
 * Carousel Views Demo
 *
 * @package ChurchTools_Suite
 * @since   1.1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="cts-card">
	<div class="cts-card-header">
		<span class="cts-card-icon">🎠</span>
		<h3><?php esc_html_e( 'Carousel Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Carousel Klassisch (Standard) -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel Klassisch - 3 Slides pro Ansicht</h4>
				<code>[cts_carousel view="carousel-klassisch" slides_per_view="3"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="carousel-klassisch" slides_per_view="3"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel mit Autoplay -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel - mit Autoplay (5 Sekunden)</h4>
				<code>[cts_carousel view="carousel-klassisch" autoplay="true" autoplay_delay="5000"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="carousel-klassisch" autoplay="true" autoplay_delay="5000"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel mit 1 Slide pro Ansicht -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel - 1 Slide (Fullwidth)</h4>
				<code>[cts_carousel view="carousel-klassisch" slides_per_view="1"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="carousel-klassisch" slides_per_view="1"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel mit mehr Slides -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel - 4 Slides pro Ansicht</h4>
				<code>[cts_carousel view="carousel-klassisch" slides_per_view="4" limit="16"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="carousel-klassisch" slides_per_view="4" limit="16"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel ohne Loop -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel - ohne Loop (endet am letzten Slide)</h4>
				<code>[cts_carousel view="carousel-klassisch" loop="false"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="carousel-klassisch" loop="false"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
