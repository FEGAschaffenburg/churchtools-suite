<?php
/**
 * Shortcode Handler
 * 
 * Handles all frontend shortcodes for displaying events in various views.
 * Supports all view types from ROADMAP v0.5.0.0
 *
 * @package ChurchTools_Suite
 * @since   0.5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Shortcodes {
	
	/**
	 * Template Loader instance
	 *
	 * @var ChurchTools_Suite_Template_Loader
	 */
	private static $template_loader;
	
	/**
	 * Data Provider instance
	 *
	 * @var ChurchTools_Suite_Template_Data
	 */
	private static $data_provider;
	
	/**
	 * Track if modal template is loaded
	 *
	 * @var bool
	 */
	private static $modal_loaded = false;
	
	/**
	 * Register all shortcodes
	 * 
	 * Called by main plugin class
	 */
	public static function register(): void {
		// Add modal template to footer
		add_action( 'wp_footer', [ __CLASS__, 'add_modal_template' ] );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_cts_load_calendar_month', [ __CLASS__, 'ajax_load_calendar_month' ] );
		add_action( 'wp_ajax_nopriv_cts_load_calendar_month', [ __CLASS__, 'ajax_load_calendar_month' ] );
		
		// DEBUG: Test shortcode
		add_shortcode( 'cts_debug_test', [ __CLASS__, 'debug_test_shortcode' ] );
		
		// Calendar Views
		add_shortcode( 'cts_calendar', [ __CLASS__, 'calendar_shortcode' ] );
		
		// List Views
		add_shortcode( 'cts_list', [ __CLASS__, 'list_shortcode' ] );
		
		// Grid Views
		add_shortcode( 'cts_grid', [ __CLASS__, 'grid_shortcode' ] );
		
		// Modal Single Event
		add_shortcode( 'cts_modal', [ __CLASS__, 'modal_shortcode' ] );
		
		// Slider Views
		add_shortcode( 'cts_slider', [ __CLASS__, 'slider_shortcode' ] );
		
		// Countdown Views
		add_shortcode( 'cts_countdown', [ __CLASS__, 'countdown_shortcode' ] );
		
		// Cover Views
		add_shortcode( 'cts_cover', [ __CLASS__, 'cover_shortcode' ] );
		
		// Timetable Views
		add_shortcode( 'cts_timetable', [ __CLASS__, 'timetable_shortcode' ] );
		
		// Carousel Views
		add_shortcode( 'cts_carousel', [ __CLASS__, 'carousel_shortcode' ] );
		
		// Single Event
		add_shortcode( 'cts_single', [ __CLASS__, 'single_shortcode' ] );
		
		// Map Views
		add_shortcode( 'cts_map', [ __CLASS__, 'map_shortcode' ] );
		
		// Search
		add_shortcode( 'cts_search', [ __CLASS__, 'search_shortcode' ] );
		
		// Widgets
		add_shortcode( 'cts_widget', [ __CLASS__, 'widget_shortcode' ] );
		
		// Legacy compatibility
		add_shortcode( 'cts_events', [ __CLASS__, 'legacy_events_shortcode' ] );
	}
	
	/**
	 * Load preset configuration if view parameter is a preset slug
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $shortcode_tag Shortcode tag (e.g., 'cts_list')
	 * @return array Modified attributes with preset configuration applied, includes '_preset_base_view' key
	 */
	private static function apply_preset_config( array $atts, string $shortcode_tag ): array {
		// Check if view looks like a preset slug (not a standard view)
		$view = $atts['view'] ?? '';
		
		// Standard views we know about - skip preset lookup for these
		$standard_views = [
			// List
			'classic', 'standard', 'modern', 'minimal', 'toggle', 'with-map', 'fluent', 
			'large-liquid', 'medium-liquid', 'small-liquid', 'medium',
			// Calendar
			'monthly-modern', 'monthly-clean', 'monthly-classic', 'weekly-fluent', 
			'weekly-liquid', 'yearly', 'daily', 'daily-liquid',
			// Grid
			'simple', 'ocean', 'colorful', 'novel', 'tile', 'large-liquid', 
			'medium-liquid', 'small-liquid', 'with-map',
		];
		
		// If it's a standard view, no preset lookup needed
		if ( in_array( $view, $standard_views, true ) ) {
			return $atts;
		}
		
		// Try to load preset by view slug
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		$all_presets = $presets_repo->get_all_presets();
		
		// Find preset by view slug and shortcode tag
		$preset = null;
		foreach ( $all_presets as $p ) {
			if ( $p['shortcode_tag'] === $shortcode_tag && 
			     isset( $p['configuration']['view'] ) && 
			     $p['configuration']['view'] === $view ) {
				$preset = $p;
				break;
			}
		}
		
		// If preset found, merge its configuration with attributes
		if ( $preset && isset( $preset['configuration'] ) ) {
			// Use stored base view from preset configuration
			$base_view = $preset['configuration']['_base_view'] ?? null;
			
			// If no base view stored (legacy preset), try to infer from shortcode tag
			if ( ! $base_view ) {
				switch ( $shortcode_tag ) {
					case 'cts_list':
						$base_view = 'classic';
						break;
					case 'cts_calendar':
						$base_view = 'monthly-modern';
						break;
					case 'cts_grid':
						$base_view = 'simple';
						break;
				}
			}
			
			$atts['_preset_base_view'] = $base_view;
			
			// Preset config has ALWAYS priority over shortcode parameters
			foreach ( $preset['configuration'] as $key => $value ) {
				// Skip internal keys that start with underscore
				if ( strpos( $key, '_' ) === 0 ) {
					continue;
				}
				
				// Skip 'view' key as it's the preset slug
				if ( $key === 'view' ) {
					continue;
				}
				
				// Preset parameters ALWAYS override shortcode parameters
				$atts[ $key ] = $value;
			}
		}
		
		return $atts;
	}
	
	/**
	 * Parse boolean value from string
	 * 
	 * Converts various string representations to actual boolean
	 * 
	 * @param mixed $value Value to parse
	 * @return bool Parsed boolean value
	 */
	public static function parse_boolean( $value ): bool {
		// Already boolean
		if ( is_bool( $value ) ) {
			return $value;
		}
		
		// String representations
		if ( is_string( $value ) ) {
			$value = strtolower( trim( $value ) );
			return in_array( $value, [ 'true', '1', 'yes', 'on' ], true );
		}
		
		// Numeric
		return (bool) $value;
	}
	
	/**
	 * Calendar Shortcode
	 * 
	 * Usage:
	 * [cts_calendar view="monthly-modern" show_time="true" show_description="true"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function calendar_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'monthly-modern',
			'calendar' => '',
			'limit' => 100,
			'from' => '',
			'to' => '',
			'class' => '',
			// Tooltip Display Options (v0.10.3.26)
			'show_time' => true,
			'show_description' => false,
			'show_location' => false,
			'show_services' => false,
			'show_calendar_name' => false,
		], $atts, 'cts_calendar' );
		
		// Apply preset configuration if view is a preset slug
		$atts = self::apply_preset_config( $atts, 'cts_calendar' );
		
		// Convert string boolean values to actual booleans
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		$atts['show_description'] = self::parse_boolean( $atts['show_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		
		// Get events
		$events = self::get_events( $atts );
		
		// Build template data
		$data = [
			'events' => $events,
			'atts' => $atts,
			'view' => $atts['view'],
		];
		
		// Use base view for template if preset
		$template_view = isset( $atts['_preset_base_view'] ) ? $atts['_preset_base_view'] : $atts['view'];
		
		// Render template
		ob_start();
		ChurchTools_Suite_Template_Loader::render_template( 'calendar/' . $template_view . '.php', $data );
		$output = ob_get_clean();
		
		// Wrap with optional CSS class
		if ( ! empty( $atts['class'] ) ) {
			$output = '<div class="' . esc_attr( $atts['class'] ) . '">' . $output . '</div>';
		}
		
		return $output;
	}
	
	/**
	 * List Shortcode
	 * 
	 * Usage:
	 * [cts_list view="classic"]
	 * [cts_list view="modern" limit="10"]
	 * [cts_list view="with-map" calendar="2"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function list_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'classic',
			'calendar' => '',
			'limit' => 5,
			'from' => '',
			'to' => '',
			'class' => '',
			// Sprint 1: Anzeige-Parameter
			'show_description' => true,
			'show_location' => true,
			// Sprint 3: Weitere Anzeige-Parameter
			'show_services' => true,
			'show_calendar_name' => false,
			'show_time' => true,
			// Sprint 4: Filter-Parameter
			'order' => 'asc',
			'date_from' => '',
			'date_to' => '',
		], $atts, 'cts_list' );
		
		// Convert string boolean values to actual booleans
		$atts['show_description'] = self::parse_boolean( $atts['show_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		
		// Validate order (asc/desc)
		if ( ! in_array( $atts['order'], [ 'asc', 'desc' ], true ) ) {
			$atts['order'] = 'asc';
		}
		
		// Apply preset configuration if view is a preset slug
		$atts = self::apply_preset_config( $atts, 'cts_list' );
		
		$events = self::get_events( $atts );
		
		// Use base view for template if preset
		$template_view = isset( $atts['_preset_base_view'] ) ? $atts['_preset_base_view'] : $atts['view'];
		
		return self::render_template( "list/{$template_view}", $events, $atts );
	}
	
	/**
	 * Grid Shortcode
	 * 
	 * Usage:
	 * [cts_grid view="simple" columns="3"]
	 * [cts_grid view="modern" columns="4" calendar="2,3"]
	 * [cts_grid view="colorful" limit="12"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function grid_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'simple',
			'columns' => 3,
			'calendar' => '',
			'limit' => 20,
			'from' => '',
			'to' => '',
			'class' => '',
			// Sprint 1: Anzeige-Parameter
			'show_description' => true,
			'show_location' => true,
			// Sprint 3: Weitere Anzeige-Parameter
			'show_services' => true,
			'show_calendar_name' => false,
			'show_time' => true,
			// Sprint 4: Filter-Parameter
			'order' => 'asc',
			'date_from' => '',
			'date_to' => '',
		], $atts, 'cts_grid' );
		
		// Validate columns (1-4)
		$atts['columns'] = absint( $atts['columns'] );
		if ( $atts['columns'] < 1 || $atts['columns'] > 4 ) {
			$atts['columns'] = 3;
		}
		
		// Convert string boolean values to actual booleans
		$atts['show_description'] = self::parse_boolean( $atts['show_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		
		// Validate order (asc/desc)
		if ( ! in_array( $atts['order'], [ 'asc', 'desc' ], true ) ) {
			$atts['order'] = 'asc';
		}
		
		// Apply preset configuration if view is a preset slug
		$atts = self::apply_preset_config( $atts, 'cts_grid' );
		
		$events = self::get_events( $atts );
		
		// Use base view for template if preset
		$template_view = isset( $atts['_preset_base_view'] ) ? $atts['_preset_base_view'] : $atts['view'];
		
		return self::render_template( "grid/{$template_view}", $events, $atts );
	}
	
	/**
	 * Modal Shortcode
	 * 
	 * Usage:
	 * [cts_modal id="2026"]
	 * [cts_modal event_id="2026" view="full-calendar"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function modal_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'id' => 0,
			'event_id' => '',
			'view' => 'single-event',
			'class' => '',
		], $atts, 'cts_modal' );
		
		// Get single event by ID
		$events = self::get_event_by_id( $atts['id'] ?: $atts['event_id'] );
		
		return self::render_template( "modal/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Slider Shortcode
	 * 
	 * Usage:
	 * [cts_slider view="type-1" limit="5"]
	 * [cts_slider view="type-3" autoplay="true"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function slider_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'type-1',
			'calendar' => '',
			'limit' => 5,
			'autoplay' => false,
			'interval' => 5000,
			'class' => '',
		], $atts, 'cts_slider' );
		
		$events = self::get_events( $atts );
		
		return self::render_template( "slider/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Countdown Shortcode
	 * 
	 * Usage:
	 * [cts_countdown view="type-1"]
	 * [cts_countdown view="type-2" event_id="2026"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function countdown_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'type-1',
			'event_id' => '',
			'calendar' => '',
			'class' => '',
		], $atts, 'cts_countdown' );
		
		// Get next upcoming event
		if ( empty( $atts['event_id'] ) ) {
			$events = self::get_next_event( $atts );
		} else {
			$events = self::get_event_by_id( $atts['event_id'] );
		}
		
		return self::render_template( "countdown/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Cover Shortcode
	 * 
	 * Usage:
	 * [cts_cover view="classic"]
	 * [cts_cover view="modern" calendar="2"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function cover_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'classic',
			'event_id' => '',
			'calendar' => '',
			'class' => '',
		], $atts, 'cts_cover' );
		
		// Get next upcoming event
		if ( empty( $atts['event_id'] ) ) {
			$events = self::get_next_event( $atts );
		} else {
			$events = self::get_event_by_id( $atts['event_id'] );
		}
		
		return self::render_template( "cover/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Timetable Shortcode
	 * 
	 * Usage:
	 * [cts_timetable view="modern"]
	 * [cts_timetable view="timeline" from="2025-12-01" to="2025-12-31"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function timetable_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'modern',
			'calendar' => '',
			'from' => '',
			'to' => '',
			'class' => '',
		], $atts, 'cts_timetable' );
		
		$events = self::get_events( $atts );
		
		return self::render_template( "timetable/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Carousel Shortcode
	 * 
	 * Usage:
	 * [cts_carousel view="type-1" limit="10"]
	 * [cts_carousel view="type-3" autoplay="true"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function carousel_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'type-1',
			'calendar' => '',
			'limit' => 10,
			'autoplay' => false,
			'interval' => 5000,
			'class' => '',
		], $atts, 'cts_carousel' );
		
		$events = self::get_events( $atts );
		
		return self::render_template( "carousel/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Single Event Shortcode
	 * 
	 * Usage:
	 * [cts_single id="2026"]
	 * [cts_single event_id="2026" view="fluent"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function single_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'id' => 0,
			'event_id' => '',
			'view' => 'default',
			'class' => '',
		], $atts, 'cts_single' );
		
		$events = self::get_event_by_id( $atts['id'] ?: $atts['event_id'] );
		
		return self::render_template( "single/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Map Shortcode
	 * 
	 * Usage:
	 * [cts_map view="standard"]
	 * [cts_map view="advanced" calendar="2,3"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function map_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'standard',
			'calendar' => '',
			'zoom' => 12,
			'center' => '',
			'class' => '',
		], $atts, 'cts_map' );
		
		$events = self::get_events( $atts );
		
		return self::render_template( "map/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Search Shortcode
	 * 
	 * Usage:
	 * [cts_search view="bar"]
	 * [cts_search view="advanced" calendar="2"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function search_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'bar',
			'calendar' => '',
			'placeholder' => __( 'Termine suchen...', 'churchtools-suite' ),
			'class' => '',
		], $atts, 'cts_search' );
		
		// Get events for search index
		$events = self::get_events( $atts );
		
		return self::render_template( "search/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Widget Shortcode
	 * 
	 * Usage:
	 * [cts_widget view="upcoming-events" limit="5"]
	 * [cts_widget view="calendar-widget"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function widget_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'upcoming',
			'calendar' => '',
			'limit' => 5,
			'class' => '',
		], $atts, 'cts_widget' );
		
		$events = self::get_events( $atts );
		
		return self::render_template( "widget/{$atts['view']}", $events, $atts );
	}
	
	/**
	 * Legacy Events Shortcode
	 * 
	 * Backward compatibility for old plugin
	 * Maps to [cts_list view="classic"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function legacy_events_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'calendar' => '',
			'limit' => 20,
			'from' => '',
			'to' => '',
		], $atts, 'cts_events' );
		
		// Map to list view
		$atts['view'] = 'classic';
		
		return self::list_shortcode( $atts );
	}
	
	/**
	 * DEBUG Test Shortcode
	 * 
	 * Simple test without templates - just shows raw data
	 * Usage: [cts_debug_test]
	 */
	public static function debug_test_shortcode( $atts ): string {
		$output = '<div style="padding: 20px; background: #f0f0f0; border: 2px solid #333; margin: 20px 0;">';
		$output .= '<h3>🔍 ChurchTools Suite Debug Test</h3>';
		
		// Test 1: Check if Data Provider exists
		try {
			if ( ! class_exists( 'ChurchTools_Suite_Template_Data' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
			}
			$data_provider = new ChurchTools_Suite_Template_Data();
			$output .= '<p>✅ Data Provider loaded</p>';
		} catch ( Exception $e ) {
			$output .= '<p>❌ Data Provider failed: ' . esc_html( $e->getMessage() ) . '</p>';
			$output .= '</div>';
			return $output;
		}
		
		// Test 2: Get events
		try {
			$events = $data_provider->get_events( [ 'limit' => 100 ] );
			$output .= '<p>✅ Events query executed</p>';
			$output .= '<p><strong>Event Count:</strong> ' . count( $events ) . '</p>';
		} catch ( Exception $e ) {
			$output .= '<p>❌ Events query failed: ' . esc_html( $e->getMessage() ) . '</p>';
			$output .= '</div>';
			return $output;
		}
		
		// Test 3: Show first 3 events
		if ( count( $events ) > 0 ) {
			$output .= '<h4>First 3 Events:</h4>';
			$output .= '<ul style="list-style: disc; margin-left: 20px;">';
			foreach ( array_slice( $events, 0, 3 ) as $event ) {
				$output .= '<li><strong>' . esc_html( $event['title'] ?? 'Untitled' ) . '</strong><br>';
				$output .= 'Date: ' . esc_html( $event['start_date'] ?? 'N/A' ) . '<br>';
				$output .= 'Calendar: ' . esc_html( $event['calendar_name'] ?? 'N/A' ) . '</li>';
			}
			$output .= '</ul>';
		} else {
			$output .= '<p>⚠️ No events found in database</p>';
		}
		
		// Test 4: Check template path
		$template_path = CHURCHTOOLS_SUITE_PATH . 'templates/list/classic.php';
		if ( file_exists( $template_path ) ) {
			$output .= '<p>✅ Template exists: templates/list/classic.php</p>';
		} else {
			$output .= '<p>❌ Template missing: templates/list/classic.php</p>';
		}
		
		$output .= '</div>';
		return $output;
	}
	
	/**
	 * Get events based on shortcode attributes
	 * 
	 * @param array $atts Shortcode attributes
	 * @return array Events data
	 */
	private static function get_events( array $atts ): array {
		// Initialize data provider if needed
		if ( ! self::$data_provider ) {
			self::$data_provider = new ChurchTools_Suite_Template_Data();
		}
		
		// Parse filters
		$filters = [
			'calendar_ids' => self::parse_calendar_ids( $atts['calendar'] ?? '' ),
			'limit' => absint( $atts['limit'] ?? 20 ),
			'from' => $atts['from'] ?? '',
			'to' => $atts['to'] ?? '',
		];
		
		// Sprint 4: Add date filters
		if ( ! empty( $atts['date_from'] ) ) {
			$filters['from'] = $atts['date_from'];
		}
		if ( ! empty( $atts['date_to'] ) ) {
			$filters['to'] = $atts['date_to'];
		}
		
		// Debug output (only when WP_DEBUG is enabled)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ChurchTools Suite Shortcode: Getting events with filters: ' . print_r( $filters, true ) );
		}
		
		$events = self::$data_provider->get_events( $filters );
		
		// Sprint 4: Apply order sorting
		if ( ! empty( $atts['order'] ) && $atts['order'] === 'desc' ) {
			$events = array_reverse( $events );
		}
		
		// Debug output
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ChurchTools Suite Shortcode: Found ' . count( $events ) . ' events' );
		}
		
		return $events;
	}
	
	/**
	 * Get single event by ID
	 * 
	 * @param string|int $id Event ID
	 * @return array Single event data
	 */
	private static function get_event_by_id( $id ): array {
		if ( ! self::$data_provider ) {
			self::$data_provider = new ChurchTools_Suite_Template_Data();
		}
		
		return self::$data_provider->get_event_by_id( $id );
	}
	
	/**
	 * Get next upcoming event
	 * 
	 * @param array $atts Shortcode attributes
	 * @return array Single event data
	 */
	private static function get_next_event( array $atts ): array {
		if ( ! self::$data_provider ) {
			self::$data_provider = new ChurchTools_Suite_Template_Data();
		}
		
		$filters = [
			'calendar_ids' => self::parse_calendar_ids( $atts['calendar'] ?? '' ),
			'limit' => 1,
			'from' => current_time( 'mysql' ),
		];
		
		$events = self::$data_provider->get_events( $filters );
		
		return ! empty( $events ) ? $events[0] : [];
	}
	
	/**
	 * Parse calendar IDs from string
	 * 
	 * @param string $calendar_ids Comma-separated calendar IDs
	 * @return array Array of calendar IDs
	 */
	private static function parse_calendar_ids( string $calendar_ids ): array {
		if ( empty( $calendar_ids ) ) {
			return [];
		}
		
		$ids = explode( ',', $calendar_ids );
		$ids = array_map( 'trim', $ids );
		$ids = array_filter( $ids );
		
		return $ids;
	}
	
	/**
	 * Render template via Template Loader
	 * 
	 * @param string $template_name Template name
	 * @param array $events Events data
	 * @param array $args Shortcode attributes
	 * @return string Rendered HTML
	 */
	private static function render_template( string $template_name, array $events, array $args ): string {
		// Initialize template loader if needed
		if ( ! self::$template_loader ) {
			self::$template_loader = new ChurchTools_Suite_Template_Loader();
		}
		
		// Apply filters to events before rendering
		$events = apply_filters( 'churchtools_suite_template_events', $events, $template_name, $args );
		
		// Render template (add .php extension if not present)
		$template_file = $template_name;
		if ( substr( $template_file, -4 ) !== '.php' ) {
			$template_file .= '.php';
		}
		
		$output = self::$template_loader->render_template( $template_file, [
			'events' => $events,
			'args' => $args,
		], false );
		
		// Check if template was found
		if ( empty( $output ) && count( $events ) > 0 ) {
			// Template not found, show error message
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				return sprintf(
					'<div class="churchtools-suite-error" style="padding: 20px; background: #fee; border: 1px solid #c33; border-radius: 4px; color: #c33;">
						<strong>ChurchTools Suite Fehler:</strong> Template "%s" wurde nicht gefunden.<br>
						Erwarteter Pfad: templates/%s.php<br>
						Gefundene Events: %d
					</div>',
					esc_html( $template_name ),
					esc_html( $template_name ),
					count( $events )
				);
			}
		}
		
		// If no events, show message
		if ( count( $events ) === 0 && empty( $output ) ) {
			return '<div class="churchtools-suite-empty" style="padding: 40px; text-align: center; color: #999;">
				<p><strong>Keine Termine gefunden</strong></p>
				<p>Es sind aktuell keine Termine verfügbar.</p>
			</div>';
		}
		
		// Apply wrapper class if specified
		if ( ! empty( $args['class'] ) ) {
			$output = sprintf(
				'<div class="churchtools-suite-wrapper %s">%s</div>',
				esc_attr( $args['class'] ),
				$output
			);
		}
		
		return $output;
	}
	
	/**
	 * Add modal template to footer
	 * 
	 * Only loads once per page, even if multiple shortcodes exist
	 */
	public static function add_modal_template(): void {
		if ( self::$modal_loaded ) {
			return;
		}
		
		// Check if any CTS shortcodes exist on page
		global $post;
		if ( ! $post || ! has_shortcode( $post->post_content, 'cts_' ) ) {
			return;
		}
		
		// Load template loader
		if ( ! self::$template_loader ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
			self::$template_loader = new ChurchTools_Suite_Template_Loader();
		}
		
		// Render modal template
		self::$template_loader::render_template( 'modal/event-detail.php', [], true );
		
		self::$modal_loaded = true;
	}
	
	/**
	 * AJAX: Load calendar month
	 * 
	 * Loads events for a specific month when navigating calendar
	 */
	public static function ajax_load_calendar_month(): void {
		check_ajax_referer( 'churchtools_suite_public', 'nonce' );
		
		$year = isset( $_POST['year'] ) ? intval( $_POST['year'] ) : date( 'Y' );
		$month = isset( $_POST['month'] ) ? intval( $_POST['month'] ) : date( 'n' );
		
		// Get first and last day of month
		$first_day = sprintf( '%04d-%02d-01', $year, $month );
		$last_day = date( 'Y-m-t', strtotime( $first_day ) );
		
		// Load repositories
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
		
		$events_repo = new ChurchTools_Suite_Events_Repository();
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$template_data = new ChurchTools_Suite_Template_Data( $events_repo, $calendars_repo );
		
		// Fetch events for date range
		$calendar_ids = $calendars_repo->get_selected_calendar_ids();
		$events = $template_data->get_events_by_date_range( $first_day, $last_day, $calendar_ids );
		
		// Group events by date
		$events_by_date = [];
		foreach ( $events as $event ) {
			$date = date( 'Y-m-d', strtotime( $event['start_datetime'] ) );
			if ( ! isset( $events_by_date[ $date ] ) ) {
				$events_by_date[ $date ] = [];
			}
			$events_by_date[ $date ][] = $event;
		}
		
		// Generate calendar grid HTML
		ob_start();
		
		// Weekdays
		echo '<div class="cts-weekday">' . esc_html__( 'Mo', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'Di', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'Mi', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'Do', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'Fr', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'Sa', 'churchtools-suite' ) . '</div>';
		echo '<div class="cts-weekday">' . esc_html__( 'So', 'churchtools-suite' ) . '</div>';
		
		// Calculate calendar grid
		$start_weekday = date( 'N', strtotime( $first_day ) );
		$days_in_month = date( 't', strtotime( $first_day ) );
		
		// Empty cells before first day
		for ( $i = 1; $i < $start_weekday; $i++ ) {
			echo '<div class="cts-day cts-day-empty"></div>';
		}
		
		// Days of month
		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$date = sprintf( '%04d-%02d-%02d', $year, $month, $day );
			$has_events = isset( $events_by_date[ $date ] );
			$is_today = $date === date( 'Y-m-d' );
			
			$classes = [ 'cts-day' ];
			if ( $is_today ) $classes[] = 'cts-day-today';
			if ( $has_events ) $classes[] = 'cts-day-has-events';
			
			echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-date="' . esc_attr( $date ) . '">';
			echo '<div class="cts-day-number">' . $day . '</div>';
			
			if ( $has_events ) {
				echo '<div class="cts-day-events">';
				foreach ( array_slice( $events_by_date[ $date ], 0, 3 ) as $event ) {
					$color = $event['calendar_color'] ?? '#667eea';
					$title = $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] . ' - ' . $event['title'];
					echo '<div class="cts-event-dot" style="background-color: ' . esc_attr( $color ) . '" title="' . esc_attr( $title ) . '">';
					echo '<span class="cts-event-time">' . esc_html( $event['start_time'] ) . '</span>';
					echo '<span class="cts-event-title-small">' . esc_html( wp_trim_words( $event['title'], 3 ) ) . '</span>';
					echo '</div>';
				}
				if ( count( $events_by_date[ $date ] ) > 3 ) {
					echo '<div class="cts-more-events">+' . ( count( $events_by_date[ $date ] ) - 3 ) . '</div>';
				}
				echo '</div>';
			}
			
			echo '</div>';
		}
		
		$html = ob_get_clean();
		
		// Generate month name
		$timestamp = mktime( 0, 0, 0, $month, 1, $year );
		$month_name = date_i18n( 'F Y', $timestamp );
		
		wp_send_json_success( [
			'html' => $html,
			'month' => $month,
			'year' => $year,
			'month_name' => $month_name,
		] );
	}
}
