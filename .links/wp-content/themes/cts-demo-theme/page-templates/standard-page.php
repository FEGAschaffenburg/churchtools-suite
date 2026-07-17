<?php
/**
 * Template Name: Standard Page
 * Description: Standard-Seiten-Template für alle Unterseiten
 *
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

	<!-- Main Content Section -->
	<div style="padding: 3rem 0; margin: 0 auto; max-width: var(--max-width, 1200px);">
		<div class="container">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
				
			<!-- Page Title (deaktiviert) -->
			<?php /* if ( ! get_post_meta( get_the_ID(), '_hide_title', true ) ) : ?>
				<header class="entry-header" style="margin-bottom: 2rem;">
					<h1 class="entry-title" style="font-size: 2.5rem; margin-bottom: 1rem;">
						<?php the_title(); ?>
					</h1>
				</header>
			<?php endif; */ ?>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="page-thumbnail" style="margin-bottom: 2rem; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
						<?php the_post_thumbnail( 'large', array( 'style' => 'width: 100%; height: auto; display: block;' ) ); ?>
					</div>
				<?php endif; ?>
				
				<!-- Entry Content -->
				<div class="entry-content" style="font-size: 1.05rem; line-height: 1.8;">
					<?php the_content(); ?>
				</div>
				
				<!-- Page Links / Pagination -->
				<?php
				wp_link_pages( array(
					'before' => '<div class="page-links" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;"><strong>' . esc_html__( 'Seiten:', 'cts-demo-theme' ) . '</strong> ',
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
