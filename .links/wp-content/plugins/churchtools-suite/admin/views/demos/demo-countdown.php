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
		
		<!-- Countdown Klassisch (Split-Layout) -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown Klassisch - Split-Layout mit Hero-Image</h4>
				<code>[cts_countdown view="countdown-klassisch"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="countdown-klassisch"]' ); ?>
			</div>
		</div>
		
		<!-- Countdown mit custom Beschreibung -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown - ohne Event-Beschreibung</h4>
				<code>[cts_countdown view="countdown-klassisch" show_event_description="false"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="countdown-klassisch" show_event_description="false"]' ); ?>
			</div>
		</div>
		
		<!-- Countdown mit spezifischem Kalender -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Countdown - nur Gottesdienste</h4>
				<code>[cts_countdown view="countdown-klassisch" calendar="1"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_countdown view="countdown-klassisch" calendar="1"]' ); ?>
			</div>
		</div>
		
	</div>
</div>
