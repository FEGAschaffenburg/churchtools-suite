<?php
/**
 * Debug Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
?>

<div class="cts-debug">
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Plugin Version', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP Version', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( phpversion() ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( get_bloginfo( 'version' ) ); ?></code></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'DB Prefix', 'churchtools-suite' ); ?></td>
				<td><code><?php echo esc_html( $prefix ); ?></code></td>
			</tr>
		</table>
	</div>

	<div class="cts-card">
		<h3><?php esc_html_e( 'Datenbank', 'churchtools-suite' ); ?></h3>
		<table class="cts-debug-table">
			<tr>
				<td><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}calendars" ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></td>
				<td><?php echo esc_html( $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}event_services" ) ); ?></td>
			</tr>
		</table>
	</div>

</div>
