<div class="about-page">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <h1><?php the_title(); ?></h1>
    <?php
      function autoblank($text) {
      	$return = str_replace('<a', '<a target="_blank"', $text);
      	return $return;
      }
      add_filter('the_content', 'autoblank');
      the_content();
      remove_filter('the_content', 'autoblank');
    ?>
  <?php endwhile; endif; ?>
</div>