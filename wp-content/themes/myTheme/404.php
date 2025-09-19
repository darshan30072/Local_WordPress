<?php get_header(); ?>

<main class="site-content py-5">
    <section class="error-404 not-found">
        <h1 class="page-title py-3">404 - Page Not Found</h1>
        <p>Sorry, the page you are looking for doesn’t exist or has been moved.</p>

        <div class="error-actions py-3  ">
            <a href="<?php echo home_url(); ?>" class="btn-home">← Back to Home</a>
            <?php get_search_form(); ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
