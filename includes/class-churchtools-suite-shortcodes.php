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
		
		// v1.0.0.0 - CLEAN SLATE: Only list/classic active
		
		// Generic shortcode (v0.9.4.11) - routes to appropriate view based on viewType parameter
		add_shortcode( 'churchtools_events', [ __CLASS__, 'generic_events_shortcode' ] );
		
		// List Views (only classic active)
		add_shortcode( 'cts_list', [ __CLASS__, 'list_shortcode' ] );
		
		// Grid Views (v0.9.9.35: background-images added)
		add_shortcode( 'cts_grid', [ __CLASS__, 'grid_shortcode' ] );
		
		// Calendar Views
		add_shortcode( 'cts_calendar', [ __CLASS__, 'calendar_shortcode' ] );
		
		// Countdown Views
		add_shortcode( 'cts_countdown', [ __CLASS__, 'countdown_shortcode' ] );
		
		// Carousel Views (v1.1.3.0)
		add_shortcode( 'cts_carousel', [ __CLASS__, 'carousel_shortcode' ] );
		
		// Carousel Views (v1.1.3.0)
		add_shortcode( 'cts_event_search', [ __CLASS__, 'event_search_shortcode' ] );
		
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
	 * Convert HEX color to RGBA with opacity
	 * 
	 * Converts hex color codes (#2563eb) to RGBA format with opacity parameter.
	 * Used for overlay colors in grid views with background images.
	 * 
	 * @param string $hex Hex color code (e.g., '#2563eb')
	 * @param float $opacity Opacity value (0.0 - 1.0), default 1.0
	 * @return string RGBA color string (e.g., 'rgba(37, 99, 235, 0.6)')
	 */
	public static function hex_to_rgba( string $hex, float $opacity = 1.0 ): string {
		// Remove '#' if present
		$hex = ltrim( $hex, '#' );
		
		// Check if valid hex color (6 or 3 characters)
		if ( ! preg_match( '/^(?:[0-9a-f]{3}){1,2}$/i', $hex ) ) {
			// Return default gray if invalid
			return 'rgba(107, 114, 128, ' . $opacity . ')';
		}
		
		// Expand shorthand (#abc → #aabbcc)
		if ( strlen( $hex ) === 3 ) {
			$hex = preg_replace( '/([0-9a-f])/i', '$1$1', $hex );
		}
		
		// Convert hex to RGB
		$rgb = unpack( 'N', hex2bin( str_pad( $hex, 8, 'f', STR_PAD_LEFT ) ) );
		$r = ( $rgb[1] >> 16 ) & 255;
		$g = ( $rgb[1] >> 8 ) & 255;
		$b = $rgb[1] & 255;
		
		// Clamp opacity to 0-1 range
		$opacity = max( 0, min( 1, floatval( $opacity ) ) );
		
		return "rgba({$r}, {$g}, {$b}, {$opacity})";
	}
	
	/**
	 * Map old show_description to new separate parameters
	 * 
	 * v0.10.4.37: Backward compatibility for old shortcodes/blocks
	 * Old: show_description (both Event + Appointment)
	 * New: show_event_description + show_appointment_description (separate)
	 * 
	 * @param array $atts Shortcode attributes
	 * @return array Modified attributes with mapping applied
	 */
	private static function map_legacy_description_param( array $atts ): array {
		// If old show_description exists and new params don't, map it
		if ( isset( $atts['show_description'] ) ) {
			if ( ! isset( $atts['show_event_description'] ) ) {
				$atts['show_event_description'] = $atts['show_description'];
			}
			if ( ! isset( $atts['show_appointment_description'] ) ) {
				$atts['show_appointment_description'] = $atts['show_description'];
			}
		}
		
		return $atts;
	}
	
	/**
	 * Generic Events Shortcode (v0.9.4.11)
	 * 
	 * Main Shortcode Handler (v1.0.1: Unified view names)
	 * 
	 * Routes to appropriate handler based on view name prefix: list-, grid-, calendar-
	 * 
	 * Usage (v1.0.1+):
	 * [churchtools_events view="list-classic"]
	 * [churchtools_events view="grid-simple" columns="3"]
	 * [churchtools_events view="calendar-monthly-simple"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function generic_events_shortcode( $atts ): string {
		// v0.9.6.6: Use global settings as defaults (from Allgemeines tab)
		$default_show_past = get_option( 'churchtools_suite_show_past_events', 0 );
		$default_show_month_sep = get_option( 'churchtools_suite_show_month_separator', 1 );
		
		$atts = shortcode_atts( [
			'view' => 'list-classic',  // v1.0.1: Unified view names with prefix
			'limit' => 5,
			'columns' => 3,  // for grid views
			'calendar' => '',
			'calendars' => '',
			'tags' => '',
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => false,
			'show_time' => true,
			'show_tags' => true,
			'show_calendar_name' => true,
			'show_month_separator' => (bool) $default_show_month_sep,
			'enable_modal' => true,
			'show_past_events' => false,
			'event_action' => 'modal',
		], $atts, 'churchtools_events' );

		// Single Event Routing: If URL contains event_id, render single view uniformly
		$event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
		if ( $event_id > 0 ) {
			return do_shortcode( '[cts_event id="' . $event_id . '"]' );
		}
		
		// Route to appropriate view handler based on view name prefix
		$view = strtolower( $atts['view'] );
		
		// v1.0.1: Auto-extract view prefix and convert to old format for backward compatibility
		if ( strpos( $view, 'list-' ) === 0 ) {
			// list-classic → classic
			$atts['view'] = substr( $view, 5 );
			return self::list_shortcode( $atts );
		}
		
		if ( strpos( $view, 'grid-' ) === 0 ) {
			// grid-simple → simple
			$atts['view'] = substr( $view, 5 );
			return self::grid_shortcode( $atts );
		}
		
		if ( strpos( $view, 'calendar-' ) === 0 ) {
			// calendar-monthly-simple → monthly-simple
			$atts['view'] = substr( $view, 9 );
			return self::calendar_shortcode( $atts );
		}
		
		// Fallback: unknown view prefix
		$valid_views = 'list-classic, list-minimal, list-modern, list-classic-with-images, list-table, grid-simple, grid-modern, calendar-monthly-simple';
		return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>View nicht verfügbar:</strong> "' . esc_html( $view ) . '" ist keine gültige View. Verfügbar: ' . esc_html( $valid_views ) . '</p>';
	}
	
	public static function event_search_shortcode($atts): string {
		$atts = shortcode_atts( [
			'view' => 'classic',
			'custom_class' => '',
		], $atts, 'cts_event_search' );

		$search_term = ChurchTools_Suite_Queryvars::getEventSearchQuery();
		$atts["search_term"] = $search_term;

		// Backward-Compatibility: Normalisiere View-ID (alte Namen → neue deutsche IDs)
		$atts['view'] = ChurchTools_Suite_Template_Loader::normalize_view_id( 'search', $atts['view'] );
		
		$allowed_views = [ 'search-classic' ];
		$allowed_views = apply_filters( 'churchtools_suite_search_allowed_views', $allowed_views );
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>View nicht verfügbar:</strong> Erlaubte Ansichten: Klassisch. View "' . esc_html( $atts['view'] ) . '" existiert nicht.</p>';
		}

		$template_filename = ChurchTools_Suite_Template_Loader::normalize_view_to_filename( $atts['view'] );
		$template_path = 'views/event-search/' . $template_filename;

		return self::render_template( $template_path, $events = [], $atts );
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
			'calendars' => '',
			'tags' => '',
			'limit' => 5,
			'from' => '',
			'to' => '',
			'class' => '',
			// v1.0.0: All display options configured per block/shortcode
			'show_images' => true,
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => false,
			'show_time' => true,
			'show_tags' => true,
			'show_calendar_name' => true,
			'show_month_separator' => true,
			'show_past_events' => false,

			'event_action' => 'modal',
			// v0.9.6.8: Style Management
			'style_mode' => 'theme',
			'use_calendar_colors' => false,
			'custom_primary_color' => '#2563eb',
			'custom_text_color' => '#1e293b',
			'custom_background_color' => '#ffffff',
			'custom_border_radius' => 6,
			'custom_font_size' => 14,
			'custom_padding' => 12,
			'custom_spacing' => 8,
			// Legacy (deprecated in v1.0.0)
			'show_description' => null,
			// Filter parameters
			'order' => 'asc',
			'date_from' => '',
			'date_to' => '',
			'filter_tags' => '',

			// Use existing search value
			'link_search' => true,
		], $atts, 'cts_list' );
		
		// Backward-Compatibility: Normalisiere View-ID (alte Namen → neue deutsche IDs)
		$atts['view'] = ChurchTools_Suite_Template_Loader::normalize_view_id( 'list', $atts['view'] );
		
		// v0.9.7.0 - Erlaubte Views (deutsche IDs mit Präfix)
		$allowed_views = [ 'list-klassisch', 'list-minimal', 'list-modern', 'list-klassisch-mit-bildern' ];
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>View nicht verfügbar:</strong> Erlaubte Ansichten: Klassisch, Minimal, Modern, Klassisch-mit-Bildern. View "' . esc_html( $atts['view'] ) . '" existiert nicht.</p>';
		}
		
		// Convert boolean values
		$atts['show_event_description'] = self::parse_boolean( $atts['show_event_description'] );
		$atts['show_appointment_description'] = self::parse_boolean( $atts['show_appointment_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		$atts['show_tags'] = self::parse_boolean( $atts['show_tags'] );
		$atts['show_month_separator'] = self::parse_boolean( $atts['show_month_separator'] );
		$atts['show_past_events'] = self::parse_boolean( $atts['show_past_events'] );
		$atts['link_search'] = self::parse_boolean( $atts['link_search'] );
		
		// Legacy show_description fallback
		if ( $atts['show_description'] !== null ) {
			$show_desc = self::parse_boolean( $atts['show_description'] );
			$atts['show_event_description'] = $show_desc;
			$atts['show_appointment_description'] = $show_desc;
		}

		if($atts["link_search"] === true) {
			$atts["search"] = ChurchTools_Suite_Queryvars::getEventSearchQuery();
		}
		
		// Validate order
		if ( ! in_array( $atts['order'], [ 'asc', 'desc' ], true ) ) {
			$atts['order'] = 'asc';
		}
		
		$events = self::get_events( $atts );
		
		// Konvertiere standardisierte View-ID zu Template-Dateiname (list-klassisch → classic)
		$template_filename = ChurchTools_Suite_Template_Loader::normalize_view_to_filename( $atts['view'] );
		$template_path = 'views/event-list/' . $template_filename;
		
		return self::render_template( $template_path, $events, $atts );
	}
	
	/**
	 * Grid Shortcode (v0.9.9.0)
	 * 
	 * Usage:
	 * [cts_grid view="simple"]
	 * [cts_grid view="simple" columns="3" limit="9"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function grid_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'simple',
			'columns' => 3,
			'limit' => 9,
			'calendar' => '',
			'calendars' => '',
			'tags' => '',
			'from' => '',
			'to' => '',
			'class' => '',
			'show_past_events' => false,
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => false,
			'show_time' => true,
			'show_tags' => true,
			'show_calendar_name' => true,
			'event_action' => 'modal',
			// Style Management
			'style_mode' => 'theme',
			'use_calendar_colors' => false,
			'custom_primary_color' => '#2563eb',
			'custom_text_color' => '#1e293b',
			'custom_background_color' => '#ffffff',
			'custom_border_radius' => 6,
			'custom_font_size' => 14,
			'custom_padding' => 12,
			'custom_spacing' => 16,

			// Use existing search value
			'link_search' => true,			
		], $atts, 'cts_grid' );
		
		// Backward-Compatibility: Normalisiere View-ID (alte Namen → neue deutsche IDs)
		$atts['view'] = ChurchTools_Suite_Template_Loader::normalize_view_id( 'grid', $atts['view'] );
		
		// Erlaubte Views (deutsche IDs mit Präfix)
		$allowed_views = [ 'grid-klassisch', 'grid-einfach', 'grid-minimal', 'grid-modern' ];
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>Grid View nicht verfügbar:</strong> Erlaubte Ansichten: Klassisch, Einfach, Minimal, Modern</p>';
		}
		
		// Validate columns (1-6)
		$atts['columns'] = max( 1, min( 6, intval( $atts['columns'] ) ) );
		
		// Convert boolean values
		$atts['show_past_events'] = self::parse_boolean( $atts['show_past_events'] );
		$atts['use_calendar_colors'] = self::parse_boolean( $atts['use_calendar_colors'] );
		$atts['use_calendar_colors'] = self::parse_boolean( $atts['use_calendar_colors'] );
		$atts['show_event_description'] = self::parse_boolean( $atts['show_event_description'] );
		$atts['show_appointment_description'] = self::parse_boolean( $atts['show_appointment_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		$atts['show_tags'] = self::parse_boolean( $atts['show_tags'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['link_search'] = self::parse_boolean( $atts['link_search'] );
		
		if($atts["link_search"] === true) {
			$atts["search"] = ChurchTools_Suite_Queryvars::getEventSearchQuery();
		}

		// Get events
		$events = self::get_events( $atts );
		
		// Konvertiere standardisierte View-ID zu Template-Dateiname (grid-klassisch → classic)
		$template_filename = ChurchTools_Suite_Template_Loader::normalize_view_to_filename( $atts['view'] );
		$template_path = 'views/event-grid/' . $template_filename;
		
		return self::render_template( $template_path, $events, $atts );
	}
	
	/**
	 * Calendar Shortcode (v0.9.8.0)
	 * 
	 * Usage:
	 * [cts_calendar view="monthly-simple"]
	 * [cts_calendar view="monthly-simple" calendar="2"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function calendar_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'monthly-simple',
			'calendar' => '',
			'calendars' => '',
			'tags' => '',
			'limit' => 100, // Higher limit for calendar views
			'from' => '',
			'to' => '',
			'class' => '',
			'show_past_events' => false,
			'event_action' => 'modal',
			// Style Management
			'style_mode' => 'theme',
			'use_calendar_colors' => false,
			'custom_primary_color' => '#2563eb',
			'custom_text_color' => '#1e293b',
			'custom_background_color' => '#ffffff',
			'custom_border_radius' => 6,
			'custom_font_size' => 14,
			'custom_padding' => 8,
			'custom_spacing' => 0,

			// Use existing search value
			'link_search' => true,			
		], $atts, 'cts_calendar' );
		
		// v0.9.8.6: Debug - Check what view value we received
		if ( WP_DEBUG ) {
			error_log( 'Calendar Shortcode - Received view: ' . var_export( $atts['view'], true ) );
			error_log( 'Calendar Shortcode - All atts: ' . var_export( $atts, true ) );
		}
		
		// v0.9.8.0: Only monthly-simple activated
		$allowed_views = [ 'monthly-simple' ];
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>Calendar View nicht verfügbar:</strong> Nur "monthly-simple" ist aktiv. (Erhalten: "' . esc_html( $atts['view'] ) . '")</p>';
		}
		
		// Convert boolean values
		$atts['show_past_events'] = self::parse_boolean( $atts['show_past_events'] );
		$atts['use_calendar_colors'] = self::parse_boolean( $atts['use_calendar_colors'] );
		$atts['link_search'] = self::parse_boolean( $atts['link_search'] );
		
		if($atts["link_search"] === true) {
			$atts["search"] = ChurchTools_Suite_Queryvars::getEventSearchQuery();
		}

		// Get events
		$events = self::get_events( $atts );
		
		// v0.9.9.44: New template structure (views/event-calendar/)
		$template_path = 'views/event-calendar/' . $atts['view'];
		
		return self::render_template( $template_path, $events, $atts );
	}
	
	/**
	 * Countdown Shortcode (v1.1.1.0)
	 * 
	 * Zeigt nächstes kommendes Event mit Live-Countdown-Timer
	 * 
	 * Usage:
	 * [cts_countdown]
	 * [cts_countdown view="countdown-klassisch"]
	 * [cts_countdown calendar="2" show_event_description="true"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function countdown_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'countdown-klassisch',
			'calendar' => '',
			'calendars' => '',
			'tags' => '',
			'event_id' => '', // v1.1.3.0: Spezifischer Event statt nächster
			'limit' => 1, // Always 1 event (next upcoming)
			'from' => '',
			'to' => '',
			'class' => '',
			'show_past_events' => false,
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_tags' => true,
			'show_calendar_name' => true,
			'show_images' => true,
			'event_action' => 'modal',
			// Style Management
			'style_mode' => 'theme',
			'use_calendar_colors' => false,
			'custom_primary_color' => '#3b82f6',
			'custom_text_color' => '#ffffff',
			'custom_background_color' => '#2d3748',
			'custom_border_radius' => 8,
			'custom_font_size' => 16,
			'custom_padding' => 24,
			'custom_spacing' => 16,
		], $atts, 'cts_countdown' );
		
		// v1.1.3.0: Fix für Block-Editor - wenn 'view' leer oder 'classic', setze countdown-klassisch
		if ( empty( $atts['view'] ) || $atts['view'] === 'classic' ) {
			$atts['view'] = 'countdown-klassisch';
		}
		
		// Backward-Compatibility: Normalisiere View-ID
		$atts['view'] = ChurchTools_Suite_Template_Loader::normalize_view_id( 'countdown', $atts['view'] );
		
		// Erlaubte Views (deutsche IDs mit Präfix)
		$allowed_views = [ 'countdown-klassisch' ];
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>Countdown View nicht verfügbar:</strong> Nur "countdown-klassisch" ist aktiv.</p>';
		}
		
		// Force limit to 1 (countdown always shows only next event)
		$atts['limit'] = 1;
		
		// v1.1.3.0: Wenn event_id angegeben, filtere Events
		if ( ! empty( $atts['event_id'] ) ) {
			$atts['event_id'] = absint( $atts['event_id'] );
		} else {
			$atts['event_id'] = 0;
		}
		
		// Convert boolean values
		$atts['show_past_events'] = self::parse_boolean( $atts['show_past_events'] );
		$atts['use_calendar_colors'] = self::parse_boolean( $atts['use_calendar_colors'] );
		$atts['show_event_description'] = self::parse_boolean( $atts['show_event_description'] );
		$atts['show_appointment_description'] = self::parse_boolean( $atts['show_appointment_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_tags'] = self::parse_boolean( $atts['show_tags'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['show_images'] = self::parse_boolean( $atts['show_images'] );
		
		// Get events
		// v1.1.3.0: Wenn event_id angegeben, lade spezifischen Event statt nächsten
		if ( ! empty( $atts['event_id'] ) ) {
			$single_event = self::get_event_by_id( $atts['event_id'] );
			$events = $single_event ? [ $single_event ] : [];
		} else {
			$events = self::get_events( $atts );
		}
		
		// Konvertiere standardisierte View-ID zu Template-Dateiname (countdown-klassisch → classic)
		$template_filename = ChurchTools_Suite_Template_Loader::normalize_view_to_filename( $atts['view'] );
		$template_path = 'views/event-countdown/' . $template_filename;
		
		return self::render_template( $template_path, $events, $atts );
	}
	
	/**
	 * Carousel Shortcode (v1.1.3.0)
	 * 
	 * Horizontales Karussell mit Swipe-Navigation (basierend auf Grid Classic)
	 * 
	 * Usage:
	 * [cts_carousel]
	 * [cts_carousel view="carousel-klassisch" slides_per_view="3"]
	 * [cts_carousel calendar="2" autoplay="true" loop="true"]
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function carousel_shortcode( $atts ): string {
		$atts = shortcode_atts( [
			'view' => 'carousel-klassisch',
			'calendar' => '',
			'calendars' => '',
			'tags' => '',
			'limit' => 12, // Default: 12 events in carousel
			'from' => '',
			'to' => '',
			'class' => '',
			'show_past_events' => false,
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_time' => true,
			'show_services' => false,
			'show_tags' => false,
			'show_calendar_name' => true,
			'show_images' => true,
			'event_action' => 'modal',
			// Carousel-spezifische Parameter
			'slides_per_view' => 3, // 1-6 slides
			'autoplay' => false,
			'autoplay_delay' => 5000, // milliseconds
			'loop' => true,
			// Style Management
			'style_mode' => 'theme',
			'use_calendar_colors' => true, // Default true (wie Grid Classic)
			'custom_primary_color' => '#2563eb',
			'custom_text_color' => '#111827',
			'custom_background_color' => '#ffffff',
			'custom_border_radius' => 0, // Wie Grid Classic: eckig
			'custom_font_size' => 14,
			'custom_padding' => 16,
			'custom_spacing' => 16,

			// Use existing search value
			'link_search' => true,			
		], $atts, 'cts_carousel' );
		
		// Backward-Compatibility: Normalisiere View-ID
		$atts['view'] = ChurchTools_Suite_Template_Loader::normalize_view_id( 'carousel', $atts['view'] );
		
		// Erlaubte Views (deutsche IDs mit Präfix)
		$allowed_views = [ 'carousel-klassisch' ];
		if ( ! in_array( $atts['view'], $allowed_views, true ) ) {
			return '<p style="padding: 12px; background: #fef3c7; border-radius: 4px;">⚠️ <strong>Carousel View nicht verfügbar:</strong> Nur "carousel-klassisch" ist aktiv.</p>';
		}
		
		// Validate carousel-specific parameters
		$atts['slides_per_view'] = max( 1, min( 6, intval( $atts['slides_per_view'] ) ) );
		$atts['autoplay_delay'] = max( 1000, min( 10000, intval( $atts['autoplay_delay'] ) ) );
		
		// Convert boolean values
		$atts['show_past_events'] = self::parse_boolean( $atts['show_past_events'] );
		$atts['use_calendar_colors'] = self::parse_boolean( $atts['use_calendar_colors'] );
		$atts['show_event_description'] = self::parse_boolean( $atts['show_event_description'] );
		$atts['show_appointment_description'] = self::parse_boolean( $atts['show_appointment_description'] );
		$atts['show_location'] = self::parse_boolean( $atts['show_location'] );
		$atts['show_time'] = self::parse_boolean( $atts['show_time'] );
		$atts['show_services'] = self::parse_boolean( $atts['show_services'] );
		$atts['show_tags'] = self::parse_boolean( $atts['show_tags'] );
		$atts['show_calendar_name'] = self::parse_boolean( $atts['show_calendar_name'] );
		$atts['show_images'] = self::parse_boolean( $atts['show_images'] );
		$atts['autoplay'] = self::parse_boolean( $atts['autoplay'] );
		$atts['loop'] = self::parse_boolean( $atts['loop'] );
		$atts['link_search'] = self::parse_boolean( $atts['link_search'] );
		
		if($atts["link_search"] === true) {
			$atts["search"] = ChurchTools_Suite_Queryvars::getEventSearchQuery();
		}
		
		// Get events
		$events = self::get_events( $atts );
		
		// Konvertiere standardisierte View-ID zu Template-Dateiname (carousel-klassisch → classic)
		$template_filename = ChurchTools_Suite_Template_Loader::normalize_view_to_filename( $atts['view'] );
		$template_path = 'views/event-carousel/' . $template_filename;
		
		return self::render_template( $template_path, $events, $atts );
	}
	
	/**
	 * Debug Shortcode (Developer Tool)
	 * 
	 * Shows debug information about template loading
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function debug_shortcode( $atts ): string {
		$output = '<div style="padding: 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">';
		$output .= '<h3>ChurchTools Suite Debug</h3>';
		
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
		$template_path = CHURCHTOOLS_SUITE_PATH . 'templates/views/event-list/classic.php';
		if ( file_exists( $template_path ) ) {
			$output .= '<p>✅ Template exists: templates/views/event-list/classic.php</p>';
		} else {
			$output .= '<p>❌ Template missing: templates/views/event-list/classic.php</p>';
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
		
		// Parse filters - support both 'calendar' (old) and 'calendars' (new)
		$calendar_param = ! empty( $atts['calendars'] ) ? $atts['calendars'] : ( $atts['calendar'] ?? '' );
		$filters = [
			'calendar_ids' => self::parse_calendar_ids( $calendar_param ),
			'limit' => absint( $atts['limit'] ?? 20 ),
			'from' => $atts['from'] ?? '',
			'to' => $atts['to'] ?? '',
			'show_past_events' => $atts['show_past_events'] ?? false,
			'search' => $atts['search'] ?? '',
		];
		
		// Sprint 4: Add date filters
		if ( ! empty( $atts['date_from'] ) ) {
			$filters['from'] = $atts['date_from'];
		}
		if ( ! empty( $atts['date_to'] ) ) {
			$filters['to'] = $atts['date_to'];
		}
		
		// v0.10.4.11: Add tag filter (support both 'filter_tags' and 'tags')
		$tags_param = ! empty( $atts['tags'] ) ? $atts['tags'] : ( $atts['filter_tags'] ?? '' );
		if ( ! empty( $tags_param ) ) {
			$filters['filter_tags'] = self::parse_tag_filter( $tags_param );
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
		// If calendar_ids parameter is explicitly provided
		if ( ! empty( $calendar_ids ) ) {
			$ids = explode( ',', $calendar_ids );
			$ids = array_map( 'trim', $ids );
			$ids = array_filter( $ids );
			return $ids;
		}
		
		// If no parameter provided, use selected calendars from admin settings (v1.0.8.0: Factory)
		$calendars_repo = churchtools_suite_get_repository( 'calendars' );
		$selected_ids = $calendars_repo->get_selected_calendar_ids();
		
		return ! empty( $selected_ids ) ? $selected_ids : [];
	}
	
	/**
	 * Parse tag filter string into array (v0.10.4.11)
	 * 
	 * Converts "Gottesdienst,Alpha,Workshop" to ["Gottesdienst", "Alpha", "Workshop"]
	 * 
	 * @param string $filter_tags Comma-separated tag names
	 * @return array Array of tag names
	 */
	private static function parse_tag_filter( string $filter_tags ): array {
		if ( empty( $filter_tags ) ) {
			return [];
		}
		
		$tags = explode( ',', $filter_tags );
		$tags = array_map( 'trim', $tags );
		$tags = array_filter( $tags );
		
		return $tags;
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
		
		// v0.9.6.18: Always load modal (needed for Gutenberg blocks)
		// v1.0: Also load on single event pages (event_id parameter)
		global $post;
		if ( ! $post ) {
			return;
		}
		
		// Check for shortcodes OR Gutenberg blocks OR single event view
		$has_cts_content = has_shortcode( $post->post_content, 'cts_' ) || 
		                   has_block( 'churchtools-suite/events-block', $post ) ||
		                   ( isset( $_GET['event_id'] ) && absint( $_GET['event_id'] ) > 0 ); // v1.0: Single event pages
		
		if ( ! $has_cts_content ) {
			return;
		}
		
		// Load template loader
		if ( ! self::$template_loader ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-template-loader.php';
			self::$template_loader = new ChurchTools_Suite_Template_Loader();
		}
		
		// v0.9.9.73: Enhanced logging
		$debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		if ( $debug ) {
			error_log( '[ChurchTools Suite] add_modal_template() called. has_content: ' . ( $has_cts_content ? 'YES' : 'NO' ) );
		}
		
		// Render modal template - WICHTIG: $echo=true means output is directly sent to browser
		$modal_html = self::$template_loader::render_template( 'views/event-modal/professional.php', [], false );
		
		if ( $debug ) {
			error_log( '[ChurchTools Suite] Modal template rendered. Length: ' . strlen( $modal_html ) . ' chars. First 100: ' . substr( $modal_html, 0, 100 ) );
		}
		
		// Output the modal HTML directly
		if ( ! empty( $modal_html ) && strpos( $modal_html, '<!--' ) !== 0 ) {
			echo $modal_html;
			self::$modal_loaded = true;
			
			if ( $debug ) {
				error_log( '[ChurchTools Suite] Modal template output successfully' );
			}
		} else {
			if ( $debug ) {
				error_log( '[ChurchTools Suite] Modal template load FAILED or returned error comment: ' . ( $modal_html ? substr( $modal_html, 0, 150 ) : 'EMPTY' ) );
			}
		}
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
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
		
		$template_data = new ChurchTools_Suite_Template_Data();
		$calendars_repo = churchtools_suite_get_repository( 'calendars' ); // v1.0.8.0: Factory
		
		// Fetch events for date range
		$calendar_ids = $calendars_repo->get_selected_calendar_ids();
		
		// Debug logging
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Before get_events', [
				'calendar_ids' => $calendar_ids,
				'from' => $first_day . ' 00:00:00',
				'to' => $last_day . ' 23:59:59',
			] );
		}
		
		$events = $template_data->get_events( [
			'from' => $first_day . ' 00:00:00',
			'to' => $last_day . ' 23:59:59',
			'calendar_ids' => $calendar_ids,
			'limit' => 1000, // Calendar needs all events in month
		] );
		
		// Debug logging
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'After get_events', [
				'events_count' => count( $events ),
				'first_event' => ! empty( $events ) ? array_keys( $events[0] ) : [],
			] );
		}
		
		// Group events by date (use WordPress timezone)
		$events_by_date = [];
		foreach ( $events as $event ) {
			$date = get_date_from_gmt( $event['start_datetime'], 'Y-m-d' );
			if ( ! isset( $events_by_date[ $date ] ) ) {
				$events_by_date[ $date ] = [];
			}
			$events_by_date[ $date ][] = $event;
		}
		
		// Debug logging
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Events grouped by date', [
				'dates_count' => count( $events_by_date ),
			] );
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
				// Debug logging
				if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
					ChurchTools_Suite_Logger::debug( 'ajax_calendar', 'Rendering events for date', [
						'date' => $date,
						'events_count' => count( $events_by_date[ $date ] ),
					] );
				}
				
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
