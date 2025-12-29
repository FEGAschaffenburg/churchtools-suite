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
	 * Locate a template file
	 * 
	 * Checks in this order:
	 * 1. Theme: {theme}/churchtools-suite/{template}.php
	 * 2. Plugin: {plugin}/templates/{template}.php
	 *
	 * @param string $template_name Template name (e.g., 'calendar/monthly.php')
	 * @return string|false Full path to template file or false
	 */
	public static function locate_template( string $template_name ) {
		// Check in theme first
		$theme_template = get_stylesheet_directory() . '/' . self::THEME_TEMPLATE_DIR . '/' . $template_name;
		
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}
		
		// Check in parent theme
		if ( is_child_theme() ) {
			$parent_template = get_template_directory() . '/' . self::THEME_TEMPLATE_DIR . '/' . $template_name;
			
			if ( file_exists( $parent_template ) ) {
				return $parent_template;
			}
		}
		
		// Fallback to plugin templates
		$plugin_template = CHURCHTOOLS_SUITE_PATH . 'templates/' . $template_name;
		
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
		
		return false;
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
		$views = [];
		
		// Scan plugin templates directory
		$plugin_dir = CHURCHTOOLS_SUITE_PATH . 'templates/' . $view_type;
		
		if ( is_dir( $plugin_dir ) ) {
			$files = scandir( $plugin_dir );
			
			foreach ( $files as $file ) {
				if ( preg_match( '/^(.+)\.php$/', $file, $matches ) ) {
					$views[] = $matches[1];
				}
			}
		}
		
		// Scan theme templates directory
		$theme_dir = get_stylesheet_directory() . '/' . self::THEME_TEMPLATE_DIR . '/' . $view_type;
		
		if ( is_dir( $theme_dir ) ) {
			$files = scandir( $theme_dir );
			
			foreach ( $files as $file ) {
				if ( preg_match( '/^(.+)\.php$/', $file, $matches ) ) {
					if ( ! in_array( $matches[1], $views ) ) {
						$views[] = $matches[1];
					}
				}
			}
		}
		
		return $views;
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
