<?php get_header(); ?>

<main class="site-content">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1 class="entry-title"><?php the_title(); ?></h1>

                <div class="news-meta">
                    <p>
                        Categories:
                        <?php
                        // Display assigned categories for this news post
                        the_terms(get_the_ID(), 'news_category', '', ', ');
                        ?>
                    </p>
                </div>

                <div class="entry-meta">
                    <p><strong>Date:</strong> <?php the_field('news_date'); ?> |
                        <strong>Source:</strong> <?php the_field('source'); ?>
                    </p>
                </div>
                <p></p>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="news-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <?php if (get_field('external_link')) : ?>
                    <p><a href="<?php the_field('external_link'); ?>" target="_blank">Read More →</a></p>
                <?php endif; ?>
            </article>

            <!-- Navigation between single news -->
            <nav class="post-navigation">
                <div class="nav-links">
                    <div class="nav-previous"><?php previous_post_link('%link', '← Previous News'); ?></div>
                    <div class="nav-next"><?php next_post_link('%link', 'Next News →'); ?></div>
                </div>
            </nav>

            <!-- Comments (optional) -->
            <?php
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>

    <?php endwhile;
    else :
        echo '<p>No news found.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>