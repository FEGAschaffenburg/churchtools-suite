<?php
/**
 * List Views Demo
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
		<span class="cts-card-icon">📋</span>
		<h3><?php esc_html_e( 'List Views', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		
		<!-- Classic -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Classic List</h4>
				<code>[cts_list view="classic"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_list view="classic"]' ); ?>
			</div>
		</div>
		
		<!-- Classic mit Services -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Classic List - mit Services</h4>
				<code>[cts_list view="classic-services"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_list view="classic-services"]' ); ?>
			</div>
		</div>
		
		<!-- Medium -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Medium List - mit Datumbox und Beschreibung</h4>
				<code>[cts_list view="medium"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_list view="medium"]' ); ?>
			</div>
		</div>
		
		<?php /* DEAKTIVIERT - TODO: Fluent List später fertigstellen
		<!-- Fluent -->
		<div class="cts-demo-item">
			<div class="cts-demo-item-header">
				<h4>Fluent List</h4>
				<code>[cts_list view="fluent"]</code>
			</div>
			<div class="cts-demo-item-preview">
				<?php echo do_shortcode( '[cts_list view="fluent"]' ); ?>
			</div>
		</div>
		*/ ?>
		
	</div>
</div>
