<?php
if ( ! defined( "ABSPATH" ) ) exit;
get_header();
?>
<div class="site-content">
    <div class="container">
        <!-- DEBUG: Global Query Check -->
        <!-- is_page: <?php echo is_page() ? "YES" : "NO"; ?> -->
        <!-- is_single: <?php echo is_single() ? "YES" : "NO"; ?> -->
        <!-- have_posts: <?php echo have_posts() ? "YES" : "NO"; ?> -->
        <!-- query_posts: <?php global $wp_query; echo $wp_query->post_count ?? "0"; ?> -->
        
        <?php
        global $post;
        
        // For single pages and posts - use global $post directly
        if ( is_page() || is_single() ) :
            
            if ( $post ) :
                ?>
                <article id="post-<?php echo esc_attr( $post->ID ); ?>" class="post-<?php echo esc_attr( $post->ID ); ?>">
                    <header class="entry-header">
                        <h1 class="entry-title"><?php echo esc_html( $post->post_title ); ?></h1>
                    </header>
                    <div class="entry-content">
                        <?php echo wp_kses_post( apply_filters( "the_content", $post->post_content ) ); ?>
                    </div>
                </article>
                <?php
            else :
                echo "<p>Post object not set</p>";
            endif;
            
        else :
            // For archives - use the loop
            if ( have_posts() ) :
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="entry-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                    <?php
                endwhile;
            else :
                echo "<p>Keine Beiträge gefunden.</p>";
            endif;
        endif;
        ?>
    </div>
</div>
<?php
get_footer();
