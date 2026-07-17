<?php
/**
 * Single Demo Page Template
 * 
 * Frontend display für öffentliche Demo Pages (CPT: cts_demo_page).
 * Zeigt den Inhalt mit Gutenberg-Blocks als eigenständige Seite.
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.2
 */

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'cts-demo-page' ); ?>>
				
				<!-- Header mit Titel -->
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					
					<?php
					// Excerpt anzeigen, falls vorhanden
					if ( has_excerpt() ) {
						?>
						<div class="entry-summary">
							<?php the_excerpt(); ?>
						</div>
						<?php
					}
					?>
				</header><!-- .entry-header -->

				<!-- Info-Banner für Backend-Hinweis -->
				<div class="cts-demo-info-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; margin: 2rem 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
					<div style="display: flex; align-items: center; gap: 1rem;">
						<span style="font-size: 2rem; flex-shrink: 0;">💡</span>
						<div>
							<h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: white;">Eigene Demo-Seiten erstellen</h3>
							<p style="margin: 0; font-size: 0.95rem; line-height: 1.5; opacity: 0.95;">
								Sie können im Backend eigene Seiten mit individuellen Einstellungen erstellen!<br>
								<strong>→ Demo Pages > Neue Seite hinzufügen</strong><br>
								Verwenden Sie den Gutenberg-Block <strong>"ChurchTools Events"</strong> oder Shortcodes wie <code style="background: rgba(255,255,255,0.2); padding: 0.2rem 0.4rem; border-radius: 3px; font-size: 0.9rem;">[cts_list view="minimal"]</code>
							</p>
						</div>
					</div>
				</div>

				<!-- Featured Image -->
				<?php
				if ( has_post_thumbnail() ) {
					?>
					<div class="post-thumbnail">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
					<?php
				}
				?>

				<!-- Hauptinhalt mit Gutenberg-Blocks -->
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages(
						[
							'before'      => '<div class="page-links"><span class="page-links-title">Seiten:</span>',
							'after'       => '</div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						]
					);
					?>
				</div><!-- .entry-content -->

				<!-- Meta-Informationen (optional) -->
				<footer class="entry-footer" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; font-size: 0.875rem; color: #6b7280;">
					<?php
					/* Translators: %s = Author Name */
					printf(
						'Demo Page von: %s',
						'<strong>' . esc_html( get_the_author() ) . '</strong>'
					);
					echo ' • ';
					/* Translators: %s = Publication Date */
					printf(
						'Veröffentlicht: %s',
						'<strong>' . esc_html( get_the_date() ) . '</strong>'
					);
					?>
				</footer><!-- .entry-footer -->

			</article><!-- #post-<?php the_ID(); ?> -->

			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
