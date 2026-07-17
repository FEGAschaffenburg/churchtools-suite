<?php
/**
 * Image Helper für ChurchTools Suite
 *
 * Zentrale Logik für Bild-Fallbacks und Darstellung
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.35
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Image_Helper {
	
	/**
	 * Dummy Bild (Base64 SVG - Event-Icon)
	 */
	const DUMMY_IMAGE = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjZTVlN2ViIi8+CjxjaSXJjeiBjeD0iMjAwIiBjeT0iMTUwIiByPSI2MCIgZmlsbD0iI2NiZDVlMSIvPgo8cmVjdCB4PSIxNjAiIHk9IjEyMCIgd2lkdGg9IjgwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjY2JkNWUxIi8+Cjwvc3ZnPg==';
	
	/**
	 * Hole das beste verfügbare Bild mit Fallback-Logik
	 *
	 * Priorität:
	 * 1. Event-Bild (image_attachment_id oder image_url)
	 * 2. Kalender-Bild (calendar_image_id aus DB)
	 * 3. Dummy-Bild
	 *
	 * @param object|array $event Event-Objekt oder Array mit Bilddaten
	 * @param object|array $calendar Optional: Kalender-Objekt mit Bild
	 * @param bool $return_url Wenn true, nur URL; wenn false, HTML img-Tag
	 * @param array $img_attrs Optional: Zusätzliche HTML-Attribute
	 * @return string HTML oder URL
	 */
	public static function get_image( $event, $calendar = null, $return_url = false, $img_attrs = [] ) {
		// Normalisiere $event zu Array für einheitlichen Zugriff
		$event_data = is_array( $event ) ? $event : (array) $event;
		$event_title = $event_data['title'] ?? 'Termin';
		
		// 1. Event-Bild (highest priority)
		if ( ! empty( $event_data['image_attachment_id'] ) ) {
			$image_url = self::get_attachment_url( $event_data['image_attachment_id'] );
			if ( $image_url ) {
				return $return_url ? $image_url : self::render_image( $image_url, $event_title, $img_attrs );
			}
		}
		
		if ( ! empty( $event_data['image_url'] ) ) {
			return $return_url ? $event_data['image_url'] : self::render_image( $event_data['image_url'], $event_title, $img_attrs );
		}
		
		// 2. Kalender-Bild (calendar_image_id aus DB-Tabelle, v0.9.9.58)
		if ( ! empty( $calendar ) ) {
			$calendar_data = is_array( $calendar ) ? $calendar : (array) $calendar;

			if ( ! empty( $calendar_data['calendar_image_id'] ) ) {
				$cal_image_url = self::get_attachment_url( $calendar_data['calendar_image_id'] );
				if ( $cal_image_url ) {
					$cal_name = $calendar_data['name'] ?? 'Calendar';
					return $return_url ? $cal_image_url : self::render_image( $cal_image_url, $cal_name, $img_attrs );
				}
			}
		}
		
		// 3. Dummy SVG (fallback) - v0.9.9.58: Use static file instead of inline SVG
		$dummy_url = plugins_url( 'assets/images/fallback-event-image.jpg', dirname( __DIR__ ) . '/churchtools-suite.php' );
		return $return_url ? $dummy_url : self::render_image( $dummy_url, 'Termin', $img_attrs );
	}
	
	/**
	 * Hole URL eines Attachments
	 *
	 * @param int $attachment_id WordPress Attachment ID
	 * @return string|false
	 */
	private static function get_attachment_url( $attachment_id ) {
		if ( ! $attachment_id ) {
			return false;
		}
		
		$url = wp_get_attachment_url( $attachment_id );
		return ! empty( $url ) ? $url : false;
	}
	
	/**
	 * Rendere Image-Tag
	 *
	 * @param string $url Image URL
	 * @param string $alt Alt-Text
	 * @param array $attrs Zusätzliche HTML-Attribute
	 * @return string HTML img-Tag
	 */
	private static function render_image( $url, $alt = '', $attrs = [] ) {
		$defaults = [
			'src' => esc_url( $url ),
			'alt' => esc_attr( $alt ),
			'loading' => 'lazy',
		];
		
		$attributes = array_merge( $defaults, $attrs );
		
		$html_attrs = '';
		foreach ( $attributes as $key => $value ) {
			if ( ! empty( $value ) ) {
				$html_attrs .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}
		}
		
		return '<img' . $html_attrs . '>';
	}
	
	/**
	 * Hole Image URL mit Fallback (nur URL, kein Tag)
	 *
	 * @param object $event Event
	 * @param object|array $calendar Optional: Kalender
	 * @return string Image URL
	 */
	public static function get_image_url( $event, $calendar = null ) {
		return self::get_image( $event, $calendar, true );
	}
	
	/**
	 * Renderiere Image mit Fallback
	 *
	 * @param object $event Event
	 * @param object|array $calendar Optional: Kalender
	 * @param array $img_attrs HTML-Attribute
	 * @return string HTML img-Tag
	 */
	public static function render_image_with_fallback( $event, $calendar = null, $img_attrs = [] ) {
		return self::get_image( $event, $calendar, false, $img_attrs );
	}
	
	/**
	 * CSS für Image-Hintergrund
	 *
	 * @param object $event Event
	 * @param object|array $calendar Optional: Kalender
	 * @return string CSS background-image
	 */
	public static function get_background_css( $event, $calendar = null ) {
		$url = self::get_image_url( $event, $calendar );
		return sprintf( 'background-image: url("%s");', esc_url( $url ) );
	}
}
