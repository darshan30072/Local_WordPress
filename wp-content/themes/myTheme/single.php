<?php get_header(); ?>

<main class="site-content">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                
                <div class="entry-meta">
                    <p>Posted on <?php echo get_the_date(); ?> by <?php the_author(); ?></p>
                </div>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>

            <!-- Post Pagination (Previous / Next) -->
            <nav class="post-navigation">
                <div class="nav-links">
                    <div class="nav-previous">
                        <?php previous_post_link( '%link', '← Previous Post: %title' ); ?>
                    </div>
                    <div class="nav-next">
                        <?php next_post_link( '%link', 'Next Post: %title →' ); ?>
                    </div>
                </div>
            </nav>

            <!-- Comments Section -->
            <?php
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        <?php endwhile;
    else :
        echo '<p>No post found.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>
