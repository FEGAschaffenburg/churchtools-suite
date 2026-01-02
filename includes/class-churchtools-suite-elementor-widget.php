<?php
/**
 * Elementor Widget: ChurchTools Events
 * 
 * @package ChurchTools_Suite
 * @since   0.5.9.38
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Elementor_Widget extends \Elementor\Widget_Base {
	
	/**
	 * Get widget name
	 */
	public function get_name() {
		return 'churchtools-events';
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
		return [ 'churchtools-suite' ];
	}
	
	/**
	 * Get widget keywords
	 */
	public function get_keywords() {
		return [ 'calendar', 'kalender', 'events', 'termine', 'churchtools' ];
	}
	
	/**
	 * Register widget controls
	 */
	protected function register_controls() {
		
		// Load presets
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
		$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();
		$all_presets = $presets_repo->get_all_presets();
		
		// Group presets by type
		$list_presets = [];
		$calendar_presets = [];
		$grid_presets = [];
		
		// Standard-Views die NICHT in Preset-Modus angezeigt werden sollen
		$standard_views = [
			'list'     => [ 'classic', 'medium' ],
			'calendar' => [ 'monthly-modern' ],
			'grid'     => [ 'simple' ],
		];
		
		foreach ( $all_presets as $preset ) {
			if ( ! isset( $preset['configuration']['view'] ) ) {
				continue;
			}
			
			$value = $preset['configuration']['view'];
			
			// Skip Standard-Views
			if ( $preset['shortcode_tag'] === 'cts_list' && in_array( $value, $standard_views['list'], true ) ) {
				continue;
			}
			if ( $preset['shortcode_tag'] === 'cts_calendar' && in_array( $value, $standard_views['calendar'], true ) ) {
				continue;
			}
			if ( $preset['shortcode_tag'] === 'cts_grid' && in_array( $value, $standard_views['grid'], true ) ) {
				continue;
			}
			
			$label = $preset['name'] . ( $preset['is_system'] ? ' 🔒' : ' ⭐' );
			
			if ( $preset['shortcode_tag'] === 'cts_list' ) {
				$list_presets[ $value ] = $label;
			} elseif ( $preset['shortcode_tag'] === 'cts_calendar' ) {
				$calendar_presets[ $value ] = $label;
			} elseif ( $preset['shortcode_tag'] === 'cts_grid' ) {
				$grid_presets[ $value ] = $label;
			}
		}
		
		// === SECTION: Ansicht ===
		$this->start_controls_section(
			'view_section',
			[
				'label' => __( '📋 Ansicht', 'churchtools-suite' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		
		// View Mode (Standard oder Preset)
		$this->add_control(
			'view_mode',
			[
				'label'   => __( 'Ansichts-Modus', 'churchtools-suite' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => [
					'standard' => __( '⚙️ Standard-Views (anpassbar)', 'churchtools-suite' ),
					'preset'   => __( '⭐ Eigene Presets (über Manager)', 'churchtools-suite' ),
				],
			]
		);
		
		// View Type - einheitliches Control für beide Modi
		// Options werden dynamisch basierend auf view_mode gefiltert
		$this->add_control(
			'view_type',
			[
				'label'   => __( 'Ansichtstyp', 'churchtools-suite' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'list',
				'options' => [
					'list'      => __( '📋 Liste', 'churchtools-suite' ),
					'calendar'  => __( '📅 Kalender', 'churchtools-suite' ),
					'grid'      => __( '▦ Raster', 'churchtools-suite' ),
					'search'    => __( '🔍 Suche', 'churchtools-suite' ),
					'widget'    => __( '📱 Widget', 'churchtools-suite' ),
					'slider'    => __( '🎬 Slider', 'churchtools-suite' ),
					'masonry'   => __( '🧱 Masonry', 'churchtools-suite' ),
					'agenda'    => __( '📒 Agenda', 'churchtools-suite' ),
					'timetable' => __( '🏢 Timetable', 'churchtools-suite' ),
					'carousel'  => __( '🎠 Carousel', 'churchtools-suite' ),
					'countdown' => __( '⏱️ Countdown', 'churchtools-suite' ),
					'cover'     => __( '🏞️ Cover', 'churchtools-suite' ),
					'map'       => __( '🗺️ Karte', 'churchtools-suite' ),
				],
			]
		);
		
		// List Views (nur Standard)
		$list_options = [
			'classic'          => __( 'Classic', 'churchtools-suite' ),
			'classic-services' => __( 'Classic Services', 'churchtools-suite' ),
			'modern'           => __( 'Modern', 'churchtools-suite' ),
			'medium'           => __( 'Medium', 'churchtools-suite' ),
			'fluent'           => __( 'Fluent', 'churchtools-suite' ),
			'compact'          => __( 'Compact', 'churchtools-suite' ),
		];
		
		$this->add_control(
			'list_view',
			[
				'label'      => __( 'Listen-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => $list_options,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'list',
						],
					],
				],
			]
		);
		
		// List Presets (nur Preset-Modus)
		if ( ! empty( $list_presets ) ) {
			$this->add_control(
				'list_preset',
				[
					'label'      => __( 'Listen-Preset', 'churchtools-suite' ),
					'type'       => \Elementor\Controls_Manager::SELECT,
					'default'    => array_key_first( $list_presets ),
					'options'    => $list_presets,
					'conditions' => [
						'relation' => 'and',
						'terms'    => [
							[
								'name'     => 'view_mode',
								'operator' => '===',
								'value'    => 'preset',
							],
							[
								'name'     => 'view_type',
								'operator' => '===',
								'value'    => 'list',
							],
						],
					],
				]
			);
		}
		
		// Calendar Views (nur Standard)
		$calendar_options = [
			'monthly-modern' => __( 'Monthly Modern', 'churchtools-suite' ),
		];
		
		$this->add_control(
			'calendar_view',
			[
				'label'      => __( 'Kalender-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'monthly-modern',
				'options'    => $calendar_options,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'calendar',
						],
					],
				],
			]
		);
		
		// Calendar Presets (nur Preset-Modus)
		if ( ! empty( $calendar_presets ) ) {
			$this->add_control(
				'calendar_preset',
				[
					'label'      => __( 'Kalender-Preset', 'churchtools-suite' ),
					'type'       => \Elementor\Controls_Manager::SELECT,
					'default'    => array_key_first( $calendar_presets ),
					'options'    => $calendar_presets,
					'conditions' => [
						'relation' => 'and',
						'terms'    => [
							[
								'name'     => 'view_mode',
								'operator' => '===',
								'value'    => 'preset',
							],
							[
								'name'     => 'view_type',
								'operator' => '===',
								'value'    => 'calendar',
							],
						],
					],
				]
			);
		}
		
		// Grid Views (nur Standard)
		$grid_options = [
			'simple'   => __( 'Simple', 'churchtools-suite' ),
			'colorful' => __( 'Colorful', 'churchtools-suite' ),
			'modern'   => __( 'Modern', 'churchtools-suite' ),
		];
		
		$this->add_control(
			'grid_view',
			[
				'label'      => __( 'Raster-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'simple',
				'options'    => $grid_options,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'grid',
						],
					],
				],
			]
		);
		
		// Grid Presets (nur Preset-Modus)
		if ( ! empty( $grid_presets ) ) {
			$this->add_control(
				'grid_preset',
				[
					'label'      => __( 'Raster-Preset', 'churchtools-suite' ),
					'type'       => \Elementor\Controls_Manager::SELECT,
					'default'    => array_key_first( $grid_presets ),
					'options'    => $grid_presets,
					'conditions' => [
						'relation' => 'and',
						'terms'    => [
							[
								'name'     => 'view_mode',
								'operator' => '===',
								'value'    => 'preset',
							],
							[
								'name'     => 'view_type',
								'operator' => '===',
								'value'    => 'grid',
							],
						],
					],
				]
			);
		}
		
		// Search Views (nur Standard - nur eine Variante)
		$this->add_control(
			'search_view',
			[
				'label'      => __( 'Suche-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'search',
						],
					],
				],
			]
		);
		
		// Widget Views (nur Standard - nur eine Variante)
		$this->add_control(
			'widget_view',
			[
				'label'      => __( 'Widget-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'upcoming',
				'options'    => [
					'upcoming' => __( 'Upcoming', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'widget',
						],
					],
				],
			]
		);
		
		// Slider Views (nur Standard - nur eine Variante)
		$this->add_control(
			'slider_view',
			[
				'label'      => __( 'Slider-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'slider',
						],
					],
				],
			]
		);
		
		// Masonry Views (nur Standard - nur eine Variante)
		$this->add_control(
			'masonry_view',
			[
				'label'      => __( 'Masonry-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'masonry',
						],
					],
				],
			]
		);
		
		// Agenda Views (nur Standard - nur eine Variante)
		$this->add_control(
			'agenda_view',
			[
				'label'      => __( 'Agenda-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'agenda',
						],
					],
				],
			]
		);
		
		// Timetable Views (nur Standard - nur eine Variante)
		$this->add_control(
			'timetable_view',
			[
				'label'      => __( 'Timetable-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'timetable',
						],
					],
				],
			]
		);
		
		// Carousel Views (nur Standard - nur eine Variante)
		$this->add_control(
			'carousel_view',
			[
				'label'      => __( 'Carousel-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'carousel',
						],
					],
				],
			]
		);
		
		// Countdown Views (nur Standard - nur eine Variante)
		$this->add_control(
			'countdown_view',
			[
				'label'      => __( 'Countdown-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'countdown',
						],
					],
				],
			]
		);
		
		// Cover Views (nur Standard - nur eine Variante)
		$this->add_control(
			'cover_view',
			[
				'label'      => __( 'Cover-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'cover',
						],
					],
				],
			]
		);
		
		// Map Views (nur Standard - nur eine Variante)
		$this->add_control(
			'map_view',
			[
				'label'      => __( 'Karten-Variante', 'churchtools-suite' ),
				'type'       => \Elementor\Controls_Manager::SELECT,
				'default'    => 'classic',
				'options'    => [
					'classic' => __( 'Classic', 'churchtools-suite' ),
				],
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'map',
						],
					],
				],
			]
		);
		
		// Calendar Selection (nur bei Standard-Modus)
		$calendars = $this->get_calendars();
		if ( ! empty( $calendars ) ) {
			foreach ( $calendars as $calendar_id => $calendar_name ) {
				$this->add_control(
					'calendar_' . $calendar_id,
					[
						'label'        => $calendar_name,
						'type'         => \Elementor\Controls_Manager::SWITCHER,
						'label_on'     => __( 'Ja', 'churchtools-suite' ),
						'label_off'    => __( 'Nein', 'churchtools-suite' ),
						'return_value' => 'yes',
						'default'      => '',
					]
				);
			}
			
			$this->add_control(
				'calendar_note',
				[
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => '<p style="font-size: 12px; color: #757575; margin-top: 8px;">' . __( 'Keine Auswahl = alle Kalender', 'churchtools-suite' ) . '</p>',
					'content_classes' => 'elementor-descriptor',
				]
			);
		}
		
		$this->end_controls_section();
		
		// === SECTION: Basis-Einstellungen ===
		$this->start_controls_section(
			'basic_section',
			[
				'label'     => __( '⚙️ Basis-Einstellungen', 'churchtools-suite' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'view_mode' => 'standard',
				],
			]
		);
		
		// Preset-Modus Hinweis
		$this->add_control(
			'preset_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="padding: 12px; background: #e0f2fe; border: 1px solid #0284c7; border-radius: 4px; font-size: 13px; color: #0c4a6e;">
					<strong>⚙️ Preset-Modus</strong><br>
					Einstellungen über Shortcode-Manager ändern. Individuelle Widget-Einstellungen sind deaktiviert.
				</div>',
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [
					'view_mode' => 'preset',
				],
			]
		);
		
		// Sprint 1: Limit (nur List & Grid)
		$this->add_control(
			'limit',
			[
				'label'       => __( 'Anzahl Termine', 'churchtools-suite' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 100,
				'step'        => 1,
				'default'     => 5,
				'description' => __( 'Maximale Anzahl der angezeigten Termine', 'churchtools-suite' ),
				'condition'   => [
					'view_type' => [ 'list', 'grid' ],
				],
			]
		);
		
		$this->end_controls_section();
		
		// === SECTION: Was anzeigen? ===
		$this->start_controls_section(
			'display_section',
			[
				'label'     => __( '👁️ Was anzeigen?', 'churchtools-suite' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'view_mode' => 'standard',
				],
			]
		);
		
		// Sprint 1: Beschreibung anzeigen
		$this->add_control(
			'show_description',
			[
				'label'        => __( 'Beschreibung anzeigen', 'churchtools-suite' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Ja', 'churchtools-suite' ),
				'label_off'    => __( 'Nein', 'churchtools-suite' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Event-Beschreibung unter dem Titel anzeigen', 'churchtools-suite' ),
			]
		);
		
		// Sprint 1: Ort anzeigen
		$this->add_control(
			'show_location',
			[
				'label'        => __( 'Ort anzeigen', 'churchtools-suite' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Ja', 'churchtools-suite' ),
				'label_off'    => __( 'Nein', 'churchtools-suite' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Veranstaltungsort unter dem Event anzeigen', 'churchtools-suite' ),
			]
		);
		
		// Sprint 3: Services anzeigen
		$this->add_control(
			'show_services',
			[
				'label'        => __( 'Services anzeigen', 'churchtools-suite' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Ja', 'churchtools-suite' ),
				'label_off'    => __( 'Nein', 'churchtools-suite' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Event-Services und Zuordnungen anzeigen', 'churchtools-suite' ),
			]
		);
		
		// Sprint 3: Kalender-Name anzeigen
		$this->add_control(
			'show_calendar_name',
			[
				'label'        => __( 'Kalender-Name anzeigen', 'churchtools-suite' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Ja', 'churchtools-suite' ),
				'label_off'    => __( 'Nein', 'churchtools-suite' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'Name des Kalenders anzeigen', 'churchtools-suite' ),
			]
		);
		
		// Sprint 3: Uhrzeit anzeigen
		$this->add_control(
			'show_time',
			[
				'label'        => __( 'Uhrzeit anzeigen', 'churchtools-suite' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Ja', 'churchtools-suite' ),
				'label_off'    => __( 'Nein', 'churchtools-suite' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Start- und Endzeit des Termins anzeigen', 'churchtools-suite' ),
			]
		);
		
		$this->end_controls_section();
		
		// === SECTION: Filter & Sortierung ===
		$this->start_controls_section(
			'filter_section',
			[
				'label'     => __( '🔍 Filter & Sortierung', 'churchtools-suite' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'view_mode' => 'standard',
				],
			]
		);
		
		// Sprint 4: Sortierung
		$this->add_control(
			'order',
			[
				'label'       => __( 'Sortierung', 'churchtools-suite' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'asc',
				'options'     => [
					'asc'  => __( 'Aufsteigend (älteste zuerst)', 'churchtools-suite' ),
					'desc' => __( 'Absteigend (neueste zuerst)', 'churchtools-suite' ),
				],
				'description' => __( 'Reihenfolge der Events nach Datum', 'churchtools-suite' ),
			]
		);
		
		// Sprint 4: Datum Von
		$this->add_control(
			'date_from',
			[
				'label'       => __( 'Datum von', 'churchtools-suite' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => [
					'enableTime' => false,
					'dateFormat' => 'Y-m-d',
				],
				'description' => __( 'Start-Datum für Filter', 'churchtools-suite' ),
			]
		);
		
		// Sprint 4: Datum Bis
		$this->add_control(
			'date_to',
			[
				'label'       => __( 'Datum bis', 'churchtools-suite' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => [
					'enableTime' => false,
					'dateFormat' => 'Y-m-d',
				],
				'description' => __( 'End-Datum für Filter', 'churchtools-suite' ),
			]
		);
		
		$this->end_controls_section();
		
		// === SECTION: Layout-Optionen ===
		$this->start_controls_section(
			'layout_section',
			[
				'label'     => __( '🎨 Layout-Optionen', 'churchtools-suite' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'view_mode',
							'operator' => '===',
							'value'    => 'standard',
						],
						[
							'name'     => 'view_type',
							'operator' => '===',
							'value'    => 'grid',
						],
					],
				],
			]
		);
		
		// Sprint 2: Spalten (nur Grid)
		$this->add_control(
			'columns',
			[
				'label'       => __( 'Spalten', 'churchtools-suite' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 4,
				'step'        => 1,
				'default'     => 3,
				'description' => __( 'Anzahl der Spalten im Raster (1-4)', 'churchtools-suite' ),
			]
		);
		
		$this->end_controls_section();
	}
	
	/**
	 * Get available calendars
	 */
	private function get_calendars() {
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
		
		$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
		$calendars = $calendars_repo->get_all();
		
		$options = [];
		foreach ( $calendars as $calendar ) {
			$options[ $calendar->calendar_id ] = $calendar->name;
		}
		
		return $options;
	}
	
	/**
	 * Render widget output
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// Fallback für alte Widgets ohne view_mode (vor v0.5.11.x)
		$view_mode = ! empty( $settings['view_mode'] ) ? $settings['view_mode'] : 'standard';
		$view_type = $settings['view_type'];
		$view = '';
		
		if ( $view_mode === 'preset' ) {
			// Use preset view
			switch ( $view_type ) {
				case 'calendar':
					$view = ! empty( $settings['calendar_preset'] ) ? $settings['calendar_preset'] : '';
					break;
				case 'grid':
					$view = ! empty( $settings['grid_preset'] ) ? $settings['grid_preset'] : '';
					break;
				case 'list':
				default:
					$view = ! empty( $settings['list_preset'] ) ? $settings['list_preset'] : '';
					break;
			}
		} else {
			// Use standard view
			switch ( $view_type ) {
				case 'calendar':
					$view = $settings['calendar_view'];
					break;
				case 'grid':
					$view = $settings['grid_view'];
					break;
				case 'search':
					$view = $settings['search_view'];
					break;
				case 'widget':
					$view = $settings['widget_view'];
					break;
				case 'slider':
					$view = $settings['slider_view'];
					break;
				case 'masonry':
					$view = $settings['masonry_view'];
					break;
				case 'agenda':
					$view = $settings['agenda_view'];
					break;
				case 'timetable':
					$view = $settings['timetable_view'];
					break;
				case 'carousel':
					$view = $settings['carousel_view'];
					break;
				case 'countdown':
					$view = $settings['countdown_view'];
					break;
				case 'cover':
					$view = $settings['cover_view'];
					break;
				case 'map':
					$view = $settings['map_view'];
					break;
				case 'list':
				default:
					$view = $settings['list_view'];
					break;
			}
		}
		
		// Collect selected calendar IDs from individual switchers (nur bei Standard-Modus)
		$calendars = $this->get_calendars();
		$selected_calendar_ids = [];
		
		if ( $view_mode === 'standard' ) {
			foreach ( array_keys( $calendars ) as $calendar_id ) {
				if ( ! empty( $settings[ 'calendar_' . $calendar_id ] ) && $settings[ 'calendar_' . $calendar_id ] === 'yes' ) {
					$selected_calendar_ids[] = $calendar_id;
				}
			}
		}
		
		// Build attributes for shortcode
		$atts = [
			'view' => $view,
		];
		
		// Add calendar selection nur bei Standard-Modus
		if ( $view_mode === 'standard' ) {
			$atts['calendar'] = ! empty( $selected_calendar_ids ) ? implode( ',', $selected_calendar_ids ) : '';
			
			// Sprint 1: Basis-Parameter hinzufügen
			if ( isset( $settings['limit'] ) ) {
				$atts['limit'] = absint( $settings['limit'] );
			}
			
			// Sprint 1: Anzeige-Parameter
			$atts['show_description'] = ( ! empty( $settings['show_description'] ) && $settings['show_description'] === 'yes' );
			$atts['show_location'] = ( ! empty( $settings['show_location'] ) && $settings['show_location'] === 'yes' );
			
			// Sprint 3: Weitere Anzeige-Parameter
			$atts['show_services'] = ( ! empty( $settings['show_services'] ) && $settings['show_services'] === 'yes' );
			$atts['show_calendar_name'] = ( ! empty( $settings['show_calendar_name'] ) && $settings['show_calendar_name'] === 'yes' );
			$atts['show_time'] = ( ! empty( $settings['show_time'] ) && $settings['show_time'] === 'yes' );
			
			// Sprint 4: Filter-Parameter
			if ( isset( $settings['order'] ) ) {
				$atts['order'] = sanitize_text_field( $settings['order'] );
			}
			if ( ! empty( $settings['date_from'] ) ) {
				$atts['date_from'] = sanitize_text_field( $settings['date_from'] );
			}
			if ( ! empty( $settings['date_to'] ) ) {
				$atts['date_to'] = sanitize_text_field( $settings['date_to'] );
			}
			
			// Sprint 2: Layout-Parameter hinzufügen
			if ( isset( $settings['columns'] ) ) {
				$atts['columns'] = absint( $settings['columns'] );
			}
			
		}
		
		// Call appropriate shortcode
		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-shortcodes.php';
		
		switch ( $view_type ) {
			case 'calendar':
				echo ChurchTools_Suite_Shortcodes::calendar_shortcode( $atts );
				break;
			case 'grid':
				echo ChurchTools_Suite_Shortcodes::grid_shortcode( $atts );
				break;
			case 'search':
				echo ChurchTools_Suite_Shortcodes::search_shortcode( $atts );
				break;
			case 'widget':
				echo ChurchTools_Suite_Shortcodes::widget_shortcode( $atts );
				break;
			case 'slider':
				echo ChurchTools_Suite_Shortcodes::slider_shortcode( $atts );
				break;
			case 'masonry':
				echo ChurchTools_Suite_Shortcodes::masonry_shortcode( $atts );
				break;
			case 'agenda':
				echo ChurchTools_Suite_Shortcodes::agenda_shortcode( $atts );
				break;
			case 'timetable':
				echo ChurchTools_Suite_Shortcodes::timetable_shortcode( $atts );
				break;
			case 'carousel':
				echo ChurchTools_Suite_Shortcodes::carousel_shortcode( $atts );
				break;
			case 'countdown':
				echo ChurchTools_Suite_Shortcodes::countdown_shortcode( $atts );
				break;
			case 'cover':
				echo ChurchTools_Suite_Shortcodes::cover_shortcode( $atts );
				break;
			case 'map':
				echo ChurchTools_Suite_Shortcodes::map_shortcode( $atts );
				break;
			case 'list':
			default:
				echo ChurchTools_Suite_Shortcodes::list_shortcode( $atts );
				break;
		}
	}
}
