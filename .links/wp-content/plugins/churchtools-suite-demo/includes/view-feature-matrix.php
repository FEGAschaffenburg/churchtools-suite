<?php
/**
 * View Feature Matrix
 * 
 * Defines which display options are supported by each view template
 * Used by Gutenberg blocks and Elementor widgets to show/disable toggles
 *
 * @package ChurchTools_Suite
 * @since   1.0.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get feature support matrix for all views
 * 
 * @return array View features matrix
 */
function churchtools_suite_get_view_features() {
	return [
		// ============================================
		// LIST VIEWS
		// ============================================
		'classic' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => false, // ❌ Keine Bilder
			'show_calendar_name' => true,
			'show_month_separator' => true,
		],
		
		'classic-with-images' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => true, // ✅ Mit Bildern
			'show_calendar_name' => true,
			'show_month_separator' => true,
		],
		
		'minimal' => [
			'show_event_description' => true, // Nur in Popup
			'show_appointment_description' => true, // Nur in Popup
			'show_location' => false, // ❌ Keine inline Location
			'show_services' => false, // ❌ Keine Services
			'show_time' => true,
			'show_tags' => false, // ❌ Keine Tags
			'show_images' => false, // ❌ Keine Bilder
			'show_calendar_name' => false, // ❌ Nur in Popup
			'show_month_separator' => true,
		],
		
		'modern' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => false, // ❌ Keine Bilder (Row Layout)
			'show_calendar_name' => true,
			'show_month_separator' => true,
		],
		
		// ============================================
		// GRID VIEWS
		// ============================================
		'simple' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => false, // ❌ Keine Tags in Cards
			'show_images' => true, // ✅ Grid mit Bildern
			'show_calendar_name' => true,
			'show_month_separator' => false, // ❌ Kein Monat-Separator in Grid
		],

		'classic-modern' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => false, // ❌ Kompaktes einzeiliges CSS-Grid-Layout
			'show_calendar_name' => true,
			'show_month_separator' => true,
		],

		// ============================================
		// COUNTDOWN VIEWS
		// ============================================
		'event-countdown' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => true, // ✅ Hero-Image (Split-Layout)
			'show_calendar_name' => true,
			'show_month_separator' => false, // ❌ Einzelnes Event, kein Separator
		],

		// ============================================
		// GRID VIEWS
		// ============================================
		'modern-grid' => [ // Grid-Version von modern
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => true, // ✅ Grid mit Bildern
			'show_calendar_name' => true,
			'show_month_separator' => false, // ❌ Kein Monat-Separator in Grid
		],
		
		// ============================================
		// CALENDAR VIEWS
		// ============================================
		'monthly-simple' => [
			'show_event_description' => false, // ❌ Kalender zeigt nur Titel
			'show_appointment_description' => false,
			'show_location' => false,
			'show_services' => false,
			'show_time' => true, // ✅ Zeit in Kalender-Cell
			'show_tags' => false,
			'show_images' => false,
			'show_calendar_name' => false,
			'show_month_separator' => false,
		],
	];
}

/**
 * Check if a feature is supported by a view
 * 
 * @param string $view View name
 * @param string $feature Feature name
 * @return bool True if supported
 */
function churchtools_suite_view_supports( $view, $feature ) {
	$features = churchtools_suite_get_view_features();
	
	if ( ! isset( $features[ $view ] ) ) {
		return true; // Unknown view = allow everything
	}
	
	return isset( $features[ $view ][ $feature ] ) && $features[ $view ][ $feature ];
}

/**
 * Get all features that a view supports
 * 
 * @param string $view View name
 * @return array Feature names (keys where value is true)
 */
function churchtools_suite_get_view_supported_features( $view ) {
	$features = churchtools_suite_get_view_features();
	
	if ( ! isset( $features[ $view ] ) ) {
		return array_keys( $features['classic'] ); // Fallback to classic features
	}
	
	return array_keys( array_filter( $features[ $view ] ) );
}

/**
 * Get all features that a view does NOT support
 * 
 * @param string $view View name
 * @return array Feature names (keys where value is false)
 */
function churchtools_suite_get_view_disabled_features( $view ) {
	$features = churchtools_suite_get_view_features();
	
	if ( ! isset( $features[ $view ] ) ) {
		return [];
	}
	
	return array_keys( array_filter( $features[ $view ], function( $supported ) {
		return ! $supported;
	} ) );
}
