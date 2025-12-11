<?php
/**
 * Settings Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Formular verarbeiten
if ( isset( $_POST['cts_save_settings'] ) && check_admin_referer( 'cts_settings' ) ) {
	update_option( 'churchtools_suite_ct_url', sanitize_text_field( $_POST['ct_url'] ?? '' ) );
	update_option( 'churchtools_suite_ct_username', sanitize_email( $_POST['ct_username'] ?? '' ) );
	update_option( 'churchtools_suite_ct_password', sanitize_text_field( $_POST['ct_password'] ?? '' ) );
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$ct_url = get_option( 'churchtools_suite_ct_url', '' );
$ct_username = get_option( 'churchtools_suite_ct_username', '' );
$ct_password = get_option( 'churchtools_suite_ct_password', '' );
?>

<div class="cts-settings">
	
	<form method="post" action="">
		<?php wp_nonce_field( 'cts_settings' ); ?>
		
		<div class="cts-card">
			<h3><?php esc_html_e( 'ChurchTools API', 'churchtools-suite' ); ?></h3>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="ct_url"><?php esc_html_e( 'ChurchTools URL', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="url" 
							   id="ct_url" 
							   name="ct_url" 
							   value="<?php echo esc_attr( $ct_url ); ?>" 
							   class="regular-text"
							   placeholder="https://ihre-gemeinde.church.tools">
						<p class="description"><?php esc_html_e( 'Vollständige URL zu Ihrer ChurchTools-Instanz', 'churchtools-suite' ); ?></p>
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
							   class="regular-text"
							   placeholder="<?php esc_attr_e( 'ihre.email@gemeinde.de', 'churchtools-suite' ); ?>">
						<p class="description"><?php esc_html_e( 'Ihre E-Mail-Adresse für ChurchTools', 'churchtools-suite' ); ?></p>
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
							   class="regular-text"
							   placeholder="<?php esc_attr_e( 'Ihr ChurchTools Passwort', 'churchtools-suite' ); ?>">
						<p class="description"><?php esc_html_e( 'Ihr Passwort für ChurchTools', 'churchtools-suite' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<button type="submit" name="cts_save_settings" class="button button-primary">
				<?php esc_html_e( 'Einstellungen speichern', 'churchtools-suite' ); ?>
			</button>
		</p>
	</form>

</div>
