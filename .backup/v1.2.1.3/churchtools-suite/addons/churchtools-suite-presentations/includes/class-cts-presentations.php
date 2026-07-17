<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Presentations {
	const OPTION_EVENT_ID = 'cts_presentations_event_id';
	const OPTION_AUTO_CREATE = 'cts_presentations_auto_create';
	const OPTION_REQUIRE_BUILDER = 'cts_presentations_require_builder';
	const OPTION_PAGE_ID = 'cts_presentations_page_id';
	const OPTION_SPECIAL_TAGS = 'cts_presentations_special_tags';
	const OPTION_SLIDE_SECONDS = 'cts_presentations_slide_seconds';
	const OPTION_SLIDE_1_VIEW = 'cts_presentations_slide_1_view';
	const OPTION_SLIDE_2_VIEW = 'cts_presentations_slide_2_view';

	const META_ENABLED = '_cts_presentation_enabled';
	const META_EVENT_ID = '_cts_presentation_event_id';
	const META_SPECIAL_TAGS = '_cts_presentation_special_tags';
	const META_SLIDE_SECONDS = '_cts_presentation_slide_seconds';
	const META_SLIDE_1_VIEW = '_cts_presentation_slide_1_view';
	const META_SLIDE_2_VIEW = '_cts_presentation_slide_2_view';

	public static function init(): void {
		add_shortcode( 'cts_presentation', [ __CLASS__, 'render_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_assets' ] );
		add_filter( 'display_post_states', [ __CLASS__, 'add_post_state' ], 10, 2 );
	}

	public static function register_assets(): void {
		wp_register_style(
			'cts-presentations',
			CTS_PRESENTATIONS_URL . 'assets/css/cts-presentations.css',
			[],
			CTS_PRESENTATIONS_VERSION
		);

		wp_register_script(
			'cts-presentations',
			CTS_PRESENTATIONS_URL . 'assets/js/cts-presentations.js',
			[],
			CTS_PRESENTATIONS_VERSION,
			true
		);
	}

	public static function enqueue_assets( int $slide_seconds ): void {
		wp_enqueue_style( 'cts-presentations' );
		wp_enqueue_script( 'cts-presentations' );
		wp_localize_script(
			'cts-presentations',
			'ctsPresentationsConfig',
			[
				'defaultSeconds' => $slide_seconds,
			]
		);
	}

	public static function render_shortcode( array $atts = [] ): string {
		$page_id = get_the_ID();
		if ( isset( $atts['page_id'] ) && absint( $atts['page_id'] ) > 0 ) {
			$page_id = absint( $atts['page_id'] );
		}

		if ( ! $page_id ) {
			return '';
		}

		$enabled = (int) get_post_meta( $page_id, self::META_ENABLED, true );
		if ( $enabled !== 1 ) {
			return '';
		}

		$event_id = (int) get_post_meta( $page_id, self::META_EVENT_ID, true );
		$special_tags = (string) get_post_meta( $page_id, self::META_SPECIAL_TAGS, true );
		$slide_seconds = max( 3, (int) get_post_meta( $page_id, self::META_SLIDE_SECONDS, true ) );
		$slide_1_view = (string) get_post_meta( $page_id, self::META_SLIDE_1_VIEW, true );
		$slide_2_view = (string) get_post_meta( $page_id, self::META_SLIDE_2_VIEW, true );

		if ( $slide_1_view === '' ) {
			$slide_1_view = 'list-classic';
		}
		if ( $slide_2_view === '' ) {
			$slide_2_view = 'grid-modern';
		}

		self::enqueue_assets( $slide_seconds );

		return CTS_Presentations_Renderer::render_slider(
			[
				'event_id' => $event_id,
				'special_tags' => $special_tags,
				'slide_seconds' => $slide_seconds,
				'slide_1_view' => $slide_1_view,
				'slide_2_view' => $slide_2_view,
			]
		);
	}

	public static function get_upcoming_events_for_select( int $limit = 150 ): array {
		global $wpdb;
		$table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'events';

		$limit = max( 10, min( 500, $limit ) );
		$sql = $wpdb->prepare(
			"SELECT id, title, start_datetime FROM {$table} WHERE start_datetime >= %s ORDER BY start_datetime ASC LIMIT %d",
			current_time( 'mysql' ),
			$limit
		);

		$rows = $wpdb->get_results( $sql );
		if ( ! is_array( $rows ) ) {
			return [];
		}

		return $rows;
	}

	public static function get_view_options_flat(): array {
		$result = [];
		if ( ! class_exists( 'ChurchTools_Suite_Template_Loader' ) ) {
			return $result;
		}

		$types = ChurchTools_Suite_Template_Loader::get_view_types_options();
		foreach ( $types as $type ) {
			$value = isset( $type['value'] ) ? (string) $type['value'] : '';
			if ( $value === '' ) {
				continue;
			}
			$views = ChurchTools_Suite_Template_Loader::get_view_options( $value );
			foreach ( $views as $view ) {
				$view_value = isset( $view['value'] ) ? (string) $view['value'] : '';
				$label = isset( $view['label'] ) ? (string) $view['label'] : $view_value;
				if ( $view_value === '' ) {
					continue;
				}
				$result[ $view_value ] = $label . ' (' . $value . ')';
			}
		}

		ksort( $result );
		return $result;
	}

	public static function get_supported_page_builders(): array {
		$builders = [
			'elementor/elementor.php' => 'Elementor',
		];

		return apply_filters( 'cts_presentations_supported_builders', $builders );
	}

	public static function has_active_page_builder(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::get_supported_page_builders() as $plugin_file => $label ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return true;
			}
		}

		return false;
	}

	public static function get_active_page_builder_labels(): array {
		$active = [];
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::get_supported_page_builders() as $plugin_file => $label ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active[] = (string) $label;
			}
		}

		return $active;
	}

	public static function maybe_auto_create_page(): array {
		$auto_create = (int) get_option( self::OPTION_AUTO_CREATE, 0 ) === 1;
		if ( ! $auto_create ) {
			return [ 'ok' => false, 'message' => __( 'Automatische Erstellung ist deaktiviert.', 'churchtools-suite-presentations' ) ];
		}

		$require_builder = (int) get_option( self::OPTION_REQUIRE_BUILDER, 1 ) === 1;
		if ( $require_builder && ! self::has_active_page_builder() ) {
			return [ 'ok' => false, 'message' => __( 'Kein unterstützter Page Builder aktiv.', 'churchtools-suite-presentations' ) ];
		}

		return self::create_or_update_presentation_page();
	}

	public static function create_or_update_presentation_page(): array {
		$event_id = (int) get_option( self::OPTION_EVENT_ID, 0 );
		if ( $event_id <= 0 ) {
			return [ 'ok' => false, 'message' => __( 'Bitte zuerst einen Termin auswählen.', 'churchtools-suite-presentations' ) ];
		}

		$slide_seconds = max( 3, (int) get_option( self::OPTION_SLIDE_SECONDS, 10 ) );
		$special_tags = (string) get_option( self::OPTION_SPECIAL_TAGS, '' );
		$slide_1_view = (string) get_option( self::OPTION_SLIDE_1_VIEW, 'list-classic' );
		$slide_2_view = (string) get_option( self::OPTION_SLIDE_2_VIEW, 'grid-modern' );

		$title = sprintf( __( 'Präsentation Termin %d', 'churchtools-suite-presentations' ), $event_id );
		$existing_page_id = (int) get_option( self::OPTION_PAGE_ID, 0 );
		$page_data = [
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_title' => $title,
			'post_name' => 'cts-praesentation-' . $event_id,
			'post_content' => '[cts_presentation]',
		];

		if ( $existing_page_id > 0 && get_post( $existing_page_id ) instanceof WP_Post ) {
			$page_data['ID'] = $existing_page_id;
			$page_id = wp_update_post( $page_data, true );
		} else {
			$page_id = wp_insert_post( $page_data, true );
		}

		if ( is_wp_error( $page_id ) ) {
			return [ 'ok' => false, 'message' => $page_id->get_error_message() ];
		}

		$page_id = (int) $page_id;
		update_option( self::OPTION_PAGE_ID, $page_id, false );

		update_post_meta( $page_id, self::META_ENABLED, 1 );
		update_post_meta( $page_id, self::META_EVENT_ID, $event_id );
		update_post_meta( $page_id, self::META_SPECIAL_TAGS, $special_tags );
		update_post_meta( $page_id, self::META_SLIDE_SECONDS, $slide_seconds );
		update_post_meta( $page_id, self::META_SLIDE_1_VIEW, $slide_1_view );
		update_post_meta( $page_id, self::META_SLIDE_2_VIEW, $slide_2_view );

		return [
			'ok' => true,
			'page_id' => $page_id,
			'edit_url' => get_edit_post_link( $page_id, 'raw' ),
			'view_url' => get_permalink( $page_id ),
			'message' => __( 'Präsentations-Seite wurde erstellt/aktualisiert.', 'churchtools-suite-presentations' ),
		];
	}

	public static function add_post_state( array $post_states, WP_Post $post ): array {
		if ( $post->post_type !== 'page' ) {
			return $post_states;
		}

		$enabled = (int) get_post_meta( $post->ID, self::META_ENABLED, true );
		if ( $enabled === 1 ) {
			$post_states[] = __( 'ChurchTools Präsentation', 'churchtools-suite-presentations' );
		}

		return $post_states;
	}
}
