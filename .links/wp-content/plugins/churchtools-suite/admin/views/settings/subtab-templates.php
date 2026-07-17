<?php
/**
 * Settings Subtab: Templates
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Helper function to get available templates (guarded to avoid redeclare conflicts)
if ( ! function_exists( 'cts_get_available_templates' ) ) {
	function cts_get_available_templates( $type ) {
		$template_dir = CHURCHTOOLS_SUITE_PATH . 'templates/views/' . $type . '/';
		$templates = [];
		$prefix_map = [
			'event-single' => 'single-',
			'event-modal'  => 'modal-',
		];
		$canonical_prefix = $prefix_map[ $type ] ?? '';
        
		if ( is_dir( $template_dir ) ) {
			$files = scandir( $template_dir );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( substr( $file, -4 ) === '.php' && $file[0] !== '.' ) {
						$template_name = substr( $file, 0, -4 );

						if ( $canonical_prefix !== '' ) {
							if ( strpos( $template_name, $canonical_prefix ) === 0 ) {
								// Canonical file name: single-professional.php -> professional.
								$template_name = substr( $template_name, strlen( $canonical_prefix ) );
							} else {
								// Skip wrapper file if canonical prefixed file exists.
								$prefixed_file = $template_dir . $canonical_prefix . $template_name . '.php';
								if ( file_exists( $prefixed_file ) ) {
									continue;
								}
							}
						}

						$templates[ $template_name ] = true;
					}
				}
			}
		}

		$templates = array_keys( $templates );

		sort( $templates );
        
		return $templates;
	}
}

// Dynamically get available templates (v0.9.9.84)
$available_single_templates = cts_get_available_templates( 'event-single' );
$available_modal_templates = cts_get_available_templates( 'event-modal' );

// Fallback if none found
if ( empty( $available_single_templates ) ) {
	$available_single_templates = [ 'professional' ];
}
if ( empty( $available_modal_templates ) ) {
	$available_modal_templates = [ 'professional' ];
}

// Form processing
if ( isset( $_POST['cts_save_templates'] ) && check_admin_referer( 'cts_settings' ) ) {
	$posted_single_template = sanitize_key( sanitize_text_field( $_POST['single_template'] ?? 'professional' ) );
	$posted_modal_template = sanitize_key( sanitize_text_field( $_POST['modal_template'] ?? 'professional' ) );

	$single_template_to_save = in_array( $posted_single_template, $available_single_templates, true )
		? $posted_single_template
		: 'professional';
	$modal_template_to_save = in_array( $posted_modal_template, $available_modal_templates, true )
		? $posted_modal_template
		: 'professional';

	update_option( 'churchtools_suite_single_template', $single_template_to_save );
	update_option( 'churchtools_suite_modal_template', $modal_template_to_save );

	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Template-Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$single_template = sanitize_key( (string) get_option( 'churchtools_suite_single_template', 'professional' ) );
$modal_template = sanitize_key( (string) get_option( 'churchtools_suite_modal_template', 'professional' ) );

if ( ! in_array( $single_template, $available_single_templates, true ) ) {
	$single_template = 'professional';
	update_option( 'churchtools_suite_single_template', $single_template );
}
if ( ! in_array( $modal_template, $available_modal_templates, true ) ) {
	$modal_template = 'professional';
	update_option( 'churchtools_suite_modal_template', $modal_template );
}
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<!-- v0.9.9.84: Click Action removed - now only in Block! -->
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'Single Page Templates', 'churchtools-suite' ); ?></h3>
		<p class="cts-card-description">
			<?php esc_html_e( 'Diese Einstellung legt fest, welches Template verwendet wird, wenn ein Event auf einer eigenen Seite angezeigt wird (über URL-Parameter ?event_id=123 oder Shortcode [cts_event id="123"]).', 'churchtools-suite' ); ?>
		</p>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="single_template"><?php esc_html_e( 'Standard Single Template', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="single_template" name="single_template" class="cts-form-input">
						<?php foreach ( $available_single_templates as $template ) : ?>
							<option value="<?php echo esc_attr( $template ); ?>" <?php selected( $single_template, $template ); ?>>
								<?php echo esc_html( ucfirst( $template ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<div class="cts-form-description">
						<?php printf( esc_html__( 'Verfügbare Templates: %s', 'churchtools-suite' ), esc_html( implode( ', ', $available_single_templates ) ) ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="cts-card" style="margin-top: 24px;">
		<h3><?php esc_html_e( 'Modal Templates', 'churchtools-suite' ); ?></h3>
		<p class="cts-card-description">
			<?php esc_html_e( 'Diese Einstellung legt fest, welches Template verwendet wird, wenn ein Event im Modal-Overlay angezeigt wird (im Block konfigurierbar via click_action="modal").', 'churchtools-suite' ); ?>
		</p>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="modal_template"><?php esc_html_e( 'Standard Modal Template', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="modal_template" name="modal_template" class="cts-form-input">
						<?php foreach ( $available_modal_templates as $template ) : ?>
							<option value="<?php echo esc_attr( $template ); ?>" <?php selected( $modal_template, $template ); ?>>
								<?php echo esc_html( ucfirst( $template ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<div class="cts-form-description">
						<?php printf( esc_html__( 'Verfügbare Templates: %s', 'churchtools-suite' ), esc_html( implode( ', ', $available_modal_templates ) ) ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<!-- v0.9.9.84: Click Action Info -->
	<div class="cts-card cts-mt-24 cts-card-info-bg">
		<h3><?php esc_html_e( '💡 Event-Klick Verhalten', 'churchtools-suite' ); ?></h3>
		<p class="cts-card-description">
			<?php esc_html_e( 'Das Verhalten beim Event-Klick wird jetzt im Block konfiguriert, nicht hier!', 'churchtools-suite' ); ?>
		</p>
		
		<div style="margin-top: 12px;">
			<p><strong><?php esc_html_e( 'Block-Parameter:', 'churchtools-suite' ); ?></strong></p>
			<code class="cts-code-block">[cts_event_list click_action="modal" template="professional"]</code>
			<code class="cts-code-block">[cts_event_list click_action="page"]</code>
			
			<p style="margin-top: 12px;">
				<strong><?php esc_html_e( 'Optionen:', 'churchtools-suite' ); ?></strong>
			</p>
			<ul style="margin-top: 8px; padding-left: 20px; list-style: disc;">
				<li><code>click_action="modal"</code> - <?php esc_html_e( 'Event in Modal-Overlay anzeigen', 'churchtools-suite' ); ?></li>
				<li><code>click_action="page"</code> - <?php esc_html_e( 'Zur Single-Page navigieren', 'churchtools-suite' ); ?></li>
			</ul>
		</div>
	</div>
	
	<div class="cts-form-actions">
		<button type="submit" name="cts_save_templates" class="cts-btn cts-btn-primary">
			<?php esc_html_e( 'Einstellungen speichern', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>

