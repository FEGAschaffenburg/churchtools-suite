<?php
/**
 * ChurchTools Suite Demo Theme Functions
 *
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Version Manager (GitHub API Integration)
require_once get_template_directory() . '/inc/version-manager.php';

/**
 * Get ChurchTools Suite plugin version.
 * Tries: 1) GitHub API, 2) Local plugin file, 3) Constant, 4) Fallback
 *
 * @return string
 */
function cts_demo_get_cts_version(): string {
	// 1) Try GitHub API (cached for 1 hour)
	$github_version = cts_get_main_plugin_version();
	if ($github_version !== 'unknown') {
		return $github_version;
	}
	
	// 2) Fallback: Read from local plugin header
	$plugin_file = WP_PLUGIN_DIR . '/churchtools-suite/churchtools-suite.php';
	if ( file_exists( $plugin_file ) ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$info = get_plugin_data( $plugin_file, false, false );
		if ( ! empty( $info['Version'] ) ) {
			return $info['Version'];
		}
	}

	// 3) Fallback: defined constant
	if ( defined( 'CHURCHTOOLS_SUITE_VERSION' ) ) {
		return CHURCHTOOLS_SUITE_VERSION;
	}

	// 4) Final fallback
	return '1.0.0.0';
}

// Force HTTPS URLs for assets
add_filter( 'style_loader_src', function( $src ) {
	return str_replace( 'http://', 'https://', $src );
}, 10, 1 );

add_filter( 'script_loader_src', function( $src ) {
	return str_replace( 'http://', 'https://', $src );
}, 10, 1 );

/**
 * Theme Setup
 */
function cts_demo_theme_setup() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script'
	) );
	
	// Register navigation menus
	register_nav_menus( array(
		'primary'       => __( 'Primary Menu', 'cts-demo' ),
		'footer'        => __( 'Footer Menu', 'cts-demo' ),
		'footer-menu-1' => __( 'Footer Menu 1 (Navigation)', 'cts-demo' ),
		'footer-menu-2' => __( 'Footer Menu 2 (Entwickler)', 'cts-demo' ),
	) );
}
add_action( 'after_setup_theme', 'cts_demo_theme_setup' );

/**
 * Fallback Menu (if no WordPress menu is set)
 */
function cts_demo_fallback_menu() {
	// Add nav-menu class so styling applies even without a WP menu assignment
	echo '<ul class="nav-menu">';
	echo '<li><a href="' . home_url( '/' ) . '">Home</a></li>';
	echo '<li><a href="' . home_url( '/demo/' ) . '">Demo</a></li>';
	echo '<li><a href="' . home_url( '/addons/' ) . '">Addons</a></li>';
	echo '<li><a href="' . home_url( '/dokumentation/' ) . '">Dokumentation</a></li>';
	echo '<li><a href="' . home_url( '/backend-test/' ) . '">Backend-Test</a></li>';
	echo '<li><a href="https://github.com/FEGAschaffenburg/churchtools-suite" target="_blank" rel="noopener">GitHub</a></li>';
	echo '</ul>';
}

/**
 * Footer Menu 1 Fallback (Navigation)
 */
function cts_demo_footer_menu_1_fallback() {
	echo '<ul>';
	echo '<li><a href="' . home_url( '/' ) . '">Home</a></li>';
	echo '<li><a href="' . home_url( '/demo/' ) . '">Demo</a></li>';
	echo '<li><a href="' . home_url( '/dokumentation/' ) . '">Dokumentation</a></li>';
	echo '<li><a href="' . home_url( '/download/' ) . '">Download</a></li>';
	echo '<li><a href="' . home_url( '/backend-demo/' ) . '">Backend-Demo</a></li>';
	echo '<li><a href="https://github.com/FEGAschaffenburg/churchtools-suite" target="_blank" rel="noopener">GitHub</a></li>';
	echo '</ul>';
}

/**
 * Footer Menu 2 Fallback (Entwickler)
 */
function cts_demo_footer_menu_2_fallback() {
	echo '<ul>';
	echo '<li><a href="https://github.com/FEGAschaffenburg/churchtools-suite" target="_blank" rel="noopener">GitHub Repository</a></li>';
	echo '<li><a href="https://github.com/FEGAschaffenburg/churchtools-suite/issues" target="_blank" rel="noopener">Issues</a></li>';
	echo '<li><a href="' . home_url( '/download/' ) . '">Release-Download</a></li>';
	echo '<li><a href="' . home_url( '/haftungsausschluss/' ) . '">Haftungsausschluss</a></li>';
	echo '</ul>';
}

/**
 * Enqueue Scripts and Styles
 */
