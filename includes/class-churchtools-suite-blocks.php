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
		// Register block category
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
		
		// Register blocks
		add_action( 'init', [ __CLASS__, 'register_blocks' ] );
		
		// Enqueue block editor assets
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_assets' ] );
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
	 * Register Gutenberg blocks
	 */
	public static function register_blocks(): void {
		// Check if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		
		// Register ChurchTools Events Block
		register_block_type( 'churchtools-suite/events', [
			'api_version' => 2,
			'attributes' => [
				'viewType' => [ 'type' => 'string', 'default' => 'list' ],
				'view' => [ 'type' => 'string', 'default' => 'classic' ],
				'limit' => [ 'type' => 'number', 'default' => 10 ],
				'calendar' => [ 'type' => 'string', 'default' => '' ],
				'show_event_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_appointment_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_location' => [ 'type' => 'boolean', 'default' => true ],
				'show_services' => [ 'type' => 'boolean', 'default' => false ],
				'show_time' => [ 'type' => 'boolean', 'default' => true ],
				'show_tags' => [ 'type' => 'boolean', 'default' => true ],
				'enable_modal' => [ 'type' => 'boolean', 'default' => true ],
			],
			'render_callback' => [ __CLASS__, 'render_events_block' ],
		] );
	}
	
	/**
	 * Render Events Block
	 *
	 * @param array $attributes Block attributes
	 * @return string Rendered HTML
	 */
	public static function render_events_block( $attributes ): string {
		// Convert boolean to string for shortcode compatibility
		$boolean_attrs = [
			'show_event_description',
			'show_appointment_description',
			'show_location',
			'show_services',
			'show_time',
			'show_tags',
			'enable_modal'
		];
		
		foreach ( $boolean_attrs as $attr ) {
			if ( isset( $attributes[ $attr ] ) ) {
				$attributes[ $attr ] = $attributes[ $attr ] ? 'true' : 'false';
			}
		}
		
		// Route to appropriate shortcode handler
		$view_type = ! empty( $attributes['viewType'] ) ? $attributes['viewType'] : 'list';
		
		// Only list view active for now
		if ( $view_type === 'list' ) {
			return ChurchTools_Suite_Shortcodes::list_shortcode( $attributes );
		}
		
		return '<p>' . __( 'Dieser Ansichtstyp ist derzeit deaktiviert.', 'churchtools-suite' ) . '</p>';
	}
	
	/**
	 * Enqueue block editor assets
	 */
	public static function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			'churchtools-suite-blocks',
			CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
			CHURCHTOOLS_SUITE_VERSION,
			true
		);
		
		// Add translations
		wp_set_script_translations( 'churchtools-suite-blocks', 'churchtools-suite' );
	}
}
