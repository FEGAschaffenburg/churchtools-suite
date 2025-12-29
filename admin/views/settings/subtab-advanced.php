<?php
/**
 * Settings Subtab: Advanced
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Form processing
if ( isset( $_POST['cts_save_advanced'] ) && check_admin_referer( 'cts_settings' ) ) {
	$advanced_mode = isset( $_POST['advanced_mode'] ) ? 1 : 0;
	update_option( 'churchtools_suite_advanced_mode', $advanced_mode );
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Erweiterte Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$advanced_mode = get_option( 'churchtools_suite_advanced_mode', 0 );
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'Erweiterte Optionen', 'churchtools-suite' ); ?></h3>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="advanced_mode"><?php esc_html_e( 'Erweiteter Modus', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<label class="cts-toggle">
						<input type="checkbox" 
							   id="advanced_mode" 
							   name="advanced_mode" 
							   value="1" 
							   <?php checked( $advanced_mode, 1 ); ?>>
						<span class="cts-toggle-slider"></span>
					</label>
					<span class="cts-form-description">
						<?php esc_html_e( 'Zeigt zusätzliche Funktionen wie Debug-Logs und erweiterte Statistiken in der Navigation an.', 'churchtools-suite' ); ?>
					</span>
				</td>
			</tr>
		</table>
		
		<?php if ( $advanced_mode ) : ?>
		<div class="cts-info" style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107;">
			<p style="margin: 0;">
				<strong>🔧 Erweiterter Modus aktiv:</strong> 
				<?php esc_html_e( 'Sie sehen jetzt zusätzliche Tabs und Optionen in der Administration.', 'churchtools-suite' ); ?>
			</p>
		</div>
		<?php endif; ?>
	</div>

	<div class="cts-submit">
		<button type="submit" name="cts_save_advanced" class="cts-button cts-button-primary">
			<span>💾</span>
			<?php esc_html_e( 'Speichern', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>