function cts_demo_theme_scripts() {
// Google Fonts (Montserrat fÃ¼r Headlines, Open Sans fÃ¼r FlieÃŸtext)
	wp_enqueue_style( 'cts-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap', array(), null );
	
		// Demo-Seiten Styles (Download, Dokumentation)

	// ChurchTools Suite Theme - Consolidated Stylesheet (v0.9.6)
	 wp_enqueue_style( 'churchtools-suite-theme', get_template_directory_uri() . '/assets/css/churchtools-suite-theme.css', array('cts-fonts'), '0.9.19' );
	
	// Prism.js for syntax highlighting
	wp_enqueue_style( 'prism-css', 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css', array(), '1.29.0' );
	wp_enqueue_script( 'prism-js', 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js', array(), '1.29.0', true );
	
	// Copy to clipboard functionality
	wp_enqueue_script( 'cts-demo-clipboard', get_template_directory_uri() . '/assets/js/clipboard.js', array(), '0.9.0', true );
	
	// Docs search
	wp_enqueue_script( 'cts-docs-search', get_template_directory_uri() . '/assets/js/search.js', array(), '0.9.0', true );

	// Sticky header shrink on scroll
	 wp_enqueue_script( 'cts-sticky-header', get_template_directory_uri() . '/assets/js/sticky-header.js', array(), '1.0.3', true );

}

add_action( 'wp_enqueue_scripts', 'cts_demo_theme_scripts' );

/**
 * Normalize view feature texts to match plugin capabilities
 */
function cts_demo_normalize_view_features( string $content ): string {
	if ( ! is_page_template( 'page-view-documentation.php' ) ) {
		return $content;
	}

	$replacements = array(
		'Services mit Avatars' => 'Services mit Personen',
		'optionalen Profilbildern angezeigt' => 'ohne Profilbilder angezeigt',
	);

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
}
add_filter( 'the_content', 'cts_demo_normalize_view_features', 20 );

/**
 * Enqueue Gutenberg Editor Styles
 */
function cts_demo_theme_editor_styles() {
	// Add editor styles for Gutenberg
	add_editor_style( 'assets/css/churchtools-suite-theme.css' );
	
	// Enqueue editor-specific styles
	wp_enqueue_style( 'churchtools-suite-editor', get_template_directory_uri() . '/assets/css/churchtools-suite-theme.css', array(), '0.9.13' );
}
add_action( 'enqueue_block_editor_assets', 'cts_demo_theme_editor_styles' );


/**
 * Breadcrumbs
 */
function cts_demo_breadcrumbs() {
	echo '<nav class="secondary-navigation secondary-navigation--breadcrumbs breadcrumbs" aria-label="Breadcrumb">';
	echo '<div class="container">';
	echo '<ul>';
	echo '<li><a href="' . home_url() . '">Home</a></li>';

	if ( is_front_page() ) {
		echo '</ul>';
		echo '</div>';
		echo '</nav>';
		return;
	}
	
	if ( is_page() ) {
		// Parent pages
		global $post;
		if ( $post->post_parent ) {
			$parent_id = $post->post_parent;
			$breadcrumbs = array();
			
			while ( $parent_id ) {
				$page = get_post( $parent_id );
				$breadcrumbs[] = '<li><a href="' . get_permalink( $page->ID ) . '">' . get_the_title( $page->ID ) . '</a></li>';
				$parent_id = $page->post_parent;
			}
			
			$breadcrumbs = array_reverse( $breadcrumbs );
			foreach ( $breadcrumbs as $crumb ) {
				echo $crumb;
			}
		}
		
		// Current page
		echo '<li>' . get_the_title() . '</li>';
	}
	
	echo '</ul>';
	echo '</div>';
	echo '</nav>';
}

/**
 * Secondary navigation below the main menu
 * - Documentation page: anchor submenu
 * - Other pages: contextual page submenu (no breadcrumbs)
 */
function cts_demo_secondary_navigation() {
	if ( cts_is_docs_page() ) {
		echo '<nav class="secondary-navigation secondary-navigation--anchors" aria-label="Dokumentations-Sprunglinks">';
		echo '<div class="container">';
		echo '<ul class="secondary-navigation-list">';
		echo '<li><a href="' . esc_url( get_permalink() ) . '">Inhalt</a></li>';
		echo '<li><a href="#quick-start">Quick Start Guide</a></li>';
		echo '<li><a href="#shortcodes">Shortcode-Referenz</a></li>';
		echo '<li><a href="#views">View-Typen</a></li>';
		echo '<li><a href="#templates">Template-System</a></li>';
		echo '<li><a href="#troubleshooting">Troubleshooting</a></li>';
		echo '</ul>';
		echo '</div>';
		echo '</nav>';

		return;
	}

	if ( ! is_page() ) {
		return;
	}

	global $post;
	$current_id = (int) $post->ID;
	$parent_id  = (int) $post->post_parent;
	$section_id = $parent_id > 0 ? $parent_id : $current_id;

	// Reihenfolge aus dem Hauptmenü (ID 2) ableiten
	$menu_items = wp_get_nav_menu_items( 2 );
	$section_pages = array();

	if ( $menu_items ) {
		// Menü-Item für die aktuelle Seite finden
		$current_menu_item = null;
		foreach ( $menu_items as $item ) {
			if ( (int) $item->object_id === $current_id ) {
				$current_menu_item = $item;
				break;
			}
		}

		// Zielgruppe bestimmen:
		// - Hat der aktuelle Menüpunkt eigene Unterpunkte, zeige diese (z. B. Listen-Varianten)
		// - Sonst zeige Geschwister unter gleichem Parent
		$group_menu_item_id = 0;
		if ( $current_menu_item ) {
			$current_menu_item_id = (int) $current_menu_item->ID;
			$has_children = false;

			foreach ( $menu_items as $item ) {
				if ( (int) $item->menu_item_parent === $current_menu_item_id && $item->object === 'page' ) {
					$has_children = true;
					break;
				}
			}

			$group_menu_item_id = $has_children ? $current_menu_item_id : (int) $current_menu_item->menu_item_parent;
		}

		// Menüeinträge für die Zielgruppe sammeln
		foreach ( $menu_items as $item ) {
			if ( (int) $item->menu_item_parent === $group_menu_item_id && $item->object === 'page' ) {
				$page = get_post( (int) $item->object_id );
				if ( $page ) {
					$section_pages[] = $page;
				}
			}
		}
	}

	// Fallback: nur aktuelle Seite
	if ( empty( $section_pages ) ) {
		$section_pages = array( get_post( $current_id ) );
	}

	echo '<nav class="secondary-navigation secondary-navigation--section" aria-label="Seiten-Untermenü">';
	echo '<div class="container">';
	echo '<ul class="secondary-navigation-list">';

	if ( $parent_id > 0 ) {
		echo '<li><a href="' . esc_url( get_permalink( $section_id ) ) . '">' . esc_html( get_the_title( $section_id ) ) . '</a></li>';
	}

	foreach ( $section_pages as $page_item ) {
		if ( ! $page_item ) {
			continue;
		}

		$is_current = ( (int) $page_item->ID === $current_id );
		$class_attr = $is_current ? ' class="is-current"' : '';

		echo '<li><a' . $class_attr . ' href="' . esc_url( get_permalink( $page_item->ID ) ) . '">' . esc_html( get_the_title( $page_item->ID ) ) . '</a></li>';
	}

	echo '</ul>';
	echo '</div>';
	echo '</nav>';
}

/**
 * Custom Page Template Detection
 */
function cts_is_demo_page() {
	return is_page_template( 'page-templates/demo-page.php' );
}

function cts_is_docs_page() {
	return is_page_template( 'page-templates/documentation.php' )
		|| is_page_template( 'page-documentation.php' )
		|| is_page( 'dokumentation' )
		|| is_page( 'documentation' );
}

/**
 * Get Demo Category
 */
function cts_get_demo_category() {
	global $post;
	
	// Get from custom field or slug
	$category = get_post_meta( $post->ID, 'demo_category', true );
	
	if ( ! $category ) {
		// Try to extract from slug
		$slug = $post->post_name;
		if ( strpos( $slug, 'calendar' ) !== false ) {
			$category = 'calendar';
		} elseif ( strpos( $slug, 'list' ) !== false ) {
			$category = 'list';
		} elseif ( strpos( $slug, 'grid' ) !== false ) {
			$category = 'grid';
		} elseif ( strpos( $slug, 'slider' ) !== false ) {
			$category = 'slider';
		} elseif ( strpos( $slug, 'single' ) !== false ) {
			$category = 'single';
		}
	}
	
	return $category;
}

/**
 * Get Related Demos
 */
function cts_get_related_demos( $category = '', $limit = 3 ) {
	global $post;
	
	if ( ! $category ) {
		$category = cts_get_demo_category();
	}
	
	$args = array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'post__not_in'   => array( $post->ID ),
		'meta_query'     => array(
			array(
				'key'     => 'demo_category',
				'value'   => $category,
				'compare' => '='
			)
		)
	);
	
	return new WP_Query( $args );
}

/**
 * Demo Code Block
 */
function cts_demo_code_block( $code, $language = 'php', $title = 'Shortcode' ) {
	ob_start();
	?>
	<div class="demo-code">
		<div class="demo-code-header">
			<span><?php echo esc_html( $title ); ?></span>
			<button class="copy-button" data-clipboard-text="<?php echo esc_attr( $code ); ?>">
				Kopieren
			</button>
		</div>
		<pre><code class="language-<?php echo esc_attr( $language ); ?>"><?php echo esc_html( $code ); ?></code></pre>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Demo Preview Block
 */
function cts_demo_preview_block( $shortcode ) {
	ob_start();
	?>
	<div class="demo-preview">
		<?php echo do_shortcode( $shortcode ); ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * [DEACTIVATED] Add Custom Meta Boxes
 * Demo Settings metabox removed - no longer needed after template consolidation
 */
/*
function cts_demo_add_meta_boxes() {
	add_meta_box(
		'cts_demo_meta',
		__( 'Demo Settings', 'cts-demo' ),
		'cts_demo_meta_box_callback',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'cts_demo_add_meta_boxes' );
*/

/**
 * [DEACTIVATED] Meta Box Callback
 */
/*
function cts_demo_meta_box_callback( $post ) {
	wp_nonce_field( 'cts_demo_meta_box', 'cts_demo_meta_box_nonce' );
	
	$category = get_post_meta( $post->ID, 'demo_category', true );
	$shortcode = get_post_meta( $post->ID, 'demo_shortcode', true );
	$difficulty = get_post_meta( $post->ID, 'demo_difficulty', true );
	?>
	
	<p>
		<label for="demo_category"><strong><?php _e( 'Kategorie:', 'cts-demo' ); ?></strong></label><br>
		<select name="demo_category" id="demo_category" style="width: 100%;">
			<option value="">-- AuswÃ¤hlen --</option>
			<option value="calendar" <?php selected( $category, 'calendar' ); ?>>Calendar</option>
			<option value="list" <?php selected( $category, 'list' ); ?>>List</option>
			<option value="grid" <?php selected( $category, 'grid' ); ?>>Grid</option>
			<option value="slider" <?php selected( $category, 'slider' ); ?>>Slider</option>
			<option value="single" <?php selected( $category, 'single' ); ?>>Single Event</option>
			<option value="other" <?php selected( $category, 'other' ); ?>>Sonstiges</option>
		</select>
	</p>
	
	<p>
		<label for="demo_shortcode"><strong><?php _e( 'Shortcode:', 'cts-demo' ); ?></strong></label><br>
		<input type="text" name="demo_shortcode" id="demo_shortcode" value="<?php echo esc_attr( $shortcode ); ?>" style="width: 100%;" placeholder='[cts_list view="classic"]'>
	</p>
	
	<p>
		<label for="demo_difficulty"><strong><?php _e( 'Schwierigkeit:', 'cts-demo' ); ?></strong></label><br>
		<select name="demo_difficulty" id="demo_difficulty" style="width: 100%;">
			<option value="beginner" <?php selected( $difficulty, 'beginner' ); ?>>AnfÃ¤nger</option>
			<option value="intermediate" <?php selected( $difficulty, 'intermediate' ); ?>>Fortgeschritten</option>
			<option value="advanced" <?php selected( $difficulty, 'advanced' ); ?>>Experte</option>
		</select>
	</p>
	<?php
}
*/

/**
 * [DEACTIVATED] Save Meta Box Data
 */
/*
function cts_demo_save_meta_box( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['cts_demo_meta_box_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['cts_demo_meta_box_nonce'], 'cts_demo_meta_box' ) ) {
		return;
	}
	
	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Save fields
	if ( isset( $_POST['demo_category'] ) ) {
		update_post_meta( $post_id, 'demo_category', sanitize_text_field( $_POST['demo_category'] ) );
	}
	
	if ( isset( $_POST['demo_shortcode'] ) ) {
		update_post_meta( $post_id, 'demo_shortcode', sanitize_text_field( $_POST['demo_shortcode'] ) );
	}
	
	if ( isset( $_POST['demo_difficulty'] ) ) {
		update_post_meta( $post_id, 'demo_difficulty', sanitize_text_field( $_POST['demo_difficulty'] ) );
	}
}
add_action( 'save_post', 'cts_demo_save_meta_box' );
*/

/**
 * Shortcode to embed external content from feg-aschaffenburg.de
 * 
 * Usage: [embed_feg_page url="https://feg-aschaffenburg.de/datenschutz/"]
 * 
 * @param array $atts Shortcode attributes
 * @return string Embedded content
 */
function cts_demo_embed_feg_page( $atts ) {
	$atts = shortcode_atts( array(
		'url' => '',
		'cache' => '86400', // 24 hours default
	), $atts );
	
	if ( empty( $atts['url'] ) ) {
		return '<p><em>Fehler: Keine URL angegeben.</em></p>';
	}
	
	// Create cache key from URL
	$cache_key = 'cts_embed_' . md5( $atts['url'] );
	
	// Try to get cached content
	$cached = get_transient( $cache_key );
	if ( $cached !== false ) {
		return $cached;
	}
	
	// Fetch content from remote URL
	$response = wp_remote_get( $atts['url'], array(
		'timeout' => 15,
		'sslverify' => true,
	) );
	
	if ( is_wp_error( $response ) ) {
		return '<p><em>Fehler beim Laden der Seite: ' . esc_html( $response->get_error_message() ) . '</em></p>';
	}
	
	$body = wp_remote_retrieve_body( $response );
	
	if ( empty( $body ) ) {
		return '<p><em>Fehler: Seite ist leer.</em></p>';
	}
	
	// Extract main content (between <main> tags or article content)
	$content = '';
	
	// Try to extract main content area
	if ( preg_match( '/<main[^>]*>(.*?)<\/main>/is', $body, $matches ) ) {
		$content = $matches[1];
	} elseif ( preg_match( '/<article[^>]*>(.*?)<\/article>/is', $body, $matches ) ) {
		$content = $matches[1];
	} elseif ( preg_match( '/<div[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/div>/is', $body, $matches ) ) {
		$content = $matches[1];
	} else {
		// Fallback: extract body content
		if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $body, $matches ) ) {
			$content = $matches[1];
		}
	}
	
	if ( empty( $content ) ) {
		return '<p><em>Fehler: Kein Inhalt gefunden.</em></p>';
	}
	
	// Clean up content: remove scripts, styles, navigation
	$content = preg_replace( '/<script[^>]*>.*?<\/script>/is', '', $content );
	$content = preg_replace( '/<style[^>]*>.*?<\/style>/is', '', $content );
	$content = preg_replace( '/<nav[^>]*>.*?<\/nav>/is', '', $content );
	$content = preg_replace( '/<header[^>]*>.*?<\/header>/is', '', $content );
	$content = preg_replace( '/<footer[^>]*>.*?<\/footer>/is', '', $content );
	
	// Convert relative URLs to absolute
	$base_url = preg_replace( '#^(https?://[^/]+).*$#', '$1', $atts['url'] );
	$content = preg_replace( '/href="\//', 'href="' . $base_url . '/', $content );
	$content = preg_replace( '/src="\//', 'src="' . $base_url . '/', $content );
	
	// Add source attribution
	$output = '<div class="embedded-content">';
	$output .= $content;
	$output .= '<p class="embedded-source"><small><em>Quelle: <a href="' . esc_url( $atts['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $atts['url'] ) . '</a></em></small></p>';
	$output .= '</div>';
	
	// Cache for specified time
	set_transient( $cache_key, $output, absint( $atts['cache'] ) );
	
	return $output;
}
add_shortcode( 'embed_feg_page', 'cts_demo_embed_feg_page' );

/**
 * Protect Download page - only accessible after registration
 * 
 * Non-logged-in users see registration options directly on the page
 */
function cts_demo_protect_download_page() {
	// Only on frontend
	if ( is_admin() ) {
		return;
	}
	
	// Check if we're on the download page
	if ( is_page( 'download' ) || is_page( 168 ) ) {
		// Allow access if user is logged in
		if ( is_user_logged_in() ) {
			return;
		}
		
		// For non-logged-in users, don't redirect - show registration form on page
		// (see cts_demo_download_page_notice filter below)
		return;
	}
}
add_action( 'template_redirect', 'cts_demo_protect_download_page' );

/**
 * Add notice/registration form to download page
 */
function cts_demo_download_page_notice( $content ) {
	// Only on download page
	if ( ! is_page( 'download' ) && ! is_page( 168 ) ) {
		return $content;
	}
	
	// Show different content based on login status
	if ( is_user_logged_in() ) {
		// Logged-in users: show welcome banner
		$current_user = wp_get_current_user();
		$notice = '<div class="download-welcome" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 2px solid #10b981; border-radius: 8px; padding: 1.5rem; margin: 0 0 2rem 0;">
			<div style="display: flex; align-items: center; gap: 1rem;">
				<span style="font-size: 2rem;">âœ…</span>
				<div>
					<strong style="color: #065f46;">Registrierung erfolgreich!</strong><br>
					<span style="color: #047857;">Willkommen ' . esc_html( $current_user->display_name ) . '! Sie haben jetzt Zugriff auf alle Downloads.</span>
				</div>
			</div>
		</div>';
		return $notice . $content;
	} else {
		// Non-logged-in users: show registration form
		$registration_notice = '<div class="download-registration" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 8px; padding: 2rem; margin: 0 0 2rem 0;">
			<div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
				<span style="font-size: 2rem;">ðŸ”</span>
				<div>
					<strong style="color: #92400e;">Anmeldung erforderlich</strong><br>
					<span style="color: #b45309;">Bitte melden Sie sich an oder registrieren Sie sich, um auf die Downloads zuzugreifen.</span>
				</div>
			</div>
			
			<div style="background: white; padding: 1.5rem; border-radius: 6px; margin-bottom: 1rem;">
				<p style="margin: 0 0 1rem 0;"><strong>Sie haben bereits ein Konto?</strong></p>
				<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="button button-primary" style="background-color: #047857; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
					â†’ Anmelden
				</a>
			</div>
			
			<div style="background: white; padding: 1.5rem; border-radius: 6px;">
				<p style="margin: 0 0 1rem 0;"><strong>Noch kein Konto?</strong></p>
				<a href="' . esc_url( home_url( '/backend-demo/' ) ) . '" class="button button-primary" style="background-color: #0891b2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
					â†’ Kostenlos registrieren
				</a>
			</div>
		</div>';
		return $registration_notice . $content;
	}
}
add_filter( 'the_content', 'cts_demo_download_page_notice' );

/**
 * Live Builder configuration for demo pages.
 */
function cts_demo_live_builder_config(): array {
	return array(
		'churchtools_events' => array(
			'requires_view' => true,
			'toggles' => array(
				'show_event_description',
				'show_appointment_description',
				'show_location',
				'show_services',
				'show_time',
				'show_tags',
				'show_calendar_name',
				'show_filter',
				'show_past_events',
			),
			'filters' => array(
				'limit' => 'number',
			),
			'defaults' => array(
				'limit' => '10',
				'show_event_description' => '1',
				'show_appointment_description' => '1',
				'show_location' => '1',
				'show_services' => '0',
				'show_time' => '1',
				'show_tags' => '1',
				'show_calendar_name' => '1',
				'show_filter' => '0',
				'show_past_events' => '0',
			),
		),
		'cts_calendar' => array(
			'requires_view' => true,
			'toggles' => array(
				'use_calendar_colors',
				'show_past_events',
			),
			'filters' => array(
				'calendars' => 'calendar_multi',
				'tags' => 'tag_multi',
				'from' => 'date',
				'to' => 'date',
			),
			'defaults' => array(
				'use_calendar_colors' => '0',
				'show_past_events' => '0',
			),
		),
		'cts_countdown' => array(
			'requires_view' => true,
			'toggles' => array(
				'show_event_description',
				'show_location',
				'show_tags',
				'show_images',
				'show_calendar_name',
			),
			'filters' => array(
				'calendars' => 'calendar_multi',
				'tags' => 'tag_multi',
				'event_id' => 'number',
			),
			'defaults' => array(
				'show_event_description' => '1',
				'show_location' => '1',
				'show_tags' => '1',
				'show_images' => '1',
				'show_calendar_name' => '1',
			),
		),
		'cts_carousel' => array(
			'requires_view' => true,
			'toggles' => array(
				'show_event_description',
				'show_location',
				'show_time',
				'show_services',
				'show_tags',
				'show_images',
				'autoplay',
				'loop',
			),
			'filters' => array(
				'limit' => 'number',
				'slides_per_view' => 'number',
				'calendars' => 'calendar_multi',
				'tags' => 'tag_multi',
			),
			'defaults' => array(
				'limit' => '8',
				'slides_per_view' => '3',
				'show_event_description' => '1',
				'show_location' => '1',
				'show_time' => '1',
				'show_services' => '0',
				'show_tags' => '0',
				'show_images' => '1',
				'autoplay' => '0',
				'loop' => '1',
			),
		),
		'cts_event' => array(
			'requires_view' => false,
			'toggles' => array(),
			'filters' => array(
				'id' => 'number',
				'template' => 'select',
			),
			'defaults' => array(
				'id' => '1',
				'template' => 'professional',
			),
		),
	);
}

/**
 * German labels for live builder controls.
 */
function cts_demo_live_builder_labels(): array {
	return array(
		'show_event_description' => 'Ereignisbeschreibung',
		'show_appointment_description' => 'Terminbeschreibung',
		'show_location' => 'Ort anzeigen',
		'show_services' => 'Dienste anzeigen',
		'show_time' => 'Uhrzeit anzeigen',
		'show_tags' => 'Tags anzeigen',
		'show_calendar_name' => 'Kalendername anzeigen',
		'show_filter' => 'Filter einblenden',
		'show_past_events' => 'Vergangene Termine',
		'use_calendar_colors' => 'Kalenderfarben nutzen',
		'show_images' => 'Bilder anzeigen',
		'autoplay' => 'Autoplay',
		'loop' => 'Endlosschleife',
		'limit' => 'Anzahl Termine',
		'calendars' => 'Kalender',
		'tags' => 'Tags',
		'order' => 'Sortierung',
		'date_from' => 'Von Datum',
		'date_to' => 'Bis Datum',
		'from' => 'Von Datum',
		'to' => 'Bis Datum',
		'slides_per_view' => 'Karten pro Ansicht',
		'event_id' => 'Event-ID',
		'id' => 'Event-ID',
		'template' => 'Template',
	);
}

/**
 * Load selectable calendars and tags from database.
 *
 * @return array{calendars:array<int,array{label:string,value:string}>,tags:array<int,array{label:string,value:string}>}
 */
function cts_demo_live_builder_db_options(): array {
	$calendars = array();
	$tags = array();

	if ( class_exists( 'ChurchTools_Suite_Calendars_Repository' ) ) {
		$repo = new ChurchTools_Suite_Calendars_Repository();
		$rows = $repo->get_all();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$label = isset( $row->name ) ? (string) $row->name : '';
				$value = isset( $row->calendar_id ) ? (string) $row->calendar_id : '';
				if ( $label !== '' && $value !== '' ) {
					$calendars[] = array(
						'label' => $label,
						'value' => $value,
					);
				}
			}
		}
	}

	if ( empty( $calendars ) ) {
		global $wpdb;
		$cal_table = $wpdb->prefix . 'cts_calendars';
		$exists_cal = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cal_table ) );
		if ( $exists_cal === $cal_table ) {
			$rows = $wpdb->get_results( "SELECT calendar_id, name FROM {$cal_table} ORDER BY name ASC", ARRAY_A );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$label = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
					$value = isset( $row['calendar_id'] ) ? trim( (string) $row['calendar_id'] ) : '';
					if ( $label !== '' && $value !== '' ) {
						$calendars[] = array(
							'label' => $label,
							'value' => $value,
						);
					}
				}
			}
		}
	}

	global $wpdb;
	$table = $wpdb->prefix . 'cts_events';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists === $table ) {
		$results = $wpdb->get_results( "SELECT tags FROM {$table} WHERE tags IS NOT NULL AND tags != ''", ARRAY_A );
		$tag_map = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( empty( $row['tags'] ) || ! is_string( $row['tags'] ) ) {
					continue;
				}
				$decoded = json_decode( $row['tags'], true );
				if ( ! is_array( $decoded ) ) {
					continue;
				}
				foreach ( $decoded as $tag ) {
					if ( is_array( $tag ) && ! empty( $tag['name'] ) ) {
						$name = trim( (string) $tag['name'] );
						if ( $name !== '' ) {
							$tag_map[ $name ] = $name;
						}
					}
				}
			}
		}
		ksort( $tag_map, SORT_NATURAL | SORT_FLAG_CASE );
		foreach ( $tag_map as $name ) {
			$tags[] = array(
				'label' => $name,
				'value' => $name,
			);
		}
	}

	return array(
		'calendars' => $calendars,
		'tags' => $tags,
	);
}

