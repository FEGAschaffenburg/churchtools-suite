<?php
/**
 * Slider Views Demo
 *
 * @package ChurchTools_Suite
 * @since   0.5.9.25
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="cts-card">
	<div class="cts-card-header">
		<span class="cts-card-icon">🎞️</span>
		<h3><?php esc_html_e( 'Slider Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Slider Type 1 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Slider Type 1</h4>
				<code>[cts_slider view="type-1" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_slider view="type-1" limit="5"]' ); ?>
			</div>
		</div>
		
		<!-- Slider Type 2 mit Autoplay -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Slider Type 2 - Autoplay</h4>
				<code>[cts_slider view="type-2" autoplay="true" interval="5000" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_slider view="type-2" autoplay="true" interval="5000" limit="5"]' ); ?>
			</div>
		</div>
		
		<!-- Slider Type 3 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Slider Type 3</h4>
				<code>[cts_slider view="type-3" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_slider view="type-3" limit="5"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
