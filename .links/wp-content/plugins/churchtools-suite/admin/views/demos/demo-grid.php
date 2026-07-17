<?php
/**
 * Grid Views Demo
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
		<span class="cts-card-icon">▦</span>
		<h3><?php esc_html_e( 'Grid Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Simple Grid -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Simple Grid</h4>
				<code>[cts_grid view="simple"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_grid view="simple"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