/**
 * Resolve shortcode/view context for builder and feature checks.
 *
 * @return array{base:string,view:string,view_type:string,matrix_view:string}
 */
function cts_demo_live_builder_resolve_context( string $base, string $view ): array {
	$resolved_base = $base;
	$resolved_view = $view;
	$view_type = '';

	if ( $base === 'churchtools_events' && $view !== '' ) {
		if ( strpos( $view, 'list-' ) === 0 ) {
			$resolved_base = 'cts_list';
			$resolved_view = substr( $view, 5 );
		} elseif ( strpos( $view, 'grid-' ) === 0 ) {
			$resolved_base = 'cts_grid';
			$resolved_view = substr( $view, 5 );
		} elseif ( strpos( $view, 'calendar-' ) === 0 ) {
			$resolved_base = 'cts_calendar';
			$resolved_view = substr( $view, 9 );
		}
	}

	if ( $resolved_base === 'cts_list' ) {
		$view_type = 'list';
	} elseif ( $resolved_base === 'cts_grid' ) {
		$view_type = 'grid';
	} elseif ( $resolved_base === 'cts_calendar' ) {
		$view_type = 'calendar';
	} elseif ( $resolved_base === 'cts_countdown' ) {
		$view_type = 'countdown';
	} elseif ( $resolved_base === 'cts_carousel' ) {
		$view_type = 'carousel';
	}

	$matrix_view = $resolved_view;
	if (
		$view_type !== '' &&
		class_exists( 'ChurchTools_Suite_Template_Loader' ) &&
		method_exists( 'ChurchTools_Suite_Template_Loader', 'normalize_view_id' )
	) {
		$matrix_view = ChurchTools_Suite_Template_Loader::normalize_view_id( $view_type, $resolved_view );
	}

	return array(
		'base' => $resolved_base,
		'view' => $resolved_view,
		'view_type' => $view_type,
		'matrix_view' => $matrix_view,
	);
}

