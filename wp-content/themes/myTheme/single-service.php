<?php
get_header();
?>

<main class="site-content container">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post(); ?>
            <article class="single-service">
                <h1 class="service-title"><?php the_field('service_title'); ?></h1>

                <?php
                $image = get_field('service_image');
                if ($image): ?>
                    <img src="<?php echo esc_url($image['url']); ?>"
                         alt="<?php echo esc_attr($image['alt']); ?>"
                         class="img-fluid mb-4">
                <?php endif; ?>

                <div class="service-description">
                    <?php the_field('service_description'); ?>
                </div>
            </article>
    <?php endwhile;
    else :
        echo '<p>No service found.</p>';
    endif;
    ?>

    <p><a href="<?php echo get_permalink(get_page_by_path('services')); ?>">← Back to Services</a></p>
</main>

<?php
get_footer();
?>
