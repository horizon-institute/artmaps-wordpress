<?php
the_post();
add_filter('wp_title', function() {
    return the_title($echo = false) . ' | ' . get_bloginfo('name');
});
get_header();
?>
<article>
	<div><?php the_content(); ?></div>
</article>
<?php get_footer(); ?>