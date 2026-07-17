<?php
/**
 * Plugin Name:       ChurchTools Suite - Elementor Integration
 * Plugin URI:        https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor
 * Description:       Elementor Page Builder Widget für ChurchTools Suite Events. Zeigt Events in Listen-, Raster- oder Kalender-Ansicht mit 28+ Anpassungsoptionen.
 * Version: 0.6.28
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  churchtools-suite, elementor
 * Author:            FEG Aschaffenburg
 * Author URI:        https://www.feg-aschaffenburg.de
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       churchtools-suite-elementor
 * Domain Path:       /languages
 * 
 * ChurchTools Suite - Elementor Integration is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * 
 * ChurchTools Suite - Elementor Integration is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ChurchTools Suite - Elementor Integration. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'CTS_ELEMENTOR_VERSION', '0.6.22' ); // Updated version for cache busting
define( 'CTS_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTS_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'CTS_ELEMENTOR_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Initialize Auto-Updater (before dependencies)
 * Runs very early so updates can be detected on Plugins page
 */
require_once CTS_ELEMENTOR_PATH . 'includes/class-cts-elementor-auto-updater.php';
CTS_Elementor_Auto_Updater::init();

/**
 * Check dependencies and initialize
 * 
 * This sub-plugin requires:
 * 1. ChurchTools Suite >= v1.0.9.0 (for churchtools_suite_loaded hook)
 * 2. Elementor >= v3.0.0 (for Widget_Base class)
 */
add_action( 'plugins_loaded', function() {
	
	// 1. Check if ChurchTools Suite is active
	if ( ! class_exists( 'ChurchTools_Suite' ) ) {
		add_action( 'admin_notices', 'cts_elementor_missing_main_plugin_notice' );
		return;
	}
	
	// 2. Check if Repository Factory is available (Main Plugin >= v1.0.9.0)
	if ( ! function_exists( 'churchtools_suite_get_repository' ) ) {
		add_action( 'admin_notices', 'cts_elementor_outdated_main_plugin_notice' );
		return;
	}
	
	// 3. Check if Elementor is active (check class existence, not hook timing)
	if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\\Elementor\\Plugin' ) ) {
		add_action( 'admin_notices', 'cts_elementor_missing_elementor_notice' );
		return;
	}
	
	// 4. Load integration class early for editor scripts
	require_once CTS_ELEMENTOR_PATH . 'includes/class-cts-elementor-integration.php';
	
	// 5. Register editor scripts hook IMMEDIATELY (before Elementor Editor loads)
	add_action( 'elementor/editor/before_enqueue_scripts', [ 'CTS_Elementor_Integration', 'enqueue_editor_scripts' ] );
	
	// 6. Initialize widget registration via normal hook
	// If churchtools_suite_loaded already fired, initialize now. Otherwise, hook in.
	if ( did_action( 'churchtools_suite_loaded' ) ) {
		// Hook already fired - initialize immediately
		global $churchtools_suite_plugin_instance;
		if ( isset( $churchtools_suite_plugin_instance ) && is_object( $churchtools_suite_plugin_instance ) ) {
			cts_elementor_init( $churchtools_suite_plugin_instance );
		} else {
			// No instance available, try initializing anyway
			cts_elementor_init( null );
		}
	} else {
		// Hook not fired yet - register callback
		add_action( 'churchtools_suite_loaded', 'cts_elementor_init', 10, 1 );
	}
	
}, 20 ); // Priority 20 to ensure Elementor and Main Plugin are loaded first

/**
 * Initialize Elementor Integration
 * 
 * Called via churchtools_suite_loaded hook after all dependencies are loaded
 * 
 * @param ChurchTools_Suite|null $plugin Main plugin instance (optional)
 * @since 0.5.0
 */
function cts_elementor_init( $plugin = null ) {
	// Integration class already loaded in plugins_loaded
	
	// Initialize widget registration (categories + widgets)
	CTS_Elementor_Integration::init();
	
	// Log initialization (if logger available)
	if ( function_exists( 'error_log' ) ) {
		$version = $plugin ? $plugin->get_version() : 'unknown';
		error_log( sprintf(
			'[CTS Elementor] Sub-Plugin v%s initialized with ChurchTools Suite v%s',
			CTS_ELEMENTOR_VERSION,
			$version
		) );
	}
}

/**
 * Admin Notice: ChurchTools Suite not found
 * 
 * @since 0.5.0
 */
function cts_elementor_missing_main_plugin_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>ChurchTools Suite - Elementor Integration</strong> erfordert das 
			<strong>ChurchTools Suite Plugin</strong> (Version 1.0.9.0 oder höher).
		</p>
		<p>
			<a href="https://github.com/FEGAschaffenburg/churchtools-suite/releases" target="_blank">
				ChurchTools Suite herunterladen
			</a>
		</p>
	</div>
	<?php
}

/**
 * Admin Notice: ChurchTools Suite version too old
 * 
 * @since 0.5.0
 */
function cts_elementor_outdated_main_plugin_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>ChurchTools Suite - Elementor Integration</strong> erfordert 
			<strong>ChurchTools Suite v1.0.9.0 oder höher</strong>.
		</p>
		<p>
			Ihre Version unterstützt den <code>churchtools_suite_loaded</code> Hook noch nicht.
			Bitte aktualisieren Sie ChurchTools Suite.
		</p>
		<p>
			<a href="<?php echo admin_url( 'plugins.php' ); ?>">Zu den Plugins</a>
		</p>
	</div>
	<?php
}

/**
 * Admin Notice: Elementor not found
 * 
 * @since 0.5.0
 */
function cts_elementor_missing_elementor_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong>ChurchTools Suite - Elementor Integration</strong> erfordert das 
			<strong>Elementor Plugin</strong> (Version 3.0.0 oder höher).
		</p>
		<p>
			<a href="<?php echo admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ); ?>">
				Elementor installieren
			</a>
		</p>
	</div>
	<?php
}
