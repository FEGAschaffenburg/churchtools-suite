<?php
/**
 * Plugin Name:       ChurchTools Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/churchtools-suite
 * Description:       Professionelle ChurchTools-Integration f�r WordPress. Synchronisiert Events, Termine und Dienste aus ChurchTools. ? Repository Factory f�r erweiterbare Architektur (Multi-User, Caching, Add-Ons).
 * Version: 1.2.1.4
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            FEG Aschaffenburg
 * Author URI:        https://github.com/FEGAschaffenburg
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       churchtools-suite
 * Domain Path:       /languages
 *
 * TRADEMARK NOTICE:
 * ChurchTools ist eine registrierte Marke der ChurchTools GmbH.
 * Dieses Projekt steht in keiner Verbindung zu oder Unterst�tzung durch die ChurchTools GmbH.
 * ChurchTools Suite wird ohne Gew�hrleistung bereitgestellt (see LICENSE).
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Suppress WP 6.7 JIT translation notice IMMEDIATELY (v1.0.3.5)
remove_filter( 'load_textdomain_mofile', 'wp_check_load_textdomain_just_in_time' );

// Plugin constants
define( 'CHURCHTOOLS_SUITE_VERSION', '1.2.1.2' );
define( 'CHURCHTOOLS_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_URL', plugin_dir_url( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_BASENAME', plugin_basename( __FILE__ ) );

// Database table prefix
define( 'CHURCHTOOLS_SUITE_DB_PREFIX', 'cts_' );

// Load repository factory (v1.0.8.0)
require_once CHURCHTOOLS_SUITE_PATH . 'includes/functions/repository-factory.php';

/**
 * Plugin activation
 */
function activate_churchtools_suite() {
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-activator.php';
	ChurchTools_Suite_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_churchtools_suite' );

/**
 * Plugin deactivation
 */
function deactivate_churchtools_suite() {
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-deactivator.php';
	ChurchTools_Suite_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_churchtools_suite' );

/**
 * Initialize the plugin
 */
function run_churchtools_suite() {
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite.php';
	$plugin = new ChurchTools_Suite();
	
	// Store instance globally for sub-plugins (v1.0.9.0)
	global $churchtools_suite_plugin_instance;
	$churchtools_suite_plugin_instance = $plugin;
	
	$plugin->run();
}
run_churchtools_suite();










