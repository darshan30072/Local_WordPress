<?php get_header(); ?>

<main class="site-content">
    <?php if ( have_posts() ) : ?>
        <h2><?php the_title(); ?></h2>

        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="entry-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>

        <!-- Add pagination -->
        <div class="pagination">
            <?php
            the_posts_pagination(array(
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ));
            ?>
        </div>

    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
