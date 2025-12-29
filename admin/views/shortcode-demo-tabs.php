<?php
/**
 * Shortcode Demo Page
 *
 * @package ChurchTools_Suite
 * @since   0.5.9.21
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Read demo HTML content
$demo_file = CHURCHTOOLS_SUITE_PATH . 'shortcode-demo.html';
$demo_content = file_exists( $demo_file ) ? file_get_contents( $demo_file ) : '';

// Read reference markdown
$reference_file = CHURCHTOOLS_SUITE_PATH . 'SHORTCODE-REFERENCE.md';
$reference_content = file_exists( $reference_file ) ? file_get_contents( $reference_file ) : '';

// Parse markdown headers from reference
$shortcuts = [];
if ( $reference_content ) {
	preg_match_all( '/###\s+\d+\.\s+(.+?)\s+\((\d+)\s+Varianten?\)/m', $reference_content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $match ) {
		$shortcuts[] = [
			'name' => $match[1],
			'count' => $match[2]
		];
	}
}
?>

<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>🎯</span>
			<?php esc_html_e( 'Shortcode Demo & Test', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'Vollständige Übersicht aller verfügbaren Shortcodes zum Testen und Optimieren', 'churchtools-suite' ); ?></p>
	</div>

	<div class="cts-tab-content">
		
		<!-- Quick Stats -->
		<div class="cts-grid cts-grid-3" style="margin-bottom: 30px;">
			<div class="cts-card">
				<div class="cts-card-body" style="text-align: center;">
					<div class="cts-stat-number">13</div>
					<p class="cts-card-detail"><?php esc_html_e( 'Shortcode-Typen', 'churchtools-suite' ); ?></p>
				</div>
			</div>
			<div class="cts-card">
				<div class="cts-card-body" style="text-align: center;">
					<div class="cts-stat-number">70+</div>
					<p class="cts-card-detail"><?php esc_html_e( 'View-Varianten', 'churchtools-suite' ); ?></p>
				</div>
			</div>
			<div class="cts-card">
				<div class="cts-card-body" style="text-align: center;">
					<div class="cts-stat-number">50+</div>
					<p class="cts-card-detail"><?php esc_html_e( 'Parameter-Kombinationen', 'churchtools-suite' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Instructions -->
		<div class="cts-card" style="max-width: 900px; margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">📋</span>
				<h3><?php esc_html_e( 'Anleitung', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<ol style="margin: 0; padding-left: 20px;">
					<li style="margin-bottom: 12px;">
						<strong><?php esc_html_e( 'Demo-Seite erstellen:', 'churchtools-suite' ); ?></strong><br>
						<?php esc_html_e( 'Neue WordPress-Seite anlegen → Code-Editor öffnen → Demo-HTML einfügen (Button unten)', 'churchtools-suite' ); ?>
					</li>
					<li style="margin-bottom: 12px;">
						<strong><?php esc_html_e( 'Systematisch testen:', 'churchtools-suite' ); ?></strong><br>
						<?php esc_html_e( 'Alle Views durchgehen und auf Layout, Funktionalität und Performance prüfen', 'churchtools-suite' ); ?>
					</li>
					<li style="margin-bottom: 12px;">
						<strong><?php esc_html_e( 'Probleme dokumentieren:', 'churchtools-suite' ); ?></strong><br>
						<?php esc_html_e( 'Im Notizen-Bereich der Demo-Seite festhalten, was optimiert werden muss', 'churchtools-suite' ); ?>
					</li>
				</ol>
			</div>
		</div>

		<!-- Shortcode Overview -->
		<div class="cts-card" style="max-width: 900px; margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">📚</span>
				<h3><?php esc_html_e( 'Verfügbare Shortcode-Typen', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<?php if ( ! empty( $shortcuts ) ) : ?>
					<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
						<?php foreach ( $shortcuts as $shortcut ) : ?>
							<div style="padding: 12px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #667eea;">
								<strong style="color: #1d2327; display: block; margin-bottom: 4px;">
									<?php echo esc_html( $shortcut['name'] ); ?>
								</strong>
								<span style="color: #646970; font-size: 13px;">
									<?php echo esc_html( $shortcut['count'] ); ?> <?php esc_html_e( 'Varianten', 'churchtools-suite' ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Shortcode-Referenz wird geladen...', 'churchtools-suite' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Live Demo Tabs -->
		<div class="cts-card" style="margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">🎬</span>
				<h3><?php esc_html_e( 'Live Demo', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body" style="padding: 0;">
				<!-- Tab Navigation -->
				<div style="display: flex; flex-wrap: wrap; gap: 4px; padding: 16px 16px 0; border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
					<button type="button" class="cts-demo-tab active" data-tab="calendar">📅 Calendar</button>
					<button type="button" class="cts-demo-tab" data-tab="list">📋 List</button>
					<button type="button" class="cts-demo-tab" data-tab="grid">▦ Grid</button>
					<button type="button" class="cts-demo-tab" data-tab="slider">🎠 Slider</button>
					<button type="button" class="cts-demo-tab" data-tab="countdown">⏱️ Countdown</button>
					<button type="button" class="cts-demo-tab" data-tab="cover">🎨 Cover</button>
					<button type="button" class="cts-demo-tab" data-tab="timetable">🗓️ Timetable</button>
					<button type="button" class="cts-demo-tab" data-tab="widget">🎁 Widget</button>
				</div>
				
				<!-- Tab Content -->
				<div class="cts-demo-content">
					
					<!-- Calendar Demo -->
					<div class="cts-demo-panel active" id="demo-calendar">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Monthly Modern</h4>
								<code>[cts_calendar view="monthly-modern" limit="20"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_calendar view="monthly-modern" limit="20"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Weekly Fluent</h4>
								<code>[cts_calendar view="weekly-fluent" limit="10"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_calendar view="weekly-fluent" limit="10"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- List Demo -->
					<div class="cts-demo-panel" id="demo-list">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Classic List</h4>
								<code>[cts_list view="classic" limit="10" show_services="true"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_list view="classic" limit="10" show_services="true"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Modern List</h4>
								<code>[cts_list view="modern" limit="10" show_services="true"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_list view="modern" limit="10" show_services="true"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Minimal List</h4>
								<code>[cts_list view="minimal" limit="10"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_list view="minimal" limit="10"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Grid Demo -->
					<div class="cts-demo-panel" id="demo-grid">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Simple Grid (3 Columns)</h4>
								<code>[cts_grid view="simple" columns="3" limit="9"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_grid view="simple" columns="3" limit="9"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Modern Grid</h4>
								<code>[cts_grid view="modern" columns="3" limit="6"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_grid view="modern" columns="3" limit="6"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Colorful Grid</h4>
								<code>[cts_grid view="colorful" columns="3" limit="6"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_grid view="colorful" columns="3" limit="6"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Slider Demo -->
					<div class="cts-demo-panel" id="demo-slider">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Slider Type 1</h4>
								<code>[cts_slider view="type-1" limit="5" autoplay="false"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_slider view="type-1" limit="5" autoplay="false"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Slider Type 2</h4>
								<code>[cts_slider view="type-2" limit="5" autoplay="false"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_slider view="type-2" limit="5" autoplay="false"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Countdown Demo -->
					<div class="cts-demo-panel" id="demo-countdown">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Countdown Type 1</h4>
								<code>[cts_countdown view="type-1"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_countdown view="type-1"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Countdown Type 2</h4>
								<code>[cts_countdown view="type-2"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_countdown view="type-2"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Cover Demo -->
					<div class="cts-demo-panel" id="demo-cover">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Cover Classic</h4>
								<code>[cts_cover view="classic" limit="1"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_cover view="classic" limit="1"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Cover Modern</h4>
								<code>[cts_cover view="modern" limit="1"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_cover view="modern" limit="1"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Timetable Demo -->
					<div class="cts-demo-panel" id="demo-timetable">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Timetable Modern</h4>
								<code>[cts_timetable view="modern" limit="20"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_timetable view="modern" limit="20"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Timetable Clean</h4>
								<code>[cts_timetable view="clean" limit="20"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_timetable view="clean" limit="20"]' ); ?>
							</div>
						</div>
					</div>
					
					<!-- Widget Demo -->
					<div class="cts-demo-panel" id="demo-widget">
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Upcoming Events Widget</h4>
								<code>[cts_widget view="upcoming-events" limit="5"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_widget view="upcoming-events" limit="5"]' ); ?>
							</div>
						</div>
						
						<div class="cts-demo-item">
							<div class="cts-demo-item-header">
								<h4>Calendar Widget</h4>
								<code>[cts_widget view="calendar-widget"]</code>
							</div>
							<div class="cts-demo-item-preview">
								<?php echo do_shortcode( '[cts_widget view="calendar-widget"]' ); ?>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		
		<!-- Demo HTML Export -->
		<div class="cts-card" style="max-width: 900px; margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">💾</span>
				<h3><?php esc_html_e( 'Demo-HTML für WordPress', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<p style="margin-top: 0;">
					<?php esc_html_e( 'Kopiere den gesamten HTML-Code und füge ihn in eine neue WordPress-Seite ein (Code-Editor-Modus).', 'churchtools-suite' ); ?>
				</p>
				
				<div style="position: relative;">
					<textarea id="cts-demo-html" readonly style="width: 100%; height: 300px; font-family: monospace; font-size: 12px; padding: 12px; border: 1px solid #8c8f94; border-radius: 4px; background: #f9fafb;"><?php echo esc_textarea( $demo_content ); ?></textarea>
					<button type="button" id="cts-copy-demo-html" class="button button-primary" style="margin-top: 12px;">
						<span class="dashicons dashicons-clipboard" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'HTML kopieren', 'churchtools-suite' ); ?>
					</button>
					<span id="cts-copy-feedback" style="display: none; margin-left: 12px; color: #00a32a; font-weight: 600;">
						✓ <?php esc_html_e( 'In Zwischenablage kopiert!', 'churchtools-suite' ); ?>
					</span>
				</div>
			</div>
		</div>

		<!-- Quick Reference -->
		<div class="cts-card" style="max-width: 900px; margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">🔖</span>
				<h3><?php esc_html_e( 'Quick Reference', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<div style="background: #f9fafb; padding: 16px; border-radius: 6px; border: 1px solid #e5e7eb;">
					<h4 style="margin-top: 0;"><?php esc_html_e( 'Häufigste Shortcodes:', 'churchtools-suite' ); ?></h4>
					<ul style="margin: 0; padding-left: 20px; list-style: none;">
						<li style="margin-bottom: 8px;">
							<code style="background: #1e293b; color: #10b981; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
								[cts_list view="classic" limit="10" show_services="true"]
							</code>
						</li>
						<li style="margin-bottom: 8px;">
							<code style="background: #1e293b; color: #10b981; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
								[cts_calendar view="monthly-modern" limit="20"]
							</code>
						</li>
						<li style="margin-bottom: 8px;">
							<code style="background: #1e293b; color: #10b981; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
								[cts_grid view="simple" columns="3" limit="9"]
							</code>
						</li>
						<li style="margin-bottom: 8px;">
							<code style="background: #1e293b; color: #10b981; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
								[cts_countdown view="type-1"]
							</code>
						</li>
					</ul>
					
					<h4 style="margin: 20px 0 8px;"><?php esc_html_e( 'Gemeinsame Parameter:', 'churchtools-suite' ); ?></h4>
					<ul style="margin: 0; padding-left: 20px;">
						<li><code>calendar="1,2,3"</code> - <?php esc_html_e( 'Kalender-IDs filtern', 'churchtools-suite' ); ?></li>
						<li><code>limit="10"</code> - <?php esc_html_e( 'Max. Anzahl Events', 'churchtools-suite' ); ?></li>
						<li><code>from="today"</code> - <?php esc_html_e( 'Start-Datum', 'churchtools-suite' ); ?></li>
						<li><code>to="+30 days"</code> - <?php esc_html_e( 'End-Datum', 'churchtools-suite' ); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Documentation Links -->
		<div class="cts-card" style="max-width: 900px; margin-bottom: 30px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">📖</span>
				<h3><?php esc_html_e( 'Dokumentation', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<div style="display: grid; gap: 12px;">
					<a href="<?php echo esc_url( CHURCHTOOLS_SUITE_URL . 'SHORTCODE-DEMO.md' ); ?>" class="button" target="_blank">
						<span class="dashicons dashicons-media-document" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'SHORTCODE-DEMO.md', 'churchtools-suite' ); ?>
					</a>
					<a href="<?php echo esc_url( CHURCHTOOLS_SUITE_URL . 'SHORTCODE-REFERENCE.md' ); ?>" class="button" target="_blank">
						<span class="dashicons dashicons-book" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'SHORTCODE-REFERENCE.md', 'churchtools-suite' ); ?>
					</a>
					<a href="<?php echo esc_url( CHURCHTOOLS_SUITE_URL . 'TEST-DOCUMENTATION.md' ); ?>" class="button" target="_blank">
						<span class="dashicons dashicons-clipboard" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'TEST-DOCUMENTATION.md', 'churchtools-suite' ); ?>
					</a>
					<a href="<?php echo esc_url( CHURCHTOOLS_SUITE_URL . 'SHORTCODE-GUIDE.md' ); ?>" class="button" target="_blank">
						<span class="dashicons dashicons-lightbulb" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'SHORTCODE-GUIDE.md', 'churchtools-suite' ); ?>
					</a>
				</div>
			</div>
		</div>

		<!-- Test Checklist -->
		<div class="cts-card" style="max-width: 900px;">
			<div class="cts-card-header">
				<span class="cts-card-icon">✅</span>
				<h3><?php esc_html_e( 'Test-Checkliste', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
					<div>
						<h4 style="margin-top: 0; color: #667eea;"><?php esc_html_e( 'Funktionalität', 'churchtools-suite' ); ?></h4>
						<ul style="list-style: none; padding: 0;">
							<li>☐ <?php esc_html_e( 'Alle Shortcodes rendern', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Events werden angezeigt', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Kalenderfilter wirken', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Services erscheinen', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Datumsformatierung', 'churchtools-suite' ); ?></li>
						</ul>
					</div>
					<div>
						<h4 style="margin-top: 0; color: #667eea;"><?php esc_html_e( 'Layout', 'churchtools-suite' ); ?></h4>
						<ul style="list-style: none; padding: 0;">
							<li>☐ <?php esc_html_e( 'Responsive Design', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Keine Layout-Breaks', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Farben konsistent', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Schriften lesbar', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Abstände harmonisch', 'churchtools-suite' ); ?></li>
						</ul>
					</div>
					<div>
						<h4 style="margin-top: 0; color: #667eea;"><?php esc_html_e( 'Performance', 'churchtools-suite' ); ?></h4>
						<ul style="list-style: none; padding: 0;">
							<li>☐ <?php esc_html_e( 'Ladezeiten < 2s', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Keine JS-Fehler', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Keine PHP-Errors', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'CSS wird geladen', 'churchtools-suite' ); ?></li>
							<li>☐ <?php esc_html_e( 'Kein Blocking', 'churchtools-suite' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

	</div>

</div>

<style>
/* Live Demo Tabs */
.cts-demo-tab {
	padding: 10px 16px;
	background: transparent;
	border: none;
	border-bottom: 2px solid transparent;
	cursor: pointer;
	font-size: 13px;
	font-weight: 500;
	color: #646970;
	transition: all 0.2s;
}

