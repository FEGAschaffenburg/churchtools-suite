<?php
/**
 * Sync Module Runtime (Status + Locks)
 *
 * @package ChurchTools_Suite
 * @since   1.2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Sync_Runtime {

	private const STATUS_OPTION_PREFIX = 'churchtools_suite_module_';
	private const STATUS_OPTION_SUFFIX = '_status';
	private const LOCK_TRANSIENT_PREFIX = 'cts_lock_module_';

	/**
	 * @return array<string, mixed>
	 */
	public static function get_default_status(): array {
		return [
			'state' => 'idle',
			'last_source_sync_at' => '',
			'last_selection_save_at' => '',
			'last_data_sync_at' => '',
			'last_result' => [],
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_module_status( string $module_id ): array {
		$module_id = sanitize_key( $module_id );
		if ( $module_id === '' ) {
			return self::get_default_status();
		}

		$status = get_option( self::get_status_option_name( $module_id ), [] );
		if ( ! is_array( $status ) ) {
			$status = [];
		}

		return self::normalize_status( $status );
	}

	/**
	 * @param array<string, mixed> $status
	 */
	public static function set_module_status( string $module_id, array $status ): void {
		$module_id = sanitize_key( $module_id );
		if ( $module_id === '' ) {
			return;
		}

		$normalized = self::normalize_status( $status );
		update_option( self::get_status_option_name( $module_id ), $normalized, false );
	}

	/**
	 * @param array<string, mixed> $result
	 */
	public static function record_result( string $module_id, string $action, array $result ): void {
		$module_id = sanitize_key( $module_id );
		$action = sanitize_key( $action );
		if ( $module_id === '' || $action === '' ) {
			return;
		}

		$status = self::get_module_status( $module_id );
		$normalized_result = self::normalize_result_payload( $result );

		if ( $action === 'source_sync' ) {
			$status['last_source_sync_at'] = current_time( 'mysql' );
		} elseif ( $action === 'selection_save' ) {
			$status['last_selection_save_at'] = current_time( 'mysql' );
		} elseif ( $action === 'data_sync' ) {
			$status['last_data_sync_at'] = current_time( 'mysql' );
		}

		$status['last_result'] = $normalized_result;
		$status['state'] = self::state_from_result_status( (string) ( $normalized_result['status'] ?? '' ) );

		self::set_module_status( $module_id, $status );
	}

	/**
	 * @param callable():mixed $callback
	 * @return mixed
	 */
	public static function run_locked_action( string $module_id, string $action, callable $callback, int $lock_timeout = 300 ) {
		$module_id = sanitize_key( $module_id );
		$action = sanitize_key( $action );

		if ( $module_id === '' || $action === '' ) {
			return new WP_Error( 'cts_invalid_module_action', __( 'Ungültiger Modulaufruf.', 'churchtools-suite' ) );
		}

		if ( ! self::acquire_lock( $module_id, $action, $lock_timeout ) ) {
			$locked_result = [
				'status' => 'locked',
				'message' => __( 'Für dieses Modul läuft bereits eine Synchronisation.', 'churchtools-suite' ),
			];
			self::record_result( $module_id, $action, $locked_result );
			return $locked_result;
		}

		try {
			$current_status = self::get_module_status( $module_id );
			$current_status['state'] = 'running';
			self::set_module_status( $module_id, $current_status );

			$result = $callback();

			if ( is_wp_error( $result ) ) {
				self::record_result(
					$module_id,
					$action,
					[
						'status' => 'error',
						'message' => $result->get_error_message(),
					]
				);
			} elseif ( is_array( $result ) ) {
				self::record_result( $module_id, $action, $result );
			} else {
				self::record_result(
					$module_id,
					$action,
					[
						'status' => 'success',
						'value' => $result,
					]
				);
			}

			return $result;
		} catch ( Exception $e ) {
			self::record_result(
				$module_id,
				$action,
				[
					'status' => 'error',
					'message' => $e->getMessage(),
				]
			);

			return new WP_Error( 'cts_module_runtime_exception', $e->getMessage() );
		} finally {
			self::release_lock( $module_id, $action );
		}
	}

	public static function acquire_lock( string $module_id, string $action, int $timeout = 300 ): bool {
		$module_id = sanitize_key( $module_id );
		$action = sanitize_key( $action );
		if ( $module_id === '' || $action === '' ) {
			return false;
		}

		$transient_key = self::get_lock_transient_name( $module_id, $action );
		$existing_lock = get_transient( $transient_key );
		if ( is_array( $existing_lock ) && ! empty( $existing_lock['started_at'] ) ) {
			return false;
		}

		$lock_payload = [
			'module_id' => $module_id,
			'action' => $action,
			'started_at' => current_time( 'mysql' ),
		];

		return set_transient( $transient_key, $lock_payload, max( 30, $timeout ) );
	}

	public static function release_lock( string $module_id, string $action ): void {
		$module_id = sanitize_key( $module_id );
		$action = sanitize_key( $action );
		if ( $module_id === '' || $action === '' ) {
			return;
		}

		delete_transient( self::get_lock_transient_name( $module_id, $action ) );
	}

	/**
	 * @param array<string, mixed> $status
	 * @return array<string, mixed>
	 */
	private static function normalize_status( array $status ): array {
		$defaults = self::get_default_status();
		$merged = array_merge( $defaults, $status );

		$state = isset( $merged['state'] ) ? sanitize_key( (string) $merged['state'] ) : 'idle';
		if ( ! in_array( $state, [ 'idle', 'running', 'error', 'ok', 'disabled' ], true ) ) {
			$state = 'idle';
		}

		$merged['state'] = $state;
		$merged['last_source_sync_at'] = (string) ( $merged['last_source_sync_at'] ?? '' );
		$merged['last_selection_save_at'] = (string) ( $merged['last_selection_save_at'] ?? '' );
		$merged['last_data_sync_at'] = (string) ( $merged['last_data_sync_at'] ?? '' );
		$merged['last_result'] = is_array( $merged['last_result'] ) ? $merged['last_result'] : [];

		return $merged;
	}

	/**
	 * @param array<string, mixed> $result
	 * @return array<string, mixed>
	 */
	private static function normalize_result_payload( array $result ): array {
		$status = isset( $result['status'] ) ? sanitize_key( (string) $result['status'] ) : 'success';
		if ( $status === '' ) {
			$status = 'success';
		}

		$payload = $result;
		$payload['status'] = $status;

		if ( isset( $payload['message'] ) ) {
			$payload['message'] = (string) $payload['message'];
		}

		$payload['recorded_at'] = current_time( 'mysql' );

		return $payload;
	}

	private static function state_from_result_status( string $result_status ): string {
		$result_status = sanitize_key( $result_status );

		if ( in_array( $result_status, [ 'error', 'failed', 'failure' ], true ) ) {
			return 'error';
		}

		if ( in_array( $result_status, [ 'success', 'ok' ], true ) ) {
			return 'ok';
		}

		if ( $result_status === 'disabled' ) {
			return 'disabled';
		}

		return 'idle';
	}

	private static function get_status_option_name( string $module_id ): string {
		return self::STATUS_OPTION_PREFIX . $module_id . self::STATUS_OPTION_SUFFIX;
	}

	private static function get_lock_transient_name( string $module_id, string $action ): string {
		return self::LOCK_TRANSIENT_PREFIX . $module_id . '_' . $action;
	}
}
