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
    const GITHUB_API_RELEASES_LATEST = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';

    public static function init(): void {
        add_action( self::CRON_HOOK, [ __CLASS__, 'check_and_update' ] );

        // Ensure weekly schedule exists if requested
        add_filter( 'cron_schedules', [ __CLASS__, 'add_weekly_cron_schedule' ] );

        // Schedule according to saved option
        $interval = get_option( 'churchtools_suite_update_interval', 'daily' );
        $schedule_key = in_array( $interval, [ 'hourly', 'daily', 'weekly' ], true ) ? $interval : 'daily';

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            // Map weekly to 'weekly' (added above)
            wp_schedule_event( time(), $schedule_key === 'weekly' ? 'weekly' : $schedule_key, self::CRON_HOOK );
        }
    }

    /**
     * Add weekly schedule if not present
     */
    public static function add_weekly_cron_schedule( array $schedules ): array {
        if ( ! isset( $schedules['weekly'] ) ) {
            $schedules['weekly'] = [
                'interval' => 7 * 24 * 60 * 60,
                'display'  => __( 'Weekly', 'churchtools-suite' ),
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
        return trim( (string) get_option( 'churchtools_suite_github_token', '' ) );
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

        // Perform update
        self::perform_update( $info['zip_url'], $info['tag_name'] );
    }

    /**
     * Get latest release info from GitHub without performing an update.
     *
     * @return array|WP_Error
     */
    public static function get_latest_release_info() {
        $headers = [ 'User-Agent' => 'ChurchTools-Suite-Updater' ];
        $token = self::get_github_token();
        if ( ! empty( $token ) ) {
            $headers['Authorization'] = 'token ' . $token;
        }

        $response = wp_remote_get( self::GITHUB_API_RELEASES_LATEST, [ 'headers' => $headers, 'timeout' => 20 ] );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            return new WP_Error( 'invalid_release', 'Invalid release data' );
        }

        $latest_tag = ltrim( $data['tag_name'], 'v' );
        $current = ltrim( CHURCHTOOLS_SUITE_VERSION, 'v' );
        $is_update = version_compare( $latest_tag, $current, '>' );

        // Determine zip URL (prefer release asset matching plugin zip, fallback to zipball_url)
        $zip_url = '';
        if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
            foreach ( $data['assets'] as $asset ) {
                if ( isset( $asset['browser_download_url'] ) ) {
                    $zip_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

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
