<?php
/**
 * Gutenberg Blocks
 * 
 * CLEAN SLATE v1.0.0 - Complete Rewrite
 * Minimal, focused, maintainable
 * 
 * @package ChurchTools_Suite
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Blocks {
	
	/**
	 * Register all blocks
	 */
	public static function register(): void {
		// Check if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		
		// Register block category
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
		
		// Register block editor script FIRST (v0.9.9.5)
		wp_register_script(
			'churchtools-suite-blocks',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ],
			CHURCHTOOLS_SUITE_VERSION,
			false // Load in header, not footer
		);
		
		// Add translations
		wp_set_script_translations( 'churchtools-suite-blocks', 'churchtools-suite' );
		
		// v0.9.9.94: Load calendars and tags for filter dropdowns
		self::localize_block_data();
		
		// Register ChurchTools Events Block
		register_block_type( 'churchtools-suite/events', [
			'api_version' => 2,
			'category' => 'churchtools-suite',
			'supports' => [
				'html' => false,
				'customClassName' => true,
				'anchor' => true,
			],
			'attributes' => [
				'viewType' => [ 'type' => 'string', 'default' => 'list' ],
				'view' => [ 'type' => 'string', 'default' => 'classic' ],
				'limit' => [ 'type' => 'number', 'default' => 5 ],
				'columns' => [ 'type' => 'number', 'default' => 3 ],
			'calendars' => [ 'type' => 'string', 'default' => '' ],
			'tags' => [ 'type' => 'string', 'default' => '' ],
				'show_event_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_appointment_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_location' => [ 'type' => 'boolean', 'default' => true ],
				'show_services' => [ 'type' => 'boolean', 'default' => false ],
				'show_time' => [ 'type' => 'boolean', 'default' => true ],
				'show_tags' => [ 'type' => 'boolean', 'default' => true ],
				'show_calendar_name' => [ 'type' => 'boolean', 'default' => true ],
				'show_images' => [ 'type' => 'boolean', 'default' => true ],
				'show_month_separator' => [ 'type' => 'boolean', 'default' => true ],
				'show_past_events' => [ 'type' => 'boolean', 'default' => false ],
				'event_action' => [ 'type' => 'string', 'default' => 'modal' ],
				'style_mode' => [ 'type' => 'string', 'default' => 'theme' ],
				'use_calendar_colors' => [ 'type' => 'boolean', 'default' => false ],
				'custom_primary_color' => [ 'type' => 'string', 'default' => '#2563eb' ],
				'custom_text_color' => [ 'type' => 'string', 'default' => '#1e293b' ],
				'custom_background_color' => [ 'type' => 'string', 'default' => '#ffffff' ],
				'custom_border_radius' => [ 'type' => 'number', 'default' => 6 ],
				'custom_font_size' => [ 'type' => 'number', 'default' => 14 ],
				'custom_padding' => [ 'type' => 'number', 'default' => 12 ],
				'custom_spacing' => [ 'type' => 'number', 'default' => 8 ],
			],
			'render_callback' => [ __CLASS__, 'render_events_block' ],
			'editor_script' => 'churchtools-suite-blocks', // v0.9.9.5: Explizite Verknüpfung mit JS
		] );
	}
	
	/**
	 * Register block category
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
	 * Render Events Block
	 *
	 * @param array $attributes Block attributes
	 * @return string Rendered HTML
	 */
	public static function render_events_block( $attributes ): string {
		// v0.9.6.8: No conversion needed - attributes are already correct type
		// Boolean attributes stay boolean, numbers stay numbers, strings stay strings
		
		// Route to appropriate shortcode handler
		$view_type = ! empty( $attributes['viewType'] ) ? $attributes['viewType'] : 'list';
		
		// v0.9.9.0: List, Grid and Calendar views active
		if ( $view_type === 'list' ) {
			return ChurchTools_Suite_Shortcodes::list_shortcode( $attributes );
		}
		
		if ( $view_type === 'grid' ) {
			return ChurchTools_Suite_Shortcodes::grid_shortcode( $attributes );
		}
		
		if ( $view_type === 'calendar' ) {
			return ChurchTools_Suite_Shortcodes::calendar_shortcode( $attributes );
		}
		
		return '<p>' . __( 'Dieser Ansichtstyp ist derzeit deaktiviert.', 'churchtools-suite' ) . '</p>';
	}
	
	/**
	 * Localize block data - pass calendars and tags to editor
	 * 
	 * @since 0.9.9.94
	 */
	private static function localize_block_data(): void {
		// Load repositories
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
		
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$all_calendars = $calendars_repo->get_all();
		
		// Format calendars for dropdown
		$calendar_options = [];
		if ( ! empty( $all_calendars ) ) {
			foreach ( $all_calendars as $calendar ) {
				$calendar_options[] = [
					'label' => $calendar->name,
					'value' => $calendar->calendar_id,
				];
			}
		}
		
		// Get unique tags from events
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'events';
		
		$results = $wpdb->get_results(
			"SELECT DISTINCT tags FROM {$table} WHERE tags IS NOT NULL AND tags != ''",
			ARRAY_A
		);
		
		// Extract and merge all tags
		$all_tags = [];
		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$tags = json_decode( $row['tags'], true );
				if ( is_array( $tags ) ) {
					foreach ( $tags as $tag ) {
						if ( isset( $tag['id'] ) && isset( $tag['name'] ) ) {
							$all_tags[ $tag['id'] ] = $tag['name'];
						}
					}
				}
			}
		}
		
		// Format tags for dropdown
		$tag_options = [];
		foreach ( $all_tags as $id => $name ) {
			$tag_options[] = [
				'label' => $name,
				'value' => (string) $id,
			];
		}
		
		// Sort by name
		usort( $tag_options, function( $a, $b ) {
			return strcmp( $a['label'], $b['label'] );
		} );
		
		// View registry (Single Source of Truth)
		$view_types = ChurchTools_Suite_Template_Loader::get_view_types_options();
		$views_map = [
			'list' => ChurchTools_Suite_Template_Loader::get_view_options( 'list' ),
			'grid' => ChurchTools_Suite_Template_Loader::get_view_options( 'grid' ),
			'calendar' => ChurchTools_Suite_Template_Loader::get_view_options( 'calendar' ),
		];

		// Pass to editor
		wp_localize_script( 'churchtools-suite-blocks', 'churchtoolsSuiteBlocks', [
			'calendars' => $calendar_options,
			'tags' => $tag_options,
			'viewTypes' => $view_types,
			'views' => $views_map,
		] );
	}
}
