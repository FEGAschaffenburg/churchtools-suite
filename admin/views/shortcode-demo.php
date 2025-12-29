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

// Available types
$demo_types = [
	'list' => [
		'icon' => '📋',
		'name' => 'List Views',
		'count' => 4,
		'status' => 'ready',
		'description' => '✓ Classic, Medium, Classic-Services | ⏳ Fluent'
	],
	'calendar' => [
		'icon' => '📅',
		'name' => 'Calendar Views',
		'count' => 5,
		'status' => 'ready',
		'description' => '✓ Monthly Modern | ⏳ Monthly Clean, Weekly, Yearly, Daily'
	],
	'grid' => [
		'icon' => '▦',
		'name' => 'Grid Views',
		'count' => 3,
		'status' => 'ready',
		'description' => '✓ Simple | ⏳ Modern, Colorful'
	],
	'slider' => [
		'icon' => '🎠',
		'name' => 'Slider Views',
		'count' => 5,
		'status' => 'planned',
		'description' => 'Autoplay, verschiedene Stile'
	],
	'countdown' => [
		'icon' => '⏱️',
		'name' => 'Countdown Views',
		'count' => 3,
		'status' => 'planned',
		'description' => 'Countdown bis zum nächsten Event'
	],
	'cover' => [
		'icon' => '🎨',
		'name' => 'Cover Views',
		'count' => 5,
		'status' => 'planned',
		'description' => 'Hero-Banner, große Teaserbilder'
	],
	'timetable' => [
		'icon' => '🗓️',
		'name' => 'Timetable Views',
		'count' => 3,
		'status' => 'planned',
		'description' => 'Zeitplan, Timeline-Ansichten'
	],
	'carousel' => [
		'icon' => '🎡',
		'name' => 'Carousel Views',
		'count' => 4,
		'status' => 'planned',
		'description' => 'Karussell mit Navigation'
	],
	'widget' => [
		'icon' => '🎁',
		'name' => 'Widget Views',
		'count' => 3,
		'status' => 'planned',
		'description' => 'Sidebar-Widgets, kleine Ansichten'
	],
	'search' => [
		'icon' => '🔍',
		'name' => 'Search Views',
		'count' => 2,
		'status' => 'planned',
		'description' => 'Suchleiste, erweiterte Suche'
	],
	'map' => [
		'icon' => '🗺️',
		'name' => 'Map Views',
		'count' => 3,
		'status' => 'planned',
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
						<div class="cts-stat-number" style="color: #10b981;">3/11</div>
						<p class="cts-card-detail"><?php esc_html_e( 'Shortcode-Typen verfügbar', 'churchtools-suite' ); ?></p>
					</div>
				</div>
				<div class="cts-card">
					<div class="cts-card-body" style="text-align: center;">
						<div class="cts-stat-number" style="color: #10b981;">5<span style="color: #3b82f6;">/12</span></div>
						<p class="cts-card-detail"><?php esc_html_e( 'View-Varianten fertig/geplant', 'churchtools-suite' ); ?></p>
					</div>
				</div>
				<div class="cts-card">
					<div class="cts-card-body" style="text-align: center;">
						<div class="cts-stat-number" style="color: #667eea;">v0.6.1</div>
						<p class="cts-card-detail"><?php esc_html_e( 'Aktuelle Version', 'churchtools-suite' ); ?></p>
					</div>
				</div>
			</div>
			
			<!-- Status Legend -->
			<div class="cts-card" style="margin-bottom: 30px;">
				<div class="cts-card-body">
					<div style="display: flex; gap: 24px; flex-wrap: wrap; justify-content: center;">
						<div style="display: flex; align-items: center; gap: 8px;">
							<span class="cts-status-badge cts-status-ready">✓ Verfügbar</span>
							<span style="color: #646970; font-size: 13px;">5 Views (List, Calendar, Grid)</span>
						</div>
						<div style="display: flex; align-items: center; gap: 8px;">
							<span class="cts-status-badge cts-status-planned">⏳ In Entwicklung</span>
							<span style="color: #646970; font-size: 13px;">7 Views + 8 neue Typen</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Type Selection Grid -->
			<div class="cts-demo-type-grid">
				<?php foreach ( $demo_types as $type_key => $type_data ) : ?>
					<?php 
					$is_ready = ( $type_data['status'] === 'ready' );
					$card_class = $is_ready ? 'cts-demo-type-card' : 'cts-demo-type-card cts-demo-type-card-disabled';
					?>
					<a href="<?php echo $is_ready ? '?page=churchtools-suite-demo&type=' . esc_attr( $type_key ) : '#'; ?>" 
					   class="<?php echo esc_attr( $card_class ); ?>"
					   <?php echo ! $is_ready ? 'onclick="return false;"' : ''; ?>>
						<div class="cts-demo-type-icon"><?php echo esc_html( $type_data['icon'] ); ?></div>
						<?php if ( ! $is_ready ) : ?>
							<span class="cts-status-badge cts-status-planned">⏳ Geplant</span>
						<?php else : ?>
							<span class="cts-status-badge cts-status-ready">✓ Verfügbar</span>
						<?php endif; ?>
						<h3><?php echo esc_html( $type_data['name'] ); ?></h3>
						<p class="cts-demo-type-count"><?php echo esc_html( $type_data['count'] ); ?> Varianten</p>
						<p class="cts-demo-type-desc"><?php echo esc_html( $type_data['description'] ); ?></p>
						<?php if ( $is_ready ) : ?>
							<span class="cts-demo-type-arrow">→</span>
						<?php endif; ?>
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

.cts-demo-type-card-disabled {
	opacity: 0.6;
	cursor: not-allowed;
	background: #f9fafb;
}

.cts-demo-type-card-disabled:hover {
	border-color: #e5e7eb;
	box-shadow: none;
	transform: none;
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

/* Status Badges */
.cts-status-badge {
	display: inline-block;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	margin-bottom: 12px;
}

.cts-status-ready {
	background: #d1fae5;
	color: #065f46;
}

.cts-status-planned {
	background: #dbeafe;
	color: #1e40af;
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
