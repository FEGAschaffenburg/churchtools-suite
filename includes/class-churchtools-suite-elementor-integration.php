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
	 * Log helper (persists to option + error_log)
	 */
	public static function log( string $message ): void {
		$line = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message;
		error_log( $line );
		$log = get_option( 'churchtools_suite_elementor_log', [] );
		if ( ! is_array( $log ) ) {
			$log = [];
		}
		$log[] = $line;
		// Keep only last 50 entries
		if ( count( $log ) > 50 ) {
			$log = array_slice( $log, -50 );
		}
		update_option( 'churchtools_suite_elementor_log', $log, false );
	}
	
	/**
	 * Initialize Elementor integration
	 * 
	 * Called only if Elementor is active
	 * 
	 * @since 1.0.4.0
	 */
	public static function init() {
		self::log( '[ChurchTools Elementor] Integration init() called' );
		
		// Register hooks
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ], 10, 1 );
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ], 10, 1 );
		self::log( '[ChurchTools Elementor] Hooks registered' );
	}
	
	/**
	 * Register widget category
	 * 
	 * @param \Elementor\Elements_Manager $elements_manager
	 * @since 1.0.4.0
	 */
	public static function register_category( $elements_manager ) {
		self::log( '[ChurchTools Elementor] register_category() called' );
		$elements_manager->add_category(
			'churchtools-suite',
			[
				'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
				'icon' => 'fa fa-calendar-alt',
			]
		);
		self::log( '[ChurchTools Elementor] Category registered: churchtools-suite' );
	}
	
	/**
	 * Register widget
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @since 1.0.4.0
	 */
	public static function register_widget( $widgets_manager ) {
		self::log( '[ChurchTools Elementor] register_widget() called' );

		// Ensure base class is loaded
		self::log( '[ChurchTools Elementor] Widget_Base exists: ' . ( class_exists( '\\Elementor\\Widget_Base' ) ? 'YES' : 'NO' ) );
		if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
			self::log( '[ChurchTools Elementor] Widget_Base missing, aborting registration' );
			return;
		}

		// Load widget class lazily here when Elementor is ready
		if ( ! class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
			$widget_path = CHURCHTOOLS_SUITE_PATH . 'includes/elementor/class-churchtools-suite-elementor-events-widget.php';
			self::log( '[ChurchTools Elementor] Loading widget file in register_widget: ' . $widget_path . ' exists: ' . ( file_exists( $widget_path ) ? 'YES' : 'NO' ) );
			require_once $widget_path;
		}

		self::log( '[ChurchTools Elementor] Widget class exists (after load): ' . ( class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ? 'YES' : 'NO' ) );
		
		if ( class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
			try {
				$widget = new ChurchTools_Suite_Elementor_Events_Widget();
				$widgets_manager->register( $widget );
				self::log( '[ChurchTools Elementor] Widget registered successfully: ' . $widget->get_name() );
			} catch ( Exception $e ) {
				self::log( '[ChurchTools Elementor] ERROR registering widget: ' . $e->getMessage() );
			}
		}
	}
}

// Initialize immediately - Elementor is already loaded when this file is included
ChurchTools_Suite_Elementor_Integration::log( '[ChurchTools Elementor] Integration file loaded' );
ChurchTools_Suite_Elementor_Integration::log( '[ChurchTools Elementor] elementor/loaded action fired: ' . ( did_action( 'elementor/loaded' ) ? 'YES' : 'NO' ) );
ChurchTools_Suite_Elementor_Integration::log( '[ChurchTools Elementor] Elementor class exists: ' . ( class_exists( '\\Elementor\\Plugin' ) ? 'YES' : 'NO' ) );

// Always init immediately since we're loaded after Elementor
ChurchTools_Suite_Elementor_Integration::init();
