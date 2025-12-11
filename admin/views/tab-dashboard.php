<?php
/**
 * Dashboard Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Status prüfen
$ct_url = get_option( 'churchtools_suite_ct_url', '' );
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
$ct_cookies = get_option( 'churchtools_suite_ct_cookies', [] );
$ct_last_login = get_option( 'churchtools_suite_ct_last_login', '' );
$is_configured = ! empty( $ct_url ) && ! empty( $ct_username ) && ! empty( $ct_password );
$is_connected = ! empty( $ct_cookies );

// Statistiken
global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" );
$calendars_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}calendars WHERE is_selected = 1" );
?>

<div class="cts-dashboard">
	
	<!-- Dashboard Header -->
	<div class="cts-section-header">
		<h2><?php esc_html_e( 'Dashboard', 'churchtools-suite' ); ?></h2>
		<p class="cts-section-description"><?php esc_html_e( 'Übersicht über den aktuellen Status der ChurchTools-Integration.', 'churchtools-suite' ); ?></p>
	</div>
	
	<!-- Status Cards -->
	<div class="cts-grid cts-grid-3">
		
		<!-- ChurchTools Verbindung -->
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">☁️</span>
				<h3><?php esc_html_e( 'ChurchTools', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php if ( $is_connected ) : ?>
					<p class="cts-status cts-status-success">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Verbunden', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php echo esc_html( parse_url( $ct_url, PHP_URL_HOST ) ?: $ct_url ); ?></p>
					<?php if ( $ct_last_login ) : ?>
						<p class="cts-card-meta"><?php echo esc_html( sprintf( __( 'Letzter Login: %s', 'churchtools-suite' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $ct_last_login ) ) ) ); ?></p>
					<?php endif; ?>
				<?php elseif ( $is_configured ) : ?>
					<p class="cts-status cts-status-inactive">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Konfiguriert', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php echo esc_html( parse_url( $ct_url, PHP_URL_HOST ) ?: $ct_url ); ?></p>
					<p class="cts-card-meta"><?php esc_html_e( 'Verbindung noch nicht getestet', 'churchtools-suite' ); ?></p>
				<?php else : ?>
					<p class="cts-status cts-status-error">
						<span class="cts-status-indicator"></span>
						<?php esc_html_e( 'Nicht konfiguriert', 'churchtools-suite' ); ?>
					</p>
					<p class="cts-card-detail"><?php esc_html_e( 'Bitte ChurchTools-Zugangsdaten eingeben', 'churchtools-suite' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="cts-card-footer">
				<a href="?page=churchtools-suite&tab=settings" class="cts-button cts-button-secondary">
					<?php esc_html_e( 'Einstellungen', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

		<!-- Automatischer Sync -->
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">🔄</span>
				<h3><?php esc_html_e( 'Automatischer Sync', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<p class="cts-status cts-status-inactive">
					<span class="cts-status-indicator"></span>
					<?php esc_html_e( 'Deaktiviert', 'churchtools-suite' ); ?>
				</p>
				<p class="cts-card-detail"><?php esc_html_e( 'Automatische Synchronisation ist ausgeschaltet', 'churchtools-suite' ); ?></p>
			</div>
			<div class="cts-card-footer">
				<a href="?page=churchtools-suite&tab=sync" class="cts-button cts-button-secondary">
					<?php esc_html_e( 'Konfigurieren', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

		<!-- Synchronisation -->
		<div class="cts-card">
			<div class="cts-card-header">
				<span class="cts-card-icon">📅</span>
				<h3><?php esc_html_e( 'Synchronisation', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<p class="cts-stat-number"><?php echo esc_html( $events_count ); ?></p>
				<p class="cts-card-detail">
					<?php
					printf(
						esc_html__( 'Termine gesamt, %s Kalender ausgewählt', 'churchtools-suite' ),
						esc_html( $calendars_count )
					);
					?>
				</p>
			</div>
			<div class="cts-card-footer">
				<a href="?page=churchtools-suite&tab=sync" class="cts-button cts-button-secondary">
					<?php esc_html_e( 'Termine anzeigen', 'churchtools-suite' ); ?>
				</a>
			</div>
		</div>

	</div>

	<!-- System Info -->
	<div class="cts-card cts-system-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">ℹ️</span>
			<h3><?php esc_html_e( 'System', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
			<table class="cts-system-table">
				<tr>
					<td><?php esc_html_e( 'Plugin-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'WordPress-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( get_bloginfo( 'version' ) ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP-Version', 'churchtools-suite' ); ?></td>
					<td><strong><?php echo esc_html( PHP_VERSION ); ?></strong></td>
				</tr>
			</table>
		</div>
		<div class="cts-card-footer">
			<a href="?page=churchtools-suite&tab=debug" class="cts-button cts-button-secondary">
				<?php esc_html_e( 'Update-Info', 'churchtools-suite' ); ?>
			</a>
		</div>
	</div>

	<!-- Quick Start -->
	<?php if ( ! $is_configured ) : ?>
	<div class="cts-card cts-quick-start">
		<h3><?php esc_html_e( 'Quick Start', 'churchtools-suite' ); ?></h3>
		<ol>
			<li><?php printf( esc_html__( 'ChurchTools-URL und API-Token in den %sEinstellungen%s hinterlegen', 'churchtools-suite' ), '<a href="?page=churchtools-suite&tab=settings">', '</a>' ); ?></li>
			<li><?php esc_html_e( 'Kalender auswählen und synchronisieren', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Events per Shortcode im Frontend anzeigen', 'churchtools-suite' ); ?></li>
		</ol>
	</div>
	<?php endif; ?>

</div>
