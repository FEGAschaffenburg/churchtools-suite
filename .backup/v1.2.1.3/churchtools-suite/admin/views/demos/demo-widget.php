<?php
/**
 * Widget Views Demo
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
		<span class="cts-card-icon">📱</span>
		<h3><?php esc_html_e( 'Widget Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Upcoming Events Widget -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Upcoming Events Widget</h4>
				<code>[cts_widget view="upcoming-events" limit="5"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_widget view="upcoming-events" limit="5"]' ); ?>
			</div>
		</div>
		
		<!-- Calendar Widget -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Calendar Widget</h4>
				<code>[cts_widget view="calendar-widget"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_widget view="calendar-widget"]' ); ?>
			</div>
		</div>
		
		<!-- Countdown Widget -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown Widget</h4>
				<code>[cts_widget view="countdown-widget"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_widget view="countdown-widget"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
