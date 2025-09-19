<?php
get_header();
?>

<main class="site-content container">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post(); ?>
            <article class="single-product">
                <h1 class="product-title"><?php the_field('products_title'); ?></h1>

                <?php
                $image = get_field('products_image');
                if ($image): ?>
                    <img src="<?php echo esc_url($image['url']); ?>"
                        alt="<?php echo esc_attr($image['alt']); ?>"
                        class="img-fluid mb-4">
                <?php endif; ?>

                <div class="product-description">
                    <?php the_field('products_description'); ?>
                </div>

                <p class="product-price"><strong>Price:</strong> <?php the_field('products_price'); ?></p>
            </article>
    <?php endwhile;
    else :
        echo '<p>No product found.</p>';
    endif;
    ?>
    <p><a href="<?php echo esc_url(get_permalink(get_page_by_path('products'))); ?>">← Back to Products</a></p>

</main>

<?php
get_footer();
?>