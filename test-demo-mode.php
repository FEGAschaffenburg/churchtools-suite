<?php
/**
 * Demo Mode Test Script
 * 
 * Dieses Script testet den Demo-Modus lokal.
 * 
 * VERWENDUNG:
 * 1. In wp-config.php hinzufügen: define('CTS_DEMO_MODE', true);
 * 2. Dieses Script in WordPress root kopieren
 * 3. Im Browser aufrufen: http://localhost/wordpress/test-demo-mode.php
 * 4. Überprüfen ob Demo-Daten geladen werden
 * 
 * @package ChurchTools_Suite
 * @since   0.9.3.0
 */

// WordPress laden
require_once __DIR__ . '/wp-load.php';

// Check ob Plugin aktiv ist
if ( ! defined( 'CHURCHTOOLS_SUITE_VERSION' ) ) {
	die( '❌ ChurchTools Suite Plugin ist nicht aktiv!' );
}

// Demo Mode aktiv?
$demo_mode = defined( 'CTS_DEMO_MODE' ) && CTS_DEMO_MODE === true;

?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ChurchTools Suite - Demo Mode Test</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			max-width: 1200px;
			margin: 40px auto;
			padding: 20px;
			background: #f5f5f5;
		}
		.status {
			padding: 20px;
			border-radius: 8px;
			margin-bottom: 30px;
			font-size: 18px;
			font-weight: 600;
		}
		.success { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
		.error { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }
		.info { background: #d1ecf1; color: #0c5460; border: 2px solid #bee5eb; }
		
		.section {
			background: white;
			padding: 25px;
			border-radius: 8px;
			margin-bottom: 20px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		.section h2 {
			margin-top: 0;
			color: #333;
			border-bottom: 2px solid #667eea;
			padding-bottom: 10px;
		}
		
		.calendar {
			display: inline-block;
			padding: 8px 16px;
			margin: 5px;
			border-radius: 4px;
			color: white;
			font-weight: 500;
		}
		
		.event {
			padding: 15px;
			margin: 10px 0;
			border-left: 4px solid #667eea;
			background: #f8f9fa;
			border-radius: 4px;
		}
		.event-title {
			font-weight: 600;
			font-size: 16px;
			margin-bottom: 5px;
		}
		.event-meta {
			color: #666;
			font-size: 14px;
		}
		
		.code {
			background: #2d3748;
			color: #68d391;
			padding: 15px;
			border-radius: 6px;
			font-family: 'Courier New', monospace;
			margin: 15px 0;
			overflow-x: auto;
		}
		
		table {
			width: 100%;
			border-collapse: collapse;
		}
		table th, table td {
			text-align: left;
			padding: 12px;
			border-bottom: 1px solid #e0e0e0;
		}
		table th {
			background: #667eea;
			color: white;
			font-weight: 600;
		}
	</style>
</head>
<body>
	
	<h1>🧪 ChurchTools Suite - Demo Mode Test</h1>
	
	<!-- Status -->
	<?php if ( $demo_mode ) : ?>
		<div class="status success">
			✅ Demo-Modus ist AKTIV (CTS_DEMO_MODE = true)
		</div>
	<?php else : ?>
		<div class="status error">
			❌ Demo-Modus ist INAKTIV
			<p style="margin-top:15px; font-size:14px; font-weight:normal;">
				Fügen Sie in wp-config.php hinzu:<br>
				<code style="background:#fff; padding:4px 8px; border-radius:3px; color:#721c24;">define('CTS_DEMO_MODE', true);</code>
			</p>
		</div>
	<?php endif; ?>
	
	<!-- Plugin Info -->
	<div class="section">
		<h2>Plugin Information</h2>
		<table>
			<tr>
				<th>Parameter</th>
				<th>Wert</th>
			</tr>
			<tr>
				<td>Plugin Version</td>
				<td><?php echo esc_html( CHURCHTOOLS_SUITE_VERSION ); ?></td>
			</tr>
			<tr>
				<td>Demo-Modus</td>
				<td><?php echo $demo_mode ? '<strong style="color:#155724;">Aktiv ✓</strong>' : '<strong style="color:#721c24;">Inaktiv ✗</strong>'; ?></td>
			</tr>
			<tr>
				<td>WordPress Version</td>
				<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
			</tr>
			<tr>
				<td>PHP Version</td>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
		</table>
	</div>
	
	<?php
	// Load Demo Data Provider
	require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-demo-data-provider.php';
	$demo_provider = new ChurchTools_Suite_Demo_Data_Provider();
	
	// Test Kalender
	$calendars = $demo_provider->get_calendars();
	?>
	
	<!-- Kalender -->
	<div class="section">
		<h2>📅 Demo-Kalender (<?php echo count( $calendars ); ?>)</h2>
		<?php foreach ( $calendars as $calendar ) : ?>
			<span class="calendar" style="background-color: <?php echo esc_attr( $calendar['color'] ); ?>">
				<?php echo esc_html( $calendar['name'] ); ?>
			</span>
		<?php endforeach; ?>
	</div>
	
	<?php
	// Test Events (nächste 30 Tage, max 10)
	$events = $demo_provider->get_events( [
		'from' => date( 'Y-m-d H:i:s' ),
		'to' => date( 'Y-m-d H:i:s', strtotime( '+30 days' ) ),
		'limit' => 10,
	] );
	?>
	
	<!-- Events -->
	<div class="section">
		<h2>🎯 Demo-Events (nächste 30 Tage, max 10)</h2>
		<p><strong>Gefunden:</strong> <?php echo count( $events ); ?> Events</p>
		
		<?php if ( ! empty( $events ) ) : ?>
			<?php foreach ( array_slice( $events, 0, 5 ) as $event ) : ?>
				<div class="event">
					<div class="event-title"><?php echo esc_html( $event['title'] ); ?></div>
					<div class="event-meta">
						📅 <?php echo esc_html( $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] ); ?> 
						⏰ <?php echo esc_html( $event['start_time'] ); ?> - <?php echo esc_html( $event['end_time'] ); ?> 
						📍 <?php echo esc_html( $event['location_name'] ); ?>
						<?php if ( ! empty( $event['tags'] ) ) : ?>
							<br>🏷️ Tags: <?php 
								$tag_names = array_map( function( $tag ) { return $tag['name']; }, $event['tags'] );
								echo esc_html( implode( ', ', $tag_names ) ); 
							?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
			
			<?php if ( count( $events ) > 5 ) : ?>
				<p style="color:#666; font-style:italic; margin-top:15px;">
					... und <?php echo ( count( $events ) - 5 ); ?> weitere Events
				</p>
			<?php endif; ?>
		<?php else : ?>
			<p style="color:#999;">Keine Events gefunden</p>
		<?php endif; ?>
	</div>
	
	<!-- Shortcode Test -->
	<div class="section">
		<h2>⚡ Shortcode Test</h2>
		<p>Fügen Sie einen dieser Shortcodes in eine WordPress-Seite ein:</p>
		
		<div class="code">
			[cts_list view="medium" limit="10"]
		</div>
		
		<div class="code">
			[cts_calendar view="monthly-modern" limit="50"]
		</div>
		
		<div class="code">
			[cts_grid view="modern" limit="12" columns="3"]
		</div>
		
		<?php if ( $demo_mode ) : ?>
			<div class="info" style="margin-top:20px;">
				ℹ️ <strong>Demo-Modus aktiv:</strong> Shortcodes zeigen automatisch Demo-Daten an!
			</div>
		<?php else : ?>
			<div class="status error" style="margin-top:20px;">
				⚠️ <strong>Demo-Modus inaktiv:</strong> Shortcodes nutzen echte ChurchTools API
			</div>
		<?php endif; ?>
	</div>
	
	<!-- Next Steps -->
	<div class="section">
		<h2>🚀 Nächste Schritte</h2>
		<ol>
			<li>Demo-Modus in wp-config.php aktivieren (falls noch nicht geschehen)</li>
			<li>WordPress-Seite erstellen mit Shortcode <code>[cts_list view="medium" limit="10"]</code></li>
			<li>Seite im Frontend aufrufen und prüfen ob Demo-Events angezeigt werden</li>
			<li>Verschiedene Shortcodes testen (Calendar, Grid, List)</li>
			<li>Auf Demo-Website deployen (define in wp-config.php nicht vergessen!)</li>
		</ol>
	</div>
	
</body>
</html>
