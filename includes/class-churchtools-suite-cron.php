<?php
/**
 * Cron Job Handler
 *
 * @package ChurchTools_Suite
 * @since   0.3.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Cron {
    
    /**
     * Schedule cron jobs
     */
    public static function schedule_jobs() {
        // Session Keep-Alive: Stündlich ChurchTools API anpingen
        if (!wp_next_scheduled('churchtools_suite_session_keepalive')) {
            wp_schedule_event(time(), 'hourly', 'churchtools_suite_session_keepalive');
        }
    }
    
    /**
     * Clear scheduled cron jobs
     */
    public static function clear_jobs() {
        $timestamp = wp_next_scheduled('churchtools_suite_session_keepalive');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'churchtools_suite_session_keepalive');
        }
    }
    
    /**
     * Session Keep-Alive: Ping ChurchTools API
     * 
     * Wird stündlich ausgeführt um die Session am Leben zu halten
     */
    public static function session_keepalive() {
        // Nur ausführen wenn konfiguriert
        $ct_url = get_option('churchtools_suite_ct_url', '');
        $ct_username = get_option('churchtools_suite_ct_username', '');
        $ct_password = get_option('churchtools_suite_ct_password', '');
        
        if (empty($ct_url) || empty($ct_username) || empty($ct_password)) {
            return;
        }
        
        // CT Client laden und whoami aufrufen
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
        
        $client = new ChurchTools_Suite_CT_Client();
        
        // Wenn nicht authentifiziert, versuche Login
        if (!$client->is_authenticated()) {
            $login_result = $client->login();
            if (!$login_result['success']) {
                error_log('ChurchTools Suite: Session Keep-Alive Login fehlgeschlagen - ' . $login_result['message']);
                return;
            }
        }
        
        // Ping API mit whoami
        $result = $client->api_request('whoami', 'GET');
        
        if (is_wp_error($result)) {
            error_log('ChurchTools Suite: Session Keep-Alive fehlgeschlagen - ' . $result->get_error_message());
        } else {
            // Update last keepalive timestamp
            update_option('churchtools_suite_last_keepalive', current_time('mysql'));
        }
    }
}
