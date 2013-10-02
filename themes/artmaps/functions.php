<?php

// Allow logo to be customised from admin
$args = array(
	'width'         => 148,
	'height'        => 61,
	'uploads'       => true
);
add_theme_support( 'custom-header', $args );

// Set up stripped-down object pages for AJAX
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

?>