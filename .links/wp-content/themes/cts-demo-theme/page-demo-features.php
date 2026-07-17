<?php
/**
 * Template Name: Backend-Demo Features
 * 
 * @package CTS_Demo_Theme
 */

get_header(); ?>

<main class="demo-features-page">
	<div class="container">
		
		<!-- Hero Section -->
		<section class="page-hero">
		<!-- <h1><?php the_title(); ?></h1> -->

		<!-- Overview -->
		<section class="demo-overview">
			<div class="overview-box">
				<?php $demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 ); ?>
				<h2>🎓 Voller Backend-Zugang für <?php echo esc_html( $demo_duration_days ); ?> Tage</h2>
				<p>Nach der <a href="/backend-demo/">kostenlosen Registrierung</a> erhältst du Zugang zum kompletten WordPress-Backend 
				mit dem ChurchTools Suite Plugin. Du kannst alle Funktionen in Echtzeit testen und selbst konfigurieren.</p>
			</div>
		</section>

		<!-- Features Grid -->
		<section class="features-section">
			<h2>Was du selbst konfigurieren kannst</h2>
			
			<div class="features-grid">
				<!-- ChurchTools Verbindung -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>ChurchTools Verbindung</h3>
					<p><strong>Eigene Credentials verwenden:</strong> Verbinde dein eigenes ChurchTools-System mit URL, Benutzername und Passwort.</p>
					<ul>
						<li>API-Verbindung testen</li>
						<li>Session-Management ansehen</li>
						<li>Fehlerbehandlung erleben</li>
					</ul>
				</div>

				<!-- Kalender auswählen -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>Kalender synchronisieren</h3>
					<p><strong>Deine ChurchTools-Kalender importieren:</strong> Wähle aus, welche Kalender du synchronisieren möchtest.</p>
					<ul>
						<li>Kalender aus ChurchTools abrufen</li>
						<li>Auswahl treffen (Multiselect)</li>
						<li>Manuellen Sync starten</li>
						<li>Sync-Historie ansehen</li>
					</ul>
				</div>

				<!-- Services -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>Services verwalten</h3>
					<p><strong>ChurchTools-Services auswählen:</strong> Bestimme, welche Dienste/Services importiert werden sollen.</p>
					<ul>
						<li>Service-Gruppen durchsuchen</li>
						<li>Services aktivieren/deaktivieren</li>
						<li>Service-Namen ansehen</li>
					</ul>
				</div>

				<!-- Views -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>Views erstellen & konfigurieren</h3>
					<p><strong>Eigene Ansichten bauen:</strong> Erstelle individuelle Event-Ansichten mit Filtern und Styling.</p>
					<ul>
						<li>View-Templates auswählen (List, Grid, Calendar)</li>
						<li>Kalender-Filter setzen</li>
						<li>Anzahl Events festlegen</li>
						<li>Tag-Filter konfigurieren</li>
						<li>Farben & Styles anpassen</li>
					</ul>
				</div>

				<!-- Shortcodes -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>Shortcodes testen</h3>
					<p><strong>Alle Shortcode-Varianten ausprobieren:</strong> Teste die verschiedenen Event-Ansichten live.</p>
					<ul>
						<li><code>[cts_events view="list"]</code></li>
						<li><code>[cts_events view="grid"]</code></li>
						<li><code>[cts_events view="calendar"]</code></li>
						<li>Parameter anpassen</li>
						<li>Preview im Backend ansehen</li>
					</ul>
				</div>

				<!-- Sync-Einstellungen -->
				<div class="feature-card can-do">
					<div class="feature-icon">✅</div>
					<h3>Sync-Einstellungen anpassen</h3>
					<p><strong>Zeiträume & Automatik konfigurieren:</strong> Bestimme, wie Events synchronisiert werden.</p>
					<ul>
						<li>Sync-Zeitraum festlegen (z.B. 7 Tage zurück, 90 Tage voraus)</li>
						<li>Auto-Sync aktivieren/deaktivieren</li>
						<li>Sync-Intervall einstellen (stündlich/täglich)</li>
					</ul>
				</div>

				<!-- Dashboard -->
				<div class="feature-card can-view">
					<div class="feature-icon">👁️</div>
					<h3>Dashboard & Statistiken</h3>
					<p><strong>Nur ansehen:</strong> Das Dashboard zeigt Sync-Statistiken und System-Status.</p>
					<ul>
						<li>Sync-Historie mit Zeitstempel</li>
						<li>Event-Statistiken (importiert, aktualisiert)</li>
						<li>ChurchTools-Verbindungsstatus</li>
						<li>Letzte Aktivitäten</li>
					</ul>
				</div>

				<!-- Debug -->
				<div class="feature-card can-view">
					<div class="feature-icon">👁️</div>
					<h3>Debug & Logs</h3>
					<p><strong>Nur ansehen:</strong> Erweiterte Entwickler-Informationen (nur im Advanced Mode).</p>
					<ul>
						<li>API-Request-Logs</li>
						<li>Datenbank-Queries</li>
						<li>Performance-Metriken</li>
						<li>Error-Handling</li>
					</ul>
				</div>

				<!-- Frontend Preview -->
				<div class="feature-card can-view">
					<div class="feature-icon">👁️</div>
					<h3>Frontend-Demo-Seiten</h3>
					<p><strong>Nur ansehen:</strong> Die öffentlichen Demo-Seiten zeigen verschiedene Templates in Aktion.</p>
					<ul>
						<li>List-Ansichten: Classic, Classic-Modern, Classic-with-Images, Minimal, Modern</li>
						<li>Grid-Ansichten: Simple (Cards), Modern Grid (Masonry)</li>
						<li>Calendar-Ansichten: Monthly Simple</li>
						<li>Countdown-Ansicht: Classic (Next Event Hero)</li>
					</ul>
				</div>
			</div>
		</section>

		<!-- Limitations -->
		<section class="limitations-section">
			<h2>⚠️ Einschränkungen der Demo</h2>
			
			<div class="limitation-box">
				<h3>Zeitlich begrenzt</h3>
				<?php $demo_duration_days = (int) get_option( 'cts_demo_duration_days', 30 ); ?>
				<p>Dein Backend-Zugang ist für <strong><?php echo esc_html( $demo_duration_days ); ?> Tage</strong> gültig. Danach wird dein Demo-Account automatisch deaktiviert. 
				Du kannst dich jederzeit erneut registrieren, wenn du mehr Zeit benötigst.</p>
			</div>

			<div class="limitation-box">
				<h3>Keine Seiten-Bearbeitung</h3>
				<p>Du kannst keine WordPress-Seiten/Posts erstellen oder bearbeiten. Die Demo-Seiten sind fix vorkonfiguriert 
				und dienen nur zur Ansicht der verschiedenen Templates.</p>
			</div>

			<div class="limitation-box">
				<h3>Keine Theme-/Plugin-Installation</h3>
				<p>Du kannst keine zusätzlichen Themes oder Plugins installieren. Der Fokus liegt auf der 
				ChurchTools Suite und deren Funktionen.</p>
			</div>

			<div class="limitation-box">
				<h3>Gemeinsame Demo-Umgebung</h3>
				<p>Alle Demo-User teilen sich dieselbe WordPress-Installation. Deine ChurchTools-Daten sind jedoch 
				privat und werden nur dir angezeigt (gefiltert nach deinem Account).</p>
			</div>
		</section>

		<!-- Use Cases -->
		<section class="use-cases-section">
			<h2>💡 Was du in der Demo lernen kannst</h2>
			
			<div class="use-cases-grid">
				<div class="use-case">
					<h3>Für Gemeinde-Admins</h3>
					<ul>
						<li>Teste die Verbindung zu eurem ChurchTools</li>
						<li>Prüfe, welche Kalender synchronisiert werden können</li>
						<li>Sieh, wie Events auf eurer Website aussehen würden</li>
						<li>Verstehe den Sync-Prozess und dessen Optionen</li>
					</ul>
				</div>

				<div class="use-case">
					<h3>Für Webmaster</h3>
					<ul>
						<li>Lerne die verschiedenen View-Templates kennen</li>
						<li>Teste Shortcode-Parameter und Filter</li>
						<li>Verstehe die View-Konfiguration</li>
						<li>Prüfe die Performance und Ladezeiten</li>
					</ul>
				</div>

				<div class="use-case">
					<h3>Für Entwickler</h3>
					<ul>
						<li>Sieh dir die API-Integration im Detail an</li>
						<li>Prüfe Debug-Logs und Error-Handling</li>
						<li>Verstehe die Datenbank-Struktur</li>
						<li>Teste Edge Cases und Fehlerszenarien</li>
					</ul>
				</div>
			</div>
		</section>

		<!-- CTA -->
		<section class="demo-cta">
			<div class="cta-box">
				<h2>Bereit zum Testen?</h2>
				<p>Registriere dich jetzt kostenlos und erhalte sofortigen Zugang zum WordPress-Backend!</p>
				
				<div class="cta-actions">
					<a href="/backend-demo/" class="btn btn-primary btn-large">
						<span class="icon">🎓</span>
						Jetzt kostenlos registrieren
					</a>
					<a href="/dokumentation/" class="btn btn-secondary">
						<span class="icon">📖</span>
						Dokumentation lesen
					</a>
				</div>
			</div>
		</section>

	</div>
