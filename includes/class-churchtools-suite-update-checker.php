<?php
/**
 * Update Checker
 *
 * Injects GitHub latest release into WordPress plugin update transient
 * so the Plugins list shows available updates and the package URL points
 * to the release asset (ZIP).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ChurchTools_Suite_Update_Checker {

    const TRANSIENT_KEY = 'churchtools_suite_github_release';
    const GITHUB_API_URL = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';

    public static function init(): void {
        // Hook both pre_set and site_transient variants to ensure compatibility
        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'check_for_update' ] );
        add_filter( 'site_transient_update_plugins', [ __CLASS__, 'check_for_update' ] );
    }

    /**
     * Inject update information into the plugins transient
     *
     * @param object $transient
     * @return object
     */
    public static function check_for_update( $transient ) {
        if ( empty( $transient ) || empty( $transient->checked ) ) {
            return $transient;
        }

        $cache = get_transient( self::TRANSIENT_KEY );
        if ( $cache ) {
            $release = $cache;
        } else {
            $release = self::fetch_latest_release();
            if ( is_wp_error( $release ) ) {
                return $transient;
            }
            // Cache for 60 minutes
            set_transient( self::TRANSIENT_KEY, $release, HOUR_IN_SECONDS );
        }

        if ( empty( $release['tag_name'] ) ) {
            return $transient;
        }

        $latest_version = ltrim( $release['tag_name'], 'v' );

        if ( version_compare( CHURCHTOOLS_SUITE_VERSION, $latest_version, '<' ) ) {
            // Find asset URL (first asset with browser_download_url)
            $asset_url = '';
            if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
                foreach ( $release['assets'] as $asset ) {
                    if ( ! empty( $asset['browser_download_url'] ) ) {
                        $asset_url = $asset['browser_download_url'];
                        break;
                    }
                }
            }

            if ( empty( $asset_url ) ) {
                return $transient;
            }

            // Determine plugin file key as used in the transient
            $plugin_file = self::find_plugin_file_key( $transient );

            $update = new stdClass();
            $update->slug = dirname( CHURCHTOOLS_SUITE_BASENAME );
            $update->new_version = $latest_version;
            $update->url = $release['html_url'] ?? 'https://github.com/FEGAschaffenburg/churchtools-suite';
            $update->package = $asset_url;

            $transient->response[ $plugin_file ] = $update;
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::debug( 'update_checker', 'Injected update for plugin', [ 'plugin_file' => $plugin_file, 'new_version' => $latest_version, 'package' => $asset_url ] );
            }
        }

        return $transient;
    }

    /**
     * Fetch latest GitHub release
     *
     * @return array|WP_Error
     */
    private static function fetch_latest_release() {
        $args = [
            'headers' => [
                'User-Agent' => 'ChurchTools-Suite-Update-Checker',
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'timeout' => 20,
        ];

        // Optional token from option or constant
        $token = get_option( 'churchtools_suite_github_token', '' );
        if ( empty( $token ) && defined( 'WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN' ) ) {
            $token = WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN;
        }

        if ( ! empty( $token ) ) {
            $args['headers']['Authorization'] = 'token ' . $token;
        }

        $response = wp_remote_get( self::GITHUB_API_URL, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            return new WP_Error( 'github_api_error', sprintf( 'GitHub API returned %s', $code ) );
        }

        $data = json_decode( $body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'json_error', 'Invalid JSON from GitHub API' );
        }

        return $data;
    }

    /**
     * Find the plugin file key in the transient->checked array
     * Fallback to CHURCHTOOLS_SUITE_BASENAME if not found
     *
     * @param object $transient
     * @return string
     */
    private static function find_plugin_file_key( $transient ): string {
        $needle = basename( CHURCHTOOLS_SUITE_BASENAME );
        foreach ( $transient->checked as $key => $ver ) {
            if ( strpos( $key, $needle ) !== false ) {
                return $key;
            }
        }

        // fallback
        return CHURCHTOOLS_SUITE_BASENAME;
    }
}
