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
     * Authentication token
     */
    private $token;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->url = get_option('churchtools_suite_ct_url', '');
        $this->username = get_option('churchtools_suite_ct_username', '');
        $this->password = get_option('churchtools_suite_ct_password', '');
        $this->token = get_option('churchtools_suite_ct_token', '');
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
            if (isset($data['message'])) {
                $error_message .= ': ' . $data['message'];
            }
            return [
                'success' => false,
                'message' => $error_message
            ];
        }
        
        // Extract token from response
        if (empty($data['data']['token'])) {
            return [
                'success' => false,
                'message' => 'Kein Token in der Antwort erhalten.'
            ];
        }
        
        $this->token = $data['data']['token'];
        
        // Save token to database
        update_option('churchtools_suite_ct_token', $this->token);
        
        // Save user info if available
        if (!empty($data['data']['personId'])) {
            update_option('churchtools_suite_ct_person_id', $data['data']['personId']);
        }
        
        // Update last login time
        update_option('churchtools_suite_ct_last_login', current_time('mysql'));
        
        return [
            'success' => true,
            'message' => 'Erfolgreich mit ChurchTools verbunden.',
            'token' => $this->token
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
        
        $response = wp_remote_get($whoami_url, [
            'headers' => [
                'Authorization' => 'Login ' . $this->token
            ],
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
        // Check if we have a token
        if (empty($this->token)) {
            $login_result = $this->login();
            if (!$login_result['success']) {
                return new WP_Error('no_token', $login_result['message']);
            }
        }
        
        // Build URL
        $url = trailingslashit($this->url) . 'api/' . ltrim($endpoint, '/');
        
        // Prepare request arguments
        $args = [
            'method' => strtoupper($method),
            'headers' => [
                'Authorization' => 'Login ' . $this->token,
                'Content-Type' => 'application/json'
            ],
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
                // Retry request with new token
                $args['headers']['Authorization'] = 'Login ' . $this->token;
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
     * Check if client is authenticated
     *
     * @return bool
     */
    public function is_authenticated() {
        return !empty($this->token);
    }
    
    /**
     * Get current token
     *
     * @return string
     */
    public function get_token() {
        return $this->token;
    }
    
    /**
     * Clear authentication
     */
    public function logout() {
        $this->token = '';
        delete_option('churchtools_suite_ct_token');
        delete_option('churchtools_suite_ct_person_id');
        delete_option('churchtools_suite_ct_user_info');
        delete_option('churchtools_suite_ct_last_login');
    }
}
