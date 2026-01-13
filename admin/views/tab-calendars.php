<?php
/**
 * Tab: Calendars
 *
 * @package ChurchTools_Suite
 * @since   0.3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Load calendars from database
global $wpdb;
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

$calendars_repo = new ChurchTools_Suite_Calendars_Repository();
$calendars = $calendars_repo->get_all();
$selected_count = $calendars_repo->count_selected();
$last_sync = get_option('churchtools_suite_calendars_last_sync', null);
// v0.9.9.58: Read calendar_image_id from table, fallback to option for backward compatibility
$calendar_images_option = get_option('churchtools_suite_calendar_images', []);
?>

   <!-- Kalender Sync Button -->
   <div class="cts-card">
	   <div class="cts-card-header">
		   <span class="cts-card-icon">🗓️</span>
		   <h3><?php esc_html_e('Kalender synchronisieren', 'churchtools-suite'); ?></h3>
	   </div>
	   <div class="cts-card-body">
		   <p class="description">
			   <?php esc_html_e('Lädt die Kalenderliste aus ChurchTools und aktualisiert die verfügbaren Kalender in der Datenbank.', 'churchtools-suite'); ?>
		   </p>
		   
		   <?php if ($last_sync): ?>
		   <p class="cts-info">
			   <strong><?php esc_html_e('Letzte Synchronisation:', 'churchtools-suite'); ?></strong>
			   <?php echo esc_html(get_date_from_gmt($last_sync, get_option('date_format') . ' ' . get_option('time_format'))); ?>
		   </p>
		   <?php endif; ?>
		   
		   <div class="cts-button-group">
			   <button type="button" id="cts-sync-calendars-btn" class="cts-button cts-button-primary">
				   <span class="dashicons dashicons-update"></span>
				   <?php esc_html_e('Kalender jetzt synchronisieren', 'churchtools-suite'); ?>
			   </button>
		   </div>
		   
		   <div id="cts-sync-calendars-result" class="cts-mt-15"></div>
	   </div>
   </div>

   <!-- Calendar Selection Card -->
   <div class="cts-card cts-mt-20">
	   <div class="cts-card-header">
		   <span class="cts-card-icon">✅</span>
		   <h3><?php esc_html_e('Kalenderauswahl', 'churchtools-suite'); ?></h3>
	   </div>
		</div>

		<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			var btn = document.getElementById('cts-sync-calendars-btn');
			var result = document.getElementById('cts-sync-calendars-result');
			if (!btn) return;
			btn.addEventListener('click', function() {
				if (!confirm('<?php echo esc_js(__('Kalender jetzt mit ChurchTools synchronisieren?', 'churchtools-suite')); ?>')) return;
				btn.disabled = true;
				var orig = btn.innerHTML;
				btn.innerHTML = '⏳ <?php echo esc_js(__('Synchronisiere...', 'churchtools-suite')); ?>';
				if (result) result.innerHTML = '';
				fetch(churchtoolsSuite.ajaxUrl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams({ action: 'cts_sync_calendars', nonce: churchtoolsSuite.nonce })
			}).then(function(r) {
				// Prüfe ob Response OK ist und JSON enthält
				if (!r.ok) {
					throw new Error('Server-Fehler: ' + r.status);
				}
				const contentType = r.headers.get('content-type');
				if (!contentType || !contentType.includes('application/json')) {
					return r.text().then(text => {
						console.error('Non-JSON Response:', text.substring(0, 500));
						throw new Error('Server hat keine gültige JSON-Antwort gesendet (möglicherweise PHP-Fehler)');
					});
				}
				return r.json();
			}).then(function(data) {
				if (data.success) {
					if (result) result.innerHTML = '<span style="color:#0a0">' + (data.data && data.data.message ? data.data.message : '✅ Synchronisation abgeschlossen') + '</span>';
					// Seite neu laden nach erfolgreicher Sync
					setTimeout(function() { window.location.reload(); }, 1500);
				} else {
					if (result) result.innerHTML = '<span style="color:#d63638">' + (data.data && data.data.message ? data.data.message : (data.message || 'Fehler beim Sync')) + '</span>';
				}
			}).catch(function(err) {
				if (result) result.innerHTML = '<span style="color:#d63638">❌ ' + err.message + '</span>';
				}).finally(function() {
					btn.disabled = false;
					btn.innerHTML = orig;
				});
			});
		});
		</script>
		<div class="cts-card-body">
			
			<?php if (empty($calendars)): ?>
				<div class="notice notice-info inline">
					<p>
						<?php esc_html_e('Keine Kalender vorhanden. Bitte synchronisieren Sie zuerst die Kalender von ChurchTools.', 'churchtools-suite'); ?>
					</p>
				</div>
			<?php else: ?>
				
				<p class="description">
					<?php
					printf(
						esc_html__('Wählen Sie die Kalender aus, deren Termine synchronisiert werden sollen. Aktuell ausgewählt: %d von %d', 'churchtools-suite'),
						(int) $selected_count,
						count($calendars)
					);
					?>
				</p>
				
				<form method="post" id="cts-calendar-selection-form">
					<?php wp_nonce_field('cts_calendar_selection', 'cts_calendar_selection_nonce'); ?>
					
					<table class="widefat" style="margin-top: 15px;">
						<thead>
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="cts-select-all-calendars">
								</th>
								<th><?php esc_html_e('Kalender', 'churchtools-suite'); ?></th>
								<th><?php esc_html_e('ChurchTools-ID', 'churchtools-suite'); ?></th>
								<th><?php esc_html_e('Sichtbarkeit', 'churchtools-suite'); ?></th>
								<th><?php esc_html_e('Farbe', 'churchtools-suite'); ?></th>
								<th><?php esc_html_e('Fallback-Bild', 'churchtools-suite'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($calendars as $calendar): ?>
								<?php
									// v0.9.9.58: Prefer calendar_image_id from table, fallback to option
									$image_id = !empty($calendar->calendar_image_id) ? absint($calendar->calendar_image_id) : (isset($calendar_images_option[$calendar->calendar_id]) ? absint($calendar_images_option[$calendar->calendar_id]) : 0);
									$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
								?>
								<tr>
									<td>
										<input 
											type="checkbox" 
											name="selected_calendars[]" 
											value="<?php echo esc_attr($calendar->id); ?>"
											class="cts-calendar-checkbox"
											<?php checked($calendar->is_selected, 1); ?>
										>
									</td>
									<td>
										<strong><?php echo esc_html($calendar->name_translated ?: $calendar->name); ?></strong>
										<?php if ($calendar->name !== $calendar->name_translated && !empty($calendar->name_translated)): ?>
											<br><small class="description"><?php echo esc_html($calendar->name); ?></small>
										<?php endif; ?>
									</td>
									<td>
										<code><?php echo esc_html($calendar->calendar_id); ?></code>
									</td>
									<td>
										<?php if ($calendar->is_public): ?>
											<span class="cts-badge cts-badge-success">
												<?php esc_html_e('Öffentlich', 'churchtools-suite'); ?>
											</span>
										<?php else: ?>
											<span class="cts-badge cts-badge-secondary">
												<?php esc_html_e('Privat', 'churchtools-suite'); ?>
											</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if (!empty($calendar->color)): ?>
											<div style="display: inline-block; width: 30px; height: 20px; background-color: <?php echo esc_attr($calendar->color); ?>; border: 1px solid #ddd; border-radius: 3px;"></div>
										<?php else: ?>
											—
										<?php endif; ?>
									</td>
									<td>
										<div class="cts-calendar-image-wrapper">
											<div class="cts-calendar-image-preview" data-calendar-id="<?php echo esc_attr($calendar->calendar_id); ?>">
												<?php if ($image_url): ?>
													<img src="<?php echo esc_url($image_url); ?>" alt="" />
												<?php else: ?>
													<span class="description"><?php esc_html_e('Kein Bild', 'churchtools-suite'); ?></span>
												<?php endif; ?>
											</div>
											<input type="hidden" class="cts-calendar-image-input" data-calendar-id="<?php echo esc_attr($calendar->calendar_id); ?>" name="calendar_images[<?php echo esc_attr($calendar->calendar_id); ?>]" value="<?php echo esc_attr($image_id); ?>">
											<div class="cts-calendar-image-actions">
												<button type="button" class="button button-secondary cts-select-calendar-image" data-calendar-id="<?php echo esc_attr($calendar->calendar_id); ?>"><?php esc_html_e('Bild wählen', 'churchtools-suite'); ?></button>
												<button type="button" class="button-link-delete cts-remove-calendar-image" data-calendar-id="<?php echo esc_attr($calendar->calendar_id); ?>"><?php esc_html_e('Entfernen', 'churchtools-suite'); ?></button>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					
					<div style="margin-top: 15px;">
						<button type="submit" class="button button-primary">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e('Auswahl speichern', 'churchtools-suite'); ?>
						</button>
					</div>
				</form>
				
				<div id="cts-calendar-selection-result" style="margin-top: 15px;"></div>
				
			<?php endif; ?>
			
		</div>
	</div>

