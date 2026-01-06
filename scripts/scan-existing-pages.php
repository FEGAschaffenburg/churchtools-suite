<?php
/**
 * Scan Existing Pages for ChurchTools Suite Content
 * 
 * Findet alle Seiten mit Gutenberg Blocks oder Shortcodes
 * und generiert einen Test-Report.
 * 
 * USAGE: wp-cli run this script or include in WordPress admin
 */

// WordPress laden (wenn direkt aufgerufen)
if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung' );
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>ChurchTools Suite - Page Scanner</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			background: #f5f5f5;
			padding: 20px;
			color: #1e293b;
		}
		.container {
			max-width: 1200px;
			margin: 0 auto;
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		h1 {
			color: #2563eb;
			border-bottom: 3px solid #2563eb;
			padding-bottom: 10px;
		}
		h2 {
			color: #4b5563;
			margin-top: 30px;
		}
		.summary {
			background: #f0f9ff;
			border-left: 4px solid #2563eb;
			padding: 15px;
			margin: 20px 0;
		}
		.summary strong {
			color: #2563eb;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}
		th {
			background: #e5e7eb;
			padding: 12px;
			text-align: left;
			font-weight: 600;
			color: #374151;
		}
		td {
			padding: 12px;
			border-bottom: 1px solid #e5e7eb;
		}
		tr:hover {
			background: #f9fafb;
		}
		.block-badge {
			display: inline-block;
			padding: 4px 8px;
			background: #dbeafe;
			color: #1e40af;
			border-radius: 4px;
			font-size: 12px;
			font-weight: 600;
			margin-right: 5px;
		}
		.shortcode-badge {
			display: inline-block;
			padding: 4px 8px;
			background: #fef3c7;
			color: #92400e;
			border-radius: 4px;
			font-size: 12px;
			font-weight: 600;
			margin-right: 5px;
		}
		.view-type {
			font-family: monospace;
			background: #f1f5f9;
			padding: 2px 6px;
			border-radius: 3px;
			color: #0f172a;
		}
		.link-btn {
			display: inline-block;
			padding: 6px 12px;
			background: #2563eb;
			color: white;
			text-decoration: none;
			border-radius: 4px;
			font-size: 13px;
			margin-right: 5px;
		}
		.link-btn:hover {
			background: #1d4ed8;
		}
		.warning {
			background: #fef3c7;
			border-left: 4px solid #f59e0b;
			padding: 15px;
			margin: 20px 0;
		}
		code {
			background: #1e293b;
			color: #e2e8f0;
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 13px;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>📊 ChurchTools Suite - Page Scanner</h1>
		<p><strong>Version:</strong> <?php echo CHURCHTOOLS_SUITE_VERSION; ?> | 
		   <strong>Datum:</strong> <?php echo date( 'd.m.Y H:i:s' ); ?></p>

		<?php
		// === 1. GUTENBERG BLOCKS ===
		$blocks_query = new WP_Query( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			's'              => 'churchtools-suite/events', // Block namespace
		] );

		$pages_with_blocks = [];
		if ( $blocks_query->have_posts() ) {
			while ( $blocks_query->have_posts() ) {
				$blocks_query->the_post();
				$content = get_the_content();
				
				// Parse blocks aus Content
				if ( has_blocks( $content ) ) {
					$blocks = parse_blocks( $content );
					foreach ( $blocks as $block ) {
						if ( $block['blockName'] === 'churchtools-suite/events' ) {
							$pages_with_blocks[] = [
								'id'         => get_the_ID(),
								'title'      => get_the_title(),
								'url'        => get_permalink(),
								'edit_url'   => get_edit_post_link(),
								'view_type'  => $block['attrs']['viewType'] ?? 'unknown',
								'view'       => $block['attrs']['view'] ?? 'unknown',
								'attributes' => $block['attrs'],
							];
						}
					}
				}
			}
			wp_reset_postdata();
		}

		// === 2. SHORTCODES ===
		global $wpdb;
		$shortcode_patterns = [ 'cts_list', 'cts_calendar', 'cts_grid', 'cts_search', 'cts_widget' ];
		$pages_with_shortcodes = [];

		foreach ( $shortcode_patterns as $shortcode ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title, post_content 
					FROM {$wpdb->posts} 
					WHERE post_type = 'page' 
					AND post_status = 'publish'
					AND post_content LIKE %s",
					'%[' . $shortcode . '%'
				)
			);

			foreach ( $results as $page ) {
				// Extract shortcode attributes
				preg_match_all( '/\[' . $shortcode . '([^\]]*)\]/', $page->post_content, $matches );
				
				if ( ! empty( $matches[1] ) ) {
					foreach ( $matches[1] as $attrs_string ) {
						$pages_with_shortcodes[] = [
							'id'        => $page->ID,
							'title'     => $page->post_title,
							'url'       => get_permalink( $page->ID ),
							'edit_url'  => get_edit_post_link( $page->ID ),
							'shortcode' => $shortcode,
							'full'      => '[' . $shortcode . $attrs_string . ']',
						];
					}
				}
			}
		}

		?>

		<!-- SUMMARY -->
		<div class="summary">
			<strong>📈 Zusammenfassung:</strong><br>
			Gefundene Seiten mit <strong><?php echo count( $pages_with_blocks ); ?> Gutenberg Blocks</strong> 
			und <strong><?php echo count( $pages_with_shortcodes ); ?> Shortcodes</strong>.
		</div>

		<?php if ( empty( $pages_with_blocks ) && empty( $pages_with_shortcodes ) ) : ?>
			<div class="warning">
				⚠️ <strong>Keine Seiten gefunden!</strong><br>
				Es wurden keine Seiten mit ChurchTools Suite Blocks oder Shortcodes gefunden.
			</div>
		<?php endif; ?>

		<!-- GUTENBERG BLOCKS -->
		<?php if ( ! empty( $pages_with_blocks ) ) : ?>
			<h2>🔷 Gutenberg Blocks (<?php echo count( $pages_with_blocks ); ?>)</h2>
			<table>
				<thead>
					<tr>
						<th style="width: 5%;">ID</th>
						<th style="width: 30%;">Seite</th>
						<th style="width: 20%;">View Type + Variante</th>
						<th style="width: 20%;">Toggles</th>
						<th style="width: 25%;">Aktionen</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pages_with_blocks as $page ) : ?>
						<tr>
							<td><?php echo esc_html( $page['id'] ); ?></td>
							<td><strong><?php echo esc_html( $page['title'] ); ?></strong></td>
							<td>
								<span class="view-type"><?php echo esc_html( $page['view_type'] ); ?></span> → 
								<span class="view-type"><?php echo esc_html( $page['view'] ); ?></span>
							</td>
							<td style="font-size: 11px;">
								<?php
								$toggles = [
									'enable_modal'                   => 'Modal',
									'show_event_description'         => 'Event-Desc',
									'show_appointment_description'   => 'Apt-Desc',
									'show_location'                  => 'Ort',
									'show_services'                  => 'Services',
									'show_calendar_name'             => 'Kalender',
									'show_time'                      => 'Zeit',
									'show_tags'                      => 'Tags',
								];
								foreach ( $toggles as $key => $label ) {
									$value = $page['attributes'][ $key ] ?? null;
									$color = $value === true || $value === 'true' ? '#10b981' : '#ef4444';
									$symbol = $value === true || $value === 'true' ? '✓' : '✗';
									echo '<span style="color:' . $color . '">' . $symbol . ' ' . $label . '</span><br>';
								}
								?>
							</td>
							<td>
								<a href="<?php echo esc_url( $page['edit_url'] ); ?>" class="link-btn" target="_blank">✏️ Bearbeiten</a>
								<a href="<?php echo esc_url( $page['url'] ); ?>" class="link-btn" target="_blank">👁️ Ansehen</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<!-- SHORTCODES -->
		<?php if ( ! empty( $pages_with_shortcodes ) ) : ?>
			<h2>📝 Shortcodes (<?php echo count( $pages_with_shortcodes ); ?>)</h2>
			<table>
				<thead>
					<tr>
						<th style="width: 5%;">ID</th>
						<th style="width: 25%;">Seite</th>
						<th style="width: 15%;">Shortcode</th>
						<th style="width: 30%;">Vollständig</th>
						<th style="width: 25%;">Aktionen</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pages_with_shortcodes as $page ) : ?>
						<tr>
							<td><?php echo esc_html( $page['id'] ); ?></td>
							<td><strong><?php echo esc_html( $page['title'] ); ?></strong></td>
							<td><span class="shortcode-badge"><?php echo esc_html( $page['shortcode'] ); ?></span></td>
							<td><code><?php echo esc_html( $page['full'] ); ?></code></td>
							<td>
								<a href="<?php echo esc_url( $page['edit_url'] ); ?>" class="link-btn" target="_blank">✏️ Bearbeiten</a>
								<a href="<?php echo esc_url( $page['url'] ); ?>" class="link-btn" target="_blank">👁️ Ansehen</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<!-- TEST RECOMMENDATIONS -->
		<h2>✅ Empfohlene Tests</h2>
		<ol>
			<li><strong>Alle gefundenen Seiten im Frontend aufrufen</strong> → Darstellung korrekt?</li>
			<li><strong>Alle gefundenen Seiten im Editor öffnen</strong> → Block/Shortcode lädt korrekt?</li>
			<li><strong>Browser Console prüfen</strong> → Keine JavaScript-Fehler?</li>
			<li><strong>Toggle-Steuerelemente testen (nur Gutenberg Blocks)</strong> → Panel "👁️ Anzeige-Optionen" sichtbar auch bei Calendar-Views?</li>
			<li><strong>Seite neu speichern</strong> → Keine Änderungen am Frontend?</li>
		</ol>

		<div class="warning">
			⚠️ <strong>Wichtig:</strong> Nach v0.10.4.43 sind Toggle-Steuerelemente in Gutenberg jetzt auch für Calendar-Views sichtbar. 
			Bestehende Blocks sollten NICHT betroffen sein (Default-Werte bleiben gleich).
		</div>

	</div>
</body>
</html>
