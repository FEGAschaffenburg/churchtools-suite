<?php
/**
 * Elementor Integration
 * 
 * Handles Elementor widget registration.
 * This file is only loaded if Elementor is active.
 *
 * @package ChurchTools_Suite
 * @since   1.0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Elementor_Integration {
	
	/**
	 * Initialize Elementor integration
	 * 
	 * Called only if Elementor is active
	 * 
	 * @since 1.0.4.0
	 */
	public static function init() {
		error_log( '[ChurchTools Elementor] Integration init() called' );
		
		// Load the widget class
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/elementor/class-churchtools-suite-elementor-events-widget.php';
		error_log( '[ChurchTools Elementor] Widget class file loaded' );
		
		// Register hooks
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ], 10, 1 );
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ], 10, 1 );
		error_log( '[ChurchTools Elementor] Hooks registered' );
	}
	
	/**
	 * Register widget category
	 * 
	 * @param \Elementor\Elements_Manager $elements_manager
	 * @since 1.0.4.0
	 */
	public static function register_category( $elements_manager ) {
		error_log( '[ChurchTools Elementor] register_category() called' );
		$elements_manager->add_category(
			'churchtools-suite',
			[
				'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
				'icon' => 'fa fa-calendar-alt',
			]
		);
		error_log( '[ChurchTools Elementor] Category registered: churchtools-suite' );
	}
	
	/**
	 * Register widget
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @since 1.0.4.0
	 */
	public static function register_widget( $widgets_manager ) {
		error_log( '[ChurchTools Elementor] register_widget() called' );
		error_log( '[ChurchTools Elementor] Widget class exists: ' . ( class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ? 'YES' : 'NO' ) );
		
		if ( class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
			try {
				$widget = new ChurchTools_Suite_Elementor_Events_Widget();
				$widgets_manager->register( $widget );
				error_log( '[ChurchTools Elementor] Widget registered successfully: ' . $widget->get_name() );
			} catch ( Exception $e ) {
				error_log( '[ChurchTools Elementor] ERROR registering widget: ' . $e->getMessage() );
			}
		}
	}
}

// Initialize immediately - Elementor is already loaded when this file is included
error_log( '[ChurchTools Elementor] Integration file loaded' );
error_log( '[ChurchTools Elementor] elementor/loaded action fired: ' . ( did_action( 'elementor/loaded' ) ? 'YES' : 'NO' ) );
error_log( '[ChurchTools Elementor] Elementor class exists: ' . ( class_exists( '\Elementor\Plugin' ) ? 'YES' : 'NO' ) );

// Always init immediately since we're loaded after Elementor
ChurchTools_Suite_Elementor_Integration::init();
