<?php
/**
 * Calendar Views Demo
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
		<span class="cts-card-icon">📅</span>
		<h3><?php esc_html_e( 'Calendar Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Monthly Modern -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Monthly Modern</h4>
				<code>[cts_calendar view="monthly-modern"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_calendar view="monthly-modern"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
