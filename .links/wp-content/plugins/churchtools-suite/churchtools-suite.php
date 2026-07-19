<?php
/**
 * Plugin Name:       ChurchTools Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/churchtools-suite
 * Description:       Professionelle ChurchTools-Integration f�r WordPress. Synchronisiert Events, Termine und Dienste aus ChurchTools. ? Repository Factory f�r erweiterbare Architektur (Multi-User, Caching, Add-Ons).
 * Version: 1.2.3.0
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

// Plugin constants
define( 'CHURCHTOOLS_SUITE_VERSION', '1.2.3.0' );
define( 'CHURCHTOOLS_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_URL', plugin_dir_url( __FILE__ ) );
define( 'CHURCHTOOLS_SUITE_BASENAME', plugin_basename( __FILE__ ) );

// Database table prefix
define( 'CHURCHTOOLS_SUITE_DB_PREFIX', 'cts_' );

/**
 * GitHub Update Checker
 * Checkt automatisch auf neue Versionen auf GitHub
 */
function churchtools_suite_check_for_updates() {
	if ( defined( 'WP_INSTALLING' ) || wp_installing() ) {
		return;
	}

	$repo_owner = 'FEGAschaffenburg';
	$repo_name  = 'churchtools-suite';
	$plugin_slug = 'churchtools-suite';
	$api_url    = "https://api.github.com/repos/{$repo_owner}/{$repo_name}/releases/latest";
	$cache_key  = "churchtools_suite_github_update_{$repo_name}";

	// Hole gecachte Release-Info (4 Stunden Cache)
	$release = get_transient( $cache_key );
	
	if ( false === $release ) {
		// Fetch von GitHub API
		$response = wp_remote_get( $api_url, array(
			'timeout'    => 10,
			'user-agent' => 'ChurchTools-Suite-WordPress-Plugin',
		) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! $release || empty( $release->tag_name ) ) {
			return;
		}

		set_transient( $cache_key, $release, 4 * HOUR_IN_SECONDS );
	}

	if ( empty( $release->tag_name ) ) {
		return;
	}

	// Entferne 'v' Prefix
	$remote_version = ltrim( $release->tag_name, 'v' );
	$local_version  = CHURCHTOOLS_SUITE_VERSION;

	// Vergleiche Versionen
	if ( version_compare( $local_version, $remote_version, '<' ) ) {
		add_filter( 'transient_update_plugins', function( $transient ) use ( $release, $plugin_slug, $repo_owner, $repo_name, $remote_version ) {
			if ( empty( $transient->response ) ) {
				$transient->response = array();
			}

			$plugin_file = CHURCHTOOLS_SUITE_BASENAME;

			$transient->response[ $plugin_file ] = (object) array(
				'id'       => "{$repo_owner}/{$repo_name}",
				'slug'     => $plugin_slug,
				'plugin'   => $plugin_file,
				'new_version' => $remote_version,
				'url'      => $release->html_url,
				'package'  => ! empty( $release->assets[0]->browser_download_url ) ? $release->assets[0]->browser_download_url : $release->zipball_url,
				'tested'   => '6.4',
				'requires' => '6.0',
			);

			return $transient;
		} );
	}
}
add_action( 'init', 'churchtools_suite_check_for_updates' );

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
add_action( 'init', 'run_churchtools_suite', 1 );










