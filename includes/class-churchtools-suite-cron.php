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
     * Initialize cron system
     */
    public static function init(): void {
        // Register custom cron intervals
        add_filter('cron_schedules', [__CLASS__, 'add_custom_cron_intervals']);
    }
    
    /**
     * Add custom cron intervals
     * 
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public static function add_custom_cron_intervals(array $schedules): array {
        // Täglich (24 Stunden) - WordPress hat kein natives 'daily'!
        $schedules['daily'] = [
            'interval' => 86400, // 24 * 60 * 60
            'display'  => __('Täglich', 'churchtools-suite')
        ];
        
        // 2 Tage
        $schedules['cts_2days'] = [
            'interval' => 172800, // 2 * 24 * 60 * 60
            'display'  => __('Alle 2 Tage', 'churchtools-suite')
        ];
        
        // 3 Tage
        $schedules['cts_3days'] = [
            'interval' => 259200, // 3 * 24 * 60 * 60
            'display'  => __('Alle 3 Tage', 'churchtools-suite')
        ];
        
        // 7 Tage (wöchentlich)
        $schedules['cts_weekly'] = [
            'interval' => 604800, // 7 * 24 * 60 * 60
            'display'  => __('Wöchentlich', 'churchtools-suite')
        ];
        
        // 14 Tage
        $schedules['cts_2weeks'] = [
            'interval' => 1209600, // 14 * 24 * 60 * 60
            'display'  => __('Alle 2 Wochen', 'churchtools-suite')
        ];
        
        // 30 Tage (monatlich)
        $schedules['cts_monthly'] = [
            'interval' => 2592000, // 30 * 24 * 60 * 60
            'display'  => __('Monatlich', 'churchtools-suite')
        ];
        
        return $schedules;
    }
    
    /**
     * Schedule cron jobs
     */
    public static function schedule_jobs() {
        // Session Keep-Alive: schedule according to cookie expiry when possible
        // Clear existing keepalive schedules to avoid duplicates
        wp_clear_scheduled_hook('churchtools_suite_session_keepalive');

        // Try to schedule based on stored CT cookies (expires)
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
        $client = new ChurchTools_Suite_CT_Client();
        $cookies = $client->get_cookies();

        $buffer_seconds = 300; // refresh 5 minutes before cookie expiry
        $now = time();
        $scheduled = false;

        if (!empty($cookies) && is_array($cookies)) {
            $max_expires = 0;
            foreach ($cookies as $c) {
                if (!empty($c['expires']) && is_numeric($c['expires'])) {
                    $max_expires = max($max_expires, (int) $c['expires']);
                }
            }

            if ($max_expires > $now + 10) {
                $next_run = max($now + 60, $max_expires - $buffer_seconds);
                wp_schedule_single_event($next_run, 'churchtools_suite_session_keepalive');
                $scheduled = true;
            }
        }

        // Fallback: schedule hourly if no cookie expiry info available
        if (!$scheduled) {
            if (!wp_next_scheduled('churchtools_suite_session_keepalive')) {
                wp_schedule_event(time(), 'hourly', 'churchtools_suite_session_keepalive');
            }
        }
        
        // Auto-Sync: Falls aktiviert, Schedule erstellen
        self::update_sync_schedule();
    }
    
    /**
     * Clear scheduled cron jobs
     */
    public static function clear_jobs() {
        $timestamp = wp_next_scheduled('churchtools_suite_session_keepalive');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'churchtools_suite_session_keepalive');
        }
        
        $timestamp = wp_next_scheduled('churchtools_suite_auto_sync');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'churchtools_suite_auto_sync');
        }
    }
    
    /**
	 * Update auto-sync schedule based on settings (v0.10.1.5, v0.10.2.3: Robust cleanup)
	 */
	public static function update_sync_schedule() {
		$auto_sync_enabled = get_option('churchtools_suite_auto_sync_enabled', 0);
		$interval = get_option('churchtools_suite_auto_sync_interval', 'daily'); // v0.10.2.0: Default 'daily' (nicht 'hourly'!)
		
		// v0.10.2.3: ALLE existierenden Schedules löschen (nicht nur den nächsten!)
		// wp_clear_scheduled_hook() ist manchmal unzuverlässig, direkter Zugriff auf Cron-Array
		$crons = _get_cron_array();
		if ( is_array( $crons ) ) {
			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron['churchtools_suite_auto_sync'] ) ) {
					unset( $crons[ $timestamp ]['churchtools_suite_auto_sync'] );
					if ( empty( $crons[ $timestamp ] ) ) {
						unset( $crons[ $timestamp ] );
					}
				}
			}
			_set_cron_array( $crons );
		}
		
		// Schedule new job if enabled
		if ($auto_sync_enabled) {
			// Calculate next run time based on interval
			$next_run = self::calculate_next_run_time($interval);
			
			wp_schedule_event($next_run, $interval, 'churchtools_suite_auto_sync');
			
			// Log schedule update
			if (class_exists('ChurchTools_Suite_Logger')) {
				ChurchTools_Suite_Logger::info('cron', 'Auto-sync schedule updated', [
					'interval' => $interval,
					'next_run' => date('Y-m-d H:i:s', $next_run),
					'next_run_timestamp' => $next_run,
				]);
			}
		}
	}
	
	/**
	 * Calculate next run time based on interval (v0.10.1.5)
	 * 
	 * @param string $interval Interval (hourly, twicedaily, daily)
	 * @return int Timestamp for next run
	 */
	private static function calculate_next_run_time($interval) {
		switch ($interval) {
			case 'hourly':
				// Run at top of next hour
				return strtotime('+1 hour', strtotime(date('Y-m-d H:00:00')));
			
			case 'twicedaily':
				// Run at midnight and noon
				$current_hour = (int) date('H');
				if ($current_hour < 12) {
					return strtotime('today 12:00:00');
				} else {
					return strtotime('tomorrow 00:00:00');
				}
			
			case 'daily':
				// Run at 3 AM tomorrow
				return strtotime('tomorrow 03:00:00');
			
			default:
				// Fallback: 1 hour from now
				return time() + HOUR_IN_SECONDS;
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
        $auth_method = get_option('churchtools_suite_ct_auth_method', 'password');
        $ct_username = get_option('churchtools_suite_ct_username', '');
        $ct_password = get_option('churchtools_suite_ct_password', '');
        $ct_token = get_option('churchtools_suite_ct_token', '');
        
        $has_password_creds = !empty($ct_url) && !empty($ct_username) && !empty($ct_password);
        $has_token_creds = !empty($ct_url) && !empty($ct_token);

        if (($auth_method === 'password' && !$has_password_creds) || ($auth_method === 'token' && !$has_token_creds)) {
            return;
        }
        
        // CT Client laden und whoami aufrufen
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';

        $client = new ChurchTools_Suite_CT_Client();

        if (!$client->is_authenticated()) {
            $login_result = $client->login();
            if (!$login_result['success']) {
                error_log('ChurchTools Suite: Session Keep-Alive Login fehlgeschlagen - ' . $login_result['message']);
                self::reschedule_keepalive_after_attempt($client);
                return;
            }
        }

        // Ping API mit whoami (bei Token auch Validierung)
        $result = $client->api_request('whoami', 'GET');

        if (is_wp_error($result)) {
            error_log('ChurchTools Suite: Session Keep-Alive fehlgeschlagen - ' . $result->get_error_message());
        } else {
            // Update last keepalive timestamp
            update_option('churchtools_suite_last_keepalive', current_time('mysql'));
        }

        // After performing keepalive, reschedule next run based on refreshed cookies
        self::reschedule_keepalive_after_attempt($client);
    }

    /**
     * Reschedule keepalive based on client's cookie expiries.
     * If no useful expiry found, schedule hourly fallback.
     *
     * @param ChurchTools_Suite_CT_Client $client
     */
    private static function reschedule_keepalive_after_attempt($client) {
        // Clear any existing scheduled hooks first
        wp_clear_scheduled_hook('churchtools_suite_session_keepalive');

        $cookies = $client->get_cookies();
        $buffer_seconds = 300; // 5 minutes before expiry
        $now = time();
        $scheduled = false;

        if (!empty($cookies) && is_array($cookies)) {
            $max_expires = 0;
            foreach ($cookies as $c) {
                if (!empty($c['expires']) && is_numeric($c['expires'])) {
                    $max_expires = max($max_expires, (int) $c['expires']);
                }
            }

            if ($max_expires > $now + 10) {
                $next_run = max($now + 60, $max_expires - $buffer_seconds);
                wp_schedule_single_event($next_run, 'churchtools_suite_session_keepalive');
                $scheduled = true;
            }
        }

        // Fallback: hourly recurring event
        if (!$scheduled) {
            if (!wp_next_scheduled('churchtools_suite_session_keepalive')) {
                wp_schedule_event(time(), 'hourly', 'churchtools_suite_session_keepalive');
            }
        }
    }
    
    /**
     * Auto-Sync: Synchronize events automatically
     * 
     * Wird in konfigurierbaren Intervallen ausgeführt
     * Mit erweitertem Error-Handling, Fehler-Tracking und Sync-Historie
     */
    public static function auto_sync() {
        $start_time = current_time('mysql');
        
        // Nur ausführen wenn aktiviert
        $auto_sync_enabled = get_option('churchtools_suite_auto_sync_enabled', 0);
        if (!$auto_sync_enabled) {
            return;
        }
        
        // Sync-Historie Repository laden
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-sync-history-repository.php';
        $history_repo = new ChurchTools_Suite_Sync_History_Repository();
        
        // Historie-Eintrag erstellen
        $sync_id = $history_repo->create_sync_entry('auto', $start_time);
        
        try {
            // Event Sync Service laden
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-event-sync-service.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
            
            // Service initialisieren (MIT Service-Repositories für Service-Import)
            $ct_client = new ChurchTools_Suite_CT_Client();
            $events_repo = new ChurchTools_Suite_Events_Repository();
            $calendars_repo = new ChurchTools_Suite_Calendars_Repository();
            $event_services_repo = new ChurchTools_Suite_Event_Services_Repository();
            $services_repo = new ChurchTools_Suite_Services_Repository();
            $sync_service = new ChurchTools_Suite_Event_Sync_Service(
                $ct_client, 
                $events_repo, 
                $calendars_repo,
                $event_services_repo,
                $services_repo
            );
            
            // Sync ausführen
            $result = $sync_service->sync_events();
            
            // Prüfe ob WP_Error zurückgegeben wurde
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Success - Fehler löschen und Stats speichern
            delete_option('churchtools_suite_last_sync_error');
            delete_option('churchtools_suite_last_sync_error_time');
            
            $stats = [
                'calendars_processed' => $result['calendars_processed'] ?? 0,
                'events_found' => $result['events_found'] ?? 0,
                'events_inserted' => $result['events_inserted'] ?? 0,
                'events_updated' => $result['events_updated'] ?? 0,
                'events_skipped' => $result['events_skipped'] ?? 0,
                'services_imported' => $result['services_imported'] ?? 0,
                'started_at' => $start_time,
                'completed_at' => current_time('mysql')
            ];
            
            update_option('churchtools_suite_last_auto_sync', current_time('mysql'));
            update_option('churchtools_suite_last_sync_status', 'success');
            update_option('churchtools_suite_last_sync_stats', $stats);
            
            // Historie-Eintrag abschließen
            if ($sync_id) {
                $history_repo->complete_sync($sync_id, $stats, null);
            }
            
            // Success Log
            error_log(sprintf(
                'ChurchTools Suite Auto-Sync [SUCCESS]: %d Kalender, %d Events gefunden, %d neu, %d aktualisiert, %d übersprungen, %d Services importiert',
                $stats['calendars_processed'],
                $stats['events_found'],
                $stats['events_inserted'],
                $stats['events_updated'],
                $stats['events_skipped'],
                $stats['services_imported']
            ));
            
        } catch (Exception $e) {
            // Error - Details speichern
            $error_message = $e->getMessage();
            $error_time = current_time('mysql');
            
            update_option('churchtools_suite_last_sync_error', $error_message);
            update_option('churchtools_suite_last_sync_error_time', $error_time);
            update_option('churchtools_suite_last_sync_status', 'error');
            
            // Historie-Eintrag mit Fehler abschließen
            if ($sync_id) {
                $history_repo->complete_sync($sync_id, [], $error_message);
            }
            
            // Error Log mit Stack Trace
            error_log(sprintf(
                'ChurchTools Suite Auto-Sync [ERROR]: %s (Zeit: %s)',
                $error_message,
                $error_time
            ));
            
            // Detaillierter Stack Trace im Debug-Modus
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Stack Trace: ' . $e->getTraceAsString());
            }
        }
    }
}