</main>

<style>
.demo-features-page {
	padding: 40px 0;
}

.demo-overview {
	margin: 40px 0;
}

.overview-box {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 40px;
	border-radius: 12px;
	text-align: center;
}

.overview-box h2 {
	margin: 0 0 16px 0;
	font-size: 28px;
}

.overview-box p {
	font-size: 18px;
	line-height: 1.6;
	margin: 0;
}

.overview-box a {
	color: white;
	text-decoration: underline;
	font-weight: 600;
}

.features-section {
	margin: 60px 0;
}

.features-section h2 {
	text-align: center;
	margin-bottom: 40px;
	font-size: 32px;
}

.features-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
	gap: 24px;
	margin-top: 32px;
}

.feature-card {
	background: white;
	border: 2px solid #e2e8f0;
	border-radius: 12px;
	padding: 32px;
	transition: all 0.3s ease;
}

.feature-card.can-do {
	border-color: #10b981;
}

.feature-card.can-view {
	border-color: #3b82f6;
}

.feature-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.feature-icon {
	font-size: 48px;
	margin-bottom: 16px;
}

.feature-card h3 {
	margin: 0 0 12px 0;
	font-size: 20px;
	color: #1e293b;
}

.feature-card p {
	color: #64748b;
	margin-bottom: 16px;
	line-height: 1.6;
}

