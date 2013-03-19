<?php
add_filter('show_admin_bar', '__return_false');
remove_action('wp_head', '_admin_bar_bump_cb');

foreach(array('artmaps-template-general') as $style)
    wp_enqueue_style($style);
if(have_posts())
    the_post();
global $ArtmapsPageTitle;
$ArtmapsPageTitle = the_title('', '', false);
get_header();
?>
<article>
	<div><?php the_content(); ?></div>
</article>
<?php get_footer(); ?>