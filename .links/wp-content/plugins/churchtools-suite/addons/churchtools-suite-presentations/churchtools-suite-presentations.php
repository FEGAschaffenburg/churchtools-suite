<?php
/**
 * Plugin Name: ChurchTools Suite - Presentations
 * Plugin URI: https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-presentations
 * Description: Erstellt lokale Präsentations-Seiten mit Slider auf Basis der vorhandenen ChurchTools Suite Views.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: churchtools-suite
 * Author: FEG Aschaffenburg
 * Author URI: https://www.feg-aschaffenburg.de
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: churchtools-suite-presentations
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CTS_PRESENTATIONS_VERSION', '0.1.0' );
define( 'CTS_PRESENTATIONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTS_PRESENTATIONS_URL', plugin_dir_url( __FILE__ ) );
define( 'CTS_PRESENTATIONS_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'ChurchTools_Suite' ) ) {
		add_action( 'admin_notices', function () {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p><strong>ChurchTools Suite - Presentations</strong> benötigt das aktive Plugin <strong>ChurchTools Suite</strong>.</p></div>';
		} );
		return;
	}

	if ( ! function_exists( 'churchtools_suite_get_repository' ) ) {
		add_action( 'admin_notices', function () {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p><strong>ChurchTools Suite - Presentations</strong> benötigt eine aktuelle ChurchTools Suite Version.</p></div>';
		} );
		return;
	}

	require_once CTS_PRESENTATIONS_PATH . 'includes/class-cts-presentations.php';
	require_once CTS_PRESENTATIONS_PATH . 'includes/class-cts-presentations-admin.php';
	require_once CTS_PRESENTATIONS_PATH . 'includes/class-cts-presentations-renderer.php';

	CTS_Presentations::init();
}, 20 );
