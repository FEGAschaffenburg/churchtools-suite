<?php
/**
 * Addons Overview Page
 * 
 * Displays all installed ChurchTools Suite addon plugins.
 * 
 * @package ChurchTools_Suite
 * @since   1.0.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure plugin.php functions are available
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Resolve canonical addon metadata for monorepo setup.
 *
 * @param string $plugin_slug Addon slug
 * @param string $plugin_uri  Plugin URI from header
 * @param string $plugin_file Plugin file path key from get_plugins()
 * @return array{github_repo:string,details_url:string}
 */
function cts_resolve_addon_meta( $plugin_slug, $plugin_uri = '', $plugin_file = '' ) {
	$plugin_slug = (string) $plugin_slug;
	$plugin_file = (string) $plugin_file;

	if (
		strpos( $plugin_slug, 'churchtools-suite-elementor' ) === 0
		|| strpos( $plugin_file, '/churchtools-suite-elementor.php' ) !== false
	) {
		return [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor',
		];
	}

	if (
		strpos( $plugin_slug, 'churchtools-suite-posts-sync' ) === 0
		|| strpos( $plugin_file, '/churchtools-suite-posts-sync.php' ) !== false
	) {
		return [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-posts-sync',
		];
	}

	if (
		strpos( $plugin_slug, 'churchtools-suite-presentations' ) === 0
		|| strpos( $plugin_file, '/churchtools-suite-presentations.php' ) !== false
	) {
		return [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-presentations',
		];
	}

	$known_addons = [
		'churchtools-suite-elementor' => [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor',
		],
		'churchtools-suite-posts-sync' => [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-posts-sync',
		],
		'churchtools-suite-presentations' => [
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-presentations',
		],
	];

	if ( isset( $known_addons[ $plugin_slug ] ) ) {
		return $known_addons[ $plugin_slug ];
	}

	$github_repo = '';
	if ( ! empty( $plugin_uri ) && preg_match( '#github\.com/([^/]+/[^/]+)#', $plugin_uri, $matches ) ) {
		$github_repo = $matches[1];
	}

	return [
		'github_repo' => $github_repo,
		'details_url' => (string) $plugin_uri,
	];
}

/**
 * Check for addon updates via GitHub API
 * 
 * @param string $github_repo Repository in format 'owner/repo'
 * @param string $current_version Current plugin version
 * @param string $plugin_slug Addon plugin slug for asset prefix matching
 * @return array|false Update info or false if no update
 */
function cts_check_addon_update( $github_repo, $current_version, $plugin_slug = '' ) {
	if ( empty( $github_repo ) || empty( $current_version ) || empty( $plugin_slug ) ) {
		return false;
	}

	$current_clean = ltrim( (string) $current_version, 'vV' );
	
	// Check transient cache first (1 hour)
	$cache_key = 'cts_addon_update_' . sanitize_key( $github_repo . '_' . $plugin_slug . '_' . $current_clean );
	$cached = get_transient( $cache_key );
	if ( $cached !== false ) {
		if ( is_array( $cached ) && ! empty( $cached['latest_version'] ) ) {
			$cached_latest = ltrim( (string) $cached['latest_version'], 'vV' );
			if ( ! version_compare( $cached_latest, $current_clean, '>' ) ) {
				return false;
			}
		}
		return $cached;
	}
	
	$api_url = sprintf( 'https://api.github.com/repos/%s/releases/latest', $github_repo );
	$response = wp_remote_get( $api_url, [
		'timeout' => 10,
		'headers' => [ 'User-Agent' => 'ChurchTools-Suite-Addon-Updater' ]
	] );
	
	if ( is_wp_error( $response ) ) {
		return false;
	}
	
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	
	if ( empty( $data['tag_name'] ) ) {
		return false;
	}
	
	$release_version = ltrim( $data['tag_name'], 'v' );
	$current_clean = ltrim( $current_version, 'vV' );

	$asset_prefix = $plugin_slug . '-';

	// Find ZIP asset and derive addon version from asset name in monorepo releases
	$zip_url = '';
	$asset_version = '';
	if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
		foreach ( $data['assets'] as $asset ) {
			if ( isset( $asset['browser_download_url'], $asset['name'] )
				&& strpos( $asset['name'], $asset_prefix ) === 0
				&& str_ends_with( $asset['name'], '.zip' ) ) {
				$zip_url = $asset['browser_download_url'];

				if ( preg_match( '/^' . preg_quote( $plugin_slug, '/' ) . '-(.+)\\.zip$/', (string) $asset['name'], $matches ) ) {
					$asset_version = ltrim( (string) $matches[1], 'v' );
				}

				break;
			}
		}
	}

	if ( empty( $zip_url ) ) {
		set_transient( $cache_key, false, HOUR_IN_SECONDS );
		return false;
	}

	$latest_version = $asset_version !== '' ? $asset_version : $release_version;

	if ( ! version_compare( $latest_version, $current_clean, '>' ) ) {
		set_transient( $cache_key, false, HOUR_IN_SECONDS );
		return false;
	}

	$update_info = [
		'current_version' => $current_clean,
		'latest_version' => $latest_version,
		'tag_name' => $data['tag_name'],
		'zip_url' => $zip_url,
		'html_url' => $data['html_url'] ?? '',
		'body' => $data['body'] ?? '',
	];

	// Cache for 1 hour
	set_transient( $cache_key, $update_info, HOUR_IN_SECONDS );

	return $update_info;
	
	// No update available - cache for 1 hour
	set_transient( $cache_key, false, HOUR_IN_SECONDS );
	return false;
}

