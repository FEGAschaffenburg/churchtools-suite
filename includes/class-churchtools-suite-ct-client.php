<?php
/**
 * ChurchTools API Client
 *
 * Handles authentication and API communication with ChurchTools
 *
 * @package ChurchTools_Suite
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_CT_Client {
    
    /**
     * ChurchTools URL
     */
    private $url;
    
    /**
     * Username (Email)
     */
    private $username;
    
    /**
     * Password
     */
    private $password;
    
    /**
     * Session cookies
     */
    private $cookies;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->url = get_option('churchtools_suite_ct_url', '');
        $this->username = get_option('churchtools_suite_ct_username', '');
        $this->password = get_option('churchtools_suite_ct_password', '');
        $this->cookies = get_option('churchtools_suite_ct_cookies', []);
    }
    
    /**
     * Login to ChurchTools and get authentication token
     *
     * @return array Success status and message
     */
    public function login() {
        // Validate required fields
        if (empty($this->url) || empty($this->username) || empty($this->password)) {
            return [
                'success' => false,
                'message' => 'ChurchTools URL, Benutzername und Passwort sind erforderlich.'
            ];
        }
        
        // Build login URL
        $login_url = trailingslashit($this->url) . 'api/login';
        
        // Prepare login data
        $login_data = [
            'username' => $this->username,
            'password' => $this->password
        ];
        
        // Send login request
        $response = wp_remote_post($login_url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($login_data),
            'timeout' => 30
        ]);
        
        // Check for errors
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Verbindungsfehler: ' . $response->get_error_message()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check status code
        if ($status_code !== 200) {
            $error_message = 'Login fehlgeschlagen (HTTP ' . $status_code . ')';
            if (isset($data['data']['message'])) {
                $error_message .= ': ' . $data['data']['message'];
            } elseif (isset($data['message'])) {
                $error_message .= ': ' . $data['message'];
            }
            return [
                'success' => false,
                'message' => $error_message
            ];
        }
        
        // Check login success
        if (!isset($data['data']['status']) || $data['data']['status'] !== 'success') {
            return [
                'success' => false,
                'message' => 'Login fehlgeschlagen: ' . ($data['data']['message'] ?? 'Unbekannter Fehler')
            ];
        }
        
        // Extract cookies from response
        $cookies = wp_remote_retrieve_cookies($response);
        
        if (empty($cookies)) {
            return [
                'success' => false,
                'message' => 'Keine Session-Cookies erhalten.'
            ];
        }
        
        // Convert WP_Http_Cookie objects to array for storage
        $cookie_array = [];
        foreach ($cookies as $cookie) {
            $cookie_array[] = [
                'name' => $cookie->name,
                'value' => $cookie->value,
                'expires' => $cookie->expires,
                'path' => $cookie->path,
                'domain' => $cookie->domain
            ];
        }
        
        $this->cookies = $cookie_array;
        
        // Save cookies to database
        update_option('churchtools_suite_ct_cookies', $this->cookies);
        
        // Save user info if available
        if (!empty($data['data']['personId'])) {
            update_option('churchtools_suite_ct_person_id', $data['data']['personId']);
        }
        
        // Update last login time
        update_option('churchtools_suite_ct_last_login', current_time('mysql'));
        
        return [
            'success' => true,
            'message' => 'Erfolgreich mit ChurchTools verbunden.',
            'person_id' => $data['data']['personId'] ?? null
        ];
    }
    
    /**
     * Test connection to ChurchTools
     *
     * @return array Success status and message
     */
    public function test_connection() {
        // First try to login
        $login_result = $this->login();
        
        if (!$login_result['success']) {
            return $login_result;
        }
        
        // Test API access by fetching whoami
        $whoami_url = trailingslashit($this->url) . 'api/whoami';
        
        // Prepare cookies for request
        $wp_cookies = $this->prepare_cookies_for_request();
        
        $response = wp_remote_get($whoami_url, [
            'cookies' => $wp_cookies,
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'API-Test fehlgeschlagen: ' . $response->get_error_message()
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return [
                'success' => false,
                'message' => 'API-Zugriff fehlgeschlagen (HTTP ' . $status_code . ')'
            ];
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Save user info
        if (!empty($data['data'])) {
            update_option('churchtools_suite_ct_user_info', $data['data']);
        }
        
        return [
            'success' => true,
            'message' => 'Verbindung erfolgreich. API-Zugriff funktioniert.',
            'user_info' => $data['data'] ?? []
        ];
    }
    
    /**
     * Make an authenticated API request
     *
     * @param string $endpoint API endpoint (e.g., 'calendars')
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $data Request data for POST/PUT requests
     * @return array|WP_Error Response data or error
     */
    public function api_request($endpoint, $method = 'GET', $data = []) {
        // Check if we have valid cookies, re-login if expired
        if (!$this->is_authenticated()) {
            $login_result = $this->login();
            if (!$login_result['success']) {
                return new WP_Error('no_cookies', $login_result['message']);
            }
        }
        
        // Build URL
        $url = trailingslashit($this->url) . 'api/' . ltrim($endpoint, '/');
        
        // Prepare cookies for request
        $wp_cookies = $this->prepare_cookies_for_request();
        
        // Prepare request arguments
        $args = [
            'method' => strtoupper($method),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'cookies' => $wp_cookies,
            'timeout' => 30
        ];
        
        // Add body for POST/PUT requests
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        // Send request
        $response = wp_remote_request($url, $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        // Handle 401 - try to re-login once
        if ($status_code === 401) {
            $login_result = $this->login();
            if ($login_result['success']) {
                // Retry request with new cookies
                $args['cookies'] = $this->prepare_cookies_for_request();
                $response = wp_remote_request($url, $args);
                
                if (is_wp_error($response)) {
                    return $response;
                }
                
                $status_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $decoded = json_decode($body, true);
            }
        }
        
        // Check status code
        if ($status_code < 200 || $status_code >= 300) {
            $error_message = 'API-Fehler (HTTP ' . $status_code . ')';
            if (isset($decoded['message'])) {
                $error_message .= ': ' . $decoded['message'];
            }
            return new WP_Error('api_error', $error_message, ['status' => $status_code]);
        }
        
        return $decoded;
    }
    
    /**
     * Check if client is authenticated and cookies are still valid
     *
     * @return bool
     */
    public function is_authenticated() {
        if (empty($this->cookies)) {
            return false;
        }
        
        // Check if any cookie has expired
        $now = time();
        foreach ($this->cookies as $cookie) {
            if (isset($cookie['expires']) && !empty($cookie['expires'])) {
                // If expires is set and in the past, cookies are expired
                if ($cookie['expires'] < $now) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get current cookies
     *
     * @return array
     */
    public function get_cookies() {
        return $this->cookies;
    }
    
    /**
     * Prepare cookies for WP HTTP request
     *
     * @return array Array of WP_Http_Cookie objects
     */
    private function prepare_cookies_for_request() {
        $wp_cookies = [];
        
        foreach ($this->cookies as $cookie) {
            $wp_cookies[] = new WP_Http_Cookie([
                'name' => $cookie['name'],
                'value' => $cookie['value'],
                'expires' => $cookie['expires'] ?? null,
                'path' => $cookie['path'] ?? '/',
                'domain' => $cookie['domain'] ?? ''
            ]);
        }
        
        return $wp_cookies;
    }
    
    /**
     * Clear authentication
     */
    public function logout() {
        $this->cookies = [];
        delete_option('churchtools_suite_ct_cookies');
        delete_option('churchtools_suite_ct_person_id');
        delete_option('churchtools_suite_ct_user_info');
        delete_option('churchtools_suite_ct_last_login');
    }
}
