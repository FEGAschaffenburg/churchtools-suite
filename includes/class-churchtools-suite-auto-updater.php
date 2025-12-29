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

        // Schedule daily if not scheduled
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
    }

    /**
     * Check GitHub for latest release and update if newer.
     */
    public static function check_and_update(): void {
        // Allow running in cron or manually from admin
        if ( ! defined( 'WP_CLI' ) && ! ( is_admin() || wp_doing_cron() ) ) {
            return;
        }

        // Fetch latest release
        $response = wp_remote_get( self::GITHUB_API_RELEASES_LATEST, [ 'headers' => [ 'User-Agent' => 'ChurchTools-Suite-Updater' ], 'timeout' => 20 ] );

        if ( is_wp_error( $response ) ) {
            if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
                ChurchTools_Suite_Logger::error( 'updater', 'GitHub request failed', [ 'error' => $response->get_error_message() ] );
            }
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            return;
        }

        $latest_tag = ltrim( $data['tag_name'], 'v' );
        $current = ltrim( CHURCHTOOLS_SUITE_VERSION, 'v' );

        if ( version_compare( $latest_tag, $current, '<=' ) ) {
            // up-to-date
            return;
        }

        // Determine zip URL (prefer release asset matching plugin zip, fallback to zipball_url)
        $zip_url = '';
        if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
            foreach ( $data['assets'] as $asset ) {
                if ( isset( $asset['name'] ) && strpos( $asset['name'], 'churchtools-suite' ) !== false && isset( $asset['browser_download_url'] ) ) {
                    $zip_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        if ( empty( $zip_url ) && ! empty( $data['zipball_url'] ) ) {
            $zip_url = $data['zipball_url'];
        }

        if ( empty( $zip_url ) ) {
            return;
        }

        // Perform update
        self::perform_update( $zip_url, $data['tag_name'] );
    }

    private static function perform_update( string $zip_url, string $tag ): void {
        if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
            ChurchTools_Suite_Logger::info( 'updater', sprintf( 'Updating to %s from %s', $tag, $zip_url ) );
        }

        $tmp_zip = sys_get_temp_dir() . '/cts_update_' . md5( $tag ) . '.zip';
        $tmp_dir = sys_get_temp_dir() . '/cts_update_' . md5( $tag );

        // Download
        $resp = wp_remote_get( $zip_url, [ 'timeout' => 60, 'headers' => [ 'User-Agent' => 'ChurchTools-Suite-Updater' ] ] );
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
