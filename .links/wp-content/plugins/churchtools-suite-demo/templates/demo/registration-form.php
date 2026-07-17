<?php
/**
 * Demo Registration Form Template
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get demo duration from settings
$demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 );
?>

<div class="cts-demo-registration">
	<div class="cts-demo-registration-header">
		<h2>Backend-Demo anfordern</h2>
		<p>Erhalten Sie kostenlosen Zugang zum Plugin-Backend und testen Sie alle Funktionen.</p>
	</div>
	
	<form id="cts-demo-registration-form" class="cts-demo-form">
		<div class="cts-form-group">
			<label for="cts-demo-email">
				E-Mail-Adresse
				<span class="required">*</span>
			</label>
			<input type="email" id="cts-demo-email" name="email" required placeholder="ihre@email.de">
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-first-name">
				Vorname
				<span class="required">*</span>
			</label>
			<input type="text" id="cts-demo-first-name" name="first_name" required placeholder="Max">
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-last-name">
				Nachname
				<span class="required">*</span>
			</label>
			<input type="text" id="cts-demo-last-name" name="last_name" required placeholder="Mustermann">
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-password">
				Passwort
				<span class="required">*</span>
			</label>
			<input type="password" id="cts-demo-password" name="password" required placeholder="Mindestens 8 Zeichen" minlength="8">
			<p class="cts-field-hint">Mindestens 8 Zeichen erforderlich</p>
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-password-confirm">
				Passwort bestätigen
				<span class="required">*</span>
			</label>
			<input type="password" id="cts-demo-password-confirm" name="password_confirm" required placeholder="Passwort wiederholen" minlength="8">
			<p class="cts-field-hint cts-password-match" style="display: none;"></p>
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-company">
				Firma / Gemeinde
			</label>
			<input type="text" id="cts-demo-company" name="company" placeholder="Musterkirche e.V.">
		</div>
		
		<div class="cts-form-group">
			<label for="cts-demo-purpose">
				Verwendungszweck
			</label>
			<textarea id="cts-demo-purpose" name="purpose" rows="3" placeholder="Ich möchte das Plugin für..."></textarea>
		</div>
		
		<div class="cts-form-group cts-checkbox-group">
			<label class="cts-checkbox-label">
				<input type="checkbox" id="cts-demo-privacy" name="privacy_accepted" required>
				<span>
					Ich akzeptiere die <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" target="_blank">Datenschutzerklärung</a> und stimme der Speicherung meiner Daten zu.
					<span class="required">*</span>
				</span>
			</label>
			<p class="cts-privacy-note">
				Ihre Daten werden nur für die Demo-Registrierung verwendet und nach <?php echo esc_html( $demo_duration_days ); ?> Tagen automatisch gelöscht.
			</p>
		</div>
		
		<div class="cts-form-actions">
			<button type="submit" class="cts-submit-btn">
				<span class="btn-text">Demo-Zugang anfordern</span>
				<span class="btn-spinner" style="display: none;">
					<span class="spinner"></span>
					Wird gesendet...
				</span>
			</button>
		</div>
		
		<div class="cts-form-message" style="display: none;"></div>
	</form>
	
	<div class="cts-demo-info">
		<h3>Was passiert nach der Registrierung?</h3>
		<ol>
			<li>Sie erhalten eine Bestätigungs-E-Mail mit einem Verifizierungslink</li>
			<li>Nach dem Klick auf den Link werden Sie automatisch eingeloggt</li>
			<li>Sie können direkt das Plugin-Backend erkunden</li>
			<li>Ihr Demo-Zugang ist 30 Tage gültig und wird dann automatisch gelöscht</li>
		</ol>
	</div>
</div>