/**
 * Get all ChurchTools Suite addon plugins
 * 
 * Detects addons by:
 * - Plugin slug starts with 'churchtools-suite-'
 * - OR Plugin header contains 'Requires Plugins: churchtools-suite'
 * 
 * Excludes:
 * - Main plugin (churchtools-suite)
 * 
 * @return array Array of addon plugin data
 */
function cts_get_addon_plugins() {
	$all_plugins = get_plugins();
	$addons = [];
	
	foreach ( $all_plugins as $plugin_file => $plugin_data ) {
		$plugin_slug = dirname( $plugin_file );

		// Skip source-only addon plugins located inside the main plugin directory
		if ( strpos( (string) $plugin_file, 'churchtools-suite/addons/' ) === 0 ) {
			continue;
		}
		
		// Skip main plugin itself
		if ( $plugin_slug === 'churchtools-suite' || $plugin_file === 'churchtools-suite/churchtools-suite.php' ) {
			continue;
		}
		
		// Check if plugin is a ChurchTools Suite addon
		$is_addon = false;
		
		// Method 1: Plugin slug starts with 'churchtools-suite-'
		if ( strpos( $plugin_slug, 'churchtools-suite-' ) === 0 ) {
			$is_addon = true;
		}
		
		// Method 2: Plugin requires churchtools-suite (WordPress 6.5+ header)
		if ( ! empty( $plugin_data['RequiresPlugins'] ) && 
		     strpos( $plugin_data['RequiresPlugins'], 'churchtools-suite' ) !== false ) {
			$is_addon = true;
		}
		
		// Method 3: Plugin description mentions ChurchTools Suite
		if ( ! empty( $plugin_data['Description'] ) && 
		     ( stripos( $plugin_data['Description'], 'ChurchTools Suite' ) !== false ||
		       stripos( $plugin_data['Description'], 'churchtools-suite' ) !== false ) ) {
			$is_addon = true;
		}
		
		if ( $is_addon ) {
			$addon_meta = cts_resolve_addon_meta( $plugin_slug, $plugin_data['PluginURI'] ?? '', $plugin_file );
			
			$addons[ $plugin_file ] = array_merge( $plugin_data, [
				'plugin_file' => $plugin_file,
				'plugin_slug' => $plugin_slug,
				'is_active' => is_plugin_active( $plugin_file ),
				'is_network_active' => is_plugin_active_for_network( $plugin_file ),
				'github_repo' => $addon_meta['github_repo'],
				'details_url' => $addon_meta['details_url'],
			] );
		}
	}
	
	return $addons;
}

/**
 * Ensure top table contains all known addons, even if not installed.
 *
 * @param array $addons Detected addons from installed plugins
 * @return array
 */
