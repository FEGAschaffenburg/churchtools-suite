<?php
/**
 * Map Views Demo
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
		<span class="cts-card-icon">🗺️</span>
		<h3><?php esc_html_e( 'Map Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Map Standard -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Map Standard</h4>
				<code>[cts_map view="standard" limit="10"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_map view="standard" limit="10"]' ); ?>
			</div>
		</div>
		
		<!-- Map Advanced -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Map Advanced</h4>
				<code>[cts_map view="advanced" limit="10"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_map view="advanced" limit="10"]' ); ?>
			</div>
		</div>
		
		<!-- Map Liquid -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Map Liquid</h4>
				<code>[cts_map view="liquid" limit="10"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_map view="liquid" limit="10"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
