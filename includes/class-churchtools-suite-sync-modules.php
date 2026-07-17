<?php
/**
 * Sync Module Registry
 *
 * @package ChurchTools_Suite
 * @since   1.1.5.17
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Sync_Modules {

	/**
	 * Returns all registered sync modules.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_registered_modules(): array {
		$raw_modules = apply_filters( 'cts_register_sync_modules', [] );
		$raw_modules = self::append_core_modules( $raw_modules );

		if ( ! is_array( $raw_modules ) ) {
			return [];
		}

		$modules = [];
		foreach ( $raw_modules as $module_key => $module ) {
			if ( ! is_array( $module ) ) {
				continue;
			}

			$id = isset( $module['id'] ) ? sanitize_key( (string) $module['id'] ) : sanitize_key( (string) $module_key );
			if ( $id === '' ) {
				continue;
			}

			$callbacks = isset( $module['callbacks'] ) && is_array( $module['callbacks'] ) ? $module['callbacks'] : [];
			if ( $callbacks === [] ) {
				continue;
			}

			$modules[ $id ] = [
				'id' => $id,
				'label' => isset( $module['label'] ) ? (string) $module['label'] : ucfirst( $id ),
				'capability' => isset( $module['capability'] ) ? sanitize_key( (string) $module['capability'] ) : 'manage_churchtools_suite',
				'dependencies' => isset( $module['dependencies'] ) && is_array( $module['dependencies'] ) ? array_values( $module['dependencies'] ) : [],
				'callbacks' => $callbacks,
				'meta' => isset( $module['meta'] ) && is_array( $module['meta'] ) ? $module['meta'] : [],
			];
		}

		return $modules;
	}

	/**
	 * Ensures built-in core modules are always present.
	 *
	 * @param mixed $raw_modules Raw module list from filters.
	 * @return array<int|string, mixed>
	 */
	private static function append_core_modules( $raw_modules ): array {
		$modules = is_array( $raw_modules ) ? $raw_modules : [];

		if ( ! isset( $modules['events'] ) || ! is_array( $modules['events'] ) ) {
			$modules['events'] = [
				'id' => 'events',
				'label' => __( 'Termine', 'churchtools-suite' ),
				'capability' => 'manage_churchtools_suite',
				'dependencies' => [ 'ct_connection' ],
				'callbacks' => [
					'get_status' => [ __CLASS__, 'get_events_module_status' ],
				],
				'meta' => [
					'owner' => 'core',
					'settings_slug' => 'sync',
				],
			];
		}

		return $modules;
	}

	/**
	 * Returns a basic status snapshot for core events sync.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_events_module_status(): array {
		$last_events_sync = (string) get_option( 'churchtools_suite_events_last_sync', '' );
		$auto_sync_enabled = (bool) get_option( 'churchtools_suite_auto_sync_enabled', 0 );

		$state = $last_events_sync !== '' ? 'ok' : 'idle';
		$message = $last_events_sync !== ''
			? __( 'Termine wurden bereits synchronisiert.', 'churchtools-suite' )
			: __( 'Noch keine Termin-Synchronisation durchgeführt.', 'churchtools-suite' );

		if ( $auto_sync_enabled && $last_events_sync === '' ) {
			$message = __( 'Auto-Sync ist aktiv; erster Lauf steht noch aus.', 'churchtools-suite' );
		}

		return [
			'state' => $state,
			'enabled' => true,
			'last_source_sync_at' => '',
			'last_data_sync_at' => $last_events_sync,
			'last_result' => [
				'status' => $state,
				'message' => $message,
			],
		];
	}

	/**
	 * Returns status for one module based on callback + runtime fallback.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_module_status( string $module_id ): array {
		$module_id = sanitize_key( $module_id );
		if ( $module_id === '' ) {
			return [];
		}

		$modules = self::get_registered_modules();
		$module = $modules[ $module_id ] ?? null;
		$callback = is_array( $module ) && isset( $module['callbacks']['get_status'] ) ? $module['callbacks']['get_status'] : null;

		$status = [];
		if ( is_callable( $callback ) ) {
			$raw_status = call_user_func( $callback );
			if ( is_array( $raw_status ) ) {
				$status = $raw_status;
			}
		}

		if ( class_exists( 'ChurchTools_Suite_Sync_Runtime' ) ) {
			$runtime_status = ChurchTools_Suite_Sync_Runtime::get_module_status( $module_id );
			if ( is_array( $runtime_status ) ) {
				$status = array_merge( $runtime_status, $status );
			}
		}

		return $status;
	}
}
