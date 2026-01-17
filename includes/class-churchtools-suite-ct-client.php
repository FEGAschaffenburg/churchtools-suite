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
     * API token (auth method: token)
     */
    private $token;
    
    /**
     * Selected auth method (password|token)
     */
    private $auth_method;
    
    /**
     * Session cookies (auth method: password)
     */
    private $cookies;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->url = get_option('churchtools_suite_ct_url', '');
        $this->username = get_option('churchtools_suite_ct_username', '');
        $this->password = get_option('churchtools_suite_ct_password', '');
        $this->token = get_option('churchtools_suite_ct_token', '');
        $this->auth_method = get_option('churchtools_suite_ct_auth_method', 'password');
        $this->cookies = get_option('churchtools_suite_ct_cookies', []);
    }
    
    /**
     * Authenticate against ChurchTools (password: create session, token: quick validation)
     *
     * @return array Success status and message
     */
    public function login() {
        // Token mode: no login request needed
        if ($this->auth_method === 'token') {
            if (empty($this->url) || empty($this->token)) {
                return [
                    'success' => false,
                    'message' => 'ChurchTools URL und API-Token sind erforderlich.'
                ];
            }

            update_option('churchtools_suite_ct_last_login', current_time('mysql'));

            return [
                'success' => true,
                'message' => 'API-Token wird verwendet – keine Anmeldung nötig.'
            ];
        }

        // Validate required fields for username/password
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
        // Ensure auth is available
        $login_result = $this->login();
        if (!$login_result['success']) {
            return $login_result;
        }
        
        // Test API access by fetching whoami
        $whoami_url = trailingslashit($this->url) . 'api/whoami';

        $args = [
            'timeout' => 30,
        ];

        if ($this->auth_method === 'token') {
            $args['headers'] = [
                'Authorization' => 'Bearer ' . $this->token,
            ];
        } else {
            $args['cookies'] = $this->prepare_cookies_for_request();
        }
        
        $response = wp_remote_get($whoami_url, $args);
        
        if (is_wp_error($response)) {
            $error_msg = $response->get_error_message();
            return [
                'success' => false,
                'message' => 'Verbindung zum Server fehlgeschlagen.',
                'error_code' => 'connection_error',
                'error_details' => $error_msg
            ];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 401 || $status_code === 403) {
            return [
                'success' => false,
                'message' => 'Authentifizierung fehlgeschlagen. Überprüfen Sie API-Key/Passwort.',
                'error_code' => 'auth_failed',
                'error_details' => "HTTP $status_code - Ungültige Anmeldedaten"
            ];
        }
        
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return [
                'success' => false,
                'message' => 'ChurchTools API antwortet nicht korrekt.',
                'error_code' => "http_$status_code",
                'error_details' => "HTTP $status_code - $body"
            ];
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'API-Antwort ist kein gültiges JSON.',
                'error_code' => 'json_error',
                'error_details' => json_last_error_msg()
            ];
        }
        
        // Save user info
        if (!empty($data['data'])) {
            update_option('churchtools_suite_ct_user_info', $data['data']);
        }
        
        return [
            'success' => true,
            'message' => 'Verbindung erfolgreich! API-Zugriff funktioniert.',
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
        // Load dependencies
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-rate-limiter.php';
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
        
        // Rate Limiting (v0.7.0.2)
        $user_id = get_current_user_id();
        $identifier = $user_id > 0 ? 'user_' . $user_id : 'guest_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if ( ! ChurchTools_Suite_Rate_Limiter::is_allowed( $identifier, 'api' ) ) {
            ChurchTools_Suite_Logger::log(
                'API request blocked by rate limiter',
                ChurchTools_Suite_Logger::WARNING,
                [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'identifier' => $identifier,
                ]
            );
            
            return new WP_Error(
                'rate_limit_exceeded',
                __( 'Zu viele API-Anfragen. Bitte warten Sie einen Moment.', 'churchtools-suite' )
            );
        }
        
        $using_token = ($this->auth_method === 'token');

        if ($using_token && (empty($this->token) || empty($this->url))) {
            return new WP_Error('missing_token', __('API-Token oder ChurchTools-URL fehlen.', 'churchtools-suite'));
        }

        // Ensure auth is available
        if (!$this->is_authenticated()) {
            ChurchTools_Suite_Logger::log(
                'API: Not authenticated, attempting login',
                ChurchTools_Suite_Logger::INFO,
                ['endpoint' => $endpoint]
            );
            
            $login_result = $this->login();
            if (!$login_result['success']) {
                ChurchTools_Suite_Logger::log(
                    'API: Login failed',
                    ChurchTools_Suite_Logger::ERROR,
                    [
                        'endpoint' => $endpoint,
                        'error' => $login_result['message'],
                    ]
                );
                return new WP_Error('no_auth', $login_result['message']);
            }
        }
        
        // Build URL
        $url = trailingslashit($this->url) . 'api/' . ltrim($endpoint, '/');
        
        // Add query parameters for GET requests
        if ($method === 'GET' && !empty($data)) {
            // v0.10.4.7: Build query string manually to support array parameters like include[]=tags
            $query_parts = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // Array parameters: include[] => ['tags', 'event'] becomes include[]=tags&include[]=event
                    foreach ($value as $item) {
                        $query_parts[] = urlencode($key) . '[]=' . urlencode($item);
                    }
                } else {
                    // Simple parameters: from => '2024-01-01' becomes from=2024-01-01
                    $query_parts[] = urlencode($key) . '=' . urlencode($value);
                }
            }
            if (!empty($query_parts)) {
                $url .= '?' . implode('&', $query_parts);
            }
        }
        
        // Log API request
        ChurchTools_Suite_Logger::log(
            sprintf('API Request: %s %s', strtoupper($method), $endpoint),
            ChurchTools_Suite_Logger::DEBUG,
            [
                'url' => $url,
                'method' => $method,
                'data_keys' => !empty($data) ? array_keys($data) : [],
                'final_url' => $url, // v0.10.4.9: Log final URL with parameters
            ]
        );
        
        // Prepare request arguments
        $args = [
            'method' => strtoupper($method),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ];

        if ($using_token) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->token;
        } else {
            $args['cookies'] = $this->prepare_cookies_for_request();
        }
        
        // Add body for POST/PUT requests
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        // Send request
        $response = wp_remote_request($url, $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            ChurchTools_Suite_Logger::log(
                'API Request failed',
                ChurchTools_Suite_Logger::ERROR,
                [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'error' => $response->get_error_message(),
                ]
            );
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Check if response is JSON (v0.7.2.5: Better error handling)
        if (empty($body) || !is_string($body)) {
            return new WP_Error(
                'empty_response',
                __('API lieferte keine Antwort', 'churchtools-suite')
            );
        }
        
        // Check if body starts with HTML instead of JSON
        if (preg_match('/^\s*</', $body)) {
            // Log the HTML response for debugging
            ChurchTools_Suite_Logger::log(
                'API returned HTML instead of JSON',
                ChurchTools_Suite_Logger::ERROR,
                [
                    'endpoint' => $endpoint,
                    'status_code' => $status_code,
                    'body_preview' => substr($body, 0, 200)
                ]
            );
            
            return new WP_Error(
                'invalid_json',
                sprintf(
                    __('ChurchTools API lieferte HTML statt JSON (HTTP %d). Mögliche Ursachen: Session abgelaufen, Server-Fehler, falsche URL.', 'churchtools-suite'),
                    $status_code
                )
            );
        }
        
        $decoded = json_decode($body, true);
        
        // Handle 401 - try to re-login once (password mode only)
        if ($status_code === 401 && !$using_token) {
            $login_result = $this->login();
            if ($login_result['success']) {
                $args['cookies'] = $this->prepare_cookies_for_request();
                $response = wp_remote_request($url, $args);
                
                if (is_wp_error($response)) {
                    return $response;
                }
                
                $status_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                
                if (preg_match('/^\s*</', $body)) {
                    return new WP_Error(
                        'invalid_json_after_retry',
                        __('ChurchTools API lieferte HTML statt JSON (auch nach erneuter Anmeldung)', 'churchtools-suite')
                    );
                }
                
                $decoded = json_decode($body, true);
            }
        }
        
        // Check status code
        if ($status_code < 200 || $status_code >= 300) {
            $error_message = 'API-Fehler (HTTP ' . $status_code . ')';
            if (isset($decoded['message'])) {
                $error_message .= ': ' . $decoded['message'];
            }
            
            // Log error
            ChurchTools_Suite_Logger::log(
                sprintf('API Error: %s %s => HTTP %d', strtoupper($method), $endpoint, $status_code),
                ChurchTools_Suite_Logger::ERROR,
                [
                    'url' => $url,
                    'status_code' => $status_code,
                    'error_message' => $decoded['message'] ?? 'No error message',
                    'response_body' => substr($body, 0, 500), // First 500 chars
                ]
            );
            
            return new WP_Error('api_error', $error_message, ['status' => $status_code, 'url' => $url]);
        }
        
        // Check if json_decode was successful (v0.7.2.5)
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            ChurchTools_Suite_Logger::log(
                'JSON decode failed',
                ChurchTools_Suite_Logger::ERROR,
                [
                    'endpoint' => $endpoint,
                    'json_error' => json_last_error_msg(),
                    'body_preview' => substr($body, 0, 200)
                ]
            );
            
            return new WP_Error(
                'json_decode_failed',
                sprintf(
                    __('JSON-Parsing fehlgeschlagen: %s', 'churchtools-suite'),
                    json_last_error_msg()
                )
            );
        }
        
        // Log success
        ChurchTools_Suite_Logger::log(
            sprintf('API Success: %s %s => HTTP %d', strtoupper($method), $endpoint, $status_code),
            ChurchTools_Suite_Logger::DEBUG
        );
        
        return $decoded;
    }
    
    /**
     * Check if client is authenticated and cookies are still valid
     *
     * @return bool
     */
    public function is_authenticated() {
        if ($this->auth_method === 'token') {
            return !empty($this->token) && !empty($this->url);
        }

        if (empty($this->cookies)) {
            return false;
        }
        
        $now = time();
        foreach ($this->cookies as $cookie) {
            if (isset($cookie['expires']) && !empty($cookie['expires'])) {
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

    /**
     * Keepalive / Session ping
     *
     * Ensures the client is authenticated and performs a lightweight API call
     * to keep the session alive. Returns WP_Error on failure or an array on success.
     *
     * @return array|WP_Error
     */
    public function keepalive() {
        // Ensure we have credentials
        if (empty($this->url)) {
            return new WP_Error('missing_credentials', 'ChurchTools connection not configured');
        }

        if ($this->auth_method === 'password' && (empty($this->username) || empty($this->password))) {
            return new WP_Error('missing_credentials', 'ChurchTools connection not configured');
        }

        if (!$this->is_authenticated()) {
            $login = $this->login();
            if (!isset($login['success']) || $login['success'] !== true) {
                return new WP_Error('login_failed', $login['message'] ?? 'Login failed');
            }
        }

        // Call whoami to keep session alive / validate token
        $result = $this->api_request('whoami', 'GET');
        if (is_wp_error($result)) {
            return $result;
        }

        // Update last keepalive timestamp
        update_option('churchtools_suite_last_keepalive', current_time('mysql'));

        return [ 'success' => true, 'message' => 'Keepalive OK', 'data' => $result ];
    }
}
