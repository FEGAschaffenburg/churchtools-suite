<?php
/**
 * ChurchTools Suite - Block Debug Page
 * 
 * Zeigt ob Blocks funktionieren und ob Events vorhanden sind
 * 
 * Verwendung: Erstelle eine Seite mit diesem Shortcode: [cts_debug_blocks]
 * 
 * @package ChurchTools_Suite
 * @since   0.5.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Debug-Shortcode registrieren
add_shortcode( 'cts_debug_blocks', 'churchtools_suite_debug_blocks' );

function churchtools_suite_debug_blocks() {
	ob_start();
	
	global $wpdb;
	$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
	
	?>
	<div style="font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9fafb; border-radius: 8px;">
		
		<h2 style="color: #1f2937; margin: 0 0 20px;">🔧 ChurchTools Suite - Block Debug</h2>
		
		<!-- 1. Plugin Status -->
		<div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
			<h3 style="margin: 0 0 12px; color: #667eea;">1️⃣ Plugin Status</h3>
			<table style="width: 100%; border-collapse: collapse;">
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Version:</td>
					<td style="padding: 8px 0; color: #1f2937;"><?php echo CHURCHTOOLS_SUITE_VERSION; ?></td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">WordPress:</td>
					<td style="padding: 8px 0; color: #1f2937;"><?php echo get_bloginfo('version'); ?></td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">PHP:</td>
					<td style="padding: 8px 0; color: #1f2937;"><?php echo PHP_VERSION; ?></td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Gutenberg aktiv:</td>
					<td style="padding: 8px 0;">
						<?php if (function_exists('register_block_type')): ?>
							<span style="color: #10b981; font-weight: 600;">✓ Ja</span>
						<?php else: ?>
							<span style="color: #ef4444; font-weight: 600;">✗ Nein</span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		
		<!-- 2. Datenbank Status -->
		<div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #8b5cf6;">
			<h3 style="margin: 0 0 12px; color: #8b5cf6;">2️⃣ Datenbank Status</h3>
			<?php
			$calendars = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}calendars");
			$events = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}events");
			$services = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}event_services");
			$selected_calendars = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}calendars WHERE is_selected = 1");
			?>
			<table style="width: 100%; border-collapse: collapse;">
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Kalender (gesamt):</td>
					<td style="padding: 8px 0; color: #1f2937; font-weight: 600;"><?php echo $calendars; ?></td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Kalender (ausgewählt):</td>
					<td style="padding: 8px 0;">
						<span style="color: <?php echo $selected_calendars > 0 ? '#10b981' : '#ef4444'; ?>; font-weight: 600;">
							<?php echo $selected_calendars; ?> <?php echo $selected_calendars > 0 ? '✓' : '✗ Keine Kalender ausgewählt!'; ?>
						</span>
					</td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Events:</td>
					<td style="padding: 8px 0;">
						<span style="color: <?php echo $events > 0 ? '#10b981' : '#ef4444'; ?>; font-weight: 600;">
							<?php echo $events; ?> <?php echo $events > 0 ? '✓' : '✗ Keine Events!'; ?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Services:</td>
					<td style="padding: 8px 0; color: #1f2937; font-weight: 600;"><?php echo $services; ?></td>
				</tr>
			</table>
			
			<?php if ($events == 0): ?>
			<div style="margin-top: 12px; padding: 12px; background: #fef2f2; border-radius: 4px; border: 1px solid #fca5a5;">
				<p style="margin: 0; color: #991b1b; font-weight: 500;">
					⚠️ Keine Events in der Datenbank!
				</p>
				<p style="margin: 8px 0 0; color: #7f1d1d; font-size: 14px;">
					Bitte synchronisiere Events unter: <strong>ChurchTools → Sync</strong>
				</p>
			</div>
			<?php endif; ?>
		</div>
		
		<!-- 3. Blocks Status -->
		<div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
			<h3 style="margin: 0 0 12px; color: #10b981;">3️⃣ Gutenberg Blocks</h3>
			<table style="width: 100%; border-collapse: collapse;">
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Blocks-Klasse geladen:</td>
					<td style="padding: 8px 0;">
						<?php if (class_exists('ChurchTools_Suite_Blocks')): ?>
							<span style="color: #10b981; font-weight: 600;">✓ Ja</span>
						<?php else: ?>
							<span style="color: #ef4444; font-weight: 600;">✗ Nein</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr style="border-bottom: 1px solid #e5e7eb;">
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Shortcodes-Klasse geladen:</td>
					<td style="padding: 8px 0;">
						<?php if (class_exists('ChurchTools_Suite_Shortcodes')): ?>
							<span style="color: #10b981; font-weight: 600;">✓ Ja</span>
						<?php else: ?>
							<span style="color: #ef4444; font-weight: 600;">✗ Nein</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="padding: 8px 0; font-weight: 600; color: #6b7280;">Template Loader geladen:</td>
					<td style="padding: 8px 0;">
						<?php if (class_exists('ChurchTools_Suite_Template_Loader')): ?>
							<span style="color: #10b981; font-weight: 600;">✓ Ja</span>
						<?php else: ?>
							<span style="color: #ef4444; font-weight: 600;">✗ Nein</span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>
		
		<!-- 4. Test Shortcode -->
		<div style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
			<h3 style="margin: 0 0 12px; color: #f59e0b;">4️⃣ Shortcode Test</h3>
			<p style="margin: 0 0 12px; color: #6b7280;">
				Wenn die Shortcodes funktionieren, solltest du unten Events sehen:
			</p>
			<div style="padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px;">
				<strong style="color: #1f2937;">Test: [cts_list view="classic" limit="5"]</strong>
			</div>
			<div style="margin-top: 12px; padding: 20px; background: #fefce8; border-radius: 4px;">
				<?php 
				if ($events > 0) {
					echo do_shortcode('[cts_list view="classic" limit="5"]'); 
				} else {
					echo '<p style="color: #92400e; margin: 0;">⚠️ Keine Events zum Anzeigen. Bitte synchronisiere zuerst Events.</p>';
				}
				?>
			</div>
		</div>
		
		<!-- 5. Nächste Schritte -->
		<div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
			<h3 style="margin: 0 0 12px; color: #3b82f6;">5️⃣ Nächste Schritte</h3>
			<ol style="margin: 0; padding-left: 20px; color: #6b7280;">
				<?php if ($selected_calendars == 0): ?>
				<li style="margin: 8px 0; color: #ef4444; font-weight: 600;">
					❌ Kalender auswählen unter: <strong>ChurchTools → Kalender</strong>
				</li>
				<?php else: ?>
				<li style="margin: 8px 0; color: #10b981;">
					✓ Kalender ausgewählt
				</li>
				<?php endif; ?>
				
				<?php if ($events == 0): ?>
				<li style="margin: 8px 0; color: #ef4444; font-weight: 600;">
					❌ Events synchronisieren unter: <strong>ChurchTools → Sync</strong>
				</li>
				<?php else: ?>
				<li style="margin: 8px 0; color: #10b981;">
					✓ Events synchronisiert
				</li>
				<?php endif; ?>
				
				<li style="margin: 8px 0; color: #6b7280;">
					Im Gutenberg-Editor <strong>+ klicken</strong> und nach <strong>"ChurchTools"</strong> suchen
				</li>
				<li style="margin: 8px 0; color: #6b7280;">
					Block einfügen und <strong>Einstellungen in der Sidebar</strong> anpassen
				</li>
				<li style="margin: 8px 0; color: #6b7280;">
					Seite <strong>speichern</strong> und im Frontend ansehen
				</li>
			</ol>
		</div>
		
	</div>
	<?php
	
	return ob_get_clean();
}
