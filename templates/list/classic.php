<?php
/**
 * List View - Classic
 *
 * Kompakte einzeilige Liste - alles in einer schmalen Zeile
 *
 * @package ChurchTools_Suite
 * @since   0.6.3.12
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Sprint 3: Parse boolean parameters (support strings from Gutenberg attributes)
$show_event_description = isset( $args['show_event_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;

// === DEBUG OUTPUT (v0.10.4.39 - ENHANCED) ===
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	echo '<div style="background: #fff3cd; border: 3px solid #ffc107; padding: 20px; margin: 20px 0; font-family: monospace; font-size: 13px; border-radius: 8px;">';
	echo '<h2 style="margin-top:0; color: #856404;">🔍 ChurchTools Suite DEBUG - list/classic.php</h2>';
	
	// CRITICAL: Version Check
	$current_version = defined( 'CHURCHTOOLS_SUITE_VERSION' ) ? CHURCHTOOLS_SUITE_VERSION : 'UNKNOWN';
	$is_correct_version = version_compare( $current_version, '0.10.4.39', '>=' );
	$bg_color = $is_correct_version ? '#d4edda' : '#f8d7da';
	$border_color = $is_correct_version ? '#28a745' : '#dc3545';
	$text_color = $is_correct_version ? '#155724' : '#721c24';
	
	echo '<div style="background: ' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 12px; margin-bottom: 15px; border-radius: 4px;">';
	echo '<strong style="font-size: 16px; color: ' . $text_color . ';">Plugin Version: ' . esc_html( $current_version ) . '</strong>';
	if ( ! $is_correct_version ) {
		echo '<p style="margin: 8px 0 0 0; color: ' . $text_color . '; font-weight: bold;">❌ ALTE VERSION! Bitte v0.10.4.39 hochladen!</p>';
		echo '<p style="margin: 4px 0 0 0; font-size: 11px;">WordPress Cache leeren + Plugin neu installieren!</p>';
	} else {
		echo '<p style="margin: 8px 0 0 0; color: ' . $text_color . '; font-weight: bold;">✅ Korrekte Version installiert</p>';
	}
	echo '</div>';
	
	// CRITICAL: show_description Detection
	if ( isset( $args['show_description'] ) ) {
		echo '<div style="background: #f8d7da; border: 2px solid #dc3545; padding: 12px; margin-bottom: 15px; border-radius: 4px;">';
		echo '<strong style="color: #721c24;">⚠️ WARNUNG: show_description gefunden!</strong><br>';
		echo '<span style="font-size: 11px;">Das ist das ALTE Attribut! Sollte NICHT mehr verwendet werden.<br>';
		echo 'Gutenberg Block sendet falsche Parameter ODER normalize_block_attributes() Bug ist noch aktiv!</span>';
		echo '</div>';
	}
	
	echo '<h3 style="color: #856404;">1. Übergebene Argumente ($args):</h3>';
	echo '<pre style="background: #f8f9fa; padding: 12px; overflow-x: auto; border-radius: 4px; max-height: 200px;">';
	print_r( $args );
	echo '</pre>';
	
	echo '<h3 style="color: #856404;">2. Geparste Toggle-Werte:</h3>';
	echo '<table style="width:100%; border-collapse: collapse; background: white;">';
	echo '<thead><tr style="background: #e9ecef;"><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Parameter</th><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Wert</th><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Raw Value</th><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Typ</th></tr></thead><tbody>';
	
	$toggles = [
		'show_event_description' => [ 'parsed' => $show_event_description, 'raw' => $args['show_event_description'] ?? 'NOT SET' ],
		'show_appointment_description' => [ 'parsed' => $show_appointment_description, 'raw' => $args['show_appointment_description'] ?? 'NOT SET' ],
		'show_services' => [ 'parsed' => $show_services, 'raw' => $args['show_services'] ?? 'NOT SET' ],
		'show_location' => [ 'parsed' => $show_location, 'raw' => $args['show_location'] ?? 'NOT SET' ],
		'show_calendar_name' => [ 'parsed' => $show_calendar_name, 'raw' => $args['show_calendar_name'] ?? 'NOT SET' ],
		'show_time' => [ 'parsed' => $show_time, 'raw' => $args['show_time'] ?? 'NOT SET' ],
		'show_tags' => [ 'parsed' => $show_tags, 'raw' => $args['show_tags'] ?? 'NOT SET' ],
	];
	
	foreach ( $toggles as $name => $data ) {
		$parsed = $data['parsed'];
		$raw = is_bool( $data['raw'] ) ? ( $data['raw'] ? 'true (bool)' : 'false (bool)' ) : var_export( $data['raw'], true );
		$display_value = $parsed ? '<strong style="color: #28a745;">✅ TRUE</strong>' : '<strong style="color: #dc3545;">❌ FALSE</strong>';
		$type = gettype( $parsed );
		$bg_color = $parsed ? '#d4edda' : '#f8d7da';
		echo '<tr style="background: ' . $bg_color . ';">';
		echo '<td style="padding: 8px; border: 1px solid #dee2e6;"><code>' . esc_html( $name ) . '</code></td>';
		echo '<td style="padding: 8px; border: 1px solid #dee2e6;">' . $display_value . '</td>';
		echo '<td style="padding: 8px; border: 1px solid #dee2e6;"><code>' . esc_html( $raw ) . '</code></td>';
		echo '<td style="padding: 8px; border: 1px solid #dee2e6;">' . esc_html( $type ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	
	echo '<h3 style="color: #856404;">3. Events Data Check:</h3>';
	echo '<p><strong>Events gefunden:</strong> ' . count( $events ) . '</p>';
	if ( ! empty( $events ) ) {
		$first = $events[0];
		echo '<table style="width:100%; border-collapse: collapse; background: white;">';
		echo '<tr style="background: #e9ecef;"><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Feld</th><th style="text-align:left; padding: 8px; border: 1px solid #dee2e6;">Wert</th></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Titel</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . esc_html( $first['title'] ?? 'LEER' ) . '</td></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Event-Beschreibung</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . ( ! empty( $first['event_description'] ) ? '<span style="color: green;">✅ ' . esc_html( substr( $first['event_description'], 0, 80 ) ) . '...</span>' : '<span style="color: red;">❌ LEER</span>' ) . '</td></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Termin-Beschreibung</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . ( ! empty( $first['appointment_description'] ) ? '<span style="color: green;">✅ ' . esc_html( substr( $first['appointment_description'], 0, 80 ) ) . '...</span>' : '<span style="color: red;">❌ LEER</span>' ) . '</td></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Services</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . ( ! empty( $first['services'] ) ? '<span style="color: green;">✅ ' . count( $first['services'] ) . ' gefunden</span>' : '<span style="color: red;">❌ LEER</span>' ) . '</td></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Ort (location_name)</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . esc_html( $first['location_name'] ?? 'LEER' ) . '</td></tr>';
		echo '<tr><td style="padding: 8px; border: 1px solid #dee2e6;">Tags</td><td style="padding: 8px; border: 1px solid #dee2e6;">' . ( ! empty( $first['tags_array'] ) ? '<span style="color: green;">✅ ' . count( $first['tags_array'] ) . ' gefunden</span>' : '<span style="color: red;">❌ LEER</span>' ) . '</td></tr>';
		echo '</table>';
	}
	
	echo '<p style="margin-top: 20px; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;"><strong>💡 Erwartetes Verhalten:</strong><br>';
	echo '• Wenn Toggle TRUE → Inhalt wird angezeigt<br>';
	echo '• Wenn Toggle FALSE → Inhalt wird NICHT angezeigt<br>';
	echo '• Überprüfe ob Raw Values von Gutenberg/Elementor korrekt übermittelt werden!</p>';
	echo '</div>';
}
?>

<div class="churchtools-suite-wrapper">
<div class="cts-list cts-list-classic" data-view="list-classic">
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-list-empty">
			<span class="cts-empty-icon">📅</span>
			<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<?php foreach ( $events as $event ) : ?>
			<?php $enable_modal = $args['enable_modal'] ?? true; ?>
<div class="cts-event-classic <?php echo $enable_modal ? 'cts-event-clickable' : ''; ?>" <?php if ( $enable_modal ) : ?>data-event-id="<?php echo esc_attr( $event['id'] ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) ); ?>"<?php endif; ?>>
				<!-- Datum (Text) -->
				<div class="cts-date">
					<?php echo esc_html( $event['start_day'] . '. ' . $event['start_month'] ); ?>
				</div>
				
				<!-- Uhrzeit (Von-Bis) -->
				<?php if ( $show_time ) : ?>
					<div class="cts-time">
						<?php echo esc_html( $event['start_time'] ); ?>
						<?php if ( ! empty( $event['end_time'] ) ) : ?>
							- <?php echo esc_html( $event['end_time'] ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<!-- Kalender-Name -->
				<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
					<div class="cts-calendar-name">
						<?php echo esc_html( $event['calendar_name'] ); ?>
					</div>
				<?php endif; ?>
				
				<!-- Titel & Description -->
				<div class="cts-title-block">
					<span class="cts-title"><?php echo esc_html( $event['title'] ); ?></span>
					<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
						<span class="cts-description"> - <?php echo esc_html( wp_trim_words( $event['event_description'], 15 ) ); ?></span>
					<?php endif; ?>
					<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
						<span class="cts-description"> - <?php echo esc_html( wp_trim_words( $event['appointment_description'], 15 ) ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
					<div class="cts-services">
						<?php 
						$service_items = array();
						
						foreach ( array_slice( $event['services'], 0, 2 ) as $s ) {
							if ( ! empty( $s['person_name'] ) ) {
								$service_items[] = $s['service_name'] . ': ' . $s['person_name'];
							} else {
								$service_items[] = $s['service_name'];
							}
						}
						
						echo esc_html( implode( ' | ', $service_items ) );
						
						if ( count( $event['services'] ) > 2 ) {
							echo ' <span class="cts-more">+' . ( count( $event['services'] ) - 2 ) . '</span>';
						}
						?>
					</div>
				<?php endif; ?>
			
			<!-- Ort -->
			<?php if ( $show_location && ( ! empty( $event['address_name'] ) || ! empty( $event['location_name'] ) || ! empty( $event['address_street'] ) ) ) : ?>
				<div class="cts-list-location">
					<span class="dashicons dashicons-location"></span>
					<?php
					if ( ! empty( $event['address_name'] ) ) {
						echo esc_html( $event['address_name'] );
					} elseif ( ! empty( $event['location_name'] ) ) {
						echo esc_html( $event['location_name'] );
					} else {
						echo esc_html( $event['address_street'] ?? '' );
					}

					$info_parts = array_filter( [ $event['address_street'] ?? '', $event['address_zip'] ?? '', $event['address_city'] ?? '' ] );
					if ( ! empty( $info_parts ) ) {
						$info_text = implode( ', ', $info_parts );
						?> <span class="cts-info-popup" title="<?php echo esc_attr( $info_text ); ?>"> ⓘ</span><?php
					}
					?>
				</div>
			<?php endif; ?>
		
			<!-- Tags (v0.10.4.11) -->
			<?php if ( $show_tags && ! empty( $event['tags_array'] ) ) : ?>
				<div class="cts-list-tags">
					<?php foreach ( $event['tags_array'] as $tag ) : ?>
						<span class="cts-tag-badge" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
							<?php echo esc_html( $tag['name'] ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<?php endforeach; ?>
		
	<?php endif; ?>
	
</div>
</div>
