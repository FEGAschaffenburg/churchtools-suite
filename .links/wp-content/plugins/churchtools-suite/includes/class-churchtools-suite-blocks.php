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
		// CSS wird zentral via admin_enqueue_scripts geladen (v1.0.6.0)
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
			'api_version' => 3, // v1.1.4.4: Updated for iframe editor compatibility
			'category' => 'churchtools-suite',
			'supports' => [
				'html' => false,
				'customClassName' => true,
				'anchor' => true,
			],
			'attributes' => [
				'viewType' => [ 'type' => 'string', 'default' => 'list' ],
				'view' => [ 'type' => 'string', 'default' => 'classic' ], // Auto-switches to grid-classic when viewType=grid
				'limit' => [ 'type' => 'number', 'default' => 5 ],
				'columns' => [ 'type' => 'number', 'default' => 3 ],
				'slides_per_view' => [ 'type' => 'number', 'default' => 3 ], // Carousel
				'autoplay' => [ 'type' => 'boolean', 'default' => false ], // Carousel
				'autoplay_delay' => [ 'type' => 'number', 'default' => 5000 ], // Carousel
				'loop' => [ 'type' => 'boolean', 'default' => true ], // Carousel
				'calendars' => [ 'type' => 'string', 'default' => '' ],
			'tags' => [ 'type' => 'string', 'default' => '' ],
			'event_id' => [ 'type' => 'number', 'default' => 0 ], // v1.1.3.0: Countdown - spezifischer Event
			'show_event_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_appointment_description' => [ 'type' => 'boolean', 'default' => true ],
				'show_location' => [ 'type' => 'boolean', 'default' => true ],
				'show_services' => [ 'type' => 'boolean', 'default' => false ],
				'show_time' => [ 'type' => 'boolean', 'default' => true ],
				'show_tags' => [ 'type' => 'boolean', 'default' => true ],
				'show_calendar_name' => [ 'type' => 'boolean', 'default' => true ],
				'show_filter' => [ 'type' => 'boolean', 'default' => false ],
				'show_images' => [ 'type' => 'boolean', 'default' => true ],
				'image_fit' => [ 'type' => 'string', 'default' => 'cover' ],
				'hero_title_font_size' => [ 'type' => 'number', 'default' => 0 ],
				'hero_layout_preset' => [ 'type' => 'string', 'default' => 'standard' ],
				'hero_mobile_optimize' => [ 'type' => 'boolean', 'default' => true ],
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
			// editor_style nicht nötig - CSS wird zentral via admin_enqueue_scripts geladen (v1.0.6.0)
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
		// Ensure services toggle is interpreted robustly across editor/runtime variants.
		$attributes['show_services'] = self::normalize_bool_attribute( $attributes, 'show_services', 'showServices', false );
		$attributes['show_filter'] = self::normalize_bool_attribute( $attributes, 'show_filter', 'showFilter', false );
		
		// v1.1.2.0: Convert Block attributes (camelCase) to Shortcode params (snake_case)
		// Route to appropriate shortcode handler
		$view_type = ! empty( $attributes['viewType'] ) ? $attributes['viewType'] : 'list';
		
		// Convert camelCase Block attributes to snake_case Shortcode params
		// Blocks use: viewType, showPastEvents, etc.
		// Shortcodes expect: view_type, show_past_events, etc.
		// BUT: Most display options already use snake_case in both (show_event_description)
		
		// Ensure all boolean/number values are properly typed
		// (Block editor already sends correct types, but ensure compatibility)
		
		// v1.1.2.0: All view types active (List, Grid, Calendar, Countdown)
		if ( $view_type === 'list' ) {
			return ChurchTools_Suite_Shortcodes::list_shortcode( $attributes );
		}
		
		if ( $view_type === 'grid' ) {
			return ChurchTools_Suite_Shortcodes::grid_shortcode( $attributes );
		}
		
		if ( $view_type === 'calendar' ) {
			return ChurchTools_Suite_Shortcodes::calendar_shortcode( $attributes );
		}
		
		if ( $view_type === 'countdown' ) {
			return ChurchTools_Suite_Shortcodes::countdown_shortcode( $attributes );
		}
		
		// v1.1.3.0: Carousel Views
		if ( $view_type === 'carousel' ) {
			return ChurchTools_Suite_Shortcodes::carousel_shortcode( $attributes );
		}
		
		return '<p>' . __( 'Dieser Ansichtstyp ist derzeit deaktiviert.', 'churchtools-suite' ) . '</p>';
	}

	/**
	 * Normalize boolean block attributes with optional fallback key.
	 *
	 * @param array<string,mixed> $attributes
	 */
	private static function normalize_bool_attribute( array $attributes, string $primary_key, string $fallback_key = '', bool $default = false ): bool {
		$value = $default;

		if ( array_key_exists( $primary_key, $attributes ) ) {
			$value = $attributes[ $primary_key ];
		} elseif ( $fallback_key !== '' && array_key_exists( $fallback_key, $attributes ) ) {
			$value = $attributes[ $fallback_key ];
		}

		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (int) $value !== 0;
		}

		if ( is_string( $value ) ) {
			$normalized = strtolower( trim( $value ) );
			if ( in_array( $normalized, [ '1', 'true', 'yes', 'on' ], true ) ) {
				return true;
			}
			if ( in_array( $normalized, [ '0', 'false', 'no', 'off', '' ], true ) ) {
				return false;
			}
		}

		return $default;
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
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/view-feature-matrix.php';
		
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
		
		// v1.1.3.0: Load upcoming events for Countdown selector
		$events_repo = new ChurchTools_Suite_Events_Repository();
		$upcoming_events = $events_repo->get_upcoming( 50 ); // Next 50 events
		
		$event_options = [
			[ 'label' => __( 'Nächster Event (automatisch)', 'churchtools-suite' ), 'value' => 0, 'calendar_id' => '', 'tags' => [] ]
		];
		
		if ( ! empty( $upcoming_events ) ) {
			foreach ( $upcoming_events as $event ) {
				$date_format = get_option( 'date_format', 'd.m.Y' );
				$event_date = '';
				
				if ( ! empty( $event->start_datetime ) ) {
					$event_date = ' (' . get_date_from_gmt( $event->start_datetime, $date_format ) . ')';
				}
				
				// Extract tag IDs from tags JSON
				$tag_ids = [];
				if ( ! empty( $event->tags ) ) {
					$tags = json_decode( $event->tags, true );
					if ( is_array( $tags ) ) {
						foreach ( $tags as $tag ) {
							if ( isset( $tag['id'] ) ) {
								$tag_ids[] = (string) $tag['id'];
							}
						}
					}
				}
				
				$event_options[] = [
					'label' => $event->title . $event_date,
					'value' => (int) ( $event->event_id ?: $event->appointment_id ),
					'calendar_id' => (string) $event->calendar_id,
					'tags' => $tag_ids,
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
			'countdown' => ChurchTools_Suite_Template_Loader::get_view_options( 'countdown' ),
			];

		// Pass to editor
		wp_localize_script( 'churchtools-suite-blocks', 'churchtoolsSuiteBlocks', [
			'calendars' => $calendar_options,
			'tags' => $tag_options,
			'events' => $event_options, // v1.1.3.0: Event selector for Countdown
			'viewTypes' => $view_types,
			'views' => $views_map,
			'viewFeatures' => churchtools_suite_get_view_features(), // Feature matrix for conditional toggles
		] );
	}
}
