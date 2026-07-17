<?php
/**
 * Settings Subtab Override: API & Connection (Demo Plugin)
 * 
 * Adds demo mode toggle before standard API settings.
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load user settings class
require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-user-settings.php';

$current_user_id = get_current_user_id();
$demo_mode = ChurchTools_Suite_User_Settings::is_demo_mode( $current_user_id );
?>

<div class="cts-card" style="margin-bottom: 20px; border: 2px solid #2271b1;">
	<div class="cts-card-header">
		<span class="cts-card-icon">🎭</span>
		<h3><?php esc_html_e( 'Demo-Modus', 'churchtools-suite' ); ?></h3>
	</div>
	<div class="cts-card-body">
		<div class="cts-form-group">
			<label class="cts-toggle-switch">
				<input type="checkbox" id="cts_demo_mode" <?php checked( $demo_mode ); ?>>
				<span class="cts-toggle-slider"></span>
			</label>
			<div style="margin-left: 60px;">
				<strong><?php esc_html_e( 'Demo-Modus verwenden', 'churchtools-suite' ); ?></strong>
				<p class="description">
					<?php esc_html_e( 'Nutzt vorkonfigurierte Demo-Daten von demo.church.tools. Sie können dies deaktivieren, um Ihre eigene ChurchTools-Instanz zu testen.', 'churchtools-suite' ); ?>
				</p>
			</div>
		</div>
		
		<div id="cts-demo-mode-notice" style="<?php echo $demo_mode ? '' : 'display:none;'; ?> margin-top: 15px; padding: 12px; background: #e7f3ff; border-left: 4px solid #2271b1;">
			<strong>ℹ️ Demo-Modus aktiv</strong>
			<p style="margin: 8px 0 0 0;">
				Sie nutzen die öffentliche Demo-Instanz mit vorkonfigurierten Daten. 
				Die API-Einstellungen unten sind schreibgeschützt.
			</p>
		</div>
		
		<div id="cts-custom-mode-notice" style="<?php echo !$demo_mode ? '' : 'display:none;'; ?> margin-top: 15px; padding: 12px; background: #fff4e5; border-left: 4px solid #ff9800;">
			<strong>⚙️ Eigene Instanz</strong>
			<p style="margin: 8px 0 0 0;">
				Sie können jetzt Ihre eigene ChurchTools-URL und Login-Daten eingeben. 
				Ihre Einstellungen sind komplett isoliert von anderen Demo-Usern.
			</p>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Toggle demo mode
	$('#cts_demo_mode').on('change', function() {
		const enabled = $(this).is(':checked');
		const $apiFields = $('#cts-api-fields input, #cts-api-fields select');
		
		// Show/hide notices
		if (enabled) {
			$('#cts-demo-mode-notice').slideDown();
			$('#cts-custom-mode-notice').slideUp();
			$apiFields.prop('readonly', true).prop('disabled', true).addClass('disabled');
		} else {
			$('#cts-demo-mode-notice').slideUp();
			$('#cts-custom-mode-notice').slideDown();
			$apiFields.prop('readonly', false).prop('disabled', false).removeClass('disabled');
		}
		
		// Save via AJAX
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_demo_toggle_mode',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_toggle' ); ?>',
				enabled: enabled ? 1 : 0
			},
			success: function(response) {
				if (response.success) {
					// Show success message
					const $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
					$('.cts-card').first().before($notice);
					
					setTimeout(function() {
						$notice.fadeOut(function() { $(this).remove(); });
					}, 3000);
					
					// Reload page after 1 second to reflect changes
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					alert('Fehler: ' + response.data.message);
				}
			}
		});
	});
	
	// Initial state
	const demoMode = $('#cts_demo_mode').is(':checked');
	if (demoMode) {
		$('#cts-api-fields input, #cts-api-fields select').prop('readonly', true).prop('disabled', true).addClass('disabled');
	}
});
</script>

<style>
.cts-toggle-switch {
	position: relative;
	display: inline-block;
	width: 50px;
	height: 24px;
	float: left;
	margin-right: 10px;
}

.cts-toggle-switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

.cts-toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .4s;
	border-radius: 24px;
}

.cts-toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .4s;
	border-radius: 50%;
}

input:checked + .cts-toggle-slider {
	background-color: #2271b1;
}

input:checked + .cts-toggle-slider:before {
	transform: translateX(26px);
}

#cts-api-fields input.disabled,
#cts-api-fields select.disabled {
	background-color: #f0f0f0 !important;
	cursor: not-allowed;
	opacity: 0.6;
}
</style>