.cts-demo-tab:hover {
	color: #1d2327;
	background: rgba(0,0,0,0.03);
}

.cts-demo-tab.active {
	color: #2271b1;
	border-bottom-color: #2271b1;
	font-weight: 600;
}

/* Demo Content Panels */
.cts-demo-content {
	padding: 20px;
	min-height: 400px;
}

.cts-demo-panel {
	display: none;
}

.cts-demo-panel.active {
	display: block;
}

/* Demo Items */
.cts-demo-item {
	margin-bottom: 40px;
	padding-bottom: 40px;
	border-bottom: 1px solid #e5e7eb;
}

.cts-demo-item:last-child {
	border-bottom: none;
	margin-bottom: 0;
	padding-bottom: 0;
}

.cts-demo-item-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 12px;
	margin-bottom: 16px;
	padding: 12px 16px;
	background: #f9fafb;
	border-left: 4px solid #667eea;
	border-radius: 4px;
}

.cts-demo-item-header h4 {
	margin: 0;
	font-size: 16px;
	color: #1d2327;
}

.cts-demo-item-header code {
	padding: 6px 12px;
	background: #1e293b;
	color: #10b981;
	border-radius: 4px;
	font-size: 13px;
	font-family: 'Courier New', monospace;
	font-weight: 500;
}

