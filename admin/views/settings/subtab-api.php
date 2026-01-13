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

	$auth_method = sanitize_key( $_POST['ct_auth_method'] ?? 'password' );
	$auth_method = in_array( $auth_method, [ 'password', 'token' ], true ) ? $auth_method : 'password';

	update_option( 'churchtools_suite_ct_url', $full_url );
	update_option( 'churchtools_suite_ct_auth_method', $auth_method );
	update_option( 'churchtools_suite_ct_username', sanitize_email( $_POST['ct_username'] ?? '' ) );
	update_option( 'churchtools_suite_ct_password', sanitize_text_field( $_POST['ct_password'] ?? '' ) );
	update_option( 'churchtools_suite_ct_token', sanitize_text_field( $_POST['ct_token'] ?? '' ) );

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
$ct_auth_method = get_option( 'churchtools_suite_ct_auth_method', 'password' );
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
$ct_token = get_option( 'churchtools_suite_ct_token', '' );
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<div class="cts-card">
		<div class="cts-card-header">
			<span class="cts-card-icon">🔌</span>
			<h3><?php esc_html_e( 'ChurchTools API-Verbindung', 'churchtools-suite' ); ?></h3>
		</div>
		<div class="cts-card-body">
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="ct_auth_method_password"><?php esc_html_e( 'Anmeldemethode', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<label style="margin-right: 16px; display: inline-flex; align-items: center; gap: 6px;">
						<input type="radio" name="ct_auth_method" id="ct_auth_method_password" value="password" <?php checked( $ct_auth_method, 'password' ); ?>>
						<span><?php esc_html_e( 'Benutzername & Passwort', 'churchtools-suite' ); ?></span>
					</label>
					<label style="display: inline-flex; align-items: center; gap: 6px;">
						<input type="radio" name="ct_auth_method" id="ct_auth_method_token" value="token" <?php checked( $ct_auth_method, 'token' ); ?>>
						<span><?php esc_html_e( 'API-Token', 'churchtools-suite' ); ?></span>
					</label>
					<span class="cts-form-description"><?php esc_html_e( 'Wählen Sie, ob Sie sich per Login oder über ein persönliches API-Token verbinden möchten.', 'churchtools-suite' ); ?></span>
				</td>
			</tr>
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
			<tr class="cts-auth-password">
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
			<tr class="cts-auth-password">
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
			<tr class="cts-auth-token">
				<th scope="row">
					<label for="ct_token"><?php esc_html_e( 'API-Token', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<input type="text"
					   id="ct_token"
					   name="ct_token"
					   value="<?php echo esc_attr( $ct_token ); ?>"
					   class="cts-form-input"
					   placeholder="<?php esc_attr_e( 'z.B. eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...', 'churchtools-suite' ); ?>">
					<span class="cts-form-description">
						<?php esc_html_e( 'Persönliches API-Token aus ChurchTools. Keine Ablaufzeit, jederzeit widerrufbar.', 'churchtools-suite' ); ?>
					</span>
					<div style="margin-top:8px; font-size:12px; line-height:1.5; color:#334155;">
						<strong><?php esc_html_e( 'Token anlegen:', 'churchtools-suite' ); ?></strong>
						<ol style="margin:6px 0 0 18px; padding:0; list-style:decimal;">
							<li><?php esc_html_e( 'In ChurchTools oben rechts auf Ihr Profilbild klicken.', 'churchtools-suite' ); ?></li>
							<li><?php esc_html_e( 'Menüpunkt „API-Token“ öffnen und „Neues Token“ wählen.', 'churchtools-suite' ); ?></li>
							<li><?php esc_html_e( 'Einen Namen vergeben, benötigte Rechte freigeben (Kalender/Events), Token kopieren.', 'churchtools-suite' ); ?></li>
						</ol>
					</div>
				</td>
			</tr>
		</table>
	
	</div>
	
	<div class="cts-card-footer">
		<button type="submit" name="cts_save_api" class="cts-button cts-button-primary">
			<span>💾</span>
			<?php esc_html_e( 'Speichern', 'churchtools-suite' ); ?>
		</button>
		<button type="button" id="cts-test-connection" class="cts-button cts-button-secondary">
			<span>🔌</span>
			<?php esc_html_e( 'Verbindung testen', 'churchtools-suite' ); ?>
		</button>
	</div>
<div id="cts-connection-result" style="display: none; margin-top: 20px;"></div>

<script>
(function(){
	const radios = document.querySelectorAll('input[name="ct_auth_method"]');
	const pwdRows = document.querySelectorAll('.cts-auth-password');
	const tokenRows = document.querySelectorAll('.cts-auth-token');

	function refreshAuthVisibility() {
		const val = document.querySelector('input[name="ct_auth_method"]:checked')?.value || 'password';
		pwdRows.forEach(r => r.style.display = (val === 'password') ? '' : 'none');
		tokenRows.forEach(r => r.style.display = (val === 'token') ? '' : 'none');
	}

	radios.forEach(r => r.addEventListener('change', refreshAuthVisibility));
	refreshAuthVisibility();
})();
</script>
