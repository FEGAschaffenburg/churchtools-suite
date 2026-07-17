<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Presentations_Admin {
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'register_submenu' ], 55 );
		add_action( 'admin_post_cts_presentations_save', [ __CLASS__, 'handle_save' ] );
		add_action( 'admin_post_cts_presentations_create_page', [ __CLASS__, 'handle_create_page' ] );
	}

	public static function register_submenu(): void {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			return;
		}

		add_submenu_page(
			'churchtools-suite',
			__( 'Präsentation', 'churchtools-suite-presentations' ),
			__( 'Präsentation', 'churchtools-suite-presentations' ),
			'manage_churchtools_suite',
			'churchtools-suite-presentations',
			[ __CLASS__, 'render_page' ]
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'churchtools-suite-presentations' ) );
		}

		$events = CTS_Presentations::get_upcoming_events_for_select();
		$views = CTS_Presentations::get_view_options_flat();
		$event_id = (int) get_option( CTS_Presentations::OPTION_EVENT_ID, 0 );
		$auto_create = (int) get_option( CTS_Presentations::OPTION_AUTO_CREATE, 0 );
		$require_builder = (int) get_option( CTS_Presentations::OPTION_REQUIRE_BUILDER, 1 );
		$special_tags = (string) get_option( CTS_Presentations::OPTION_SPECIAL_TAGS, '' );
		$slide_seconds = (int) get_option( CTS_Presentations::OPTION_SLIDE_SECONDS, 10 );
		$slide_1_view = (string) get_option( CTS_Presentations::OPTION_SLIDE_1_VIEW, 'list-classic' );
		$slide_2_view = (string) get_option( CTS_Presentations::OPTION_SLIDE_2_VIEW, 'grid-modern' );
		$page_id = (int) get_option( CTS_Presentations::OPTION_PAGE_ID, 0 );
		$active_builders = CTS_Presentations::get_active_page_builder_labels();

		$message = isset( $_GET['cts_msg'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['cts_msg'] ) ) : '';
		$is_error = isset( $_GET['cts_err'] ) ? (int) $_GET['cts_err'] === 1 : false;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ChurchTools Präsentation', 'churchtools-suite-presentations' ); ?></h1>

			<?php if ( $message !== '' ) : ?>
				<div class="notice <?php echo $is_error ? 'notice-error' : 'notice-success'; ?> is-dismissible">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width:1200px;padding:18px 20px;">
				<h2 style="margin-top:0"><?php esc_html_e( 'Konfiguration', 'churchtools-suite-presentations' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'cts_presentations_save' ); ?>
					<input type="hidden" name="action" value="cts_presentations_save" />

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="cts_presentations_event_id"><?php esc_html_e( 'Leit-Termin', 'churchtools-suite-presentations' ); ?></label></th>
							<td>
								<select id="cts_presentations_event_id" name="cts_presentations_event_id" style="min-width:420px;max-width:100%;">
									<option value="0"><?php esc_html_e( 'Bitte Termin wählen', 'churchtools-suite-presentations' ); ?></option>
									<?php foreach ( $events as $event ) : ?>
										<?php $id = (int) $event->id; ?>
										<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $event_id, $id ); ?>>
											<?php echo esc_html( sprintf( '#%d | %s | %s', $id, (string) $event->start_datetime, (string) $event->title ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Dieser Termin steuert die Folie Heute Dienste.', 'churchtools-suite-presentations' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Automatik', 'churchtools-suite-presentations' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="cts_presentations_auto_create" value="1" <?php checked( $auto_create, 1 ); ?> />
									<?php esc_html_e( 'Automatisch Seite erstellen/aktualisieren', 'churchtools-suite-presentations' ); ?>
								</label>
								<br />
								<label>
									<input type="checkbox" name="cts_presentations_require_builder" value="1" <?php checked( $require_builder, 1 ); ?> />
									<?php esc_html_e( 'Nur wenn unterstützter Page Builder aktiv ist', 'churchtools-suite-presentations' ); ?>
								</label>
								<p class="description">
									<?php
									if ( empty( $active_builders ) ) {
										esc_html_e( 'Aktive Builder: keine', 'churchtools-suite-presentations' );
									} else {
										echo esc_html( sprintf( 'Aktive Builder: %s', implode( ', ', $active_builders ) ) );
									}
									?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cts_presentations_slide_seconds"><?php esc_html_e( 'Slide-Dauer (Sekunden)', 'churchtools-suite-presentations' ); ?></label></th>
							<td>
								<input id="cts_presentations_slide_seconds" type="number" min="3" max="60" name="cts_presentations_slide_seconds" value="<?php echo esc_attr( (string) $slide_seconds ); ?>" />
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cts_presentations_slide_1_view"><?php esc_html_e( 'Folie 1: Nächste Termine (View)', 'churchtools-suite-presentations' ); ?></label></th>
							<td>
								<select id="cts_presentations_slide_1_view" name="cts_presentations_slide_1_view" style="min-width:280px;">
									<?php foreach ( $views as $value => $label ) : ?>
										<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( $slide_1_view, (string) $value ); ?>><?php echo esc_html( (string) $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cts_presentations_slide_2_view"><?php esc_html_e( 'Folie 2: Besondere Termine (View)', 'churchtools-suite-presentations' ); ?></label></th>
							<td>
								<select id="cts_presentations_slide_2_view" name="cts_presentations_slide_2_view" style="min-width:280px;">
									<?php foreach ( $views as $value => $label ) : ?>
										<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( $slide_2_view, (string) $value ); ?>><?php echo esc_html( (string) $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="cts_presentations_special_tags"><?php esc_html_e( 'Tags für Besondere Termine', 'churchtools-suite-presentations' ); ?></label></th>
							<td>
								<input id="cts_presentations_special_tags" type="text" class="regular-text" name="cts_presentations_special_tags" value="<?php echo esc_attr( $special_tags ); ?>" />
								<p class="description"><?php esc_html_e( 'Kommagetrennt, z.B. Highlight,Taufe,Special', 'churchtools-suite-presentations' ); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Einstellungen speichern', 'churchtools-suite-presentations' ) ); ?>
				</form>
			</div>

			<div class="card" style="max-width:1200px;padding:18px 20px;margin-top:16px;">
				<h2 style="margin-top:0"><?php esc_html_e( 'Seite erzeugen', 'churchtools-suite-presentations' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'cts_presentations_create_page' ); ?>
					<input type="hidden" name="action" value="cts_presentations_create_page" />
					<?php submit_button( __( 'Präsentations-Seite jetzt erstellen/aktualisieren', 'churchtools-suite-presentations' ), 'primary', 'submit', false ); ?>
				</form>

				<?php if ( $page_id > 0 ) : ?>
					<p style="margin-top:12px;">
						<a class="button" href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php esc_html_e( 'Seite bearbeiten', 'churchtools-suite-presentations' ); ?></a>
						<a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( get_permalink( $page_id ) ); ?>"><?php esc_html_e( 'Seite öffnen', 'churchtools-suite-presentations' ); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'churchtools-suite-presentations' ) );
		}

		check_admin_referer( 'cts_presentations_save' );

		$event_id = isset( $_POST['cts_presentations_event_id'] ) ? absint( wp_unslash( (string) $_POST['cts_presentations_event_id'] ) ) : 0;
		$auto_create = isset( $_POST['cts_presentations_auto_create'] ) ? 1 : 0;
		$require_builder = isset( $_POST['cts_presentations_require_builder'] ) ? 1 : 0;
		$special_tags = isset( $_POST['cts_presentations_special_tags'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['cts_presentations_special_tags'] ) ) : '';
		$slide_seconds = isset( $_POST['cts_presentations_slide_seconds'] ) ? absint( wp_unslash( (string) $_POST['cts_presentations_slide_seconds'] ) ) : 10;
		$slide_1_view = isset( $_POST['cts_presentations_slide_1_view'] ) ? sanitize_key( wp_unslash( (string) $_POST['cts_presentations_slide_1_view'] ) ) : 'list-classic';
		$slide_2_view = isset( $_POST['cts_presentations_slide_2_view'] ) ? sanitize_key( wp_unslash( (string) $_POST['cts_presentations_slide_2_view'] ) ) : 'grid-modern';

		$views = CTS_Presentations::get_view_options_flat();
		if ( ! isset( $views[ $slide_1_view ] ) ) {
			$slide_1_view = 'list-classic';
		}
		if ( ! isset( $views[ $slide_2_view ] ) ) {
			$slide_2_view = 'grid-modern';
		}

		$slide_seconds = max( 3, min( 60, $slide_seconds ) );

		update_option( CTS_Presentations::OPTION_EVENT_ID, $event_id, false );
		update_option( CTS_Presentations::OPTION_AUTO_CREATE, $auto_create, false );
		update_option( CTS_Presentations::OPTION_REQUIRE_BUILDER, $require_builder, false );
		update_option( CTS_Presentations::OPTION_SPECIAL_TAGS, $special_tags, false );
		update_option( CTS_Presentations::OPTION_SLIDE_SECONDS, $slide_seconds, false );
		update_option( CTS_Presentations::OPTION_SLIDE_1_VIEW, $slide_1_view, false );
		update_option( CTS_Presentations::OPTION_SLIDE_2_VIEW, $slide_2_view, false );

		if ( $auto_create === 1 ) {
			CTS_Presentations::maybe_auto_create_page();
		}

		wp_safe_redirect( self::build_redirect_url( __( 'Einstellungen gespeichert.', 'churchtools-suite-presentations' ), false ) );
		exit;
	}

	public static function handle_create_page(): void {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'churchtools-suite-presentations' ) );
		}

		check_admin_referer( 'cts_presentations_create_page' );

		$result = CTS_Presentations::create_or_update_presentation_page();
		$message = isset( $result['message'] ) ? (string) $result['message'] : __( 'Unbekanntes Ergebnis.', 'churchtools-suite-presentations' );
		$err = isset( $result['ok'] ) && $result['ok'] ? false : true;

		wp_safe_redirect( self::build_redirect_url( $message, $err ) );
		exit;
	}

	private static function build_redirect_url( string $message, bool $error ): string {
		$url = add_query_arg(
			[
				'page' => 'churchtools-suite-presentations',
				'cts_msg' => $message,
				'cts_err' => $error ? 1 : 0,
			],
			admin_url( 'admin.php' )
		);

		return $url;
	}
}

CTS_Presentations_Admin::init();
