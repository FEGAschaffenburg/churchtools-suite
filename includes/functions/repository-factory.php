<?php
/**
 * Repository Factory
 * 
 * Central factory function for creating repository instances.
 * Ensures consistent repository initialization across the plugin.
 * 
 * @package ChurchTools_Suite
 * @since   1.0.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get repository instance by name
 * 
 * Factory function that creates and returns repository instances.
 * Automatically loads required files and ensures base class is available.
 * 
 * @param string $repository_name Repository name (e.g., 'events', 'calendars', 'event_services')
 * @return object Repository instance
 * @throws Exception If repository class not found
 * 
 * @since 1.0.8.0
 */
function churchtools_suite_get_repository( string $repository_name ) {
	// Ensure base repository is loaded
	if ( ! class_exists( 'ChurchTools_Suite_Repository_Base' ) ) {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
	}
	
	// Map repository name to class name
	$repository_map = [
		'events' => 'ChurchTools_Suite_Events_Repository',
		'calendars' => 'ChurchTools_Suite_Calendars_Repository',
		'event_services' => 'ChurchTools_Suite_Event_Services_Repository',
		'services' => 'ChurchTools_Suite_Services_Repository',
		'service_groups' => 'ChurchTools_Suite_Service_Groups_Repository',
		'shortcode_presets' => 'ChurchTools_Suite_Shortcode_Presets_Repository',
	];
	
	// Check if repository exists in map
	if ( ! isset( $repository_map[ $repository_name ] ) ) {
		throw new Exception( sprintf(
			'Unknown repository: %s. Available repositories: %s',
			$repository_name,
			implode( ', ', array_keys( $repository_map ) )
		) );
	}
	
	$class_name = $repository_map[ $repository_name ];
	
	// Load repository file if class not yet available
	if ( ! class_exists( $class_name ) ) {
		// Convert class name to file name (e.g., ChurchTools_Suite_Events_Repository -> class-churchtools-suite-events-repository.php)
		$file_name = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
		$file_path = CHURCHTOOLS_SUITE_PATH . 'includes/repositories/' . $file_name;
		
		if ( ! file_exists( $file_path ) ) {
			throw new Exception( sprintf(
				'Repository file not found: %s (expected at %s)',
				$file_name,
				$file_path
			) );
		}
		
		require_once $file_path;
	}
	
	// Check if class exists after loading
	if ( ! class_exists( $class_name ) ) {
		throw new Exception( sprintf(
			'Repository class not found after loading file: %s',
			$class_name
		) );
	}
	
	// Create and return repository instance
	return new $class_name();
}
