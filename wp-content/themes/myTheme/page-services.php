<?php
/**
 * Template Name: Service
 */
get_header();
?>

<main class="site-content container">
    <h1 class="page-title"><?php the_title(); ?></h1>

    <?php
    // Pagination setup
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = [
        'post_type'      => 'service',   // custom post type
        'posts_per_page' => 6,           // adjust per page
        'paged'          => $paged,
        'order'          => 'DESC',
        'orderby'        => 'date',
    ];

    $services_query = new WP_Query($args);  

    if ($services_query->have_posts()) :
        echo '<div class="row services-list">';
        while ($services_query->have_posts()) : $services_query->the_post(); ?>

            <div class="col-md-4 service-item mb-4">
                <div class="card h-100 text-center p-3">

                    <?php
                    $image = get_field('service_image');
                    if ($image): ?>
                        <a href="<?php the_permalink(); ?>">
                            <img src="<?php echo esc_url($image['url']); ?>"
                                 alt="<?php echo esc_attr($image['alt']); ?>"
                                 class="card-img-top mb-3">
                        </a>
                    <?php endif; ?>

                    <h3 class="card-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_field('service_title'); ?>
                        </a>
                    </h3>

                    <p class="card-text"><?php the_field('service_description'); ?></p>
                </div>
            </div>

        <?php endwhile;
        echo '</div>';

        // Pagination
        echo '<div class="pagination mt-4">';
        echo paginate_links([
            'total' => $services_query->max_num_pages,
        ]);
        echo '</div>';

        wp_reset_postdata();
    else :
        echo '<p>No services found.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>