.feature-card strong {
	color: #1e293b;
}

.feature-card ul {
	list-style: none;
	padding: 0;
	margin: 0;
}

.feature-card ul li {
	padding: 8px 0;
	padding-left: 24px;
	position: relative;
	color: #475569;
	font-size: 15px;
}

.feature-card.can-do ul li:before {
	content: "✓";
	position: absolute;
	left: 0;
	color: #10b981;
	font-weight: bold;
}

.feature-card.can-view ul li:before {
	content: "👁";
	position: absolute;
	left: 0;
}

.feature-card code {
	background: #f1f5f9;
	padding: 2px 6px;
	border-radius: 4px;
	font-size: 13px;
	color: #e11d48;
}

.limitations-section {
	margin: 60px 0;
	background: #fef3c7;
	padding: 40px;
	border-radius: 12px;
}

.limitations-section h2 {
	margin: 0 0 32px 0;
	font-size: 32px;
	text-align: center;
}

.limitation-box {
	background: white;
	padding: 24px;
	margin-bottom: 16px;
	border-radius: 8px;
	border-left: 4px solid #f59e0b;
}

.limitation-box:last-child {
	margin-bottom: 0;
}

.limitation-box h3 {
	margin: 0 0 8px 0;
	color: #92400e;
	font-size: 18px;
}

.limitation-box p {
	margin: 0;
	color: #78350f;
	line-height: 1.6;
}

.use-cases-section {
	margin: 60px 0;
}

.use-cases-section h2 {
	text-align: center;
	margin-bottom: 40px;
	font-size: 32px;
}

.use-cases-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 32px;
}

.use-case {
	background: #f8fafc;
	padding: 32px;
	border-radius: 12px;
	border: 2px solid #e2e8f0;
}

.use-case h3 {
	margin: 0 0 16px 0;
	color: #1e293b;
	font-size: 20px;
}

.use-case ul {
	list-style: none;
	padding: 0;
	margin: 0;
}

.use-case ul li {
	padding: 8px 0;
	padding-left: 24px;
	position: relative;
	color: #475569;
	line-height: 1.5;
}

.use-case ul li:before {
	content: "→";
	position: absolute;
	left: 0;
	color: #3b82f6;
	font-weight: bold;
}

.demo-cta {
	margin: 60px 0;
}

.cta-box {
	background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
	color: white;
	padding: 60px 40px;
	border-radius: 12px;
	text-align: center;
}

.cta-box h2 {
	margin: 0 0 16px 0;
	font-size: 36px;
}

.cta-box > p {
	font-size: 18px;
	margin-bottom: 32px;
}

.cta-actions {
	display: flex;
	gap: 16px;
	justify-content: center;
	flex-wrap: wrap;
}

@media (max-width: 768px) {
	.features-grid,
	.use-cases-grid {
		grid-template-columns: 1fr;
	}
	
	.cta-actions {
		flex-direction: column;
		align-items: stretch;
	}
}
</style>

<?php get_footer(); ?>
