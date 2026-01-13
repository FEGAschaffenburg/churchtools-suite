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
        
        // Hook for after update completion
        add_action( 'upgrader_process_complete', [ __CLASS__, 'after_update' ], 10, 2 );
        
        // Force cache refresh on plugins page (v1.0.3.14)
        add_action( 'load-plugins.php', [ __CLASS__, 'force_cache_refresh' ] );
    }
    
    /**
     * Force cache refresh when viewing plugins page
     * Ensures latest version is always shown (v1.0.3.14)
     */
    public static function force_cache_refresh(): void {
        delete_transient( self::TRANSIENT_KEY );
        delete_site_transient( 'update_plugins' );
        wp_clean_plugins_cache();
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
    
    /**
     * Handle after update completion
     * 
     * Clears caches and ensures proper WordPress redirect/refresh
     *
     * @param WP_Upgrader $upgrader
     * @param array $hook_extra
     */
    public static function after_update( $upgrader, $hook_extra ): void {
        // Check if this is a plugin update
        if ( ! isset( $hook_extra['type'] ) || $hook_extra['type'] !== 'plugin' ) {
            return;
        }
        
        // Check if this is an update (not install)
        if ( ! isset( $hook_extra['action'] ) || $hook_extra['action'] !== 'update' ) {
            return;
        }
        
        // Check if our plugin was updated
        $our_plugin = false;
        if ( isset( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ) {
            foreach ( $hook_extra['plugins'] as $plugin ) {
                if ( $plugin === CHURCHTOOLS_SUITE_BASENAME || strpos( $plugin, 'churchtools-suite.php' ) !== false ) {
                    $our_plugin = true;
                    break;
                }
            }
        } elseif ( isset( $hook_extra['plugin'] ) && ( $hook_extra['plugin'] === CHURCHTOOLS_SUITE_BASENAME || strpos( $hook_extra['plugin'], 'churchtools-suite.php' ) !== false ) ) {
            $our_plugin = true;
        }
        
        if ( ! $our_plugin ) {
            return;
        }
        
        // Clear our cache
        delete_transient( self::TRANSIENT_KEY );
        
        // Cleanup old update directories (v0.10.3.50)
        self::cleanup_old_update_directories();
        
        // Log successful update
        if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
            ChurchTools_Suite_Logger::info( 'update_checker', 'Plugin updated successfully', [
                'new_version' => CHURCHTOOLS_SUITE_VERSION,
                'hook_extra' => $hook_extra,
            ] );
        }
        
        // Force WordPress to refresh the plugins page
        // This ensures the "Successfully updated" message is shown
        if ( ! wp_doing_ajax() ) {
            wp_cache_flush();
        }
    }
    
    /**
     * Cleanup old update directories left behind by WordPress updater
     * 
     * WordPress sometimes leaves temporary plugin directories when updating
     * from GitHub releases. This method removes them.
     * 
     * Pattern: FEGAschaffenburg-churchtools-suite-{commit-hash}
     * 
     * @since 0.10.3.50
     * @return void
     */
    private static function cleanup_old_update_directories(): void {
        $plugins_dir = WP_PLUGIN_DIR;
        
        if ( ! is_dir( $plugins_dir ) ) {
            return;
        }
        
        $dirs = glob( $plugins_dir . '/FEGAschaffenburg-churchtools-suite-*' );
        
        if ( empty( $dirs ) || ! is_array( $dirs ) ) {
            return;
        }
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        
        $deleted_count = 0;
        
        foreach ( $dirs as $dir ) {
            // Safety check: Only delete directories matching the pattern exactly
            if ( ! is_dir( $dir ) || ! preg_match( '/FEGAschaffenburg-churchtools-suite-[a-f0-9]{7,}$/i', basename( $dir ) ) ) {
                continue;
            }
            
            // Use WordPress filesystem API for safe deletion
            global $wp_filesystem;
            
            if ( WP_Filesystem() ) {
                if ( $wp_filesystem->delete( $dir, true ) ) {
                    $deleted_count++;
                    
                    if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                        ChurchTools_Suite_Logger::debug( 'update_checker', 'Deleted old update directory', [
                            'directory' => basename( $dir ),
                        ] );
                    }
                }
            }
        }
        
        if ( $deleted_count > 0 && class_exists( 'ChurchTools_Suite_Logger' ) ) {
            ChurchTools_Suite_Logger::info( 'update_checker', 'Cleanup completed', [
                'deleted_directories' => $deleted_count,
            ] );
        }
    }
}
