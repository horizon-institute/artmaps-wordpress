<?php

# AJAX comment handler
add_action('comment_post', 'ajaxify_comments',20, 2);
function ajaxify_comments($comment_ID, $comment_status){
  if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
    // If AJAX request Then
    switch($comment_status){
    case '0': // Comment needs approval
    wp_notify_moderator($comment_ID);
    case '1': // Comment published
    echo "success";
    $commentdata=&get_comment($comment_ID, ARRAY_A);
    $post=&get_post($commentdata['comment_post_ID']);
    wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
    break;
    default:
    echo "error";
  }
  exit;
  }
}

# Hide admin bar for non admins
if (!current_user_can('administrator')):
  show_admin_bar(false);
endif;

# Enqueue stylesheet
function artmaps_theme_style() {
	wp_enqueue_style( 'artmaps-theme-style', get_stylesheet_uri() );
	wp_enqueue_style( 'artmaps-theme-style-icons', get_stylesheet_directory_uri().'/font-awesome.min.css' );
	
	wp_register_script( 'artmaps-theme-scripts', get_stylesheet_directory_uri().'/js/scripts.js', 'jquery' );
	wp_enqueue_script( 'artmaps-theme-scripts' );
	wp_localize_script( 'artmaps-theme-scripts', 'ajax_login_object', array( 
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'redirecturl' => home_url(),
      'loadingmessage' => __('Logging in&hellip;')
  ));

  // Enable the user with no privileges to run ajax_login() in AJAX
  add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
  
}
add_action( 'wp_enqueue_scripts', 'artmaps_theme_style' );

add_action( 'login_form_middle', 'add_lost_password_link' );
function add_lost_password_link() {
  return '<a href="/wp-login.php?action=lostpassword" class="forgot-password">Forgot?</a>'.
  wp_nonce_field( 'ajax-login-nonce', 'security', true, false );
}


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

function ajax_login(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }

    die();
}

?>