<?php get_header(); ?>

<main>
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
      <article>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php if ( has_post_thumbnail() ) : ?>
          <?php the_post_thumbnail('medium'); ?>
        <?php endif; ?>
        <?php the_excerpt(); ?>
      </article>
    <?php endwhile; ?>

    <div class="pagination">
      <?php the_posts_pagination(); ?>
    </div>
  <?php else : ?>
    <p>No posts found.</p>
  <?php endif; ?>
</main>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
