<?php
/**
 * Force Cache Clear & Plugin Reload
 * 
 * VERWENDUNG:
 * 1. Diese Datei ins Plugin-Verzeichnis kopieren
 * 2. Im Browser aufrufen: https://deine-domain.de/wp-content/plugins/churchtools-suite/force-cache-clear.php
 * 3. Plugin deaktivieren + aktivieren
 */

// WordPress laden
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Keine Berechtigung');
}

echo '<h1>ChurchTools Suite - Force Cache Clear</h1>';

// 1. WordPress Object Cache leeren
wp_cache_flush();
echo '<p>✅ WordPress Object Cache geleert</p>';

// 2. Transients löschen
delete_transient('churchtools_suite_version_check');
echo '<p>✅ Transients gelöscht</p>';

// 3. CSS/JS Cache hinweise
echo '<h2>NÄCHSTE SCHRITTE:</h2>';
echo '<ol>';
echo '<li><strong>WordPress Admin → Plugins → ChurchTools Suite DEAKTIVIEREN</strong></li>';
echo '<li><strong>ChurchTools Suite AKTIVIEREN</strong></li>';
echo '<li><strong>Browser-Cache leeren:</strong><ul>';
echo '<li>Chrome: Strg+Shift+R (Hard Reload)</li>';
echo '<li>Firefox: Strg+F5</li>';
echo '<li>Safari: Cmd+Option+R</li>';
echo '</ul></li>';
echo '<li><strong>Wenn Caching-Plugin aktiv:</strong><ul>';
echo '<li>WP Rocket → Cache leeren</li>';
echo '<li>W3 Total Cache → Performance → Purge All Caches</li>';
echo '<li>WP Super Cache → Contents löschen</li>';
echo '</ul></li>';
echo '</ol>';

echo '<h2>DEBUG-INFO:</h2>';
echo '<pre>';
echo 'Plugin Version: ' . (defined('CHURCHTOOLS_SUITE_VERSION') ? CHURCHTOOLS_SUITE_VERSION : 'NICHT GELADEN') . "\n";
echo 'Plugin Pfad: ' . plugin_dir_path(__FILE__) . "\n";
echo 'WordPress Version: ' . get_bloginfo('version') . "\n";
echo 'PHP Version: ' . phpversion() . "\n";
echo '</pre>';

// 4. CSS-Datei Check
$css_file = plugin_dir_path(__FILE__) . 'assets/css/churchtools-suite-public.css';
if (file_exists($css_file)) {
    $css_size = filesize($css_file);
    $css_modified = date('Y-m-d H:i:s', filemtime($css_file));
    echo '<p>✅ CSS-Datei gefunden: ' . round($css_size / 1024, 2) . ' KB (geändert: ' . $css_modified . ')</p>';
    
    // Prüfe ob Media Queries vorhanden
    $css_content = file_get_contents($css_file);
    if (strpos($css_content, '@media (max-width: 480px)') !== false) {
        echo '<p>✅ Responsive Media Queries vorhanden</p>';
    } else {
        echo '<p>❌ FEHLER: Responsive Media Queries NICHT gefunden!</p>';
    }
} else {
    echo '<p>❌ FEHLER: CSS-Datei nicht gefunden!</p>';
}
