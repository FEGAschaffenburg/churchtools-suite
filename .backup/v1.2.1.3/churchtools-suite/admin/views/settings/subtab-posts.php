<?php
/**
 * Settings Subtab: Posts
 *
 * @package ChurchTools_Suite
 * @since   1.1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( isset( $_POST['cts_save_posts'] ) || isset( $_POST['ct_posts_sync_groups_now'] ) ) && check_admin_referer( 'cts_settings' ) ) {
	do_action( 'cts_posts_settings_save', $_POST );

	if ( isset( $_POST['ct_posts_sync_groups_now'] ) ) {
		echo '<div class="cts-notice cts-notice-info"><p>' . esc_html__( 'Gruppen-Sync wurde ausgeführt.', 'churchtools-suite' ) . '</p></div>';
	} else {
		echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Berichte-Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
	}
}
?>

<form method="post" action="">
	<?php wp_nonce_field( 'cts_settings' ); ?>

	<?php do_action( 'cts_posts_settings_render' ); ?>

	<div class="cts-form-actions">
		<button type="submit" name="cts_save_posts" class="cts-button cts-button-primary">
			<?php esc_html_e( 'Berichte-Einstellungen speichern', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>
