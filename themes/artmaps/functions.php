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

# Open comment links in new tab
function comment_links_filter($text) {
  $return = str_replace('<a', '<a target="_blank"', $text);
  return $return;
}
add_filter('comment_text', 'comment_links_filter');

# Block author links
function comment_author_link_filter($text) {
  return strip_tags($text);
}
add_filter('get_comment_author_link', 'comment_author_link_filter');

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

}
add_action( 'wp_enqueue_scripts', 'artmaps_theme_style' );

# Hook forgot password link to login form
add_action( 'login_form_middle', 'add_lost_password_link' );
function add_lost_password_link() {
  return '<div class="loader"></div><a href="/wp-login.php?action=lostpassword" class="forgot-password">Forgot password?</a>'.
  wp_nonce_field( 'ajax-login-nonce', 'security', true, false );
}

# Use HTML5 tags
$args = array(
	'search-form',
	'comment-form',
	'comment-list',
);
add_theme_support( 'html5', $args );

# Have admin bar overlay site instead of bump down
function my_filter_head() {
	remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('get_header', 'my_filter_head');

# Rename Posts to Challenges in backend
function artmaps_change_post_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'Challenges';
    $submenu['edit.php'][5][0] = 'Challenges';
    $submenu['edit.php'][10][0] = 'Add Challenge';
    $submenu['edit.php'][16][0] = 'Challenge Tags';
    echo '';
}
function artmaps_change_post_object() {
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name = 'Challenge';
    $labels->singular_name = 'Challenge';
    $labels->add_new = 'Add Challenge';
    $labels->add_new_item = 'Add Challenge';
    $labels->edit_item = 'Edit Challenge';
    $labels->new_item = 'Challenge';
    $labels->view_item = 'View Challenge';
    $labels->search_items = 'Search challenges';
    $labels->not_found = 'No challenges found';
    $labels->not_found_in_trash = 'No challenges found in Trash';
    $labels->all_items = 'All Challenges';
    $labels->menu_name = 'Challenges';
    $labels->name_admin_bar = 'Challenge';
}
 
add_action( 'admin_menu', 'artmaps_change_post_label' );
add_action( 'init', 'artmaps_change_post_object' );

// Remove login with ajax plugin's default css
function remove_login_with_ajax_css(){
  wp_dequeue_style("login-with-ajax");
}
add_action('init', 'remove_login_with_ajax_css');

function artmaps_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
		<?php endif; ?>
		<div class="comment-author vcard">
		<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
		<?php echo get_comment_author(); ?>
		</div>
<?php if ($comment->comment_approved == '0') : ?>
		<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
		<br />
<?php endif; ?>

		<time datetime="<?php echo date(DATE_W3C,strtotime(get_comment_date()." ".get_comment_time())); ?>">
			<?php printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()); ?>
		</time>

    <div class="comment-content">
  		<?php comment_text(); ?>
      <?php //edit_comment_link(__('Edit'),'  ','' ); ?>
    </div>
    
		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
        }

?>