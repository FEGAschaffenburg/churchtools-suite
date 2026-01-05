<?php
/**
 * CSS Enqueue Debug Shortcode
 * 
 * Usage: [cts_debug_css]
 * 
 * Zeigt CSS-Enqueue-Status auf echten WordPress-Seiten.
 * Läuft NACH wp_enqueue_scripts und zeigt echten Status.
 * 
 * @package ChurchTools_Suite
 * @since   0.10.4.34
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Enqueue Debug Shortcode
 */
function cts_debug_css_enqueue_shortcode( $atts ) {
	global $wp_styles;
	
	ob_start();
	?>
	<div style="background: #fff3cd; border: 3px solid #ffc107; padding: 20px; margin: 20px 0; font-family: monospace;">
		<h2 style="margin-top: 0;">🔍 ChurchTools Suite - CSS Enqueue Debug</h2>
		
		<h3>1. CSS Datei Status</h3>
		<?php
		$css_path = CHURCHTOOLS_SUITE_PATH . 'assets/css/churchtools-suite-public.css';
		$css_url = CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-public.css';
		
		if ( file_exists( $css_path ) ) {
			echo '<p style="color: green;">✅ <strong>CSS-Datei existiert</strong></p>';
			echo '<ul>';
			echo '<li>Pfad: <code>' . esc_html( $css_path ) . '</code></li>';
			echo '<li>URL: <code>' . esc_html( $css_url ) . '</code></li>';
			echo '<li>Größe: ' . number_format( filesize( $css_path ) / 1024, 2 ) . ' KB</li>';
			echo '<li>Geändert: ' . date( 'Y-m-d H:i:s', filemtime( $css_path ) ) . '</li>';
			echo '</ul>';
		} else {
			echo '<p style="color: red;">❌ <strong>CSS-Datei NICHT gefunden!</strong></p>';
		}
		?>
		
		<h3>2. WordPress Enqueue Status</h3>
		<?php
		if ( isset( $wp_styles->registered['churchtools-suite-public'] ) ) {
			$style = $wp_styles->registered['churchtools-suite-public'];
			echo '<p style="color: green;">✅ <strong>CSS ist registriert</strong></p>';
			echo '<table style="border-collapse: collapse; width: 100%;">';
			echo '<tr><td style="padding: 5px; border: 1px solid #ccc;"><strong>Handle:</strong></td><td style="padding: 5px; border: 1px solid #ccc;">' . esc_html( $style->handle ) . '</td></tr>';
			echo '<tr><td style="padding: 5px; border: 1px solid #ccc;"><strong>Source:</strong></td><td style="padding: 5px; border: 1px solid #ccc;"><code>' . esc_html( $style->src ) . '</code></td></tr>';
			echo '<tr><td style="padding: 5px; border: 1px solid #ccc;"><strong>Version:</strong></td><td style="padding: 5px; border: 1px solid #ccc;">' . esc_html( $style->ver ) . '</td></tr>';
			echo '<tr><td style="padding: 5px; border: 1px solid #ccc;"><strong>Dependencies:</strong></td><td style="padding: 5px; border: 1px solid #ccc;">' . implode( ', ', $style->deps ) . '</td></tr>';
			echo '</table>';
			
			// Check if enqueued (not just registered)
			if ( wp_style_is( 'churchtools-suite-public', 'enqueued' ) ) {
				echo '<p style="color: green;">✅ <strong>CSS ist ENQUEUED (wird im &lt;head&gt; geladen)</strong></p>';
			} elseif ( wp_style_is( 'churchtools-suite-public', 'registered' ) ) {
				echo '<p style="color: orange;">⚠️ <strong>CSS ist registriert aber NICHT enqueued</strong></p>';
				echo '<p>Das CSS wird nur registriert wenn ein Shortcode/Block verwendet wird.</p>';
			}
		} else {
			echo '<p style="color: red;">❌ <strong>CSS ist NICHT registriert!</strong></p>';
			echo '<p>Das Plugin hat wp_enqueue_scripts Hook nicht ausgeführt!</p>';
			
			// Debug: Zeige alle registrierten Styles
			echo '<details><summary>Alle registrierten Styles (klicken zum Ausklappen)</summary>';
			echo '<pre style="max-height: 200px; overflow-y: scroll;">';
			print_r( array_keys( $wp_styles->registered ) );
			echo '</pre></details>';
		}
		?>
		
		<h3>3. Browser DevTools Check</h3>
		<ol>
			<li>F12 drücken → <strong>Network Tab</strong></li>
			<li>Filter: <strong>CSS</strong></li>
			<li>Seite neu laden (Ctrl+Shift+R)</li>
			<li>Suche: <code>churchtools-suite-public.css</code></li>
			<li>Status: <strong>200 OK</strong> = geladen, <strong>404</strong> = nicht gefunden</li>
		</ol>
		
		<h3>4. Media Queries Check</h3>
		<?php
		if ( file_exists( $css_path ) ) {
			$css_content = file_get_contents( $css_path );
			$has_480 = strpos( $css_content, '@media (max-width: 480px)' ) !== false;
			$has_768 = strpos( $css_content, '@media (max-width: 768px)' ) !== false;
			$has_1024 = strpos( $css_content, '@media (max-width: 1024px)' ) !== false;
			
			echo '<ul>';
			echo '<li>' . ( $has_480 ? '✅' : '❌' ) . ' Mobile (480px)</li>';
			echo '<li>' . ( $has_768 ? '✅' : '❌' ) . ' Tablet (768px)</li>';
			echo '<li>' . ( $has_1024 ? '✅' : '❌' ) . ' Desktop (1024px)</li>';
			echo '</ul>';
		}
		?>
		
		<h3>5. Plugin Info</h3>
		<ul>
			<li><strong>Version:</strong> <?php echo CHURCHTOOLS_SUITE_VERSION; ?></li>
			<li><strong>WP_DEBUG:</strong> <?php echo defined( 'WP_DEBUG' ) && WP_DEBUG ? 'AN ✅' : 'AUS ❌'; ?></li>
		</ul>
	</div>
	<?php
	return ob_get_clean();
}

// Register shortcode
add_shortcode( 'cts_debug_css', 'cts_debug_css_enqueue_shortcode' );
