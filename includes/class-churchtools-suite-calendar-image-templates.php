<?php
/**
 * Calendar Image Templates
 *
 * Bundled, selectable calendar images for common ChurchTools calendar types.
 *
 * @package ChurchTools_Suite
 * @since   1.2.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Calendar_Image_Templates {
	private const OPTION_KEY = 'churchtools_suite_calendar_template_images';

	/**
	 * Return all bundled templates, ensuring they are imported into the media library.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_templates(): array {
		$definitions = self::get_template_definitions();
		$stored_ids = get_option( self::OPTION_KEY, [] );
		$templates = [];

		require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-importer.php';

		foreach ( $definitions as $definition ) {
			$template_key = $definition['key'];
			$attachment_id = isset( $stored_ids[ $template_key ] ) ? absint( $stored_ids[ $template_key ] ) : 0;

			if ( $attachment_id <= 0 || ! get_post( $attachment_id ) ) {
				$attachment_id = self::ensure_template_attachment( $definition );
				if ( $attachment_id > 0 ) {
					$stored_ids[ $template_key ] = $attachment_id;
				}
			}

			$templates[] = [
				'key' => $template_key,
				'name' => $definition['name'],
				'description' => $definition['description'],
				'attachment_id' => $attachment_id,
				'image_url' => $attachment_id ? wp_get_attachment_url( $attachment_id ) : '',
				'preview_url' => CHURCHTOOLS_SUITE_URL . $definition['relative_path'],
				'copyright' => $definition['copyright'],
			];
		}

		update_option( self::OPTION_KEY, $stored_ids, false );

		return $templates;
	}

	/**
	 * Template definitions.
	 *
	 * @return array<int, array<string, string>>
	 */
	private static function get_template_definitions(): array {
		return [
			[
				'key' => 'gottesdienst',
				'name' => __( 'Gottesdienst', 'churchtools-suite' ),
				'description' => __( 'Feierliche Vorlage mit ruhiger Liturgie-Optik.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/gottesdienst.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
			[
				'key' => 'jugend',
				'name' => __( 'Jugend', 'churchtools-suite' ),
				'description' => __( 'Dynamische Vorlage für Jugendabende und Teens.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/jugend.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
			[
				'key' => 'kinder',
				'name' => __( 'Kinder', 'churchtools-suite' ),
				'description' => __( 'Freundlich, bunt und leicht verspielt.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/kinder.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
			[
				'key' => 'gebet',
				'name' => __( 'Gebet', 'churchtools-suite' ),
				'description' => __( 'Klar und ruhig für Gebetsabende.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/gebet.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
			[
				'key' => 'bibelkreis',
				'name' => __( 'Bibelkreis', 'churchtools-suite' ),
				'description' => __( 'Zurückhaltende Vorlage mit Buchsymbolik.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/bibelkreis.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
			[
				'key' => 'gemeinschaft',
				'name' => __( 'Gemeinschaft', 'churchtools-suite' ),
				'description' => __( 'Allgemeine Vorlage für Treffen und Gruppen.', 'churchtools-suite' ),
				'relative_path' => 'assets/images/calendar-templates/gemeinschaft.svg',
				'copyright' => 'Copyright 2026 FEG Aschaffenburg',
			],
		];
	}

	/**
	 * Import a template file into the media library.
	 *
	 * @param array<string, string> $definition Template definition
	 * @return int Attachment ID or 0 on failure
	 */
	private static function ensure_template_attachment( array $definition ): int {
		$source_path = CHURCHTOOLS_SUITE_PATH . $definition['relative_path'];
		if ( ! file_exists( $source_path ) ) {
			return 0;
		}

		$meta = [
			'_cts_template_key' => $definition['key'],
			'_cts_template_notice' => $definition['copyright'],
		];

		$attachment_id = ChurchTools_Suite_Image_Importer::import_local_file(
			$source_path,
			$definition['name'],
			'cts-template-' . $definition['key'],
			$meta
		);

		return is_wp_error( $attachment_id ) ? 0 : absint( $attachment_id );
	}
}