/**
 * Check if a toggle is allowed for the resolved view.
 */
function cts_demo_live_builder_is_toggle_allowed( string $toggle, string $resolved_base, string $view_type, string $matrix_view ): bool {
	if ( $toggle === 'show_filter' ) {
		return $resolved_base === 'cts_list';
	}

	if ( in_array( $toggle, array( 'autoplay', 'loop' ), true ) ) {
		return $resolved_base === 'cts_carousel';
	}

	if ( in_array( $toggle, array( 'show_past_events', 'use_calendar_colors' ), true ) ) {
		return true;
	}

	$feature_toggles = array(
		'show_event_description',
		'show_appointment_description',
		'show_location',
		'show_services',
		'show_time',
		'show_tags',
		'show_images',
		'show_calendar_name',
		'show_month_separator',
	);

	if ( ! in_array( $toggle, $feature_toggles, true ) ) {
		return true;
	}

	if ( ! function_exists( 'churchtools_suite_view_supports' ) && defined( 'CHURCHTOOLS_SUITE_PATH' ) ) {
		$matrix_file = CHURCHTOOLS_SUITE_PATH . 'includes/view-feature-matrix.php';
		if ( file_exists( $matrix_file ) ) {
			require_once $matrix_file;
		}
	}

	if ( function_exists( 'churchtools_suite_view_supports' ) && $view_type !== '' && $matrix_view !== '' ) {
		return (bool) churchtools_suite_view_supports( $matrix_view, $toggle );
	}

	return true;
}

