<?php
/**
 * OPcache Reset für ChurchTools Suite
 * 
 * VERWENDUNG:
 * https://deine-domain.de/wp-content/plugins/churchtools-suite/clear-opcache.php
 */

// WordPress laden
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('❌ Keine Berechtigung');
}

echo '<h1>🔄 ChurchTools Suite - OPcache Reset</h1>';
echo '<div style="font-family: monospace; padding: 20px; background: #f5f5f5; border-radius: 8px;">';

// OPcache Reset
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    if ($result) {
        echo '<p style="color: green; font-weight: bold;">✅ OPcache erfolgreich geleert!</p>';
    } else {
        echo '<p style="color: orange;">⚠️ OPcache konnte nicht geleert werden (evtl. bereits leer)</p>';
    }
} else {
    echo '<p style="color: gray;">ℹ️ OPcache nicht verfügbar (nicht installiert oder deaktiviert)</p>';
}

// Weitere Caches
wp_cache_flush();
echo '<p style="color: green;">✅ WordPress Object Cache geleert</p>';

echo '<h2>NÄCHSTE SCHRITTE:</h2>';
echo '<ol>';
echo '<li><strong>Seite neu laden:</strong> <a href="' . home_url() . '?nocache=' . time() . '" target="_blank">Zur Website</a></li>';
echo '<li><strong>Hard Reload:</strong> Strg+Shift+R im Browser</li>';
echo '<li><strong>Block Editor testen:</strong> Gutenberg Block prüfen</li>';
echo '</ol>';

echo '<h2>SYSTEM-INFO:</h2>';
echo '<pre>';
echo 'Plugin Version: ' . (defined('CHURCHTOOLS_SUITE_VERSION') ? CHURCHTOOLS_SUITE_VERSION : 'NICHT GELADEN') . "\n";
echo 'OPcache aktiv: ' . (function_exists('opcache_get_status') ? 'JA' : 'NEIN') . "\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status !== false) {
        echo 'OPcache Status: ' . ($status['opcache_enabled'] ? 'AKTIVIERT' : 'DEAKTIVIERT') . "\n";
        echo 'Cached Files: ' . ($status['opcache_statistics']['num_cached_scripts'] ?? 'UNKNOWN') . "\n";
    }
}
echo '</pre>';

echo '</div>';
