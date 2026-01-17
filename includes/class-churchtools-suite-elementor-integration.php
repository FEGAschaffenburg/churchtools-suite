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
		// Load the widget class
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/elementor/class-churchtools-suite-elementor-events-widget.php';
		
		// Register hooks
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ], 10, 1 );
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ], 10, 1 );
	}
	
	/**
	 * Register widget category
	 * 
	 * @param \Elementor\Elements_Manager $elements_manager
	 * @since 1.0.4.0
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'churchtools-suite',
			[
				'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
				'icon' => 'fa fa-calendar-alt',
			]
		);
	}
	
	/**
	 * Register widget
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @since 1.0.4.0
	 */
	public static function register_widget( $widgets_manager ) {
		if ( class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
			$widgets_manager->register( new ChurchTools_Suite_Elementor_Events_Widget() );
		}
	}
}

// Initialize if Elementor is active
if ( did_action( 'elementor/loaded' ) ) {
	ChurchTools_Suite_Elementor_Integration::init();
} else {
	// Register initialization for when Elementor loads
	add_action( 'elementor/loaded', [ 'ChurchTools_Suite_Elementor_Integration', 'init' ] );
}
