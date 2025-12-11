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
	update_option( 'churchtools_suite_ct_token', sanitize_text_field( $_POST['ct_token'] ?? '' ) );
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$ct_url = get_option( 'churchtools_suite_ct_url', '' );
$ct_token = get_option( 'churchtools_suite_ct_token', '' );
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
						<label for="ct_token"><?php esc_html_e( 'API Token', 'churchtools-suite' ); ?></label>
					</th>
					<td>
						<input type="password" 
							   id="ct_token" 
							   name="ct_token" 
							   value="<?php echo esc_attr( $ct_token ); ?>" 
							   class="regular-text"
							   placeholder="<?php esc_attr_e( 'Ihr API Login Token', 'churchtools-suite' ); ?>">
						<p class="description"><?php esc_html_e( 'Login Token aus ChurchTools (Benutzer → API Token)', 'churchtools-suite' ); ?></p>
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