function cts_ensure_known_addons_in_table( array $addons ): array {
	$known = [
		'churchtools-suite-elementor' => [
			'name' => 'ChurchTools Suite - Elementor Integration',
			'description' => 'Elementor Page Builder Widget für ChurchTools Suite Events.',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-elementor',
		],
		'churchtools-suite-posts-sync' => [
			'name' => 'ChurchTools Suite - Posts Sync Addon',
			'description' => 'Synchronisiert ChurchTools-Posts in WordPress-Posts/Seiten.',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-posts-sync',
		],
		'churchtools-suite-presentations' => [
			'name' => 'ChurchTools Suite - Präsentation Addon',
			'description' => 'Präsentationsseiten für ChurchTools Termine und Informationen.',
			'details_url' => 'https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/addons/churchtools-suite-presentations',
		],
	];

	$existing_slugs = array_map(
		static function( $addon ) {
			return (string) ( $addon['plugin_slug'] ?? '' );
		},
		$addons
	);

	foreach ( $known as $slug => $meta ) {
		if ( in_array( $slug, $existing_slugs, true ) ) {
			continue;
		}

		$addons[ '__virtual__' . $slug ] = [
			'Name' => $meta['name'],
			'Description' => $meta['description'],
			'Version' => '—',
			'plugin_file' => '',
			'plugin_slug' => $slug,
			'is_active' => false,
			'is_network_active' => false,
			'github_repo' => 'FEGAschaffenburg/churchtools-suite',
			'details_url' => $meta['details_url'],
			'is_virtual' => true,
		];
	}

	return $addons;
}

$addons = cts_ensure_known_addons_in_table( cts_get_addon_plugins() );
$has_addons = ! empty( $addons );
$active_addons = array_filter( $addons, fn( $addon ) => $addon['is_active'] );

// Check if Elementor is active but sub-plugin is not
$elementor_active = false;
$elementor_subplugin_active = false;
$elementor_subplugin_installed = false;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$elementor_active = is_plugin_active( 'elementor/elementor.php' ) || did_action( 'elementor/loaded' );
$elementor_subplugin_active = is_plugin_active( 'churchtools-suite-elementor/churchtools-suite-elementor.php' );

// Check if Elementor sub-plugin is installed (but possibly inactive)
$all_plugins = get_plugins();
$elementor_subplugin_installed = isset( $all_plugins['churchtools-suite-elementor/churchtools-suite-elementor.php'] );

$posts_sync_plugin_file = 'churchtools-suite-posts-sync/churchtools-suite-posts-sync.php';
$posts_sync_installed = isset( $all_plugins[ $posts_sync_plugin_file ] );