.cts-demo-item-preview {
	padding: 20px;
	background: #fff;
	border: 1px solid #e5e7eb;
	border-radius: 6px;
	min-height: 200px;
}

/* Loading State */
.cts-demo-loading {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 60px 20px;
	color: #646970;
	font-size: 14px;
}

.cts-demo-loading:before {
	content: "⏳";
	font-size: 32px;
	margin-right: 12px;
	animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.5; }
}

/* Empty State */
.cts-demo-empty {
	text-align: center;
	padding: 60px 20px;
	color: #646970;
}

.cts-demo-empty:before {
	content: "📭";
	font-size: 64px;
	display: block;
	margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 768px) {
	.cts-demo-item-header {
		flex-direction: column;
		align-items: flex-start;
	}
	
	.cts-demo-item-header code {
		font-size: 11px;
		word-break: break-all;
	}
}
</style>

<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
	console.log('CTS Demo: DOM Ready');
	
	// Tab Navigation
	const tabs = document.querySelectorAll('.cts-demo-tab');
	const panels = document.querySelectorAll('.cts-demo-panel');
	
	console.log('CTS Demo: Found', tabs.length, 'tabs and', panels.length, 'panels');
	
	tabs.forEach(function(tab) {
		tab.addEventListener('click', function(e) {
			e.preventDefault();
			const targetTab = this.getAttribute('data-tab');
			const targetId = 'demo-' + targetTab;
			
			console.log('CTS Demo: Tab clicked:', targetTab, '→', targetId);
			
			// Deactivate all tabs and panels
			tabs.forEach(function(t) {
				t.classList.remove('active');
			});
			panels.forEach(function(p) {
				p.classList.remove('active');
			});
			
			// Activate clicked tab and corresponding panel
			this.classList.add('active');
			const targetPanel = document.getElementById(targetId);
			if (targetPanel) {
				targetPanel.classList.add('active');
				console.log('CTS Demo: Activated panel:', targetId);
			} else {
				console.error('CTS Demo: Panel not found:', targetId);
			}
		});
	});
	
	// Copy HTML to clipboard
	const copyButton = document.getElementById('cts-copy-demo-html');
	const textarea = document.getElementById('cts-demo-html');
	const feedback = document.getElementById('cts-copy-feedback');
	
	if (copyButton && textarea) {
		copyButton.addEventListener('click', function() {
			textarea.select();
			
			// Modern clipboard API
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(textarea.value).then(function() {
					if (feedback) {
						feedback.style.display = 'inline';
						setTimeout(function() {
							feedback.style.display = 'none';
						}, 3000);
					}
				});
			} else {
				// Fallback for older browsers
				document.execCommand('copy');
				if (feedback) {
					feedback.style.display = 'inline';
					setTimeout(function() {
						feedback.style.display = 'none';
					}, 3000);
				}
			}
		});
	}
	
	console.log('CTS Demo: Initialized successfully');
});
</script>
