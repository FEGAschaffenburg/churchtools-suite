<?php
/**
 * Template Name: Dokumentation
 * 
 * @package CTS_Demo_Theme
 */

get_header(); ?>

<main class="documentation-page">
	<div class="container">
		<style>
			.documentation-page .doc-nav.doc-nav--inline {
				display: flex;
				align-items: center;
				gap: 0.6rem;
				flex-wrap: wrap;
				margin: 0 0 1rem;
				padding: 0;
				border: 0;
				background: transparent;
			}
			.documentation-page .doc-nav.doc-nav--inline h2 {
				font-size: 1rem;
				margin: 0;
				line-height: 1;
			}
			.documentation-page .doc-nav.doc-nav--inline ul {
				display: flex;
				gap: 0.45rem;
				flex-wrap: wrap;
				list-style: none;
				margin: 0;
				padding: 0;
			}
			.documentation-page .doc-nav.doc-nav--inline li {
				margin: 0;
			}
			.documentation-page .doc-nav.doc-nav--inline a {
				display: inline-block;
				padding: 0.3rem 0.65rem;
				border-radius: 999px;
				border: 1px solid #cfd8e3;
				background: #f7f9fc;
				font-size: 0.9rem;
				line-height: 1.2;
				text-decoration: none;
			}

			/* Screenshots verkleinert */
			.doc-screenshot {
				margin: 1rem 0;
			}
			.doc-screenshot img {
				max-width: 420px;
				width: 100%;
				height: auto;
				border: 1px solid #dde3ea;
				border-radius: 6px;
				box-shadow: 0 2px 8px rgba(0,0,0,0.08);
				display: block;
			}
			.doc-screenshot figcaption {
				margin-top: 0.35rem;
				font-size: 0.8rem;
				color: #6b7280;
			}

			/* Konfigurations-Grid */
			.doc-config-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
				gap: 1rem;
				margin: 1rem 0 1.25rem;
			}
			.doc-config-item {
				background: #f7f9fc;
				border: 1px solid #dde3ea;
				border-radius: 8px;
				padding: 1rem;
			}
			.doc-config-item h4 {
				margin: 0 0 0.5rem;
				font-size: 0.95rem;
				color: #1f4e79;
			}
			.doc-config-item p {
				margin: 0 0 0.5rem;
				font-size: 0.88rem;
			}
			.doc-config-item .code-block {
				font-size: 0.82rem;
				padding: 0.4rem 0.6rem;
			}
		</style>
		
		<!-- Hero Section -->
		<section class="page-hero">
		<!-- <h1><?php the_title(); ?></h1> -->
			<p class="lead">Alles was du wissen musst, um ChurchTools Suite optimal zu nutzen</p>
		</section>

		<!-- Quick Navigation -->
		<nav class="doc-nav doc-nav--inline">
			<h2>Inhalt</h2>
			<ul>
				<li><a href="#quick-start">Quick Start Guide</a></li>
				<li><a href="#shortcodes">Shortcode-Referenz</a></li>
				<li><a href="#views">View-Typen</a></li>
				<li><a href="#templates">Template-System</a></li>
				<li><a href="#troubleshooting">Troubleshooting</a></li>
			</ul>
		</nav>

		<!-- Quick Start Guide -->
		<section id="quick-start" class="doc-section">
			<h2>Quick Start Guide</h2>
			<p class="section-intro">In 5 Minuten von der Installation zur ersten Event-Anzeige</p>

			<div class="guide-steps">
				<div class="guide-step">
					<h3>1. ChurchTools-Verbindung herstellen</h3>
					<p>Gehe zu <strong>ChurchTools Suite → Einstellungen → ChurchTools</strong></p>
					<div class="code-block">
						<strong>Benötigte Daten:</strong><br>
						• ChurchTools-URL (z.B. https://deine-gemeinde.church.tools)<br>
						• Benutzername (API-Zugang erforderlich)<br>
						• Passwort
					</div>
					<p>Klicke auf <strong>Verbindung testen</strong> - bei Erfolg siehst du eine grüne Bestätigung.</p>
					<figure class="doc-screenshot">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/docs/churchtools-einstellungen.png' ); ?>" alt="ChurchTools Einstellungen im WordPress Admin" loading="lazy">
						<figcaption>ChurchTools-Einstellungen</figcaption>
					</figure>
				</div>

				<div class="guide-step">
					<h3>2. Kalender auswählen</h3>
					<p>Gehe zu <strong>ChurchTools Suite → Daten → Kalender</strong></p>
					<ol>
						<li>Klicke auf <strong>Kalender synchronisieren</strong></li>
						<li>Wähle die gewünschten Kalender per Checkbox aus</li>
						<li>Klicke auf <strong>Auswahl speichern</strong></li>
					</ol>
					<figure class="doc-screenshot">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/docs/kalender-auswahl.png' ); ?>" alt="Kalender-Auswahl in ChurchTools Suite" loading="lazy">
						<figcaption>Kalender-Auswahl</figcaption>
					</figure>
				</div>

				<div class="guide-step">
					<h3>3. Events synchronisieren</h3>
					<p>Gehe zu <strong>ChurchTools Suite → Events</strong></p>
					<ol>
						<li>Klicke auf <strong>Events jetzt synchronisieren</strong></li>
						<li>Warte bis der Sync abgeschlossen ist (Status: Erfolg)</li>
						<li>Du siehst nun eine Übersicht: X Events importiert</li>
					</ol>
					<div class="info-box">
						<strong>Tipp:</strong> Unter <strong>Einstellungen → Synchronisation</strong> kannst du einen automatischen Sync einrichten (stündlich/täglich).
					</div>
					<figure class="doc-screenshot">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/docs/event-sync.png' ); ?>" alt="Event-Synchronisation in ChurchTools Suite" loading="lazy">
						<figcaption>Event-Sync</figcaption>
					</figure>
				</div>

				<div class="guide-step">
					<h3>4. Ersten Shortcode einfügen</h3>
					<p>Erstelle eine neue Seite oder bearbeite eine bestehende</p>
					<div class="code-block">
						<code>[cts_events view="list"]</code>
					</div>
					<p>Alternativ nutze den Gutenberg-Block:</p>
					<ol>
						<li>Klicke auf <strong>Block hinzufügen</strong> (+)</li>
						<li>Suche nach <strong>ChurchTools Events</strong></li>
						<li>Wähle eine View-Vorlage (List, Grid, Calendar)</li>
						<li>Passe Filter an (optional)</li>
					</ol>
					<figure class="doc-screenshot">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/docs/gutenberg-block.png' ); ?>" alt="Gutenberg Editor mit ChurchTools Block-Suche" loading="lazy">
						<figcaption>Gutenberg-Block</figcaption>
					</figure>
				</div>

				<div class="guide-step">
					<h3>5. Block konfigurieren</h3>
					<p>Nach dem Einfügen kannst du den Block oder Shortcode direkt im Editor anpassen:</p>

					<div class="doc-config-grid">
						<div class="doc-config-item">
							<h4>View-Typ wählen</h4>
							<p>Das Attribut <code>view</code> bestimmt das Anzeigelayout:</p>
							<div class="code-block"><code>view="list-classic"</code><br><code>view="grid-modern"</code><br><code>view="calendar-monthly-simple"</code></div>
						</div>
						<div class="doc-config-item">
							<h4>Kalender filtern</h4>
							<p>Mit <code>calendar_ids</code> werden nur bestimmte Kalender angezeigt:</p>
							<div class="code-block"><code>calendar_ids="1,2,3"</code></div>
							<p>Die Kalender-IDs findest du unter <strong>ChurchTools Suite → Kalender</strong>.</p>
						</div>
						<div class="doc-config-item">
							<h4>Anzahl begrenzen</h4>
							<p>Mit <code>limit</code> steuerst du wie viele Events angezeigt werden:</p>
							<div class="code-block"><code>limit="10"</code></div>
						</div>
						<div class="doc-config-item">
							<h4>Klick-Verhalten</h4>
							<p><code>event_action</code> legt fest, was beim Klick auf ein Event passiert:</p>
							<div class="code-block"><code>event_action="modal"</code><br><code>event_action="single"</code></div>
						</div>
					</div>

					<figure class="doc-screenshot">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/docs/block-konfiguration-editor.png' ); ?>" alt="Gutenberg Editor mit ChurchTools Shortcode und Konfigurations-Optionen" loading="lazy">
						<figcaption>Block-Konfiguration im Gutenberg-Editor – oben der Shortcode, unten die verfügbaren Optionen</figcaption>
					</figure>
				</div>

				<div class="guide-step">
					<h3>6. Seite veröffentlichen</h3>
					<p>Klicke auf <strong>Veröffentlichen</strong> und bewundere deine Events! 🎉</p>
				</div>
			</div>
		</section>

		<!-- Shortcode Reference -->
		<section id="shortcodes" class="doc-section">
			<h2>Shortcode-Referenz</h2>
			<p class="section-intro">Alle verfügbaren Shortcodes und ihre Attribute</p>

			<div class="shortcode-reference">
				
				<!-- Events Shortcode -->
				<div class="shortcode-block">
					<h3>Events anzeigen</h3>
					<div class="code-block">
						<code>[cts_events view="list" calendar_ids="1,2" limit="10" order="ASC"]</code>
					</div>
					
					<h4>Attribute:</h4>
					<table class="attribute-table">
						<thead>
							<tr>
								<th>Attribut</th>
								<th>Werte</th>
								<th>Standard</th>
								<th>Beschreibung</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>view</code></td>
								<td>list, grid, calendar</td>
								<td>list</td>
								<td>View-Typ für die Anzeige</td>
							</tr>
							<tr>
								<td><code>template</code></td>
								<td>modern, classic, compact, cards, masonry, monthly</td>
								<td>modern</td>
								<td>Template-Variante (abhängig von view)</td>
							</tr>
							<tr>
								<td><code>calendar_ids</code></td>
								<td>1,2,3 oder leer</td>
								<td>alle</td>
								<td>Kommaseparierte Kalender-IDs</td>
							</tr>
							<tr>
								<td><code>limit</code></td>
								<td>Zahl</td>
								<td>10</td>
								<td>Anzahl der Events (bei list/grid)</td>
							</tr>
							<tr>
								<td><code>order</code></td>
								<td>ASC, DESC</td>
								<td>ASC</td>
								<td>Sortierung nach Datum</td>
							</tr>
							<tr>
								<td><code>show_past</code></td>
								<td>true, false</td>
								<td>false</td>
								<td>Vergangene Events anzeigen</td>
							</tr>
							<tr>
								<td><code>show_services</code></td>
								<td>true, false</td>
								<td>true</td>
								<td>Services mit Personen anzeigen</td>
							</tr>
							<tr>
								<td><code>show_tags</code></td>
								<td>true, false</td>
								<td>true</td>
								<td>Event-Tags anzeigen</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Single Event Shortcode -->
				<div class="shortcode-block">
					<h3>Einzelnes Event anzeigen</h3>
					<div class="code-block">
						<code>[cts_single_event id="123" template="default"]</code>
					</div>
					
					<h4>Attribute:</h4>
					<table class="attribute-table">
						<thead>
							<tr>
								<th>Attribut</th>
								<th>Werte</th>
								<th>Standard</th>
								<th>Beschreibung</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>id</code></td>
								<td>Event-ID</td>
								<td>-</td>
								<td>Pflichtfeld: Event-ID aus Datenbank</td>
							</tr>
							<tr>
								<td><code>template</code></td>
								<td>default, minimal, full</td>
								<td>default</td>
								<td>Template-Variante</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Calendar Widget Shortcode -->
				<div class="shortcode-block">
					<h3>Kalender-Widget</h3>
					<div class="code-block">
						<code>[cts_calendar_widget style="badges"]</code>
					</div>
					
					<h4>Attribute:</h4>
					<table class="attribute-table">
						<thead>
							<tr>
								<th>Attribut</th>
								<th>Werte</th>
								<th>Standard</th>
								<th>Beschreibung</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>style</code></td>
								<td>badges, list, cards</td>
								<td>badges</td>
								<td>Anzeige-Stil</td>
							</tr>
						</tbody>
					</table>
				</div>

			</div>
		</section>

		<!-- View Types Reference -->
		<section id="views" class="doc-section">
			<h2>View-Typen Übersicht</h2>
			<p class="section-intro">Alle verfügbaren View-Templates sind ausführlich dokumentiert:</p>

			<div class="view-reference-links">
				<div class="view-link-card">
					<h3>📋 List-Ansichten</h3>
					<p>Chronologische Event-Auflistungen in verschiedenen Detailstufen</p>
					<ul>
						<li><a href="/list-ansichten/list-modern/">List Modern</a> - Event-Karten mit großen Bildern</li>
						<li><a href="/list-ansichten/list-classic/">List Classic</a> - Kompakte Liste mit kleineren Bildern</li>
						<li><a href="/list-ansichten/list-compact/">List Compact</a> - Minimale Darstellung für Sidebars</li>
					</ul>
					<a href="/list-ansichten/" class="btn btn-small">Alle List-Views →</a>
				</div>

				<div class="view-link-card">
					<h3>🎯 Grid-Ansichten</h3>
					<p>Events in flexiblen Raster-Layouts präsentieren</p>
					<ul>
						<li><a href="/grid-ansichten/grid-cards/">Grid Cards</a> - 3-Spalten-Grid mit Event-Karten</li>
						<li><a href="/grid-ansichten/grid-compact/">Grid Compact</a> - 4-Spalten-Grid kompakt</li>
						<li><a href="/grid-ansichten/grid-masonry/">Grid Masonry</a> - Pinterest-Style mit variablen Höhen</li>
					</ul>
					<a href="/grid-ansichten/" class="btn btn-small">Alle Grid-Views →</a>
				</div>

				<div class="view-link-card">
					<h3>📅 Calendar-Ansichten</h3>
					<p>Events in klassischer Kalender-Form darstellen</p>
					<ul>
						<li><a href="/calendar-ansichten/calendar-monthly/">Calendar Monthly</a> - Vollständiger Monatskalender</li>
					</ul>
					<a href="/calendar-ansichten/" class="btn btn-small">Alle Calendar-Views →</a>
				</div>

				<div class="view-link-card">
					<h3>🔍 Single-Event-Ansichten</h3>
					<p>Einzelne Events mit allen Details präsentieren</p>
					<ul>
						<li><a href="/single-event-ansichten/single-event-detail/">Single Event Detail</a> - Vollständige Event-Seite</li>
						<li><a href="/single-event-ansichten/single-event-modal/">Single Event Modal</a> - Kompakte Modal-Darstellung</li>
					</ul>
					<a href="/single-event-ansichten/" class="btn btn-small">Alle Single-Views →</a>
				</div>
			</div>

			<div class="info-box">
				<p><strong>💡 Tipp:</strong> Jede View-Seite enthält Screenshots, Code-Beispiele und Anwendungsfälle.</p>
			</div>

			<div class="view-click-behavior">
				<h3>🖱️ Klick-Verhalten (Modal oder SingleSite)</h3>
				<p>Lege fest, was beim Klick auf ein Event geöffnet wird.</p>

				<div class="view-click-grid">
					<details class="view-click-card" open>
						<summary>Modal öffnen</summary>
						<p>Öffnet Event-Details in einem Popup. Das ist das Standardverhalten bei vielen Listen- und Grid-Views.</p>
						<div class="code-block"><code>event_action="modal"</code></div>
						<a class="btn btn-small" href="<?php echo esc_url( home_url( '/?page_id=30' ) ); ?>">Modal-Beispiel öffnen</a>
					</details>

					<details class="view-click-card" open>
						<summary>SingleSite öffnen</summary>
						<p>Öffnet ein Event auf einer eigenen Seite. Ideal für SEO und ausführliche Eventseiten.</p>
						<div class="code-block"><code>event_action="single"</code></div>
						<div class="view-click-links">
							<a class="btn btn-small" href="<?php echo esc_url( home_url( '/?page_id=44' ) ); ?>">Single Minimal</a>
							<a class="btn btn-small" href="<?php echo esc_url( home_url( '/?page_id=45' ) ); ?>">Single Professional</a>
						</div>
					</details>
				</div>
			</div>
		</section>

		<!-- Template System -->
		<section id="templates" class="doc-section">
			<h2>Template-Auswahl</h2>
			<p class="section-intro">Single Event und Modal Templates konfigurieren</p>

			<div class="template-docs">
				<h3>Single Page Templates</h3>
				<p>Diese Einstellung legt fest, welches Template verwendet wird, wenn ein Event auf einer eigenen Seite angezeigt wird (über URL-Parameter <code>?event_id=123</code> oder Shortcode <code>[cts_event id="123"]</code>).</p>
				
				<div class="guide-step">
					<h4>Standard Single Template</h4>
					<p>Im Plugin-Backend unter <strong>Einstellungen → Templates → Single Event</strong> kannst du zwischen verschiedenen Designs wählen:</p>
					<ul>
						<li><strong>minimal</strong> - Minimalistisches Design, perfekt für schlichte Layouts</li>
						<li><strong>professional</strong> - Umfangreiches Template mit allen Details</li>
					</ul>
					<div class="info-box">
						<strong>💡 Tipp:</strong> Wenn du keine Template-Auswahl siehst, nutzt dein Plugin automatisch das Standard-Template.
					</div>
				</div>

				<h3>Modal Templates</h3>
				<p>Diese Einstellung bestimmt, wie Events im Popup/Modal-Fenster dargestellt werden (Standard-Verhalten bei Click auf Event-Links).</p>
				
				<div class="guide-step">
					<h4>Standard Modal Template</h4>
					<p>Im Plugin-Backend unter <strong>Einstellungen → Templates → Modal</strong> kannst du das Modal-Design festlegen:</p>
					<ul>
						<li><strong>minimal</strong> - Kompaktes Modal mit den wichtigsten Infos</li>
						<li><strong>professional</strong> - Detailliertes Modal mit vollständigen Event-Daten</li>
					</ul>
					<div class="info-box">
						<strong>⚠️ Hinweis:</strong> Das Modal-Template wird nur verwendet, wenn <code>event_action="modal"</code> gesetzt ist (Standard-Verhalten).
					</div>
				</div>

			</div>
		</section>

		<!-- Troubleshooting -->
		<section id="troubleshooting" class="doc-section">
			<h2>Troubleshooting</h2>
			<p class="section-intro">Häufige Probleme und ihre Lösungen</p>

			<div class="troubleshooting-list">
				
				<div class="trouble-item">
					<h3>🔴 Verbindung zu ChurchTools fehlgeschlagen</h3>
					<h4>Mögliche Ursachen:</h4>
					<ul>
						<li>Falsche ChurchTools-URL (prüfe https:// und Subdomain)</li>
						<li>Benutzername/Passwort falsch</li>
						<li>Kein API-Zugang für den Account</li>
						<li>ChurchTools-Server nicht erreichbar</li>
					</ul>
					<h4>Lösung:</h4>
					<ol>
						<li>Prüfe die URL in einem Browser</li>
						<li>Teste Login direkt in ChurchTools</li>
						<li>Aktiviere <strong>Erweitert-Modus</strong> für detaillierte Fehler-Logs</li>
					</ol>
				</div>

				<div class="trouble-item">
					<h3>🔴 Events werden nicht synchronisiert</h3>
					<h4>Mögliche Ursachen:</h4>
					<ul>
						<li>Keine Kalender ausgewählt</li>
						<li>Zeitraum-Einstellungen zu eng</li>
						<li>WP-Cron deaktiviert</li>
					</ul>
					<h4>Lösung:</h4>
					<ol>
						<li>Gehe zu <strong>Daten → Kalender</strong> und wähle mind. 1 Kalender</li>
						<li>Unter <strong>Einstellungen → Synchronisation</strong> Zeitraum anpassen</li>
						<li>Manuellen Sync über <strong>Events → Jetzt synchronisieren</strong> testen</li>
					</ol>
				</div>

				<div class="trouble-item">
					<h3>🔴 Shortcode zeigt nur Text, keine Events</h3>
					<h4>Mögliche Ursachen:</h4>
					<ul>
						<li>Keine Events in der Datenbank</li>
						<li>Falsche View/Template-Kombination</li>
						<li>PHP-Fehler im Template</li>
					</ul>
					<h4>Lösung:</h4>
					<ol>
						<li>Prüfe unter <strong>Events</strong> ob Events vorhanden sind</li>
						<li>Aktiviere <strong>WP_DEBUG</strong> in wp-config.php</li>
						<li>Prüfe Browser-Console auf JavaScript-Fehler</li>
					</ol>
				</div>

				<div class="trouble-item">
					<h3>🟡 Performance-Probleme bei vielen Events</h3>
					<h4>Optimierungen:</h4>
					<ul>
						<li>Nutze <code>limit</code>-Attribut im Shortcode</li>
						<li>Aktiviere Caching-Plugin (z.B. WP Super Cache)</li>
						<li>Erhöhe <code>memory_limit</code> in php.ini auf 256M</li>
						<li>Nutze <strong>Incremental Sync</strong> statt Full Sync</li>
					</ul>
				</div>

			</div>

			<div class="debug-section">
				<h3>Debug-Modus aktivieren</h3>
				<p>Für detaillierte Fehler-Logs:</p>
				<ol>
					<li>Gehe zu <strong>ChurchTools Suite → Einstellungen → Erweiterte Optionen</strong></li>
					<li>Aktiviere <strong>Erweitert-Modus</strong></li>
					<li>Gehe zu <strong>Erweitert → Logs</strong></li>
				</ol>
			</div>
		</section>

		<!-- Support CTA -->
		<section class="support-cta">
			<h2>Noch Fragen?</h2>
			<p>Wir helfen gerne weiter!</p>
			<div class="cta-buttons">
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite/issues" target="_blank" class="btn btn-primary">
					Issue auf GitHub erstellen
				</a>
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite/discussions" target="_blank" class="btn btn-secondary">
					In Discussions fragen
				</a>
			</div>
		</section>

	</div>
</main>

<?php get_footer(); ?>
