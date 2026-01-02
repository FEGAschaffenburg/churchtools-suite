<?php
/**
 * Cache Clearer - Temporäre Datei zum Leeren aller Caches
 * 
 * ACHTUNG: Nach Nutzung diese Datei wieder löschen!
 * 
 * Aufruf: https://IHRE-DOMAIN.de/wp-content/plugins/churchtools-suite/clear-cache.php
 */

// WordPress laden
$wp_load = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load)) {
	die('WordPress nicht gefunden. Bitte Pfad überprüfen.');
}
require_once $wp_load;

echo '<h1>ChurchTools Suite - Cache Clearer</h1>';
echo '<p><strong>Status:</strong></p><ul>';

// 1. PHP OPcache leeren
if (function_exists('opcache_reset')) {
	opcache_reset();
	echo '<li>✅ PHP OPcache geleert</li>';
} else {
	echo '<li>⚠️ PHP OPcache nicht verfügbar</li>';
}

// 2. WordPress Transients leeren
delete_transient('churchtools_suite_presets');
delete_transient('churchtools_suite_calendars');
echo '<li>✅ WordPress Transients geleert</li>';

// 3. Elementor Cache leeren (falls Elementor aktiv)
if (class_exists('\Elementor\Plugin')) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	echo '<li>✅ Elementor Cache geleert</li>';
} else {
	echo '<li>⚠️ Elementor nicht aktiv</li>';
}

// 4. Object Cache leeren
wp_cache_flush();
echo '<li>✅ WordPress Object Cache geleert</li>';

echo '</ul>';
echo '<p><strong>✅ Alle Caches erfolgreich geleert!</strong></p>';
echo '<p><a href="' . admin_url('admin.php?page=elementor') . '">→ Zurück zu Elementor</a></p>';
echo '<hr>';
echo '<p style="color:#d63638;"><strong>WICHTIG:</strong> Löschen Sie diese Datei jetzt aus Sicherheitsgründen!</p>';
echo '<pre>Datei: ' . __FILE__ . '</pre>';
