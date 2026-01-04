<?php
/**
 * Force Update Check
 * 
 * Löscht Update-Cache und zeigt GitHub Release-Info
 * 
 * USAGE: Im Browser aufrufen: /wp-content/plugins/churchtools-suite/force-update-check.php
 */

// WordPress laden
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung' );
}

echo '<h1>ChurchTools Suite - Force Update Check</h1>';
echo '<hr>';

// 1. Transient löschen
$deleted = delete_transient( 'churchtools_suite_github_release' );
echo '<h2>1. Transient gelöscht:</h2>';
echo $deleted ? '✅ Ja' : '❌ Nein';
echo '<br><br>';

// 2. GitHub API direkt abfragen
echo '<h2>2. GitHub API abfragen:</h2>';
$response = wp_remote_get( 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest', [
	'headers' => [
		'User-Agent' => 'ChurchTools-Suite-Update-Checker',
		'Accept' => 'application/vnd.github.v3+json',
	],
] );

if ( is_wp_error( $response ) ) {
	echo '❌ Fehler: ' . esc_html( $response->get_error_message() );
} else {
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	
	echo '<strong>Latest Release:</strong> ' . esc_html( $body['tag_name'] ?? 'N/A' ) . '<br>';
	echo '<strong>Published:</strong> ' . esc_html( $body['published_at'] ?? 'N/A' ) . '<br>';
	echo '<strong>Assets:</strong> ' . count( $body['assets'] ?? [] ) . '<br>';
	
	if ( ! empty( $body['assets'] ) ) {
		foreach ( $body['assets'] as $asset ) {
			echo '  - ' . esc_html( $asset['name'] ) . ' (' . esc_html( $asset['browser_download_url'] ) . ')<br>';
		}
	}
}

echo '<br>';

// 3. Aktuelle Plugin-Version
echo '<h2>3. Installierte Version:</h2>';
echo '<strong>CHURCHTOOLS_SUITE_VERSION:</strong> ' . esc_html( CHURCHTOOLS_SUITE_VERSION ) . '<br><br>';

// 4. WordPress Update-Transient prüfen
echo '<h2>4. WordPress Update-Transient:</h2>';
$update_plugins = get_site_transient( 'update_plugins' );

if ( $update_plugins && isset( $update_plugins->response ) ) {
	$found = false;
	foreach ( $update_plugins->response as $plugin => $data ) {
		if ( strpos( $plugin, 'churchtools-suite' ) !== false ) {
			echo '<strong>Plugin:</strong> ' . esc_html( $plugin ) . '<br>';
			echo '<strong>Neue Version:</strong> ' . esc_html( $data->new_version ?? 'N/A' ) . '<br>';
			echo '<strong>Package URL:</strong> ' . esc_html( $data->package ?? 'N/A' ) . '<br>';
			$found = true;
		}
	}
	
	if ( ! $found ) {
		echo '❌ Kein Update im WordPress-Transient gefunden<br>';
	}
} else {
	echo '❌ Update-Transient nicht verfügbar<br>';
}

echo '<br>';

// 5. Update-Transient löschen und neu laden
echo '<h2>5. WordPress Update-Transient neu laden:</h2>';
delete_site_transient( 'update_plugins' );
wp_update_plugins();
echo '✅ Update-Transient gelöscht und neu geladen<br>';

echo '<br><hr>';
echo '<a href="' . admin_url( 'plugins.php' ) . '">→ Zu den Plugins</a>';
