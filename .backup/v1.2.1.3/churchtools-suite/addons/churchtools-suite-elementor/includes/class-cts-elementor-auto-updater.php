<?php
/**
 * Elementor Integration Auto Updater
 *
 * Checks GitHub releases for new versions and provides update info to WordPress.
 *
 * @package ChurchTools_Suite_Elementor
 * @since   0.6.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Elementor_Auto_Updater {

	const GITHUB_API_RELEASES_LATEST = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';
	const PLUGIN_SLUG = 'churchtools-suite-elementor';
	const PLUGIN_FILE = 'churchtools-suite-elementor/churchtools-suite-elementor.php';

	/**
	 * Resolve active plugin file key used by WordPress update transient.
	 * Supports renamed plugin folder installations.
	 *
	 * @param object $transient Update transient object
	 * @return string
	 */
	private static function resolve_plugin_file( $transient ): string {
		$default = defined( 'CTS_ELEMENTOR_BASENAME' ) ? CTS_ELEMENTOR_BASENAME : self::PLUGIN_FILE;

		if ( ! is_object( $transient ) || empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $default;
		}

		if ( isset( $transient->checked[ $default ] ) ) {
			return $default;
		}

		foreach ( array_keys( $transient->checked ) as $plugin_file ) {
			if ( is_string( $plugin_file ) && preg_match( '#/churchtools-suite-elementor\.php$#', $plugin_file ) ) {
				return $plugin_file;
			}
		}

		return $default;
	}

	public static function init(): void {
		// Offer update info to WordPress update API
		add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );
		add_filter( 'site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );
		
		// Provide plugin information for update modal
		add_filter( 'plugins_api', [ __CLASS__, 'plugins_api_filter' ], 10, 3 );

		// Force refresh when opening Plugins / Updates screens to avoid stale cache.
		add_action( 'load-plugins.php', [ __CLASS__, 'force_cache_refresh' ] );
		add_action( 'load-update-core.php', [ __CLASS__, 'force_cache_refresh' ] );
	}

	/**
	 * Force refresh updater caches so WordPress requests fresh release metadata.
	 */
	public static function force_cache_refresh(): void {
		self::clear_cache();
		delete_site_transient( 'update_plugins' );
		wp_clean_plugins_cache();
		wp_update_plugins();
	}

	/**
	 * Get latest release info from GitHub
	 *
	 * @return array|WP_Error Release info or error
	 */
	public static function get_latest_release_info() {
		// Check cache first (1 hour)
		$cache_key = 'cts_elementor_latest_release';
		$cached = get_transient( $cache_key );
		if ( $cached !== false ) {
			return $cached;
		}

		$response = wp_remote_get( self::GITHUB_API_RELEASES_LATEST, [
			'timeout' => 10,
			'headers' => [
				'User-Agent' => 'ChurchTools-Suite-Elementor-Updater',
				'Accept'     => 'application/vnd.github.v3+json',
			],
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( $status !== 200 ) {
			return new WP_Error( 'github_error', sprintf( 'GitHub API returned status %d', $status ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['tag_name'] ) ) {
			return new WP_Error( 'no_tag', 'No tag_name in GitHub response' );
		}

		// Find ZIP asset for Elementor addon inside monorepo release assets
		$zip_url = '';
		$asset_version = '';
		if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				if (
					isset( $asset['browser_download_url'] )
					&& ! empty( $asset['name'] )
					&& strpos( $asset['name'], 'churchtools-suite-elementor-' ) === 0
					&& strpos( $asset['name'], '.zip' ) !== false
				) {
					$zip_url = $asset['browser_download_url'];

					if ( preg_match( '/^churchtools-suite-elementor-(.+)\.zip$/i', (string) $asset['name'], $matches ) ) {
						$asset_version = ltrim( (string) $matches[1], 'vV' );
					}
					break;
				}
			}
		}

		if ( empty( $zip_url ) ) {
			return new WP_Error( 'no_zip', 'No ZIP asset found in release' );
		}

		$resolved_version = $asset_version !== '' ? $asset_version : ltrim( (string) $data['tag_name'], 'vV' );

		$info = [
			'tag_name'       => $data['tag_name'],
			'version'        => $resolved_version,
			'zip_url'        => $zip_url,
			'html_url'       => $data['html_url'] ?? '',
			'name'           => $data['name'] ?? $data['tag_name'],
			'body'           => $data['body'] ?? '',
			'published_at'   => $data['published_at'] ?? '',
		];

		// Cache for 1 hour
		set_transient( $cache_key, $info, HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Push update info to WordPress transient
	 *
	 * @param object $transient
	 * @return object
	 */
	public static function push_update_to_transient( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$plugin_file = self::resolve_plugin_file( $transient );

		$release = self::get_latest_release_info();
		if ( is_wp_error( $release ) ) {
			return $transient;
		}

		$current_version = $transient->checked[ $plugin_file ] ?? CTS_ELEMENTOR_VERSION;
		$current_version = ltrim( trim( (string) $current_version ), 'vV' );
		$latest_version = ltrim( trim( (string) ( $release['version'] ?? '' ) ), 'vV' );

		if ( $latest_version === '' ) {
			unset( $transient->response[ $plugin_file ] );
			return $transient;
		}

		if ( version_compare( $latest_version, $current_version, '>' ) ) {
			$plugin_data = [
				'slug'        => self::PLUGIN_SLUG,
				'plugin'      => $plugin_file,
				'new_version' => $latest_version,
				'url'         => $release['html_url'],
				'package'     => $release['zip_url'],
				'tested'      => '6.7',
				'requires_php' => '8.0',
			];

			$transient->response[ $plugin_file ] = (object) $plugin_data;
		} else {
			unset( $transient->response[ $plugin_file ] );
			$transient->no_update[ $plugin_file ] = (object) [
				'slug'        => self::PLUGIN_SLUG,
				'plugin'      => $plugin_file,
				'new_version' => $current_version,
				'url'         => $release['html_url'],
				'package'     => '',
			];
		}

		return $transient;
	}

	/**
	 * Provide plugin information for the update modal
	 *
	 * @param false|object|array $result
	 * @param string $action
	 * @param object $args
	 * @return false|object|array
	 */
	public static function plugins_api_filter( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== self::PLUGIN_SLUG ) {
			return $result;
		}

		$release = self::get_latest_release_info();
		if ( is_wp_error( $release ) ) {
			return $result;
		}

		$plugin_info = (object) [
			'name'          => 'ChurchTools Suite - Elementor Integration',
			'slug'          => self::PLUGIN_SLUG,
			'version'       => $release['version'],
			'author'        => '<a href="https://feg-aschaffenburg.de">FEG Aschaffenburg</a>',
			'homepage'      => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor',
			'requires'      => '6.0',
			'tested'        => '6.7',
			'requires_php'  => '8.0',
			'download_link' => $release['zip_url'],
			'sections'      => [
				'description' => 'Elementor Page Builder Widget für ChurchTools Suite Events. Zeigt Events in Listen-, Raster- oder Kalender-Ansicht.',
				'changelog'   => $release['body'] ? wp_kses_post( $release['body'] ) : 'Siehe GitHub Release für Details.',
			],
			'banners'       => [],
			'external'      => true,
		];

		return $plugin_info;
	}

	/**
	 * Clear update cache
	 */
	public static function clear_cache(): void {
		delete_transient( 'cts_elementor_latest_release' );
	}
}
