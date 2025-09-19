<?php
/*
Template Name: Media
*/
get_header();
?>

<main class="site-content">
    <h1 class="page-title"><?php the_title(); ?></h1>

    <div class="news-filter">
        <ul>
            <?php
            $terms = get_terms([
                'taxonomy' => 'news_category',
                'hide_empty' => true,
            ]);

            foreach ($terms as $term) {
                echo '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a></li>';
            }
            ?>
        </ul>
    </div>

    <?php
    // Pagination setup
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'news',
        'posts_per_page' => 3,
        'paged'          => $paged,
        'order'          => 'DESC',
        'orderby'        => 'date',
    );

    $news_query = new WP_Query($args);

    if ($news_query->have_posts()) :
        echo '<div class="news-list">';
        while ($news_query->have_posts()) : $news_query->the_post(); ?>

            <article class="news-item" id="post-<?php the_ID(); ?>">
                <h2 class="news-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="news-thumbnail">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php endif; ?>

                <div class="news-meta">
                    <p><strong>Date:</strong> <?php the_field('news_date'); ?></p>
                    <p><strong>Source:</strong> <?php the_field('source'); ?></p>
                </div>

                <div class="news-excerpt">
                    <?php the_excerpt(); ?>
                </div>

                <?php if (get_field('external_link')) : ?>
                    <p><a href="<?php the_field('external_link'); ?>" target="_blank">Read More →</a></p>
                <?php endif; ?>
            </article>

    <?php endwhile;
        echo '</div>';

        // Pagination
        echo '<div class="pagination">';
        echo paginate_links(array(
            'total' => $news_query->max_num_pages,
        ));
        echo '</div>';

        wp_reset_postdata();
    else :
        echo '<p>No news found.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>