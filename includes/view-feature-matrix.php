<?php
/**
 * View Feature Matrix
 * 
 * Defines which display options are supported by each view template.
 * Used by Gutenberg blocks and Elementor widgets to show/disable toggles.
 * 
 * WICHTIG: View-IDs sind standardisiert mit deutschem Präfix:
 * - List: list-einfach, list-klassisch, list-minimal, list-modern, list-klassisch-mit-bildern
 * - Grid: grid-klassisch, grid-einfach, grid-minimal, grid-modern
 * - Calendar: calendar-monatlich-einfach
 * - Countdown: countdown-klassisch
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
		// LIST VIEWS (standardisiert mit Präfix)
		// ============================================
		'list-einfach' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => false,
			'show_calendar_name' => true,
			'show_month_separator' => true,
		],

		'list-klassisch' => [
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
		
		'list-klassisch-mit-bildern' => [
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
		
		'list-minimal' => [
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
		
		'list-modern' => [
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
		// GRID VIEWS (standardisiert mit Präfix)
		// ============================================
		'grid-klassisch' => [ // Grid Classic (Hero-Image + Buttons)
			'show_event_description' => false, // ❌ Keine Beschreibung in Card
			'show_appointment_description' => false,
			'show_location' => true, // ✅ Location anzeigen
			'show_services' => false, // ❌ Keine Services
			'show_time' => true, // ✅ Zeit-Range
			'show_tags' => false, // ❌ Keine Tags
			'show_images' => true, // ✅ Hero-Image
			'show_calendar_name' => true, // ✅ Badge rechts oben
			'show_month_separator' => false, // ❌ Kein Monat-Separator in Grid
		],
		
		'grid-einfach' => [ // Grid Simple (Alle Details sichtbar)
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true, // ✅ Tags angezeigt
			'show_images' => false, // ❌ Keine Bilder
			'show_calendar_name' => true,
			'show_month_separator' => false, // ❌ Kein Monat-Separator in Grid
		],
		
		'grid-minimal' => [ // Grid Minimal (Nur Essentials + Info-Icon)
			'show_event_description' => true, // In Info-Popup
			'show_appointment_description' => false,
			'show_location' => true, // ✅ Main Info
			'show_services' => false, // ❌ Keine Services
			'show_time' => true, // In Info-Popup
			'show_tags' => false, // In Info-Popup
			'show_images' => false, // ❌ Keine Bilder
			'show_calendar_name' => true, // ✅ Badge unten
			'show_month_separator' => false, // ❌ Kein Monat-Separator in Grid
		],
		
		'grid-modern' => [ // Grid Modern (Card-Style)
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
		// CALENDAR VIEWS (standardisiert mit Präfix)
		// ============================================
		'calendar-monatlich-einfach' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => false,
			'show_time' => true,
			'show_tags' => false,
			'show_images' => false,
			'show_calendar_name' => true,
			'show_month_separator' => false,
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
		
		// ============================================
		// COUNTDOWN VIEWS
		// ============================================
		'countdown-klassisch' => [
			'show_event_description' => true, // ✅ Beschreibung unter Titel
		'show_appointment_description' => true, // ✅ Termin-spezifische Beschreibung
		'show_location' => true, // ✅ Location mit Icon
		'show_services' => true, // ✅ Services werden angezeigt
		'show_time' => true, // ✅ Uhrzeit mit "Uhr"-Suffix
		'show_tags' => true, // ✅ Tags als Badges
		'show_images' => true, // ✅ Hero-Image oder Calendar-Color
		'show_calendar_name' => true, // ✅ Calendar-Badge
		'show_month_separator' => false, // ❌ Keine Monats-Trenner bei Countdown
	],
	
		// ============================================
		// CAROUSEL VIEWS (v1.1.3.0)
		// ============================================
		'carousel-klassisch' => [
			'show_event_description' => true, // ✅ Beschreibung auf Karte
			'show_appointment_description' => true,
			'show_location' => true, // ✅ Location mit Icon
			'show_services' => true, // ✅ Services werden angezeigt
			'show_time' => true, // ✅ Zeit-Anzeige
			'show_tags' => true, // ✅ Tags am Footer
			'show_images' => true, // ✅ Hero-Image ODER Calendar-Color (wenn false)
			'show_calendar_name' => true, // ✅ Calendar-Badge
			'show_month_separator' => false, // ❌ Keine Monats-Trenner bei Carousel
		],
		'carousel-einzel-event' => [
			'show_event_description' => true,
			'show_appointment_description' => true,
			'show_location' => true,
			'show_services' => true,
			'show_time' => true,
			'show_tags' => true,
			'show_images' => true,
			'show_calendar_name' => true,
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
