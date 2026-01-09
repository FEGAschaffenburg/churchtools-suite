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

// Form processing
if ( isset( $_POST['cts_save_templates'] ) && check_admin_referer( 'cts_settings' ) ) {
	update_option( 'churchtools_suite_single_template', sanitize_text_field( $_POST['single_template'] ?? 'professional' ) );
	update_option( 'churchtools_suite_modal_template', sanitize_text_field( $_POST['modal_template'] ?? 'event-detail' ) );
	
	echo '<div class="cts-notice cts-notice-success"><p>' . esc_html__( 'Template-Einstellungen gespeichert.', 'churchtools-suite' ) . '</p></div>';
}

$single_template = get_option( 'churchtools_suite_single_template', 'professional' );
$modal_template = get_option( 'churchtools_suite_modal_template', 'professional' );

// Available templates
$single_templates = [
	'professional' => __( 'Professional', 'churchtools-suite' ),
];

$modal_templates = [
	'professional' => __( 'Professional', 'churchtools-suite' ),
];
?>

<form method="post" action="" class="cts-form">
	<?php wp_nonce_field( 'cts_settings' ); ?>
	
	<div class="cts-card">
		<h3><?php esc_html_e( 'Single Page Templates', 'churchtools-suite' ); ?></h3>
		<p class="cts-card-description">
			<?php esc_html_e( 'Diese Einstellung legt fest, welches Template standardmäßig verwendet wird, wenn ein Event auf einer eigenen Seite angezeigt wird (über URL-Parameter ?event_id=123 oder Shortcode [cts_event id="123"]).', 'churchtools-suite' ); ?>
		</p>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="single_template"><?php esc_html_e( 'Standard Single Template', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="single_template" name="single_template" class="cts-form-input">
						<?php foreach ( $single_templates as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $single_template, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<div class="cts-form-description">
						<strong><?php esc_html_e( 'Hinweis:', 'churchtools-suite' ); ?></strong> 
						<?php esc_html_e( 'Dieses Template kann pro Event überschrieben werden: [cts_event id="123" template="classic"]', 'churchtools-suite' ); ?>
					</div>
					
					<div style="margin-top: 16px;">
						<h4><?php esc_html_e( 'Template-Beschreibung', 'churchtools-suite' ); ?></h4>
						<ul style="list-style: disc; padding-left: 24px; line-height: 1.8;">
							<li><strong><?php esc_html_e( 'Professional:', 'churchtools-suite' ); ?></strong> <?php esc_html_e( 'Professionelles Layout mit großem Hero-Bild links, langer Beschreibung und rechts Sidebar mit Date, Time, Local Time, Labels, und Location-Bild.', 'churchtools-suite' ); ?></li>
						</ul>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="cts-card" style="margin-top: 24px;">
		<h3><?php esc_html_e( 'Modal Templates', 'churchtools-suite' ); ?></h3>
		<p class="cts-card-description">
			<?php esc_html_e( 'Diese Einstellung legt fest, welches Template verwendet wird, wenn ein Event im Modal-Overlay angezeigt wird (event_action="modal" in Listen-Shortcodes).', 'churchtools-suite' ); ?>
		</p>
		
		<table class="cts-form-table">
			<tr>
				<th scope="row">
					<label for="modal_template"><?php esc_html_e( 'Standard Modal Template', 'churchtools-suite' ); ?></label>
				</th>
				<td>
					<select id="modal_template" name="modal_template" class="cts-form-input">
						<?php foreach ( $modal_templates as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $modal_template, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<div class="cts-form-description">
						<?php esc_html_e( 'Aktuell ist nur ein Modal-Template verfügbar. Weitere Varianten können in zukünftigen Versionen hinzugefügt werden.', 'churchtools-suite' ); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="cts-form-actions">
		<button type="submit" name="cts_save_templates" class="cts-btn cts-btn-primary">
			<?php esc_html_e( 'Einstellungen speichern', 'churchtools-suite' ); ?>
		</button>
	</div>
</form>

<style>
.cts-card-description {
	color: #64748b;
	margin: 12px 0 20px 0;
	line-height: 1.6;
}
</style>
