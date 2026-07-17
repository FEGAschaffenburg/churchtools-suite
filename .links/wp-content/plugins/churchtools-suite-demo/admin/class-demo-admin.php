<?php
/**
 * Demo Admin Panel
 *
 * Admin interface for managing demo user registrations.
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Admin {
	
	/**
	 * Demo Users Repository
	 *
	 * @var ChurchTools_Suite_Demo_Users_Repository
	 */
	private $repo;
	
	/**
	 * Constructor
	 *
	 * @param ChurchTools_Suite_Demo_Users_Repository $repo
	 */
	public function __construct( ChurchTools_Suite_Demo_Users_Repository $repo ) {
		$this->repo = $repo;
	}
	
	/**
	 * Initialize admin
	 */
	public function init(): void {
		// Add submenu to parent plugin
		add_action( 'admin_menu', [ $this, 'add_submenu' ], 20 );
		
		// Register AJAX handlers (User Management)
		add_action( 'wp_ajax_cts_demo_delete_user', [ $this, 'ajax_delete_user' ] );
		add_action( 'wp_ajax_cts_demo_export_users', [ $this, 'ajax_export_users' ] );
		add_action( 'wp_ajax_cts_demo_resend_email', [ $this, 'ajax_resend_email' ] );
		add_action( 'wp_ajax_cts_demo_manual_verify', [ $this, 'ajax_manual_verify' ] );
		
		// Register AJAX handlers (Settings Page - v1.1.2.0)
		add_action( 'wp_ajax_cts_demo_save_config', [ $this, 'ajax_save_config' ] );
		add_action( 'wp_ajax_cts_demo_check_updates', [ $this, 'ajax_check_updates' ] );
		add_action( 'wp_ajax_cts_demo_clear_update_cache', [ $this, 'ajax_clear_update_cache' ] );
		add_action( 'wp_ajax_cts_demo_get_version_history', [ $this, 'ajax_get_version_history' ] );
		add_action( 'wp_ajax_cts_demo_run_migrations', [ $this, 'ajax_run_migrations' ] );
		add_action( 'wp_ajax_cts_demo_migrate_users', [ $this, 'ajax_migrate_users' ] );
		
		// Register AJAX handlers (Cron - v1.1.4.0)
		add_action( 'wp_ajax_cts_demo_run_cleanup', [ $this, 'ajax_run_cleanup' ] );
	}
	
	/**
	 * Add submenu page
	 */
	public function add_submenu(): void {
		// Demo Users submenu (Admin only - v1.1.2.1)
		add_submenu_page(
			'edit.php?post_type=cts_demo_page',
			'Demo-Registrierungen',
			'Demo-Users',
			'manage_options', // Admin only (changed from manage_churchtools_suite)
			'churchtools-suite-demo',
			[ $this, 'render_admin_page' ]
		);
		
		// Settings submenu (v1.1.2.0 - Admin only)
		add_submenu_page(
			'edit.php?post_type=cts_demo_page',
			'Einstellungen',
			'Einstellungen',
			'manage_options', // Admin only
			'churchtools-suite-demo-settings',
			[ $this, 'render_settings_page' ]
		);
	}
	
	/**
	 * Render admin page
	 */
	public function render_admin_page(): void {
		// Get statistics
		$stats = $this->repo->get_statistics();
		
		// Get users (paginated)
		$page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$per_page = 50;
		$offset = ( $page - 1 ) * $per_page;
		
		$users = $this->repo->get_paginated( [
			'limit' => $per_page,
			'offset' => $offset,
			'orderby' => 'created_at',
			'order' => 'DESC',
		] );
		
		include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/demo-users.php';
	}
	
	/**
	 * AJAX: Delete demo user
	 */
	public function ajax_delete_user(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Ungültige ID' ] );
		}
		
		// Get user to delete WP user too
		$demo_user = $this->repo->get_by_id( $id );
		
		if ( ! $demo_user ) {
			wp_send_json_error( [ 'message' => 'Benutzer nicht gefunden' ] );
		}
		
		// Delete WP user if exists
		if ( $demo_user->wordpress_user_id ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $demo_user->wordpress_user_id );
		}
		
		// Delete demo user
		$this->repo->delete( $id );
		
		wp_send_json_success( [ 'message' => 'Benutzer gelöscht' ] );
	}
	
	/**
	 * AJAX: Export users to CSV
	 */
	public function ajax_export_users(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Keine Berechtigung' );
		}
		
		$users = $this->repo->get_all();
		
		// Set headers
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=demo-users-' . date( 'Y-m-d' ) . '.csv' );
		
		// Output CSV
		$output = fopen( 'php://output', 'w' );
		
		// Header row
		fputcsv( $output, [ 'ID', 'Email', 'Name', 'Firma/Gemeinde', 'Zweck', 'Verifiziert', 'Letzter Login', 'Registriert am' ] );
		
		// Data rows
		foreach ( $users as $user ) {
			fputcsv( $output, [
				$user->id,
				$user->email,
				$user->name,
				$user->company,
				$user->purpose,
				$user->verified_at ? 'Ja' : 'Nein',
				$user->last_login_at ?: '-',
				$user->created_at,
			] );
		}
		
		fclose( $output );
		exit;
	}
	
	/**
	 * AJAX: Resend verification email
	 */
	public function ajax_resend_email(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Ungültige ID' ] );
		}
		
		$user = $this->repo->get_by_id( $id );
		
		if ( ! $user ) {
			wp_send_json_error( [ 'message' => 'Benutzer nicht gefunden' ] );
		}
		
		if ( $user->verified_at ) {
			wp_send_json_error( [ 'message' => 'Benutzer ist bereits verifiziert' ] );
		}
		
		// Get registration service
		$registration_service = ChurchTools_Suite_Demo::instance()->registration_service;
		
		// Resend verification email
		$result = $registration_service->resend_verification_email( $user->email, $user->verification_token );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}
		
		wp_send_json_success( [ 'message' => 'Verifizierungs-E-Mail wurde erneut gesendet' ] );
	}
	
	/**
	 * AJAX: Manually verify user
	 */
	public function ajax_manual_verify(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Ungültige ID' ] );
		}
		
		$user = $this->repo->get_by_id( $id );
		
		if ( ! $user ) {
			wp_send_json_error( [ 'message' => 'Benutzer nicht gefunden' ] );
		}
		
		if ( $user->verified_at ) {
			wp_send_json_error( [ 'message' => 'Benutzer ist bereits verifiziert' ] );
		}
		
		// Get registration service
		$registration_service = ChurchTools_Suite_Demo::instance()->registration_service;
		
		// Manually verify (creates WP user)
		$result = $registration_service->verify_email( $user->verification_token );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}
		
		wp_send_json_success( [ 
			'message' => 'Benutzer wurde manuell verifiziert und WordPress-User erstellt',
			'wp_user_id' => $result['wp_user_id']
		] );
	}
	
	/**
	 * Render settings page (v1.1.2.0)
	 */
	public function render_settings_page(): void {
		include CHURCHTOOLS_SUITE_DEMO_PATH . 'admin/views/admin-settings.php';
	}
	
	/**
	 * AJAX: Save configuration (v1.1.4.0: Added BCC email and cron schedule update)
	 */
	public function ajax_save_config(): void {
		check_ajax_referer( 'cts_demo_save_config', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		$demo_duration = isset( $_POST['demo_duration'] ) ? absint( $_POST['demo_duration'] ) : 30;
		$auto_cleanup = isset( $_POST['auto_cleanup'] ) ? (bool) $_POST['auto_cleanup'] : false;
		$admin_notifications = isset( $_POST['admin_notifications'] ) ? (bool) $_POST['admin_notifications'] : false;
		$demo_user_limit = isset( $_POST['demo_user_limit'] ) ? absint( $_POST['demo_user_limit'] ) : 0;
		$bcc_email = isset( $_POST['bcc_email'] ) ? sanitize_email( $_POST['bcc_email'] ) : '';
		
		update_option( 'cts_demo_duration_days', $demo_duration );
		update_option( 'cts_demo_auto_cleanup', $auto_cleanup );
		update_option( 'cts_demo_admin_notifications', $admin_notifications );
		update_option( 'cts_demo_user_limit', $demo_user_limit );
		update_option( 'cts_demo_bcc_email', $bcc_email );
		
		// Update cron schedule based on auto_cleanup setting
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
		ChurchTools_Suite_Demo_Cron::update_cleanup_schedule();
		
		wp_send_json_success( [ 'message' => 'Einstellungen erfolgreich gespeichert' ] );
	}
	
	/**
	 * AJAX: Check for updates (v1.1.2.0)
	 */
	public function ajax_check_updates(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Get current version
		$current_version = CHURCHTOOLS_SUITE_DEMO_VERSION;
		
		// Fetch latest release from GitHub
		$response = wp_remote_get( 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite-demos/releases/latest', [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
			],
			'timeout' => 10,
		] );
		
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => 'Fehler beim Abrufen der GitHub API: ' . $response->get_error_message() ] );
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( ! isset( $data['tag_name'] ) ) {
			wp_send_json_error( [ 'message' => 'Ungültige Antwort von GitHub API' ] );
		}
		
		$latest_version = ltrim( $data['tag_name'], 'v' );
		$has_update = version_compare( $current_version, $latest_version, '<' );
		
		// Cache result
		set_transient( 'cts_demo_update_check', [
			'current_version' => $current_version,
			'latest_version' => $latest_version,
			'has_update' => $has_update,
		], 12 * HOUR_IN_SECONDS );
		
		wp_send_json_success( [
			'current_version' => $current_version,
			'latest_version' => $latest_version,
			'has_update' => $has_update,
			'message' => $has_update ? 'Update verfügbar!' : 'Plugin ist aktuell',
		] );
	}
	
	/**
	 * AJAX: Clear update cache (v1.1.2.0)
	 */
	public function ajax_clear_update_cache(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		delete_transient( 'cts_demo_update_check' );
		delete_transient( 'cts_demo_github_update' ); // Auto-updater transient
		
		wp_send_json_success( [ 'message' => 'Update-Cache wurde geleert' ] );
	}
	
	/**
	 * AJAX: Get version history (v1.1.2.0)
	 */
	public function ajax_get_version_history(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Fetch releases from GitHub
		$response = wp_remote_get( 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite-demos/releases', [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
			],
			'timeout' => 10,
		] );
		
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => 'Fehler beim Abrufen der GitHub API' ] );
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		$releases = [];
		foreach ( $data as $release ) {
			$releases[] = [
				'tag' => $release['tag_name'],
				'date' => date_i18n( get_option( 'date_format' ), strtotime( $release['published_at'] ) ),
				'description' => wp_trim_words( $release['name'], 10 ),
				'url' => $release['html_url'],
			];
		}
		
		wp_send_json_success( [ 'releases' => $releases ] );
	}
	
	/**
	 * AJAX: Run migrations (v1.1.2.0)
	 */
	public function ajax_run_migrations(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Capture error log
		$log = [];
		$error_handler = function( $message ) use ( &$log ) {
			$log[] = $message;
		};
		add_filter( 'error_log', $error_handler );
		
		// Run migrations
		ChurchTools_Suite_Demo_Migrations::run_migrations();
		
		remove_filter( 'error_log', $error_handler );
		
		$new_version = ChurchTools_Suite_Demo_Migrations::get_current_version();
		
		wp_send_json_success( [
			'message' => 'Migrationen erfolgreich ausgeführt',
			'version' => $new_version,
			'log' => $log,
		] );
	}
	
	/**
	 * AJAX: Migrate users (v1.1.2.0)
	 */
	public function ajax_migrate_users(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Get old role users
		$old_users = get_users( [ 'role' => 'cts_demo_user' ] );
		$migrated = 0;
		
		foreach ( $old_users as $user ) {
			$user->set_role( 'demo_tester' );
			$migrated++;
		}
		
		// Remove old role
		$old_role = get_role( 'cts_demo_user' );
		if ( $old_role ) {
			remove_role( 'cts_demo_user' );
		}
		
		wp_send_json_success( [
			'message' => sprintf( '%d Benutzer erfolgreich migriert', $migrated ),
			'migrated' => $migrated,
		] );
	}
	
	/**
	 * AJAX: Run cleanup manually (v1.1.4.0)
	 */
	public function ajax_run_cleanup(): void {
		check_ajax_referer( 'cts_demo_admin', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Keine Berechtigung' ] );
		}
		
		// Run cleanup
		require_once CHURCHTOOLS_SUITE_DEMO_PATH . 'includes/class-demo-cron.php';
		ChurchTools_Suite_Demo_Cron::cleanup_expired_demos();
		
		wp_send_json_success( [
			'message' => 'Bereinigung erfolgreich ausgeführt. Prüfen Sie das Server-Log für Details.',
		] );
	}
}
