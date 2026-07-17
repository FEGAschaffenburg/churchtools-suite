<?php
/**
 * Demo Users Admin View
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Demo-Registrierungen', 'churchtools-suite-demo' ); ?></h1>
	
	<!-- Statistics -->
	<div class="cts-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
		<div class="cts-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Gesamt', 'churchtools-suite-demo' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: bold; color: #2563eb;"><?php echo esc_html( $stats['total'] ); ?></p>
		</div>
		<div class="cts-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Verifiziert', 'churchtools-suite-demo' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: bold; color: #16a34a;"><?php echo esc_html( $stats['verified'] ); ?></p>
		</div>
		<div class="cts-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Unverifiziert', 'churchtools-suite-demo' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: bold; color: #eab308;"><?php echo esc_html( $stats['unverified'] ); ?></p>
		</div>
		<div class="cts-stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Letzte 7 Tage', 'churchtools-suite-demo' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: bold; color: #9333ea;"><?php echo esc_html( $stats['last_7_days'] ); ?></p>
		</div>
	</div>
	
	<!-- Actions -->
	<div class="tablenav top">
		<div class="alignleft actions">
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=cts_demo_export_users' ), 'cts_demo_admin', 'nonce' ) ); ?>" class="button">
				<?php esc_html_e( 'Als CSV exportieren', 'churchtools-suite-demo' ); ?>
			</a>
		</div>
	</div>
	
	<!-- Users Table -->
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'E-Mail', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Name', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Firma/Gemeinde', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Status', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'WP-User', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Letzter Login', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Registriert', 'churchtools-suite-demo' ); ?></th>
				<th><?php esc_html_e( 'Aktionen', 'churchtools-suite-demo' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $users ) ) : ?>
				<tr>
					<td colspan="8" style="text-align: center; padding: 40px 20px; color: #666;">
						<?php esc_html_e( 'Noch keine Registrierungen vorhanden', 'churchtools-suite-demo' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $users as $user ) : ?>
					<tr data-user-id="<?php echo esc_attr( $user->id ); ?>">
						<td><strong><?php echo esc_html( $user->email ); ?></strong></td>
						<td><?php echo esc_html( $user->name ?: '-' ); ?></td>
						<td><?php echo esc_html( $user->company ?: '-' ); ?></td>
						<td>
							<?php if ( $user->verified_at ) : ?>
								<span style="color: #16a34a;">✓ <?php esc_html_e( 'Verifiziert', 'churchtools-suite-demo' ); ?></span>
							<?php else : ?>
								<span style="color: #eab308;">⏳ <?php esc_html_e( 'Ausstehend', 'churchtools-suite-demo' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $user->wordpress_user_id ) : ?>
								<?php $wp_user = get_userdata( $user->wordpress_user_id ); ?>
								<?php if ( $wp_user ) : ?>
									<span style="color: #16a34a;">✓ <?php echo esc_html( $wp_user->user_login ); ?></span>
									<br><small style="color: #666;">(ID: <?php echo esc_html( $user->wordpress_user_id ); ?>)</small>
								<?php else : ?>
									<span style="color: #dc2626;">✗ Gelöscht (ID: <?php echo esc_html( $user->wordpress_user_id ); ?>)</span>
								<?php endif; ?>
							<?php else : ?>
								<span style="color: #94a3b8;">− <?php esc_html_e( 'Nicht erstellt', 'churchtools-suite-demo' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $user->last_login_at ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $user->last_login_at ) ) : '-' ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $user->created_at ) ) ); ?></td>
						<td>
							<?php if ( ! $user->verified_at ) : ?>
								<button type="button" class="button button-small cts-resend-email" data-id="<?php echo esc_attr( $user->id ); ?>" style="margin-bottom: 4px;">
									📧 <?php esc_html_e( 'E-Mail erneut senden', 'churchtools-suite-demo' ); ?>
								</button>
								<br>
								<button type="button" class="button button-small cts-manual-verify" data-id="<?php echo esc_attr( $user->id ); ?>" style="margin-bottom: 4px;">
									✓ <?php esc_html_e( 'Manuell verifizieren', 'churchtools-suite-demo' ); ?>
								</button>
								<br>
							<?php endif; ?>
							<button type="button" class="button button-small cts-delete-user" data-id="<?php echo esc_attr( $user->id ); ?>">
								🗑️ <?php esc_html_e( 'Löschen', 'churchtools-suite-demo' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<script>
jQuery(document).ready(function($) {
	// Delete user
	$('.cts-delete-user').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Wirklich löschen?', 'churchtools-suite-demo' ); ?>')) {
			return;
		}
		
		const userId = $(this).data('id');
		const $row = $('tr[data-user-id="' + userId + '"]');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_delete_user',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>',
				id: userId
			},
			success: function(response) {
				if (response.success) {
					$row.fadeOut(300, function() { $(this).remove(); });
				} else {
					alert(response.data.message);
				}
			}
		});
	});
	
	// Resend verification email
	$('.cts-resend-email').on('click', function() {
		const $btn = $(this);
		const userId = $btn.data('id');
		
		$btn.prop('disabled', true).text('Wird gesendet...');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_resend_email',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>',
				id: userId
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					$btn.text('📧 <?php esc_html_e( 'E-Mail erneut senden', 'churchtools-suite-demo' ); ?>');
				} else {
					alert(response.data.message);
					$btn.text('📧 <?php esc_html_e( 'E-Mail erneut senden', 'churchtools-suite-demo' ); ?>');
				}
				$btn.prop('disabled', false);
			}
		});
	});
	
	// Manual verification
	$('.cts-manual-verify').on('click', function() {
		if (!confirm('<?php esc_html_e( 'User manuell verifizieren und WordPress-Account erstellen?', 'churchtools-suite-demo' ); ?>')) {
			return;
		}
		
		const $btn = $(this);
		const userId = $btn.data('id');
		
		$btn.prop('disabled', true).text('Wird verifiziert...');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_manual_verify',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>',
				id: userId
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message + '\nWordPress User ID: ' + response.data.wp_user_id);
					location.reload();
				} else {
					alert(response.data.message);
					$btn.text('✓ <?php esc_html_e( 'Manuell verifizieren', 'churchtools-suite-demo' ); ?>').prop('disabled', false);
				}
			}
		});
	});
});
</script>
