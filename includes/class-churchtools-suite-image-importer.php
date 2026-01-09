<?php
/**
 * Image Importer for ChurchTools Events
 *
 * Importiert Bilder von der ChurchTools API in die WordPress Media Library
 *
 * @package ChurchTools_Suite
 * @since   0.10.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Image_Importer {
    
    /**
     * Import image from URL and store in WordPress media library
     *
     * @param string $image_url External image URL from ChurchTools API
     * @param string $title Image title/alt text
     * @param string $event_id Event ID for unique identification
     * @return int|WP_Error Attachment ID on success, WP_Error on failure
     */
    public static function import_image(string $image_url, string $title = '', string $event_id = ''): mixed {
        if (empty($image_url)) {
            return new WP_Error('empty_url', __('Bild-URL ist leer', 'churchtools-suite'));
        }
        
        // Validiere URL
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', __('Ungültige Bild-URL', 'churchtools-suite'));
        }
        
        // FIX: ChurchTools sendet Bilder mit Query-Parametern (?w=430&h=215)
        // Entferne sie um die Original-Auflösung zu bekommen
        $clean_url = strtok($image_url, '?');
        
        // Benutze WordPress-Funktion zum Herunterladen und Speichern
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        // Versuche, sprechenden Dateinamen aus JSON zu extrahieren
        $json_filename = '';
        if (!empty($title) && is_string($title) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $title)) {
            $json_filename = $title;
        }
        // Falls $image_url ein JSON-Objekt ist (z.B. als Array übergeben)
        if (is_array($image_url) && isset($image_url['name'])) {
            $json_filename = $image_url['name'];
        } elseif (is_array($image_url) && isset($image_url['filename'])) {
            $json_filename = $image_url['filename'];
        }

        // Extrahiere Original-Dateinamen aus der URL für bessere Deduplication
        $parsed_url = parse_url(is_string($image_url) ? $image_url : $clean_url, PHP_URL_PATH);
        $path_parts = explode('/', trim($parsed_url, '/'));
        $original_filename = end($path_parts);

        // Priorität: JSON-Name > URL-Name > Fallback
        if (!empty($json_filename)) {
            $filename = sanitize_file_name($json_filename);
        } elseif (!empty($original_filename) && strlen($original_filename) >= 5) {
            $filename = sanitize_file_name($original_filename);
        } else {
            $filename = !empty($event_id) ? 'ct-event-' . sanitize_file_name($event_id) : 'ct-event-' . time();
        }
        
        // Versuche, das Bild herunterzuladen
        $tmp_file = download_url($clean_url, 300); // 5 Minuten Timeout
        
        if (is_wp_error($tmp_file)) {
            return new WP_Error(
                'download_failed',
                sprintf(__('Fehler beim Herunterladen von %s: %s', 'churchtools-suite'), 
                    esc_url($image_url), 
                    $tmp_file->get_error_message())
            );
        }
        
        // Erhalte MIME-Type
        $file_type = wp_check_filetype_and_ext($tmp_file, $filename);
        
        // Debug logging - log actual MIME type received
        error_log(sprintf(
            '[CTS Image Import] URL: %s | File: %s | MIME Type: "%s" | Extension: "%s" | File Size: %d bytes',
            $image_url,
            basename($tmp_file),
            $file_type['type'] ?: 'EMPTY',
            $file_type['ext'] ?: 'NONE',
            filesize($tmp_file)
        ));
        
        // Fallback 1: Für vertrauenswürdige church.tools URLs - nutze Dateiendung aus URL
        if (empty($file_type['type']) && strpos($image_url, 'church.tools') !== false) {
            $parsed_url = parse_url($clean_url, PHP_URL_PATH);
            $path_info = pathinfo($parsed_url);
            $extension = strtolower($path_info['extension'] ?? '');
            
            $ext_to_mime = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];
            
            if (isset($ext_to_mime[$extension])) {
                $file_type['type'] = $ext_to_mime[$extension];
                $file_type['ext'] = $extension;
                error_log(sprintf('[CTS Image Import] FALLBACK 1 - church.tools MIME by extension: %s (%s)', 
                    $file_type['type'], $extension));
            }
        }
        
        // Fallback 2: Wenn immer noch leer - prüfe Magic Bytes
        if (empty($file_type['type'])) {
            $file_type['type'] = self::detect_image_by_magic_bytes($tmp_file);
            error_log('[CTS Image Import] FALLBACK 2 - Detected by magic bytes: ' . ($file_type['type'] ?: 'FAILED'));
        }
        
        if (!in_array($file_type['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            @unlink($tmp_file);
            $error_msg = sprintf('Nur Bilder (JPG, PNG, GIF, WebP) sind erlaubt. Erhalten: "%s"', $file_type['type'] ?: 'UNKNOWN');
            error_log('[CTS Image Import] REJECTED - ' . $error_msg);
            return new WP_Error('invalid_type', $error_msg);
        }
        
        // Verschiebe Datei in Upload-Verzeichnis
        $upload_dir = wp_upload_dir();
        $target_file = $upload_dir['path'] . '/' . $filename . '.' . pathinfo($tmp_file, PATHINFO_EXTENSION);
        
        // Vermeide Duplikate durch Versionierung
        $counter = 1;
        $base_target = $target_file;
        while (file_exists($target_file)) {
            $pathinfo = pathinfo($base_target);
            $target_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $counter . '.' . $pathinfo['extension'];
            $counter++;
        }
        
        if (!@rename($tmp_file, $target_file)) {
            @unlink($tmp_file);
            return new WP_Error('move_failed', __('Fehler beim Verschieben der Datei', 'churchtools-suite'));
        }
        
        // Erstelle WordPress Attachment Post
        $attachment = [
            'post_mime_type' => $file_type['type'],
            'post_title' => sanitize_text_field($title ?: $filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_name' => sanitize_title($filename),
        ];
        
        $attachment_id = wp_insert_attachment($attachment, $target_file);
        
        if (is_wp_error($attachment_id)) {
            @unlink($target_file);
            return $attachment_id;
        }
        
        // Generiere Thumbnail und Metadata
        $attach_data = wp_generate_attachment_metadata($attachment_id, $target_file);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        // Setze Custom Meta um zu zeigen, dass es importiert wurde
        update_post_meta($attachment_id, '_cts_imported_from', $image_url);
        update_post_meta($attachment_id, '_cts_event_id', $event_id);
        
        return $attachment_id;
    }
    
    /**
     * Get image URL from attachment ID
     *
     * @param int $attachment_id WordPress attachment ID
     * @return string|false Image URL or false if not found
     */
    public static function get_image_url(int $attachment_id) {
        return wp_get_attachment_url($attachment_id);
    }
    
    /**
     * Delete imported image
     *
     * @param int $attachment_id WordPress attachment ID
     * @return bool True on success
     */
    public static function delete_image(int $attachment_id): bool {
        if (empty($attachment_id)) {
            return false;
        }
        
        wp_delete_attachment($attachment_id, true);
        return true;
    }
    
    /**
     * Detect image MIME type by magic bytes (file signature)
     * 
     * Fallback wenn WordPress MIME-Erkennung fehlschlägt
     *
     * @param string $file_path Path to file
     * @return string|false MIME type or false
     */
    private static function detect_image_by_magic_bytes(string $file_path) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        
        $fh = fopen($file_path, 'rb');
        if (!$fh) {
            return false;
        }
        
        $bytes = fread($fh, 12); // Read first 12 bytes for magic detection
        fclose($fh);
        
        if (empty($bytes)) {
            return false;
        }
        
        // JPEG: FF D8 FF
        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            return 'image/jpeg';
        }
        
        // PNG: 89 50 4E 47
        if (substr($bytes, 0, 4) === "\x89PNG") {
            return 'image/png';
        }
        
        // GIF: GIF87a oder GIF89a
        if (substr($bytes, 0, 3) === "GIF") {
            return 'image/gif';
        }
        
        // WebP: RIFF ... WEBP
        if (substr($bytes, 0, 4) === "RIFF" && substr($bytes, 8, 4) === "WEBP") {
            return 'image/webp';
        }
        
        return false;
    }
    
    /**
     * Check if image already imported
     *
     * @param string $image_url External image URL
     * @return int|false Attachment ID if found, false otherwise
     */
    public static function find_existing_image(string $image_url) {
        if (empty($image_url)) {
            return false;
        }
        
        // Suche nach gespeicherten Import-URLs
        $args = [
            'meta_key' => '_cts_imported_from',
            'meta_value' => $image_url,
            'post_type' => 'attachment',
            'posts_per_page' => 1,
        ];
        
        $posts = get_posts($args);
        
        return !empty($posts) ? $posts[0]->ID : false;
    }
}
