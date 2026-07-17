<?php
/**
 * Shortcode Demo Page - Simplified Overview
 *
 * @package ChurchTools_Suite
 * @since   0.5.9.25
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get selected type
$selected_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';

// Available types (grouped logically: Main views → Dynamic views → Specialized views)
$demo_types = [
	// === Haupt-Ansichten ===
	'list' => [
		'icon' => '📋',
		'name' => 'List Views',
		'count' => 10,
		'description' => 'Classic, Modern, Minimal, mit Services'
	],
	'grid' => [
		'icon' => '▦',
		'name' => 'Grid Views',
		'count' => 14,
		'description' => 'Simple, Modern, Colorful, verschiedene Spalten'
	],
	'calendar' => [
		'icon' => '📅',
		'name' => 'Calendar Views',
		'count' => 8,
		'description' => 'Monatsansicht, Wochenansicht, Jahresansicht'
	],
	// === Dynamische Ansichten (NEU) ===
	'countdown' => [
		'icon' => '⏱️',
		'name' => 'Countdown Views',
		'count' => 3,
		'description' => 'Countdown bis zum nächsten Event'
	],
	'carousel' => [
		'icon' => '🎠',
		'name' => 'Carousel Views',
		'count' => 5,
		'description' => 'Karussell mit Swipe-Navigation'
	],
	'slider' => [
		'icon' => '🎞️',
		'name' => 'Slider Views',
		'count' => 5,
		'description' => 'Autoplay, verschiedene Stile'
	],
	// === Hero/Cover ===
	'cover' => [
		'icon' => '🎨',
		'name' => 'Cover Views',
		'count' => 5,
		'description' => 'Hero-Banner, große Teaserbilder'
	],
	// === Spezialisierte Ansichten ===
	'timetable' => [
		'icon' => '🗓️',
		'name' => 'Timetable Views',
		'count' => 3,
		'description' => 'Zeitplan, Timeline-Ansichten'
	],
	'widget' => [
		'icon' => '🎁',
		'name' => 'Widget Views',
		'count' => 3,
		'description' => 'Sidebar-Widgets, kleine Ansichten'
	],
	'search' => [
		'icon' => '🔍',
		'name' => 'Search Views',
		'count' => 2,
		'description' => 'Suchleiste, erweiterte Suche'
	],
	'map' => [
		'icon' => '🗺️',
		'name' => 'Map Views',
		'count' => 3,
		'description' => 'Kartenansichten mit Orten'
	]
];
?>

<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>🎯</span>
			<?php esc_html_e( 'Shortcode Demo', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle">
			<?php if ( $selected_type ) : ?>
				<?php echo esc_html( $demo_types[$selected_type]['icon'] ?? '' ); ?>
				<?php echo esc_html( $demo_types[$selected_type]['name'] ?? 'Live Demo' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Wähle einen Shortcode-Typ zum Testen', 'churchtools-suite' ); ?>
			<?php endif; ?>
		</p>
	</div>

	<div class="cts-tab-content">
		
		<?php if ( ! $selected_type ) : ?>
			<!-- Overview Page -->
			
			<!-- Quick Stats -->
			<div class="cts-grid cts-grid-3" style="margin-bottom: 30px;">
				<div class="cts-card">
					<div class="cts-card-body" style="text-align: center;">
						<div class="cts-stat-number">11</div>
						<p class="cts-card-detail"><?php esc_html_e( 'Shortcode-Typen', 'churchtools-suite' ); ?></p>
					</div>
				</div>
				<div class="cts-card">
					<div class="cts-card-body" style="text-align: center;">
						<div class="cts-stat-number">60+</div>
						<p class="cts-card-detail"><?php esc_html_e( 'View-Varianten', 'churchtools-suite' ); ?></p>
					</div>
				</div>
				<div class="cts-card">
					<div class="cts-card-body" style="text-align: center;">
						<div class="cts-stat-number">50+</div>
						<p class="cts-card-detail"><?php esc_html_e( 'Parameter', 'churchtools-suite' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Type Selection Grid -->
			<div class="cts-demo-type-grid">
				<?php foreach ( $demo_types as $type_key => $type_data ) : ?>
					<a href="?page=churchtools-suite-demo&type=<?php echo esc_attr( $type_key ); ?>" class="cts-demo-type-card">
						<div class="cts-demo-type-icon"><?php echo esc_html( $type_data['icon'] ); ?></div>
						<h3><?php echo esc_html( $type_data['name'] ); ?></h3>
						<p class="cts-demo-type-count"><?php echo esc_html( $type_data['count'] ); ?> Varianten</p>
						<p class="cts-demo-type-desc"><?php echo esc_html( $type_data['description'] ); ?></p>
						<span class="cts-demo-type-arrow">→</span>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Quick Reference -->
			<div class="cts-card" style="max-width: 900px; margin: 40px auto 0;">
				<div class="cts-card-header">
					<span class="cts-card-icon">💡</span>
					<h3><?php esc_html_e( 'Schnellstart', 'churchtools-suite' ); ?></h3>
				</div>
				<div class="cts-card-body">
					<ol style="margin: 0; padding-left: 20px;">
						<li style="margin-bottom: 12px;">
							<strong><?php esc_html_e( 'Typ auswählen', 'churchtools-suite' ); ?></strong> - 
							<?php esc_html_e( 'Klicke auf eine der Karten oben', 'churchtools-suite' ); ?>
						</li>
						<li style="margin-bottom: 12px;">
							<strong><?php esc_html_e( 'Live-Demo ansehen', 'churchtools-suite' ); ?></strong> - 
							<?php esc_html_e( 'Alle Varianten werden mit echten Daten gerendert', 'churchtools-suite' ); ?>
						</li>
						<li style="margin-bottom: 12px;">
							<strong><?php esc_html_e( 'Shortcode kopieren', 'churchtools-suite' ); ?></strong> - 
							<?php esc_html_e( 'Verwende den Code in deinen Seiten oder Beiträgen', 'churchtools-suite' ); ?>
						</li>
					</ol>
				</div>
			</div>

		<?php else : ?>
			<!-- Detail Page for specific type -->
			
			<!-- Back Button -->
			<div style="margin-bottom: 20px;">
				<a href="?page=churchtools-suite-demo" class="button">
					<span class="dashicons dashicons-arrow-left-alt2" style="margin-top: 3px;"></span>
					<?php esc_html_e( 'Zurück zur Übersicht', 'churchtools-suite' ); ?>
				</a>
			</div>

			<!-- Load specific demo content -->
			<?php
			$demo_file = CHURCHTOOLS_SUITE_PATH . "admin/views/demos/demo-{$selected_type}.php";
			if ( file_exists( $demo_file ) ) {
				include $demo_file;
			} else {
				echo '<div class="cts-card"><div class="cts-card-body">';
				echo '<p>' . esc_html__( 'Demo für diesen Typ wird noch erstellt...', 'churchtools-suite' ) . '</p>';
				echo '</div></div>';
			}
			?>

		<?php endif; ?>

	</div>

</div>

<style>
/* Demo Type Grid */
.cts-demo-type-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 20px;
	margin-bottom: 40px;
}