/**
 * Build shortcode string from allowed parameters.
 */
function cts_demo_live_builder_build_shortcode( string $base, string $view, array $params ): string {
	$config_all = cts_demo_live_builder_config();
	if ( ! isset( $config_all[ $base ] ) ) {
		return '';
	}

	$config = $config_all[ $base ];
	$context = cts_demo_live_builder_resolve_context( $base, $view );
	$resolved_base = $context['base'];
	$resolved_view = $context['view'];
	$view_type = $context['view_type'];
	$matrix_view = $context['matrix_view'];

	$shortcode = '[' . $resolved_base;

	if ( ! empty( $config['requires_view'] ) && $resolved_view !== '' ) {
		$shortcode .= ' view="' . esc_attr( $resolved_view ) . '"';
	}

	if ( ! empty( $config['toggles'] ) ) {
		foreach ( $config['toggles'] as $toggle_key ) {
			if ( ! cts_demo_live_builder_is_toggle_allowed( $toggle_key, $resolved_base, $view_type, $matrix_view ) ) {
				continue;
			}

			$value = isset( $params[ $toggle_key ] ) ? $params[ $toggle_key ] : ( $config['defaults'][ $toggle_key ] ?? '0' );
			$bool = in_array( (string) $value, array( '1', 'true', 'on', 'yes' ), true ) ? 'true' : 'false';
			$shortcode .= ' ' . $toggle_key . '="' . $bool . '"';
		}
	}

	if ( ! empty( $config['filters'] ) ) {
		foreach ( $config['filters'] as $filter_key => $filter_type ) {
			$value = isset( $params[ $filter_key ] ) ? $params[ $filter_key ] : ( $config['defaults'][ $filter_key ] ?? '' );

			if ( in_array( $filter_type, array( 'calendar_multi', 'tag_multi' ), true ) ) {
				if ( is_array( $value ) ) {
					$values = array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
				} else {
					$values = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $value ) ) ) ) );
				}
				$value = implode( ',', $values );
			} else {
				$value = trim( (string) $value );
			}

			if ( $value === '' ) {
				continue;
			}

			if ( $filter_type === 'number' ) {
				$value = (string) absint( $value );
			} elseif ( $filter_type === 'date' ) {
				$value = preg_replace( '/[^0-9\-]/', '', $value );
			} elseif ( ! in_array( $filter_type, array( 'calendar_multi', 'tag_multi' ), true ) ) {
				$value = sanitize_text_field( $value );
			}

			if ( $value !== '' ) {
				$shortcode .= ' ' . $filter_key . '="' . esc_attr( $value ) . '"';
			}
		}
	}

	$shortcode .= ']';
	return $shortcode;
}

