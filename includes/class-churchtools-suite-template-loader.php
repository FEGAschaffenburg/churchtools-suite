<?php
/**
 * Template Loader
 * 
 * Handles template loading with Theme override support.
 * Templates can be overridden in themes/your-theme/churchtools-suite/
 *
 * @package ChurchTools_Suite
 * @since   0.4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Template_Loader {
	
	/**
	 * Template directory in theme
	 */
	const THEME_TEMPLATE_DIR = 'churchtools-suite';
	
	/**
	 * Prüft, ob ein Template für einen View-Typ existiert
	 *
	 * @param string $view_type list|grid|calendar
	 * @param string $view      Template-Name
	 * @return bool
	 */
	public static function template_exists( string $view_type, string $view ): bool {
		$base = [
			'list' => 'views/event-list/',
			'grid' => 'views/event-grid/',
			'calendar' => 'views/event-calendar/',
		];

		if ( ! isset( $base[ $view_type ] ) ) {
			return false;
		}

		$tpl = $base[ $view_type ] . $view . '.php';
		return (bool) self::locate_template( $tpl );
	}

	/**
	 * Liefert die verfügbaren View-Typen (mit Labels) basierend auf Templates
	 *
	 * @return array[] Array von Options-Objekten: [ [label => '...', value => '...'], ... ]
	 */
	public static function get_view_types_options(): array {
		$types = [
			[ 'label' => __( 'Liste', 'churchtools-suite' ), 'value' => 'list' ],
			[ 'label' => __( 'Grid', 'churchtools-suite' ), 'value' => 'grid' ],
			[ 'label' => __( 'Kalender', 'churchtools-suite' ), 'value' => 'calendar' ],
		];

		$filtered = [];
		foreach ( $types as $opt ) {
			$views = self::get_view_options( $opt['value'] );
			if ( ! empty( $views ) ) {
				$filtered[] = $opt;
			}
		}

		return $filtered;
	}

	/**
	 * Liefert die verfügbaren Views (Templates) für einen Typ als Options-Liste
	 *
	 * @param string $view_type list|grid|calendar
	 * @return array[] Array von Options-Objekten: [ [label => '...', value => '...'], ... ]
	 */
	public static function get_view_options( string $view_type ): array {
		$labels = [
			'list' => [
				'classic' => __( 'Klassisch', 'churchtools-suite' ),
				'classic-with-images' => __( 'Klassisch mit Bildern', 'churchtools-suite' ),
				'minimal' => __( 'Minimal', 'churchtools-suite' ),
				'modern' => __( 'Modern', 'churchtools-suite' ),
			],
			'grid' => [
				'simple' => __( 'Einfach', 'churchtools-suite' ),
				'modern' => __( 'Modern', 'churchtools-suite' ),
			],
			'calendar' => [
				'monthly-simple' => __( 'Monat (Simple)', 'churchtools-suite' ),
			],
		];

		if ( ! isset( $labels[ $view_type ] ) ) {
			return [];
		}

		$options = [];
		foreach ( $labels[ $view_type ] as $view => $label ) {
			if ( self::template_exists( $view_type, $view ) ) {
				$options[] = [ 'label' => $label, 'value' => $view ];
			}
		}

		return $options;
	}

	/**
	 * Locate a template file
	 * 
	 * Checks in this order:
	 * 1. Theme: {theme}/churchtools-suite/{template}.php
	 * 2. Plugin: {plugin}/templates/{template}.php (with migration support)
	 *
	 * @param string $template_name Template name (e.g., 'calendar/monthly.php')
	 * @return string|false Full path to template file or false
	 */
	public static function locate_template( string $template_name ) {
		// v0.9.9.44: Template-Pfad-Migration (Kompatibilitäts-Layer)
		$template_name = self::migrate_template_path( $template_name );
		
		// Load logger for debugging
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'template_loader', 'Locating template START', [
				'template_name' => $template_name,
				'churchtools_suite_path' => CHURCHTOOLS_SUITE_PATH,
				'churchtools_suite_path_length' => strlen( CHURCHTOOLS_SUITE_PATH ),
				'churchtools_suite_path_defined' => defined( 'CHURCHTOOLS_SUITE_PATH' ),
			] );
		}
		
		// Check in theme first
		$theme_template = get_stylesheet_directory() . '/' . self::THEME_TEMPLATE_DIR . '/' . $template_name;
		$theme_exists = file_exists( $theme_template );
		
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'template_loader', 'Checking theme template', [
				'path' => $theme_template,
				'exists' => $theme_exists,
				'is_readable' => $theme_exists ? is_readable( $theme_template ) : 'N/A',
				'path_length' => strlen( $theme_template ),
			] );
		}
		
		if ( $theme_exists ) {
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::debug( 'template_loader', 'Template found in theme', [
					'path' => $theme_template,
					'filesize' => filesize( $theme_template ),
				] );
			}
			return $theme_template;
		}
		
		// Check in parent theme
		if ( is_child_theme() ) {
			$parent_template = get_template_directory() . '/' . self::THEME_TEMPLATE_DIR . '/' . $template_name;
			$parent_exists = file_exists( $parent_template );
			
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				ChurchTools_Suite_Logger::debug( 'template_loader', 'Checking parent theme template', [
					'path' => $parent_template,
					'exists' => $parent_exists,
					'is_readable' => $parent_exists ? is_readable( $parent_template ) : 'N/A',
				] );
			}
			
			if ( $parent_exists ) {
				if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
					ChurchTools_Suite_Logger::debug( 'template_loader', 'Template found in parent theme', [
						'path' => $parent_template,
						'filesize' => filesize( $parent_template ),
					] );
				}
				return $parent_template;
			}
		}
		
		// Fallback to plugin templates
		$plugin_template = CHURCHTOOLS_SUITE_PATH . 'templates/' . $template_name;
		$plugin_exists = file_exists( $plugin_template );
		$plugin_readable = $plugin_exists ? is_readable( $plugin_template ) : false;
		$plugin_size = $plugin_exists ? filesize( $plugin_template ) : 0;
		
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::debug( 'template_loader', 'Checking plugin template (DETAILED)', [
				'path' => $plugin_template,
				'path_length' => strlen( $plugin_template ),
				'exists' => $plugin_exists,
				'is_readable' => $plugin_readable,
				'filesize' => $plugin_size,
				'churchtools_suite_path' => CHURCHTOOLS_SUITE_PATH,
				'relative_part' => 'templates/' . $template_name,
			] );
		}
		
		if ( $plugin_exists ) {
			if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
				// v0.9.9.86: Extract template name from path for better debugging
				$template_name_extracted = basename( $template_name, '.php' );
				ChurchTools_Suite_Logger::debug( 'template_loader', 'Template found in plugin', [
					'template_name' => $template_name_extracted,
					'full_path' => $plugin_template,
					'relative_path' => 'templates/' . $template_name,
					'filesize' => $plugin_size,
					'is_readable' => $plugin_readable,
				] );
			}
			return $plugin_template;
		}
		
		if ( class_exists( 'ChurchTools_Suite_Logger' ) ) {
			ChurchTools_Suite_Logger::warning( 'template_loader', 'Template NOT FOUND - DETAILED ERROR', [
				'template_name' => $template_name,
				'theme_template_path' => $theme_template,
				'theme_exists' => $theme_exists,
				'parent_template_path' => is_child_theme() ? $parent_template : 'N/A',
				'parent_exists' => is_child_theme() ? $parent_exists : false,
				'plugin_template_path' => $plugin_template,
				'plugin_template_path_length' => strlen( $plugin_template ),
				'plugin_exists' => $plugin_exists,
				'plugin_readable' => $plugin_readable,
				'churchtools_suite_path' => CHURCHTOOLS_SUITE_PATH,
				'churchtools_suite_path_length' => strlen( CHURCHTOOLS_SUITE_PATH ),
				'churchtools_suite_path_defined' => defined( 'CHURCHTOOLS_SUITE_PATH' ),
			] );
		}
		
		return false;
	}
	
	/**
	 * Migrate old template paths to new structure (v0.9.9.44)
	 * 
	 * Alte Struktur: list/modern.php, grid/simple.php, single/modern.php
	 * Neue Struktur: views/event-list/modern.php, views/event-grid/simple.php
	 * 
	 * @param string $template_name Template path
	 * @return string Migrated template path
	 * @since 0.9.9.44
	 */
	private static function migrate_template_path( string $template_name ): string {
		// Migration-Map: alt → neu
		$migrations = [
			'list/'     => 'views/event-list/',
			'grid/'     => 'views/event-grid/',
			'single/'   => 'views/event-single/',
			'modal/'    => 'views/event-modal/',
			'calendar/' => 'views/event-calendar/',
		];
		
		foreach ( $migrations as $old => $new ) {
			if ( strpos( $template_name, $old ) === 0 ) {
				return str_replace( $old, $new, $template_name );
			}
		}
		
		return $template_name;
	}
	
	/**
	 * Render a template
	 * 
	 * @param string $template_name Template name (e.g., 'calendar/monthly.php')
	 * @param array  $args          Variables to pass to template
	 * @param bool   $echo          Echo output or return as string
	 * @return string|void
	 */
	public static function render_template( string $template_name, array $args = [], bool $echo = true ) {
		$template_path = self::locate_template( $template_name );
		
		if ( ! $template_path ) {
			// Detailed error message for debugging
			$error_msg = sprintf(
				'ChurchTools Suite Fehler: Template "%s" wurde nicht gefunden.',
				str_replace( '.php', '', $template_name )
			);
			
			$expected_path = 'templates/' . $template_name;
			$error_msg .= ' Erwarteter Pfad: ' . $expected_path;
			
			// Check if CHURCHTOOLS_SUITE_PATH is defined
			if ( defined( 'CHURCHTOOLS_SUITE_PATH' ) ) {
				$full_path = CHURCHTOOLS_SUITE_PATH . 'templates/' . $template_name;
				$exists = file_exists( $full_path ) ? 'Ja' : 'Nein';
				$error_msg .= ' | Vollständiger Pfad: ' . $full_path . ' (Existiert: ' . $exists . ')';
			} else {
				$error_msg .= ' | CHURCHTOOLS_SUITE_PATH ist nicht definiert!';
			}
			
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $error_msg );
			}
			
			// Return error message for frontend
			return '<!-- ' . esc_html( $error_msg ) . ' -->';
		}
		
		// Make $args available in template as variables
		if ( ! empty( $args ) ) {
			extract( $args, EXTR_OVERWRITE );
		}
		
		// Capture output
		ob_start();
		
		/**
		 * Filter template path before loading
		 *
		 * @param string $template_path Full path to template
		 * @param string $template_name Template name
		 * @param array  $args          Template arguments
		 */
		$template_path = apply_filters( 'churchtools_suite_template_path', $template_path, $template_name, $args );
		
		include $template_path;
		
		$output = ob_get_clean();
		
		/**
		 * Filter template output
		 *
		 * @param string $output        Template output
		 * @param string $template_name Template name
		 * @param array  $args          Template arguments
		 */
		$output = apply_filters( 'churchtools_suite_template_output', $output, $template_name, $args );
		
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
	
	/**
	 * Get available views for a view type
	 *
	 * @param string $view_type View type (calendar, list, grid, etc.)
	 * @return array Available views
	 */
	public static function get_available_views( string $view_type ): array {
		$opts = self::get_view_options( $view_type );
		return array_map( function( $o ) { return $o['value']; }, $opts );
	}
	
	/**
	 * Get template info for documentation
	 *
	 * @param string $template_name Template name
	 * @return array|null Template info or null
	 */
	public static function get_template_info( string $template_name ): ?array {
		$template_path = self::locate_template( $template_name );
		
		if ( ! $template_path ) {
			return null;
		}
		
		$info = [
			'path' => $template_path,
			'is_theme_override' => strpos( $template_path, get_stylesheet_directory() ) === 0,
			'size' => filesize( $template_path ),
			'modified' => filemtime( $template_path ),
		];
		
		return $info;
	}
}
