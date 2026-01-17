<?php
/**
 * Elementor Events Widget
 * 
 * Displays ChurchTools events in Elementor using the built-in shortcodes
 * Provides UI controls for all shortcode parameters
 *
 * @package ChurchTools_Suite
 * @since   1.0.3.18
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only define class if not already defined
if ( ! class_exists( 'ChurchTools_Suite_Elementor_Events_Widget' ) ) {
	
	// Check if Elementor Widget_Base exists, only define our class if it does
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		// Elementor not properly loaded, exit gracefully
		return;
	}

	class ChurchTools_Suite_Elementor_Events_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name
	 */
	public function get_name() {
		return 'churchtools_suite_events';
	}

	/**
	 * Get widget title
	 */
	public function get_title() {
		return __( 'ChurchTools Events', 'churchtools-suite' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon() {
		return 'eicon-calendar';
	}

	/**
	 * Get widget categories
	 */
	public function get_categories() {
		return [ 'basic', 'churchtools-suite' ];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls() {
		// ========================================
		// CONTENT SECTION
		// ========================================
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Inhalt', 'churchtools-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Event Action (Modal, Page, None)
		$this->add_control(
			'event_action',
			[
				'label' => __( 'Bei Event-Klick', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'modal' => __( 'Modal öffnen', 'churchtools-suite' ),
					'page' => __( 'Event-Seite öffnen', 'churchtools-suite' ),
					'none' => __( 'Nicht anklickbar', 'churchtools-suite' ),
				],
				'default' => 'modal',
				'description' => __( 'Modal = Popup-Fenster, Event-Seite = Eigene Seite mit URL-Parameter', 'churchtools-suite' ),
			]
		);

		// View Type (List/Grid/Calendar)
		$this->add_control(
			'view_type',
			[
				'label' => __( 'Ansichtstyp', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'list' => __( 'Liste', 'churchtools-suite' ),
					'grid' => __( 'Gitter', 'churchtools-suite' ),
					'calendar' => __( 'Kalender', 'churchtools-suite' ),
				],
				'default' => 'list',
				'description' => __( 'Wähle zwischen Listenansicht, Gitteransicht und Kalender', 'churchtools-suite' ),
			]
		);

		// View Template
		$this->add_control(
			'view',
			[
				'label' => __( 'Template', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					// List views
					'classic' => __( 'Klassisch', 'churchtools-suite' ),
					'classic-with-images' => __( 'Klassisch mit Bildern', 'churchtools-suite' ),
					'minimal' => __( 'Minimal', 'churchtools-suite' ),
					'modern' => __( 'Modern', 'churchtools-suite' ),
					'standard' => __( 'Standard', 'churchtools-suite' ),
					'toggle' => __( 'Toggle', 'churchtools-suite' ),
					'with-map' => __( 'Mit Karte', 'churchtools-suite' ),
					// Grid views
					'simple' => __( 'Einfach', 'churchtools-suite' ),
					'ocean' => __( 'Ocean', 'churchtools-suite' ),
					'colorful' => __( 'Farbig', 'churchtools-suite' ),
					'novel' => __( 'Novel', 'churchtools-suite' ),
					'tile' => __( 'Kachel', 'churchtools-suite' ),
					// Calendar views
					'monthly-simple' => __( 'Monat (Simple)', 'churchtools-suite' ),
				],
				'default' => 'classic',
			]
		);

		// Limit
		$this->add_control(
			'limit',
			[
				'label' => __( 'Anzahl Events', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5,
				'min' => 1,
				'max' => 100,
				'condition' => [
					'view_type!' => 'calendar',
				],
			]
		);

		// Columns (nur für Grid)
		$this->add_control(
			'columns',
			[
				'label' => __( 'Spalten', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
				'max' => 6,
				'condition' => [
					'view_type' => 'grid',
				],
			]
		);

		$this->end_controls_section();

		// ========================================
		// FILTER SECTION
		// ========================================
		$this->start_controls_section(
			'filter_section',
			[
				'label' => __( 'Filter', 'churchtools-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Calendars
		$this->add_control(
			'calendars',
			[
				'label' => __( 'Kalender', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_calendars_options(),
				'multiple' => true,
				'label_block' => true,
				'description' => __( 'Leer = alle ausgewählten Kalender', 'churchtools-suite' ),
			]
		);

		// Tags
		$this->add_control(
			'tags',
			[
				'label' => __( 'Tags', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_tags_options(),
				'multiple' => true,
				'label_block' => true,
				'description' => __( 'Leer = alle Tags', 'churchtools-suite' ),
			]
		);

		// Show past events
		$this->add_control(
			'show_past_events',
			[
				'label' => __( 'Vergangene Events anzeigen', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'no',
			]
		);

		$this->end_controls_section();

		// ========================================
		// DISPLAY OPTIONS SECTION
		// ========================================
		$this->start_controls_section(
			'display_section',
			[
				'label' => __( 'Anzeigeoptionen', 'churchtools-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_event_description',
			[
				'label' => __( 'Event-Beschreibung', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_appointment_description',
			[
				'label' => __( 'Termin-Beschreibung', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_location',
			[
				'label' => __( 'Ort', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_time',
			[
				'label' => __( 'Uhrzeit', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_tags',
			[
				'label' => __( 'Tags', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_images',
			[
				'label' => __( 'Bilder', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_calendar_name',
			[
				'label' => __( 'Kalendername', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_month_separator',
			[
				'label' => __( 'Monatstrenner', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'yes',
				'condition' => [
					'view_type' => 'list',
				],
			]
		);

		$this->add_control(
			'show_services',
			[
				'label' => __( 'Services', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'no',
			]
		);

		$this->end_controls_section();

		// ========================================
		// STYLE SECTION
		// ========================================
		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Stil', 'churchtools-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_mode',
			[
				'label' => __( 'Stil-Modus', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'theme' => __( 'Theme-Standard', 'churchtools-suite' ),
					'custom' => __( 'Benutzerdefiniert', 'churchtools-suite' ),
				],
				'default' => 'theme',
			]
		);

		$this->add_control(
			'use_calendar_colors',
			[
				'label' => __( 'Kalenderfarben verwenden', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Ja', 'churchtools-suite' ),
				'label_off' => __( 'Nein', 'churchtools-suite' ),
				'default' => 'no',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_primary_color',
			[
				'label' => __( 'Primärfarbe', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#2563eb',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_text_color',
			[
				'label' => __( 'Textfarbe', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#1e293b',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_background_color',
			[
				'label' => __( 'Hintergrundfarbe', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_border_radius',
			[
				'label' => __( 'Border Radius', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 0,
				'max' => 50,
				'unit' => 'px',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_font_size',
			[
				'label' => __( 'Schriftgröße', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 14,
				'min' => 10,
				'max' => 32,
				'unit' => 'px',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_padding',
			[
				'label' => __( 'Padding', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min' => 0,
				'max' => 50,
				'unit' => 'px',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_spacing',
			[
				'label' => __( 'Abstände', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 8,
				'min' => 0,
				'max' => 50,
				'unit' => 'px',
				'condition' => [
					'style_mode' => 'custom',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget
	 */
	protected function render() {
		$settings = $this->get_settings_all();

		// Build shortcode attributes
		$atts = [
			'view' => $settings['view'],
			'show_event_description' => $settings['show_event_description'] ? '1' : '0',
			'show_appointment_description' => $settings['show_appointment_description'] ? '1' : '0',
			'show_location' => $settings['show_location'] ? '1' : '0',
			'show_time' => $settings['show_time'] ? '1' : '0',
			'show_tags' => $settings['show_tags'] ? '1' : '0',
			'show_images' => $settings['show_images'] ? '1' : '0',
			'show_calendar_name' => $settings['show_calendar_name'] ? '1' : '0',
			'show_services' => $settings['show_services'] ? '1' : '0',
			'show_past_events' => $settings['show_past_events'] ? '1' : '0',
			'show_month_separator' => isset( $settings['show_month_separator'] ) ? ( $settings['show_month_separator'] ? '1' : '0' ) : '1',
			'event_action' => isset( $settings['event_action'] ) ? $settings['event_action'] : 'modal',
			'style_mode' => $settings['style_mode'],
			'use_calendar_colors' => $settings['use_calendar_colors'] ? '1' : '0',
			'custom_primary_color' => $settings['custom_primary_color'],
			'custom_text_color' => $settings['custom_text_color'],
			'custom_background_color' => $settings['custom_background_color'],
			'custom_border_radius' => $settings['custom_border_radius'],
			'custom_font_size' => $settings['custom_font_size'],
			'custom_padding' => $settings['custom_padding'],
			'custom_spacing' => $settings['custom_spacing'],
		];

		// Add limit for non-calendar views
		if ( $settings['view_type'] !== 'calendar' ) {
			$atts['limit'] = $settings['limit'];
		}

		// Add calendars filter if specified
		if ( ! empty( $settings['calendars'] ) ) {
			$atts['calendars'] = implode( ',', $settings['calendars'] );
		}

		// Add tags filter if specified
		if ( ! empty( $settings['tags'] ) ) {
			$atts['tags'] = implode( ',', $settings['tags'] );
		}

		// Determine shortcode tag based on view type
		$shortcode_tag = 'cts_list'; // Default

		if ( $settings['view_type'] === 'grid' ) {
			$shortcode_tag = 'cts_grid';
			$atts['columns'] = $settings['columns'];
		} elseif ( $settings['view_type'] === 'calendar' ) {
			$shortcode_tag = 'cts_calendar';
		}

		// Execute shortcode
		echo wp_kses_post( do_shortcode( '[' . $shortcode_tag . ' ' . $this->build_shortcode_atts( $atts ) . ']' ) );
	}

	/**
	 * Build shortcode attributes string
	 *
	 * @param array $atts Attributes array
	 * @return string Attributes string
	 */
	private function build_shortcode_atts( $atts ) {
		$output = '';
		foreach ( $atts as $key => $value ) {
			$output .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
		return $output;
	}

	/**
	 * Get calendars options
	 *
	 * @return array Calendar options
	 */
	private function get_calendars_options() {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

		$repo = new ChurchTools_Suite_Calendars_Repository();
		$calendars = $repo->get_all();

		$options = [];
		foreach ( $calendars as $calendar ) {
			$options[ $calendar->calendar_id ] = $calendar->name;
		}

		return $options;
	}

	/**
	 * Get tags options
	 *
	 * @return array Tags options
	 */
	private function get_tags_options() {
		// Tags werden dynamisch aus Events extrahiert
		// Für Elementor Dropdown-Vorschau können wir hier eine Liste gängiger Tags anbieten
		// oder sie von einer Methode abrufen

		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';

		$repo = new ChurchTools_Suite_Events_Repository();
		
		// Get all unique tags from database
		$all_events = $repo->get_all();
		$tags_set = [];

		foreach ( $all_events as $event ) {
			if ( ! empty( $event->tags ) ) {
				$tags_data = json_decode( $event->tags, true );
				if ( is_array( $tags_data ) ) {
					foreach ( $tags_data as $tag ) {
						if ( isset( $tag['name'] ) ) {
							$tags_set[ $tag['name'] ] = $tag['name'];
						}
					}
				}
			}
		}

		return $tags_set;
	}
}
}
