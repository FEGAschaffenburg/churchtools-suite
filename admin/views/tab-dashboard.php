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
$is_configured = ! empty( $ct_url ) && ! empty( $ct_username ) && ! empty( $ct_password );

// Statistiken
global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" );
$calendars_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}calendars WHERE is_selected = 1" );
?>

<div class="cts-dashboard">
	
	<!-- Status Cards -->
	<div class="cts-grid cts-grid-3">
		
		<!-- ChurchTools Verbindung -->
		<div class="cts-card">
			<div class="cts-card-icon">
				<span class="dashicons dashicons-cloud"></span>
			</div>
			<div class="cts-card-content">
				<h3><?php esc_html_e( 'ChurchTools', 'churchtools-suite' ); ?></h3>
				<?php if ( $is_configured ) : ?>
					<p class="cts-status cts-status-success">
						<span class="cts-status-dot"></span>
						<?php esc_html_e( 'Verbunden', 'churchtools-suite' ); ?>
					</p>
				<?php else : ?>
					<p class="cts-status cts-status-error">
						<span class="cts-status-dot"></span>
						<?php esc_html_e( 'Nicht konfiguriert', 'churchtools-suite' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Events -->
		<div class="cts-card">
			<div class="cts-card-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="cts-card-content">
				<h3><?php esc_html_e( 'Events', 'churchtools-suite' ); ?></h3>
				<p class="cts-stat-number"><?php echo esc_html( $events_count ); ?></p>
			</div>
		</div>

		<!-- Kalender -->
		<div class="cts-card">
			<div class="cts-card-icon">
				<span class="dashicons dashicons-list-view"></span>
			</div>
			<div class="cts-card-content">
				<h3><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></h3>
				<p class="cts-stat-number"><?php echo esc_html( $calendars_count ); ?></p>
			</div>
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
