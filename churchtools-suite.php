<?php
/**
 * Plugin Name:       ChurchTools Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/churchtools-suite
 * Description:       Professionelle ChurchTools-Integration für WordPress. Synchronisiert Events, Termine und Dienste aus ChurchTools mit 50+ Frontend-Views.
 * Version:           0.9.5.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            FEG Aschaffenburg
 * Author URI:        https://github.com/FEGAschaffenburg
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       churchtools-suite
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'CHURCHTOOLS_SUITE_VERSION', '0.9.5.0' );
define( 'CHURCHTOOLS_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_URL', plugin_dir_url( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_BASENAME', plugin_basename( __FILE__ ) );

// Database table prefix
define( 'CHURCHTOOLS_SUITE_DB_PREFIX', 'cts_' );

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
	$plugin->run();
}
run_churchtools_suite();
