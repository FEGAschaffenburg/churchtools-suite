<?php
/**
 * Debug Tab Template
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="churchtools-suite-card">
	<div class="churchtools-suite-card-header">
		<span class="dashicons dashicons-admin-tools"></span>
		<h2><?php esc_html_e( 'Debug', 'churchtools-suite' ); ?></h2>
	</div>
	<div class="churchtools-suite-card-body">
		<h3><?php esc_html_e( 'System-Information', 'churchtools-suite' ); ?></h3>
		<table class="widefat">
			<tr>
				<td><strong>Plugin Version:</strong></td>
				<td><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></td>
			</tr>
			<tr>
				<td><strong>PHP Version:</strong></td>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
			<tr>
				<td><strong>WordPress Version:</strong></td>
				<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
			</tr>
			<tr>
				<td><strong>DB Prefix:</strong></td>
				<td><?php echo esc_html( $GLOBALS['wpdb']->prefix . CHURCHTOOLS_SUITE_DB_PREFIX ); ?></td>
			</tr>
		</table>
	</div>
</div>
