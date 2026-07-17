<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$last_run_at = is_array( $last_result ) ? (string) ( $last_result['run_at'] ?? '' ) : '';
$last_stats = is_array( $last_result ) ? (array) ( $last_result['stats'] ?? [] ) : [];
?>

<div class="wrap cts-wrap">
	<div class="cts-header">
		<h1><span>📝</span> <?php esc_html_e( 'Berichte Übersicht', 'churchtools-suite-posts-sync' ); ?></h1>
		<p class="cts-subtitle"><?php esc_html_e( 'Übersicht synchronisierter ChurchTools-Berichte analog zur normalen Beitragsansicht.', 'churchtools-suite-posts-sync' ); ?></p>
	</div>

	<div class="cts-card cts-posts-sync-overview-card">
		<div class="cts-card-body">
			<p class="cts-posts-sync-overview-meta">
				<strong><?php esc_html_e( 'Sync aktiv:', 'churchtools-suite-posts-sync' ); ?></strong>
				<?php echo $sync_enabled ? '✅' : '❌'; ?>
				<span class="cts-posts-sync-meta-item"><strong><?php esc_html_e( 'Zieltyp:', 'churchtools-suite-posts-sync' ); ?></strong> <?php echo esc_html( $target_type ); ?></span>
				<?php if ( $last_run_at !== '' ) : ?>
					<span class="cts-posts-sync-meta-item"><strong><?php esc_html_e( 'Letzter Lauf:', 'churchtools-suite-posts-sync' ); ?></strong> <?php echo esc_html( $last_run_at ); ?></span>
				<?php endif; ?>
			</p>

			<?php if ( ! empty( $last_stats ) ) : ?>
				<p class="cts-posts-sync-overview-stats">
					<?php echo esc_html( sprintf( 'Gefunden: %d | Erstellt: %d | Aktualisiert: %d | Übersprungen: %d | Fehler: %d', (int) ( $last_stats['posts_found'] ?? 0 ), (int) ( $last_stats['posts_created'] ?? 0 ), (int) ( $last_stats['posts_updated'] ?? 0 ), (int) ( $last_stats['posts_skipped'] ?? 0 ), (int) ( $last_stats['errors'] ?? 0 ) ) ); ?>
				</p>
			<?php endif; ?>

			<p class="cts-posts-sync-overview-actions">
				<?php if ( $is_local_environment ) : ?>
					<button type="button" class="button button-primary" id="cts-posts-overview-run-sync" data-action="run-sync"><?php esc_html_e( 'Jetzt manuell synchronisieren', 'churchtools-suite-posts-sync' ); ?></button>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=churchtools-suite&tab=settings&subtab=posts' ) ); ?>"><?php esc_html_e( 'Zu Berichte-Einstellungen', 'churchtools-suite-posts-sync' ); ?></a>
			</p>

			<div id="cts-posts-overview-result" class="cts-posts-sync-overview-result"></div>
		</div>
	</div>

	<div class="cts-card cts-posts-sync-overview-card">
		<div class="cts-card-body">
			<h2 class="cts-posts-sync-overview-heading"><?php esc_html_e( 'Synchronisierte Inhalte', 'churchtools-suite-posts-sync' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titel', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Post-Gruppe', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Person', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Typ', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Status', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Sichtbarkeit', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'ChurchTools ID', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Veröffentlicht', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Läuft ab', 'churchtools-suite-posts-sync' ); ?></th>
						<th><?php esc_html_e( 'Aktion', 'churchtools-suite-posts-sync' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $posts ) ) : ?>
						<?php foreach ( $posts as $post_item ) : ?>
							<?php $ct_id = get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_POST_ID, true ); ?>
							<?php $group_title = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_GROUP_TITLE, true ); ?>
							<?php $actor_name = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_ACTOR_NAME, true ); ?>
							<?php $post_visibility = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_POST_VISIBILITY, true ); ?>
							<?php $group_visibility = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_GROUP_VISIBILITY, true ); ?>
							<?php $published_date = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_PUBLISHED_DATE, true ); ?>
							<?php $expiration_date = (string) get_post_meta( (int) $post_item->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_EXPIRATION_DATE, true ); ?>
							<tr>
								<td><?php echo esc_html( get_the_title( $post_item ) ); ?></td>
								<td><?php echo esc_html( $group_title !== '' ? $group_title : '—' ); ?></td>
								<td><?php echo esc_html( $actor_name !== '' ? $actor_name : '—' ); ?></td>
								<td><?php echo esc_html( (string) $post_item->post_type ); ?></td>
								<td><?php echo esc_html( (string) $post_item->post_status ); ?></td>
								<td><?php echo esc_html( $post_visibility !== '' ? $post_visibility : ( $group_visibility !== '' ? $group_visibility : '—' ) ); ?></td>
								<td><?php echo esc_html( (string) $ct_id ); ?></td>
								<td><?php echo esc_html( $published_date !== '' ? $published_date : (string) $post_item->post_date ); ?></td>
								<td><?php echo esc_html( $expiration_date !== '' ? $expiration_date : '—' ); ?></td>
								<td><a href="<?php echo esc_url( get_edit_post_link( (int) $post_item->ID ) ); ?>"><?php esc_html_e( 'Bearbeiten', 'churchtools-suite-posts-sync' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="10"><?php esc_html_e( 'Noch keine synchronisierten Berichte gefunden.', 'churchtools-suite-posts-sync' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>