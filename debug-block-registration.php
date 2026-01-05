<?php
/**
 * Debug Block Registration
 * 
 * Zeigt alle registrierten Gutenberg Blocks und ChurchTools-spezifische Infos
 * 
 * USAGE: Im Browser aufrufen: /wp-content/plugins/churchtools-suite/debug-block-registration.php
 */

// WordPress laden
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung' );
}

echo '<h1>Block Registration Debug</h1>';
echo '<style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}h2{color:#4ec9b0}pre{background:#2d2d2d;padding:10px;border-left:3px solid #007acc}.ok{color:#4ec9b0}.error{color:#f48771}</style>';

// 1. Check if WP_Block_Type_Registry exists
echo '<h2>1. WordPress Block System</h2>';
if ( class_exists( 'WP_Block_Type_Registry' ) ) {
	echo '<span class="ok">✅ WP_Block_Type_Registry verfügbar</span><br>';
	
	$registry = WP_Block_Type_Registry::get_instance();
	$all_blocks = $registry->get_all_registered();
	
	echo '<strong>Anzahl registrierter Blocks:</strong> ' . count( $all_blocks ) . '<br><br>';
	
	// Suche nach ChurchTools Blocks
	$ct_blocks = array_filter( $all_blocks, function( $block_name ) {
		return strpos( $block_name, 'churchtools' ) !== false;
	}, ARRAY_FILTER_USE_KEY );
	
	if ( ! empty( $ct_blocks ) ) {
		echo '<span class="ok">✅ ChurchTools Blocks gefunden: ' . count( $ct_blocks ) . '</span><br>';
		foreach ( $ct_blocks as $block_name => $block_type ) {
			echo '<pre>';
			echo '<strong>Block:</strong> ' . esc_html( $block_name ) . "\n";
			echo '<strong>Title:</strong> ' . esc_html( $block_type->title ) . "\n";
			echo '<strong>Kategorie:</strong> ' . esc_html( $block_type->category ) . "\n";
			echo '<strong>Render Callback:</strong> ' . ( is_callable( $block_type->render_callback ) ? 'JA' : 'NEIN' ) . "\n";
			echo '<strong>Attributes:</strong> ' . count( $block_type->attributes ) . "\n";
			echo '</pre>';
		}
	} else {
		echo '<span class="error">❌ KEINE ChurchTools Blocks registriert!</span><br>';
	}
} else {
	echo '<span class="error">❌ WP_Block_Type_Registry NICHT verfügbar!</span><br>';
}

echo '<br>';

// 2. Check Block Categories
echo '<h2>2. Block Kategorien</h2>';
$categories = get_default_block_categories();
$has_ct_category = false;
foreach ( $categories as $cat ) {
	if ( $cat['slug'] === 'churchtools-suite' ) {
		$has_ct_category = true;
		echo '<span class="ok">✅ ChurchTools Suite Kategorie gefunden</span><br>';
		echo '<pre>' . print_r( $cat, true ) . '</pre>';
	}
}

if ( ! $has_ct_category ) {
	echo '<span class="error">❌ ChurchTools Suite Kategorie NICHT gefunden!</span><br>';
	echo '<strong>Verfügbare Kategorien:</strong><pre>';
	foreach ( $categories as $cat ) {
		echo '- ' . esc_html( $cat['slug'] ) . ' (' . esc_html( $cat['title'] ) . ")\n";
	}
	echo '</pre>';
}

echo '<br>';

// 3. Check if Blocks class is loaded
echo '<h2>3. ChurchTools Suite Classes</h2>';
if ( class_exists( 'ChurchTools_Suite_Blocks' ) ) {
	echo '<span class="ok">✅ ChurchTools_Suite_Blocks class geladen</span><br>';
	
	// Try to get block status from options
	$block_status = get_option( 'churchtools_suite_block_status', [] );
	if ( ! empty( $block_status ) ) {
		echo '<pre>';
		print_r( $block_status );
		echo '</pre>';
	}
} else {
	echo '<span class="error">❌ ChurchTools_Suite_Blocks class NICHT geladen!</span><br>';
}

echo '<br>';

// 4. Check JavaScript file
echo '<h2>4. JavaScript Assets</h2>';
$js_file = CHURCHTOOLS_SUITE_PATH . 'assets/js/churchtools-suite-blocks.js';
if ( file_exists( $js_file ) ) {
	echo '<span class="ok">✅ JavaScript-Datei existiert</span><br>';
	echo '<strong>Pfad:</strong> ' . esc_html( $js_file ) . '<br>';
	echo '<strong>Größe:</strong> ' . filesize( $js_file ) . ' Bytes<br>';
	echo '<strong>URL:</strong> ' . esc_html( CHURCHTOOLS_SUITE_URL . 'assets/js/churchtools-suite-blocks.js' ) . '<br>';
	
	// Check if wp_enqueue_script was called
	global $wp_scripts;
	if ( isset( $wp_scripts->registered['churchtools-suite-blocks'] ) ) {
		echo '<span class="ok">✅ Script registriert in WordPress</span><br>';
		$script = $wp_scripts->registered['churchtools-suite-blocks'];
		echo '<strong>Version:</strong> ' . esc_html( $script->ver ) . '<br>';
		echo '<strong>Dependencies:</strong> ' . implode( ', ', $script->deps ) . '<br>';
	} else {
		echo '<span class="error">⚠️ Script NICHT registriert (normal im Admin-Kontext)</span><br>';
	}
} else {
	echo '<span class="error">❌ JavaScript-Datei NICHT gefunden!</span><br>';
}

echo '<br>';

// 5. Plugin Version
echo '<h2>5. Plugin Info</h2>';
echo '<strong>Version:</strong> ' . esc_html( CHURCHTOOLS_SUITE_VERSION ) . '<br>';
echo '<strong>Path:</strong> ' . esc_html( CHURCHTOOLS_SUITE_PATH ) . '<br>';
echo '<strong>URL:</strong> ' . esc_html( CHURCHTOOLS_SUITE_URL ) . '<br>';

echo '<br><hr>';
echo '<a href="' . admin_url( 'post-new.php' ) . '">→ Neuen Beitrag erstellen (Block testen)</a><br>';
echo '<a href="' . admin_url( 'admin.php?page=churchtools-suite&tab=debug' ) . '">→ ChurchTools Suite Debug</a>';
