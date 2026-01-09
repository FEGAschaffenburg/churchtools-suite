<?php
/**
 * Single Event Shortcode Handler
 * 
 * Displays a single event with various templates.
 * Usage: [cts_event id="123" template="modern"]
 *
 * @package ChurchTools_Suite
 * @since   0.7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Single_Event_Shortcode {
	
	/**
	 * Register shortcode
	 */
	public static function register(): void {
		add_shortcode( 'cts_event', [ __CLASS__, 'render' ] );
	}
	
	/**
	 * Render single event shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public static function render( $atts ): string {
		// Get default template from settings (v0.9.9.43)
		// v0.9.9.x: "professional" ist das einzige aktive Template; alte Werte werden gemappt
		$default_template = get_option( 'churchtools_suite_single_template', 'professional' );
		
		$atts = shortcode_atts( [
			'id'       => 0,
			'template' => $default_template, // Use setting, fallback to validated template
		], $atts, 'cts_event' );
		
		// Validate event ID
		$event_id = absint( $atts['id'] );
		if ( ! $event_id ) {
			return '<div class="cts-error">' . __( 'Fehler: Keine Event-ID angegeben.', 'churchtools-suite' ) . '</div>';
		}
		
		// Load repositories
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
		
		$events_repo = new ChurchTools_Suite_Events_Repository();
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
		
		// Load event
		$event = $events_repo->get_by_id( $event_id );
		
		if ( ! $event ) {
			return '<div class="cts-error">' . __( 'Fehler: Event nicht gefunden.', 'churchtools-suite' ) . '</div>';
		}
		
		// Load calendar
		$calendar = null;
		if ( ! empty( $event->calendar_id ) ) {
			$calendar = $calendars_repo->get_by_calendar_id( $event->calendar_id );
		}
		
		// Load services
		$services = $event_services_repo->get_by_event_id( $event_id );
		
		// Enqueue styles
		self::enqueue_styles();
		
		// Load template
		$template = self::validate_template( $atts['template'] );
		return self::load_template( $template, [
			'event'    => $event,
			'calendar' => $calendar,
			'services' => $services,
		] );
	}
	
	/**
	 * Validate template name
	 *
	 * @param string $template Template name
	 * @return string Valid template name
	 */
	private static function validate_template( string $template ): string {
		// Nur "professional" ist aktiv; frühere Namen werden kompatibel gemappt
		$alias_map = [
			'professional' => 'professional',
			'modern' => 'professional',
			'classic' => 'professional',
			'minimal' => 'professional',
			'card' => 'professional',
		];

		return $alias_map[ $template ] ?? 'professional';
	}
	
	/**
	 * Load template file
	 *
	 * @param string $template Template name
	 * @param array $data Template data
	 * @return string Rendered HTML
	 */
	private static function load_template( string $template, array $data ): string {
		// Extract data for template
		extract( $data );
		
		// v0.9.9.44: Neue Template-Struktur (views/event-single/)
		// Check for theme override (mit Kompatibilität für alte Pfade)
		$theme_template = locate_template( "churchtools-suite/views/event-single/{$template}.php" );
		
		if ( ! $theme_template ) {
			// Fallback: Alte Struktur im Theme
			$theme_template = locate_template( "churchtools-suite/single/{$template}.php" );
		}
		
		if ( $theme_template ) {
			$template_path = $theme_template;
		} else {
			// v0.9.9.44: Neue Struktur
			$template_path = CHURCHTOOLS_SUITE_PATH . "templates/views/event-single/{$template}.php";
			
			// Fallback: Alte Struktur
			if ( ! file_exists( $template_path ) ) {
				$template_path = CHURCHTOOLS_SUITE_PATH . "templates/single/{$template}.php";
			}
		}
		
		// Check if template exists
		if ( ! file_exists( $template_path ) ) {
			return '<div class="cts-error">' . 
				sprintf( __( 'Fehler: Template "%s" nicht gefunden.', 'churchtools-suite' ), esc_html( $template ) ) . 
				'</div>';
		}
		
		// Capture output
		ob_start();
		include $template_path;
		return ob_get_clean();
	}
	
	/**
	 * Enqueue stylesheet
	 */
	private static function enqueue_styles(): void {
		static $enqueued = false;
		
		if ( $enqueued ) {
			return;
		}
		
		wp_enqueue_style(
			'churchtools-suite-single',
			CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-single.css',
			[],
			CHURCHTOOLS_SUITE_VERSION
		);
		
		$enqueued = true;
	}
}

// Register shortcode
ChurchTools_Suite_Single_Event_Shortcode::register();
