<?php
/**
 * Template Name: Addons Page (ohne Titel)
 * Description: Template für die Addons-Übersichtsseite ohne Titel-Anzeige
 *
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

	<!-- Main Content Section -->
	<div style="padding: 3rem 0; margin: 0 auto; max-width: var(--max-width);">
		<div class="container">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'addons-page-article' ); ?>>
				
				<!-- Entry Content (ohne Titel) -->
				<div class="entry-content" style="font-size: 1.05rem; line-height: 1.8; color: var(--text-color);">
					<?php the_content(); ?>
				</div>
				
				<!-- Page Links / Pagination -->
				<?php
				wp_link_pages( array(
					'before' => '<div class="page-links" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);"><strong>' . esc_html__( 'Seiten:', 'cts-demo-theme' ) . '</strong> ',
					'after'  => '</div>',
					'link_before' => '<span style="display: inline-block; margin: 0 0.5rem;">',
					'link_after'  => '</span>',
				) );
				?>
				
			</article>
		</div>
	</div>

<?php endwhile; ?>

<?php
get_footer();
?>
