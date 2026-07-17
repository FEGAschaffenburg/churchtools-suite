<?php
/**
 * Migrations Tab
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get migration status
$current_version = ChurchTools_Suite_Demo_Migrations::get_current_version();
$target_version = ChurchTools_Suite_Demo_Migrations::DB_VERSION;
$has_pending = ChurchTools_Suite_Demo_Migrations::has_pending_migrations();

// Get demo user counts
$old_role_users = get_users( [ 'role' => 'cts_demo_user' ] );
$new_role_users = get_users( [ 'role' => 'demo_tester' ] );
?>

<div class="tab-migrations">
	<h2><?php _e( 'Datenbank-Migrationen', 'churchtools-suite-demo' ); ?></h2>
	
	<!-- Current Status -->
	<div class="<?php echo $has_pending ? 'cts-warning-box' : 'cts-success-box'; ?>">
		<h3><?php _e( 'Migrationsstatus', 'churchtools-suite-demo' ); ?></h3>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php _e( 'Aktuelle DB-Version', 'churchtools-suite-demo' ); ?></th>
				<td><strong><?php echo esc_html( $current_version ); ?></strong></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Ziel DB-Version', 'churchtools-suite-demo' ); ?></th>
				<td><strong><?php echo esc_html( $target_version ); ?></strong></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Status', 'churchtools-suite-demo' ); ?></th>
				<td>
					<?php if ( $has_pending ) : ?>
						<span style="color: #d63638;">
							<span class="dashicons dashicons-warning"></span>
							<?php _e( 'Ausstehende Migrationen vorhanden', 'churchtools-suite-demo' ); ?>
						</span>
					<?php else : ?>
						<span style="color: #00a32a;">
							<span class="dashicons dashicons-yes"></span>
							<?php _e( 'Datenbank ist aktuell', 'churchtools-suite-demo' ); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</div>
	
	<!-- User Role Migration Status -->
	<div class="cts-info-box" style="margin-top: 20px;">
		<h4><?php _e( 'Benutzer-Rollen Status', 'churchtools-suite-demo' ); ?></h4>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php _e( 'Alte Rolle (cts_demo_user)', 'churchtools-suite-demo' ); ?></th>
				<td>
					<strong><?php echo count( $old_role_users ); ?></strong> <?php _e( 'Benutzer', 'churchtools-suite-demo' ); ?>
					<?php if ( count( $old_role_users ) > 0 ) : ?>
						<span style="color: #d63638;">
							(<?php _e( 'Migration erforderlich', 'churchtools-suite-demo' ); ?>)
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Neue Rolle (demo_tester)', 'churchtools-suite-demo' ); ?></th>
				<td>
					<strong><?php echo count( $new_role_users ); ?></strong> <?php _e( 'Benutzer', 'churchtools-suite-demo' ); ?>
				</td>
			</tr>
		</table>
	</div>
	
	<!-- Migration Actions -->
	<hr>
	
	<h3><?php _e( 'Migrations-Aktionen', 'churchtools-suite-demo' ); ?></h3>
	
	<div class="cts-action-buttons">
		<?php if ( $has_pending ) : ?>
			<button type="button" id="cts-run-migrations" class="button button-primary">
				<span class="dashicons dashicons-database-import"></span>
				<?php _e( 'Alle ausstehenden Migrationen ausführen', 'churchtools-suite-demo' ); ?>
			</button>
		<?php endif; ?>
		
		<?php if ( count( $old_role_users ) > 0 ) : ?>
			<button type="button" id="cts-migrate-users" class="button button-secondary">
				<span class="dashicons dashicons-admin-users"></span>
				<?php _e( 'Benutzer-Rollen migrieren', 'churchtools-suite-demo' ); ?>
			</button>
		<?php endif; ?>
		
		<button type="button" id="cts-refresh-status" class="button">
			<span class="dashicons dashicons-update"></span>
			<?php _e( 'Status aktualisieren', 'churchtools-suite-demo' ); ?>
		</button>
	</div>
	
	<!-- Migration Results -->
	<div id="cts-migration-results" style="margin-top: 20px;"></div>
	
	<hr>
	
	<!-- Migration History -->
	<h3><?php _e( 'Migrations-Historie', 'churchtools-suite-demo' ); ?></h3>
	
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php _e( 'Version', 'churchtools-suite-demo' ); ?></th>
				<th><?php _e( 'Beschreibung', 'churchtools-suite-demo' ); ?></th>
				<th><?php _e( 'Status', 'churchtools-suite-demo' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong>1.0</strong></td>
				<td><?php _e( 'Initiale demo_users Tabelle', 'churchtools-suite-demo' ); ?></td>
				<td><span class="dashicons dashicons-yes" style="color: #00a32a;"></span></td>
			</tr>
			<tr>
				<td><strong>1.1</strong></td>
				<td><?php _e( 'Multi-User Support: user_id Spalten für views/presets', 'churchtools-suite-demo' ); ?></td>
				<td><span class="dashicons dashicons-yes" style="color: #00a32a;"></span></td>
			</tr>
			<tr>
				<td><strong>1.2</strong></td>
				<td><?php _e( 'Isolierte Demo-Tabellen: demo_cts_events, demo_cts_calendars, demo_cts_services', 'churchtools-suite-demo' ); ?></td>
				<td><span class="dashicons dashicons-yes" style="color: #00a32a;"></span></td>
			</tr>
			<tr>
				<td><strong>1.3</strong></td>
				<td><?php _e( 'Benutzer-Rollen Migration: cts_demo_user → demo_tester', 'churchtools-suite-demo' ); ?></td>
				<td>
					<?php if ( version_compare( $current_version, '1.3', '>=' ) ) : ?>
						<span class="dashicons dashicons-yes" style="color: #00a32a;"></span>
					<?php else : ?>
						<span class="dashicons dashicons-minus" style="color: #dba617;"></span>
						<?php _e( 'Ausstehend', 'churchtools-suite-demo' ); ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<hr>
	
	<!-- WP-CLI Commands -->
	<h3><?php _e( 'WP-CLI Befehle', 'churchtools-suite-demo' ); ?></h3>
	
	<p><?php _e( 'Alternativ können Migrationen auch per WP-CLI durchgeführt werden:', 'churchtools-suite-demo' ); ?></p>
	
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php _e( 'Befehl', 'churchtools-suite-demo' ); ?></th>
				<th><?php _e( 'Beschreibung', 'churchtools-suite-demo' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><code>wp cts-demo status</code></td>
				<td><?php _e( 'Zeigt aktuellen Migrationsstatus und User-Statistiken', 'churchtools-suite-demo' ); ?></td>
			</tr>
			<tr>
				<td><code>wp cts-demo migrate</code></td>
				<td><?php _e( 'Führt alle ausstehenden Migrationen aus', 'churchtools-suite-demo' ); ?></td>
			</tr>
			<tr>
				<td><code>wp cts-demo migrate_users</code></td>
				<td><?php _e( 'Migriert Benutzer von alter zu neuer Rolle', 'churchtools-suite-demo' ); ?></td>
			</tr>
			<tr>
				<td><code>wp cts-demo list_users</code></td>
				<td><?php _e( 'Listet alle Demo-Benutzer auf', 'churchtools-suite-demo' ); ?></td>
			</tr>
		</tbody>
	</table>
	
	<div class="cts-info-box" style="margin-top: 20px;">
		<p><strong><?php _e( 'Hinweis:', 'churchtools-suite-demo' ); ?></strong></p>
		<ul>
			<li><?php _e( 'Migrationen werden automatisch beim Plugin-Update ausgeführt', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Manuelle Ausführung ist nur in Ausnahmefällen notwendig', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Alle Migrationen sind idempotent (können mehrfach ausgeführt werden)', 'churchtools-suite-demo' ); ?></li>
			<li><?php _e( 'Vor größeren Migrationen wird empfohlen, ein Datenbank-Backup anzulegen', 'churchtools-suite-demo' ); ?></li>
		</ul>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Run migrations
	$('#cts-run-migrations').on('click', function() {
		if (!confirm('<?php _e( 'Möchten Sie wirklich alle ausstehenden Migrationen ausführen?', 'churchtools-suite-demo' ); ?>')) {
			return;
		}
		
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="cts-loading"></span> <?php _e( 'Migration läuft...', 'churchtools-suite-demo' ); ?>');
		$('#cts-migration-results').html('');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_run_migrations',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					let html = '<div class="cts-success-box">';
					html += '<p><strong>✓ ' + response.data.message + '</strong></p>';
					if (response.data.log && response.data.log.length > 0) {
						html += '<p><?php _e( 'Migration Log:', 'churchtools-suite-demo' ); ?></p>';
						html += '<pre style="background: #f6f7f7; padding: 10px; overflow-x: auto;">';
						response.data.log.forEach(function(line) {
							html += line + '\n';
						});
						html += '</pre>';
					}
					html += '<p><a href="" class="button"><?php _e( 'Seite neu laden', 'churchtools-suite-demo' ); ?></a></p>';
					html += '</div>';
					$('#cts-migration-results').html(html);
				} else {
					$('#cts-migration-results').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
				}
			},
			error: function() {
				$('#cts-migration-results').html('<div class="notice notice-error"><p><?php _e( 'Fehler beim Ausführen der Migrationen', 'churchtools-suite-demo' ); ?></p></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});
	
	// Migrate users
	$('#cts-migrate-users').on('click', function() {
		if (!confirm('<?php _e( 'Möchten Sie alle Benutzer von cts_demo_user zu demo_tester migrieren?', 'churchtools-suite-demo' ); ?>')) {
			return;
		}
		
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="cts-loading"></span> <?php _e( 'Migriere...', 'churchtools-suite-demo' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cts_demo_migrate_users',
				nonce: '<?php echo wp_create_nonce( 'cts_demo_admin' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					let html = '<div class="cts-success-box">';
					html += '<p><strong>✓ ' + response.data.message + '</strong></p>';
					html += '<p><?php _e( 'Migrierte Benutzer:', 'churchtools-suite-demo' ); ?> ' + response.data.migrated + '</p>';
					html += '<p><a href="" class="button"><?php _e( 'Seite neu laden', 'churchtools-suite-demo' ); ?></a></p>';
					html += '</div>';
					$('#cts-migration-results').html(html);
					$btn.hide();
				} else {
					$('#cts-migration-results').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
				}
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});
	
	// Refresh status
	$('#cts-refresh-status').on('click', function() {
		location.reload();
	});
});
</script>
