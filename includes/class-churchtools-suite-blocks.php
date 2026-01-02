<?php
/**
 * Gutenberg Blocks Registration
 * 
 * CLEAN REBUILD - Neue Implementation ohne alten Ballast
 * Ruft Shortcodes direkt und korrekt auf
 * 
 * @package ChurchTools_Suite
 * @since   0.5.9.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Blocks {
	
	/**
	 * Register all blocks
	 */
	public static function register(): void {
		// Register block category
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
		
		// Register blocks DIRECTLY - crucial for block editor to find them!
		// DO NOT use init hook here - blocks class is already called via init
		self::register_blocks();
		
		// Enqueue block editor assets
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_assets' ] );
		
		// Register REST API routes for block editor
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_routes' ] );
		
		// Add filter to normalize block attributes BEFORE rendering
		// This ensures old blocks (saved before v0.5.11.12) get the new attributes
		add_filter( 'render_block_data', [ __CLASS__, 'normalize_block_attributes' ], 10, 1 );
	}
	
	/**
	 * Register REST API routes
	 */
	public static function register_rest_routes(): void {
		register_rest_route( 'churchtools-suite/v1', '/calendars', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_calendars_for_editor' ],
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		] );
		
		register_rest_route( 'churchtools-suite/v1', '/presets', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_presets_for_editor' ],
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		] );
	}
	
	/**
	 * Get calendars for block editor
	 */
	public static function get_calendars_for_editor(): array {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$calendars = $calendars_repo->get_all();
		
		$options = [
			[ 'label' => __( 'Alle Kalender', 'churchtools-suite' ), 'value' => '' ]
		];
		
		foreach ( $calendars as $calendar ) {
			$options[] = [
				'label' => $calendar->name,
				'value' => (string) $calendar->calendar_id,
			];
		}
		
		return $options;
	}
	
	/**
	 * Get presets for block editor
	 */
	public static function get_presets_for_editor(): array {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		$all_presets = $presets_repo->get_all_presets();
		
		// Gruppiere nach Shortcode-Tag
		$grouped = [];
		foreach ( $all_presets as $preset ) {
			$tag = $preset['shortcode_tag'];
			
			// Map shortcode tags to view types
			$view_type = '';
			if ( $tag === 'cts_list' ) {
				$view_type = 'list';
			} elseif ( $tag === 'cts_calendar' ) {
				$view_type = 'calendar';
			} elseif ( $tag === 'cts_grid' ) {
				$view_type = 'grid';
			}
			
			if ( $view_type && isset( $preset['configuration']['view'] ) ) {
				if ( ! isset( $grouped[ $view_type ] ) ) {
					$grouped[ $view_type ] = [];
				}
				
				$grouped[ $view_type ][] = [
					'label' => $preset['name'] . ( $preset['is_system'] ? ' 🔒' : ' ⭐' ),
					'value' => $preset['configuration']['view'],
					'preset_id' => $preset['id'],
					'is_system' => $preset['is_system'],
				];
			}
		}
		
		return $grouped;
	}
	
	/**
	 * Normalize block attributes for backward compatibility
	 * 
	 * This filter is called BEFORE any block is rendered and ensures that
	 * old blocks (saved before v0.5.11.12) get the new boolean attributes.
	 * 
	 * This is the WordPress-native way to handle block migrations without
	 * needing complex deprecation APIs or database migrations.
	 * 
	 * @param array $parsed_block Parsed block data
	 * @return array Modified block data
	 * @since 0.5.11.14
	 */
	public static function normalize_block_attributes( $parsed_block ): array {
		// Only process our blocks
		if ( isset( $parsed_block['blockName'] ) && $parsed_block['blockName'] === 'churchtools-suite/events' ) {
			// Ensure attrs array exists
			if ( ! isset( $parsed_block['attrs'] ) ) {
				$parsed_block['attrs'] = [];
			}
			
			// Add missing boolean attributes with defaults
			// These were added in v0.5.11.12 and may be missing in old blocks
			if ( ! isset( $parsed_block['attrs']['show_services'] ) ) {
				$parsed_block['attrs']['show_services'] = true;
			}
			if ( ! isset( $parsed_block['attrs']['show_description'] ) ) {
				$parsed_block['attrs']['show_description'] = true;
			}
			if ( ! isset( $parsed_block['attrs']['show_location'] ) ) {
				$parsed_block['attrs']['show_location'] = true;
			}
			
			self::block_log( '🔄 Block attributes normalized: ' . json_encode( $parsed_block['attrs'] ) );
		}
		
		return $parsed_block;
	}
	
	/**
	 * Register ChurchTools block category
	 */
	public static function register_block_category( $categories, $post ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'churchtools-suite',
					'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
					'icon'  => 'calendar-alt',
				],
			]
		);
	}
	
	/**
	 * Register all Gutenberg blocks
	 */
	public static function register_blocks(): void {
		// DEBUG LOG: Block Registration Start
		self::block_log( '🔴 register_blocks() CALLED!' );
		
		// Check if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			self::block_log( '🔴 register_block_type() NOT AVAILABLE!' );
			return;
		}
		
		self::block_log( '🔴 Registering churchtools-events block...' );
		
		// Unified ChurchTools Events Block
		$result = register_block_type( 'churchtools-suite/events', [
			'api_version'     => 2,
			'title'           => __( 'ChurchTools Events', 'churchtools-suite' ),
			'description'     => __( 'Zeigt ChurchTools Events in verschiedenen Ansichten', 'churchtools-suite' ),
			'category'        => 'churchtools-suite',
			'icon'            => 'calendar-alt',
			'keywords'        => [ 'calendar', 'kalender', 'events', 'termine', 'list', 'grid' ],
			'supports'        => [
				'html' => false,
			],
			'attributes'      => [
				'viewType'         => [ 'type' => 'string', 'default' => 'list' ],
				'view'             => [ 'type' => 'string', 'default' => 'classic' ],
				'calendar'         => [ 'type' => 'string', 'default' => '' ],
				'limit'            => [ 'type' => 'number', 'default' => 5 ],
				'columns'          => [ 'type' => 'number', 'default' => 3 ],
				// Sprint 1: Anzeige-Parameter
				'enable_modal'     => [ 'type' => 'boolean', 'default' => true ],
				'show_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_location'    => [ 'type' => 'boolean', 'default' => true ],
				// Sprint 3: Weitere Anzeige-Parameter
				'show_services'    => [ 'type' => 'boolean', 'default' => true ],
				'show_calendar_name' => [ 'type' => 'boolean', 'default' => false ],
				'show_time'        => [ 'type' => 'boolean', 'default' => true ],
				// Sprint 4: Filter-Parameter
				'order'            => [ 'type' => 'string', 'default' => 'asc' ],
				'date_from'        => [ 'type' => 'string', 'default' => '' ],
				'date_to'          => [ 'type' => 'string', 'default' => '' ],
				// Live Preview Toggle
				'enableLivePreview' => [ 'type' => 'boolean', 'default' => false ],
			],
			'render_callback' => [ __CLASS__, 'render_events_block' ],
		] );
		
		$status = is_object( $result ) ? 'SUCCESS' : 'FAILED';
		self::block_log( '🔴 churchtools-events registered: ' . $status );
		
		// Store block status
		self::store_block_status( 'churchtools-suite/events', [
			'registered'      => is_object( $result ),
			'render_callback' => __CLASS__ . '::render_events_block',
		] );
	}
	
	/**
	 * Enqueue block editor assets
	 */
	public static function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			'churchtools-suite-blocks',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch' ],
			CHURCHTOOLS_SUITE_VERSION,
			false
		);
		
		// Force block re-registration by adding inline script
		wp_add_inline_script(
			'churchtools-suite-blocks',
			'console.log("ChurchTools Suite Blocks v' . CHURCHTOOLS_SUITE_VERSION . ' wird geladen...");',
			'before'
		);
	}
	
	/**
	 * Render Events Block (unified)
	 * 
	 * @param array $attributes Block attributes from Gutenberg
	 * @return string Rendered HTML
	 */
	public static function render_events_block( $attributes ): string {
		self::block_log( '🟢 render_events_block() CALLED!' );
		self::block_log( '🟢 Attributes: ' . json_encode( $attributes ) );
		
		// Update last render time
		self::update_render_time( 'churchtools-suite/events' );
		
		// Determine which shortcode to call based on viewType
		$view_type = $attributes['viewType'] ?? 'list';
		
		switch ( $view_type ) {
			case 'calendar':
				$output = ChurchTools_Suite_Shortcodes::calendar_shortcode( $attributes );
				break;
			case 'grid':
				$output = ChurchTools_Suite_Shortcodes::grid_shortcode( $attributes );
				break;
			case 'list':
			default:
				$output = ChurchTools_Suite_Shortcodes::list_shortcode( $attributes );
				break;
		}
		
		self::block_log( '🟢 Output length: ' . strlen( $output ) . ' bytes' );
		
		return $output;
	}
	
	/**
	 * Log block debug message
	 * 
	 * @param string $message Log message
	 */
	private static function block_log( string $message ): void {
		// Get existing logs
		$logs = get_option( 'churchtools_suite_block_debug_logs', [] );
		
		// Add new log entry
		$logs[] = [
			'time'    => time(),
			'message' => $message,
			'level'   => 'info',
		];
		
		// Keep only last 100 entries
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}
		
		// Save
		update_option( 'churchtools_suite_block_debug_logs', $logs, false );
	}
	
	/**
	 * Store block registration status
	 * 
	 * @param string $block_id Block identifier
	 * @param array  $status   Status data
	 */
	private static function store_block_status( string $block_id, array $status ): void {
		$all_status = get_option( 'churchtools_suite_block_status', [] );
		$all_status[ $block_id ] = $status;
		update_option( 'churchtools_suite_block_status', $all_status, false );
	}
	
	/**
	 * Update last render time for block
	 * 
	 * @param string $block_id Block identifier
	 */
	private static function update_render_time( string $block_id ): void {
		$all_status = get_option( 'churchtools_suite_block_status', [] );
		
		if ( isset( $all_status[ $block_id ] ) ) {
			$all_status[ $block_id ]['last_render'] = current_time( 'mysql' );
			update_option( 'churchtools_suite_block_status', $all_status, false );
		}
	}
}
