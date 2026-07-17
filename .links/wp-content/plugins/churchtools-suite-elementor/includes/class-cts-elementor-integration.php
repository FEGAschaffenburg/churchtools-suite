<?php
/**
 * Elementor Integration for ChurchTools Suite
 * 
 * Handles Elementor widget registration.
 * This file is only loaded if Elementor is active.
 *
 * @package ChurchTools_Suite_Elementor
 * @since   0.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Elementor_Integration {

	/**
	 * Prevent multiple initialization
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Log helper (persists to option + error_log)
	 */
	public static function log( string $message ): void {
		$line = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message;
		error_log( $line );
		$log = get_option( 'cts_elementor_log', [] );
		if ( ! is_array( $log ) ) {
			$log = [];
		}
		$log[] = $line;
		// Keep only last 50 entries
		if ( count( $log ) > 50 ) {
			$log = array_slice( $log, -50 );
		}
		update_option( 'cts_elementor_log', $log, false );
	}
	
	/**
	 * Initialize Elementor integration
	 * 
	 * Called only if Elementor is active
	 * 
	 * @since 0.5.0
	 */
	public static function init() {
		// Prevent multiple initialization
		if ( self::$initialized ) {
			self::log( '[CTS Elementor] SKIPPED: Already initialized' );
			return;
		}
		
		self::$initialized = true;
		self::log( '[CTS Elementor] Integration init() called' );
		
		// Register widget hooks only (editor scripts registered earlier in main plugin file)
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'register_category' ], 10, 1 );
		add_action( 'elementor/widgets/register', [ __CLASS__, 'register_widget' ], 10, 1 );
		self::log( '[CTS Elementor] Hooks registered' );
	}
	
	/**
	 * Register widget category
	 * 
	 * @param \Elementor\Elements_Manager $elements_manager
	 * @since 0.5.0
	 */
	public static function register_category( $elements_manager ) {
		self::log( '[CTS Elementor] register_category() called' );
		$elements_manager->add_category(
			'churchtools-suite',
			[
				'title' => __( 'ChurchTools Suite', 'churchtools-suite' ),
				'icon' => 'fa fa-calendar-alt',
			]
		);
		self::log( '[CTS Elementor] Category registered: churchtools-suite' );
	}
	
	/**
	 * Register widget
	 * 
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @since 0.5.0
	 */
	public static function register_widget( $widgets_manager ) {
		self::log( '[CTS Elementor] register_widget() called' );

		// Ensure base class is loaded
		self::log( '[CTS Elementor] Widget_Base exists: ' . ( class_exists( '\\Elementor\\Widget_Base' ) ? 'YES' : 'NO' ) );
		if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
			self::log( '[CTS Elementor] Widget_Base missing, aborting registration' );
			return;
		}

		// Load widget class lazily here when Elementor is ready
		if ( ! class_exists( 'CTS_Elementor_Events_Widget' ) ) {
			$widget_path = CTS_ELEMENTOR_PATH . 'includes/class-cts-elementor-events-widget.php';
			self::log( '[CTS Elementor] Loading widget file: ' . $widget_path . ' exists: ' . ( file_exists( $widget_path ) ? 'YES' : 'NO' ) );
			require_once $widget_path;
		}

		self::log( '[CTS Elementor] Widget class exists (after load): ' . ( class_exists( 'CTS_Elementor_Events_Widget' ) ? 'YES' : 'NO' ) );
		
		if ( class_exists( 'CTS_Elementor_Events_Widget' ) ) {
			try {
				$widget = new CTS_Elementor_Events_Widget();
				$widgets_manager->register( $widget );
				self::log( '[CTS Elementor] Widget registered successfully: ' . $widget->get_name() );
			} catch ( Exception $e ) {
				self::log( '[CTS Elementor] ERROR registering widget: ' . $e->getMessage() );
			}
		}
	}
	
	/**
	 * Enqueue editor scripts
	 * 
	 * Loads events data for dynamic event_id control population
	 * 
	 * @since 0.6.1
	 */
	public static function enqueue_editor_scripts() {
		self::log( '[CTS Elementor] enqueue_editor_scripts() called' );
		
		// Load Events Repository
		if ( ! class_exists( 'ChurchTools_Suite_Events_Repository' ) ) {
			require_once WP_PLUGIN_DIR . '/churchtools-suite/includes/repositories/class-churchtools-suite-repository-base.php';
			require_once WP_PLUGIN_DIR . '/churchtools-suite/includes/repositories/class-churchtools-suite-events-repository.php';
		}
		
		// Load upcoming events (same as Gutenberg)
		$events_repo = new ChurchTools_Suite_Events_Repository();
		$upcoming_events = $events_repo->get_upcoming( 50 ); // Next 50 events
		
		$event_options = [
			[
				'label' => __( 'Nächstes Event (automatisch)', 'churchtools-suite' ),
				'value' => 0,
				'calendar_id' => '',
				'tags' => []
			]
		];
		
		if ( ! empty( $upcoming_events ) ) {
			foreach ( $upcoming_events as $event ) {
				$date_format = get_option( 'date_format', 'd.m.Y' );
				$event_date = '';
				
				if ( ! empty( $event->start_datetime ) ) {
					// Convert to timestamp for wp_date (which handles locale)
					$timestamp = strtotime( get_date_from_gmt( $event->start_datetime, 'Y-m-d H:i:s' ) );
					$event_date = ' (' . wp_date( $date_format, $timestamp ) . ')';
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
		
		// Register script
		wp_enqueue_script(
			'cts-elementor-editor',
			CTS_ELEMENTOR_URL . 'assets/js/elementor-editor.js',
			[ 'jquery', 'elementor-editor' ],
			CTS_ELEMENTOR_VERSION,
			true
		);
		
		// Localize events data
		wp_localize_script( 'cts-elementor-editor', 'ctsElementorData', [
			'events' => $event_options,
			'i18n' => [
				'automatic' => __( 'Nächstes Event (automatisch)', 'churchtools-suite' ),
			]
		] );
		
		self::log( '[CTS Elementor] Editor script enqueued with ' . count( $event_options ) . ' events' );
	}
}

// AJAX handler for clearing logs
add_action('wp_ajax_cts_elementor_clear_logs', function() {
	check_ajax_referer('cts_elementor_clear_logs', 'nonce');
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized');
	}
	delete_option('cts_elementor_log');
	wp_send_json_success();
});
