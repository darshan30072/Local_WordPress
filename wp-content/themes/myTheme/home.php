<?php get_header(); ?>

<main class="site-content container">
    <?php
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id) :
        $posts_page = get_post($posts_page_id);
        if ($posts_page && !empty($posts_page->post_content)) :
            echo '<div class="page-content">';
            echo apply_filters('the_content', $posts_page->post_content);
            echo '</div>';
        endif;
    endif;
    ?>

    <?php
    // Show the content of the "Posts page" (as set in Settings → Reading → Posts page)
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id) {
        // Check if Elementor is active
        if (did_action('elementor/loaded')) {
            echo '<div class="elementor-page-content">';
            echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($posts_page_id);
            echo '</div>';
        } else {
            // Fallback: show regular content if Elementor not used
            $posts_page = get_post($posts_page_id);
            if ($posts_page && !empty($posts_page->post_content)) {
                echo '<div class="page-content">';
                echo apply_filters('the_content', $posts_page->post_content);
                echo '</div>';
            }
        }
    }
    ?>


    <?php if (have_posts()) : ?>
        <h2 class="page-title"><?php echo get_the_title($posts_page_id); ?></h2>

        <div class="post-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('grid-item'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <h3 class="post-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <div class="entry-excerpt">
                        <?php the_excerpt(); ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                </article>
            <?php endwhile; ?>
        </div>


        <!-- Pagination -->
        <div class="pagination">
            <?php
            if (function_exists('wp_pagenavi')) {
                wp_pagenavi();
            } else {
                // Fallback default pagination
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('« Prev'),
                    'next_text' => __('Next »'),
                ));
            }
            ?>
        </div>

    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>