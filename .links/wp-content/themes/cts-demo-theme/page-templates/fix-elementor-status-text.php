<?php
/**
 * Fix Homepage Marketing Text
 * 
 * Corrects presentation mismatches between the homepage and the current
 * implementation state of ChurchTools Suite + Addons.
 * 
 * Fixes include:
 * - Elementor status: available instead of "in Vorbereitung"
 * - View count: concrete, current scope instead of inflated wording
 * - Demo view naming: canonical names aligned with implemented templates
 * 
 * Usage: Run via WP-CLI or add ?run=now to URL as admin
 * 
 * @package CTS_Demo_Theme
 * @since   1.0.0
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

// Restrict access: WP-CLI or admin with explicit confirmation parameter
if ( ! defined( 'WP_CLI' ) && ( ! isset( $_GET['run'] ) || $_GET['run'] !== 'now' || ! current_user_can( 'manage_options' ) ) ) {
	wp_die( 'Zugriff verweigert. Nur Administratoren mit ?run=now.' );
}

global $wpdb;

// Find the front page
$front_page_id = (int) get_option( 'page_on_front' );

if ( ! $front_page_id ) {
	die( "Error: Keine statische Startseite konfiguriert (Settings > Reading).\n" );
}

$page = get_post( $front_page_id );

if ( ! $page ) {
	die( "Error: Startseite (ID $front_page_id) nicht gefunden.\n" );
}

echo "Startseite: '{$page->post_title}' (ID: {$page->ID})\n\n";

// Text replacements to fix homepage marketing contradictions.
$replacements = [
	// Fix: Elementor "in Vorbereitung" → correctly show as available
	'Elementor-Widget (in Vorbereitung)'
		=> 'Elementor-Widget (verfügbar)',

	// Fix: More descriptive combo if both are mentioned together
	'Gutenberg-Block mit Live-Preview und Elementor-Widget (in Vorbereitung)'
		=> 'Gutenberg-Block mit Live-Preview und Elementor-Widget (verfügbar ab v0.5.3)',

	// Fix: Avoid inflated view count claim on homepage.
	'13+ Frontend Views'
		=> 'Frontend Views für List, Grid, Calendar & Countdown',

	// Fix: Replace marketing shorthand with implemented canonical names.
	'List (Modern, Classic, Compact), Grid (Cards, Masonry), Calendar (Monthly) – alle responsive und anpassbar..'
		=> 'List (Classic, Classic-Modern, Classic-with-Images, Minimal, Modern), Grid (Simple, Modern Grid), Calendar (Monthly Simple) und Countdown (Classic) – responsive und anpassbar.',
];

$content       = $page->post_content;
$changes_made  = 0;

foreach ( $replacements as $search => $replace ) {
	if ( strpos( $content, $search ) !== false ) {
		$content = str_replace( $search, $replace, $content );
		echo "✅ Ersetzt: \"$search\"\n   → \"$replace\"\n\n";
		$changes_made++;
	} else {
		echo "⚠️  Nicht gefunden (evtl. bereits korrigiert): \"$search\"\n\n";
	}
}

if ( $changes_made === 0 ) {
	echo "Keine Änderungen nötig – Text ist bereits korrekt.\n";
	exit( 0 );
}

// Update the page content
$result = $wpdb->update(
	$wpdb->posts,
	[
		'post_content'     => $content,
		'post_modified'    => current_time( 'mysql' ),
		'post_modified_gmt' => current_time( 'mysql', 1 ),
	],
	[ 'ID' => $page->ID ],
	[ '%s', '%s', '%s' ],
	[ '%d' ]
);

if ( $result === false ) {
	die( "Error: Datenbankaktualisierung fehlgeschlagen: " . $wpdb->last_error . "\n" );
}

// Clear page cache
clean_post_cache( $page->ID );

echo "✅ Startseite aktualisiert ($changes_made Ersetzung(en)).\n";
echo "   URL: " . get_permalink( $page->ID ) . "\n";
