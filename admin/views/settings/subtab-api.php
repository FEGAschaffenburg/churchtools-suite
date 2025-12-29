<?php
/**
 * Settings Subtab: API & Connection
 *
 * @package ChurchTools_Suite
 * @since   0.7.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Form processing
if ( isset( $_POST['cts_save_api'] ) && check_admin_referer( 'cts_settings' ) ) {
	$tenant = sanitize_text_field( $_POST['ct_tenant'] ?? '' );
	$full_url = ! empty( $tenant ) ? 'https://' . $tenant . '.church.tools' : '';
	
	update_option( 'churchtools_suite_ct_url', $full_url );
	update_option( 'churchtools_suite_ct_username', sanitize_email( $_POST['ct_username'] ?? '' ) );
	update_option( 'churchtools_suite_ct_password', sanitize_text_field( $_POST['ct_password'] ?? '' ) );
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'API-Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$ct_url = get_option( 'churchtools_suite_ct_url', '' );
$ct_tenant = '';
if ( ! empty( $ct_url ) ) {
	$parsed = parse_url( $ct_url );
	if ( isset( $parsed['host'] ) ) {
		$ct_tenant = str_replace( '.church.tools', '', $parsed['host'] );
	}
}
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'ChurchTools API-Verbindung', 'churchtools-suite' ); ?></h3>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="ct_tenant"><?php esc_html_e( 'ChurchTools Tenant', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<div style="display: flex; align-items: center; gap: 8px; max-width: 450px;">
						<span style="color: #646970; font-size: 13px; white-space: nowrap;">https://</span>
						<input type="text" 
							   id="ct_tenant" 
							   name="ct_tenant" 
							   value="<?php echo esc_attr( $ct_tenant ); ?>" 
							   class="cts-form-input"
							   style="max-width: none; flex: 1;"
							   placeholder="ihre-gemeinde">
						<span style="color: #646970; font-size: 13px; white-space: nowrap;">.church.tools</span>
					</div>
					<span class="cts-form-description"><?php esc_html_e( 'Nur der Name Ihrer ChurchTools-Instanz (ohne https:// und .church.tools)', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ct_username"><?php esc_html_e( 'Benutzername', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<input type="email" 
						   id="ct_username" 
						   name="ct_username" 
						   value="<?php echo esc_attr( $ct_username ); ?>" 
						   class="cts-form-input"
						   placeholder="<?php esc_attr_e( 'ihre.email@gemeinde.de', 'churchtools-suite' ); ?>">
					<span class="cts-form-description"><?php esc_html_e( 'Ihre E-Mail-Adresse für ChurchTools', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ct_password"><?php esc_html_e( 'Passwort', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<input type="password" 
						   id="ct_password" 
						   name="ct_password" 
						   value="<?php echo esc_attr( $ct_password ); ?>" 
						   class="cts-form-input"
						   placeholder="<?php esc_attr_e( 'Ihr ChurchTools Passwort', 'churchtools-suite' ); ?>">
					<span class="cts-form-description"><?php esc_html_e( 'Ihr Passwort für ChurchTools', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
		</table>
	</div>

	<div class="cts-submit">
		<button type="submit" name="cts_save_api" class="cts-button cts-button-primary">
			<span>💾</span>
			<?php esc_html_e( 'Speichern', 'churchtools-suite' ); ?>
		</button>
		<button type="button" id="cts-test-connection" class="cts-button cts-button-secondary">
			<span>🔌</span>
			<?php esc_html_e( 'Verbindung testen', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>

<div id="cts-connection-result" style="display: none; margin-top: 20px;"></div>
