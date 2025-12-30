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
?>

<div class="cts-tab-content-inner">

   <!-- Kalender Sync Button -->
   <div class="cts-card" style="margin-top: 20px; margin-bottom: 0; background: #f8f9fa; border: 1px solid #e0e0e0;">
	   <div class="cts-card-header" style="display: flex; align-items: center; justify-content: space-between;">
		   <h2 style="margin:0;">📅 <?php esc_html_e('Kalender', 'churchtools-suite'); ?></h2>
		   <button id="cts-sync-calendars-btn" class="button button-secondary" style="font-size:15px; padding:8px 18px;">
			   <span class="dashicons dashicons-update"></span> <?php esc_html_e('Kalender synchronisieren', 'churchtools-suite'); ?>
		   </button>
	   </div>
	   <div id="cts-sync-calendars-result" style="margin-top:8px; font-size:13px; color:#2271b1;"></div>
   </div>

   <!-- Calendar Selection Card -->
   <div class="cts-card" style="margin-top: 20px;">
	   <div class="cts-card-header">
		   <h2>✅ <?php esc_html_e('Kalenderauswahl', 'churchtools-suite'); ?></h2>
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
				}).then(r => r.json()).then(function(data) {
					if (data.success) {
						if (result) result.innerHTML = '<span style="color:#0a0">' + (data.data && data.data.message ? data.data.message : '✅ Synchronisation abgeschlossen') + '</span>';
					} else {
						if (result) result.innerHTML = '<span style="color:#d63638">' + (data.data && data.data.message ? data.data.message : (data.message || 'Fehler beim Sync')) + '</span>';
					}
				}).catch(function(err) {
					if (result) result.innerHTML = '<span style="color:#d63638">Fehler: ' + err.message + '</span>';
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
							</tr>
						</thead>
						<tbody>
							<?php foreach ($calendars as $calendar): ?>
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
	
</div>

<style>
.cts-badge {
	display: inline-block;
	padding: 3px 8px;
	font-size: 12px;
	font-weight: 600;
	border-radius: 3px;
}
.cts-badge-success {
	background: #d4edda;
	color: #155724;
}
.cts-badge-secondary {
	background: #e2e3e5;
	color: #383d41;
}
.cts-info {
	background: #f0f0f1;
	padding: 10px;
	border-left: 4px solid #72aee6;
	margin-bottom: 15px;
}
</style>
