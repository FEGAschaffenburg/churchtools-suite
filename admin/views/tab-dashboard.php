<?php
/**
 * Dashboard Tab Template
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
		<span class="dashicons dashicons-dashboard"></span>
		<h2><?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?></h2>
	</div>
	<div class="churchtools-suite-card-body">
		<p><?php esc_html_e( 'Willkommen bei ChurchTools Suite!', 'churchtools-suite' ); ?></p>
		<p><?php printf( esc_html__( 'Version: %s', 'churchtools-suite' ), CHURCHTOOLS_SUITE_VERSION ); ?></p>
	</div>
</div>

<div class="churchtools-suite-card">
	<div class="churchtools-suite-card-header">
		<span class="dashicons dashicons-info"></span>
		<h2><?php esc_html_e( 'Quick Start', 'churchtools-suite' ); ?></h2>
	</div>
	<div class="churchtools-suite-card-body">
		<ol>
			<li><?php esc_html_e( 'Gehen Sie zu Einstellungen und konfigurieren Sie Ihre ChurchTools-Zugangsdaten', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Wählen Sie die gewünschten Kalender aus', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Starten Sie die erste Synchronisation', 'churchtools-suite' ); ?></li>
		</ol>
	</div>
</div>