/**
 * AJAX endpoint: render live shortcode output.
 */
function cts_demo_live_builder_ajax_render(): void {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'cts_demo_live_builder' ) ) {
		wp_send_json_error( array( 'message' => 'Ungueltige Anfrage.' ), 403 );
	}

	$base = isset( $_POST['base'] ) ? sanitize_key( wp_unslash( $_POST['base'] ) ) : '';
	$view = isset( $_POST['view'] ) ? sanitize_text_field( wp_unslash( $_POST['view'] ) ) : '';
	$params = isset( $_POST['params'] ) && is_array( $_POST['params'] ) ? wp_unslash( $_POST['params'] ) : array();

	$shortcode = cts_demo_live_builder_build_shortcode( $base, $view, $params );
	if ( $shortcode === '' ) {
		wp_send_json_error( array( 'message' => 'Shortcode nicht unterstuetzt.' ), 400 );
	}

	wp_send_json_success(
		array(
			'shortcode' => $shortcode,
			'html' => do_shortcode( $shortcode ),
		)
	);
}
add_action( 'wp_ajax_cts_demo_live_builder_render', 'cts_demo_live_builder_ajax_render' );
add_action( 'wp_ajax_nopriv_cts_demo_live_builder_render', 'cts_demo_live_builder_ajax_render' );

