<?php

# Enqueue stylesheet
function artmaps_theme_style() {
	wp_enqueue_style( 'artmaps-theme', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'artmaps_theme_style' );

# Use HTML5 tags
$args = array(
	'search-form',
	'comment-form',
	'comment-list',
);
add_theme_support( 'html5', $args );

# Set up stripped-down object pages for AJAX
function add_query_vars($vars){
    $vars[] = "framed";
    return $vars;
}
add_filter( 'query_vars', 'add_query_vars');
add_rewrite_endpoint('framed', EP_PERMALINK);
add_filter( 'single_template', 'project_attachments_template' );

function project_attachments_template($templates = ""){
	global $wp_query;

	if(!isset( $wp_query->query['framed'] ))
		return $templates;

	$templates = locate_template( "object-framed.php", false );
	if( empty($templates) ) { $templates = dirname(__FILE__).'/object-framed.php'; }

	return $templates;
}

# Have admin bar overlay site instead of bump down
function my_filter_head() {
	remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'my_filter_head');

# Comment reply script
function theme_queue_js(){
  if ( (!is_admin()) && is_singular() && comments_open() && get_option('thread_comments') )
    wp_enqueue_script( 'comment-reply' );
}
add_action('wp_print_scripts', 'theme_queue_js');

?>