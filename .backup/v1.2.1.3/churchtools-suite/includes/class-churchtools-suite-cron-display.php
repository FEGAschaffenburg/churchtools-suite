<?php
/**
 * Cron Job Display Names Helper
 *
 * WordPress zeigt Cron-Job-Namen technisch an. Diese Klasse bietet
 * benutzerfreundliche Anzeigenamen für unsere Cron-Jobs.
 *
 * @package ChurchTools_Suite
 * @since   0.10.1.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Cron_Display {
	
	/**
	 * Get human-readable display name for a cron hook
	 *
	 * @param string $hook Cron hook name
	 * @return string Display name
	 */
	public static function get_cron_display_name( string $hook ): string {
		$names = [
			'churchtools_suite_auto_sync' => __( 'Event-Synchronisation', 'churchtools-suite' ),
			'churchtools_suite_session_keepalive' => __( 'Session aufrechterhalten', 'churchtools-suite' ),
			'churchtools_suite_check_updates' => __( 'Update-Prüfung', 'churchtools-suite' ),
		];
		
		return $names[ $hook ] ?? $hook;
	}
	
	/**
	 * Get human-readable description for a cron hook
	 *
	 * @param string $hook Cron hook name
	 * @return string Description
	 */
	public static function get_cron_description( string $hook ): string {
		$descriptions = [
			'churchtools_suite_auto_sync' => __( 'Synchronisiert Events automatisch gemäß Zeitplan.', 'churchtools-suite' ),
			'churchtools_suite_session_keepalive' => __( 'Verlängert die ChurchTools-Session.', 'churchtools-suite' ),
			'churchtools_suite_check_updates' => __( 'Prüft auf neue Plugin-Versionen und installiert Updates automatisch.', 'churchtools-suite' ),
		];
		
		return $descriptions[ $hook ] ?? '';
	}
	
	/**
	 * Filter cron event display (for WP-CLI and debug screens)
	 *
	 * @param string $hook Hook name
	 * @return string Formatted display text
	 */
	public static function format_cron_event( string $hook ): string {
		$name = self::get_cron_display_name( $hook );
		$desc = self::get_cron_description( $hook );
		
		if ( $name === $hook ) {
			// Nicht unserer, original zurückgeben
			return $hook;
		}
		
		return sprintf(
			'<strong>%s</strong><br><span style="color:#666; font-size:0.9em;">%s</span><br><code style="font-size:0.85em; color:#999;">%s</code>',
			esc_html( $name ),
			esc_html( $desc ),
			esc_html( $hook )
		);
	}
}
