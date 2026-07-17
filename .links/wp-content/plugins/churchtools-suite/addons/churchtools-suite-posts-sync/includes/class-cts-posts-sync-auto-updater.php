<?php
/**
 * Posts Sync Addon Auto Updater
 *
 * Provides WordPress update metadata for the Posts Sync addon
 * based on GitHub release assets from the monorepo.
 *
 * @package churchtools_suite_posts_sync
 * @since   0.1.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Posts_Sync_Auto_Updater {

	const GITHUB_API_RELEASES = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases?per_page=30';
	const GITHUB_API_RELEASES_LATEST = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';
	const PLUGIN_SLUG = 'churchtools-suite-posts-sync';
	const PLUGIN_FILE = 'churchtools-suite-posts-sync/churchtools-suite-posts-sync.php';

	/**
	 * Resolve plugin file used in the WP update transient.
	 * Supports custom plugin folder names.
	 *
	 * @param object $transient Update transient object.
	 * @return string
	 */
	private static function resolve_plugin_file( $transient ): string {
		$default = defined( 'CTS_POSTS_SYNC_BASENAME' ) ? CTS_POSTS_SYNC_BASENAME : self::PLUGIN_FILE;

		if ( ! is_object( $transient ) || empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $default;
		}

		if ( isset( $transient->checked[ $default ] ) ) {
			return $default;
		}

		foreach ( array_keys( $transient->checked ) as $plugin_file ) {
			if ( is_string( $plugin_file ) && preg_match( '#/churchtools-suite-posts-sync\.php$#', $plugin_file ) ) {
				return $plugin_file;
			}
		}

		return $default;
	}

	/**
	 * Register update hooks.
	 */
	public static function init(): void {
		add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );
		add_filter( 'site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );
		add_filter( 'plugins_api', [ __CLASS__, 'plugins_api_filter' ], 10, 3 );
		add_action( 'load-plugins.php', [ __CLASS__, 'force_cache_refresh' ] );
		add_action( 'load-update-core.php', [ __CLASS__, 'force_cache_refresh' ] );
	}

	/**
	 * Force refresh updater caches.
	 */
	public static function force_cache_refresh(): void {
		self::clear_cache();
		delete_site_transient( 'update_plugins' );
		wp_clean_plugins_cache();
		wp_update_plugins();
	}

	/**
	 * Get latest release info from GitHub with asset matching.
	 *
	 * @return array|WP_Error
	 */
	public static function get_latest_release_info() {
		$cache_key = 'cts_posts_sync_latest_release';
		$cached = get_transient( $cache_key );
		if ( $cached !== false ) {
			return $cached;
		}

		$headers = [
			'User-Agent' => 'ChurchTools-Suite-Posts-Sync-Updater',
			'Accept'     => 'application/vnd.github.v3+json',
		];

		$response = wp_remote_get( self::GITHUB_API_RELEASES, [
			'timeout' => 20,
			'headers' => $headers,
		] );

		$release = null;
		if ( ! is_wp_error( $response ) && (int) wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$releases = json_decode( $body, true );
			if ( is_array( $releases ) ) {
				$release = self::select_highest_stable_release_with_asset( $releases );
			}
		}

		if ( ! is_array( $release ) || empty( $release['tag_name'] ) ) {
			$response = wp_remote_get( self::GITHUB_API_RELEASES_LATEST, [
				'timeout' => 20,
				'headers' => $headers,
			] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
				return new WP_Error( 'github_error', 'GitHub API returned non-200 response for latest release.' );
			}

			$body = wp_remote_retrieve_body( $response );
			$release = json_decode( $body, true );
		}

		if ( ! is_array( $release ) || empty( $release['tag_name'] ) ) {
			return new WP_Error( 'invalid_release', 'No valid release data from GitHub.' );
		}

		$zip_url = '';
		$asset_version = '';
		$assets = $release['assets'] ?? [];
		if ( is_array( $assets ) ) {
			foreach ( $assets as $asset ) {
				if (
					isset( $asset['browser_download_url'] )
					&& ! empty( $asset['name'] )
					&& strpos( (string) $asset['name'], 'churchtools-suite-posts-sync-' ) === 0
					&& str_ends_with( (string) $asset['name'], '.zip' )
				) {
					$zip_url = (string) $asset['browser_download_url'];

					if ( preg_match( '/^churchtools-suite-posts-sync-(.+)\.zip$/i', (string) $asset['name'], $matches ) ) {
						$asset_version = ltrim( (string) $matches[1], 'vV' );
					}
					break;
				}
			}
		}

		if ( $zip_url === '' ) {
			return new WP_Error( 'no_zip', 'No Posts Sync ZIP asset found in release.' );
		}

		$resolved_version = $asset_version !== '' ? $asset_version : ltrim( (string) $release['tag_name'], 'vV' );

		$info = [
			'tag_name'     => (string) $release['tag_name'],
			'version'      => $resolved_version,
			'zip_url'      => $zip_url,
			'html_url'     => (string) ( $release['html_url'] ?? '' ),
			'name'         => (string) ( $release['name'] ?? $release['tag_name'] ),
			'body'         => (string) ( $release['body'] ?? '' ),
			'published_at' => (string) ( $release['published_at'] ?? '' ),
		];

		set_transient( $cache_key, $info, HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Select highest stable release that contains the Posts Sync ZIP asset.
	 *
	 * @param array $releases
	 * @return array|null
	 */
	private static function select_highest_stable_release_with_asset( array $releases ): ?array {
		$selected = null;
		$selected_version = '0.0.0';

		foreach ( $releases as $release ) {
			if ( ! is_array( $release ) || ! empty( $release['draft'] ) || ! empty( $release['prerelease'] ) ) {
				continue;
			}

			$tag = ltrim( (string) ( $release['tag_name'] ?? '' ), 'vV' );
			if ( $tag === '' || ! preg_match( '/^\d+(?:\.\d+)+$/', $tag ) ) {
				continue;
			}

			$has_asset = false;
			$assets = $release['assets'] ?? [];
			if ( is_array( $assets ) ) {
				foreach ( $assets as $asset ) {
					if ( ! empty( $asset['name'] ) && strpos( (string) $asset['name'], 'churchtools-suite-posts-sync-' ) === 0 && str_ends_with( (string) $asset['name'], '.zip' ) ) {
						$has_asset = true;
						break;
					}
				}
			}

			if ( ! $has_asset ) {
				continue;
			}

			if ( $selected === null || version_compare( $tag, $selected_version, '>' ) ) {
				$selected = $release;
				$selected_version = $tag;
			}
		}

		return $selected;
	}

	/**
	 * Push update metadata into WordPress transient.
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

		$current_version = $transient->checked[ $plugin_file ] ?? CTS_POSTS_SYNC_VERSION;
		$current_version = ltrim( trim( (string) $current_version ), 'vV' );
		$latest_version = ltrim( trim( (string) ( $release['version'] ?? '' ) ), 'vV' );

		if ( $latest_version === '' ) {
			unset( $transient->response[ $plugin_file ] );
			return $transient;
		}

		if ( version_compare( $latest_version, $current_version, '>' ) ) {
			$transient->response[ $plugin_file ] = (object) [
				'slug'         => self::PLUGIN_SLUG,
				'plugin'       => $plugin_file,
				'new_version'  => $latest_version,
				'url'          => $release['html_url'],
				'package'      => $release['zip_url'],
				'tested'       => '6.7',
				'requires_php' => '8.0',
			];
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
	 * Plugin info modal support.
	 *
	 * @param false|object|array $result
	 * @param string             $action
	 * @param object             $args
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

		return (object) [
			'name'          => 'ChurchTools Suite – Posts Sync Addon',
			'slug'          => self::PLUGIN_SLUG,
			'version'       => $release['version'],
			'author'        => '<a href="https://feg-aschaffenburg.de">FEG Aschaffenburg</a>',
			'homepage'      => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-posts-sync',
			'requires'      => '5.0',
			'tested'        => '6.7',
			'requires_php'  => '8.0',
			'download_link' => $release['zip_url'],
			'sections'      => [
				'description' => 'Synchronisiert ChurchTools-Posts in WordPress-Posts und -Seiten.',
				'changelog'   => $release['body'] ? wp_kses_post( $release['body'] ) : 'Siehe GitHub Release für Details.',
			],
			'banners'       => [],
			'external'      => true,
		];
	}

	/**
	 * Clear update cache.
	 */
	public static function clear_cache(): void {
		delete_transient( 'cts_posts_sync_latest_release' );
	}
}
