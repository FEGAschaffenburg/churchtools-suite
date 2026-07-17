<?php
/**
 * Enhanced Shortcode Manager Page
 * 
 * Verwaltung für ChurchTools Suite Shortcodes mit Preset-System:
 * - Übersicht aller Standard-Shortcodes
 * - Eigene Presets erstellen und speichern
 * - System-Presets für häufige Use Cases
 * 
 * @package ChurchTools_Suite
 * @since   0.5.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Presets Repository
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
$presets_repo = new ChurchTools_Suite_Shortcode_Presets_Repository();

// Get saved presets
$all_presets = $presets_repo->get_all_presets();
$saved_presets = array_filter( $all_presets, fn($p) => ! $p['is_system'] ); // Nur User-Presets
$system_presets = array_filter( $all_presets, fn($p) => $p['is_system'] ); // System-Presets

// Shortcode Definitions (nur getestete Views)
$shortcodes = [
	[
		'tag' => 'cts_list',
		'name' => 'List',
		'icon' => '📋',
		'category' => 'list',
		'description' => 'Zeigt Events als Liste an',
		'views' => ['classic', 'classic-with-images', 'medium'], // Nur getestete Views
		'params' => [
			// === Ansicht & Basis ===
			'view' => ['type' => 'select', 'label' => 'View', 'options' => ['classic', 'classic-with-images', 'medium'], 'section' => '📋 Ansicht & Basis'],
			'calendar' => ['type' => 'checkboxes', 'label' => 'Kalender auswählen', 'section' => '📋 Ansicht & Basis'],
			'limit' => ['type' => 'number', 'label' => 'Anzahl Events', 'default' => '20', 'section' => '⚙️ Basis-Einstellungen'],
			// === Anzeige-Optionen ===
			'show_images' => ['type' => 'toggle', 'label' => 'Bilder anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_description' => ['type' => 'toggle', 'label' => 'Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_location' => ['type' => 'toggle', 'label' => 'Ort anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_services' => ['type' => 'toggle', 'label' => 'Services anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_calendar_name' => ['type' => 'toggle', 'label' => 'Kalender-Name anzeigen', 'default' => false, 'section' => '👁️ Anzeige-Optionen'],
			'show_time' => ['type' => 'toggle', 'label' => 'Uhrzeit anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			// === Filter & Sortierung ===
			'order' => ['type' => 'select', 'label' => 'Sortierung', 'options' => ['asc' => 'Aufsteigend', 'desc' => 'Absteigend'], 'default' => 'asc', 'section' => '🔍 Filter & Sortierung'],
			'date_from' => ['type' => 'date', 'label' => 'Datum von (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			'date_to' => ['type' => 'date', 'label' => 'Datum bis (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			// === Legacy ===
			'from' => ['type' => 'date', 'label' => 'Von Datum (Legacy)', 'section' => '📦 Legacy-Parameter'],
			'to' => ['type' => 'date', 'label' => 'Bis Datum (Legacy)', 'section' => '📦 Legacy-Parameter'],
			'class' => ['type' => 'text', 'label' => 'CSS Klasse', 'section' => '🎨 Styling'],
		],
		'example' => '[cts_list view="classic"]',
	],
	[
		'tag' => 'cts_calendar',
		'name' => 'Calendar',
		'icon' => '📅',
		'category' => 'calendar',
		'description' => 'Zeigt Events in Kalender-Ansicht an',
		'views' => ['monthly-modern'], // Nur getestete Views
		'params' => [
			'view' => ['type' => 'select', 'label' => 'View', 'options' => ['monthly-modern'], 'section' => '📋 Ansicht & Basis'],
			'calendar' => ['type' => 'checkboxes', 'label' => 'Kalender auswählen', 'section' => '📋 Ansicht & Basis'],
			'from' => ['type' => 'date', 'label' => 'Von Datum', 'section' => '🔍 Filter & Sortierung'],
			'to' => ['type' => 'date', 'label' => 'Bis Datum', 'section' => '🔍 Filter & Sortierung'],
			'class' => ['type' => 'text', 'label' => 'CSS Klasse', 'section' => '🎨 Styling'],
		],
		'example' => '[cts_calendar view="monthly-modern"]',
	],
	[
		'tag' => 'cts_grid',
		'name' => 'Grid',
		'icon' => '🎯',
		'category' => 'grid',
		'description' => 'Zeigt Events als Karten-Raster (Klassisch: Hero-Bild+Buttons, Einfach: Alle Infos, Minimal: Essentials+Info-Icon)',
		'views' => ['grid-klassisch', 'grid-einfach', 'grid-minimal', 'grid-modern'], // v1.1.0.5: Standardisierte IDs
		'params' => [
			// === Ansicht & Basis ===
			'view' => ['type' => 'select', 'label' => 'View', 'options' => ['grid-klassisch' => 'Klassisch (Hero-Bild)', 'grid-einfach' => 'Einfach (Alle Details)', 'grid-minimal' => 'Minimal (Kompakt)', 'grid-modern' => 'Modern (Card-Style)'], 'section' => '📋 Ansicht & Basis'],
			'calendar' => ['type' => 'checkboxes', 'label' => 'Kalender auswählen', 'section' => '📋 Ansicht & Basis'],
			'limit' => ['type' => 'number', 'label' => 'Anzahl Events', 'default' => '12', 'section' => '⚙️ Basis-Einstellungen'],
			// === Layout ===
			'columns' => ['type' => 'number', 'label' => 'Spalten (1-4)', 'default' => '3', 'section' => '⚙️ Basis-Einstellungen'],
			// === Anzeige-Optionen ===
			'show_description' => ['type' => 'toggle', 'label' => 'Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_location' => ['type' => 'toggle', 'label' => 'Ort anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_services' => ['type' => 'toggle', 'label' => 'Services anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_calendar_name' => ['type' => 'toggle', 'label' => 'Kalender-Name anzeigen', 'default' => false, 'section' => '👁️ Anzeige-Optionen'],
			'show_time' => ['type' => 'toggle', 'label' => 'Uhrzeit anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			// === Filter & Sortierung ===
			'order' => ['type' => 'select', 'label' => 'Sortierung', 'options' => ['asc' => 'Aufsteigend', 'desc' => 'Absteigend'], 'default' => 'asc', 'section' => '🔍 Filter & Sortierung'],
			'date_from' => ['type' => 'date', 'label' => 'Datum von (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			'date_to' => ['type' => 'date', 'label' => 'Datum bis (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			// === Legacy ===
			'from' => ['type' => 'date', 'label' => 'Von Datum (Legacy)', 'section' => '📦 Legacy-Parameter'],
			'to' => ['type' => 'date', 'label' => 'Bis Datum (Legacy)', 'section' => '📦 Legacy-Parameter'],
			'class' => ['type' => 'text', 'label' => 'CSS Klasse', 'section' => '🎨 Styling'],
		],
		'example' => '[cts_grid view="grid-klassisch" columns="3"]',
	],
	[
		'tag' => 'cts_countdown',
		'name' => 'Countdown',
		'icon' => '⏱️',
		'category' => 'countdown',
		'description' => 'Zeigt nächstes kommendes Event mit Live-Countdown-Timer (Split-Layout: Timer links, Hero-Image rechts)',
		'views' => ['countdown-klassisch'], // v1.1.1.0: Neuer View-Typ
		'params' => [
			// === Ansicht & Basis ===
			'view' => ['type' => 'select', 'label' => 'View', 'options' => ['countdown-klassisch' => 'Klassisch (Split-Layout)'], 'section' => '📋 Ansicht & Basis'],
			'calendar' => ['type' => 'checkboxes', 'label' => 'Kalender auswählen', 'section' => '📋 Ansicht & Basis'],
			// === Anzeige-Optionen ===
			'show_event_description' => ['type' => 'toggle', 'label' => 'Event-Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_appointment_description' => ['type' => 'toggle', 'label' => 'Termin-Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_location' => ['type' => 'toggle', 'label' => 'Ort anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_calendar_name' => ['type' => 'toggle', 'label' => 'Kalender-Name anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_images' => ['type' => 'toggle', 'label' => 'Hero-Image anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			// === Verhalten ===
			'event_action' => ['type' => 'select', 'label' => 'Event Aktion', 'options' => ['modal' => 'Modal öffnen', 'page' => 'Seite öffnen', 'none' => 'Keine Aktion'], 'default' => 'modal', 'section' => '⚙️ Basis-Einstellungen'],
			// === Filter & Sortierung ===
			'date_from' => ['type' => 'date', 'label' => 'Datum von (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			'date_to' => ['type' => 'date', 'label' => 'Datum bis (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			// === Styling ===
			'class' => ['type' => 'text', 'label' => 'CSS Klasse', 'section' => '🎨 Styling'],
		],
		'example' => '[cts_countdown view="countdown-klassisch"]',
	],
	[
		'tag' => 'cts_carousel',
		'name' => 'Carousel',
		'icon' => '🎠',
		'category' => 'carousel',
		'description' => 'Horizontales Karussell mit Swipe-Navigation (Hero-Images, mehrere Events, Touch-Support)',
		'views' => ['carousel-klassisch'], // v1.1.1.0: Neuer View-Typ
		'params' => [
			// === Ansicht & Basis ===
			'view' => ['type' => 'select', 'label' => 'View', 'options' => ['carousel-klassisch' => 'Klassisch (Swipe)'], 'section' => '📋 Ansicht & Basis'],
			'calendar' => ['type' => 'checkboxes', 'label' => 'Kalender auswählen', 'section' => '📋 Ansicht & Basis'],
			'limit' => ['type' => 'number', 'label' => 'Anzahl Events', 'default' => '12', 'section' => '⚙️ Basis-Einstellungen'],
			// === Carousel-Einstellungen ===
			'slides_per_view' => ['type' => 'number', 'label' => 'Slides pro Ansicht (1-6)', 'default' => '3', 'section' => '🎠 Carousel-Einstellungen'],
			'autoplay' => ['type' => 'toggle', 'label' => 'Auto-Play aktivieren', 'default' => false, 'section' => '🎠 Carousel-Einstellungen'],
			'autoplay_delay' => ['type' => 'number', 'label' => 'Auto-Play Verzögerung (ms)', 'default' => '5000', 'section' => '🎠 Carousel-Einstellungen'],
			'loop' => ['type' => 'toggle', 'label' => 'Loop-Modus (endlos)', 'default' => true, 'section' => '🎠 Carousel-Einstellungen'],
			// === Anzeige-Optionen ===
			'show_event_description' => ['type' => 'toggle', 'label' => 'Event-Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_appointment_description' => ['type' => 'toggle', 'label' => 'Termin-Beschreibung anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_location' => ['type' => 'toggle', 'label' => 'Ort anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_time' => ['type' => 'toggle', 'label' => 'Uhrzeit anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_services' => ['type' => 'toggle', 'label' => 'Services anzeigen', 'default' => false, 'section' => '👁️ Anzeige-Optionen'],
			'show_tags' => ['type' => 'toggle', 'label' => 'Tags anzeigen', 'default' => false, 'section' => '👁️ Anzeige-Optionen'],
			'show_calendar_name' => ['type' => 'toggle', 'label' => 'Kalender-Name anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			'show_images' => ['type' => 'toggle', 'label' => 'Hero-Images anzeigen', 'default' => true, 'section' => '👁️ Anzeige-Optionen'],
			// === Verhalten ===
			'event_action' => ['type' => 'select', 'label' => 'Event Aktion', 'options' => ['modal' => 'Modal öffnen', 'page' => 'Seite öffnen', 'none' => 'Keine Aktion'], 'default' => 'modal', 'section' => '⚙️ Basis-Einstellungen'],
			// === Filter & Sortierung ===
			'date_from' => ['type' => 'date', 'label' => 'Datum von (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			'date_to' => ['type' => 'date', 'label' => 'Datum bis (YYYY-MM-DD)', 'section' => '🔍 Filter & Sortierung'],
			// === Styling ===
			'class' => ['type' => 'text', 'label' => 'CSS Klasse', 'section' => '🎨 Styling'],
		],
		'example' => '[cts_carousel view="carousel-klassisch" slides_per_view="3" autoplay="true"]',
	],
];
?>

<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>⚡</span>
			<?php esc_html_e( 'Shortcode Manager', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'Verwalte Standard-Shortcodes und erstelle eigene Presets', 'churchtools-suite' ); ?></p>
	</div>
	
	<!-- Tabs -->
	<div class="cts-tabs" style="margin-bottom: 20px;">
		<a href="#" class="cts-tab active" data-tab="standards">
			<span>📚</span>
			<?php esc_html_e( 'Standard-Shortcodes', 'churchtools-suite' ); ?>
		</a>
		<a href="#" class="cts-tab" data-tab="presets">
			<span>⭐</span>
			<?php esc_html_e( 'Meine Presets', 'churchtools-suite' ); ?> (<?php echo count( array_filter( $saved_presets, fn($p) => ! $p['is_system'] ) ); ?>)
		</a>
		<a href="#" class="cts-tab" data-tab="create" id="cts-create-tab">
			<span id="cts-create-icon">➕</span>
			<span id="cts-create-label"><?php esc_html_e( 'Neues Preset erstellen', 'churchtools-suite' ); ?></span>
		</a>
	</div>
	
	<!-- Tab: Standard Shortcodes -->
	<div id="tab-standards" class="cts-tab-content active">
		<div class="cts-card" style="max-width: 1200px; margin-bottom: 20px;">
			<div class="cts-card-body">
				<div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
					<input 
						type="text" 
						id="cts-shortcode-search" 
						placeholder="<?php esc_attr_e( 'Shortcode suchen...', 'churchtools-suite' ); ?>"
						class="cts-form-input"
						style="max-width: 300px; margin: 0;"
					>
					
					<select id="cts-category-filter" class="cts-form-select" style="max-width: 200px; margin: 0;">
						<option value=""><?php esc_html_e( 'Alle Kategorien', 'churchtools-suite' ); ?></option>
						<option value="list">📋 List</option>
						<option value="calendar">📅 Calendar</option>
						<option value="grid">🎯 Grid</option>
					</select>
					
					<span id="cts-shortcode-count" style="margin-left: auto; color: #6b7280; font-size: 14px; font-weight: 600;">
						<?php echo count( $shortcodes ); ?> Shortcodes
					</span>
				</div>
			</div>
		</div>
		
		<div id="cts-shortcodes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; max-width: 1200px;">
			
			<?php foreach ( $shortcodes as $shortcode ) : ?>
				<div class="cts-shortcode-card" data-category="<?php echo esc_attr( $shortcode['category'] ); ?>" data-tag="<?php echo esc_attr( $shortcode['tag'] ); ?>" data-name="<?php echo esc_attr( strtolower( $shortcode['name'] ) ); ?>">
					<div class="cts-card">
						<div class="cts-card-header">
							<span class="cts-card-icon"><?php echo $shortcode['icon']; ?></span>
							<h3><?php echo esc_html( $shortcode['name'] ); ?></h3>
						</div>
						<div class="cts-card-body">
							<p style="color: #6b7280; font-size: 13px; margin: 0 0 12px;">
								<?php echo esc_html( $shortcode['description'] ); ?>
							</p>
							
							<div style="background: #f9fafb; padding: 10px; border-radius: 4px; margin-bottom: 12px;">
								<code style="font-size: 12px; color: #d63638;"><?php echo esc_html( $shortcode['example'] ); ?></code>
							</div>
							
							<details style="margin-bottom: 12px;">
								<summary style="cursor: pointer; font-size: 13px; font-weight: 600; color: #2271b1;">
									<?php esc_html_e( 'Verfügbare Views', 'churchtools-suite' ); ?> (<?php echo count( $shortcode['views'] ); ?>)
								</summary>
								<div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
									<?php foreach ( $shortcode['views'] as $view ) : ?>
										<span style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-size: 11px; color: #374151;">
											<?php echo esc_html( $view ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							</details>
							
							<details>
								<summary style="cursor: pointer; font-size: 13px; font-weight: 600; color: #2271b1;">
									<?php esc_html_e( 'Parameter', 'churchtools-suite' ); ?> (<?php echo count( $shortcode['params'] ); ?>)
								</summary>
								<div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
									<?php foreach ( $shortcode['params'] as $param => $config ) : ?>
										<span style="background: #dbeafe; padding: 4px 8px; border-radius: 4px; font-size: 11px; color: #1e40af;">
											<?php echo esc_html( $param ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							</details>
						</div>
						<div class="cts-card-footer" style="display: flex; gap: 8px;">
							<button class="cts-button cts-button-secondary cts-copy-shortcode" data-shortcode="<?php echo esc_attr( $shortcode['example'] ); ?>">
								📋 <?php esc_html_e( 'Kopieren', 'churchtools-suite' ); ?>
							</button>
							<button class="cts-button cts-button-secondary cts-create-from-standard" data-shortcode="<?php echo esc_attr( wp_json_encode( $shortcode ) ); ?>">
								⭐ <?php esc_html_e( 'Als Preset', 'churchtools-suite' ); ?>
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			
		</div>
	</div>
	
	<!-- Tab: Meine Presets -->
	<div id="tab-presets" class="cts-tab-content" style="display: none;">
		<div id="cts-presets-container" style="max-width: 1200px;">
			<?php if ( empty( $saved_presets ) ) : ?>
				<div style="text-align: center; padding: 60px 20px; color: #6b7280;">
					<span style="font-size: 64px; display: block; margin-bottom: 16px;">⭐</span>
					<h3 style="margin: 0 0 8px; font-size: 18px; color: #374151;"><?php esc_html_e( 'Noch keine Presets', 'churchtools-suite' ); ?></h3>
					<p style="margin: 0 0 16px; font-size: 14px;"><?php esc_html_e( 'Erstelle dein erstes Preset basierend auf einem Standard-Shortcode', 'churchtools-suite' ); ?></p>
					<button class="cts-button cts-button-primary" onclick="document.querySelector('[data-tab=create]').click();">
						➕ <?php esc_html_e( 'Preset erstellen', 'churchtools-suite' ); ?>
					</button>
				</div>
			<?php else : ?>
				<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
					<?php foreach ( $saved_presets as $preset ) : 
						$is_system = $preset['is_system'];
						$config = $preset['configuration'];
						
						// Build shortcode string - NUR view Parameter anzeigen (Preset-Slug)
						// Der Rest wird intern durch apply_preset_config() geladen
						$view_value = $config['view'] ?? '';
						$shortcode_string = '[' . $preset['shortcode_tag'] . ' view="' . esc_attr( $view_value ) . '"]';
					?>
						<div class="cts-preset-card" data-preset-id="<?php echo $preset['id']; ?>">
							<div class="cts-card">
								<div class="cts-card-header" style="display: flex; justify-content: space-between; align-items: center;">
									<div style="display: flex; align-items: center; gap: 8px;">
										<span class="cts-card-icon"><?php echo $is_system ? '🔒' : '⭐'; ?></span>
										<h3><?php echo esc_html( $preset['name'] ); ?></h3>
									</div>
									<?php if ( $is_system ) : ?>
										<span style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-size: 11px; color: #374151;">
											System
										</span>
									<?php endif; ?>
								</div>
								<div class="cts-card-body">
									<?php if ( ! empty( $preset['description'] ) ) : ?>
										<p style="color: #6b7280; font-size: 13px; margin: 0 0 12px;">
											<?php echo esc_html( $preset['description'] ); ?>
										</p>
									<?php endif; ?>
									
									<div style="background: #f9fafb; padding: 10px; border-radius: 4px; margin-bottom: 12px;">
										<code style="font-size: 12px; color: #d63638; word-break: break-all;">
											<?php echo esc_html( $shortcode_string ); ?>
										</code>
									</div>
									
									<details>
										<summary style="cursor: pointer; font-size: 13px; font-weight: 600; color: #2271b1;">
											<?php esc_html_e( 'Konfiguration', 'churchtools-suite' ); ?>
										</summary>
										<div style="margin-top: 8px;">
											<?php foreach ( $config as $key => $value ) : ?>
												<div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f0f0f1;">
													<span style="font-size: 12px; color: #6b7280;"><?php echo esc_html( $key ); ?>:</span>
													<span style="font-size: 12px; color: #1f2937; font-weight: 600;"><?php echo esc_html( $value ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									</details>
								</div>
								<div class="cts-card-footer" style="display: flex; gap: 8px; justify-content: space-between;">
									<button class="cts-button cts-button-secondary cts-copy-preset" data-shortcode="<?php echo esc_attr( $shortcode_string ); ?>">
										📋 <?php esc_html_e( 'Kopieren', 'churchtools-suite' ); ?>
									</button>
									<?php if ( ! $is_system ) : ?>									<button class="cts-button cts-button-secondary cts-edit-preset" data-preset="<?php echo esc_attr( wp_json_encode( $preset ) ); ?>">
										✏️ <?php esc_html_e( 'Bearbeiten', 'churchtools-suite' ); ?>
									</button>										<button class="cts-button cts-button-secondary cts-delete-preset" data-preset-id="<?php echo $preset['id']; ?>" style="color: #d63638; border-color: #d63638;">
											🗑️ <?php esc_html_e( 'Löschen', 'churchtools-suite' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	
	<!-- Tab: Neues Preset erstellen -->
	<div id="tab-create" class="cts-tab-content" style="display: none;">
		<div class="cts-card" style="max-width: 800px;">
			<div class="cts-card-header">
				<h3 id="cts-preset-form-title"><?php esc_html_e( 'Neues Preset erstellen', 'churchtools-suite' ); ?></h3>
			</div>
			<div class="cts-card-body">
				<form id="cts-preset-form">
					<input type="hidden" id="preset-id" name="preset-id" value="">
					<table class="cts-form-table">
						<tr>
							<th><?php esc_html_e( 'Preset-Name', 'churchtools-suite' ); ?> *</th>
							<td>
								<input 
									type="text" 
									id="preset-name" 
									name="preset-name" 
									class="cts-form-input" 
									required
									placeholder="z.B. Startseite Events"
								>
								<span class="cts-form-description"><?php esc_html_e( 'Eindeutiger Name für dein Preset (wird als view="name" im Shortcode verwendet)', 'churchtools-suite' ); ?></span>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></th>
							<td>
								<textarea 
									id="preset-description" 
									name="preset-description" 
									class="cts-form-textarea"
									rows="3"
									placeholder="Optional: Wofür wird dieses Preset verwendet?"
								></textarea>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Shortcode-Typ', 'churchtools-suite' ); ?> *</th>
							<td>
								<select id="preset-shortcode-tag" name="preset-shortcode-tag" class="cts-form-select" required>
									<option value=""><?php esc_html_e( 'Bitte wählen...', 'churchtools-suite' ); ?></option>
									<?php foreach ( $shortcodes as $sc ) : ?>
										<option value="<?php echo esc_attr( $sc['tag'] ); ?>" data-params="<?php echo esc_attr( wp_json_encode( $sc['params'] ) ); ?>">
											<?php echo esc_html( $sc['icon'] . ' ' . $sc['name'] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					
					<div id="preset-params-container" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f1;">
						<h4 style="margin: 0 0 16px;"><?php esc_html_e( 'Parameter konfigurieren', 'churchtools-suite' ); ?></h4>
						<table class="cts-form-table" id="preset-params-table">
							<!-- Dynamisch gefüllt via JavaScript -->
						</table>
					</div>
					
					<div id="preset-preview" style="display: none; margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 4px;">
						<h4 style="margin: 0 0 8px; font-size: 14px;"><?php esc_html_e( 'Vorschau', 'churchtools-suite' ); ?></h4>
						<code id="preset-preview-code" style="font-size: 13px; color: #d63638; word-break: break-all;"></code>
					</div>
				</form>
			</div>
			<div class="cts-card-footer">
				<button type="button" id="cts-save-preset" class="cts-button cts-button-primary" disabled>
					<span id="cts-save-icon">💾</span> <span id="cts-save-label"><?php esc_html_e( 'Preset speichern', 'churchtools-suite' ); ?></span>
				</button>
				<button type="button" id="cts-cancel-edit" class="cts-button cts-button-secondary" style="display: none;">
					❌ <?php esc_html_e( 'Abbrechen', 'churchtools-suite' ); ?>
				</button>
				<span id="cts-save-result" style="margin-left: 12px;"></span>
			</div>
		</div>
	</div>
	
</div>

<style>
.cts-tab {
	cursor: pointer;
	transition: all 0.2s;
}

.cts-tab:not(.active):hover {
	background: rgba(0,0,0,0.03);
}

.cts-tab-content {
	animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
	from { opacity: 0; transform: translateY(-10px); }
	to { opacity: 1; transform: translateY(0); }
}

.cts-button.copied {
	background: #00a32a !important;
	border-color: #00a32a !important;
	color: #fff !important;
}
</style>

<script>
(function() {
	'use strict';
	
	// Shortcode data (from PHP)
	const shortcodesData = <?php echo wp_json_encode( $shortcodes ); ?>;
	
	// Tab switching
	const tabs = document.querySelectorAll('.cts-tab');
	const tabContents = document.querySelectorAll('.cts-tab-content');
	
	tabs.forEach(tab => {
		tab.addEventListener('click', function(e) {
			e.preventDefault();
			const targetTab = this.dataset.tab;
			
			// Update tabs
			tabs.forEach(t => t.classList.remove('active'));
			this.classList.add('active');
			
			// Update content
			tabContents.forEach(content => {
				if (content.id === 'tab-' + targetTab) {
					content.style.display = 'block';
				} else {
					content.style.display = 'none';
				}
			});
		});
	});
	
	// Reset preset form function (muss VOR Tab-Listener sein)
	function resetPresetForm() {
		const form = document.getElementById('cts-preset-form');
		const presetParamsContainer = document.getElementById('preset-params-container');
		const presetPreview = document.getElementById('preset-preview');
		const saveButton = document.getElementById('cts-save-preset');
		
		if (form) form.reset();
		document.getElementById('preset-id').value = '';
		document.getElementById('cts-preset-form-title').textContent = '<?php esc_html_e( 'Neues Preset erstellen', 'churchtools-suite' ); ?>';
		document.getElementById('cts-create-icon').textContent = '➕';
		document.getElementById('cts-create-label').textContent = '<?php esc_html_e( 'Neues Preset erstellen', 'churchtools-suite' ); ?>';
		document.getElementById('cts-save-icon').textContent = '💾';
		document.getElementById('cts-save-label').textContent = '<?php esc_html_e( 'Preset speichern', 'churchtools-suite' ); ?>';
		document.getElementById('cts-cancel-edit').style.display = 'none';
		if (presetParamsContainer) presetParamsContainer.style.display = 'none';
		if (presetPreview) presetPreview.style.display = 'none';
		if (saveButton) saveButton.disabled = true;
	}
	
	// Filter shortcodes (Tab: Standards)
	const searchInput = document.getElementById('cts-shortcode-search');
	const categoryFilter = document.getElementById('cts-category-filter');
	const shortcodeCards = document.querySelectorAll('.cts-shortcode-card');
	const countDisplay = document.getElementById('cts-shortcode-count');
	
	function filterShortcodes() {
		const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
		const category = categoryFilter ? categoryFilter.value : '';
		let visibleCount = 0;
		
		shortcodeCards.forEach(function(card) {
			const cardCategory = card.getAttribute('data-category');
			const cardTag = card.getAttribute('data-tag');
			const cardName = card.getAttribute('data-name');
			
			const matchesSearch = !searchTerm || cardTag.includes(searchTerm) || cardName.includes(searchTerm);
			const matchesCategory = !category || cardCategory === category;
			
			if (matchesSearch && matchesCategory) {
				card.style.display = 'block';
				visibleCount++;
			} else {
				card.style.display = 'none';
			}
		});
		
		if (countDisplay) {
			countDisplay.textContent = visibleCount + ' Shortcode' + (visibleCount !== 1 ? 's' : '');
		}
	}
	
	if (searchInput) searchInput.addEventListener('input', filterShortcodes);
	if (categoryFilter) categoryFilter.addEventListener('change', filterShortcodes);
	
	// Copy shortcode
	document.querySelectorAll('.cts-copy-shortcode, .cts-copy-preset').forEach(function(button) {
		button.addEventListener('click', function() {
			const shortcode = this.dataset.shortcode || this.getAttribute('data-shortcode');
			const originalText = this.innerHTML;
			
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(shortcode).then(function() {
					button.innerHTML = '✓ Kopiert!';
					button.classList.add('copied');
					
					setTimeout(function() {
						button.innerHTML = originalText;
						button.classList.remove('copied');
					}, 2000);
				}).catch(function() {
					alert('Shortcode: ' + shortcode);
				});
			} else {
				alert('Shortcode: ' + shortcode);
			}
		});
	});
	
	// Create preset from standard shortcode
	document.querySelectorAll('.cts-create-from-standard').forEach(function(button) {
		button.addEventListener('click', function() {
			const shortcodeData = JSON.parse(this.dataset.shortcode);
			
			// Switch to create tab
			document.querySelector('[data-tab="create"]').click();
			
			// Pre-fill form
			document.getElementById('preset-name').value = shortcodeData.name + ' Preset';
			document.getElementById('preset-description').value = shortcodeData.description;
			document.getElementById('preset-shortcode-tag').value = shortcodeData.tag;
			
			// Trigger change event to load parameters
			document.getElementById('preset-shortcode-tag').dispatchEvent(new Event('change'));
		});
	});
	
	// Preset form: Shortcode type change
	const presetShortcodeTag = document.getElementById('preset-shortcode-tag');
	const presetParamsContainer = document.getElementById('preset-params-container');
	const presetParamsTable = document.getElementById('preset-params-table');
	const presetPreview = document.getElementById('preset-preview');
	const presetPreviewCode = document.getElementById('preset-preview-code');
	const saveButton = document.getElementById('cts-save-preset');
	const presetNameInput = document.getElementById('preset-name');
	
	// Update preview function (MUSS VOR Event-Listenern sein)
	function updatePreview() {
		const tag = presetShortcodeTag ? presetShortcodeTag.value : '';
		if (!tag) return;
		
		// Nur View-Parameter im Shortcode anzeigen
		const presetName = document.getElementById('preset-name').value;
		if (presetName) {
			const slug = presetName.toLowerCase()
				.replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
				.replace(/[^a-z0-9]+/g, '-')
				.replace(/^-+|-+$/g, '');
			
			// Minimaler Shortcode - Parameter kommen aus Preset-Config
			const shortcode = '[' + tag + ' view="' + slug + '"]';
			presetPreviewCode.textContent = shortcode;
		}
	}
	
	// Update preview when preset name changes
	if (presetNameInput) {
		presetNameInput.addEventListener('input', updatePreview);
	}
	
	if (presetShortcodeTag) {
		presetShortcodeTag.addEventListener('change', function() {
			const selectedTag = this.value;
			
			if (!selectedTag) {
				presetParamsContainer.style.display = 'none';
				presetPreview.style.display = 'none';
				saveButton.disabled = true;
				return;
			}
			
			// Get params from selected option
			const selectedOption = this.options[this.selectedIndex];
			const paramsJson = selectedOption.dataset.params || '{}';
			const params = JSON.parse(paramsJson);
			
			// Build parameter fields (mit Sections)
			presetParamsTable.innerHTML = '';
			
			// Gruppiere Parameter nach Sections
			const paramsBySection = {};
			Object.keys(params).forEach(paramName => {
				const paramConfig = params[paramName];
				const section = paramConfig.section || '⚙️ Allgemein';
				
				if (!paramsBySection[section]) {
					paramsBySection[section] = [];
				}
				paramsBySection[section].push({name: paramName, config: paramConfig});
			});
			
			// Rendere Sections (mit Collapse-Funktion)
			Object.keys(paramsBySection).forEach((sectionName, index) => {
				// Section Header (collapsible)
				const sectionRow = document.createElement('tr');
				const sectionHeader = document.createElement('th');
				sectionHeader.colSpan = 2;
				sectionHeader.innerHTML = '<span style="cursor: pointer; user-select: none;">' + sectionName + ' <span class="cts-section-toggle">▼</span></span>';
				sectionHeader.style.cssText = 'background: #f3f4f6; padding: 12px; font-size: 13px; font-weight: 600; text-align: left; border-top: 2px solid #e5e7eb; cursor: pointer;';
				sectionHeader.dataset.section = sectionName;
				
				// Toggle Funktion
				sectionHeader.addEventListener('click', function() {
					const section = this.dataset.section;
					const rows = presetParamsTable.querySelectorAll('[data-section="' + section + '"]');
					const toggle = this.querySelector('.cts-section-toggle');
					const isCollapsed = toggle.textContent === '▶';
					
					rows.forEach(row => {
						row.style.display = isCollapsed ? 'table-row' : 'none';
					});
					toggle.textContent = isCollapsed ? '▼' : '▶';
				});
				
				sectionRow.appendChild(sectionHeader);
				presetParamsTable.appendChild(sectionRow);
				
				// Section Parameters
				paramsBySection[sectionName].forEach(param => {
					const paramName = param.name;
					const paramConfig = param.config;
					const row = document.createElement('tr');
					row.dataset.section = sectionName;
					
					// Erste Section offen, Rest zu
					if (index > 0) {
						row.style.display = 'none';
						const toggle = sectionRow.querySelector('.cts-section-toggle');
						if (toggle) toggle.textContent = '▶';
					}
					
					const th = document.createElement('th');
				th.textContent = paramConfig.label || paramName;
				row.appendChild(th);
				
				const td = document.createElement('td');
				let input;
				
				if (paramConfig.type === 'toggle') {
					// Toggle Switch mit Ja/Nein
					const toggleContainer = document.createElement('div');
					toggleContainer.style.display = 'flex';
					toggleContainer.style.alignItems = 'center';
					toggleContainer.style.gap = '12px';
					
					// Hidden Input für den Wert
					input = document.createElement('input');
					input.type = 'hidden';
					input.dataset.paramName = paramName;
					input.value = paramConfig.default ? 'true' : 'false';
					
					// Toggle Label
					const toggleLabel = document.createElement('label');
					toggleLabel.className = 'cts-toggle';
					toggleLabel.style.position = 'relative';
					toggleLabel.style.display = 'inline-block';
					toggleLabel.style.width = '48px';
					toggleLabel.style.height = '24px';
					
					// Checkbox für Toggle
					const checkbox = document.createElement('input');
					checkbox.type = 'checkbox';
					checkbox.checked = paramConfig.default === true;
					checkbox.style.opacity = '0';
					checkbox.style.width = '0';
					checkbox.style.height = '0';
					
					// Toggle Slider
					const slider = document.createElement('span');
					slider.className = 'cts-toggle-slider';
					slider.style.cssText = `
						position: absolute;
						cursor: pointer;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						background-color: ${checkbox.checked ? '#667eea' : '#cbd5e1'};
						transition: 0.3s;
						border-radius: 24px;
					`;
					
					const sliderButton = document.createElement('span');
					sliderButton.style.cssText = `
						position: absolute;
						content: "";
						height: 18px;
						width: 18px;
						left: ${checkbox.checked ? '27px' : '3px'};
						bottom: 3px;
						background-color: white;
						transition: 0.3s;
						border-radius: 50%;
					`;
					slider.appendChild(sliderButton);
					
					// Text Label
					const textLabel = document.createElement('span');
					textLabel.textContent = checkbox.checked ? 'Ja' : 'Nein';
					textLabel.style.fontWeight = '600';
					textLabel.style.color = checkbox.checked ? '#667eea' : '#6b7280';
					
					// Change Event
					checkbox.addEventListener('change', function() {
						const isChecked = this.checked;
						input.value = isChecked ? 'true' : 'false';
						slider.style.backgroundColor = isChecked ? '#667eea' : '#cbd5e1';
						sliderButton.style.left = isChecked ? '27px' : '3px';
						textLabel.textContent = isChecked ? 'Ja' : 'Nein';
						textLabel.style.color = isChecked ? '#667eea' : '#6b7280';
						updatePreview();
					});
					
					toggleLabel.appendChild(checkbox);
					toggleLabel.appendChild(slider);
					
					toggleContainer.appendChild(toggleLabel);
					toggleContainer.appendChild(textLabel);
					toggleContainer.appendChild(input);
					
					td.appendChild(toggleContainer);
				} else if (paramConfig.type === 'select') {
					input = document.createElement('select');
					input.className = 'cts-form-select';
					
					// Add empty option
					const emptyOption = document.createElement('option');
					emptyOption.value = '';
					emptyOption.textContent = '-- Bitte wählen --';
					input.appendChild(emptyOption);
					
					// Add options (unterstützt sowohl Array als auch Objekt)
					if (Array.isArray(paramConfig.options)) {
						// Array: ["classic", "medium"]
						paramConfig.options.forEach(opt => {
							const option = document.createElement('option');
							option.value = opt;
							option.textContent = opt;
							if (paramConfig.default === opt) {
								option.selected = true;
							}
							input.appendChild(option);
						});
					} else {
						// Objekt: {"asc": "Aufsteigend", "desc": "Absteigend"}
						Object.keys(paramConfig.options).forEach(key => {
							const option = document.createElement('option');
							option.value = key;
							option.textContent = paramConfig.options[key];
							if (paramConfig.default === key) {
								option.selected = true;
							}
							input.appendChild(option);
						});
					}
				} else if (paramConfig.type === 'checkboxes') {
					// Checkboxes für Kalender-Auswahl
					const checkboxContainer = document.createElement('div');
					checkboxContainer.style.cssText = 'display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px;';
					checkboxContainer.innerHTML = '<div style="padding: 8px; text-align: center; color: #6b7280;"><span style="font-size: 16px;">⏳</span> Kalender werden geladen...</div>';
					
					td.appendChild(checkboxContainer);
					
					// Hidden input für kommagetrennte IDs
					input = document.createElement('input');
					input.type = 'hidden';
					input.name = 'param-' + paramName;
					input.dataset.paramName = paramName;
					input.value = '';
					td.appendChild(input);
					
					// Kalender via AJAX laden
					fetch(ajaxurl, {
						method: 'POST',
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						body: new URLSearchParams({
							action: 'cts_get_calendars',
							nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>'
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success && data.data.calendars) {
							const calendars = data.data.calendars;
							
							if (calendars.length === 0) {
								checkboxContainer.innerHTML = '<div style="padding: 8px; text-align: center; color: #d97706;"><span style="font-size: 16px;">⚠️</span> Keine Kalender verfügbar. Bitte zuerst im Tab "Kalender" synchronisieren.</div>';
								return;
							}
							
							// Render Checkboxen
							checkboxContainer.innerHTML = '';
							calendars.forEach(calendar => {
								const label = document.createElement('label');
								label.style.cssText = 'display: flex; align-items: center; gap: 8px; padding: 6px; border-radius: 4px; cursor: pointer; transition: background 0.2s;';
								label.onmouseover = () => label.style.backgroundColor = '#f9fafb';
								label.onmouseout = () => label.style.backgroundColor = 'transparent';
								
								const checkbox = document.createElement('input');
								checkbox.type = 'checkbox';
								checkbox.value = calendar.id;
								checkbox.style.cursor = 'pointer';
								
								const colorDot = document.createElement('span');
								colorDot.style.cssText = `width: 12px; height: 12px; border-radius: 50%; background: ${calendar.color}; flex-shrink: 0;`;
								
								const nameSpan = document.createElement('span');
								nameSpan.textContent = calendar.name;
								nameSpan.style.fontSize = '14px';
								
								// Update hidden input when checkbox changes
								checkbox.addEventListener('change', function() {
									const checkedBoxes = checkboxContainer.querySelectorAll('input[type=checkbox]:checked');
									const ids = Array.from(checkedBoxes).map(cb => cb.value);
									input.value = ids.join(',');
									updatePreview();
								});
								
								label.appendChild(checkbox);
								label.appendChild(colorDot);
								label.appendChild(nameSpan);
								checkboxContainer.appendChild(label);
							});
						} else {
							checkboxContainer.innerHTML = '<div style="padding: 8px; text-align: center; color: #ef4444;"><span style="font-size: 16px;">❌</span> Fehler beim Laden der Kalender</div>';
						}
					})
					.catch(error => {
						console.error('Kalender-Load-Fehler:', error);
						checkboxContainer.innerHTML = '<div style="padding: 8px; text-align: center; color: #ef4444;"><span style="font-size: 16px;">❌</span> Netzwerkfehler</div>';
					});
				} else if (paramConfig.type === 'number') {
					input = document.createElement('input');
					input.type = 'number';
					input.className = 'cts-form-input';
					if (paramConfig.default) {
						input.value = paramConfig.default;
					}
				} else if (paramConfig.type === 'date') {
					input = document.createElement('input');
					input.type = 'date';
					input.className = 'cts-form-input';
				} else {
					input = document.createElement('input');
					input.type = 'text';
					input.className = 'cts-form-input';
					if (paramConfig.default) {
						input.value = paramConfig.default;
					}
				}
				
				// Nur bei nicht-checkboxes: name, dataset und events setzen
				if (paramConfig.type !== 'checkboxes') {
					input.name = 'param-' + paramName;
					input.dataset.paramName = paramName;
					input.addEventListener('input', updatePreview);
					input.addEventListener('change', updatePreview);
					
					td.appendChild(input);
				}
				
				row.appendChild(td);
				
				presetParamsTable.appendChild(row);
				});
			});
			
			presetParamsContainer.style.display = 'block';
			presetPreview.style.display = 'block';
			saveButton.disabled = false;
			
			updatePreview();
		});
		
		// WICHTIG: Trigger initial load wenn bereits ein Wert ausgewählt ist
		// (z.B. beim Wechsel vom Edit-Modus zurück oder beim Tab-Wechsel)
		if (presetShortcodeTag.value) {
			presetShortcodeTag.dispatchEvent(new Event('change'));
		}
	}
	
	// Update preview
	// Save preset
	if (saveButton) {
		saveButton.addEventListener('click', function() {
			const presetId = document.getElementById('preset-id').value;
			const name = document.getElementById('preset-name').value;
			const description = document.getElementById('preset-description').value;
			const shortcodeTag = presetShortcodeTag.value;
			
			if (!name || !shortcodeTag) {
				alert('<?php esc_html_e( 'Bitte fülle alle Pflichtfelder aus', 'churchtools-suite' ); ?>');
				return;
			}
			
			// Collect configuration
			const configuration = {};
			const inputs = presetParamsTable.querySelectorAll('input, select');
			
			inputs.forEach(input => {
				const paramName = input.dataset.paramName;
				const value = input.value;
				
				if (value && value !== '') {
					configuration[paramName] = value;
				}
			});
			
			// Disable button
			saveButton.disabled = true;
			const savingText = presetId ? '⏳ Aktualisiert...' : '⏳ Speichert...';
			saveButton.querySelector('#cts-save-label').textContent = savingText;
			
			// Determine action
			const action = presetId ? 'cts_update_preset' : 'cts_save_preset';
			const params = {
				action: action,
				nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>',
				name: name,
				description: description,
				shortcode_tag: shortcodeTag,
				configuration: JSON.stringify(configuration)
			};
			
			if (presetId) {
				params.preset_id = presetId;
			}
			
			// AJAX save/update
			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams(params)
			})
			.then(response => response.json())
			.then(data => {
				const resultSpan = document.getElementById('cts-save-result');
				
				if (data.success) {
					resultSpan.innerHTML = '<span style="color: #00a32a;">✓ Preset gespeichert!</span>';
					
					// Reset form
					document.getElementById('cts-preset-form').reset();
					presetParamsContainer.style.display = 'none';
					presetPreview.style.display = 'none';
					
					// Reload page after 1.5s
					setTimeout(() => {
						location.reload();
					}, 1500);
				} else {
					resultSpan.innerHTML = '<span style="color: #d63638;">✗ Fehler: ' + (data.data ? data.data.message : 'Unbekannter Fehler') + '</span>';
					saveButton.disabled = false;
					const presetId = document.getElementById('preset-id').value;
					saveButton.querySelector('#cts-save-label').textContent = presetId ? '<?php esc_html_e( 'Preset aktualisieren', 'churchtools-suite' ); ?>' : '<?php esc_html_e( 'Preset speichern', 'churchtools-suite' ); ?>';
				}
			})
			.catch(error => {
				const resultSpan = document.getElementById('cts-save-result');
				resultSpan.innerHTML = '<span style="color: #d63638;">✗ Fehler: ' + error.message + '</span>';
				saveButton.disabled = false;
				const presetId = document.getElementById('preset-id').value;
				saveButton.querySelector('#cts-save-label').textContent = presetId ? '<?php esc_html_e( 'Preset aktualisieren', 'churchtools-suite' ); ?>' : '<?php esc_html_e( 'Preset speichern', 'churchtools-suite' ); ?>';
			});
		});
	}
	
	// Edit preset (KEIN automatischer Tab-Wechsel mehr)
	document.querySelectorAll('.cts-edit-preset').forEach(function(button) {
		button.addEventListener('click', function() {
			const presetData = JSON.parse(this.dataset.preset);
			
			// Wechsel NUR zum Tab, wenn User nicht schon dort ist
			const createTab = document.querySelector('[data-tab="create"]');
			if (!createTab.classList.contains('active')) {
				createTab.click();
			}
			
			// Change form title and button
			document.getElementById('cts-preset-form-title').textContent = '<?php esc_html_e( 'Preset bearbeiten', 'churchtools-suite' ); ?>';
			document.getElementById('cts-create-icon').textContent = '✏️';
			document.getElementById('cts-create-label').textContent = '<?php esc_html_e( 'Preset bearbeiten', 'churchtools-suite' ); ?>';
			
			// Fill form
			document.getElementById('preset-id').value = presetData.id;
			document.getElementById('preset-name').value = presetData.name;
			document.getElementById('preset-description').value = presetData.description || '';
			document.getElementById('preset-shortcode-tag').value = presetData.shortcode_tag;
			
			// Trigger change to load params (synchron)
			const changeEvent = new Event('change', { bubbles: true });
			document.getElementById('preset-shortcode-tag').dispatchEvent(changeEvent);
			
			// Fill values IMMEDIATELY after params are loaded
			// Use requestAnimationFrame to ensure DOM is updated
			requestAnimationFrame(() => {
				const config = presetData.configuration || {};
				Object.keys(config).forEach(function(key) {
					// If key is _base_view, fill into 'view' parameter
					const targetKey = (key === '_base_view') ? 'view' : key;
					
					const input = document.querySelector('[data-param-name="' + targetKey + '"]');
					if (input) {
						if (input.type === 'checkbox') {
							input.checked = (config[key] === 'true' || config[key] === true);
							// Update hidden input for toggles
							const hiddenInput = input.parentElement.parentElement.querySelector('input[type="hidden"]');
							if (hiddenInput) {
								hiddenInput.value = input.checked ? 'true' : 'false';
							}
						} else {
							input.value = config[key];
						}
					}
				});
				updatePreview();
			});
			
			// Change titles and button text
			document.getElementById('cts-preset-form-title').textContent = '<?php esc_html_e( 'Preset bearbeiten', 'churchtools-suite' ); ?>';
			document.getElementById('cts-save-icon').textContent = '💾';
			document.getElementById('cts-save-label').textContent = '<?php esc_html_e( 'Preset aktualisieren', 'churchtools-suite' ); ?>';
			
			// Show cancel button
			document.getElementById('cts-cancel-edit').style.display = 'inline-block';
		});
	});
	
	// Cancel edit - zurück zum Create-Modus
	const cancelButton = document.getElementById('cts-cancel-edit');
	if (cancelButton) {
		cancelButton.addEventListener('click', function() {
			resetPresetForm();
			// Optional: Scroll to top
			document.getElementById('cts-preset-form-title').scrollIntoView({ behavior: 'smooth' });
		});
	}
	
	// Delete preset
	document.querySelectorAll('.cts-delete-preset').forEach(function(button) {
		button.addEventListener('click', function() {
			if (!confirm('<?php esc_html_e( 'Möchtest du dieses Preset wirklich löschen?', 'churchtools-suite' ); ?>')) {
				return;
			}
			
			const presetId = this.dataset.presetId;
			const card = this.closest('.cts-preset-card');
			
			button.disabled = true;
			button.textContent = '⏳';
			
			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_delete_preset',
					nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>',
					preset_id: presetId
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					card.style.transition = 'opacity 0.3s, transform 0.3s';
					card.style.opacity = '0';
					card.style.transform = 'scale(0.9)';
					
					setTimeout(() => {
						card.remove();
						
						// Check if no more presets
						const remainingPresets = document.querySelectorAll('.cts-preset-card');
						if (remainingPresets.length === 0) {
							location.reload();
						}
					}, 300);
				} else {
					alert('Fehler beim Löschen: ' + (data.data ? data.data.message : 'Unbekannter Fehler'));
					button.disabled = false;
					button.textContent = '🗑️ <?php esc_html_e( 'Löschen', 'churchtools-suite' ); ?>';
				}
			})
			.catch(error => {
				alert('Fehler: ' + error.message);
				button.disabled = false;
				button.textContent = '🗑️ <?php esc_html_e( 'Löschen', 'churchtools-suite' ); ?>';
			});
		});
	});
	
})();
</script>
