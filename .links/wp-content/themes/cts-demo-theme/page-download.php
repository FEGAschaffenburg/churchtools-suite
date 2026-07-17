<?php
/**
 * Template Name: Download
 * 
 * @package CTS_Demo_Theme
 */

get_header(); ?>

<main class="download-page">
	<div class="container">
		
		<!-- Hero Section -->
		<section class="page-hero">
		<!-- <h1><?php the_title(); ?></h1> -->
			<p class="lead">Kostenlos herunterladen und in wenigen Minuten einrichten</p>
		</section>

		<!-- Download Box -->
		<section class="download-section">
			<div class="download-box">
				<div class="download-header">
					<h2>ChurchTools Suite</h2>
					<span class="version-badge">Version <?php echo esc_html( cts_demo_get_cts_version() ); ?></span>
				</div>
				
				<div class="download-info">
					<div class="info-item">
						<span class="label">Letzte Aktualisierung:</span>
						<span class="value">17. Juli 2026</span>
					</div>
					<div class="info-item">
						<span class="label">Dateigröße:</span>
						<span class="value">~800 KB</span>
					</div>
					<div class="info-item">
						<span class="label">WordPress Version:</span>
						<span class="value">6.0+</span>
					</div>
					<div class="info-item">
						<span class="label">PHP Version:</span>
						<span class="value">8.0+</span>
					</div>
					<div class="info-item">
						<span class="label">Lizenz:</span>
						<span class="value">GPL-2.0</span>
					</div>
				</div>

				<?php if ( is_user_logged_in() ) : ?>
					<!-- Download buttons for logged-in users -->
					<div class="download-actions">
						<a href="https://github.com/FEGAschaffenburg/churchtools-suite/releases/latest/download/churchtools-suite.zip" class="btn btn-primary btn-large download-btn">
							<span class="icon">📦</span>
							Jetzt herunterladen (ZIP)
						</a>
						<a href="https://github.com/FEGAschaffenburg/churchtools-suite/releases" class="btn btn-secondary" target="_blank">
							<span class="icon">📋</span>
							Alle Versionen (GitHub)
						</a>
					</div>

					<p class="download-note">
						<strong>Hinweis:</strong> Das Plugin benötigt einen ChurchTools-Account mit API-Zugang. 
						<a href="/dokumentation/">Installationsanleitung ansehen</a>
					</p>
				<?php else : ?>
					<!-- Login required message -->
					<div class="download-login-required">
						<div class="login-box">
							<h3>🔒 Anmeldung erforderlich</h3>
							<p>Um das Plugin herunterzuladen, musst du dich zuerst anmelden oder registrieren.</p>
							
							<div class="login-actions">
								<a href="<?php echo wp_login_url( get_permalink() ); ?>" class="btn btn-primary btn-large">
									<span class="icon">🔑</span>
									Jetzt anmelden
								</a>
								<a href="/backend-demo/" class="btn btn-secondary">
									<span class="icon">🎓</span>
									Kostenlos registrieren
								</a>
							</div>

							<p class="login-note">
							<?php $demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 ); ?>
							<strong>Neu hier?</strong> Registriere dich kostenlos über die <a href="/backend-demo/">Backend-Demo</a> 
							und erhalte sofort Zugang zum Download sowie zum WordPress-Backend für <?php echo esc_html( $demo_duration_days ); ?> Tage.
							</p>
						</div>
					</div>

					<style>
						.download-login-required {
							margin-top: 30px;
						}
						
						.login-box {
							background: #f8fafc;
							border: 2px solid #e2e8f0;
							border-radius: 12px;
							padding: 40px;
							text-align: center;
						}
						
						.login-box h3 {
							margin: 0 0 16px 0;
							color: #1e293b;
							font-size: 24px;
						}
						
						.login-box > p {
							color: #64748b;
							font-size: 16px;
							margin-bottom: 32px;
						}
						
						.login-actions {
							display: flex;
							gap: 16px;
							justify-content: center;
							flex-wrap: wrap;
							margin-bottom: 24px;
						}
						
						.login-note {
							font-size: 14px;
							color: #475569;
							margin: 24px 0 0 0;
							padding-top: 24px;
							border-top: 1px solid #e2e8f0;
						}
						
						.login-note strong {
							color: #1e293b;
						}
						
						.login-note a {
							color: #3b82f6;
							text-decoration: none;
							font-weight: 600;
						}
						
						.login-note a:hover {
							text-decoration: underline;
						}
					</style>
				<?php endif; ?>
			</div>
		</section>

		<!-- Changelog Section -->
		<section class="changelog-section">
			<h2>Changelog</h2>
			
			<p class="intro">
				Alle Versionshinweise, Änderungen und Release-Notes findest du auf der offiziellen GitHub Releases-Seite.
			</p>

			<div class="changelog-more" style="margin-top: 2rem;">
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite/releases" target="_blank" class="btn btn-primary">
					📋 Changelog auf GitHub ansehen →
				</a>
			</div>
		</section>

		<!-- System Requirements -->
		<section class="requirements-section">
			<h2>System-Anforderungen</h2>
			
			<div class="requirements-grid">
				<div class="requirement-card">
					<h3>WordPress</h3>
					<p class="version">Version 6.0+</p>
					<p class="description">Getestet bis WordPress 6.4.x</p>
				</div>

				<div class="requirement-card">
					<h3>PHP</h3>
					<p class="version">Version 8.0+</p>
					<p class="description">Empfohlen: PHP 8.1 oder höher</p>
				</div>

				<div class="requirement-card">
					<h3>MySQL / MariaDB</h3>
					<p class="version">Version 5.7+ / 10.3+</p>
					<p class="description">InnoDB Storage Engine</p>
				</div>

				<div class="requirement-card">
					<h3>ChurchTools</h3>
					<p class="version">API v2.0+</p>
					<p class="description">Account mit API-Zugang erforderlich</p>
				</div>
			</div>

			<div class="requirements-note">
				<p><strong>Empfohlene Server-Konfiguration:</strong></p>
				<ul>
					<li>memory_limit: 256M oder höher</li>
					<li>max_execution_time: 300 (für große Event-Syncs)</li>
					<li>WP-Cron aktiviert (für automatische Syncs)</li>
				</ul>
			</div>
		</section>

		<!-- Support Section -->
		<section class="support-section">
			<h2>Support & Community</h2>
			
			<div class="support-grid">
				<div class="support-card">
					<h3>📖 Dokumentation</h3>
					<p>Vollständige Anleitungen, Shortcode-Referenz und Troubleshooting</p>
					<a href="/dokumentation/" class="btn btn-text">Zur Dokumentation →</a>
				</div>

				<div class="support-card">
					<h3>🐛 Bug Reports</h3>
					<p>Fehler gefunden? Erstelle ein Issue auf GitHub</p>
					<a href="https://github.com/FEGAschaffenburg/churchtools-suite/issues" target="_blank" class="btn btn-text">Issue erstellen →</a>
				</div>

				<div class="support-card">
					<h3>💬 Diskussionen</h3>
					<p>Fragen stellen, Features vorschlagen, Erfahrungen teilen</p>
					<a href="https://github.com/FEGAschaffenburg/churchtools-suite/discussions" target="_blank" class="btn btn-text">Zu Discussions →</a>
				</div>

				<div class="support-card">
					<h3>🎓 Backend-Demo</h3>
					<p>Teste das Plugin live im WordPress-Backend (7-Tage-Zugang)</p>
					<a href="/backend-demo/" class="btn btn-text">Demo anfordern →</a>
				</div>
			</div>
		</section>

	</div>
</main>

<?php get_footer(); ?>
