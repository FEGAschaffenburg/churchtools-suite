<?php
/**
 * Carousel Views Demo
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
		<span class="cts-card-icon">🎠</span>
		<h3><?php esc_html_e( 'Carousel Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Carousel Type 1 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel Type 1</h4>
				<code>[cts_carousel view="type-1" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="type-1" limit="5"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel Type 2 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel Type 2</h4>
				<code>[cts_carousel view="type-2" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="type-2" limit="5"]' ); ?>
			</div>
		</div>
		
		<!-- Carousel Type 3 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Carousel Type 3</h4>
				<code>[cts_carousel view="type-3" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_carousel view="type-3" limit="5"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
