<?php
/**
 * Secret storage helper for encrypted option values.
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ChurchTools_Suite_Secret_Storage' ) ) {
    class ChurchTools_Suite_Secret_Storage {
        private const PREFIX = 'ctsenc:v1:';

        /**
         * Encrypt a plaintext value for database storage.
         */
        public static function encrypt( string $plaintext ): string {
            $plaintext = trim( $plaintext );
            if ( $plaintext === '' ) {
                return '';
            }

            if ( self::is_encrypted( $plaintext ) ) {
                return $plaintext;
            }

            if ( ! function_exists( 'openssl_encrypt' ) || ! function_exists( 'openssl_cipher_iv_length' ) ) {
                return $plaintext;
            }

            $cipher = 'aes-256-cbc';
            $iv_len = openssl_cipher_iv_length( $cipher );
            if ( ! is_int( $iv_len ) || $iv_len <= 0 ) {
                return $plaintext;
            }

            try {
                $iv = random_bytes( $iv_len );
            } catch ( Exception $e ) {
                $iv = openssl_random_pseudo_bytes( $iv_len );
            }

            if ( ! is_string( $iv ) || strlen( $iv ) !== $iv_len ) {
                return $plaintext;
            }

            $encrypted = openssl_encrypt( $plaintext, $cipher, self::get_key(), OPENSSL_RAW_DATA, $iv );
            if ( ! is_string( $encrypted ) || $encrypted === '' ) {
                return $plaintext;
            }

            return self::PREFIX . base64_encode( $iv . $encrypted );
        }

        /**
         * Decrypt a value from option storage.
         */
        public static function decrypt( string $stored ): string {
            $stored = trim( $stored );
            if ( $stored === '' ) {
                return '';
            }

            if ( ! self::is_encrypted( $stored ) ) {
                // Backward compatibility: previously stored in plaintext.
                return $stored;
            }

            if ( ! function_exists( 'openssl_decrypt' ) || ! function_exists( 'openssl_cipher_iv_length' ) ) {
                return '';
            }

            $cipher = 'aes-256-cbc';
            $iv_len = openssl_cipher_iv_length( $cipher );
            if ( ! is_int( $iv_len ) || $iv_len <= 0 ) {
                return '';
            }

            $payload = substr( $stored, strlen( self::PREFIX ) );
            $raw = base64_decode( $payload, true );
            if ( ! is_string( $raw ) || strlen( $raw ) <= $iv_len ) {
                return '';
            }

            $iv = substr( $raw, 0, $iv_len );
            $encrypted = substr( $raw, $iv_len );

            $plaintext = openssl_decrypt( $encrypted, $cipher, self::get_key(), OPENSSL_RAW_DATA, $iv );
            return is_string( $plaintext ) ? $plaintext : '';
        }

        /**
         * Check if a value is in encrypted storage format.
         */
        public static function is_encrypted( string $value ): bool {
            return strpos( $value, self::PREFIX ) === 0;
        }

        /**
         * Build a stable key from WP salts.
         */
        private static function get_key(): string {
            $material = AUTH_KEY . '|' . SECURE_AUTH_KEY . '|' . NONCE_SALT . '|' . wp_salt( 'auth' );
            return hash( 'sha256', $material, true );
        }
    }
}