?>
<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>🧩</span>
			<?php esc_html_e( 'Addons', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'Erweitere ChurchTools Suite mit zusätzlichen Plugins', 'churchtools-suite' ); ?></p>
	</div>
	
	<?php if ( $elementor_active && ! $elementor_subplugin_active ) : ?>
		<div class="notice notice-info" style="margin: 20px 0;">
			<p>
				<strong><?php esc_html_e( 'ChurchTools Suite - Elementor Integration', 'churchtools-suite' ); ?></strong><br>
				<?php 
				if ( $elementor_subplugin_installed ) {
					esc_html_e( 'Das ChurchTools Suite - Elementor Integration Plugin ist installiert aber nicht aktiv.', 'churchtools-suite' );
				} else {
					esc_html_e( 'Elementor wurde erkannt! Installiere das ChurchTools Suite - Elementor Integration Plugin für erweiterte Funktionen.', 'churchtools-suite' );
				}
				?>
			</p>
			<p>
				<?php if ( $elementor_subplugin_installed ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=activate&plugin=' . urlencode( 'churchtools-suite-elementor/churchtools-suite-elementor.php' ), 'activate-plugin_churchtools-suite-elementor/churchtools-suite-elementor.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( '✅ Aktivieren', 'churchtools-suite' ); ?>
					</a>
				<?php else : ?>
					<button type="button" class="button button-primary cts-install-addon" data-addon-slug="churchtools-suite-elementor">
						<?php esc_html_e( '⚡ Jetzt installieren', 'churchtools-suite' ); ?>
					</button>
				<?php endif; ?>
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite/releases" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Auf GitHub ansehen', 'churchtools-suite' ); ?>
				</a>
			</p>
			<?php if ( ! $elementor_subplugin_installed ) : ?>
				<div class="cts-install-result" style="display:none; margin-top:10px;"></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $posts_sync_installed ) : ?>
		<div class="notice notice-info" style="margin: 20px 0;">
			<p>
				<strong><?php esc_html_e( 'ChurchTools Suite – Posts Sync Addon', 'churchtools-suite' ); ?></strong><br>
				<?php esc_html_e( 'Synchronisiere ChurchTools-Posts (Berichte) automatisch nach WordPress. Das Addon muss separat installiert werden.', 'churchtools-suite' ); ?>
			</p>
			<p>
				<button type="button" class="button button-primary cts-install-addon" data-addon-slug="churchtools-suite-posts-sync">
					<?php esc_html_e( '⚡ Jetzt installieren', 'churchtools-suite' ); ?>
				</button>
			</p>
			<div class="cts-install-result" style="display:none; margin-top:10px;"></div>
		</div>
	<?php endif; ?>

	<?php if ( ! $has_addons ) : ?>
		<div class="notice notice-info inline">
			<p>
				<strong><?php esc_html_e( 'Keine Addons installiert', 'churchtools-suite' ); ?></strong><br>
				<?php esc_html_e( 'Es sind derzeit keine Erweiterungen für ChurchTools Suite installiert.', 'churchtools-suite' ); ?>
			</p>
		</div>
		
	<?php else : ?>
		
		<div class="cts-section">
			<div class="cts-section-header">
				<h2><?php esc_html_e( 'Installierte Addons', 'churchtools-suite' ); ?></h2>
				<p class="cts-section-description">
					<?php printf( 
						esc_html__( '%d Addon(s) installiert, davon %d aktiv', 'churchtools-suite' ),
						count( $addons ),
						count( $active_addons )
					); ?>
				</p>
				<button type="button" class="button cts-check-addon-updates" style="margin-left: 10px;">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Nach Updates suchen', 'churchtools-suite' ); ?>
				</button>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 3%;"><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></th>
						<th style="width: 25%;"><?php esc_html_e( 'Name', 'churchtools-suite' ); ?></th>
						<th style="width: 35%;"><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></th>
						<th style="width: 10%;"><?php esc_html_e( 'Version', 'churchtools-suite' ); ?></th>
						<th style="width: 12%;"><?php esc_html_e( 'Update', 'churchtools-suite' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Aktionen', 'churchtools-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $addons as $plugin_file => $addon ) : ?>
						<?php
						$status_icon = $addon['is_active'] ? '✅' : '⚪';
						$is_coming_soon = in_array( (string) $addon['plugin_slug'], [ 'churchtools-suite-presentations' ], true );
						$is_virtual = ! empty( $addon['is_virtual'] );
						if ( $is_coming_soon ) {
							$status_icon = '⏳';
							$status_text = esc_html__( 'Kommt bald', 'churchtools-suite' );
						} else {
							$status_text = $addon['is_active']
								? esc_html__( 'Aktiv', 'churchtools-suite' )
								: esc_html__( 'Inaktiv', 'churchtools-suite' );
						}
						$row_class = $addon['is_active'] ? 'active' : 'inactive';
						
						// Check for updates
						$update_info = false;
						if ( ! $is_virtual && ! $is_coming_soon && ! empty( $addon['github_repo'] ) && ! empty( $addon['Version'] ) && $addon['Version'] !== '—' ) {
							$update_info = cts_check_addon_update( $addon['github_repo'], $addon['Version'], $addon['plugin_slug'] );
						}
						
						// Generate action links
						$actions = [];
						if ( $is_coming_soon ) {
							$actions[] = esc_html__( 'Kommt bald', 'churchtools-suite' );
						} elseif ( $is_virtual ) {
							// Not installed but available – install directly via AJAX
							$actions[] = sprintf(
								'<button type="button" class="button-link cts-install-addon" data-addon-slug="%s">%s</button>',
								esc_attr( (string) $addon['plugin_slug'] ),
								esc_html__( 'Installieren', 'churchtools-suite' )
							);
						} elseif ( $addon['is_active'] ) {
							$deactivate_url = wp_nonce_url(
								admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( $plugin_file ) . '&plugin_status=all&paged=1' ),
								'deactivate-plugin_' . $plugin_file
							);
							$actions[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url( $deactivate_url ),
								esc_html__( 'Deaktivieren', 'churchtools-suite' )
							);
						} else {
							$activate_url = wp_nonce_url(
								admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $plugin_file ) . '&plugin_status=all&paged=1' ),
								'activate-plugin_' . $plugin_file
							);
							$actions[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url( $activate_url ),
								esc_html__( 'Aktivieren', 'churchtools-suite' )
							);
						}
						
						// Details link
						if ( ! empty( $addon['details_url'] ) ) {
							$actions[] = sprintf(
								'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
								esc_url( $addon['details_url'] ),
								esc_html__( 'Details', 'churchtools-suite' )
							);
						}
						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>" data-plugin-file="<?php echo esc_attr( $plugin_file ); ?>" data-github-repo="<?php echo esc_attr( $addon['github_repo'] ?? '' ); ?>">
							<td style="text-align: center; font-size: 1.2em;">
								<span title="<?php echo esc_attr( $status_text ); ?>"><?php echo $status_icon; ?></span>
							</td>
							<td>
								<strong><?php echo esc_html( $addon['Name'] ); ?></strong>
								<?php if ( $addon['is_network_active'] ) : ?>
									<span class="cts-badge cts-badge-info">
										<?php esc_html_e( 'Netzwerk', 'churchtools-suite' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo wp_kses_post( $addon['Description'] ); ?>
							</td>
							<td>
								<code><?php echo esc_html( $addon['Version'] ); ?></code>
							</td>
							<td class="cts-update-cell">
								<?php if ( $is_coming_soon ) : ?>
									<span style="color: #996800;">⏳ <?php esc_html_e( 'kommt bald', 'churchtools-suite' ); ?></span>
								<?php elseif ( $is_virtual ) : ?>
									<span style="color: #6b7280;">—</span>
								<?php elseif ( $update_info ) : ?>
									<div class="cts-update-available">
										<span class="dashicons dashicons-update" style="color: #d63638;"></span>
										<strong style="color: #d63638;"><?php echo esc_html( $update_info['latest_version'] ); ?></strong>
										<button type="button" 
										        class="button button-primary button-small cts-update-addon" 
										        data-plugin-file="<?php echo esc_attr( $plugin_file ); ?>"
										        data-github-repo="<?php echo esc_attr( $addon['github_repo'] ); ?>"
										        data-zip-url="<?php echo esc_attr( $update_info['zip_url'] ); ?>"
										        data-version="<?php echo esc_attr( $update_info['latest_version'] ); ?>">
											<?php esc_html_e( 'Jetzt aktualisieren', 'churchtools-suite' ); ?>
										</button>
									</div>
								<?php else : ?>
									<span style="color: #2271b1;">✓ <?php esc_html_e( 'Aktuell', 'churchtools-suite' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo implode( ' | ', $actions ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
	<?php endif; ?>
	
</div>

<script>
jQuery(document).ready(function($) {
	// Install addon handler
	$('.cts-install-addon').on('click', function(e) {
		e.preventDefault();
		
		const $btn = $(this);
		const addonSlug = $btn.data('addon-slug');
		let $resultDiv = $btn.closest('.cts-addon-card, .notice').find('.cts-install-result');
		if (!$resultDiv.length) {
			const $row = $btn.closest('tr');
			if ($row.length) {
				$resultDiv = $('<div class="cts-install-result notice" style="margin:8px 0;"></div>');
				$row.find('td').eq(2).append($resultDiv);
			}
		}
		
		// Disable button and show loading
		$btn.prop('disabled', true);
		const originalText = $btn.html();
		$btn.html('<span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Installiere...', 'churchtools-suite' ); ?>');
		
		// Hide previous result
		$resultDiv.hide().removeClass('notice-success notice-error').empty();
		
		// AJAX request
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_install_addon',
				nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>',
				addon_slug: addonSlug
			},
			success: function(response) {
				if (response.success) {
					$resultDiv
						.addClass('notice notice-success')
						.html('<p>' + response.data.message + '</p>')
						.show();
					
					// Reload page after 2 seconds
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					$resultDiv
						.addClass('notice notice-error')
						.html('<p>' + (response.data.message || '<?php esc_html_e( 'Unbekannter Fehler', 'churchtools-suite' ); ?>') + '</p>')
						.show();
					
					// Re-enable button
					$btn.prop('disabled', false).html(originalText);
				}
			},
			error: function(xhr, status, error) {
				$resultDiv
					.addClass('notice notice-error')
					.html('<p><?php esc_html_e( 'Netzwerkfehler bei der Installation', 'churchtools-suite' ); ?>: ' + error + '</p>')
					.show();
				
				// Re-enable button
				$btn.prop('disabled', false).html(originalText);
			}
		});
	});
	
	// Check for updates handler
	$('.cts-check-addon-updates').on('click', function(e) {
		e.preventDefault();
		
		const $btn = $(this);
		const originalHtml = $btn.html();
		
		// Show loading
		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Prüfe...', 'churchtools-suite' ); ?>');
		
		// Clear transient cache and reload
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_clear_addon_update_cache',
				nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>'
			},
			success: function(response) {
				if (response && response.success) {
					location.reload();
					return;
				}

				const msg = (response && response.data && response.data.message)
					? response.data.message
					: '<?php esc_html_e( 'Fehler beim Prüfen der Updates', 'churchtools-suite' ); ?>';
				$btn.prop('disabled', false).html(originalHtml);
				alert(msg);
			},
			error: function(xhr) {
				let msg = '<?php esc_html_e( 'Fehler beim Prüfen der Updates', 'churchtools-suite' ); ?>';
				if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					msg = xhr.responseJSON.data.message;
				}
				$btn.prop('disabled', false).html(originalHtml);
				alert(msg);
			}
		});
	});
	
	// Update addon handler
	$('.cts-update-addon').on('click', function(e) {
		e.preventDefault();
		
		const $btn = $(this);
		const pluginFile = $btn.data('plugin-file');
		const zipUrl = $btn.data('zip-url');
		const version = $btn.data('version');
		const $row = $btn.closest('tr');
		const $updateCell = $row.find('.cts-update-cell');
		
		// Confirm update
		if (!confirm('<?php esc_html_e( 'Möchten Sie dieses Addon wirklich aktualisieren?', 'churchtools-suite' ); ?>')) {
			return;
		}
		
		// Show loading in update cell
		const originalHtml = $updateCell.html();
		$updateCell.html('<span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Aktualisiere...', 'churchtools-suite' ); ?>');
		$btn.prop('disabled', true);
		
		// AJAX request
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'cts_update_addon',
				nonce: '<?php echo wp_create_nonce( 'churchtools_suite_admin' ); ?>',
				plugin_file: pluginFile,
				zip_url: zipUrl,
				version: version
			},
			success: function(response) {
				if (response.success) {
					$updateCell.html('<span style="color: #00a32a;">✓ ' + response.data.message + '</span>');
					
					// Reload page after 2 seconds
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					$updateCell.html('<span style="color: #d63638;">✗ ' + (response.data.message || '<?php esc_html_e( 'Update fehlgeschlagen', 'churchtools-suite' ); ?>') + '</span>');
					$btn.prop('disabled', false);
					
					// Restore after 3 seconds
					setTimeout(function() {
						$updateCell.html(originalHtml);
					}, 3000);
				}
			},
			error: function(xhr, status, error) {
				$updateCell.html('<span style="color: #d63638;">✗ <?php esc_html_e( 'Netzwerkfehler', 'churchtools-suite' ); ?></span>');
				$btn.prop('disabled', false);
				
				// Restore after 3 seconds
				setTimeout(function() {
					$updateCell.html(originalHtml);
				}, 3000);
			}
		});
	});
});
</script>

<style>
.cts-install-addon .dashicons.spin,
.cts-check-addon-updates .dashicons.spin,
.cts-update-cell .dashicons.spin {
	animation: rotation 1s infinite linear;
}

@keyframes rotation {
	from { transform: rotate(0deg); }
	to { transform: rotate(359deg); }
}

.cts-install-result {
	margin-top: 10px;
}

.cts-install-result.notice {
	padding: 10px;
	border-left: 4px solid #00a32a;
}

.cts-install-result.notice-error {
	border-left-color: #d63638;
}

.cts-install-result p {
	margin: 0;
}

.cts-update-available {
	display: flex;
	align-items: center;
	gap: 8px;
}

.cts-update-available .dashicons {
	font-size: 18px;
	width: 18px;
	height: 18px;
}

.cts-update-addon.button-small {
	padding: 2px 8px;
	font-size: 12px;
	height: auto;
	line-height: 1.5;
}

.cts-check-addon-updates {
	vertical-align: middle;
}

.cts-check-addon-updates .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
	vertical-align: text-top;
}
</style>
