<?php
/**
 * Elementor Integration
 * 
 * Registers ChurchTools Events widget for Elementor
 * 
 * @package ChurchTools_Suite
 * @since   0.5.9.38
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Elementor {
	
	/**
	 * Initialize Elementor integration
	 */
	public static function init() {
		// Check if Elementor is installed and active
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		
		// Register widget
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widgets' ] );
		
		// Register widget category
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ] );
	}
	
	/**
	 * Register ChurchTools widget category
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'churchtools-suite',
			[
				'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
				'icon'  => 'fa fa-calendar',
			]
		);
	}
	
	/**
	 * Register ChurchTools Events widget
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-elementor-widget.php';
		
		$widgets_manager->register( new ChurchTools_Suite_Elementor_Widget() );
	}
}