/**
 * Render interactive live builder on demo pages.
 *
 * Usage: [cts_live_builder base="churchtools_events" view="list-classic"]
 */
function cts_demo_live_builder_shortcode( $atts ): string {
	$atts = shortcode_atts(
		array(
			'base' => 'churchtools_events',
			'view' => 'list-classic',
			'event_id' => '',
			'event_template' => '',
		),
		$atts,
		'cts_live_builder'
	);

	$base = sanitize_key( $atts['base'] );
	$view = sanitize_text_field( $atts['view'] );
	$config_all = cts_demo_live_builder_config();

	if ( ! isset( $config_all[ $base ] ) ) {
		return '<p><em>Unbekannter Builder-Typ.</em></p>';
	}

	$config = $config_all[ $base ];
	$labels = cts_demo_live_builder_labels();
	$db_options = cts_demo_live_builder_db_options();
	$context = cts_demo_live_builder_resolve_context( $base, $view );
	$resolved_base = $context['base'];
	$resolved_view = $context['view'];
	$view_type = $context['view_type'];
	$matrix_view = $context['matrix_view'];
	$defaults = $config['defaults'];

	if ( $base === 'cts_event' ) {
		if ( $atts['event_id'] !== '' ) {
			$defaults['id'] = (string) absint( $atts['event_id'] );
		}
		if ( in_array( $atts['event_template'], array( 'minimal', 'professional' ), true ) ) {
			$defaults['template'] = $atts['event_template'];
		}
	}
	$instance_id = 'cts-live-builder-' . wp_rand( 1000, 999999 );
	$nonce = wp_create_nonce( 'cts_demo_live_builder' );

	$initial_shortcode = cts_demo_live_builder_build_shortcode( $base, $view, $defaults );
	$initial_html = do_shortcode( $initial_shortcode );
	$calendar_options = $db_options['calendars'];
	$tag_options = $db_options['tags'];

	ob_start();
	?>
	<style>
		#<?php echo esc_html( $instance_id ); ?>.cts-live-builder {
			border: 1px solid #dcdcde;
			border-radius: 8px;
			padding: 14px;
			margin: 12px 0 16px;
			background: #f6f7f7;
			font-size: 13px;
			line-height: 1.4;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__title {
			font-size: 13px;
			font-weight: 600;
			margin: 0 0 8px;
			color: #1d2327;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__details {
			margin-bottom: 12px;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			background: #fff;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__summary {
			padding: 8px 10px;
			cursor: pointer;
			font-weight: 500;
			color: #1d2327;
			user-select: none;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__details-body {
			padding: 0 10px 10px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__grid {
			display: grid;
			gap: 12px;
			margin-bottom: 12px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__panel {
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 10px;
			background: #fff;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__panel-title {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
			color: #1d2327;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__toggle-grid {
			display: grid;
			gap: 8px;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__limit-row {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-top: 10px;
			padding-top: 8px;
			border-top: 1px solid #dcdcde;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__limit-label {
			font-size: 13px;
			color: #1d2327;
			white-space: nowrap;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__limit-input {
			width: 60px !important;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__toggle {
			display: flex;
			align-items: center;
			gap: 7px;
			color: #1d2327;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__filter-grid {
			display: grid;
			gap: 10px;
			grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__field {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__label {
			font-weight: 500;
			color: #1d2327;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder input[type="text"],
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder input[type="number"],
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder input[type="date"],
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder select {
			height: 32px;
			padding: 0 8px;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			background: #fff;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__multi {
			display: grid;
			gap: 4px;
			max-height: 140px;
			overflow: auto;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			padding: 8px;
			background: #fff;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__multi-item {
			display: flex;
			align-items: center;
			gap: 6px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__actions {
			display: flex;
			gap: 10px;
			align-items: center;
			flex-wrap: wrap;
			margin-bottom: 12px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__btn {
			height: 32px;
			padding: 0 12px;
			border-radius: 4px;
			border: 1px solid #2271b1;
			background: #2271b1;
			color: #fff;
			cursor: pointer;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__code {
			padding: 6px 8px;
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 4px;
		}
		#<?php echo esc_html( $instance_id ); ?> .cts-live-builder__result {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 10px;
		}
	</style>
	<div id="<?php echo esc_attr( $instance_id ); ?>" class="cts-live-builder">
		<p class="cts-live-builder__title">Live-Konfigurator</p>
		<details class="cts-live-builder__details">
			<summary class="cts-live-builder__summary">Einstellungen anzeigen</summary>
			<div class="cts-live-builder__details-body">
				<div class="cts-live-builder__grid">
					<div class="cts-live-builder__panel">
						<strong class="cts-live-builder__panel-title">Anzeige</strong>
						<div class="cts-live-builder__toggle-grid">
							<?php foreach ( $config['toggles'] as $toggle_key ) : ?>
								<?php if ( ! cts_demo_live_builder_is_toggle_allowed( $toggle_key, $resolved_base, $view_type, $matrix_view ) ) { continue; } ?>
								<label class="cts-live-builder__toggle">
									<input type="checkbox" data-cts-param="<?php echo esc_attr( $toggle_key ); ?>" <?php checked( ( $defaults[ $toggle_key ] ?? '0' ) === '1' ); ?>>
									<span><?php echo esc_html( $labels[ $toggle_key ] ?? $toggle_key ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php if ( isset( $config['filters']['limit'] ) ) : ?>
						<div class="cts-live-builder__limit-row">
							<label class="cts-live-builder__limit-label"><?php echo esc_html( $labels['limit'] ?? 'Anzahl' ); ?></label>
							<input type="number" min="1" max="100" data-cts-param="limit" value="<?php echo esc_attr( $defaults['limit'] ?? '10' ); ?>" class="cts-live-builder__limit-input">
						</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $config['filters'] ) ) : ?>
					<?php endif; ?>
				</div>

				<div class="cts-live-builder__actions">
					<code data-cts-shortcode class="cts-live-builder__code"><?php echo esc_html( $initial_shortcode ); ?></code>
				</div>
			</div>
		</details>

		<div data-cts-result class="cts-live-builder__result">
			<?php echo $initial_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>

	<script>
	(function(){
		const root = document.getElementById('<?php echo esc_js( $instance_id ); ?>');
		if(!root){ return; }
		const result = root.querySelector('[data-cts-result]');
		const shortEl = root.querySelector('[data-cts-shortcode]');

		const collectParams = () => {
			const params = {};
			root.querySelectorAll('[data-cts-param]').forEach((el) => {
				const key = el.getAttribute('data-cts-param');
				if(!key){ return; }
				if(el.type === 'checkbox'){
					params[key] = el.checked ? '1' : '0';
				} else {
					params[key] = el.value || '';
				}
			});
			root.querySelectorAll('[data-cts-param-multi]').forEach((el) => {
				const key = el.getAttribute('data-cts-param-multi');
				if(!key){ return; }
				if(!params[key]){ params[key] = []; }
				if(el.checked){ params[key].push(el.value || ''); }
			});
			return params;
		};

		const renderLive = () => {
			const formData = new FormData();
			formData.append('action', 'cts_demo_live_builder_render');
			formData.append('nonce', '<?php echo esc_js( $nonce ); ?>');
			formData.append('base', '<?php echo esc_js( $base ); ?>');
			formData.append('view', '<?php echo esc_js( $view ); ?>');
			const params = collectParams();
			Object.keys(params).forEach((k) => {
				if(Array.isArray(params[k])){
					params[k].forEach((v) => formData.append('params[' + k + '][]', v));
				} else {
					formData.append('params[' + k + ']', params[k]);
				}
			});

			fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			})
			.then((res) => res.json())
			.then((json) => {
				if(!json || !json.success){
					return;
				}
				if(shortEl){ shortEl.textContent = json.data.shortcode || ''; }
				if(result){ result.innerHTML = json.data.html || ''; }
			});
		};

		root.querySelectorAll('[data-cts-param], [data-cts-param-multi]').forEach((el) => {
			el.addEventListener('change', renderLive);
		});
	})();
	</script>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'cts_live_builder', 'cts_demo_live_builder_shortcode' );





