<?php
/**
 * Plugin Name: ChurchTools Suite – Posts Sync Addon
 * Plugin URI: https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-posts-sync
 * Description: Synchronisiert ChurchTools-Posts in WordPress-Posts/Seiten. Benötigt ChurchTools Suite v1.2.0.0+
 * Version: 0.1.7
 * Author: FEG Aschaffenburg
 * Author URI: https://www.feg-aschaffenburg.de
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-license.html
 * Text Domain: churchtools-suite-posts-sync
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * 
 * @package churchtools_suite_posts_sync
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'CTS_POSTS_SYNC_VERSION', '0.1.7' );
define( 'CTS_POSTS_SYNC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTS_POSTS_SYNC_URL', plugin_dir_url( __FILE__ ) );
define( 'CTS_POSTS_SYNC_BASENAME', plugin_basename( __FILE__ ) );
define( 'CTS_POSTS_SYNC_CPT', 'ct_post' );

require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-auto-updater.php';
CTS_Posts_Sync_Auto_Updater::init();

class ChurchTools_Suite_Posts_Sync {

	/**
	 * Returns the supported target post types.
	 *
	 * @return array<int, string>
	 */
	public static function get_supported_target_types(): array {
		return [ 'post', 'page', CTS_POSTS_SYNC_CPT ];
	}

	/**
	 * Register custom post type for ChurchTools posts.
	 */
	public static function register_post_type(): void {
		$labels = [
			'name' => __( 'ChurchTools Berichte', 'churchtools-suite-posts-sync' ),
			'singular_name' => __( 'ChurchTools Bericht', 'churchtools-suite-posts-sync' ),
			'menu_name' => __( 'ChurchTools Berichte', 'churchtools-suite-posts-sync' ),
			'add_new' => __( 'Neu hinzufügen', 'churchtools-suite-posts-sync' ),
			'add_new_item' => __( 'Neuen ChurchTools Bericht hinzufügen', 'churchtools-suite-posts-sync' ),
			'edit_item' => __( 'ChurchTools Bericht bearbeiten', 'churchtools-suite-posts-sync' ),
			'new_item' => __( 'Neuer ChurchTools Bericht', 'churchtools-suite-posts-sync' ),
			'view_item' => __( 'ChurchTools Bericht ansehen', 'churchtools-suite-posts-sync' ),
			'search_items' => __( 'ChurchTools Berichte durchsuchen', 'churchtools-suite-posts-sync' ),
			'not_found' => __( 'Keine ChurchTools Berichte gefunden.', 'churchtools-suite-posts-sync' ),
			'not_found_in_trash' => __( 'Keine ChurchTools Berichte im Papierkorb gefunden.', 'churchtools-suite-posts-sync' ),
		];

		register_post_type(
			CTS_POSTS_SYNC_CPT,
			[
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'show_in_admin_bar' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => false,
				'rewrite' => false,
				'query_var' => false,
				'map_meta_cap' => true,
				'taxonomies' => [ 'category' ],
				'supports' => [ 'title', 'editor', 'excerpt', 'author', 'thumbnail' ],
			]
		);
	}

	/**
	 * Initialize the addon
	 */
	public static function init() {
		// Check if main plugin is active
		if ( ! self::is_main_plugin_active() ) {
			add_action( 'admin_notices', [ __CLASS__, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Load text domain
		load_plugin_textdomain(
			'churchtools-suite-posts-sync',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Register hooks
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'cts_do_sync_posts', [ __CLASS__, 'handle_sync_posts' ], 10, 2 );
		add_filter( 'cts_register_sync_modules', [ __CLASS__, 'register_sync_module' ] );

		require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-frontend.php';
		ChurchTools_Suite_Posts_Sync_Frontend::init();

		// Initialize admin if applicable
		if ( is_admin() ) {
			require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-service.php';
			require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-admin.php';

			$admin = new ChurchTools_Suite_Posts_Sync_Admin();
			$admin->init();
		}
	}

	/**
	 * Register this addon as sync module in the core registry contract.
	 *
	 * @param array<int|string, array<string, mixed>> $modules Existing modules.
	 * @return array<int|string, array<string, mixed>>
	 */
	public static function register_sync_module( array $modules ): array {
		$modules['posts'] = [
			'id' => 'posts',
			'label' => __( 'Berichte', 'churchtools-suite-posts-sync' ),
			'capability' => 'manage_churchtools_suite',
			'dependencies' => [ 'ct_connection' ],
			'callbacks' => [
				'render_settings' => [ __CLASS__, 'module_render_settings' ],
				'save_settings' => [ __CLASS__, 'module_save_settings' ],
				'run_source_sync' => [ __CLASS__, 'module_run_source_sync' ],
				'run_data_sync' => [ __CLASS__, 'module_run_data_sync' ],
				'get_status' => [ __CLASS__, 'module_get_status' ],
			],
			'meta' => [
				'owner' => 'addon:churchtools-suite-posts-sync',
				'settings_slug' => 'posts',
			],
		];

		return $modules;
	}

	/**
	 * Contract callback: render module settings UI.
	 */
	public static function module_render_settings(): void {
		do_action( 'cts_posts_settings_render' );
	}

	/**
	 * Contract callback: save module settings.
	 *
	 * @param array<string, mixed> $post_data Raw post data.
	 */
	public static function module_save_settings( array $post_data = [] ): void {
		do_action( 'cts_posts_settings_save', $post_data );
	}

	/**
	 * Contract callback: run source sync (groups cache).
	 *
	 * @return array<string, mixed>
	 */
	public static function module_run_source_sync(): array {
		$runner = static function (): array {
			if ( ! is_admin() ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-admin.php';

			$admin = new ChurchTools_Suite_Posts_Sync_Admin();
			return $admin->run_groups_source_sync();
		};

		if ( class_exists( 'ChurchTools_Suite_Sync_Runtime' ) ) {
			$result = ChurchTools_Suite_Sync_Runtime::run_locked_action( 'posts', 'source_sync', $runner );
			if ( is_array( $result ) ) {
				return $result;
			}
			if ( is_wp_error( $result ) ) {
				return [
					'status' => 'error',
					'message' => $result->get_error_message(),
				];
			}
		}

		return $runner();
	}

	/**
	 * Contract callback: run data sync.
	 *
	 * @param object $ct_client ChurchTools client instance.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function module_run_data_sync( $ct_client ) {
		if ( ! self::is_enabled() ) {
			$disabled_result = [
				'status' => 'skipped',
				'message' => __( 'Berichte-Sync ist deaktiviert.', 'churchtools-suite-posts-sync' ),
			];

			if ( class_exists( 'ChurchTools_Suite_Sync_Runtime' ) ) {
				ChurchTools_Suite_Sync_Runtime::record_result( 'posts', 'data_sync', $disabled_result );
			}

			return $disabled_result;
		}

		$runner = static function () use ( $ct_client ) {
			require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-service.php';

			$service = new ChurchTools_Suite_Posts_Sync_Service( $ct_client );
			return $service->sync_posts();
		};

		if ( class_exists( 'ChurchTools_Suite_Sync_Runtime' ) ) {
			return ChurchTools_Suite_Sync_Runtime::run_locked_action( 'posts', 'data_sync', $runner );
		}

		return $runner();
	}

	/**
	 * Contract callback: get module status snapshot.
	 *
	 * @return array<string, mixed>
	 */
	public static function module_get_status(): array {
		$status = class_exists( 'ChurchTools_Suite_Sync_Runtime' )
			? ChurchTools_Suite_Sync_Runtime::get_module_status( 'posts' )
			: [];

		$last_result = get_option( 'churchtools_suite_posts_sync_last_result', [] );
		$groups_last_sync = (string) get_option( 'churchtools_suite_ct_posts_groups_last_sync', '' );
		$enabled = self::is_enabled();

		$state = isset( $status['state'] ) ? (string) $status['state'] : 'idle';
		if ( ! $enabled ) {
			$state = 'disabled';
		} elseif ( $state === 'idle' && is_array( $last_result ) ) {
			$stats = isset( $last_result['stats'] ) && is_array( $last_result['stats'] ) ? $last_result['stats'] : [];
			$errors = isset( $stats['errors'] ) ? (int) $stats['errors'] : 0;
			$state = $errors > 0 ? 'error' : 'ok';
		}

		$status_last_result = isset( $status['last_result'] ) && is_array( $status['last_result'] ) ? $status['last_result'] : [];

		return [
			'state' => $state,
			'enabled' => $enabled,
			'last_source_sync_at' => $groups_last_sync !== '' ? $groups_last_sync : (string) ( $status['last_source_sync_at'] ?? '' ),
			'last_selection_save_at' => (string) ( $status['last_selection_save_at'] ?? '' ),
			'last_data_sync_at' => (string) ( $status['last_data_sync_at'] ?? ( is_array( $last_result ) ? (string) ( $last_result['run_at'] ?? '' ) : '' ) ),
			'last_result' => $status_last_result !== [] ? $status_last_result : ( is_array( $last_result ) ? $last_result : [] ),
		];
	}

	/**
	 * Check if ChurchTools Suite main plugin is active
	 */
	private static function is_main_plugin_active() {
		if ( class_exists( 'ChurchTools_Suite' ) || defined( 'CHURCHTOOLS_SUITE_VERSION' ) ) {
			return true;
		}

		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( 'churchtools-suite/churchtools-suite.php' );
		}

		return false;
	}

	/**
	 * Deactivate addon outside allowed environments and show notice.
	 *
	 * @deprecated 0.1.6 Umgebungseinschränkung entfernt – Plugin ist jetzt für alle Umgebungen freigegeben.
	 */
	private static function deactivate_if_not_allowed_environment() {
		// No longer used – kept for backward compatibility only
	}

	/**
	 * Show admin notice if main plugin is missing
	 */
	public static function admin_notice_missing_main_plugin() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong><?php esc_html_e( 'ChurchTools Suite – Posts Sync:', 'churchtools-suite-posts-sync' ); ?></strong>
				<?php esc_html_e( 'Dieses Addon benötigt ChurchTools Suite v1.1.5.0+. Bitte installieren und aktivieren Sie das Hauptplugin.', 'churchtools-suite-posts-sync' ); ?>
			</p>
		</div>
		<?php
	}



	/**
	 * Handle the sync_posts hook from main plugin
	 * 
	 * @param object $ct_client ChurchTools_Suite_CT_Client instance
	 * @param array  $result    Sync result array (by reference)
	 */
	public static function handle_sync_posts( $ct_client, &$result ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-service.php';

		$service = new ChurchTools_Suite_Posts_Sync_Service( $ct_client );
		$post_sync_result = $service->sync_posts();

		if ( is_wp_error( $post_sync_result ) ) {
			$result['ct_posts_error'] = $post_sync_result->get_error_message();
		} elseif ( is_array( $post_sync_result ) ) {
			$result['ct_posts_found'] = $post_sync_result['posts_found'] ?? 0;
			$result['ct_posts_created'] = $post_sync_result['posts_created'] ?? 0;
			$result['ct_posts_updated'] = $post_sync_result['posts_updated'] ?? 0;
			$result['ct_posts_skipped'] = $post_sync_result['posts_skipped'] ?? 0;
			$result['ct_posts_errors'] = $post_sync_result['errors'] ?? 0;
		}
	}

	/**
	 * Check if Posts Sync is enabled
	 */
	private static function is_enabled() {
		return (bool) get_option( 'churchtools_suite_ct_posts_sync_enabled', 0 );
	}
}

// Initialize addon on plugins_loaded
add_action( 'plugins_loaded', [ 'ChurchTools_Suite_Posts_Sync', 'init' ] );
