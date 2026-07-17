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
			// Load feature matrix
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/view-feature-matrix.php';
			
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

			// View Template - List
			$this->add_control(
				'view_list',
				[
					'label' => __( 'Template', 'churchtools-suite' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'classic' => __( 'Klassisch', 'churchtools-suite' ),
						'classic-modern' => __( 'Klassisch Modernisiert (Grid + BEM)', 'churchtools-suite' ),
						'classic-with-images' => __( 'Klassisch mit Bildern', 'churchtools-suite' ),
						'minimal' => __( 'Minimal', 'churchtools-suite' ),
						'modern' => __( 'Modern', 'churchtools-suite' ),
					],
					'default' => 'classic',
					'condition' => [ 'view_type' => 'list' ],
				]
			);

			// View Template - Grid
			$this->add_control(
				'view_grid',
				[
					'label' => __( 'Template', 'churchtools-suite' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'simple' => __( 'Einfach', 'churchtools-suite' ),
						'modern' => __( 'Modern', 'churchtools-suite' ),
					],
					'default' => 'simple',
					'condition' => [ 'view_type' => 'grid' ],
				]
			);

			// View Template - Calendar
			$this->add_control(
				'view_calendar',
				[
					'label' => __( 'Template', 'churchtools-suite' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'monthly-simple' => __( 'Monat (Simple)', 'churchtools-suite' ),
					],
					'default' => 'monthly-simple',
					'condition' => [ 'view_type' => 'calendar' ],
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
		// FILTER & ANZAHL SECTION (v1.0.6.0)
		// ========================================
		$this->start_controls_section(
			'filter_section',
			[
				'label' => __( 'Filter & Anzahl', 'churchtools-suite' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Tags FIRST (v1.0.6.0)
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

		// Calendars SECOND (v1.0.6.0)
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

		// Limit LAST (v1.0.6.0)
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
				'description' => __( 'Maximale Anzahl nach Filterung durch Tags und Kalender', 'churchtools-suite' ),
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
					'description' => __( '💡 Hinweis: Nicht alle Optionen werden von jeder View unterstützt. Minimal-View unterstützt z.B. keine Bilder, Services oder inline Location-Anzeige.', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Kalender-Views', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Kalender-Views', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Minimal, Kalender-Views', 'churchtools-suite' ),
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
					'description' => __( '✅ Wird von allen Views unterstützt', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Minimal, Grid-Simple, Kalender-Views', 'churchtools-suite' ),
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
					'description' => __( '✅ Nur unterstützt: Classic-with-Images, Grid-Views (Simple & Modern)', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Minimal (nur Popup), Kalender-Views', 'churchtools-suite' ),
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
					'description' => __( '✅ Nur unterstützt: Listen-Views (nicht Grid/Kalender)', 'churchtools-suite' ),
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
					'description' => __( '❌ Nicht unterstützt: Minimal, Kalender-Views', 'churchtools-suite' ),
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
				'label' => __( 'Style-Modus', 'churchtools-suite' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'plugin' => __( 'Plugin-Styles', 'churchtools-suite' ),
					'theme' => __( 'Theme-Styles', 'churchtools-suite' ),
					'custom' => __( 'Individuelle Styles', 'churchtools-suite' ),
				],
				'default' => 'plugin',
				'description' => __( 'Plugin-Styles = eigene Farbpalette, Theme-Styles = Theme-Farben (inherit), Individuelle Styles = eigene Farben definieren', 'churchtools-suite' ),
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
		$settings = $this->get_settings_for_display();

		// If a single event is requested via URL, render single view
		$event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
		if ( $event_id > 0 ) {
			$event_action = isset( $settings['event_action'] ) ? $settings['event_action'] : 'modal';
			// Only switch to page view when configured for page navigation
			if ( $event_action === 'page' ) {
				echo do_shortcode( '[cts_event id="' . $event_id . '"]' );
				return;
			}
		}

		// Determine selected view based on type
		$view_type = $settings['view_type'] ?? 'list';
		$selected_view = null;

		if ( $view_type === 'list' && ! empty( $settings['view_list'] ) ) {
			$selected_view = $settings['view_list'];
		} elseif ( $view_type === 'grid' && ! empty( $settings['view_grid'] ) ) {
			$selected_view = $settings['view_grid'];
		} elseif ( $view_type === 'calendar' && ! empty( $settings['view_calendar'] ) ) {
			$selected_view = $settings['view_calendar'];
		} else {
			// Fallback für alte Widgets
			$selected_view = $settings['view'] ?? 'classic';
		}

		// Build shortcode attributes
		$atts = [
			'view' => $selected_view,
			'show_event_description' => ( isset($settings['show_event_description']) && $settings['show_event_description'] === 'yes' ) ? '1' : '0',
			'show_appointment_description' => ( isset($settings['show_appointment_description']) && $settings['show_appointment_description'] === 'yes' ) ? '1' : '0',
			'show_location' => ( isset($settings['show_location']) && $settings['show_location'] === 'yes' ) ? '1' : '0',
			'show_time' => ( isset($settings['show_time']) && $settings['show_time'] === 'yes' ) ? '1' : '0',
			'show_tags' => ( isset($settings['show_tags']) && $settings['show_tags'] === 'yes' ) ? '1' : '0',
			'show_images' => ( isset($settings['show_images']) && $settings['show_images'] === 'yes' ) ? '1' : '0',
			'show_calendar_name' => ( isset($settings['show_calendar_name']) && $settings['show_calendar_name'] === 'yes' ) ? '1' : '0',
			'show_services' => ( isset($settings['show_services']) && $settings['show_services'] === 'yes' ) ? '1' : '0',
			'show_past_events' => ( isset($settings['show_past_events']) && $settings['show_past_events'] === 'yes' ) ? '1' : '0',
			'show_month_separator' => ( isset($settings['show_month_separator']) && $settings['show_month_separator'] === 'yes' ) ? '1' : '0',
			'event_action' => isset( $settings['event_action'] ) ? $settings['event_action'] : 'modal',
			'style_mode' => $settings['style_mode'] ?? 'theme',
			'use_calendar_colors' => ( isset($settings['use_calendar_colors']) && $settings['use_calendar_colors'] === 'yes' ) ? '1' : '0',
			'custom_primary_color' => $settings['custom_primary_color'] ?? '#2563eb',
			'custom_text_color' => $settings['custom_text_color'] ?? '#1e293b',
			'custom_background_color' => $settings['custom_background_color'] ?? '#ffffff',
			'custom_border_radius' => $settings['custom_border_radius'] ?? 6,
			'custom_font_size' => $settings['custom_font_size'] ?? 14,
			'custom_padding' => $settings['custom_padding'] ?? 12,
			'custom_spacing' => $settings['custom_spacing'] ?? 8,
		];

		// Add limit for non-calendar views
		if ( $view_type !== 'calendar' ) {
			$atts['limit'] = $settings['limit'] ?? 5;
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

		if ( $view_type === 'grid' ) {
			$shortcode_tag = 'cts_grid';
			$atts['columns'] = $settings['columns'] ?? 3;
		} elseif ( $view_type === 'calendar' ) {
			$shortcode_tag = 'cts_calendar';
		}

		// Execute shortcode
		echo do_shortcode( '[' . $shortcode_tag . ' ' . $this->build_shortcode_atts( $atts ) . ']' );
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
			// v1.0.8.0: Use factory
			$repo = churchtools_suite_get_repository( 'calendars' );
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
			// v1.0.8.0: Use factory
			$repo = churchtools_suite_get_repository( 'events' );

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
