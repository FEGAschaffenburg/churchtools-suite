<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Posts_Sync_Admin {

	public function init() {
		add_action( 'admin_menu', [ $this, 'register_submenu' ], 45 );
		add_action( 'add_meta_boxes', [ $this, 'register_ct_meta_boxes' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'cts_posts_settings_save', [ $this, 'save_posts_settings' ] );
		add_action( 'cts_posts_settings_render', [ $this, 'render_posts_settings' ] );
		add_action( 'wp_ajax_cts_posts_sync_run_now', [ $this, 'ajax_posts_sync_run_now' ] );
	}

	public function enqueue_admin_assets() {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( (string) $_GET['page'] ) : '';
		$tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : '';
		$subtab = isset( $_GET['subtab'] ) ? sanitize_key( (string) $_GET['subtab'] ) : '';

		$is_overview_page = ( $page === 'churchtools-suite-posts-overview' );
		$is_posts_settings_tab = ( $page === 'churchtools-suite' && $tab === 'settings' && $subtab === 'posts' );

		if ( ! $is_overview_page && ! $is_posts_settings_tab ) {
			return;
		}

		wp_enqueue_style(
			'cts-posts-sync-admin',
			CTS_POSTS_SYNC_URL . 'assets/css/churchtools-suite-posts-sync-admin.css',
			[],
			CTS_POSTS_SYNC_VERSION
		);

		if ( $is_overview_page ) {
			wp_enqueue_script(
				'cts-posts-sync-admin',
				CTS_POSTS_SYNC_URL . 'assets/js/churchtools-suite-posts-sync-admin.js',
				[ 'jquery' ],
				CTS_POSTS_SYNC_VERSION,
				true
			);

			wp_localize_script(
				'cts-posts-sync-admin',
				'ctsPostsSyncAdmin',
				[
					'nonce' => wp_create_nonce( 'churchtools_suite_admin' ),
					'messages' => [
						'networkError' => __( 'Netzwerkfehler beim Synchronisieren.', 'churchtools-suite-posts-sync' ),
						'syncError' => __( 'Fehler beim Synchronisieren.', 'churchtools-suite-posts-sync' ),
					],
				]
			);
		}
	}

	public function register_submenu() {
		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			return;
		}

		add_submenu_page(
			'churchtools-suite',
			__( 'Übersicht Berichte', 'churchtools-suite-posts-sync' ),
			__( '📝 Berichte', 'churchtools-suite-posts-sync' ),
			'manage_churchtools_suite',
			'churchtools-suite-posts-overview',
			[ $this, 'render_overview_page' ]
		);
	}

	public function register_ct_meta_boxes( $post_type, $post ): void {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$supported_target_types = $this->get_supported_target_types();
		if ( ! in_array( (string) $post_type, $supported_target_types, true ) ) {
			return;
		}

		$ct_post_id = (string) get_post_meta( $post->ID, ChurchTools_Suite_Posts_Sync_Service::META_CT_POST_ID, true );
		if ( $ct_post_id === '' ) {
			return;
		}

		add_meta_box(
			'cts-posts-sync-meta-box',
			__( 'ChurchTools Metadaten', 'churchtools-suite-posts-sync' ),
			[ $this, 'render_ct_meta_box' ],
			(string) $post_type,
			'normal',
			'default'
		);
	}

	public function render_ct_meta_box( WP_Post $post ): void {
		$all_meta = get_post_meta( $post->ID );
		if ( ! is_array( $all_meta ) ) {
			echo '<p>' . esc_html__( 'Keine ChurchTools Metadaten gefunden.', 'churchtools-suite-posts-sync' ) . '</p>';
			return;
		}

		$ct_meta = [];
		foreach ( $all_meta as $meta_key => $meta_values ) {
			if ( strpos( (string) $meta_key, '_cts_ct_' ) !== 0 ) {
				continue;
			}

			if ( ! is_array( $meta_values ) || $meta_values === [] ) {
				$ct_meta[ $meta_key ] = '';
				continue;
			}

			$ct_meta[ $meta_key ] = maybe_unserialize( (string) $meta_values[0] );
		}

		if ( $ct_meta === [] ) {
			echo '<p>' . esc_html__( 'Keine ChurchTools Metadaten gefunden.', 'churchtools-suite-posts-sync' ) . '</p>';
			return;
		}

		ksort( $ct_meta );

		echo '<table class="widefat striped" style="margin-top:8px">';
		echo '<thead><tr><th style="width:32%">' . esc_html__( 'Meta-Key', 'churchtools-suite-posts-sync' ) . '</th><th>' . esc_html__( 'Wert', 'churchtools-suite-posts-sync' ) . '</th></tr></thead><tbody>';

		foreach ( $ct_meta as $meta_key => $meta_value ) {
			echo '<tr>';
			echo '<td><code>' . esc_html( (string) $meta_key ) . '</code></td>';
			echo '<td><textarea readonly="readonly" rows="3" style="width:100%;font-family:monospace">' . esc_textarea( $this->format_meta_value_for_display( $meta_value ) ) . '</textarea></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	public function save_posts_settings( $raw_post_data = [] ) {
		if ( ! is_admin() || ! current_user_can( 'manage_churchtools_suite' ) ) {
			return;
		}

		$post_data = is_array( $raw_post_data ) ? $raw_post_data : [];

		if ( ! $this->is_local_environment() ) {
			return;
		}

		$enabled = isset( $post_data['ct_posts_sync_enabled'] ) ? 1 : 0;
		$target_type = isset( $post_data['ct_posts_target_type'] ) ? sanitize_key( wp_unslash( (string) $post_data['ct_posts_target_type'] ) ) : ( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' );
		$target_status = isset( $post_data['ct_posts_target_status'] ) ? sanitize_key( wp_unslash( (string) $post_data['ct_posts_target_status'] ) ) : 'draft';
		$sync_limit = isset( $post_data['ct_posts_sync_limit'] ) ? absint( wp_unslash( (string) $post_data['ct_posts_sync_limit'] ) ) : 200;
		$after_date = isset( $post_data['ct_posts_after_date'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_after_date'] ) ) : '';
		$after_time = isset( $post_data['ct_posts_after_time'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_after_time'] ) ) : '';
		$before_date = isset( $post_data['ct_posts_before_date'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_before_date'] ) ) : '';
		$before_time = isset( $post_data['ct_posts_before_time'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_before_time'] ) ) : '';

		$after_local = isset( $post_data['ct_posts_after_local'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_after_local'] ) ) : '';
		$before_local = isset( $post_data['ct_posts_before_local'] ) ? sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_before_local'] ) ) : '';

		if ( $after_local === '' && $after_date !== '' ) {
			$after_local = $after_date . 'T' . ( $after_time !== '' ? $after_time : '00:00' );
		}

		if ( $before_local === '' && $before_date !== '' ) {
			$before_local = $before_date . 'T' . ( $before_time !== '' ? $before_time : '23:59' );
		}

		$after = $this->normalize_local_datetime_to_utc_iso( $after_local );
		$before = $this->normalize_local_datetime_to_utc_iso( $before_local );

		if ( $after === '' && isset( $post_data['ct_posts_after'] ) ) {
			$after = sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_after'] ) );
		}
		if ( $before === '' && isset( $post_data['ct_posts_before'] ) ) {
			$before = sanitize_text_field( wp_unslash( (string) $post_data['ct_posts_before'] ) );
		}
		$campus_ids = isset( $post_data['ct_posts_campus_ids'] ) ? $this->sanitize_id_list_input( wp_unslash( (string) $post_data['ct_posts_campus_ids'] ) ) : '';
		$actor_ids = isset( $post_data['ct_posts_actor_ids'] ) ? $this->sanitize_id_list_input( wp_unslash( (string) $post_data['ct_posts_actor_ids'] ) ) : '';
		$group_ids = '';
		if ( isset( $post_data['ct_posts_group_ids_select'] ) && is_array( $post_data['ct_posts_group_ids_select'] ) ) {
			$group_ids = $this->sanitize_id_list_from_array( wp_unslash( $post_data['ct_posts_group_ids_select'] ) );
		} elseif ( isset( $post_data['ct_posts_group_ids'] ) ) {
			$group_ids = $this->sanitize_id_list_input( wp_unslash( (string) $post_data['ct_posts_group_ids'] ) );
		}
		$group_visibility = isset( $post_data['ct_posts_group_visibility'] ) ? sanitize_key( wp_unslash( (string) $post_data['ct_posts_group_visibility'] ) ) : '';
		$post_visibility = isset( $post_data['ct_posts_post_visibility'] ) ? sanitize_key( wp_unslash( (string) $post_data['ct_posts_post_visibility'] ) ) : '';
		$only_my_groups = isset( $post_data['ct_posts_only_my_groups'] ) ? 1 : 0;
		$include_comments = isset( $post_data['ct_posts_include_comments'] ) ? 1 : 0;
		$include_linkings = isset( $post_data['ct_posts_include_linkings'] ) ? 1 : 0;
		$include_reactions = isset( $post_data['ct_posts_include_reactions'] ) ? 1 : 0;

		$supported_target_types = $this->get_supported_target_types();

		if ( ! in_array( $target_type, $supported_target_types, true ) ) {
			$target_type = in_array( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post', $supported_target_types, true ) ? ( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' ) : 'post';
		}

		if ( ! in_array( $target_status, [ 'draft', 'publish', 'private' ], true ) ) {
			$target_status = 'draft';
		}

		if ( $sync_limit < 1 || $sync_limit > 1000 ) {
			$sync_limit = 200;
		}

		if ( ! in_array( $group_visibility, [ '', 'hidden', 'intern', 'public' ], true ) ) {
			$group_visibility = '';
		}

		if ( ! in_array( $post_visibility, [ '', 'group_visible', 'group_intern', 'public' ], true ) ) {
			$post_visibility = '';
		}

		update_option( 'churchtools_suite_ct_posts_sync_enabled', $enabled, false );
		update_option( 'churchtools_suite_ct_posts_target_type', $target_type, false );
		update_option( 'churchtools_suite_ct_posts_target_status', $target_status, false );
		update_option( 'churchtools_suite_ct_posts_sync_limit', $sync_limit, false );
		update_option( 'churchtools_suite_ct_posts_after', $after, false );
		update_option( 'churchtools_suite_ct_posts_before', $before, false );
		update_option( 'churchtools_suite_ct_posts_campus_ids', $campus_ids, false );
		update_option( 'churchtools_suite_ct_posts_actor_ids', $actor_ids, false );
		update_option( 'churchtools_suite_ct_posts_group_ids', $group_ids, false );
		update_option( 'churchtools_suite_ct_posts_group_visibility', $group_visibility, false );
		update_option( 'churchtools_suite_ct_posts_post_visibility', $post_visibility, false );
		update_option( 'churchtools_suite_ct_posts_only_my_groups', $only_my_groups, false );
		update_option( 'churchtools_suite_ct_posts_include_comments', $include_comments, false );
		update_option( 'churchtools_suite_ct_posts_include_linkings', $include_linkings, false );
		update_option( 'churchtools_suite_ct_posts_include_reactions', $include_reactions, false );

		if ( isset( $post_data['ct_posts_sync_groups_now'] ) ) {
			$groups_sync_result = $this->sync_groups_cache_from_api();
			update_option( 'churchtools_suite_ct_posts_groups_sync_feedback', $groups_sync_result, false );
		}
	}

	public function render_posts_settings() {
		$groups_sync_feedback = get_option( 'churchtools_suite_ct_posts_groups_sync_feedback', null );
		if ( is_array( $groups_sync_feedback ) ) {
			$feedback_status = isset( $groups_sync_feedback['status'] ) ? (string) $groups_sync_feedback['status'] : '';
			$feedback_message = isset( $groups_sync_feedback['message'] ) ? (string) $groups_sync_feedback['message'] : '';

			$notice_class = 'cts-notice-info';
			if ( $feedback_status === 'success' ) {
				$notice_class = 'cts-notice-success';
			} elseif ( $feedback_status === 'error' ) {
				$notice_class = 'cts-notice-error';
			}

			if ( $feedback_message !== '' ) {
				echo '<div class="cts-notice ' . esc_attr( $notice_class ) . '"><p>' . esc_html( $feedback_message ) . '</p></div>';
			}

			delete_option( 'churchtools_suite_ct_posts_groups_sync_feedback' );
		}

		$enabled = (int) get_option( 'churchtools_suite_ct_posts_sync_enabled', 0 );
		$target_type = (string) get_option( 'churchtools_suite_ct_posts_target_type', defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' );
		$target_status = (string) get_option( 'churchtools_suite_ct_posts_target_status', 'draft' );
		$sync_limit = (int) get_option( 'churchtools_suite_ct_posts_sync_limit', 200 );
		$after = (string) get_option( 'churchtools_suite_ct_posts_after', '' );
		$before = (string) get_option( 'churchtools_suite_ct_posts_before', '' );
		$after_parts = $this->get_local_datetime_parts( $after );
		$before_parts = $this->get_local_datetime_parts( $before );
		$after_date_local = $after_parts['date'];
		$after_time_local = $after_parts['time'];
		$before_date_local = $before_parts['date'];
		$before_time_local = $before_parts['time'];
		$campus_ids = (string) get_option( 'churchtools_suite_ct_posts_campus_ids', '' );
		$actor_ids = (string) get_option( 'churchtools_suite_ct_posts_actor_ids', '' );
		$group_ids = (string) get_option( 'churchtools_suite_ct_posts_group_ids', '' );
		$selected_group_ids = $this->parse_group_ids( $group_ids );
		$groups_cache = get_option( 'churchtools_suite_ct_posts_groups_cache', [] );
		$groups_cache = is_array( $groups_cache ) ? $groups_cache : [];
		$groups_last_sync = (string) get_option( 'churchtools_suite_ct_posts_groups_last_sync', '' );
		$group_visibility = (string) get_option( 'churchtools_suite_ct_posts_group_visibility', '' );
		$post_visibility = (string) get_option( 'churchtools_suite_ct_posts_post_visibility', '' );
		$only_my_groups = (int) get_option( 'churchtools_suite_ct_posts_only_my_groups', 0 );
		$include_comments = (int) get_option( 'churchtools_suite_ct_posts_include_comments', 0 );
		$include_linkings = (int) get_option( 'churchtools_suite_ct_posts_include_linkings', 0 );
		$include_reactions = (int) get_option( 'churchtools_suite_ct_posts_include_reactions', 0 );
		$is_local_environment = $this->is_local_environment();
		$supported_target_types = $this->get_supported_target_types();

		if ( $sync_limit < 1 || $sync_limit > 1000 ) {
			$sync_limit = 200;
		}

		echo '<div class="cts-card cts-mt-20">';
		echo '<div class="cts-card-header">';
		echo '<span class="cts-card-icon">📝</span>';
		echo '<h3>' . esc_html__( 'Berichte Konfiguration (Addon)', 'churchtools-suite-posts-sync' ) . '</h3>';
		echo '</div>';
		echo '<div class="cts-posts-sync-settings-intro">' . esc_html__( 'Hier steuerst du den ChurchTools Berichte-Sync inklusive Zieltyp, Filter und API-Optionen.', 'churchtools-suite-posts-sync' ) . '</div>';

		if ( ! $is_local_environment ) {
			echo '<p class="cts-form-description cts-posts-sync-settings-notice">' . esc_html__( 'Berichte-Sync ist nur in lokaler Umgebung konfigurierbar.', 'churchtools-suite-posts-sync' ) . '</p>';
		}

		echo '<table class="cts-form-table">';
		echo '<tr class="cts-posts-sync-section-row"><th colspan="2">' . esc_html__( 'Allgemein', 'churchtools-suite-posts-sync' ) . '</th></tr>';
		echo '<tr><th scope="row"><label for="ct_posts_sync_enabled">' . esc_html__( 'Berichte-Sync aktivieren', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<label class="cts-toggle">';
		echo '<input type="checkbox" id="ct_posts_sync_enabled" name="ct_posts_sync_enabled" value="1" ' . checked( $enabled, 1, false ) . disabled( ! $is_local_environment, true, false ) . ' />';
		echo '<span class="cts-toggle-slider"></span></label>';
		echo '<span class="cts-form-description">' . esc_html__( 'Synchronisiert ChurchTools-Berichte zusätzlich zur Event-Synchronisation.', 'churchtools-suite-posts-sync' ) . '</span></td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_target_type">' . esc_html__( 'Ziel in WordPress', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<select id="ct_posts_target_type" name="ct_posts_target_type" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>';
		if ( in_array( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post', $supported_target_types, true ) ) {
			echo '<option value="' . esc_attr( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' ) . '" ' . selected( $target_type, defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post', false ) . '>' . esc_html__( 'ChurchTools Berichte (CPT)', 'churchtools-suite-posts-sync' ) . '</option>';
		}
		echo '<option value="post" ' . selected( $target_type, 'post', false ) . '>' . esc_html__( 'Beiträge', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '<option value="page" ' . selected( $target_type, 'page', false ) . '>' . esc_html__( 'Seiten', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_target_status">' . esc_html__( 'Status der Zielinhalte', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<select id="ct_posts_target_status" name="ct_posts_target_status" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>';
		echo '<option value="draft" ' . selected( $target_status, 'draft', false ) . '>' . esc_html__( 'Entwurf', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '<option value="publish" ' . selected( $target_status, 'publish', false ) . '>' . esc_html__( 'Veröffentlicht', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '<option value="private" ' . selected( $target_status, 'private', false ) . '>' . esc_html__( 'Privat', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_sync_limit">' . esc_html__( 'Sync-Limit', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<input type="number" min="1" max="1000" id="ct_posts_sync_limit" name="ct_posts_sync_limit" value="' . esc_attr( (string) $sync_limit ) . '" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-small" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '<span class="cts-form-description">' . esc_html__( 'Maximale Anzahl ChurchTools-Berichte pro Sync-Lauf.', 'churchtools-suite-posts-sync' ) . '</span></td></tr>';

		echo '<tr class="cts-posts-sync-section-row"><th colspan="2">' . esc_html__( 'Filter', 'churchtools-suite-posts-sync' ) . '</th></tr>';
		echo '<tr><th scope="row"><label for="ct_posts_after_date">' . esc_html__( 'Zeitraum ab (lokale Zeit)', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<div class="cts-posts-sync-datetime-row">';
		echo '<input type="date" id="ct_posts_after_date" name="ct_posts_after_date" value="' . esc_attr( $after_date_local ) . '" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '<input type="time" id="ct_posts_after_time" name="ct_posts_after_time" value="' . esc_attr( $after_time_local ) . '" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-small" step="60" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '</div>';
		echo '<span class="cts-form-description">' . esc_html__( 'Eingabe in lokaler WordPress-Zeit; Speicherung als UTC für die ChurchTools API.', 'churchtools-suite-posts-sync' ) . '</span>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_before_date">' . esc_html__( 'Zeitraum bis (lokale Zeit)', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<div class="cts-posts-sync-datetime-row">';
		echo '<input type="date" id="ct_posts_before_date" name="ct_posts_before_date" value="' . esc_attr( $before_date_local ) . '" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '<input type="time" id="ct_posts_before_time" name="ct_posts_before_time" value="' . esc_attr( $before_time_local ) . '" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-small" step="60" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '</div>';
		echo '<span class="cts-form-description">' . esc_html__( 'Eingabe in lokaler WordPress-Zeit; Speicherung als UTC für die ChurchTools API.', 'churchtools-suite-posts-sync' ) . '</span>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_group_ids">' . esc_html__( 'Post-Gruppen-IDs', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<div class="cts-posts-sync-group-sync-row">';
		echo '<button type="submit" name="ct_posts_sync_groups_now" value="1" class="button" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>' . esc_html__( 'Post-Gruppen jetzt synchronisieren', 'churchtools-suite-posts-sync' ) . '</button>';
		if ( $groups_last_sync !== '' ) {
			echo '<span class="cts-form-description">' . esc_html__( 'Letzter Post-Gruppen-Sync:', 'churchtools-suite-posts-sync' ) . ' ' . esc_html( $groups_last_sync ) . '</span>';
		}
		echo '</div>';

		echo '<select id="ct_posts_group_ids_select" name="ct_posts_group_ids_select[]" multiple="multiple" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium cts-posts-sync-multiselect" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>';
		foreach ( $groups_cache as $group_item ) {
			if ( ! is_array( $group_item ) ) {
				continue;
			}

			$group_id = isset( $group_item['id'] ) ? (int) $group_item['id'] : 0;
			$group_title = isset( $group_item['title'] ) ? (string) $group_item['title'] : '';
			if ( $group_id <= 0 || $group_title === '' ) {
				continue;
			}

			$group_visibility_item = isset( $group_item['visibility'] ) ? (string) $group_item['visibility'] : '';
			$label = $group_title . ' (#' . $group_id . ')';
			if ( $group_visibility_item !== '' ) {
				$label .= ' [' . $group_visibility_item . ']';
			}

			echo '<option value="' . esc_attr( (string) $group_id ) . '" ' . selected( in_array( $group_id, $selected_group_ids, true ), true, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '<input type="hidden" name="ct_posts_group_ids" value="' . esc_attr( $group_ids ) . '" />';
		echo '<span class="cts-form-description">' . esc_html__( 'Erst Post-Gruppen synchronisieren, dann gewünschte Post-Gruppen auswählen.', 'churchtools-suite-posts-sync' ) . '</span>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_campus_ids">' . esc_html__( 'Campus-IDs', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<input type="text" id="ct_posts_campus_ids" name="ct_posts_campus_ids" value="' . esc_attr( $campus_ids ) . '" placeholder="1,2" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_actor_ids">' . esc_html__( 'Personen-IDs', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<input type="text" id="ct_posts_actor_ids" name="ct_posts_actor_ids" value="' . esc_attr( $actor_ids ) . '" placeholder="187" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' />';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_group_visibility">' . esc_html__( 'Post-Gruppen-Sichtbarkeit', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<select id="ct_posts_group_visibility" name="ct_posts_group_visibility" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>';
		echo '<option value="" ' . selected( $group_visibility, '', false ) . '>' . esc_html__( 'Alle', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '<option value="hidden" ' . selected( $group_visibility, 'hidden', false ) . '>hidden</option>';
		echo '<option value="intern" ' . selected( $group_visibility, 'intern', false ) . '>intern</option>';
		echo '<option value="public" ' . selected( $group_visibility, 'public', false ) . '>public</option>';
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="ct_posts_post_visibility">' . esc_html__( 'Bericht-Sichtbarkeit', 'churchtools-suite-posts-sync' ) . '</label></th><td>';
		echo '<select id="ct_posts_post_visibility" name="ct_posts_post_visibility" class="cts-form-input cts-posts-sync-field cts-posts-sync-field-medium" ' . disabled( ! $is_local_environment || ! $enabled, true, false ) . '>';
		echo '<option value="" ' . selected( $post_visibility, '', false ) . '>' . esc_html__( 'Alle', 'churchtools-suite-posts-sync' ) . '</option>';
		echo '<option value="group_visible" ' . selected( $post_visibility, 'group_visible', false ) . '>group_visible</option>';
		echo '<option value="group_intern" ' . selected( $post_visibility, 'group_intern', false ) . '>group_intern</option>';
		echo '<option value="public" ' . selected( $post_visibility, 'public', false ) . '>public</option>';
		echo '</select></td></tr>';

		echo '<tr class="cts-posts-sync-section-row"><th colspan="2">' . esc_html__( 'API Includes', 'churchtools-suite-posts-sync' ) . '</th></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Zusätzliche API-Optionen', 'churchtools-suite-posts-sync' ) . '</th><td>';
		echo '<div class="cts-posts-sync-checkbox-list">';
		echo '<label><input type="checkbox" name="ct_posts_only_my_groups" value="1" ' . checked( $only_my_groups, 1, false ) . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' /> <span>only_my_groups</span></label>';
		echo '<label><input type="checkbox" name="ct_posts_include_comments" value="1" ' . checked( $include_comments, 1, false ) . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' /> <span>include[]=comments</span></label>';
		echo '<label><input type="checkbox" name="ct_posts_include_linkings" value="1" ' . checked( $include_linkings, 1, false ) . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' /> <span>include[]=linkings</span></label>';
		echo '<label><input type="checkbox" name="ct_posts_include_reactions" value="1" ' . checked( $include_reactions, 1, false ) . disabled( ! $is_local_environment || ! $enabled, true, false ) . ' /> <span>include[]=reactions</span></label>';
		echo '</div>';
		echo '</td></tr>';
		echo '</table>';
		echo '</div>';
	}

	public function render_overview_page() {
		$sync_enabled = (bool) get_option( 'churchtools_suite_ct_posts_sync_enabled', 0 );
		$target_type = (string) get_option( 'churchtools_suite_ct_posts_target_type', defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' );
		$last_result = get_option( 'churchtools_suite_posts_sync_last_result', [] );
		$is_local_environment = $this->is_local_environment();
		$supported_target_types = $this->get_supported_target_types();

		$posts = get_posts(
			[
				'post_type' => in_array( $target_type, $supported_target_types, true ) ? $target_type : ( defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' ),
				'post_status' => [ 'publish', 'draft', 'private', 'pending', 'future' ],
				'numberposts' => 200,
				'meta_key' => ChurchTools_Suite_Posts_Sync_Service::META_CT_POST_ID,
				'orderby' => 'date',
				'order' => 'DESC',
				'suppress_filters' => true,
			]
		);

		require CTS_POSTS_SYNC_PATH . 'admin/views/page-posts-overview.php';
	}

	public function ajax_posts_sync_run_now() {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		$nonce_ok = check_ajax_referer( 'churchtools_suite_admin', 'nonce', false );
		if ( $nonce_ok === false ) {
			wp_send_json_error( [ 'message' => __( 'Sicherheitsprüfung fehlgeschlagen.', 'churchtools-suite-posts-sync' ) ] );
			return;
		}

		if ( ! current_user_can( 'manage_churchtools_suite' ) ) {
			wp_send_json_error( [ 'message' => __( 'Keine Berechtigung.', 'churchtools-suite-posts-sync' ) ] );
			return;
		}

		if ( ! $this->is_local_environment() ) {
			wp_send_json_error( [ 'message' => __( 'Berichte-Sync ist nur in lokaler Umgebung ausführbar.', 'churchtools-suite-posts-sync' ) ] );
			return;
		}

		try {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			require_once CTS_POSTS_SYNC_PATH . 'includes/class-cts-posts-sync-service.php';

			$limit = (int) get_option( 'churchtools_suite_ct_posts_sync_limit', 200 );
			if ( $limit < 1 || $limit > 1000 ) {
				$limit = 200;
			}

			$client = new ChurchTools_Suite_CT_Client();
			$service = new ChurchTools_Suite_Posts_Sync_Service( $client );
			$result = $service->sync_posts( [ 'limit' => $limit ] );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				return;
			}

			if ( ! is_array( $result ) ) {
				wp_send_json_error( [ 'message' => __( 'Unerwartetes Sync-Ergebnis.', 'churchtools-suite-posts-sync' ) ] );
				return;
			}

			update_option( 'churchtools_suite_posts_sync_last_result', [
				'run_at' => current_time( 'mysql' ),
				'stats' => $result,
			], false );

			wp_send_json_success( [
				'message' => __( 'Berichte-Sync erfolgreich ausgeführt.', 'churchtools-suite-posts-sync' ),
				'stats' => $result,
			] );
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'message' => __( 'Fehler beim Berichte-Sync: ', 'churchtools-suite-posts-sync' ) . $e->getMessage() ] );
		}
	}

	private function is_local_environment(): bool {
		if ( defined( 'CTS_POSTS_SYNC_FORCE_ENABLE' ) && CTS_POSTS_SYNC_FORCE_ENABLE ) {
			return true;
		}

		$env_type = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : '';
		$home_host = parse_url( home_url(), PHP_URL_HOST );
		$home_host = is_string( $home_host ) ? strtolower( $home_host ) : '';
		$is_local_host = $home_host !== '' && ( in_array( $home_host, [ 'localhost', '127.0.0.1', '::1' ], true ) || preg_match( '/\.(test|local|localhost)$/', $home_host ) );

		$is_allowed = in_array( (string) $env_type, [ 'local', 'development', 'staging' ], true ) || (bool) $is_local_host;

		return (bool) apply_filters( 'cts_posts_sync_is_allowed_environment', $is_allowed, (string) $env_type, $home_host );
	}

	private function sanitize_id_list_input( string $input ): string {
		$parts = preg_split( '/[^0-9]+/', $input );
		if ( ! is_array( $parts ) ) {
			return '';
		}

		$ids = [];
		foreach ( $parts as $part ) {
			$part = trim( (string) $part );
			if ( $part === '' ) {
				continue;
			}
			$value = (int) $part;
			if ( $value > 0 ) {
				$ids[] = $value;
			}
		}

		$ids = array_values( array_unique( $ids ) );
		return implode( ',', $ids );
	}

	private function sanitize_id_list_from_array( $values ): string {
		if ( ! is_array( $values ) ) {
			return '';
		}

		$ids = [];
		foreach ( $values as $value ) {
			$id = (int) $value;
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		$ids = array_values( array_unique( $ids ) );
		return implode( ',', $ids );
	}

	private function parse_group_ids( string $raw_group_ids ): array {
		if ( trim( $raw_group_ids ) === '' ) {
			return [];
		}

		$parts = preg_split( '/[^0-9]+/', $raw_group_ids );
		if ( ! is_array( $parts ) ) {
			return [];
		}

		$ids = [];
		foreach ( $parts as $part ) {
			$id = (int) $part;
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Public contract wrapper for group source sync.
	 *
	 * @return array<string, mixed>
	 */
	public function run_groups_source_sync(): array {
		return $this->sync_groups_cache_from_api();
	}

	private function sync_groups_cache_from_api(): array {
		try {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-ct-client.php';
			$client = new ChurchTools_Suite_CT_Client();

			$response = $client->api_request( 'post/groups', 'GET', [] );
			if ( is_wp_error( $response ) ) {
				$response = $client->api_request( 'groups', 'GET', [ 'limit' => 1000 ] );
			}
			if ( is_wp_error( $response ) ) {
				return [
					'status' => 'error',
					'message' => sprintf(
						/* translators: %s: API error message */
						__( 'Gruppen-Sync fehlgeschlagen: %s', 'churchtools-suite-posts-sync' ),
						$response->get_error_message()
					),
				];
			}

			$groups = $this->extract_groups_from_response( is_array( $response ) ? $response : [] );
			if ( empty( $groups ) ) {
				return [
					'status' => 'warning',
					'message' => __( 'Gruppen-Sync abgeschlossen, aber keine Gruppen in der API-Antwort gefunden.', 'churchtools-suite-posts-sync' ),
				];
			}

			$cache = [];
			foreach ( $groups as $group ) {
				if ( ! is_array( $group ) ) {
					continue;
				}

				$group_data = isset( $group['group'] ) && is_array( $group['group'] ) ? $group['group'] : $group;
				$group_id = (int) ( $group_data['domainIdentifier'] ?? $group_data['id'] ?? 0 );
				$group_title = trim( (string) ( $group_data['title'] ?? $group_data['name'] ?? '' ) );
				if ( $group_id <= 0 || $group_title === '' ) {
					continue;
				}

				$visibility = (string) ( $group_data['domainAttributes']['visibility'] ?? '' );
				$cache[] = [
					'id' => $group_id,
					'title' => $group_title,
					'visibility' => $visibility,
				];

				ChurchTools_Suite_Posts_Sync_Service::ensure_group_category_term( (string) $group_id, $group_title );
			}

			if ( ! empty( $cache ) ) {
				$cache = array_values(
					array_reduce(
						$cache,
						static function ( array $carry, array $item ): array {
							$id = (int) ( $item['id'] ?? 0 );
							if ( $id > 0 ) {
								$carry[ $id ] = $item;
							}
							return $carry;
						},
						[]
					)
				);

				usort(
					$cache,
					static function ( array $left, array $right ): int {
						return strcasecmp( (string) ( $left['title'] ?? '' ), (string) ( $right['title'] ?? '' ) );
					}
				);

				update_option( 'churchtools_suite_ct_posts_groups_cache', $cache, false );
				update_option( 'churchtools_suite_ct_posts_groups_last_sync', current_time( 'mysql' ), false );

				return [
					'status' => 'success',
					'message' => sprintf(
						/* translators: %d: count of synced groups */
						__( 'Gruppen-Sync erfolgreich: %d Gruppen geladen.', 'churchtools-suite-posts-sync' ),
						count( $cache )
					),
				];
			}

			return [
				'status' => 'warning',
				'message' => __( 'Gruppen-Sync abgeschlossen, aber keine gültigen Gruppen verarbeitet.', 'churchtools-suite-posts-sync' ),
			];
		} catch ( Exception $e ) {
			return [
				'status' => 'error',
				'message' => sprintf(
					/* translators: %s: exception message */
					__( 'Gruppen-Sync fehlgeschlagen: %s', 'churchtools-suite-posts-sync' ),
					$e->getMessage()
				),
			];
		}
	}

	private function extract_groups_from_response( array $response ): array {
		if ( isset( $response['data'] ) && is_array( $response['data'] ) ) {
			$data = $response['data'];
			if ( $this->is_numeric_array( $data ) ) {
				return $data;
			}

			foreach ( [ 'groups', 'items', 'results' ] as $key ) {
				if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
					return $data[ $key ];
				}
			}
		}

		foreach ( [ 'groups', 'items', 'results' ] as $key ) {
			if ( isset( $response[ $key ] ) && is_array( $response[ $key ] ) ) {
				return $response[ $key ];
			}
		}

		return [];
	}

	private function is_numeric_array( array $array ): bool {
		if ( $array === [] ) {
			return true;
		}

		return array_keys( $array ) === range( 0, count( $array ) - 1 );
	}

	private function get_supported_target_types(): array {
		if ( class_exists( 'ChurchTools_Suite_Posts_Sync' ) && method_exists( 'ChurchTools_Suite_Posts_Sync', 'get_supported_target_types' ) ) {
			return ChurchTools_Suite_Posts_Sync::get_supported_target_types();
		}

		return [ 'post', 'page', defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' ];
	}

	private function normalize_local_datetime_to_utc_iso( string $value ): string {
		$value = trim( $value );
		if ( $value === '' ) {
			return '';
		}

		$formats = [ 'Y-m-d\TH:i', 'Y-m-d\TH:i:s' ];
		$local_timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );

		foreach ( $formats as $format ) {
			$dt = DateTimeImmutable::createFromFormat( $format, $value, $local_timezone );
			if ( $dt instanceof DateTimeImmutable ) {
				return $dt->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d\TH:i:s\Z' );
			}
		}

		return '';
	}

	private function get_local_datetime_parts( string $value ): array {
		$value = trim( $value );
		if ( $value === '' ) {
			return [
				'date' => '',
				'time' => '',
			];
		}

		try {
			$utc = new DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
			$local_timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
			$local = $utc->setTimezone( $local_timezone );
			return [
				'date' => $local->format( 'Y-m-d' ),
				'time' => $local->format( 'H:i' ),
			];
		} catch ( Exception $e ) {
			return [
				'date' => '',
				'time' => '',
			];
		}
	}

	private function format_meta_value_for_display( $meta_value ): string {
		if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
			$json = wp_json_encode( $meta_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			return is_string( $json ) ? $json : '';
		}

		$value = (string) $meta_value;
		$trimmed = trim( $value );
		if ( $trimmed === '' ) {
			return '';
		}

		$decoded = json_decode( $trimmed, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
			$json = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			return is_string( $json ) ? $json : $value;
		}

		return $value;
	}
}