.cts-demo-type-card {
	display: flex;
	flex-direction: column;
	padding: 24px;
	background: #fff;
	border: 2px solid #e5e7eb;
	border-radius: 8px;
	text-decoration: none;
	transition: all 0.2s;
	position: relative;
	overflow: hidden;
}

.cts-demo-type-card:hover {
	border-color: #667eea;
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
	transform: translateY(-2px);
}

.cts-demo-type-icon {
	font-size: 48px;
	line-height: 1;
	margin-bottom: 16px;
}

.cts-demo-type-card h3 {
	margin: 0 0 8px;
	font-size: 18px;
	font-weight: 600;
	color: #1d2327;
}

.cts-demo-type-count {
	margin: 0 0 8px;
	font-size: 13px;
	font-weight: 600;
	color: #667eea;
}

.cts-demo-type-desc {
	margin: 0 0 16px;
	font-size: 13px;
	color: #646970;
	line-height: 1.5;
	flex: 1;
}

.cts-demo-type-arrow {
	font-size: 20px;
	color: #667eea;
	font-weight: 600;
	transition: transform 0.2s;
}

.cts-demo-type-card:hover .cts-demo-type-arrow {
	transform: translateX(4px);
}

/* Demo Item */
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

/* Responsive */
@media (max-width: 768px) {
	.cts-demo-type-grid {
		grid-template-columns: 1fr;
	}
	
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
