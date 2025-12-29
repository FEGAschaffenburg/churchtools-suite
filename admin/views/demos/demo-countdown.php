<?php
/**
 * Countdown Views Demo
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
		<span class="cts-card-icon">⏱️</span>
		<h3><?php esc_html_e( 'Countdown Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Countdown Type 1 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown Type 1</h4>
				<code>[cts_countdown view="type-1"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="type-1"]' ); ?>
			</div>
		</div>
		
		<!-- Countdown Type 2 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown Type 2</h4>
				<code>[cts_countdown view="type-2"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="type-2"]' ); ?>
			</div>
		</div>
		
		<!-- Countdown Type 3 -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown Type 3</h4>
				<code>[cts_countdown view="type-3"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="type-3"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
