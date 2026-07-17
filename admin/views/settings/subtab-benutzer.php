<?php
/**
 * Settings Subtab: CTS Managers / Benutzer
 * 
 * Zeigt Read-Only Liste aller Users mit cts_manager Rolle
 * 
 * @package ChurchTools_Suite
 * @since   1.0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-roles.php';

$cts_managers = ChurchTools_Suite_Roles::get_cts_managers();
?>
	<h3><?php esc_html_e( 'ChurchTools Suite Manager', 'churchtools-suite' ); ?></h3>
	<p class="description">
		<?php esc_html_e( 'Benutzer mit Zugriff auf die ChurchTools Suite Plugin-Verwaltung.', 'churchtools-suite' ); ?>
	</p>

	<?php if ( empty( $cts_managers ) ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Noch kein CTS Manager zugewiesen.', 'churchtools-suite' ); ?></p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Benutzername', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Email', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Angemeldet', 'churchtools-suite' ); ?></th>
					<th><?php esc_html_e( 'Aktion', 'churchtools-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $cts_managers as $user ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $user->user_login ); ?></strong>
						</td>
						<td>
							<a href="mailto:<?php echo esc_attr( $user->user_email ); ?>">
								<?php echo esc_html( $user->user_email ); ?>
							</a>
						</td>
						<td>
							<?php
							$last_login = get_user_meta( $user->ID, 'last_login', true );
							if ( $last_login ) {
								echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_login ) );
							} else {
								esc_html_e( 'Nie', 'churchtools-suite' );
							}
							?>
						</td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( [ 'user_id' => $user->ID ], admin_url( 'user-edit.php' ) ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Bearbeiten', 'churchtools-suite' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p style="margin-top: 20px;">
			<a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>" class="button">
				<?php esc_html_e( 'Alle Benutzer verwalten', 'churchtools-suite' ); ?>
			</a>
		</p>
	<?php endif; ?>

	<div style="margin-top: 30px; padding: 15px; background: #f6f8fa; border-radius: 4px;">
		<h4><?php esc_html_e( 'Neuen Manager hinzufügen', 'churchtools-suite' ); ?></h4>
		<p class="description">
			<?php esc_html_e( 'Um einen neuen ChurchTools Manager hinzuzufügen:', 'churchtools-suite' ); ?>
		</p>
		<ol style="margin-left: 20px;">
			<li><?php esc_html_e( 'Gehe zu Benutzer → Alle Benutzer', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Wähle einen vorhandenen Benutzer oder erstelle einen neuen', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Unter "Rolle" wähle "ChurchTools Suite Manager"', 'churchtools-suite' ); ?></li>
			<li><?php esc_html_e( 'Speichere die Änderungen', 'churchtools-suite' ); ?></li>
		</ol>
	</div>

