<?php
/**
 * Simple Auto Updater
 *
 * Checks GitHub releases for new versions and installs the ZIP automatically.
 * Minimal implementation: scheduled daily check and safe unzip/copy flow.
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ChurchTools_Suite_Auto_Updater {

    const CRON_HOOK = 'churchtools_suite_check_updates';
    const GITHUB_API_RELEASES = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases?per_page=30';
    const GITHUB_API_RELEASES_LATEST = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';

    public static function init(): void {
        add_action( self::CRON_HOOK, [ __CLASS__, 'check_and_update' ] );

        // Ensure weekly schedule exists if requested
        add_filter( 'cron_schedules', [ __CLASS__, 'add_weekly_cron_schedule' ] );
        
        // Offer update info to WordPress update API so plugin updates show in Plugins list
        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );
        add_filter( 'site_transient_update_plugins', [ __CLASS__, 'push_update_to_transient' ] );

        // Schedule according to saved option
        $interval = get_option( 'churchtools_suite_update_interval', 'daily' );
        $schedule_key = in_array( $interval, [ 'hourly', 'daily', 'weekly' ], true ) ? $interval : 'daily';

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            // Map weekly to 'weekly' (added above)
            wp_schedule_event( time(), $schedule_key === 'weekly' ? 'weekly' : $schedule_key, self::CRON_HOOK );
        }
    }
    
    /**
     * Get display name for our cron hook (for manual display)
     */
    public static function get_cron_hook_display_name(): string {
        return __( 'ChurchTools Suite: Update-Pr\u00fcfung', 'churchtools-suite' );
    }
    
    /**
     * Add weekly schedule if not present
     */
    public static function add_weekly_cron_schedule( array $schedules ): array {
        if ( ! isset( $schedules['weekly'] ) ) {
            $schedules['weekly'] = [
                'interval' => 7 * 24 * 60 * 60,
                'display'  => __( 'Wöchentlich', 'churchtools-suite' ),
            ];
        }
        return $schedules;
    }

    /**
     * Reschedule the updater with a new interval
     *
     * @param string $interval hourly|daily|weekly
     */
    public static function reschedule( string $interval ): void {
        // Clear existing
        wp_clear_scheduled_hook( self::CRON_HOOK );

        // Ensure weekly schedule exists
        add_filter( 'cron_schedules', [ __CLASS__, 'add_weekly_cron_schedule' ] );

        $schedule_key = in_array( $interval, [ 'hourly', 'daily', 'weekly' ], true ) ? $interval : 'daily';
        wp_schedule_event( time(), $schedule_key === 'weekly' ? 'weekly' : $schedule_key, self::CRON_HOOK );
    }

    /**
     * Get stored GitHub token
     *
     * @return string
     */
    private static function get_github_token(): string {
        $token = trim( (string) get_option( 'churchtools_suite_github_token', '' ) );
        if ( $token === '' && defined( 'WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN' ) ) {
            $token = trim( (string) WP_CHURCHTOOLS_SUITE_GITHUB_TOKEN );
        }

        return $token;
    }

    /**
     * Build a descriptive WP_Error from a GitHub API response.
     *
     * @param array  $response HTTP response array from wp_remote_get
     * @param string $context  Short context label
     * @return WP_Error
     */
    private static function build_github_http_error( array $response, string $context ): WP_Error {
        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = (string) wp_remote_retrieve_body( $response );
        $msg = '';

        if ( $body !== '' ) {
            $decoded = json_decode( $body, true );
            if ( is_array( $decoded ) && ! empty( $decoded['message'] ) ) {
                $msg = (string) $decoded['message'];
            }
        }

        if ( $msg === '' ) {
            $msg = sprintf( 'GitHub API HTTP %d (%s)', $code, $context );
        } else {
            $msg = sprintf( 'GitHub API HTTP %d (%s): %s', $code, $context, $msg );
        }

        return new WP_Error( 'github_api_error', $msg, [ 'http_code' => $code, 'context' => $context ] );
    }

    /**
     * Check GitHub for latest release and update if newer.
     */
    public static function check_and_update(): void {
        // Allow running in cron or manually from admin
        if ( ! defined( 'WP_CLI' ) && ! ( is_admin() || wp_doing_cron() ) ) {
            return;
        }

        $info = self::get_latest_release_info();
        if ( is_wp_error( $info ) ) {
            // Log error and exit
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::error( 'updater', 'Failed to fetch release info', [ 'error' => $info->get_error_message() ] );
            }
            return;
        }

        if ( empty( $info['is_update'] ) || empty( $info['zip_url'] ) ) {
            // No update available or no package
            return;
        }

        // Check auto-update settings
        $auto_enabled = (int) get_option( 'churchtools_suite_auto_update_enabled', 0 );
        $auto_level = get_option( 'churchtools_suite_auto_update_level', 'none' );

        if ( ! $auto_enabled ) {
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::info( 'updater', 'Update available but auto-update disabled by settings', [ 'tag' => $info['tag_name'] ] );
            }
            return;
        }

        // Determine bump level between current and latest
        $current = ltrim( CHURCHTOOLS_SUITE_VERSION, 'v' );
        $latest = ltrim( $info['latest_version'] ?? ( $info['tag_name'] ?? '' ), 'v' );
        $bump = self::determine_bump_level( $current, $latest );

        $allow = false;
        switch ( $auto_level ) {
            case 'major':
                // Only allow when major version increased
                $allow = ( $bump === 'major' );
                break;
            case 'major_minor':
                // Allow major or minor bumps
                $allow = ( $bump === 'major' || $bump === 'minor' );
                break;
            case 'all':
                // Allow any bump (major, minor, patch)
                $allow = ( $bump === 'major' || $bump === 'minor' || $bump === 'patch' );
                break;
            default:
                $allow = false;
        }

        if ( ! $allow ) {
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::info( 'updater', 'Auto-update skipped due to configured level', [ 'current' => $current, 'latest' => $latest, 'bump' => $bump, 'configured' => $auto_level ] );
            }
            return;
        }

        // Perform update
        self::perform_update( $info['zip_url'], $info['tag_name'] );
    }

    /**
     * Determine semantic bump level between two versions
     * Returns 'major', 'minor', 'patch' or 'none'
     */
    private static function determine_bump_level( string $current, string $latest ): string {
        if ( empty( $current ) || empty( $latest ) ) {
            return 'none';
        }

        // Normalize to numbers
        $curParts = array_map( 'intval', array_pad( explode( '.', $current ), 3, 0 ) );
        $latParts = array_map( 'intval', array_pad( explode( '.', $latest ), 3, 0 ) );

        if ( $latParts[0] > $curParts[0] ) {
            return 'major';
        }
        if ( $latParts[0] === $curParts[0] && $latParts[1] > $curParts[1] ) {
            return 'minor';
        }
        if ( $latParts[0] === $curParts[0] && $latParts[1] === $curParts[1] && $latParts[2] > $curParts[2] ) {
            return 'patch';
        }
        return 'none';
    }

    /**
     * Get latest release info from GitHub without performing an update.
     *
     * @return array|WP_Error
     */
    public static function get_latest_release_info() {
        $headers = [
            'User-Agent' => 'ChurchTools-Suite-Updater',
            'Accept' => 'application/vnd.github.v3+json',
        ];
        $token = self::get_github_token();
        if ( ! empty( $token ) ) {
            $headers['Authorization'] = 'token ' . $token;
        }

        $last_http_error = null;

        // Prefer full releases list and pick highest stable version (more reliable than /latest for this repo)
        $response = wp_remote_get( self::GITHUB_API_RELEASES, [ 'headers' => $headers, 'timeout' => 20 ] );
        if ( is_wp_error( $response ) ) {
            $last_http_error = $response;
        } else {
            $releases_code = (int) wp_remote_retrieve_response_code( $response );
            if ( $releases_code === 200 ) {
                $body = wp_remote_retrieve_body( $response );
                $releases = json_decode( $body, true );
                $selected = self::select_highest_stable_release( $releases );
                if ( ! empty( $selected['tag_name'] ) ) {
                    $data = $selected;
                }
            } else {
                $last_http_error = self::build_github_http_error( $response, 'releases' );
            }
        }

        // Fallback to GitHub /latest endpoint
        if ( empty( $data ) || ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            $response = wp_remote_get( self::GITHUB_API_RELEASES_LATEST, [ 'headers' => $headers, 'timeout' => 20 ] );
            if ( is_wp_error( $response ) ) {
                $last_http_error = $response;
            } else {
                $latest_code = (int) wp_remote_retrieve_response_code( $response );
                if ( $latest_code === 200 ) {
                    $body = wp_remote_retrieve_body( $response );
                    $data = json_decode( $body, true );
                } else {
                    $last_http_error = self::build_github_http_error( $response, 'latest' );
                }
            }
        }

        // If the Releases API didn't return a valid release, attempt to fall back to the tags API
        if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            // Try tags endpoint (may exist when a tag was pushed but no GitHub Release created)
            $tags_url = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/tags';
            $tags_resp = wp_remote_get( $tags_url, [ 'headers' => $headers, 'timeout' => 20 ] );
            if ( is_wp_error( $tags_resp ) ) {
                return $last_http_error instanceof WP_Error ? $last_http_error : $tags_resp;
            }
            $tags_code = (int) wp_remote_retrieve_response_code( $tags_resp );
            if ( $tags_code !== 200 ) {
                $tags_error = self::build_github_http_error( $tags_resp, 'tags' );
                return $last_http_error instanceof WP_Error ? $last_http_error : $tags_error;
            }
            $tags_body = wp_remote_retrieve_body( $tags_resp );
            $tags = json_decode( $tags_body, true );
            if ( is_array( $tags ) && ! empty( $tags[0]['name'] ) ) {
                $tag_name = $tags[0]['name'];
                $latest_tag = ltrim( $tag_name, 'v' );
                $current = ltrim( CHURCHTOOLS_SUITE_VERSION, 'v' );
                $is_update = version_compare( $latest_tag, $current, '>' );

                // Construct zip URL for the tag (GitHub provides archive by tag)
                $zip_url = sprintf( 'https://github.com/FEGAschaffenburg/churchtools-suite/archive/refs/tags/%s.zip', rawurlencode( $tag_name ) );
                $html_url = sprintf( 'https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/%s', rawurlencode( $tag_name ) );

                return [
                    'tag_name' => $tag_name,
                    'latest_version' => $latest_tag,
                    'is_update' => $is_update,
                    'zip_url' => $zip_url,
                    'html_url' => $html_url,
                    'assets' => [],
                ];
            }

            if ( $last_http_error instanceof WP_Error ) {
                return $last_http_error;
            }

            return new WP_Error( 'invalid_release', 'Invalid release data from GitHub APIs' );
        }

        $latest_tag = ltrim( $data['tag_name'], 'v' );
        $current = ltrim( CHURCHTOOLS_SUITE_VERSION, 'v' );
        $is_update = version_compare( $latest_tag, $current, '>' );

        // Determine zip URL (prefer main plugin ZIP in monorepo releases)
        $zip_url = self::select_main_plugin_zip_url( $data['assets'] ?? [] );

        if ( empty( $zip_url ) && ! empty( $data['zipball_url'] ) ) {
            $zip_url = $data['zipball_url'];
        }

        return [
            'tag_name' => $data['tag_name'],
            'latest_version' => $latest_tag,
            'is_update' => $is_update,
            'zip_url' => $zip_url,
            'html_url' => $data['html_url'] ?? '',
            'assets' => $data['assets'] ?? [],
        ];
    }

    /**
     * Select highest stable release by semantic version from GitHub releases list.
     *
     * @param array $releases
     * @return array
     */
    private static function select_highest_stable_release( array $releases ): array {
        $selected = [];
        $selected_version = '0.0.0';

        foreach ( $releases as $release ) {
            if ( ! is_array( $release ) ) {
                continue;
            }
            if ( ! empty( $release['draft'] ) || ! empty( $release['prerelease'] ) ) {
                continue;
            }
            if ( empty( $release['tag_name'] ) ) {
                continue;
            }

            $version = ltrim( (string) $release['tag_name'], 'vV' );
            if ( ! preg_match( '/^\d+(?:\.\d+)+$/', $version ) ) {
                continue;
            }

            if ( empty( $selected ) || version_compare( $version, $selected_version, '>' ) ) {
                $selected = $release;
                $selected_version = $version;
            }
        }

        return $selected;
    }

    /**
     * Select the main plugin ZIP from release assets.
     *
     * @param array $assets
     * @return string
     */
    private static function select_main_plugin_zip_url( array $assets ): string {
        if ( empty( $assets ) ) {
            return '';
        }

        foreach ( $assets as $asset ) {
            if ( empty( $asset['name'] ) || empty( $asset['browser_download_url'] ) ) {
                continue;
            }

            $name = (string) $asset['name'];
            if ( preg_match( '/^churchtools-suite-\d+(?:\.\d+)+\.zip$/i', $name ) ) {
                return (string) $asset['browser_download_url'];
            }
        }

        // Fallback: first ZIP asset
        foreach ( $assets as $asset ) {
            if ( ! empty( $asset['name'] ) && ! empty( $asset['browser_download_url'] ) && preg_match( '/\.zip$/i', (string) $asset['name'] ) ) {
                return (string) $asset['browser_download_url'];
            }
        }

        return '';
    }

    /**
     * Public runner to start update now (used by admin manual trigger).
     *
     * @return array|WP_Error
     */
    public static function run_update_now() {
        $info = self::get_latest_release_info();
        if ( is_wp_error( $info ) ) {
            return $info;
        }

        if ( empty( $info['is_update'] ) || empty( $info['zip_url'] ) ) {
            return [ 'success' => false, 'message' => 'No update available' ];
        }

        try {
            self::perform_update( $info['zip_url'], $info['tag_name'] );
            return [ 'success' => true, 'message' => sprintf( 'Update auf %s gestartet', $info['tag_name'] ) ];
        } catch ( Exception $e ) {
            return new WP_Error( 'update_failed', $e->getMessage() );
        }
    }

    private static function perform_update( string $zip_url, string $tag ): void {
        if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
            ChurchTools_Suite_Logger::info( 'updater', sprintf( 'Updating to %s from %s', $tag, $zip_url ) );
        }

        $tmp_zip = sys_get_temp_dir() . '/cts_update_' . md5( $tag ) . '.zip';
        $tmp_dir = sys_get_temp_dir() . '/cts_update_' . md5( $tag );

        // Download
        $download_headers = [ 'User-Agent' => 'ChurchTools-Suite-Updater' ];
        $token = self::get_github_token();
        if ( ! empty( $token ) ) {
            $download_headers['Authorization'] = 'token ' . $token;
        }
        $resp = wp_remote_get( $zip_url, [ 'timeout' => 60, 'headers' => $download_headers ] );
        if ( is_wp_error( $resp ) ) {
            ChurchTools_Suite_Logger::error( 'updater', 'Download failed', [ 'error' => $resp->get_error_message() ] );
            return;
        }

        $body = wp_remote_retrieve_body( $resp );
        if ( empty( $body ) ) {
            ChurchTools_Suite_Logger::error( 'updater', 'Empty ZIP body' );
            return;
        }

        file_put_contents( $tmp_zip, $body );

        // Extract with ZipArchive
        $zip = new ZipArchive();
        if ( $zip->open( $tmp_zip ) !== true ) {
            ChurchTools_Suite_Logger::error( 'updater', 'Failed to open ZIP' );
            @unlink( $tmp_zip );
            return;
        }

        // Clean temp dir
        if ( is_dir( $tmp_dir ) ) {
            self::rrmdir( $tmp_dir );
        }
        mkdir( $tmp_dir );

        $zip->extractTo( $tmp_dir );
        $zip->close();

        // Find extracted folder (first child)
        $children = array_values( array_filter( scandir( $tmp_dir ), function( $n ) { return $n !== '.' && $n !== '..'; } ) );
        if ( empty( $children ) ) {
            ChurchTools_Suite_Logger::error( 'updater', 'No files in extracted ZIP' );
            self::rrmdir( $tmp_dir );
            @unlink( $tmp_zip );
            return;
        }

        $extracted_root = $tmp_dir . '/' . $children[0];

        // Copy files into plugin path
        $dest = rtrim( CHURCHTOOLS_SUITE_PATH, '/\\' );
        if ( ! self::rcopy( $extracted_root, $dest ) ) {
            ChurchTools_Suite_Logger::error( 'updater', 'Failed to copy files to plugin directory' );
            self::rrmdir( $tmp_dir );
            @unlink( $tmp_zip );
            return;
        }

        // Cleanup
        self::rrmdir( $tmp_dir );
        @unlink( $tmp_zip );

        // Log success
        if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
            ChurchTools_Suite_Logger::info( 'updater', sprintf( 'Plugin updated to %s', $tag ) );
        }
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::info( 'updater', sprintf( 'Updating to %s from %s (WP Upgrader)', $tag, $zip_url ) );
            }

            $tmp_zip = sys_get_temp_dir() . '/cts_update_' . md5( $tag . time() ) . '.zip';

            // Download package
            $download_headers = [ 'User-Agent' => 'ChurchTools-Suite-Updater' ];
            $token = self::get_github_token();
            if ( ! empty( $token ) ) {
                $download_headers['Authorization'] = 'token ' . $token;
            }
            $resp = wp_remote_get( $zip_url, [ 'timeout' => 120, 'headers' => $download_headers ] );
            if ( is_wp_error( $resp ) ) {
                ChurchTools_Suite_Logger::error( 'updater', 'Download failed', [ 'error' => $resp->get_error_message() ] );
                return;
            }

            $body = wp_remote_retrieve_body( $resp );
            if ( empty( $body ) ) {
                ChurchTools_Suite_Logger::error( 'updater', 'Empty ZIP body' );
                return;
            }

            file_put_contents( $tmp_zip, $body );

            // Prepare WP Filesystem and Upgrader
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

            // Create backup of existing plugin directory
            $backup_zip = sys_get_temp_dir() . '/cts_backup_' . md5( CHURCHTOOLS_SUITE_VERSION . time() ) . '.zip';
            $plugin_dir = rtrim( CHURCHTOOLS_SUITE_PATH, '/\\' );
            $backup_ok = false;
            if ( class_exists( 'ZipArchive' ) ) {
                $zip = new ZipArchive();
                if ( $zip->open( $backup_zip, ZipArchive::CREATE ) === true ) {
                    $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $plugin_dir ) );
                    foreach ( $files as $file ) {
                        if ( $file->isDir() ) {
                            continue;
                        }
                        $filePath = $file->getRealPath();
                        $relativePath = substr( $filePath, strlen( dirname( $plugin_dir ) ) + 1 );
                        $zip->addFile( $filePath, $relativePath );
                    }
                    $zip->close();
                    $backup_ok = true;
                }
            }

            // Use Plugin_Upgrader with a non-interactive skin
            $skin = new Automatic_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader( $skin );

            $result = $upgrader->install( $tmp_zip );

            // Clean temp package
            @unlink( $tmp_zip );

            if ( is_wp_error( $result ) || $result === false ) {
                ChurchTools_Suite_Logger::error( 'updater', 'WP Upgrader failed', [ 'result' => $result ] );

                // Attempt rollback from backup
                if ( $backup_ok && file_exists( $backup_zip ) && class_exists( 'ZipArchive' ) ) {
                    $zip = new ZipArchive();
                    if ( $zip->open( $backup_zip ) === true ) {
                        // Remove current plugin dir
                        self::rrmdir( $plugin_dir );
                        mkdir( $plugin_dir );
                        $zip->extractTo( dirname( $plugin_dir ) );
                        $zip->close();
                        ChurchTools_Suite_Logger::info( 'updater', 'Rollback: restored backup after failed update' );
                    }
                }
                if ( isset( $skin ) && method_exists( $skin, 'get_errors' ) ) {
                    $errs = $skin->get_errors();
                    ChurchTools_Suite_Logger::error( 'updater', 'Upgrader skin errors', [ 'errors' => $errs ] );
                }
                // Cleanup backup
                @unlink( $backup_zip );
                return;
            }

            // Success - remove backup
            if ( isset( $backup_zip ) && file_exists( $backup_zip ) ) {
                @unlink( $backup_zip );
            }

            ChurchTools_Suite_Logger::info( 'updater', sprintf( 'Plugin updated to %s via WP Upgrader', $tag ) );
    }

    /**
     * Push GitHub release info into WP plugin update transient so updates appear on Plugins page.
     *
     * @param object|false $transient
     * @return object|false
     */
    public static function push_update_to_transient( $transient ) {
        if ( empty( $transient ) || ! is_object( $transient ) ) {
            return $transient;
        }

        // If no checked list present, bail
        if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
            return $transient;
        }

        $info = self::get_latest_release_info();
        if ( is_wp_error( $info ) ) {
            return $transient;
        }

        $plugin_file = plugin_basename( CHURCHTOOLS_SUITE_PATH . 'churchtools-suite.php' );

        $plugin_slug = self::get_plugin_slug( $plugin_file );

        if ( empty( $info['is_update'] ) || empty( $info['zip_url'] ) ) {
            if ( isset( $transient->response ) && is_array( $transient->response ) ) {
                unset( $transient->response[ $plugin_file ] );
            }
            if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
                $transient->no_update = [];
            }
            $transient->no_update[ $plugin_file ] = (object) [
                'id'          => 0,
                'slug'        => $plugin_slug,
                'plugin'      => $plugin_file,
                'new_version' => ltrim( CHURCHTOOLS_SUITE_VERSION, 'vV' ),
                'package'     => '',
                'url'         => '',
            ];
            return $transient;
        }

        // If response already set for this plugin, don't overwrite (allows other sources to take precedence)
        if ( isset( $transient->response ) && is_object( $transient->response ) && isset( $transient->response->{$plugin_file} ) ) {
            return $transient;
        }

        $update = new stdClass();
        $update->id = 0;
        $update->slug = $plugin_slug;
        $update->plugin = $plugin_file;
        $update->new_version = ltrim( $info['latest_version'] ?? ( $info['tag_name'] ?? '' ), 'v' );
        $update->package = $info['zip_url'];
        $update->url = $info['html_url'] ?? '';

        // Ensure response property exists as array (WordPress expects an array, not object)
        if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
            $transient->response = [];
        }
		if ( isset( $transient->no_update ) && is_array( $transient->no_update ) ) {
			unset( $transient->no_update[ $plugin_file ] );
		}

        $transient->response[$plugin_file] = $update;

        return $transient;
    }

    /**
     * Resolve plugin slug for update payloads.
     *
     * @param string $plugin_file
     * @return string
     */
    private static function get_plugin_slug( string $plugin_file ): string {
        $slug = dirname( $plugin_file );
        if ( $slug === '.' || $slug === '' ) {
            $slug = 'churchtools-suite';
        }

        return $slug;
    }

    private static function rcopy( string $src, string $dst ): bool {
        $dir = opendir( $src );
        if ( ! is_dir( $dst ) ) {
            @mkdir( $dst, 0755, true );
        }
        if ( $dir === false ) {
            return false;
        }
        while ( false !== ( $file = readdir( $dir ) ) ) {
            if ( ( $file !== '.' ) && ( $file !== '..' ) ) {
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;
                if ( is_dir( $srcPath ) ) {
                    if ( ! self::rcopy( $srcPath, $dstPath ) ) {
                        closedir( $dir );
                        return false;
                    }
                } else {
                    if ( ! copy( $srcPath, $dstPath ) ) {
                        closedir( $dir );
                        return false;
                    }
                }
            }
        }
        closedir( $dir );
        return true;
    }

    private static function rrmdir( string $dir ): void {
        if ( ! is_dir( $dir ) ) {
            return;
        }
        $objects = scandir( $dir );
        foreach ( $objects as $object ) {
            if ( $object !== '.' && $object !== '..' ) {
                $path = $dir . '/' . $object;
                if ( is_dir( $path ) ) {
                    self::rrmdir( $path );
                } else {
                    @unlink( $path );
                }
            }
        }
        @rmdir( $dir );
    }
}

// Auto-init
add_action( 'init', [ 'ChurchTools_Suite_Auto_Updater', 'init' ] );
