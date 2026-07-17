<?php
/**
 * ChurchTools Suite Version Manager
 * 
 * Holt Versionsnummern automatisch aus GitHub Releases
 * und cached sie für Performance.
 * 
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

if (!function_exists('cts_get_github_latest_version')) {
    /**
     * Holt die neueste Version eines GitHub Repositories
     * 
     * @param string $repo Repository im Format "owner/repo"
     * @param string $cache_key Eindeutiger Cache-Key
     * @param int $cache_time Cache-Zeit in Sekunden (default: 1 Stunde)
     * @return string Version (z.B. "1.0.6.2" oder "0.5.3")
     */
    function cts_get_github_latest_version($repo, $cache_key, $cache_time = 3600) {
        // Prüfe Cache
        $cached_version = get_transient($cache_key);
        if ($cached_version !== false) {
            return $cached_version;
        }
        
        // GitHub API Request
        $api_url = "https://api.github.com/repos/{$repo}/releases/latest";
        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'ChurchTools-Suite-Demo'
            )
        ));
        
        // Fehlerbehandlung
        if (is_wp_error($response)) {
            error_log('GitHub API Error: ' . $response->get_error_message());
            return 'unknown';
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['tag_name'])) {
            // Entferne "v" prefix falls vorhanden (v1.0.6.2 → 1.0.6.2)
            $version = ltrim($data['tag_name'], 'v');
            
            // Cache für 1 Stunde
            set_transient($cache_key, $version, $cache_time);
            
            return $version;
        }
        
        error_log('GitHub API: No tag_name found in response');
        return 'unknown';
    }
}

if (!function_exists('cts_get_main_plugin_version')) {
    /**
     * Holt die Version des Hauptplugins (ChurchTools Suite)
     * 
     * @return string Version
     */
    function cts_get_main_plugin_version() {
        return cts_get_github_latest_version(
            'FEGAschaffenburg/churchtools-suite',
            'cts_main_plugin_version',
            3600 // 1 Stunde Cache
        );
    }
}

if (!function_exists('cts_get_elementor_addon_version')) {
    /**
     * Holt die Version des Elementor Addons
     * 
     * @return string Version
     */
    function cts_get_elementor_addon_version() {
        return cts_get_github_latest_version(
            'FEGAschaffenburg/churchtools-suite-elementor',
            'cts_elementor_addon_version',
            3600 // 1 Stunde Cache
        );
    }
}

if (!function_exists('cts_get_demo_plugin_version')) {
    /**
     * Holt die Version des Demo Plugins aus dem installierten Plugin
     * 
     * @return string Version
     */
    function cts_get_demo_plugin_version() {
        // Suche nach installiertem Demo Plugin (mit oder ohne Versionssuffix)
        $plugin_dirs = array(
            'churchtools-suite-demo',
            'churchtools-suite-demo-' . get_option('cts_demo_plugin_version', ''),
        );
        
        // Durchsuche auch alle Verzeichnisse die mit churchtools-suite-demo beginnen
        $all_plugins = get_plugins();
        foreach ($all_plugins as $plugin_path => $plugin_data) {
            if (strpos($plugin_path, 'churchtools-suite-demo') === 0) {
                // Plugin gefunden - lese Version aus Header
                return $plugin_data['Version'];
            }
        }
        
        // Fallback: Versuche direkt aus bekannten Pfaden
        foreach ($plugin_dirs as $dir) {
            $plugin_file = WP_PLUGIN_DIR . '/' . $dir . '/churchtools-suite-demo.php';
            if (file_exists($plugin_file)) {
                if (!function_exists('get_plugin_data')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $plugin_data = get_plugin_data($plugin_file, false, false);
                if (!empty($plugin_data['Version'])) {
                    return $plugin_data['Version'];
                }
            }
        }
        
        return 'unknown';
    }
}

if (!function_exists('cts_clear_version_cache')) {
    /**
     * Löscht alle Version-Caches
     * Nützlich nach Plugin-Updates
     */
    function cts_clear_version_cache() {
        delete_transient('cts_main_plugin_version');
        delete_transient('cts_elementor_addon_version');
        // Demo Plugin wird direkt aus installiertem Plugin gelesen, kein Cache nötig
    }
}

// Admin Action zum manuellen Cache-Löschen
add_action('admin_post_cts_clear_version_cache', function() {
    check_admin_referer('cts_clear_version_cache');

    if (!current_user_can('manage_options')) {
        wp_die('Keine Berechtigung');
    }
    
    cts_clear_version_cache();
    
    $redirect_to = wp_get_referer() ?: admin_url();
    wp_safe_redirect(add_query_arg('cache_cleared', '1', $redirect_to));
    exit;
});

// Shortcodes für Version-Anzeige in Posts/Pages
add_shortcode('cts_version', function($atts) {
    $atts = shortcode_atts(array(
        'plugin' => 'main', // main, elementor, demo
    ), $atts);
    
    switch ($atts['plugin']) {
        case 'elementor':
            return esc_html(cts_get_elementor_addon_version());
        case 'demo':
            return esc_html(cts_get_demo_plugin_version());
        case 'main':
        default:
            return esc_html(cts_demo_get_cts_version());
    }
});

// Ajax Handler für Version-Aktualisierung (für JavaScript)
add_action('wp_ajax_cts_get_versions', function() {
    wp_send_json_success(array(
        'main' => cts_demo_get_cts_version(),
        'elementor' => cts_get_elementor_addon_version(),
        'demo' => cts_get_demo_plugin_version(),
    ));
});

add_action('wp_ajax_nopriv_cts_get_versions', function() {
    wp_send_json_success(array(
        'main' => cts_demo_get_cts_version(),
        'elementor' => cts_get_elementor_addon_version(),
        'demo' => cts_get_demo_plugin_version(),
    ));
});
