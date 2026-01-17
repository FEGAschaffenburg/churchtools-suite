<?php
/**
 * Admin Dashboard
 * 
 * Shows plugin status and Elementor integration info
 *
 * @package ChurchTools_Suite
 * @since   1.0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Admin_Dashboard {
	
	/**
	 * Initialize admin dashboard
	 * 
	 * @since 1.0.4.0
	 */
	public static function init() {
		add_action( 'admin_notices', [ __CLASS__, 'show_status_notice' ] );
	}
	
	/**
	 * Show status notice in admin
	 * 
	 * @since 1.0.4.0
	 */
	public static function show_status_notice() {
		// Only show on plugin pages
		if ( ! self::is_plugin_page() ) {
			return;
		}
		
		$elementor_active = is_plugin_active( 'elementor/elementor.php' );
		
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'ChurchTools Suite Status:', 'churchtools-suite' ); ?></strong>
			</p>
			<ul style="margin: 10px 0 10px 20px; list-style: disc;">
				<li>
					<?php
					if ( $elementor_active ) {
						echo '<span style="color: green;">✓ Elementor ist installiert und aktiv</span>';
						echo '<br><small>Das Elementor Widget ist verfügbar und sollte in der Widget-Liste sichtbar sein.</small>';
					} else {
						echo '<span style="color: orange;">⚠ Elementor ist nicht aktiv</span>';
						echo '<br><small>Das Elementor Widget wird nicht geladen. Bitte installieren und aktivieren Sie Elementor.</small>';
					}
					?>
				</li>
				<li>
					<?php
					$gutenberg_active = is_plugin_active( 'gutenberg/gutenberg.php' ) || version_compare( get_bloginfo( 'version' ), '5.0', '>=' );
					if ( $gutenberg_active ) {
						echo '<span style="color: green;">✓ Gutenberg-Blöcke sind verfügbar</span>';
					} else {
						echo '<span style="color: orange;">⚠ Gutenberg nicht verfügbar</span>';
					}
					?>
				</li>
			</ul>
		</div>
		<?php
	}
	
	/**
	 * Check if current page is a ChurchTools plugin page
	 * 
	 * @return bool
	 * @since 1.0.4.0
	 */
	private static function is_plugin_page(): bool {
		if ( ! is_admin() ) {
			return false;
		}
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		
		return strpos( $page, 'churchtools' ) !== false;
	}
}

// Initialize on admin_init
add_action( 'admin_init', [ 'ChurchTools_Suite_Admin_Dashboard', 'init' ] );
