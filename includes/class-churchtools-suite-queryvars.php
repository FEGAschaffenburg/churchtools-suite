<?php
/**
 * Query variable Class
 * 
 * Simple wrapper around the wordpress query variables
 *
 * @package ChurchTools_Suite
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Queryvars {

    public static function filter($vars) {
        # Event Search Shortcode
        $vars[] = "event-search";

        return $vars;
    }

    public static function get($key, $default = null) {
        return get_query_var( $key, $default);
    }

    public static function getEventSearchQuery(): string {
        $query_variable = self::get("event-search", "");

        return sanitize_text_field($query_variable);
    }
        
}
